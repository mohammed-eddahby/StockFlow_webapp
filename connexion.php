<?php
// connexion.php – Configuration de la base de données XAMPP
$host     = 'localhost';
$dbname   = 'stockflow2';
$user     = 'root';
$password = '';          

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['erreur' => 'Connexion BDD impossible : ' . $e->getMessage()]));
}
