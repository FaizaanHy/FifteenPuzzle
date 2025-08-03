<?php
session_start();
require 'db_connect.php';



// Handle form submissions for user edits, background image uploads/edits, etc.
// For brevity, only basic example implementations are shown

// --- Handle User Edit (username, activate/deactivate, reset password) ---
if (isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password']; // If empty, no change
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate username length
    if (strlen($new_username) < 3) {
        $message = "Username must be at least 3 characters.";
    } else {
        // Check if username taken by other users
        $stmt = $pdo->prepare("SELECT user_id FROM users2 WHERE username = ? AND user_id != ?");
        $stmt->execute([$new_username, $user_id]);
        if ($stmt->fetch()) {
            $message = "Username already taken.";
        } else {
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users2 SET username = ?, password_hash = ?, is_active = ? WHERE user_id = ?");
                $stmt->execute([$new_username, $hashed, $is_active, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users2 SET username = ?, is_active = ? WHERE user_id = ?");
                $stmt->execute([$new_username, $is_active, $user_id]);
            }
            $message = "User updated successfully.";
        }
    }
}

// --- Handle Background Image Upload ---
if (isset($_POST['upload_bg'])) {
    if (isset($_FILES['bg_file']) && $_FILES['bg_file']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['bg_file']['type'], $allowed_types)) {
            $upload_dir = 'uploads/backgrounds/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $filename = basename($_FILES['bg_file']['name']);
            $target_file = $upload_dir . time() . '_' . $filename;

            var_dump($_FILES['bg_file']['tmp_name']);
var_dump($target_file);
var_dump(is_dir($upload_dir));
var_dump(is_writable($upload_dir));

            if (move_uploaded_file($_FILES['bg_file']['tmp_name'], $target_file)) {
                $image_name = trim($_POST['image_name']) ?: $filename;
                $stmt = $pdo->prepare("INSERT INTO background_images2 (image_name, image_url, is_active) VALUES (?, ?, 1)");
                $stmt->execute([$image_name, $target_file]);
                $message = "Background image uploaded successfully.";
            } else {
                $message = "Error uploading file.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    } else {
        $message = "No file uploaded or upload error.";
    }
}

// --- Handle Background Image Edit/Delete ---
if (isset($_POST['edit_bg'])) {
    $image_id = intval($_POST['image_id']);
    $image_name = trim($_POST['image_name']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE background_images2 SET image_name = ?, is_active = ? WHERE image_id = ?");
    $stmt->execute([$image_name, $is_active, $image_id]);
    $message = "Background image updated.";
}

if (isset($_POST['delete_bg'])) {
    $image_id = intval($_POST['image_id']);

    // Get image URL to delete file from server
    $stmt = $pdo->prepare("SELECT image_url FROM background_images2 WHERE image_id = ?");
    $stmt->execute([$image_id]);
    $row = $stmt->fetch();
    if ($row) {
        $file_path = $row['image_url'];
        if (file_exists($file_path)) unlink($file_path);

        // Instead of DELETE, use UPDATE
    $stmt = $pdo->prepare("UPDATE background_images2 SET is_active = 0 WHERE image_id = ?");
    $stmt->execute([$imageId]);
        $message = "Background image deleted.";
    }
}

// --- Fetch all users ---
$users = $pdo->query("SELECT user_id, username, role, is_active, registration_date FROM users2 ORDER BY user_id ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch all background images ---
$bg_images = $pdo->query("SELECT image_id, image_name, image_url, is_active FROM background_images2 ORDER BY image_id ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch game statistics summary ---
$total_games = $pdo->query("SELECT COUNT(*) FROM game_stats2")->fetchColumn();
$avg_time = $pdo->query("SELECT AVG(time_taken_seconds) FROM game_stats2")->fetchColumn();
$popular_puzzle = $pdo->query("SELECT puzzle_size, COUNT(*) as cnt FROM game_stats2 GROUP BY puzzle_size ORDER BY cnt DESC LIMIT 1")->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Fifteen Puzzle</title>
    <link rel="stylesheet" href="style.css" />
    <style>
       body {
  font-family: Arial, sans-serif;
  margin: 20px;
  background-color: #fafafa; /* subtle background to soften overall look */
}

h1 {
  text-align: center;
  color: #333;
}

table {
  width: 90%;              /* slightly less than full width to add side space */
  max-width: 900px;        /* max width to prevent it from stretching too much on big screens */
  margin: 0 auto 40px;     /* center table horizontally + bottom margin */
  border-collapse: separate; /* use separate borders for cleaner look */
  border-spacing: 0;       /* no space between cells */
  box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* subtle shadow for depth */
  background-color: white; /* white background for the table */
  border-radius: 6px;      /* rounded corners */
  overflow: hidden;        /* to keep border-radius working */
}

th, td {
  border-bottom: 1px solid #ddd; /* lighter border only at bottom */
  padding: 12px 15px;             /* more padding for spacious cells */
  text-align: left;
  color: #444;
}

th {
  background: #f9f9f9;
  font-weight: 600;
  color: #555;
}

tr:last-child td {
  border-bottom: none; /* remove border for last row */
}

input[type=text], input[type=password], input[type=file] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.btn {
  padding: 8px 16px;
  margin: 6px 0;
  cursor: pointer;
  border-radius: 4px;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.btn-danger {
  background-color: #d9534f;
  color: white;
  border: none;
}

.btn-danger:hover {
  background-color: #c9302c;
}

.btn-primary {
  background-color: #0275d8;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #025aa5;
}

.message {
  padding: 12px 20px;
  background: #dff0d8;
  color: #3c763d;
  margin-bottom: 20px;
  border-radius: 4px;
  border: 1px solid #d6e9c6;
}

.section {
  margin-bottom: 60px;
}

label {
  display: block;
  margin: 8px 0 4px;
  font-weight: 600;
  color: #333;
}

form.inline {
  display: inline-block;
  margin-right: 10px;
}
 
    </style>
</head>
<body>
<div class="container">
<h1>Admin Dashboard</h1>

<?php if (!empty($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- User Management -->
<div class="section">
    <h2>User Account Management</h2>
<div class="table-wrapper">
    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Active</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <form method="POST" class="inline">
                    <td><?= $user['user_id'] ?></td>
                    <td><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>></td>
                    <td><?= $user['registration_date'] ?></td>
                    <td>
                        <input type="password" name="password" placeholder="New Password">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <label>
  <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
  Active
</label>

                        <button type="submit" name="edit_user" class="btn btn-primary">Save</button>
                    </td>
                </form>
            </tr>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Background Image Management -->
<div class="section">
    <h2>Background Image Management</h2>
    <form method="POST" enctype="multipart/form-data" style="margin-bottom:20px;">
        <label for="image_name">Image Name:</label>
        <input type="text" name="image_name" id="image_name" required>
        <label for="bg_file">Select Image File:</label>
        <input type="file" name="bg_file" id="bg_file" required accept="image/*">
        <button type="submit" name="upload_bg" class="btn btn-primary">Upload Image</button>
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>URL</th><th>Active</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($bg_images as $img): ?>
            <tr>
                <form method="POST" class="inline">
                    <td><?= $img['image_id'] ?></td>
                    <td><input type="text" name="image_name" value="<?= htmlspecialchars($img['image_name']) ?>" required></td>
                    <td><a href="<?= htmlspecialchars($img['image_url']) ?>" target="_blank">View</a></td>
                    <td><input type="checkbox" name="is_active" <?= $img['is_active'] ? 'checked' : '' ?>></td>
                    <td>
                        <input type="hidden" name="image_id" value="<?= $img['image_id'] ?>">
                        <button type="submit" name="edit_bg" class="btn btn-primary">Save</button>
                        <button type="submit" name="delete_bg" class="btn btn-danger" onclick="return confirm('Delete this image?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Game Statistics -->
<div class="section">
    <h2>Game Statistics</h2>
    <p>Total Games Played: <?= $total_games ?: 0 ?></p>
    <p>Average Time to Solve (seconds): <?= $avg_time ? number_format($avg_time, 2) : 'N/A' ?></p>
    <p>Most Popular Puzzle Size: <?= $popular_puzzle ? htmlspecialchars($popular_puzzle['puzzle_size']) : 'N/A' ?></p>
</div>

<p><a href="logout.php">Logout</a></p>
</div>

</body>
</html>
