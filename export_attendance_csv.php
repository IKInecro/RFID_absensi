<?php
// export_attendance_csv.php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// ===== Nama File Dinamis =====
$hari = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
$filename = 'ABSENSI_' . date('d-') . $hari[date('D')] . '-' . date('m-Y') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// ===== Input Filter =====
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$name   = trim($_GET['name'] ?? '');
$class  = trim($_GET['class'] ?? '');
$card   = trim($_GET['card'] ?? '');
$device = trim($_GET['device'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// ===== Build WHERE safely =====
$whereParts = [];
$types = '';
$params = [];

// validasi tanggal
function valid_date($d) {
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
  ['al.card_id', $card],
  ['al.device_id', $device],
];
foreach ($filters as [$col, $val]) {
  if ($val !== '') {
    $whereParts[] = "$col LIKE ?";
    $types .= 's';
    $params[] = "%$val%";
  }
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
  ORDER BY al.timestamp DESC
";

$stmt = $conn->prepare($sql);
if ($types !== '') {
  $bind = [$types];
  foreach ($params as &$p) $bind[] = &$p;
  call_user_func_array([$stmt, 'bind_param'], $bind);
}
$stmt->execute();
$res = $stmt->get_result();

// ===== Ambil Jadwal Hari Ini (buat status fallback) =====
$day = date('D');
$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();
$time_in = $schedule['time_in'] ?? null;
$time_out = $schedule['time_out'] ?? null;
$grace = $schedule['grace_period'] ?? 0;
$grace_limit = $time_in ? date('H:i:s', strtotime($time_in . " +$grace minutes")) : null;
$isHoliday = $schedule['is_holiday'] ?? 0;

// ===== Output CSV =====
$output = fopen('php://output', 'w');
fputcsv($output, ['Tanggal/Waktu', 'Nama', 'Kelas', 'Card ID', 'Device', 'Status Jadwal']);

while ($row = $res->fetch_assoc()) {
  $timestamp = $row['timestamp'];
  $jam = date('H:i:s', strtotime($timestamp));
  $status = trim($row['schedule_status'] ?? '');

  // fallback status kalkulasi ulang
  if ($status === '') {
    if ($isHoliday) {
      $status = 'Libur';
    } elseif ($time_in && $time_out) {
      if ($jam <= $time_in) $status = 'On Time';
      elseif ($jam <= $grace_limit) $status = 'Toleransi';
      elseif ($jam > $grace_limit && $jam <= $time_out) $status = 'Late';
      else $status = 'Tidak Diketahui';
    } else {
      $status = 'Tidak Diketahui';
    }
  }

  // jika ada filter status aktif
  if ($statusFilter && $status !== $statusFilter) continue;

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
