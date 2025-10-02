<?php
// layout/header.php
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
    /* Sidebar default hidden */
    .sidebar {
      width: 240px;
      transform: translateX(-100%);
      transition: transform .25s ease;
      position: fixed;
      top: 0; left: 0;
      height: 100vh;
      background: #1f2937; /* bg-gray-800 */
      z-index: 40;
      padding: 1rem;
    }
    .sidebar.open { transform: translateX(0); }
    .overlay {
      display: none;
    }
    .overlay.show {
      display: block;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      z-index: 30;
    }
    .nav-link.active { background: rgba(255,255,255,0.1); }
  </style>
</head>
<body class="bg-gray-900 text-gray-200">

  <!-- Overlay -->
  <div id="overlay" class="overlay"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold">MRC</div>
      <div>
        <div class="font-semibold">IKI MARCO</div>
        <div class="text-xs text-gray-400">Dashboard</div>
      </div>
    </div>
    <nav class="space-y-2">
      <a href="index.php?page=dashboard" class="nav-link block px-3 py-2 rounded hover:bg-white/10">🏠 Dashboard</a>
      <a href="index.php?page=students" class="nav-link block px-3 py-2 rounded hover:bg-white/10">👨‍🎓 Data Siswa</a>
      <a href="index.php?page=schedules" class="nav-link block px-3 py-2 rounded hover:bg-white/10">📅 Jadwal</a>
      <a href="index.php?page=live_feed" class="nav-link block px-3 py-2 rounded hover:bg-white/10">🔴 Live Feed</a>
      <a href="index.php?page=attendance_log" class="nav-link block px-3 py-2 rounded hover:bg-white/10">📂 Riwayat</a>
    </nav>
  </aside>

  <!-- Topbar -->
  <header class="fixed top-0 left-0 right-0 h-14 bg-gray-900 flex items-center justify-between px-4 z-20">
    <!-- Toggle button (desktop & mobile) -->
    <button id="btnToggle" class="p-2 bg-white/10 rounded">☰</button>
    <div class="flex items-center gap-3 ml-auto">
      <div id="wibClock" class="text-sm text-gray-300 bg-white/10 px-3 py-1 rounded">--:--:--</div>
    </div>
  </header>

  <!-- Main -->
  <main class="pt-16 px-4 pb-10">
