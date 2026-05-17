<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - StockFlow</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="user-page">

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

<div class="user-content">

    <div class="page-header">
        <h1><i class="fas fa-warehouse"></i> Inventaire des Produits</h1>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchProduct" placeholder="Rechercher..." onkeyup="searchUserTable()">
        </div>
    </div>

    <!-- STATS -->
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
                $low_stock = 0;

                while ($p = $produits_result->fetch_assoc()) {
                    if ($p['stock_actuel'] <= $p['seuil_alerte']) {
                        $low_stock++;
                    }
                }
                ?>
                <h3><?php echo $low_stock; ?></h3>
                <p>Stock Faible</p>
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <div class="user-card">
        <table class="user-table" id="userProductsTable">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $produits_result->data_seek(0);

                while ($prod = $produits_result->fetch_assoc()):
                    $low = $prod['stock_actuel'] <= $prod['seuil_alerte'];
                ?>
                <tr class="<?php echo $low ? 'low-stock-row' : ''; ?>">

                    <td>
                        <span class="badge badge-gray">
                            <?php echo htmlspecialchars($prod['reference']); ?>
                        </span>
                    </td>

                    <td>
                        <i class="fas fa-box"></i>
                        <?php echo htmlspecialchars($prod['nom']); ?>
                    </td>

                    <td>
                        <?php echo number_format($prod['prix_unitaire'], 2); ?> €
                    </td>

                    <td>
                        <span class="<?php echo $low ? 'stock-low' : 'stock-ok'; ?>">
                            <?php echo $prod['stock_actuel']; ?>
                        </span>
                    </td>

                    <td>
                        <?php if ($low): ?>
                            <span class="status-badge status-danger">Stock Faible</span>
                        <?php else: ?>
                            <span class="status-badge status-success">OK</span>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>

</div>

<script>
function searchUserTable() {
    let input = document.getElementById("searchProduct");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("userProductsTable");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let txt = tr[i].innerText;
        tr[i].style.display = txt.toUpperCase().includes(filter) ? "" : "none";
    }
}
</script>

</body>
</html>