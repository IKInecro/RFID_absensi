<?php
// pages/tester_clear.php
header('Content-Type: application/json');

$file = __DIR__ . '/../test_data.json';
if (file_exists($file)) {
    file_put_contents($file, '[]');
}

echo json_encode(['success' => true]);