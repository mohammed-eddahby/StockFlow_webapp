<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer tous les produits avec stock actuel
$produits_query = "SELECT p.id, p.reference, p.nom, p.prix_unitaire, p.seuil_alerte,
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'entree' THEN m.quantite ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'sortie' THEN m.quantite ELSE 0 END), 0) as stock_actuel
    FROM produits p
    LEFT JOIN mouvements_stock m ON p.id = m.produit_id
    GROUP BY p.id
    ORDER BY p.nom ASC";
$produits_result = $conn->query($produits_query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Utilisateur - StockFlow</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="user-page">
    <!-- HEADER -->
    <header class="user-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>StockFlow</span>
            </div>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['name']); ?>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <div class="user-content">
        <div class="page-header">
            <h1><i class="fas fa-warehouse"></i> Inventaire des Produits</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher un produit..." id="searchProduct" onkeyup="searchUserTable()">
            </div>
        </div>

        <!-- STATISTIQUES RAPIDES -->
        <div class="user-stats">
            <div class="stat-item">
                <i class="fas fa-box"></i>
                <div>
                    <h3><?php echo $produits_result->num_rows; ?></h3>
                    <p>Produits</p>
                </div>
            </div>
            <div class="stat-item alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <?php
                    $produits_result->data_seek(0);
                    $low_stock_count = 0;
                    while ($p = $produits_result->fetch_assoc()) {
                        if ($p['stock_actuel'] <= $p['seuil_alerte']) {
                            $low_stock_count++;
                        }
                    }
                    ?>
                    <h3><?php echo $low_stock_count; ?></h3>
                    <p>Stock Faible</p>
                </div>
            </div>
        </div>

        <!-- TABLEAU DES PRODUITS -->
        <div class="user-card">
            <div class="table-responsive">
                <table class="user-table" id="userProductsTable">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Nom du Produit</th>
                            <th>Prix Unitaire</th>
                            <th>Stock Actuel</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $produits_result->data_seek(0);
                        while ($prod = $produits_result->fetch_assoc()): 
                            $is_low_stock = $prod['stock_actuel'] <= $prod['seuil_alerte'];
                        ?>
                        <tr class="<?php echo $is_low_stock ? 'low-stock-row' : ''; ?>">
                            <td>
                                <span class="badge badge-gray">
                                    <?php echo htmlspecialchars($prod['reference']); ?>
                                </span>
                            </td>
                            <td class="product-name">
                                <i class="fas fa-box"></i>
                                <?php echo htmlspecialchars($prod['nom']); ?>
                            </td>
                            <td class="price">
                                <i class="fas fa-euro-sign"></i>
                                <?php echo number_format($prod['prix_unitaire'], 2); ?> €
                            </td>
                            <td>
                                <span class="stock-badge <?php echo $is_low_stock ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $prod['stock_actuel']; ?>
                                </span>
                            </td>
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
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function searchUserTable() {
        const input = document.getElementById('searchProduct');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('userProductsTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let txtValue = tr[i].textContent || tr[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>