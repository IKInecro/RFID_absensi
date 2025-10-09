<?php
// layout/header.php — versi fix toggle sidebar (nav muncul saat klik ☰)
require_once __DIR__ . '/../db.php';
$reg_mode = 0;
$res_mode = $conn->query("SELECT reg_mode FROM settings WHERE id = 1");
if ($res_mode) {
  $rmode = $res_mode->fetch_assoc();
  $reg_mode = intval($rmode['reg_mode']);
}
?>
<!doctype html>
<html lang="id" class="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Absensi RFID') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: Inter, sans-serif; }
    .sidebar {
      width: 240px;
      transform: translateX(-100%);
      transition: transform .2s ease;
    }
    .sidebar.show {
      transform: translateX(0);
    }
    .overlay {
      display: none;
    }
    .overlay.show {
      display: block;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(6px);
      z-index: 30;
    }
    .nav-link.active {
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
</head>
<body class="bg-gray-900 text-gray-200">

  <!-- Overlay -->
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar fixed left-0 top-0 bottom-0 bg-gray-800 p-4 z-40">
    <h3 class="text-xl font-bold mb-4">Absensi</h3>
    <nav class="space-y-2">
      <a href="index.php?page=dashboard" class="nav-link block px-3 py-2 rounded hover:bg-white/10">🏠 Dashboard</a>
      <a href="index.php?page=students" class="nav-link block px-3 py-2 rounded hover:bg-white/10">👨‍🎓 Data Siswa</a>
      <a href="index.php?page=schedules" class="nav-link block px-3 py-2 rounded hover:bg-white/10">📅 Jadwal</a>
      <a href="index.php?page=live_feed" class="nav-link block px-3 py-2 rounded hover:bg-white/10">🔴 Live Feed</a>
      <a href="index.php?page=attendance_log" class="nav-link block px-3 py-2 rounded hover:bg-white/10">📂 Riwayat</a>
      <?php if ($reg_mode === 0): ?>
      <a href="index.php?page=tester" class="nav-link block px-3 py-2 rounded hover:bg-white/10">🎛 Tester</a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Topbar -->
  <header class="fixed top-0 left-0 right-0 h-14 bg-gray-900 flex items-center justify-between px-4 z-20">
    <button id="btnToggle" class="p-2 bg-white/10 rounded text-lg">☰</button>
    <div class="flex items-center gap-3 ml-auto">
      <div id="wibClock" class="text-sm text-gray-300 bg-white/10 px-3 py-1 rounded">--:--:--</div>
    </div>
  </header>

  <!-- Main -->
  <main class="pt-16 px-4 pb-10">

  <script>
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const btnToggle = document.getElementById('btnToggle');

    btnToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });

    // Clock update
    function updateClock() {
      const now = new Date();
      const time = now.toLocaleTimeString('id-ID', { hour12: false });
      document.getElementById('wibClock').textContent = time;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
