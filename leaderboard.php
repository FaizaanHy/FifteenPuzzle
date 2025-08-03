<?php
session_start();
require 'db_connect.php';

$sql = "
  SELECT u.username, g.puzzle_size, g.moves_count, g.time_taken_seconds, g.game_date
FROM game_stats2 g
JOIN users2 u ON g.user_id = u.user_id
WHERE g.win_status = TRUE
ORDER BY g.moves_count ASC, g.time_taken_seconds ASC
LIMIT 10
";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Leaderboard - Fifteen Puzzle</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
      background-color: #f0f8ff;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    table {
      width: 80%;
      margin: auto;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ccc;
    }
    th {
      background-color: #007acc;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .back-link {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>

<h1>🏆 Top 10 Leaderboard</h1>

<table>
  <tr>
    <th>Username</th>
    <th>Puzzle Size</th>
    <th>Moves</th>
    <th>Time (seconds)</th>
    <th>Game Date</th>
  </tr>

  <?php if (count($results) > 0): ?>
    <?php foreach ($results as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['puzzle_size']) ?></td>
        <td><?= $row['moves_count'] ?></td>
        <td><?= $row['time_taken_seconds'] ?></td>
        <td><?= date("Y-m-d H:i", strtotime($row['game_date'])) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="5">No results yet!</td></tr>
  <?php endif; ?>
</table>

<div class="back-link">
  <p><a href="fifteen.html">⬅️ Back to Game</a></p>
</div>

</body>
</html>
