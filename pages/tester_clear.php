
<?php
require_once '../db.php';
header('Content-Type: application/json');

// cek reg mode
$mode = $conn->query("SELECT reg_mode FROM settings WHERE id = 1")->fetch_assoc();
if (intval($mode['reg_mode']) === 1) {
  echo json_encode(['success' => false, 'error' => 'Mode registrasi aktif, tidak bisa hapus data.']);
  exit;
}

// hapus semua data absensi
if ($conn->query("DELETE FROM attendance_log")) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>
