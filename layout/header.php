<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? 'Absensi RFID') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark bg-gray-900 text-gray-100">
<div class="flex min-h-screen bg-gray-900">

  <!-- Sidebar -->
  <aside class="w-64 bg-gray-800 shadow-lg">
    <div class="p-4 font-bold text-xl text-gray-200">Absensi RFID</div>
    <nav class="mt-6 space-y-1">
      <a href="index.php?page=dashboard" class="block px-4 py-2 hover:bg-gray-700">🏠 Dashboard</a>
      <a href="index.php?page=students" class="block px-4 py-2 hover:bg-gray-700">👨‍🎓 Data Siswa</a>
      <a href="index.php?page=schedule" class="block px-4 py-2 hover:bg-gray-700">📅 Jadwal</a>
      <a href="index.php?page=attendance_log" class="block px-4 py-2 hover:bg-gray-700">📂 Riwayat</a>
      <a href="index.php?page=live_feed" class="block px-4 py-2 hover:bg-gray-700">⚡ Live Feed</a>
    </nav>
  </aside>

  <!-- Content wrapper -->
  <div class="flex-1 flex flex-col">
    <!-- Navbar atas -->
    <header class="bg-gray-800 shadow p-4 flex justify-between items-center">
      <h1 class="text-lg font-semibold"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
      <span id="clock" class="font-mono text-sm"></span>
    </header>

    <!-- Main content -->
    <main class="p-6">
