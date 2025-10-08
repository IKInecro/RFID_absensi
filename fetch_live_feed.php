<?php
// fetch_live_feed.php
// Full replace: returns JSON array of recent attendance_log entries (joined with students).
// Place at repo root: fetch_live_feed.php (replace existing).
// This endpoint is lightweight and returns last 20 taps by default.

include 'db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

// limit param optional
$limit = isset($_GET['limit']) ? max(1,intval($_GET['limit'])) : 20;

$sql = "
  SELECT al.id, al.timestamp, al.card_id, al.device_id, al.schedule_status,
         s.id AS student_id, s.name, s.class, s.profile_pic
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  ORDER BY al.timestamp DESC
  LIMIT ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['success'=>false,'error'=>'Prepare failed','db_error'=>$conn->error]);
  exit;
}
$stmt->bind_param('i', $limit);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  // normalize fields
  $rows[] = [
    'id' => (int)$r['id'],
    'timestamp' => $r['timestamp'],
    'card_id' => $r['card_id'],
    'device_id' => $r['device_id'],
    'schedule_status' => $r['schedule_status'],
    'student_id' => $r['student_id'] !== null ? (int)$r['student_id'] : null,
    'name' => $r['name'] ?? null,
    'class' => $r['class'] ?? null,
    'profile_pic' => $r['profile_pic'] ?? null
  ];
}
$stmt->close();

echo json_encode(['success'=>true,'records'=>$rows], JSON_UNESCAPED_UNICODE);
