<?php
session_start();
// التأكد واش المستخدم أدمين بصح
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="index.css">
    <title>Admin Dashboard - CodeWithDahby</title>
    <style>
        .welcome-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 198, 255, 0.3);
            box-shadow: 0 0 20px rgba(0, 198, 255, 0.2);
        }
        h1 { color: #00c6ff; text-shadow: 0 0 10px rgba(0, 198, 255, 0.5); }
        .role-badge {
            background: #ff4d4d;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            text-transform: uppercase;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-container">
            <span class="role-badge">Admin Panel</span>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h1>
            <p>You have full access to the Stock Management System.</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>