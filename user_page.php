<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="index.css">
    <title>User Dashboard - CodeWithDahby</title>
    <style>
        .welcome-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(57, 255, 20, 0.3);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);
        }
        h1 { color: #39FF14; text-shadow: 0 0 10px rgba(57, 255, 20, 0.5); }
        .role-badge {
            background: #0072ff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-container">
            <span class="role-badge">User Dashboard</span>
            <h1>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p>Welcome to your personal dashboard.</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>