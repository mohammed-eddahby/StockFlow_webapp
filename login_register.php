<?php
// login_register.php – Traite les soumissions des formulaires de connexion / inscription
session_start();
require_once 'connexion.php';

// ============================================================
//  VALIDATION EMAIL
//  filter_var(..., FILTER_VALIDATE_EMAIL) accepte "user@gmail"
//  (sans extension), donc on ajoute une vérification stricte
//  exigeant un domaine avec un point + une extension (.com, .fr, ...).
// ============================================================
function email_est_valide(string $email): bool {
    $email = trim($email);
    if ($email === '') {
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    // Exige explicitement un point suivi d'une extension d'au moins 2 lettres
    if (!preg_match('/@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
        return false;
    }
    return true;
}

// ============================================================
//  CONNEXION
// ============================================================
if (isset($_POST['login'])) {
    $_SESSION['active_form'] = 'login';

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['login_error'] = 'Veuillez renseigner votre email et votre mot de passe.';
        header('Location: index.php');
        exit;
    }

    if (!email_est_valide($email)) {
        $_SESSION['login_error'] = 'Format d\'email invalide (ex : nom@domaine.com).';
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch();

    // Comparaison stricte : il faut à la fois un utilisateur existant
    // ET un mot de passe qui correspond au hash stocké.
    if (!$utilisateur || !is_string($utilisateur['mot_de_passe'] ?? null) || !password_verify($password, $utilisateur['mot_de_passe'])) {
        $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
        header('Location: index.php');
        exit;
    }

    // Connexion réussie
    session_regenerate_id(true);
    $_SESSION['user_id']   = $utilisateur['id'];
    $_SESSION['user_nom']  = $utilisateur['nom'];
    $_SESSION['user_role'] = $utilisateur['role'];
    unset($_SESSION['active_form'], $_SESSION['login_error']);

    header('Location: dashboard.php');
    exit;
}

// ============================================================
//  INSCRIPTION
// ============================================================
if (isset($_POST['register'])) {
    $_SESSION['active_form'] = 'register';

    $nom      = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';
    if (!in_array($role, ['user', 'admin'], true)) {
        $role = 'user';
    }

    if ($nom === '' || $email === '' || $password === '') {
        $_SESSION['register_error'] = 'Tous les champs sont obligatoires.';
        header('Location: index.php');
        exit;
    }

    if (!email_est_valide($email)) {
        $_SESSION['register_error'] = 'Format d\'email invalide (ex : nom@domaine.com).';
        header('Location: index.php');
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['register_error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        header('Location: index.php');
        exit;
    }

    // Vérifier que l'email n'existe pas déjà
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'Un compte existe déjà avec cet email.';
        header('Location: index.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$nom, $email, $hash, $role]);

    // Connexion automatique après inscription
    $_SESSION['user_id']   = $pdo->lastInsertId();
    $_SESSION['user_nom']  = $nom;
    $_SESSION['user_role'] = $role;
    unset($_SESSION['active_form'], $_SESSION['register_error']);

    header('Location: dashboard.php');
    exit;
}

// Aucune action reconnue : retour à l'accueil
header('Location: index.php');
exit;
