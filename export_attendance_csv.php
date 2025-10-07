<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// Format nama file: ABSENSI_(tanggal-hari-bulan-tahun).csv
$hari = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
$filename = 'ABSENSI_' . date('d-') . $hari[date('D')] . '-' . date('m-Y') . '.csv';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// ambil filter
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

// ambil data
$res = $conn->query("
  SELECT al.timestamp, al.schedule_status, s.name, s.class, al.card_id, al.device_id
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  $whereSql
  ORDER BY al.timestamp DESC
");

// ambil jadwal hari ini
$day = date('D');
$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();
$time_in = $schedule['time_in'] ?? null;
$time_out = $schedule['time_out'] ?? null;
$grace = $schedule['grace_period'] ?? 0;
$grace_limit = $time_in ? date('H:i:s', strtotime($time_in . " +$grace minutes")) : null;
$isHoliday = $schedule['is_holiday'] ?? 0;

$output = fopen("php://output", "w");
fputcsv($output, ['Tanggal/Waktu', 'Nama', 'Kelas', 'Card ID', 'Device', 'Status Jadwal']);

while ($row = $res->fetch_assoc()) {
  $timestamp = $row['timestamp'];
  $jam = date('H:i:s', strtotime($timestamp));
  $status = trim($row['schedule_status']);

  // kalkulasi ulang status jika kosong/null
  if ($status === '' || $status === null) {
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

  // filter jika user pilih status tertentu
  if ($statusFilter && $status !== $statusFilter) continue;

  fputcsv($output, [
    $timestamp,
    $row['name'],
    $row['class'],
    $row['card_id'],
    $row['device_id'],
    $status
  ]);
}

fclose($output);
