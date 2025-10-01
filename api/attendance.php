<?php
include '../db.php';
header('Content-Type: application/json');

// Baca JSON dari ESP8266
$data = json_decode(file_get_contents("php://input"), true);

$device_id = $conn->real_escape_string($data['device_id'] ?? '');
$uid       = $conn->real_escape_string($data['uid'] ?? '');
$timestamp = date('Y-m-d H:i:s');

// Validasi input
if(empty($uid)){
    echo json_encode(['success'=>false, 'message'=>'UID kosong']);
    exit;
}

// Cek apakah kartu terdaftar
$q = $conn->query("SELECT id, name FROM students WHERE card_id='$uid' AND status='active'");
if($q->num_rows === 0){
    echo json_encode(['success'=>false, 'message'=>'Kartu tidak terdaftar']);
    exit;
}
$student = $q->fetch_assoc();
$student_id = $student['id'];

// Tentukan status absensi (sederhana dulu)
$status = 'On Time';

// Masukkan log absensi
$conn->query("
    INSERT INTO attendance_log (student_id, card_id, device_id, timestamp, schedule_status)
    VALUES ('$student_id','$uid','$device_id','$timestamp','$status')
");

echo json_encode([
    'success'=>true,
    'message'=>'Absensi tercatat',
    'student'=>$student['name'],
    'status'=>$status,
    'time'=>$timestamp
]);
