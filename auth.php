<?php
// auth.php – Garde de session, à inclure en tout premier sur les pages protégées
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: ' . (strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false ? '../index.php' : 'index.php'));
    exit;
}
