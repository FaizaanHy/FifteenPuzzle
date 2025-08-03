<?php
session_start();
require 'db_connect.php';

// Admin check as above
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

// Aggregate stats example
$stmt = $pdo->query("
    SELECT 
        COUNT(*) AS total_games,
        AVG(time_taken_seconds) AS avg_time,
        AVG(moves_count) AS avg_moves
    FROM game_stats2
");
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8" /><title>Game Statistics</title></head>
<body>
    <div class="container">
<h1>Game Statistics</h1>

<p>Total games played: <?= intval($stats['total_games']) ?></p>
<p>Average time to solve (seconds): <?= round($stats['avg_time'], 2) ?></p>
<p>Average moves: <?= round($stats['avg_moves'], 2) ?></p>

<!-- TODO: Add more detailed stats & leaderboards -->

<p><a href="admin_dashboard.php" class="back-link">Back to Dashboard</a></p>
    </div>
</body>
</html>
