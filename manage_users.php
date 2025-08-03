<?php
session_start();
require 'db_connect.php';

// Admin check (same as above)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users2 WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Handle activation/deactivation if requested
if (isset($_GET['toggle_active']) && is_numeric($_GET['toggle_active'])) {
    $toggle_id = intval($_GET['toggle_active']);
    // Fetch current status
    $stmt = $pdo->prepare("SELECT is_active FROM users2 WHERE id = ?");
    $stmt->execute([$toggle_id]);
    $target = $stmt->fetch();
    if ($target) {
        $new_status = $target['is_active'] ? 0 : 1;
        $update = $pdo->prepare("UPDATE users2 SET is_active = ? WHERE id = ?");
        $update->execute([$new_status, $toggle_id]);
    }
    header("Location: manage_users.php");
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email, role, is_active FROM users2 ORDER BY username");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="style.css">
    <title>Manage Users</title>
</head>
<body>
    <div class="container">
    <h1>Manage Users</h1>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $u['id'] ?>">Edit</a> |
                        <a href="manage_users.php?toggle_active=<?= $u['id'] ?>">
                            <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
