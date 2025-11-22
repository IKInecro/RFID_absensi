<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role']; // 'admin' or 'user'
$username = $_SESSION['username'] ?? 'User';

$page = $_GET['page'] ?? 'dashboard';

// RBAC: Define allowed pages for 'user' role
$allowed_user_pages = ['dashboard', 'live_feed', 'tester', 'attendance_log', 'schedule', 'students'];

if ($role === 'user' && !in_array($page, $allowed_user_pages)) {
    // If user tries to access restricted page, redirect to dashboard
    header('Location: index.php?page=dashboard');
    exit;
}

$pageTitle = ucfirst(str_replace('_', ' ', $page));
if ($page === 'students')
    $pageTitle = 'Data Siswa';
if ($page === 'attendance_log')
    $pageTitle = 'Riwayat Absensi';

include 'layout/header.php';
include 'route.php';
include 'layout/footer.php';
