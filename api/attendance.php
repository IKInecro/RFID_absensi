<?php
include '../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$device_id = $input['device_id'] ?? '';
$uid = $input['uid'] ?? '';

if (!$uid || !$device_id) {
  echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
  exit;
}

$reg_mode = $conn->query("SELECT reg_mode FROM settings WHERE id=1")->fetch_assoc()['reg_mode'];
$test_mode = $reg_mode ? 0 : 1;

// cari siswa
$student = $conn->query("SELECT * FROM students WHERE card_id='$uid' LIMIT 1")->fetch_assoc();
if (!$student) {
  echo json_encode(['success' => false, 'status' => 'unknown']);
  exit;
}

// ambil jadwal hari ini
$day = date('D');
$now = date('H:i:s');
$status = 'Tidak Diketahui';

$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();
if ($schedule) {
  if ($schedule['is_holiday']) {
    $status = 'Libur';
  } else {
    $time_in = $schedule['time_in'];
    $time_out = $schedule['time_out'];
    $grace_limit = date('H:i:s', strtotime($time_in . ' +' . $schedule['grace_period'] . ' minutes'));

    if ($now <= $time_in) {
      $status = 'On Time';
    } elseif ($now <= $grace_limit) {
      $status = 'Toleransi';
    } elseif ($now > $grace_limit && $now <= $time_out) {
      $status = 'Late';
    } else {
      $status = 'Tidak Diketahui';
    }
  }
}

// simpan jika bukan mode registrasi
if (!$reg_mode) {
  $date = date('Y-m-d');
  $uidCheck = $conn->query("SELECT * FROM attendance_log WHERE student_id='{$student['id']}' AND DATE(timestamp)='$date'");
  if ($uidCheck->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO attendance_log (student_id, card_id, device_id, schedule_status) VALUES (?,?,?,?)");
    $stmt->bind_param('isss', $student['id'], $uid, $device_id, $status);
    $stmt->execute();
  }
}

echo json_encode([
  'success' => true,
  'status' => $status,
  'name' => $student['name'],
  'class' => $student['class'],
  'test_mode' => !$reg_mode ? 1 : 0
]);
