<?php
// api/get_feed.php - Get latest attendance feed entries
// Returns array of recent attendance records for initial load and polling

include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;
$limit = isset($_GET['limit']) ? min(50, (int) $_GET['limit']) : 10;

// Fetch attendance records with student info
$stmt = $conn->prepare("SELECT al.id, al.timestamp, al.card_id, al.device_id, al.schedule_status,
                               s.name AS student_name, s.class AS student_class, s.profile_pic
                        FROM attendance_log al
                        LEFT JOIN students s ON al.student_id = s.id
                        WHERE al.id > ?
                        ORDER BY al.id DESC
                        LIMIT ?");
$stmt->bind_param('ii', $last_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}
$stmt->close();

echo json_encode([
    'success' => true,
    'entries' => $entries,
    'count' => count($entries),
    'last_id' => $last_id,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
