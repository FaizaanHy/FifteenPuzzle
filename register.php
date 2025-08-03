<?php
session_start();
require 'db_connect.php'; // make sure this connects to your DB with $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Basic validation
    if ($password !== $confirm) {
        $_SESSION['register_error'] = "Passwords do not match.";
        header("Location: register.php");
        exit;
    }

    if (strlen($username) < 3 || strlen($password) < 6) {
        $_SESSION['register_error'] = "Username must be at least 3 characters and password at least 6.";
        header("Location: register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email address.";
        header("Location: register.php");
        exit;
    }

    // Check for existing username or email
    $stmt = $pdo->prepare("SELECT user_id  FROM users2 WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    if ($stmt->fetch()) {
        $_SESSION['register_error'] = "Username or email already exists.";
        header("Location: register.php");
        exit;
    }

    // Insert into database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users2 (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $username;

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Fifteen Puzzle</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Create an Account</h1>

    <?php if (isset($_SESSION['register_error'])): ?>
        <p style="color:red"><?= $_SESSION['register_error'] ?></p>
        <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <div class="container">
    <form action="register.php" method="post">
        <label>Username:<br>
            <input type="text" name="username" required>
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" required>
        </label><br><br>

        <label>Password:<br>
            <input type="password" name="password" required>
        </label><br><br>

        <label>Confirm Password:<br>
            <input type="password" name="confirm_password" required>
        </label><br><br>

        <button type="submit">Register</button>
    </form>
    

    <p>Already have an account? <a href="login.php">Login here</a></p>
    <footer>
        <a href="login.php">Return to Homepage</a>
    </footer>
    </div>
</body>
</html>
