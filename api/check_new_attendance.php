<?php
include '../db.php';

$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$res = $conn->query("SELECT * FROM attendance_log WHERE id > $last_id ORDER BY id DESC");

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode([
  'new' => count($rows) > 0,
  'rows' => $rows
]);
?>
