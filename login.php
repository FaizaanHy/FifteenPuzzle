<?php 
session_start(); 
require 'db_connect.php'; 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = trim($_POST['username']); 
    $password = $_POST['password']; 

    $stmt = $pdo->prepare("SELECT * FROM users2 WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        if (!$user['is_active']) {
            $error = "Your account has been deactivated.";
        } elseif (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Update last login 
            $stmt = $pdo->prepare("UPDATE users2 SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);

            header("Location: fifteen.html");  // redirect to game page 
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - Fifteen Puzzle</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php"> 
          <label>Username: <input type="text" name="username" required></label><br> 
          <label>Password: <input type="password" name="password" required></label><br> 
          <button type="submit">Login</button> 
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p>Are you an admin? <a href="admin-login.php">Login here</a></p>
    </div>
</body>
</html>
