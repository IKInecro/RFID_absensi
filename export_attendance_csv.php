<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// === FORMAT NAMA FILE ===
$hari = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
$filename = 'ABSENSI_' . date('d-') . $hari[date('D')] . '-' . date('m-Y') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// === AMBIL FILTER ===
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$name = $_GET['name'] ?? '';
$class = $_GET['class'] ?? '';
$card = $_GET['card'] ?? '';
$device = $_GET['device'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = [];
if ($from && $to) $where[] = "DATE(al.timestamp) BETWEEN '$from' AND '$to'";
if ($name) $where[] = "s.name LIKE '%$name%'";
if ($class) $where[] = "s.class LIKE '%$class%'";
if ($card) $where[] = "al.card_id LIKE '%$card%'";
if ($device) $where[] = "al.device_id LIKE '%$device%'";
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// === AMBIL DATA ABSENSI ===
$query = "
  SELECT 
    al.timestamp, 
    al.schedule_status, 
    s.name AS student_name, 
    s.class AS student_class, 
    al.card_id, 
    al.device_id
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  $whereSql
  ORDER BY al.timestamp DESC
";

$res = $conn->query($query);
if (!$res) {
  die('Query Error: ' . $conn->error);
}

// === AMBIL JADWAL HARI INI ===
$day = date('D');
$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();
$time_in = $schedule['time_in'] ?? null;
$time_out = $schedule['time_out'] ?? null;
$grace = $schedule['grace_period'] ?? 0;
$grace_limit = ($time_in) ? date('H:i:s', strtotime($time_in . " +$grace minutes")) : null;
$isHoliday = $schedule['is_holiday'] ?? 0;

// === OUTPUT CSV ===
$output = fopen('php://output', 'w');
fputcsv($output, ['Tanggal/Waktu', 'Nama', 'Kelas', 'Card ID', 'Device', 'Status Jadwal']);

while ($row = $res->fetch_assoc()) {
  $timestamp = $row['timestamp'];
  $jam = date('H:i:s', strtotime($timestamp));
  $status = trim($row['schedule_status'] ?? '');

  // Kalkulasi ulang status kalau kosong
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

  // Filter kalau user pilih status tertentu
  if ($statusFilter && $status !== $statusFilter) continue;

  fputcsv($output, [
    $timestamp,
    $row['student_name'],
    $row['student_class'],
    $row['card_id'],
    $row['device_id'],
    $status
  ]);
}

fclose($output);
exit;
?>
