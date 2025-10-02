<?php
include '../db.php';
header('Content-Type: application/json');

// ambil JSON input
$data = json_decode(file_get_contents("php://input"), true);

if(!$data || !isset($data['device_id']) || !isset($data['uid'])){
    http_response_code(400);
    echo json_encode(["error"=>"Invalid data"]);
    exit;
}

$device_id = $conn->real_escape_string($data['device_id']);
$uid = $conn->real_escape_string($data['uid']);

// cek apakah register mode aktif
$reg_mode = $conn->query("SELECT reg_mode FROM settings WHERE id=1")->fetch_assoc()['reg_mode'];

if($reg_mode){
    // kalau mode register aktif → jangan absen, daftarkan UID
    $check = $conn->query("SELECT * FROM students WHERE card_id='$uid'")->fetch_assoc();
    if(!$check){
        $stmt = $conn->prepare("INSERT INTO students (name,class,card_id,status) VALUES ('','',?, 'active')");
        $stmt->bind_param("s",$uid);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["status"=>"success","mode"=>"register","message"=>"UID terdaftar: $uid"]);
    } else {
        echo json_encode(["status"=>"info","mode"=>"register","message"=>"UID sudah ada"]);
    }
    exit;
}


// cek siswa
// cek siswa
$student = $conn->query("SELECT * FROM students WHERE card_id='$uid'")->fetch_assoc();

if(!$student){
    // log kartu tidak dikenal
    $stmt = $conn->prepare("INSERT INTO attendance_log (student_id,card_id,device_id,status,location) VALUES (NULL,?,?, 'Unknown','Gate')");
    $stmt->bind_param("ss",$uid,$device_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status"=>"error",
        "mode"=>"attendance",
        "message"=>"Kartu Tidak Dikenal",
        "uid"=>$uid
    ]);
    exit;
}


// tentukan hari ini
$day = date('D'); // Mon/Tue/...
$now = date('H:i:s');

// ambil jadwal hari ini
$schedule = $conn->query("SELECT * FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc();

$status = 'On Time';
if($schedule){
    if($schedule['is_holiday']){
        $status = 'Holiday';
    } else {
        $time_in = strtotime($schedule['time_in']);
        $grace = $schedule['grace_period'] * 60;
        $check_in = strtotime($now);

        if($check_in > ($time_in + $grace)){
            $status = 'Late';
        } else {
            $status = 'On Time';
        }
    }
}

// insert log
$stmt = $conn->prepare("INSERT INTO attendance_log (student_id,card_id,device_id,status,location) VALUES (?,?,?,?,?)");
$loc = "Gate"; // bisa diubah sesuai device
$stmt->bind_param("issss",$student['id'],$uid,$device_id,$status,$loc);
$stmt->execute();
$stmt->close();

echo json_encode([
    "status"=>"success",
    "student"=>$student['name'],
    "class"=>$student['class'],
    "absen_status"=>$status,
    "time"=>$now
]);
