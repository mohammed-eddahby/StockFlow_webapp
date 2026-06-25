<?php
// api.php – Point d'entrée unique pour toutes les requêtes AJAX
header('Content-Type: application/json; charset=utf-8');

session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['erreur' => 'Non authentifié']);
    exit;
}
$role = $_SESSION['user_role'] ?? 'user';

require_once 'connexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Actions réservées aux administrateurs (gestion produits, catégories, correction des mouvements)
$actionsAdmin = [
    'ajouter_produit', 'modifier_produit', 'supprimer_produit',
    'ajouter_categorie', 'supprimer_mouvement',
];
if (in_array($action, $actionsAdmin, true) && $role !== 'admin') {
    http_response_code(403);
    echo json_encode(['erreur' => 'Action réservée aux administrateurs']);
    exit;
}

try {

// ============================================================
//  STOCK – liste avec état d'alerted
// ============================================================
if ($action === 'liste_stock') {
    $stmt = $pdo->query("
        SELECT
            p.id, p.reference, p.nom,
            c.nom AS categorie,
            p.prix_unitaire, p.seuil_alerte,
            COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE -m.quantite END),0) AS stock_actuel
        FROM produits p
        LEFT JOIN mouvements_stock m    ON p.id = m.produit_id
        LEFT JOIN categories_produits c ON p.categorie_id = c.id
        GROUP BY p.id
        ORDER BY p.nom
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

// ============================================================
//  STOCK – produits en alerte seulement
// ============================================================
if ($action === 'alertes') {
    $stmt = $pdo->query("
        SELECT
            p.id, p.reference, p.nom,
            COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE -m.quantite END),0) AS stock_actuel,
            p.seuil_alerte
        FROM produits p
        LEFT JOIN mouvements_stock m ON p.id = m.produit_id
        GROUP BY p.id
        HAVING stock_actuel <= p.seuil_alerte
        ORDER BY stock_actuel ASC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

// ============================================================
//  PRODUITS – CRUD
// ============================================================
if ($action === 'liste_produits') {
    $stmt = $pdo->query("
        SELECT p.*, c.nom AS categorie_nom
        FROM produits p
        JOIN categories_produits c ON p.categorie_id = c.id
        ORDER BY p.nom
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'ajouter_produit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("
        INSERT INTO produits (categorie_id, reference, nom, description, prix_unitaire, seuil_alerte)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['categorie_id'], $data['reference'], $data['nom'],
        $data['description'] ?? '', $data['prix_unitaire'], $data['seuil_alerte']
    ]);
    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

if ($action === 'modifier_produit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("
        UPDATE produits SET categorie_id=?, reference=?, nom=?, description=?, prix_unitaire=?, seuil_alerte=?
        WHERE id=?
    ");
    $stmt->execute([
        $data['categorie_id'], $data['reference'], $data['nom'],
        $data['description'] ?? '', $data['prix_unitaire'], $data['seuil_alerte'], $data['id']
    ]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'supprimer_produit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $pdo->prepare("DELETE FROM produits WHERE id=?")->execute([$data['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

// ============================================================
//  MOUVEMENTS – liste + ajout + suppression
// ============================================================
if ($action === 'liste_mouvements') {
    $produit_id = $_GET['produit_id'] ?? null;
    if ($produit_id) {
        $stmt = $pdo->prepare("
            SELECT m.*, p.nom AS produit_nom
            FROM mouvements_stock m
            JOIN produits p ON m.produit_id = p.id
            WHERE m.produit_id = ?
            ORDER BY m.date_mouvement DESC
        ");
        $stmt->execute([$produit_id]);
    } else {
        $stmt = $pdo->query("
            SELECT m.*, p.nom AS produit_nom
            FROM mouvements_stock m
            JOIN produits p ON m.produit_id = p.id
            ORDER BY m.date_mouvement DESC
            LIMIT 100
        ");
    }
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'ajouter_mouvement') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("
        INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, commentaire, utilisateur)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['produit_id'], $data['type_mouvement'],
        $data['quantite'], $data['commentaire'] ?? '', $data['utilisateur'] ?? 'Anonyme'
    ]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'supprimer_mouvement') {
    $data = json_decode(file_get_contents('php://input'), true);
    $pdo->prepare("DELETE FROM mouvements_stock WHERE id=?")->execute([$data['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

// ============================================================
//  CATÉGORIES
// ============================================================
if ($action === 'liste_categories') {
    $stmt = $pdo->query("SELECT * FROM categories_produits ORDER BY nom");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'ajouter_categorie') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO categories_produits (nom) VALUES (?)");
    $stmt->execute([$data['nom']]);
    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// ============================================================
//  DASHBOARD – statistiques globales
// ============================================================
if ($action === 'stats') {
    $nb_produits = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
    $nb_alertes  = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT p.id, p.seuil_alerte,
                   COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE -m.quantite END),0) AS s
            FROM produits p LEFT JOIN mouvements_stock m ON p.id=m.produit_id
            GROUP BY p.id, p.seuil_alerte HAVING s <= p.seuil_alerte
        ) x
    ")->fetchColumn();
    $nb_entrees  = $pdo->query("SELECT COALESCE(SUM(quantite),0) FROM mouvements_stock WHERE type_mouvement='entree'")->fetchColumn();
    $nb_sorties  = $pdo->query("SELECT COALESCE(SUM(quantite),0) FROM mouvements_stock WHERE type_mouvement='sortie'")->fetchColumn();
    $val_stock   = $pdo->query("
        SELECT COALESCE(SUM(stock_actuel * prix_unitaire),0) FROM (
            SELECT p.prix_unitaire,
                   COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE -m.quantite END),0) AS stock_actuel
            FROM produits p LEFT JOIN mouvements_stock m ON p.id=m.produit_id
            GROUP BY p.id
        ) x
    ")->fetchColumn();

    echo json_encode([
        'nb_produits' => (int)$nb_produits,
        'nb_alertes'  => (int)$nb_alertes,
        'nb_entrees'  => (int)$nb_entrees,
        'nb_sorties'  => (int)$nb_sorties,
        'val_stock'   => round((float)$val_stock, 2),
    ]);
    exit;
}

echo json_encode(['erreur' => "Action inconnue : $action"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Erreur base de données : ' . $e->getMessage()]);
    exit;
}
