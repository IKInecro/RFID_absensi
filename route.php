<?php
switch($page){
  case 'students':
    include 'pages/students.php';
    break;
  case 'schedule':
    include 'pages/schedule.php';
    break;
  case 'schedules':
    include 'pages/schedule.php';
    break;
  case 'attendance_log':
    include 'pages/attendance_log.php';
    break;
  case 'live_feed':
    include 'pages/live_feed.php';
    break;
  case 'tester':
    include 'pages/tester.php';
    break;
  default:
    include 'pages/dashboard.php';
}
