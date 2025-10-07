<?php
include '../db.php';
header('Content-Type: application/json');

$q = $conn->query("
  SELECT al.*, s.name, s.class
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  ORDER BY al.timestamp DESC
  LIMIT 10
");

$data = [];
while($r = $q->fetch_assoc()){
  $data[] = $r;
}
echo json_encode($data);
?>
