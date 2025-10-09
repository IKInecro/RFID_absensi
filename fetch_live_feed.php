<?php
// fetch_live_feed.php
// Full replace: fixed SQL alias, consistent JSON structure for live polling
include __DIR__ . '/db.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil last_id dari request (default 0)
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Query untuk ambil data absensi terbaru setelah last_id
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
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
$new_last_id = $last_id;

while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
    if ((int)$row['id'] > $new_last_id) {
        $new_last_id = (int)$row['id'];
    }
}

$stmt->close();

// Bentuk response JSON
$response = [
    'success' => true,
    'entries' => $entries,
    'last_id' => $new_last_id,
    'count'   => count($entries)
];

// Header JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
