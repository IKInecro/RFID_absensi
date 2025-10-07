<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// pagination
$limit = 10;
$page  = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// filters
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$name = $_GET['name'] ?? '';
$class = $_GET['class'] ?? '';
$card = $_GET['card'] ?? '';
$device = $_GET['device'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
if ($from && $to) $where[] = "DATE(al.timestamp) BETWEEN '$from' AND '$to'";
if ($name) $where[] = "s.name LIKE '%$name%'";
if ($class) $where[] = "s.class LIKE '%$class%'";
if ($card) $where[] = "al.card_id LIKE '%$card%'";
if ($device) $where[] = "al.device_id LIKE '%$device%'";
if ($status) $where[] = "al.schedule_status='$status'";
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// count total
$totalRows = $conn->query("
  SELECT COUNT(*) as c
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  $whereSql
")->fetch_assoc()['c'];
$totalPages = ceil($totalRows / $limit);

// query data
$data = $conn->query("
  SELECT al.*, s.name, s.class, s.profile_pic
  FROM attendance_log al
  LEFT JOIN students s ON s.id = al.student_id
  $whereSql
  ORDER BY al.timestamp DESC
  LIMIT $limit OFFSET $offset
");
?>

<div class="space-y-8">
  <!-- Filter -->
  <form method="GET" action="index.php" class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-md grid grid-cols-1 sm:grid-cols-3 md:grid-cols-6 gap-3">
    <input type="hidden" name="page" value="attendance_log">
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Dari</label>
      <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Sampai</label>
      <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Nama</label>
      <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
      <input type="text" name="class" value="<?= htmlspecialchars($class) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Card ID</label>
      <input type="text" name="card" value="<?= htmlspecialchars($card) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Device</label>
      <input type="text" name="device" value="<?= htmlspecialchars($device) ?>" class="p-2 border rounded w-full dark:bg-gray-700">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Status</label>
      <select name="status" class="p-2 border rounded w-full dark:bg-gray-700">
        <option value="">Semua</option>
        <option value="On Time" <?= $status == 'On Time' ? 'selected' : '' ?>>On Time</option>
        <option value="Toleransi" <?= $status == 'Toleransi' ? 'selected' : '' ?>>Toleransi</option>
        <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Late</option>
        <option value="Libur" <?= $status == 'Libur' ? 'selected' : '' ?>>Libur</option>
        <option value="Out of Schedule" <?= $status == 'Out of Schedule' ? 'selected' : '' ?>>Out of Schedule</option>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Filter</button>
    </div>
    <div class="flex items-end">
      <?php $params = http_build_query($_GET); ?>
      <a href="export_attendance_csv.php?<?= $params ?>" class="bg-blue-600 text-white px-4 py-2 rounded">⬇️ Export CSV</a>
    </div>
  </form>

  <!-- Table -->

  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Riwayat Absensi</h2>
    <table class="w-full border border-gray-300 dark:border-gray-700 border-collapse text-sm">
      <thead>
        <tr class="bg-blue-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
          <th class="border p-2">Waktu</th>
          <th class="border p-2">Foto</th>
          <th class="border p-2">Nama</th>
          <th class="border p-2">Kelas</th>
          <th class="border p-2">Card ID</th>
          <th class="border p-2">Device</th>
          <th class="border p-2">Status Jadwal</th>
        </tr>
      </thead>
      <tbody id="attendance-table">
        <?php if ($data->num_rows > 0): ?>
          <?php while($row = $data->fetch_assoc()): ?>
            <?php
              $statusTxt = $row['schedule_status'] ?: 'Tidak Diketahui';
              $profilePath = (!empty($row['profile_pic']) && file_exists('uploads/'.$row['profile_pic']))
                ? 'uploads/'.$row['profile_pic']
                : 'assets/img/default-avatar.png';
            ?>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <td class="border p-2"><?= $row['timestamp'] ?></td>
              <td class="border p-2"><img src="<?= $profilePath ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover"></td>
              <td class="border p-2"><?= htmlspecialchars($row['name'] ?? '-') ?></td>
              <td class="border p-2"><?= htmlspecialchars($row['class'] ?? '-') ?></td>
              <td class="border p-2"><?= htmlspecialchars($row['card_id'] ?? '-') ?></td>
              <td class="border p-2"><?= htmlspecialchars($row['device_id'] ?? '-') ?></td>
              <td class="border p-2 font-semibold"><?= htmlspecialchars($statusTxt) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center p-4">Tidak ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Auto refresh -->

<script>
setInterval(() => {
  fetch(window.location.href)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const newTable = parser.parseFromString(html, 'text/html').querySelector('#attendance-table');
      document.querySelector('#attendance-table').innerHTML = newTable.innerHTML;
    });
}, 5000);
</script>
