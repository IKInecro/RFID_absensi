<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? 'Absensi RFID') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">
<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">

  <!-- Sidebar -->
  <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg">
    <div class="p-4 font-bold text-xl text-gray-700 dark:text-gray-200">Absensi RFID</div>
    <nav class="mt-6">
      <a href="index.php?page=dashboard" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Dashboard</a>
      <a href="index.php?page=students" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Data Siswa</a>
      <a href="index.php?page=attendance" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Absensi</a>
      <a href="index.php?page=schedules" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Jadwal</a>
      <a href="index.php?page=history" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Riwayat</a>
    </nav>
  </aside>

  <!-- Content wrapper -->
  <div class="flex-1 flex flex-col">
    <!-- Navbar atas -->
    <header class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
      <h1 class="text-lg font-semibold"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
      <div class="flex items-center gap-4">
        <span id="clock" class="font-mono text-sm"></span>
        <button id="toggle-theme" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded">
          🌞 / 🌙
        </button>
      </div>
    </header>

    <!-- Main content -->
    <main class="p-6">
