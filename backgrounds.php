<?php
require 'db_connect.php';

$stmt = $pdo->prepare("SELECT image_id, image_name, image_url FROM background_images2 WHERE is_active = TRUE ORDER BY image_name");
$stmt->execute();
$backgrounds = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($backgrounds);
?>
