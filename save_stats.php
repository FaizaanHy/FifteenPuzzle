<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user_id = $_SESSION['user_id'];
$puzzle_size = $data['puzzle_size'] ?? '4x4';
$time_taken = (int)($data['time_taken_seconds'] ?? 0);
$moves_count = (int)($data['moves_count'] ?? 0);
$background_image_id = isset($data['background_image_id']) ? (int)$data['background_image_id'] : null;
$win_status = isset($data['win_status']) ? (bool)$data['win_status'] : false;

$stmt = $pdo->prepare("INSERT INTO game_stats2 (user_id, puzzle_size, time_taken_seconds, moves_count, background_image_id, win_status, game_date)
VALUES (?, ?, ?, ?, ?, ?, NOW())");

$stmt->execute([$user_id, $puzzle_size, $time_taken, $moves_count, $background_image_id, $win_status]);

echo json_encode(['success' => true]);
?>
