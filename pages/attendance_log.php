<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// Pagination
$limit = 10;
$page  = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// Filter tanggal
$from = isset($_GET['from']) ? $conn->real_escape_string($_GET['from']) : '';
$to   = isset($_GET['to'])   ? $conn->real_escape_string($_GET['to'])   : '';

// Search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build WHERE
$where = [];
if($from && $to){
    $where[] = "DATE(al.timestamp) BETWEEN '$from' AND '$to'";
}
if($search){
    $where[] = "(s.name LIKE '%$search%' OR s.class LIKE '%$search%' OR al.card_id LIKE '%$search%')";
}
$whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

// Count total
$totalRows = $conn->query("
    SELECT COUNT(*) as c
    FROM attendance_log al
    LEFT JOIN students s ON s.id = al.student_id
    $whereSql
")->fetch_assoc()['c'];
$totalPages = ceil($totalRows / $limit);

// Query data
$data = $conn->query("
    SELECT al.*, s.name, s.class
    FROM attendance_log al
    LEFT JOIN students s ON s.id = al.student_id
    $whereSql
    ORDER BY al.timestamp DESC
    LIMIT $limit OFFSET $offset
");
?>
<div class="space-y-9">

  <!-- Filter -->
  <form method="GET" action="index.php" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex flex-col sm:flex-row gap-3">
    <input type="hidden" name="page" value="attendance_log">
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Dari</label>
      <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"
             class="border p-2 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
    </div>
    <div>
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Sampai</label>
      <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"
             class="border p-2 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
    </div>
    <div class="flex-1">
      <label class="block text-gray-700 dark:text-gray-300 mb-1">Cari</label>
      <input type="text" name="search" placeholder="Nama / Kelas / Card ID"
             value="<?= htmlspecialchars($search) ?>"
             class="border p-2 rounded w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
    </div>
    <div class="flex items-end">
      <button type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
        Filter
      </button>
    </div>
  </form>
  <!-- csv data -->
  <div class="flex justify-end mb-4">
  <?php
    $params = http_build_query([
        'from'   => $from,
        'to'     => $to,
        'search' => $search
    ]);
  ?>
  <a href="export_attendance_csv.php?<?= $params ?>"
     class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
     ⬇️ Export CSV
  </a>
</div>

  <!-- Tabel Data -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Riwayat Absensi</h2>
    <table class="w-full border border-gray-300 dark:border-gray-700 border-collapse text-sm">
      <thead>
        <tr class="bg-blue-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
          <th class="border p-2">Tanggal/Waktu</th>
          <th class="border p-2">Nama</th>
          <th class="border p-2">Kelas</th>
          <th class="border p-2">Card ID</th>
          <th class="border p-2">Device</th>
          <th class="border p-2">Status Jadwal</th>
        </tr>
      </thead>
      <tbody>
      <?php if($data->num_rows > 0): ?>
      <?php while($row = $data->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
          <td class="border p-2 dark:border-gray-700"><?= $row['timestamp'] ?></td>
          <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['name']) ?></td>
          <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['class']) ?></td>
          <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['card_id']) ?></td>
          <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['device_id']) ?></td>
          <td class="border p-2 dark:border-gray-700">
            <?php
            $status = $row['schedule_status'];
            $color  = $status=='Late'?'text-red-600':
                      ($status=='On Time'?'text-green-600':'text-purple-600');
            echo "<span class='font-semibold $color'>".$status."</span>";
            ?>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center p-4 text-gray-600 dark:text-gray-300">Tidak ada data</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="flex justify-center mt-4 gap-2">
      <?php for($i=1; $i<=$totalPages; $i++):
        $query = http_build_query([
            'page'=>'attendance_log','p'=>$i,
            'from'=>$from,'to'=>$to,'search'=>$search
        ]);
        $active = ($i==$page) ? 'bg-blue-600 text-white' :
                   'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200';
      ?>
      <a href="index.php?<?= $query ?>"
         class="px-3 py-1 rounded <?= $active ?> hover:bg-blue-500 hover:text-white transition">
         <?= $i ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
