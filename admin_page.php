<?php
session_start();
require_once 'config.php';

/* ================= AUTH FIX ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_register.php");
    exit();
}

/* ================= ADD PRODUCT ================= */
if (isset($_POST['add_product'])) {
    $reference = $conn->real_escape_string($_POST['reference']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);
    $seuil_alerte = intval($_POST['seuil_alerte']);

    $sql = "INSERT INTO produits (reference, nom, prix_unitaire, seuil_alerte)
            VALUES ('$reference', '$nom', $prix_unitaire, $seuil_alerte)";

    $conn->query($sql);
}

/* ================= EDIT PRODUCT ================= */
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $reference = $conn->real_escape_string($_POST['edit_reference']);
    $nom = $conn->real_escape_string($_POST['edit_nom']);
    $prix_unitaire = floatval($_POST['edit_prix_unitaire']);
    $seuil_alerte = intval($_POST['edit_seuil_alerte']);

    $sql = "UPDATE produits SET
            reference='$reference',
            nom='$nom',
            prix_unitaire=$prix_unitaire,
            seuil_alerte=$seuil_alerte
            WHERE id=$id";

    $conn->query($sql);
}

/* ================= DELETE PRODUCT ================= */
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);

    $conn->query("DELETE FROM mouvements_stock WHERE produit_id=$id");
    $conn->query("DELETE FROM produits WHERE id=$id");
}

/* ================= ADD MOVEMENT ================= */
if (isset($_POST['add_movement'])) {
    $produit_id = intval($_POST['produit_id']);
    $type = $_POST['type_mouvement'];
    $qte = intval($_POST['quantite']);

    if ($type == "sortie") {
        $r = $conn->query("
            SELECT 
            COALESCE(SUM(CASE WHEN type_mouvement='entree' THEN quantite ELSE 0 END),0)
            - COALESCE(SUM(CASE WHEN type_mouvement='sortie' THEN quantite ELSE 0 END),0)
            AS stock
            FROM mouvements_stock
            WHERE produit_id=$produit_id
        ");

        $stock = $r->fetch_assoc()['stock'];

        if ($stock < $qte) {
            $error = "Stock insuffisant";
        } else {
            $conn->query("INSERT INTO mouvements_stock (produit_id,type_mouvement,quantite,date)
            VALUES ($produit_id,'$type',$qte,NOW())");
        }
    } else {
        $conn->query("INSERT INTO mouvements_stock (produit_id,type_mouvement,quantite,date)
        VALUES ($produit_id,'$type',$qte,NOW())");
    }
}

/* ================= STATS ================= */
$stats = [];

$stats['total_produits'] = $conn->query("SELECT COUNT(*) as t FROM produits")->fetch_assoc()['t'];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];

$stats['produits_alerte'] = $conn->query("
    SELECT COUNT(*) as t FROM (
        SELECT p.id,
        (COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE 0 END),0)
        - COALESCE(SUM(CASE WHEN m.type_mouvement='sortie' THEN m.quantite ELSE 0 END),0)) as stock
        FROM produits p
        LEFT JOIN mouvements_stock m ON p.id=m.produit_id
        GROUP BY p.id
        HAVING stock <= p.seuil_alerte
    ) x
")->fetch_assoc()['t'];

$stats['valeur_stock'] = $conn->query("
    SELECT SUM(prix_unitaire * stock) as v FROM (
        SELECT p.prix_unitaire,
        (COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE 0 END),0)
        - COALESCE(SUM(CASE WHEN m.type_mouvement='sortie' THEN m.quantite ELSE 0 END),0)) as stock
        FROM produits p
        LEFT JOIN mouvements_stock m ON p.id=m.produit_id
        GROUP BY p.id
    ) s
")->fetch_assoc()['v'] ?? 0;

/* ================= DATA ================= */
$produits = $conn->query("
SELECT p.*,
(COALESCE(SUM(CASE WHEN m.type_mouvement='entree' THEN m.quantite ELSE 0 END),0)
- COALESCE(SUM(CASE WHEN m.type_mouvement='sortie' THEN m.quantite ELSE 0 END),0)) as stock
FROM produits p
LEFT JOIN mouvements_stock m ON p.id=m.produit_id
GROUP BY p.id
");

$mouvements = $conn->query("
SELECT m.*, p.nom, p.reference
FROM mouvements_stock m
JOIN produits p ON p.id=m.produit_id
ORDER BY m.date DESC
LIMIT 10
");

$users = $conn->query("SELECT name,email,role FROM users");
?>