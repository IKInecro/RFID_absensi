<?php
// api/attendance.php — Enhanced version with proper test mode integration
// Handles RFID card taps from IoT device (mrc.ino)

include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

// Read input (support both JSON and POST)
$input = [];
$raw = @file_get_contents('php://input');
$json = @json_decode($raw, true);

if (is_array($json)) {
    $input = $json;
} else {
    $input = $_POST;
}

$device_id = trim($input['device_id'] ?? '');
$uid = trim($input['uid'] ?? '');
$timestamp = trim($input['timestamp'] ?? date('Y-m-d H:i:s'));

if ($device_id === '' || $uid === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing device_id or uid']);
    exit;
}

// Fetch settings
$reg_mode = 0;
$test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
    $s = $set_q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
}
if ($reg_mode == 1)
    $test_mode = 0;

// Lookup student
$stmt = $conn->prepare("SELECT id, name, class, profile_pic FROM students WHERE card_id = ? LIMIT 1");
$stmt->bind_param('s', $uid);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// === REGISTER MODE ===
if ($reg_mode == 1) {
    if (!$student) {
        $ins = $conn->prepare("INSERT INTO students (card_id, name, class) VALUES (?, 'Baru', 'Belum Ditentukan')");
        $ins->bind_param('s', $uid);
        $ins->execute();
        $ins->close();

        $stmt2 = $conn->prepare("SELECT id, name, class, profile_pic FROM students WHERE card_id = ? LIMIT 1");
        $stmt2->bind_param('s', $uid);
        $stmt2->execute();
        $student = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    }

    echo json_encode([
        'success' => true,
        'mode' => 'register',
        'message' => 'Card registered. No attendance recorded.',
        'name' => $student['name'],
        'class' => $student['class'],
        'device_id' => $device_id
    ]);
    exit;
}

// === TEST MODE ===
if ($test_mode == 1) {
    // In Test Mode, we accept ANY card, even if not registered
    // But if registered, we show the name

    // Determine schedule status (just for display)
    $day = date('D', strtotime($timestamp));
    $now = date('H:i:s', strtotime($timestamp));
    $status = 'Tidak Diketahui';
    $stmt = $conn->prepare("SELECT time_in, time_out, grace_period, is_holiday FROM schedules WHERE day = ? LIMIT 1");
    $stmt->bind_param('s', $day);
    $stmt->execute();
    $sched = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($sched) {
        if (intval($sched['is_holiday']) === 1)
            $status = 'Libur';
        else {
            $time_in = $sched['time_in'];
            $time_out = $sched['time_out'];
            $grace_limit = date('H:i:s', strtotime($time_in . ' +' . intval($sched['grace_period']) . ' minutes'));
            if ($now <= $time_in)
                $status = 'On Time';
            elseif ($now <= $grace_limit)
                $status = 'Toleransi';
            elseif ($now > $grace_limit && $now <= $time_out)
                $status = 'Late';
            else
                $status = 'Out of Schedule';
        }
    }

    // Save to test_data.json (for Tester Mode page)
    $file = __DIR__ . '/../test_data.json';
    $data = [];
    if (file_exists($file)) {
        $raw2 = @file_get_contents($file);
        $data = $raw2 ? json_decode($raw2, true) : [];
        if (!is_array($data))
            $data = [];
    }

    // Create entry
    $entry = [
        'id' => time(),
        'timestamp' => $timestamp, // Use the timestamp from device or current time
        'card_id' => $uid,
        'device_id' => $device_id,
        'schedule_status' => $status,
        'student_name' => $student ? $student['name'] : 'Unknown Card',
        'student_class' => $student ? $student['class'] : 'N/A',
        'profile_pic' => $student ? $student['profile_pic'] : null,
        'status' => $status
    ];

    // Add new entry at the beginning
    array_unshift($data, $entry); // Add to top

    // Keep only last 50 entries
    $data = array_slice($data, 0, 50);

    @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

    echo json_encode([
        'success' => true,
        'mode' => 'test',
        'message' => 'Test data recorded',
        'status' => $status,
        'name' => $student ? $student['name'] : 'Unknown Card',
        'class' => $student ? $student['class'] : 'N/A'
    ]);
    exit;
}

// === NORMAL MODE ===
if (!$student) {
    echo json_encode(['success' => false, 'status' => 'unknown', 'message' => 'Card not registered']);
    exit;
}

// Compute schedule status
$day = date('D', strtotime($timestamp));
$now = date('H:i:s', strtotime($timestamp));
$status = 'Tidak Diketahui';
$stmt = $conn->prepare("SELECT time_in, time_out, grace_period, is_holiday FROM schedules WHERE day = ? LIMIT 1");
$stmt->bind_param('s', $day);
$stmt->execute();
$sched = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($sched) {
    if (intval($sched['is_holiday']) === 1)
        $status = 'Libur';
    else {
        $time_in = $sched['time_in'];
        $time_out = $sched['time_out'];
        $grace_limit = date('H:i:s', strtotime($time_in . ' +' . intval($sched['grace_period']) . ' minutes'));
        if ($now <= $time_in)
            $status = 'On Time';
        elseif ($now <= $grace_limit)
            $status = 'Toleransi';
        elseif ($now > $grace_limit && $now <= $time_out)
            $status = 'Late';
        else
            $status = 'Out of Schedule';
    }
}

// Check existing attendance today (prevent duplicates)
$today = date('Y-m-d', strtotime($timestamp));
$check = $conn->prepare("SELECT id FROM attendance_log WHERE student_id = ? AND DATE(timestamp) = ?");
$check->bind_param('is', $student['id'], $today);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if (!$exists) {
    $ins = $conn->prepare("INSERT INTO attendance_log (student_id, card_id, device_id, schedule_status, timestamp) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param('issss', $student['id'], $uid, $device_id, $status, $timestamp);
    $ins->execute();
    $ins->close();
}

// Update live_feed.json for dashboard
$feed = __DIR__ . '/../live_feed.json';
$feedData = [];
if (file_exists($feed)) {
    $r = @file_get_contents($feed);
    $feedData = $r ? json_decode($r, true) : [];
    if (!is_array($feedData))
        $feedData = [];
}

$newEntry = [
    'timestamp' => $timestamp,
    'card_id' => $uid,
    'device_id' => $device_id,
    'schedule_status' => $status,
    'name' => $student['name'],
    'class' => $student['class'],
    'profile_pic' => $student['profile_pic'] ?? null
];

array_unshift($feedData, $newEntry);
$feedData = array_slice($feedData, 0, 300);
@file_put_contents($feed, json_encode($feedData, JSON_UNESCAPED_UNICODE), LOCK_EX);

echo json_encode([
    'success' => true,
    'mode' => 'attendance',
    'recorded' => !$exists,
    'status' => $status,
    'name' => $student['name'],
    'class' => $student['class'],
    'device_id' => $device_id
]);