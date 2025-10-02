<?php
include '../db.php';
header('Content-Type: application/json');

// ambil 10 absensi terakhir
$res = $conn->query("SELECT a.*, s.name, s.class, s.profile_pic 
                     FROM attendance_log a 
                     LEFT JOIN students s ON a.student_id=s.id 
                     ORDER BY a.timestamp DESC LIMIT 10");

$data = [];
while($row = $res->fetch_assoc()){
    if(!$row['student_id']){ // kartu tidak dikenal
        $data[] = [
            "id" => $row['id'],
            "name" => "Kartu Tidak Dikenal",
            "class" => "-",
            "card_id" => $row['card_id'],
            "status" => "Unknown",
            "timestamp" => $row['timestamp'],
            "profile_pic" => "default.png"
        ];
    } else {
        $data[] = [
            "id" => $row['id'],
            "name" => $row['name'],
            "class" => $row['class'],
            "card_id" => $row['card_id'],
            "status" => $row['status'],
            "timestamp" => $row['timestamp'],
            "profile_pic" => $row['profile_pic'] ?: 'default.png'
        ];
    }
}


echo json_encode($data);
