<?php
// api/get_test_data.php - Get latest test data for Tester Mode
// Returns array of test tap records

include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$last_ts = isset($_GET['last_ts']) ? trim($_GET['last_ts']) : '';
$limit = isset($_GET['limit']) ? min(50, (int) $_GET['limit']) : 20;

// Read test_data.json
$file = __DIR__ . '/../test_data.json';
$data = [];

if (file_exists($file)) {
    $raw = @file_get_contents($file);
    $data = $raw ? json_decode($raw, true) : [];
    if (!is_array($data))
        $data = [];
}

// Filter new entries if last_ts provided
$newEntries = [];
if ($last_ts) {
    foreach ($data as $entry) {
        if (isset($entry['timestamp']) && $entry['timestamp'] > $last_ts) {
            $newEntries[] = $entry;
        }
    }
} else {
    // Return latest entries
    $newEntries = array_slice($data, 0, $limit);
}

echo json_encode([
    'success' => true,
    'entries' => $newEntries,
    'count' => count($newEntries),
    'total' => count($data),
    'last_ts' => $last_ts,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
