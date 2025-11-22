<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// ===== Ambil Mode dari settings =====
$setting = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id = 1")->fetch_assoc() ?? ['reg_mode' => 0, 'test_mode' => 0];
$reg_mode = intval($setting['reg_mode']);
$testMode = intval($setting['test_mode']);

// ===== Statistik =====
$totalStudents = ($conn->query("SELECT COUNT(*) as c FROM students WHERE status='active'")->fetch_assoc()['c'] ?? 0);
$today = date('Y-m-d');
$totalToday = ($conn->query("SELECT COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$today' AND schedule_status IN ('On Time','Late')")->fetch_assoc()['c'] ?? 0);
$lateToday = ($conn->query("SELECT COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$today' AND schedule_status='Late'")->fetch_assoc()['c'] ?? 0);
$onTimeToday = $totalToday - $lateToday;

$day = date('D');
$isHoliday = ($conn->query("SELECT is_holiday FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc()['is_holiday'] ?? 0);

// ===== Data Grafik 7 Hari Terakhir =====
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i day"));
  $chart_labels[] = date('d M', strtotime($date));

  $row = $conn->query("SELECT COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$date' AND schedule_status IN ('On Time', 'Late')")->fetch_assoc();
  $chart_data[] = intval($row['c'] ?? 0);
}
?>

<div class="space-y-6 animate-fade-in">

  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Pantau aktivitas absensi secara real-time.</p>
    </div>
    <div
      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-5 py-3 shadow-sm flex items-center gap-3">
      <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <div>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Hari ini</div>
        <div class="text-lg font-bold text-gray-900 dark:text-white"><?= date('l, d M Y') ?></div>
      </div>
    </div>
  </div>

  <!-- Control Panel -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Register Mode Card -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div
            class="p-2.5 rounded-xl <?= $reg_mode ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Register Mode</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mode pendaftaran kartu baru</p>
          </div>
        </div>
        <div
          class="h-3 w-3 rounded-full <?= $reg_mode ? 'bg-green-500 animate-pulse' : 'bg-gray-300 dark:bg-gray-600' ?>">
        </div>
      </div>

      <form action="action_register.php" method="POST">
        <input type="hidden" name="toggle_reg_mode" value="<?= $reg_mode ? 0 : 1 ?>">
        <button type="submit"
          class="w-full py-3 px-4 rounded-xl font-semibold text-white shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 <?= $reg_mode ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-red-500/30' : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 shadow-green-500/30' ?>">
          <?= $reg_mode ? 'Matikan Register Mode' : 'Aktifkan Register Mode' ?>
        </button>
      </form>
    </div>

    <!-- Test Mode Card -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div
            class="p-2.5 rounded-xl <?= $testMode ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Test Mode</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Simulasi tap kartu tanpa hardware</p>
          </div>
        </div>
        <div
          class="h-3 w-3 rounded-full <?= $testMode ? 'bg-yellow-500 animate-pulse' : 'bg-gray-300 dark:bg-gray-600' ?>">
        </div>
      </div>

      <form method="POST" action="toggle_testmode.php">
        <input type="hidden" name="new_mode" value="<?= $testMode ? 0 : 1 ?>">
        <button type="submit"
          class="w-full py-3 px-4 rounded-xl font-semibold text-white shadow-lg transition-all active:scale-95 <?= $testMode ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 shadow-yellow-500/30' : 'bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 shadow-gray-500/30' ?>">
          <?= $testMode ? 'Matikan Test Mode' : 'Aktifkan Test Mode' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Students -->
    <div
      class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
      <div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </div>
      <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider">Total Siswa</h3>
      <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $totalStudents ?></p>
    </div>

    <!-- Hadir Hari Ini -->
    <div
      class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
      <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider">Hadir Hari Ini</h3>
      <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $totalToday ?></p>
    </div>

    <!-- Terlambat -->
    <div
      class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
      <div class="p-3 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider">Terlambat</h3>
      <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $lateToday ?></p>
    </div>

    <!-- Tepat Waktu -->
    <div
      class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
      <div class="p-3 rounded-full bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider">Tepat Waktu</h3>
      <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $onTimeToday ?></p>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Chart -->
    <div
      class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Statistik Mingguan</h3>
      <div class="relative h-72 w-full">
        <canvas id="weeklyChart"></canvas>
      </div>
    </div>

    <!-- Pie Chart -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Status Hari Ini</h3>
      <div class="relative h-64 w-full flex justify-center">
        <canvas id="statusChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="index.php?page=students"
      class="flex items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all group">
      <div
        class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
      </div>
      <div>
        <h3 class="font-bold text-gray-900 dark:text-white">Tambah Siswa</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">Input data siswa baru</p>
      </div>
    </a>

    <a href="index.php?page=schedule"
      class="flex items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all group">
      <div
        class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <div>
        <h3 class="font-bold text-gray-900 dark:text-white">Atur Jadwal</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">Kelola jam masuk/pulang</p>
      </div>
    </a>

    <a href="index.php?page=attendance_log"
      class="flex items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all group">
      <div
        class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
      </div>
      <div>
        <h3 class="font-bold text-gray-900 dark:text-white">Laporan</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">Lihat & export log absensi</p>
      </div>
    </a>
  </div>
</div>

<script>
  // Weekly Chart
  const ctx = document.getElementById('weeklyChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($chart_labels) ?>,
      datasets: [{
        label: 'Hadir',
        data: <?= json_encode($chart_data) ?>,
        backgroundColor: '#3B82F6',
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
        x: { grid: { display: false } }
      }
    }
  });

  // Status Pie Chart
  const ctxPie = document.getElementById('statusChart').getContext('2d');
  new Chart(ctxPie, {
    type: 'doughnut',
    data: {
      labels: ['Tepat Waktu', 'Terlambat', 'Belum Hadir'],
      datasets: [{
        data: [<?= $onTimeToday ?>, <?= $lateToday ?>, <?= $totalStudents - $totalToday ?>],
        backgroundColor: ['#10B981', '#EF4444', '#E5E7EB'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
</script>