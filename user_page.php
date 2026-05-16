<?php
session_start();

if (!isset($_SESSION['name'])) {
    header("Location: index.php");
    exit();
}

include 'config/db.php';

// Requête produits + stock actuel
$sql = "
SELECT 
    p.id,
    p.reference,
    p.nom,
    p.prix_unitaire,
    p.seuil_alerte,
    
    COALESCE(SUM(
        CASE
            WHEN m.type_mouvement = 'entree' THEN m.quantite
            ELSE -m.quantite
        END
    ),0) AS stock_actuel

FROM produits p

LEFT JOIN mouvements_stock m 
ON p.id = m.produit_id

GROUP BY p.id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>

<style>

body{
    font-family: Arial;
    background: #111;
    color: white;
    margin: 0;
    padding: 0;
}

.container{
    width: 90%;
    margin: auto;
    padding: 30px;
}

h1{
    color: #39FF14;
}

.role-badge{
    background: #0072ff;
    padding: 8px 15px;
    border-radius: 20px;
}

.logout-btn{
    padding: 10px 20px;
    background: red;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

table{
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: #222;
}

table th, table td{
    padding: 15px;
    border: 1px solid #444;
    text-align: center;
}

table th{
    background: #39FF14;
    color: black;
}

.alert{
    background: #ff4444;
    color: white;
    font-weight: bold;
}

.normal{
    background: #1e1e1e;
}

</style>

</head>

<body>

<div class="container">

    <span class="role-badge">User Dashboard</span>

    <h1>
        Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!
    </h1>

    <p>Welcome to StockFlow.</p>

    <a href="logout.php" class="logout-btn">Logout</a>

    <h2>Products Stock</h2>

    <table>

        <tr>
            <th>Reference</th>
            <th>Product</th>
            <th>Price</th>
            <th>Current Stock</th>
            <th>Status</th>
        </tr>

        <?php
        if($result->num_rows > 0){

            while($row = $result->fetch_assoc()){

                $alert = $row['stock_actuel'] <= $row['seuil_alerte'];

                echo "<tr class='".($alert ? "alert" : "normal")."'>";

                echo "<td>".$row['reference']."</td>";
                echo "<td>".$row['nom']."</td>";
                echo "<td>".$row['prix_unitaire']." DH</td>";
                echo "<td>".$row['stock_actuel']."</td>";

                if($alert){
                    echo "<td>Low Stock ⚠️</td>";
                }else{
                    echo "<td>Available ✅</td>";
                }

                echo "</tr>";
            }

        } else {
            echo "<tr><td colspan='5'>No products found</td></tr>";
        }
        ?>

    </table>

</div>

</body>
</html>