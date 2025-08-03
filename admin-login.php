<?php
session_start();
require 'db_connect.php';  // make sure this path is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

$stmt = $pdo->prepare("SELECT user_id, password_hash, is_admin FROM users2 WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && $user['is_admin'] == 1) {
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['role'] = 'admin'; 
        $_SESSION['username'] = $username;

        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Invalid password!";
    }
} else {
    echo "User not found or not an admin!";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Login - Fifteen Puzzle</title>
    <link rel="stylesheet" href="style.css" />
</head>
    <div class="container">
<body>
    <form method="POST" class="login-form">
        <h2>Admin Login</h2>
        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <a href="login.php" class="btn btn-primary">Back to Login</a>

</div>
</body>
</html>
