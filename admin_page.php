<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ========== GESTION DES ACTIONS ==========

// AJOUTER UN PRODUIT
if (isset($_POST['add_product'])) {
    $reference = $conn->real_escape_string($_POST['reference']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);
    $seuil_alerte = intval($_POST['seuil_alerte']);

    $sql = "INSERT INTO produits (reference, nom, prix_unitaire, seuil_alerte) 
            VALUES ('$reference', '$nom', $prix_unitaire, $seuil_alerte)";
    
    if ($conn->query($sql)) {
        $success = "Produit ajouté avec succès !";
    } else {
        $error = "Erreur lors de l'ajout du produit.";
    }
}

// MODIFIER UN PRODUIT
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $reference = $conn->real_escape_string($_POST['edit_reference']);
    $nom = $conn->real_escape_string($_POST['edit_nom']);
    $prix_unitaire = floatval($_POST['edit_prix_unitaire']);
    $seuil_alerte = intval($_POST['edit_seuil_alerte']);

    $sql = "UPDATE produits SET 
            reference = '$reference', 
            nom = '$nom', 
            prix_unitaire = $prix_unitaire, 
            seuil_alerte = $seuil_alerte 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $success = "Produit modifié avec succès !";
    } else {
        $error = "Erreur lors de la modification.";
    }
}

// SUPPRIMER UN PRODUIT
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    
    // Supprimer d'abord les mouvements liés
    $conn->query("DELETE FROM mouvements_stock WHERE produit_id = $id");
    
    // Puis supprimer le produit
    $sql = "DELETE FROM produits WHERE id = $id";
    
    if ($conn->query($sql)) {
        $success = "Produit supprimé avec succès !";
    } else {
        $error = "Erreur lors de la suppression.";
    }
}

// AJOUTER UN MOUVEMENT DE STOCK
if (isset($_POST['add_movement'])) {
    $produit_id = intval($_POST['produit_id']);
    $type_mouvement = $_POST['type_mouvement'];
    $quantite = intval($_POST['quantite']);
    
    // Vérifier le stock actuel pour les sorties
    if ($type_mouvement === 'sortie') {
        $stock_query = "SELECT 
            COALESCE(SUM(CASE WHEN type_mouvement = 'entree' THEN quantite ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN type_mouvement = 'sortie' THEN quantite ELSE 0 END), 0) as stock_actuel
            FROM mouvements_stock WHERE produit_id = $produit_id";
        
        $result = $conn->query($stock_query);
        $stock_data = $result->fetch_assoc();
        
        if ($stock_data['stock_actuel'] < $quantite) {
            $error = "Stock insuffisant ! Stock actuel : " . $stock_data['stock_actuel'];
        } else {
            $sql = "INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, date) 
                    VALUES ($produit_id, '$type_mouvement', $quantite, NOW())";
            
            if ($conn->query($sql)) {
                $success = "Mouvement enregistré avec succès !";
            } else {
                $error = "Erreur lors de l'enregistrement du mouvement.";
            }
        }
    } else {
        $sql = "INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, date) 
                VALUES ($produit_id, '$type_mouvement', $quantite, NOW())";
        
        if ($conn->query($sql)) {
            $success = "Mouvement enregistré avec succès !";
        } else {
            $error = "Erreur lors de l'enregistrement du mouvement.";
        }
    }
}

// ========== RÉCUPÉRATION DES DONNÉES ==========

// Statistiques
$stats = [];

// Total produits
$result = $conn->query("SELECT COUNT(*) as total FROM produits");
$stats['total_produits'] = $result->fetch_assoc()['total'];

// Total utilisateurs
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Produits en alerte
$alert_query = "SELECT COUNT(*) as total FROM (
    SELECT p.id, p.seuil_alerte,
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'entree' THEN m.quantite ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'sortie' THEN m.quantite ELSE 0 END), 0) as stock_actuel
    FROM produits p
    LEFT JOIN mouvements_stock m ON p.id = m.produit_id
    GROUP BY p.id
    HAVING stock_actuel <= p.seuil_alerte
) as low_stock";
$result = $conn->query($alert_query);
$stats['produits_alerte'] = $result->fetch_assoc()['total'];

