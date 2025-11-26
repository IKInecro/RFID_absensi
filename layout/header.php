<?php
// layout/header.php — versi UI modern + responsive sidebar + dark/light mode
require_once __DIR__ . '/../db.php';
$reg_mode = 0;
if ($res = $conn->query("SELECT reg_mode FROM settings WHERE id = 1")) {
  $data = $res->fetch_assoc();
  $reg_mode = intval($data['reg_mode']);
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Absensi RFID') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // On page load or when changing themes, best to add inline in `head` to avoid FOUC
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  </script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            gray: {
              900: '#111827',
              950: '#030712',
            }
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .sidebar {
      width: 260px;
      transform: translateX(-100%);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }

    ::-webkit-scrollbar-track {
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }

    .dark ::-webkit-scrollbar-thumb {
      background: #475569;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-300">

  <!-- Mobile Sidebar Overlay -->
  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity">
  </div>

  <!-- Sidebar -->
  <aside id="sidebar"
    class="fixed top-0 left-0 z-50 h-screen w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-xl lg:shadow-none flex flex-col">

    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-100 dark:border-gray-700 shrink-0">
      <div class="flex items-center gap-3">
        <img src="assets/img/pgri_transparent.png" alt="Logo" class="w-10 h-10 object-contain">
        <span
          class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
          SMAPIJ ABSEN
        </span>
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1">
      <?php
      $menu = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />'],
        'scan' => ['label' => 'Scan/Register', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />'],
        'students' => ['label' => 'Data Siswa', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'],
        'schedule' => ['label' => 'Jadwal', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
        'attendance_log' => ['label' => 'Log Absensi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />'],
        'live_feed' => ['label' => 'Live Feed', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />'],
        'tester' => ['label' => 'Tester Mode', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />'],
      ];

      foreach ($menu as $k => $v):
        $isActive = ($page == $k);
        $activeClass = $isActive
          ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold shadow-sm ring-1 ring-blue-200 dark:ring-blue-800'
          : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200 hover:translate-x-1';
        ?>
        <a href="index.php?page=<?= $k ?>"
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?= $activeClass ?>">
          <svg xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 transition-transform group-hover:scale-110 <?= $isActive ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300' ?>"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <?= $v['icon'] ?>
          </svg>
          <span><?= $v['label'] ?></span>
          <?php if ($k === 'live_feed'): ?>
            <span class="ml-auto flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
            </span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-gray-100 dark:border-gray-700 shrink-0">
      <div
        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer group">
        <div
          class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-105 transition-transform">
          <?= strtoupper(substr($username ?? 'U', 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
            <?= htmlspecialchars($username ?? 'User') ?>
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate uppercase">
            <?= htmlspecialchars($role ?? 'user') ?>
          </p>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <div class="lg:ml-64 min-h-screen flex flex-col transition-all duration-300">

    <!-- Topbar -->
    <header
      class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30 transition-colors duration-300/80 backdrop-blur-md bg-white/90 dark:bg-gray-800/90">
      <div class="flex items-center gap-4">
        <button id="sidebarToggle"
          class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h2 class="text-xl font-bold text-gray-800 dark:text-white hidden sm:block">
          <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
        </h2>
      </div>

      <!-- Right Side: Clock, Theme Toggle, User Profile -->
      <div class="flex items-center gap-3 sm:gap-4">
        <!-- Clock -->
        <div id="clock"
          class="hidden md:block font-mono text-sm sm:text-base font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
          00:00:00
        </div>

        <!-- Theme Toggle -->
        <button id="themeToggle"
          class="relative inline-flex items-center h-8 w-14 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500">
          <span class="sr-only">Toggle Theme</span>
          <span id="themeIndicator"
            class="transform transition-transform duration-200 ease-in-out translate-x-1 dark:translate-x-7 inline-block w-6 h-6 rounded-full bg-white shadow-md flex items-center justify-center">
            <!-- Sun Icon -->
            <svg id="sunIcon" class="w-4 h-4 text-yellow-500 dark:hidden" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Moon Icon -->
            <svg id="moonIcon" class="w-4 h-4 text-blue-500 hidden dark:block" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </span>
        </button>

        <!-- User Profile (Mobile/Desktop) -->
        <div class="flex items-center gap-3 pl-3 sm:pl-4 border-l border-gray-200 dark:border-gray-700">
          <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($username ?? 'User') ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?= htmlspecialchars($role ?? 'user') ?></p>
          </div>
          <a href="logout.php" title="Logout"
            class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </a>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20 pb-8 px-4 sm:px-6 lg:px-8 w-full mx-auto min-h-screen">