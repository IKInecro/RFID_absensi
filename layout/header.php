<?php
// layout/header.php — versi UI modern + responsive sidebar
require_once __DIR__ . '/../db.php';
$reg_mode = 0;
if ($res = $conn->query("SELECT reg_mode FROM settings WHERE id = 1")) {
  $data = $res->fetch_assoc();
  $reg_mode = intval($data['reg_mode']);
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
    body { font-family: 'Inter', sans-serif; }
    .sidebar {
      width: 250px;
      transform: translateX(-100%);
      transition: transform 0.25s ease;
      box-shadow: 3px 0 10px rgba(0,0,0,0.3);
    }
    .sidebar.show { transform: translateX(0); }
    .overlay {
      display: none;
    }
    .overlay.show {
      display: block;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(6px);
      z-index: 30;
    }
    .nav-link {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 8px;
      font-weight: 500;
      transition: background .2s ease, color .2s ease;
    }
    .nav-link:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    .nav-link.active {
      background: linear-gradient(90deg, rgba(59,130,246,0.3), rgba(59,130,246,0.15));
      color: #fff;
      position: relative;
    }
    .nav-link.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 8px;
      bottom: 8px;
      width: 3px;
      border-radius: 4px;
      background: #3b82f6;
    }
  </style>
</head>
<body class="bg-gray-950 text-gray-100">

  <!-- Overlay -->
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar fixed left-0 top-0 bottom-0 bg-gray-900/95 p-5 z-40 backdrop-blur-md border-r border-white/10">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-xl font-bold tracking-wide">📘 Absensi</h3>
      <button id="closeSidebar" class="md:hidden text-gray-400 hover:text-white text-xl font-bold">×</button>
    </div>
    <nav class="space-y-1 text-sm">
      <a href="index.php?page=dashboard" class="nav-link <?= ($page ?? '')==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
      <a href="index.php?page=students" class="nav-link <?= ($page ?? '')==='students'?'active':'' ?>">👨‍🎓 Data Siswa</a>
      <a href="index.php?page=schedules" class="nav-link <?= ($page ?? '')==='schedules'?'active':'' ?>">📅 Jadwal</a>
      <a href="index.php?page=live_feed" class="nav-link <?= ($page ?? '')==='live_feed'?'active':'' ?>">🔴 Live Feed</a>
      <a href="index.php?page=attendance_log" class="nav-link <?= ($page ?? '')==='attendance_log'?'active':'' ?>">📂 Riwayat</a>
      <?php if ($reg_mode === 0): ?>
      <a href="index.php?page=tester" class="nav-link <?= ($page ?? '')==='tester'?'active':'' ?>">🎛 Tester Mode</a>
      <?php endif; ?>
    </nav>

    <div class="mt-8 text-xs text-gray-500 border-t border-white/10 pt-4">
      <div class="flex items-center justify-between">
        <span>Reg Mode:</span>
        <span class="font-semibold <?= $reg_mode ? 'text-green-400' : 'text-red-400' ?>">
          <?= $reg_mode ? 'ON' : 'OFF' ?>
        </span>
      </div>
      <div class="mt-3 text-gray-500">© <?= date('Y') ?> Absensi RFID</div>
    </div>
  </aside>

  <!-- Topbar -->
  <header class="fixed top-0 left-0 right-0 h-14 bg-gray-900/90 backdrop-blur-md border-b border-white/10 flex items-center justify-between px-4 z-20">
    <button id="btnToggle" class="p-2 rounded bg-white/10 hover:bg-white/20 transition text-lg font-semibold">☰</button>
    <div class="flex items-center gap-3 ml-auto">
      <div id="wibClock" class="text-sm text-gray-300 bg-white/10 px-3 py-1 rounded">--:--:--</div>
    </div>
  </header>

  <!-- Main -->
  <main class="pt-16 px-4 pb-10 transition-all duration-200 ease-in-out">

  <script>
    // === Sidebar toggle ===
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const btnToggle = document.getElementById('btnToggle');
    const closeSidebar = document.getElementById('closeSidebar');

    btnToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });

    closeSidebar?.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });

    // === Clock (WIB) ===
    function updateClock() {
      const now = new Date();
      const time = now.toLocaleTimeString('id-ID', { hour12: false });
      document.getElementById('wibClock').textContent = time + ' WIB';
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
