<?php
$page = $_GET['page'] ?? 'dashboard';
$pageTitle = ucfirst($page);

// koneksi DB (cukup sekali di sini)
include 'db.php';

// layout
include 'layout/header.php';
include 'route.php';
include 'layout/footer.php';
