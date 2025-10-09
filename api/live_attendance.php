<?php
// api/live_attendance.php
// Full replace: unified live attendance API endpoint
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// mode: live (fetch after last_id) or latest (recent 10)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'latest';
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$entries = [];
$new_last_id = $last_id;

// Query builder
if ($mode === 'live' && $last_id > 0) {
    $sql = "SELECT 
                al.id,
                al.student_id,
                al.card_id,
                al.timestamp,
                s.name AS student_name,
                s.class AS student_class
            FROM attendance_log AS al
            LEFT JOIN students AS s ON s.id = al.student_id
            WHERE al.id > ?
            ORDER BY al.id ASC
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $last_id);
} else {
    // latest mode (default)
    $sql = "SELECT 
                al.id,
                al.student_id,
                al.card_id,
                al.timestamp,
                s.name AS student_name,
                s.class AS student_class
            FROM attendance_log AS al
            LEFT JOIN students AS s ON s.id = al.student_id
            ORDER BY al.id DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
}

// Eksekusi
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $entries[] = $r;
    if ((int)$r['id'] > $new_last_id) {
        $new_last_id = (int)$r['id'];
    }
}
$stmt->close();

// Urutkan naik kalau mode=latest biar tampil rapi (paling lama → paling baru)
if ($mode === 'latest') {
    $entries = array_reverse($entries);
}

// Bentuk JSON response
$response = [
    'success' => true,
    'mode' => $mode,
    'entries' => $entries,
    'last_id' => $new_last_id,
    'count' => count($entries)
];

// Header JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
