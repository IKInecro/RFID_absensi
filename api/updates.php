<?php
// api/updates.php - Enhanced long-poll endpoint for real-time updates
// Supports: mode=live|test|students
// Parameters: last_id (for live/students), last_ts (for test)

include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Accel-Buffering: no'); // Disable nginx buffering
ignore_user_abort(true);
set_time_limit(35);

$mode = isset($_GET['mode']) ? trim($_GET['mode']) : 'live';
$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;
$last_ts = isset($_GET['last_ts']) ? trim($_GET['last_ts']) : '';

$timeout = 30; // seconds to wait for new data
$interval = 1; // poll interval in seconds
$start = time();

function send_json($arr)
{
    echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    flush();
    exit;
}

// Long-poll loop
while (time() - $start < $timeout) {
    $found = false;

    if ($mode === 'live') {
        // Check for new attendance records
        $stmt = $conn->prepare("SELECT al.id, al.timestamp, al.card_id, al.device_id, al.schedule_status, 
                                       s.name AS student_name, s.class AS student_class, s.profile_pic
                                FROM attendance_log al
                                LEFT JOIN students s ON al.student_id = s.id
                                WHERE al.id > ?
                                ORDER BY al.id DESC
                                LIMIT 10");
        $stmt->bind_param('i', $last_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $entries = [];
            while ($row = $result->fetch_assoc()) {
                $entries[] = $row;
            }
            $stmt->close();
            send_json([
                'new' => true,
                'mode' => 'live',
                'entries' => $entries,
                'count' => count($entries),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        $stmt->close();

    } elseif ($mode === 'test') {
        // Check test_data.json for updates
        $file = __DIR__ . '/../test_data.json';
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : [];

            if (is_array($data) && count($data) > 0) {
                $newEntries = [];
                foreach ($data as $entry) {
                    $ts = isset($entry['timestamp']) ? $entry['timestamp'] : '';
                    if ($ts && $ts > $last_ts) {
                        $newEntries[] = $entry;
                    }
                }

                if (count($newEntries) > 0) {
                    send_json([
                        'new' => true,
                        'mode' => 'test',
                        'entries' => $newEntries,
                        'count' => count($newEntries),
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

    } elseif ($mode === 'students') {
        // Check for new student registrations
        $stmt = $conn->prepare("SELECT id, name, class, card_id, profile_pic, created_at, status
                                FROM students
                                WHERE id > ?
                                ORDER BY id DESC
                                LIMIT 1");
        $stmt->bind_param('i', $last_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            send_json([
                'new' => true,
                'mode' => 'students',
                'item' => $row,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        $stmt->close();

    } else {
        // Invalid mode
        send_json([
            'new' => false,
            'error' => 'Invalid mode. Use: live, test, or students',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // Sleep before next check
    sleep($interval);

    // Check if connection is still alive
    if (connection_aborted()) {
        exit;
    }
}

// Timeout reached, no new data
send_json([
    'new' => false,
    'timeout' => true,
    'timestamp' => date('Y-m-d H:i:s')
]);