// Valeur totale du stock
$value_query = "SELECT SUM(p.prix_unitaire * stock_actuel) as valeur_totale FROM (
    SELECT p.id, p.prix_unitaire,
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'entree' THEN m.quantite ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'sortie' THEN m.quantite ELSE 0 END), 0) as stock_actuel
    FROM produits p
    LEFT JOIN mouvements_stock m ON p.id = m.produit_id
    GROUP BY p.id
) as stock_data";
$result = $conn->query($value_query);
$stats['valeur_stock'] = $result->fetch_assoc()['valeur_totale'] ?? 0;

// Liste des produits avec stock actuel
$produits_query = "SELECT p.id, p.reference, p.nom, p.prix_unitaire, p.seuil_alerte,
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'entree' THEN m.quantite ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'sortie' THEN m.quantite ELSE 0 END), 0) as stock_actuel
    FROM produits p
    LEFT JOIN mouvements_stock m ON p.id = m.produit_id
    GROUP BY p.id
    ORDER BY p.nom ASC";
$produits_result = $conn->query($produits_query);

// Liste des derniers mouvements
$mouvements_query = "SELECT m.*, p.nom as produit_nom, p.reference
    FROM mouvements_stock m
    JOIN produits p ON m.produit_id = p.id
    ORDER BY m.date DESC
    LIMIT 10";
$mouvements_result = $conn->query($mouvements_query);

