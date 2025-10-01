<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta'); // ganti sesuai zona

// ===== Statistik =====
// Total siswa aktif
$totalStudents = $conn->query("SELECT COUNT(*) as c FROM students WHERE status='active'")->fetch_assoc()['c'];

// Total hadir hari ini
$today = date('Y-m-d');
$totalToday = $conn->query("
    SELECT COUNT(*) as c FROM attendance_log 
    WHERE DATE(timestamp)='$today' 
      AND schedule_status IN ('On Time','Late')
")->fetch_assoc()['c'];

// Total terlambat hari ini
$lateToday = $conn->query("
    SELECT COUNT(*) as c FROM attendance_log 
    WHERE DATE(timestamp)='$today' 
      AND schedule_status='Late'
")->fetch_assoc()['c'];

// Cek apakah hari ini libur
$day = date('D');
$isHoliday = $conn->query("SELECT is_holiday FROM schedules WHERE day='$day' LIMIT 1")
                 ->fetch_assoc()['is_holiday'] ?? 0;

// ===== Data Grafik 7 Hari Terakhir =====
$labels = [];
$dataOnTime = [];
$dataLate   = [];
for($i=6; $i>=0; $i--){
    $date = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d M', strtotime($date));

    $rowOn = $conn->query("
        SELECT COUNT(*) as c FROM attendance_log 
        WHERE DATE(timestamp)='$date' AND schedule_status='On Time'
    ")->fetch_assoc();
    $rowLate = $conn->query("
        SELECT COUNT(*) as c FROM attendance_log 
        WHERE DATE(timestamp)='$date' AND schedule_status='Late'
    ")->fetch_assoc();

    $dataOnTime[] = $rowOn['c'];
    $dataLate[]   = $rowLate['c'];
}
?>
<div class="space-y-8">

  <!-- Statistik Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md text-center">
      <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-300">Total Siswa</h3>
      <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $totalStudents ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md text-center">
      <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-300">Hadir Hari Ini</h3>
      <p class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $totalToday ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md text-center">
      <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-300">Terlambat Hari Ini</h3>
      <p class="text-3xl font-bold text-red-600 dark:text-red-400"><?= $lateToday ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md text-center">
      <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-300">Status Hari Ini</h3>
      <p class="text-2xl font-bold <?= $isHoliday?'text-purple-600 dark:text-purple-400':'text-gray-700 dark:text-gray-200' ?>">
        <?= $isHoliday ? 'Libur' : 'Aktif' ?>
      </p>
    </div>
  </div>

  <!-- Grafik Absensi 7 Hari -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Absensi 7 Hari Terakhir</h3>
    <canvas id="attendanceChart" height="100"></canvas>
  </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
    type: 'lin// ganti semua schedule_status menjadi status
    $totalToday = $conn->query("
    SELECT COUNT(*) as c FROM attendance_log 
    WHERE DATE(timestamp)='$today' 
      AND status IN ('ontime','late')
")->fetch_assoc()['c'];

$lateToday = $conn->query("
    SELECT COUNT(*) as c FROM attendance_log 
    WHERE DATE(timestamp)='$today' 
      AND status='late'
")->fetch_assoc()['c'];
e',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'On Time',
                data: <?= json_encode($dataOnTime) ?>,
                borderColor: 'rgb(37, 99, 235)', // biru
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Late',
                data: <?= json_encode($dataLate) ?>,
                borderColor: 'rgb(239, 68, 68)', // merah
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: getComputedStyle(document.documentElement)
                           .getPropertyValue('--tw-prose-body')
                }
            }
        },
        scales: {
            x: { ticks: { color: '#6b7280' } },
            y: { ticks: { color: '#6b7280' }, beginAtZero: true }
        }
    }
});
</script>
