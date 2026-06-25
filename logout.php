<?php
// logout.php – Détruit la session et renvoie vers la page de connexion
session_start();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
