<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=attendance_log&error=unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($id > 0 && in_array($status, ['On Time', 'Late', 'Toleransi', 'Sakit', 'Izin', 'Alpha'])) {
        $stmt = $conn->prepare("UPDATE attendance_log SET schedule_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            header('Location: index.php?page=attendance_log&success=updated');
        } else {
            header('Location: index.php?page=attendance_log&error=failed');
        }
        $stmt->close();
    } else {
        header('Location: index.php?page=attendance_log&error=invalid');
    }
} else {
    header('Location: index.php?page=attendance_log');
}
exit;
