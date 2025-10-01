<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch($page){
    case 'dashboard':
        include 'pages/dashboard.php';
        break;
    case 'students':
        include 'pages/students.php';
        break;
    case 'schedule':   // <<< INI ROUTER UNTUK HALAMAN JADWAL
        include 'pages/schedule.php';
        break;
    default:
        include 'pages/dashboard.php';
    case 'attendance_log':
    $pageTitle = "Riwayat Absensi";
        include 'pages/attendance_log.php';
        break;
    case 'live_feed':
    $pageTitle = "Live Feed Absensi";
    include 'pages/live_feed.php';
    break;
}
