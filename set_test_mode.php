<?php
// set_test_mode.php - Enable tester mode for RFID system
include __DIR__ . '/db.php';

// Check if test_mode column exists
$colCheck = $conn->query("SHOW COLUMNS FROM settings LIKE 'test_mode'");
if ($colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE settings ADD COLUMN test_mode TINYINT(1) DEFAULT 0");
    echo "Added test_mode column.<br>";
}

// Ensure settings row exists with id=1
$conn->query("INSERT INTO settings (id, test_mode) VALUES (1, 1) ON DUPLICATE KEY UPDATE test_mode=1");
echo "Tester mode enabled.";
?>