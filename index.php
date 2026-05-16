<?php 
session_start();
$login_err = $_SESSION['login_error'] ?? '';
$reg_err = $_SESSION['register_error'] ?? '';
$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Stock Application</title>
</head>
<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-box <?php echo ($activeForm === 'login') ? 'active' : ''; ?>" id="loginForm">
            <h2>Login</h2>
            <?php if($login_err) echo "<div class='error-message'>$login_err</div>"; ?>
            <form action="login_register.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('registerForm')">Sign up</a></p>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-box <?php echo ($activeForm === 'register') ? 'active' : ''; ?>" id="registerForm">
            <h2>Register</h2>
            <?php if($reg_err) echo "<div class='error-message'>$reg_err</div>"; ?>
            <form action="login_register.php" method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="" disabled selected>--Select Role--</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('loginForm')">Login</a></p>
            </form>
        </div>
    </div>

    <script src="index.js"></script>
</body>
</html>