<?php
$page = $_GET['page'] ?? 'dashboard';

switch($page){
  case 'students': $pageTitle = 'Data Siswa'; break;
  case 'schedule': $pageTitle = 'Jadwal'; break;
  case 'attendance_log': $pageTitle = 'Riwayat Absensi'; break;
  case 'live_feed': $pageTitle = 'Live Feed'; break;
  default: $pageTitle = 'Dashboard';
}

include 'layout/header.php';
include 'route.php';
include 'layout/footer.php';
