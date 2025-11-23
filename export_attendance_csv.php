<?php
// export_attendance_csv.php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// ===== Nama File Dinamis =====
$hari = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
$filename = 'ABSENSI_' . date('d-') . $hari[date('D')] . '-' . date('m-Y') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// ===== Input Filter =====
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$name = trim($_GET['name'] ?? '');
$class = trim($_GET['class'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// ===== Build WHERE safely =====
$whereParts = [];
$types = '';
$params = [];

// validasi tanggal
function valid_date($d)
{
  return DateTime::createFromFormat('Y-m-d', $d) && DateTime::createFromFormat('Y-m-d', $d)->format('Y-m-d') === $d;
}

if (valid_date($from) && valid_date($to)) {
  $whereParts[] = "DATE(al.timestamp) BETWEEN ? AND ?";
  $types .= 'ss';
  $params[] = $from;
  $params[] = $to;
}

$filters = [
  ['s.name', $name],
  ['s.class', $class],
];
foreach ($filters as [$col, $val]) {
  if ($val !== '') {
    $whereParts[] = "$col LIKE ?";
    $types .= 's';
    $params[] = "%$val%";
  }
}

if ($statusFilter !== '') {
  $whereParts[] = "al.schedule_status = ?";
  $types .= 's';
  $params[] = $statusFilter;
}

$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// ===== Query Data =====
$sql = "
  SELECT 
    al.timestamp, 
    al.schedule_status, 
    s.name AS student_name, 
    s.class AS student_class, 
    al.card_id, 
    al.device_id
  FROM attendance_log AS al
  LEFT JOIN students AS s ON s.id = al.student_id
  $whereSql
  ORDER BY s.class ASC, al.timestamp DESC
";

$stmt = $conn->prepare($sql);
if ($types !== '') {
  $bind = [$types];
  foreach ($params as &$p)
    $bind[] = &$p;
  call_user_func_array([$stmt, 'bind_param'], $bind);
}
$stmt->execute();
$res = $stmt->get_result();

// ===== Output CSV =====
$output = fopen('php://output', 'w');
fputcsv($output, ['Tanggal/Waktu', 'Nama', 'Kelas', 'Card ID', 'Device', 'Status Jadwal']);

while ($row = $res->fetch_assoc()) {
  $timestamp = $row['timestamp'];
  $status = trim($row['schedule_status'] ?? 'Tidak Diketahui');

  fputcsv($output, [
    $timestamp,
    $row['student_name'] ?: '-',
    $row['student_class'] ?: '-',
    $row['card_id'] ?: '-',
    $row['device_id'] ?: '-',
    $status
  ]);
}

fclose($output);
exit;
?>