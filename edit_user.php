<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check admin role
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users2 WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
if (!$admin || $admin['role'] !== 'admin') {
    die("Access denied.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}
$user_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_role = $_POST['role'];
    $new_password = $_POST['password'];

    // Validate inputs here as needed

    // Update username, email, role
    $update_stmt = $pdo->prepare("UPDATE users2 SET username = ?, email = ?, role = ? WHERE id = ?");
    $update_stmt->execute([$new_username, $new_email, $new_role, $user_id]);

    // Update password if provided
    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $pass_stmt = $pdo->prepare("UPDATE users2 SET password_hash = ? WHERE id = ?");
        $pass_stmt->execute([$hashed, $user_id]);
    }

    header("Location: manage_users.php");
    exit;
}

// Fetch user details to edit
$stmt = $pdo->prepare("SELECT username, email, role FROM users2 WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="style.css">
    <title>Edit User</title>
</head>
<body>
    <div class="container">
    <h1>Edit User: <?= htmlspecialchars($user['username']) ?></h1>
    <form method="post" action="edit_user.php?id=<?= $user_id ?>">
        <label>Username:<br>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </label><br><br>

        <label>Role:<br>
            <select name="role" required>
                <option value="player" <?= $user['role'] === 'player' ? 'selected' : '' ?>>Player</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </label><br><br>

        <label>New Password (leave blank to keep current):<br>
            <input type="password" name="password">
        </label><br><br>

        <button type="submit">Save Changes</button>
    </form>

    <p><a href="manage_users.php">Back to User List</a></p>
    </div>
</body>
</html>
