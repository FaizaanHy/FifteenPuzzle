<?php
session_start();
require 'db_connect.php';

// Admin check (same as before)
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

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['background_image'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $filename = basename($_FILES['background_image']['name']);
    $target_file = $target_dir . uniqid() . "_" . $filename;

    if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
        $image_name = $_POST['image_name'] ?? $filename;
        $stmt = $pdo->prepare("INSERT INTO background_images2 (image_name, image_url, is_active, uploaded_by_user_id) VALUES (?, ?, 1, ?)");
        $stmt->execute([$image_name, $target_file, $user_id]);
        $message = "Image uploaded successfully.";
    } else {
        $message = "Failed to upload image.";
    }
}

// Fetch existing images
$stmt = $pdo->query("SELECT * FROM background_images2 ORDER BY image_name");
$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8" />
    <title>Manage Background Images</title>
</head>
<body>
    <div class="container">
    <h1>Manage Background Images</h1>

    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="manage_images.php" enctype="multipart/form-data">
        <label>Image Name:<br>
            <input type="text" name="image_name" required>
        </label><br><br>
        <label>Select Image:<br>
            <input type="file" name="background_image" accept="image/*" required>
        </label><br><br>
        <button type="submit">Upload Image</button>
    </form>

    <h2>Existing Images</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr><th>Name</th><th>Preview</th><th>Active</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($images as $img): ?>
                <tr>
                    <td><?= htmlspecialchars($img['image_name']) ?></td>
                    <td><img src="<?= htmlspecialchars($img['image_url']) ?>" alt="" style="height:50px;"></td>
                    <td><?= $img['is_active'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <!-- Add edit/delete/toggle active links here -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
