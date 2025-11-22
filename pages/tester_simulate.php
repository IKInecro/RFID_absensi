<?php
// pages/tester_simulate.php
// Helper script to append fake data to test_data.json for simulation
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

try {
    $mode = $_GET['mode'] ?? 'random'; // random | unknown
    $testFile = __DIR__ . '/test_data.json';

    // Ensure file exists
    if (!file_exists($testFile)) {
        file_put_contents($testFile, json_encode([]));
    }

    $currentData = json_decode(file_get_contents($testFile), true) ?? [];
    if (!is_array($currentData))
        $currentData = [];

    $newItem = [];
    $ts = date('H:i:s');

    if ($mode === 'unknown') {
        $newItem = [
            'id' => uniqid(),
            'card_id' => 'UNKNOWN-' . rand(1000, 9999),
            'timestamp' => $ts,
            'name' => null,
            'class' => null,
            'profile_pic' => null,
            'schedule_status' => 'Tidak Diketahui'
        ];
    } else {
        // Fetch a random student
        $q = $conn->query("SELECT * FROM students ORDER BY RAND() LIMIT 1");
        if ($q && $q->num_rows > 0) {
            $s = $q->fetch_assoc();

            // Randomize status
            $statuses = ['On Time', 'Late', 'Toleransi', 'Libur', 'Out of Schedule'];
            $status = $statuses[array_rand($statuses)];

            $newItem = [
                'id' => uniqid(),
                'card_id' => $s['card_id'],
                'timestamp' => $ts,
                'name' => $s['name'],
                'class' => $s['class'],
                'profile_pic' => $s['profile_pic'],
                'schedule_status' => $status
            ];
        } else {
            // Fallback if no students in DB
            $newItem = [
                'id' => uniqid(),
                'card_id' => 'TEST-' . rand(1000, 9999),
                'timestamp' => $ts,
                'name' => 'Test Student ' . rand(1, 100),
                'class' => 'XII-RPL',
                'profile_pic' => null,
                'schedule_status' => 'On Time'
            ];
        }
    }

    // Prepend new item
    array_unshift($currentData, $newItem);

    // Keep only last 50 items
    if (count($currentData) > 50) {
        $currentData = array_slice($currentData, 0, 50);
    }

    // Save
    if (file_put_contents($testFile, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        echo json_encode(['success' => true, 'item' => $newItem]);
    } else {
        throw new Exception('Gagal menyimpan data simulasi.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>