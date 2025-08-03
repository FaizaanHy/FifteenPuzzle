<?php
$dir = "uploads/backgrounds/";
$images = array_filter(scandir($dir), function($file) {
    return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
});
echo json_encode(array_values($images));
