<?php
$baseDir = ($role === 'user') ? 'pages_user' : 'pages';

switch ($page) {
    case 'dashboard':
        include "$baseDir/dashboard.php";
        break;
    case 'students':
        include "$baseDir/students.php";
        break;
    case 'schedule':
        if ($role === 'user' && file_exists("pages_user/$page.php")) {
            include "pages_user/$page.php";
        } else {
            include "pages/$page.php";
        }
        break;
    case 'schedules':
        include 'pages/schedule.php';
        break;
    case 'attendance_log':
        include "$baseDir/attendance_log.php";
        break;
    case 'live_feed':
        include 'pages/live_feed.php';
        break;
    case 'scan':
        include 'pages/scan.php';
        break;
    case 'tester':
        include 'pages/tester.php';
        break;
    default:
        include 'pages/dashboard.php';
        break;
}
?>