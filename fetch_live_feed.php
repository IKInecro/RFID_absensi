<?php
include 'db.php';
header('Content-Type: application/json');

// Ambil 10 data terbaru
$q = $conn->query("
    SELECT al.timestamp, s.name, s.class, al.card_id, al.device_id, al.schedule_status
    FROM attendance_log al
    LEFT JOIN students s ON s.id = al.student_id
    ORDER BY al.timestamp DESC
    LIMIT 10
");

$data = [];
while($row = $q->fetch_assoc()){
    $data[] = $row;
}
echo json_encode($data);
