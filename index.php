<?php
$page = $_GET['page'] ?? 'dashboard';
$pageTitle = ($page==='students') ? 'Data Siswa' : 'Dashboard';
include 'layout/header.php';
include 'route.php';
include 'layout/footer.php';
