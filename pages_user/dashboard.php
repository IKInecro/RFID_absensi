<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

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

<div class="space-y-8 animate-fade-in">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Pantau aktivitas absensi secara real-time.</p>
        </div>
        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-5 py-3 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
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

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Students -->
        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
            <div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider">Hadir Hari Ini
            </h3>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $totalToday ?></p>
        </div>

        <!-- Terlambat -->
        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center text-center hover:scale-[1.02] transition-transform duration-200">
            <div class="p-3 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
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
                <h3 class="font-bold text-gray-900 dark:text-white">Data Siswa</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat data siswa</p>
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
                <h3 class="font-bold text-gray-900 dark:text-white">Jadwal</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat jadwal masuk/pulang</p>
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
                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat riwayat absensi</p>
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