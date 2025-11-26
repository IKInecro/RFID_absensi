<?php
header('Content-Type: application/json');
include 'db.php';

// ---- Terima JSON dari ESP8266 ----
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['uid']) || !isset($data['device_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$uid = $conn->real_escape_string($data['uid']);
$device_id = $conn->real_escape_string($data['device_id']);

// ---- Cek apakah kartu terdaftar ----
$q = $conn->query("SELECT id FROM students WHERE card_id='$uid' AND status='active' LIMIT 1");
if ($q->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Card not registered"]);
    exit;
}
$student = $q->fetch_assoc();
$student_id = (int) $student['id'];

// ---- Cek Jadwal Hari Ini ----
$day = date('D'); // Mon, Tue, ...
$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();

$schedule_status = 'On Time';
if ($schedule) {
    if ($schedule['is_holiday']) {
        $schedule_status = 'Holiday';
    } else {
        $now = strtotime(date('H:i:s'));
        $timeIn = strtotime($schedule['time_in']);
        $grace = (int) $schedule['grace_period'] * 60; // detik
        if ($now > $timeIn + $grace) {
            $schedule_status = 'Late';
        }
    }
} else {
    $schedule_status = 'Holiday';
}

// ---- Simpan ke attendance_log ----
// Catatan: kita tetap isi kolom status lama (ontime/late/absent) sebagai 'ontime' default,
// dan kolom schedule_status untuk hasil jadwal (On Time/Late/Holiday)
$conn->query("
    INSERT INTO attendance_log
    (student_id, card_id, device_id, status, location, schedule_status, timestamp)
    VALUES
    ($student_id, '$uid', '$device_id', 'ontime', '', '$schedule_status', NOW())
");

if ($conn->affected_rows > 0) {
    echo json_encode([
        "success" => true,
        "student_id" => $student_id,
        "schedule_status" => $schedule_status
    ]);
} else {
    echo json_encode(["success" => false, "message" => "DB insert failed"]);
}