// Liste des produits pour les select
$produits_select = $conn->query("SELECT id, reference, nom FROM produits ORDER BY nom ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StockFlow</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-boxes"></i>
            <span>StockFlow</span>
        </div>
        
        <nav class="nav-menu">
            <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="#produits" class="nav-item" onclick="showSection('produits')">
                <i class="fas fa-box"></i>
                <span>Produits</span>
            </a>
            <a href="#mouvements" class="nav-item" onclick="showSection('mouvements')">
                <i class="fas fa-exchange-alt"></i>
                <span>Mouvements</span>
            </a>
            <a href="#users" class="nav-item" onclick="showSection('users')">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                    <div class="user-role">Administrateur</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- HEADER -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher..." id="globalSearch">
                </div>
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <?php if ($stats['produits_alerte'] > 0): ?>
                    <span class="badge"><?php echo $stats['produits_alerte']; ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <!-- ALERTS -->
        <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- DASHBOARD SECTION -->
        <section id="dashboard" class="content-section active">
            <!-- STATISTIQUES -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_produits']; ?></h3>
                        <p>Total Produits</p>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['produits_alerte']; ?></h3>
                        <p>Stock Faible</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['valeur_stock'], 2); ?> €</h3>
                        <p>Valeur Stock</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Utilisateurs</p>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <button class="action-btn" onclick="openModal('addProductModal')">
                    <i class="fas fa-plus"></i>
                    Ajouter Produit
                </button>
                <button class="action-btn" onclick="openModal('addMovementModal')">
                    <i class="fas fa-exchange-alt"></i>
                    Mouvement Stock
                </button>
                <button class="action-btn" onclick="showSection('produits')">
                    <i class="fas fa-list"></i>
                    Voir Produits
                </button>
            </div>

            <!-- RECENT MOVEMENTS -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Derniers Mouvements</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Produit</th>
                                <th>Référence</th>
                                <th>Type</th>
                                <th>Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($mouv = $mouvements_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($mouv['date'])); ?></td>
                                <td><?php echo htmlspecialchars($mouv['produit_nom']); ?></td>
                                <td><span class="badge badge-gray"><?php echo htmlspecialchars($mouv['reference']); ?></span></td>
                                <td>
                                    <?php if ($mouv['type_mouvement'] === 'entree'): ?>
                                        <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Entrée</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Sortie</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $mouv['quantite']; ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- PRODUITS SECTION -->
        <section id="produits" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-box"></i> Gestion des Produits</h2>
                <button class="btn btn-primary" onclick="openModal('addProductModal')">
                    <i class="fas fa-plus"></i> Ajouter Produit
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un produit..." id="searchProduct" onkeyup="searchTable('productsTable', 'searchProduct')">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Nom</th>
                                <th>Prix Unitaire</th>
                                <th>Stock Actuel</th>
                                <th>Seuil Alerte</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $produits_result->data_seek(0);
                            while ($prod = $produits_result->fetch_assoc()): 
                                $is_low_stock = $prod['stock_actuel'] <= $prod['seuil_alerte'];
                            ?>
                            <tr class="<?php echo $is_low_stock ? 'low-stock-row' : ''; ?>">
                                <td><span class="badge badge-gray"><?php echo htmlspecialchars($prod['reference']); ?></span></td>
                                <td class="font-bold"><?php echo htmlspecialchars($prod['nom']); ?></td>
                                <td><?php echo number_format($prod['prix_unitaire'], 2); ?> €</td>
                                <td>
                                    <span class="badge <?php echo $is_low_stock ? 'badge-danger' : 'badge-success'; ?>">
                                        <?php echo $prod['stock_actuel']; ?>
                                    </span>
                                </td>
                                <td><?php echo $prod['seuil_alerte']; ?></td>
                                <td>
                                    <?php if ($is_low_stock): ?>
                                        <span class="status-badge status-danger">
                                            <i class="fas fa-exclamation-circle"></i> Stock Faible
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check-circle"></i> OK
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-icon btn-edit" onclick='editProduct(<?php echo json_encode($prod); ?>)' title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nom']); ?>')" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- MOUVEMENTS SECTION -->
        <section id="mouvements" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-exchange-alt"></i> Mouvements de Stock</h2>
                <button class="btn btn-primary" onclick="openModal('addMovementModal')">
                    <i class="fas fa-plus"></i> Nouveau Mouvement
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un mouvement..." id="searchMovement" onkeyup="searchTable('movementsTable', 'searchMovement')">
                    </div>
                    <div class="filter-btns">
                        <button class="btn btn-sm" onclick="filterMovements('all')">Tous</button>
                        <button class="btn btn-sm" onclick="filterMovements('entree')">Entrées</button>
                        <button class="btn btn-sm" onclick="filterMovements('sortie')">Sorties</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="movementsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Produit</th>
                                <th>Référence</th>
                                <th>Type</th>
                                <th>Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $mouvements_result->data_seek(0);
                            while ($mouv = $mouvements_result->fetch_assoc()): 
                            ?>
                            <tr data-type="<?php echo $mouv['type_mouvement']; ?>">
                                <td><?php echo date('d/m/Y H:i', strtotime($mouv['date'])); ?></td>
                                <td class="font-bold"><?php echo htmlspecialchars($mouv['produit_nom']); ?></td>
                                <td><span class="badge badge-gray"><?php echo htmlspecialchars($mouv['reference']); ?></span></td>
                                <td>
                                    <?php if ($mouv['type_mouvement'] === 'entree'): ?>
                                        <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Entrée</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Sortie</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $mouv['quantite']; ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- USERS SECTION -->
        <section id="users" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Gestion des Utilisateurs</h2>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY name ASC");
                            while ($user = $users_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="font-bold"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge badge-primary"><i class="fas fa-crown"></i> Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-user"></i> User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- MODALS -->

    <!-- Modal Ajouter Produit -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Ajouter un Produit</h2>
                <button class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-barcode"></i> Référence *</label>
                        <input type="text" name="reference" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nom du Produit *</label>
                        <input type="text" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-euro-sign"></i> Prix Unitaire (€) *</label>
                        <input type="number" name="prix_unitaire" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-bell"></i> Seuil d'Alerte *</label>
                        <input type="number" name="seuil_alerte" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">Annuler</button>
                    <button type="submit" name="add_product" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Modifier Produit -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Modifier le Produit</h2>
                <button class="modal-close" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-barcode"></i> Référence *</label>
                        <input type="text" name="edit_reference" id="edit_reference" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nom du Produit *</label>
                        <input type="text" name="edit_nom" id="edit_nom" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-euro-sign"></i> Prix Unitaire (€) *</label>
                        <input type="number" name="edit_prix_unitaire" id="edit_prix_unitaire" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-bell"></i> Seuil d'Alerte *</label>
                        <input type="number" name="edit_seuil_alerte" id="edit_seuil_alerte" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Annuler</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ajouter Mouvement -->
    <div id="addMovementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exchange-alt"></i> Nouveau Mouvement de Stock</h2>
                <button class="modal-close" onclick="closeModal('addMovementModal')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Produit *</label>
                    <select name="produit_id" required>
                        <option value="">Sélectionner un produit</option>
                        <?php 
                        $produits_select->data_seek(0);
                        while ($p = $produits_select->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo htmlspecialchars($p['reference'] . ' - ' . $p['nom']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Type de Mouvement *</label>
                    <select name="type_mouvement" required>
                        <option value="">Sélectionner un type</option>
                        <option value="entree">Entrée de Stock</option>
                        <option value="sortie">Sortie de Stock</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-hashtag"></i> Quantité *</label>
                    <input type="number" name="quantite" min="1" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addMovementModal')">Annuler</button>
                    <button type="submit" name="add_movement" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>