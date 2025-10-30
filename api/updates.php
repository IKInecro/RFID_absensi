<?php
// NEW FILE: api/updates.php
// Long-poll endpoint. Query params:
//  - mode = "live" | "test" | "students"
//  - last_id (for live/students) integer
//  - last_ts (for test) ISO timestamp string
// This script will block up to ~25 seconds waiting for new data, then return when new item appears.
// Place at api/updates.php

include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
ignore_user_abort(true);
set_time_limit(30);

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'live';
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$last_ts = isset($_GET['last_ts']) ? trim($_GET['last_ts']) : '';

$timeout = 25; // seconds
$interval = 1; // poll every 1s
$start = time();

function send_json($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

while (time() - $start < $timeout) {
    if ($mode === 'live') {
        // check newest attendance_log id
        $r = $conn->query("SELECT id, timestamp, card_id, device_id, schedule_status FROM attendance_log ORDER BY id DESC LIMIT 1");
        if ($r && $r->num_rows) {
            $row = $r->fetch_assoc();
            $newId = intval($row['id']);
            if ($newId > $last_id) {
                send_json(['new'=>true,'mode'=>'live','item'=>$row]);
            }
        }
    } elseif ($mode === 'test') {
        $file = __DIR__ . '/../test_data.json';
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : [];
            if (is_array($data) && count($data)>0) {
                $first = $data[0];
                $ts = isset($first['timestamp']) ? $first['timestamp'] : '';
                if ($ts && $ts !== $last_ts) {
                    send_json(['new'=>true,'mode'=>'test','item'=>$first]);
                }
            }
        }
    } elseif ($mode === 'students') {
        // check max id in students
        $r = $conn->query("SELECT id, name, class, card_id, profile_pic FROM students ORDER BY id DESC LIMIT 1");
        if ($r && $r->num_rows) {
            $row = $r->fetch_assoc();
            $newId = intval($row['id']);
            if ($newId > $last_id) {
                send_json(['new'=>true,'mode'=>'students','item'=>$row]);
            }
        }
    } else {
        send_json(['new'=>false]);
    }

    // sleep a bit then continue
    sleep($interval);
}

// timeout, no new data
send_json(['new'=>false]);
