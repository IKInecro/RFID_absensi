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

$day = date('D');
$isHoliday = ($conn->query("SELECT is_holiday FROM schedules WHERE day='$day' LIMIT 1")->fetch_assoc()['is_holiday'] ?? 0);

// ===== Data Grafik 7 Hari Terakhir =====
$labels = [];
$dataOnTime = [];
$dataLate   = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d M', strtotime($date));

    $rowOn = $conn->query("SELECT COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$date' AND schedule_status='On Time'")->fetch_assoc();
    $rowLate = $conn->query("SELECT COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$date' AND schedule_status='Late'")->fetch_assoc();

    $dataOnTime[] = intval($rowOn['c']);
    $dataLate[]   = intval($rowLate['c']);
}
?>

<style>
.dashboard-card {
  transition: all 0.25s ease;
}
.dashboard-card:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  transform: translateY(-3px);
}
.stat-number {
  font-size: 2.3rem;
  font-weight: 700;
}
.card-icon {
  font-size: 2rem;
  opacity: 0.8;
}
.chart-container {
  position: relative;
  height: 360px;
}
</style>

<div class="space-y-10">

  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-3">
    <div>
      <h1 class="text-2xl font-bold text-white">📊 Dashboard Absensi</h1>
      <p class="text-gray-400">Pantau aktivitas siswa dan status hari ini.</p>
    </div>
    <div class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-right">
      <div id="liveClock" class="text-white text-lg font-semibold"></div>
      <div class="text-sm text-gray-400"><?= date('l, d M Y') ?></div>
    </div>
  </div>

  <!-- Mode Switch Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-white">Register Mode</h2>
        <span class="card-icon">🧭</span>
      </div>
      <form action="action_register.php" method="POST">
        <input type="hidden" name="toggle_reg_mode" value="<?= $reg_mode ? 0 : 1 ?>">
        <button type="submit"
          class="w-full py-2.5 rounded-lg text-white font-semibold transition
          <?= $reg_mode ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' ?>">
          <?= $reg_mode ? 'Matikan Register Mode' : 'Aktifkan Register Mode' ?>
        </button>
      </form>
    </div>

    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-white">Test Mode</h2>
        <span class="card-icon">🧪</span>
      </div>
      <form method="POST" action="toggle_testmode.php">
        <input type="hidden" name="new_mode" value="<?= $testMode ? 0 : 1 ?>">
        <button type="submit"
          class="w-full py-2.5 rounded-lg font-semibold text-white transition
          <?= $testMode ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-gray-700 hover:bg-gray-800' ?>">
          <?= $testMode ? '🧪 Test Mode Aktif' : 'Aktifkan Test Mode' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Statistik Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl text-center">
      <div class="card-icon mb-2 text-blue-500">👨‍🎓</div>
      <h3 class="text-gray-400 font-medium mb-1">Total Siswa</h3>
      <p class="stat-number text-blue-400"><?= $totalStudents ?></p>
    </div>

    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl text-center">
      <div class="card-icon mb-2 text-green-500">✅</div>
      <h3 class="text-gray-400 font-medium mb-1">Hadir Hari Ini</h3>
      <p class="stat-number text-green-400"><?= $totalToday ?></p>
    </div>

    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl text-center">
      <div class="card-icon mb-2 text-red-500">⏰</div>
      <h3 class="text-gray-400 font-medium mb-1">Terlambat Hari Ini</h3>
      <p class="stat-number text-red-400"><?= $lateToday ?></p>
    </div>

    <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl text-center">
      <div class="card-icon mb-2 text-purple-500">📅</div>
      <h3 class="text-gray-400 font-medium mb-1">Status Hari Ini</h3>
      <p class="stat-number <?= $isHoliday ? 'text-purple-400' : 'text-gray-300' ?>">
        <?= $isHoliday ? 'Libur' : 'Aktif' ?>
      </p>
    </div>
  </div>

  <!-- Grafik Absensi -->
  <div class="dashboard-card bg-gray-900 border border-gray-700 p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4 text-white">Absensi 7 Hari Terakhir</h3>
    <div class="chart-container">
      <canvas id="attendanceChart"></canvas>
    </div>
  </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// live clock
setInterval(() => {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  document.getElementById('liveClock').innerText = timeStr;
}, 1000);

// Chart
const ctx = document.getElementById('attendanceChart').getContext('2d');
const gradientOnTime = ctx.createLinearGradient(0, 0, 0, 300);
gradientOnTime.addColorStop(0, 'rgba(59,130,246,0.6)');
gradientOnTime.addColorStop(1, 'rgba(59,130,246,0.05)');

const gradientLate = ctx.createLinearGradient(0, 0, 0, 300);
gradientLate.addColorStop(0, 'rgba(239,68,68,0.6)');
gradientLate.addColorStop(1, 'rgba(239,68,68,0.05)');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [
      {
        label: 'On Time',
        data: <?= json_encode($dataOnTime) ?>,
        borderColor: '#3b82f6',
        backgroundColor: gradientOnTime,
        borderWidth: 2,
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#1e3a8a',
        pointRadius: 4,
      },
      {
        label: 'Late',
        data: <?= json_encode($dataLate) ?>,
        borderColor: '#ef4444',
        backgroundColor: gradientLate,
        borderWidth: 2,
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#ef4444',
        pointBorderColor: '#7f1d1d',
        pointRadius: 4,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: { color: '#d1d5db', font: { size: 13 } }
      },
      tooltip: {
        backgroundColor: '#1f2937',
        titleColor: '#fff',
        bodyColor: '#e5e7eb',
        borderWidth: 1,
        borderColor: '#374151',
        cornerRadius: 8,
        padding: 10
      }
    },
    scales: {
      x: {
        ticks: { color: '#9ca3af' },
        grid: { color: '#374151' }
      },
      y: {
        ticks: { color: '#9ca3af' },
        grid: { color: '#374151' },
        beginAtZero: true
      }
    }
  }
});
</script>
