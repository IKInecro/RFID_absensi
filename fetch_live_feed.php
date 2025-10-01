<?php
include 'db.php';
header('Content-Type: application/json');

$q = $conn->query("
  SELECT al.timestamp, al.card_id, al.device_id, al.schedule_status, s.name, s.class, s.profile_pic
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  ORDER BY al.timestamp DESC
  LIMIT 10
");

$out = [];
while($r = $q->fetch_assoc()){
  // ensure profile_pic fallback
  $r['profile_pic'] = $r['profile_pic'] ? $r['profile_pic'] : null;
  $out[] = $r;
}
echo json_encode($out);
