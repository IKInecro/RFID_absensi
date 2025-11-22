<?php
// pages/attendance_log.php — Enhanced Filters (Class, RFID, Status)
include_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Helper escape
function esc($s)
{
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// --- CONFIG / FILTER / PAGINATION ---
$limit = 20;
$pageNo = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;

// Filter Params
$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d');
$to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';
$rfidFilter = isset($_GET['rfid']) ? trim($_GET['rfid']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build Query
$where = [];
if ($from && $to) {
  $where[] = "DATE(a.timestamp) BETWEEN '$from' AND '$to'";
}
if ($name) {
  $nameSql = $conn->real_escape_string($name);
  $where[] = "s.name LIKE '%$nameSql%'";
}
if ($classFilter) {
  $classSql = $conn->real_escape_string($classFilter);
  $where[] = "s.class = '$classSql'";
}
if ($rfidFilter) {
  $rfidSql = $conn->real_escape_string($rfidFilter);
  $where[] = "s.card_id LIKE '%$rfidSql%'";
}
if ($status) {
  $statusSql = $conn->real_escape_string($status);
  $where[] = "a.schedule_status = '$statusSql'";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count Total
$totalRows = 0;
$countSql = "SELECT COUNT(*) as c FROM attendance_log a JOIN students s ON a.card_id = s.card_id $whereSql";
$qTotal = $conn->query($countSql);
if ($qTotal) {
  $totalRows = intval($qTotal->fetch_assoc()['c'] ?? 0);
}

// Pagination
$totalPages = max(1, (int) ceil($totalRows / $limit));
$offset = ($pageNo - 1) * $limit;

// Fetch Data
$sql = "SELECT a.*, s.name, s.class, s.profile_pic 
        FROM attendance_log a 
        JOIN students s ON a.card_id = s.card_id 
        $whereSql 
        ORDER BY a.timestamp DESC 
        LIMIT $limit OFFSET $offset";
$q = $conn->query($sql);

// Summary Stats (Today)
$today = date('Y-m-d');
$summary = [
  'total' => 0,
  'late' => 0,
  'ontime' => 0
];
$qSum = $conn->query("SELECT schedule_status, COUNT(*) as c FROM attendance_log WHERE DATE(timestamp)='$today' GROUP BY schedule_status");
if ($qSum) {
  while ($row = $qSum->fetch_assoc()) {
    $st = $row['schedule_status'];
    $c = intval($row['c']);
    $summary['total'] += $c;
    if ($st == 'Late')
      $summary['late'] += $c;
    else
      $summary['ontime'] += $c;
  }
}

// Class List for Dropdown
$kelasList = [
  'XII IPA 1',
  'XII IPA 2',
  'XII IPS 1',
  'XII IPS 2',
  'XI IPA 1',
  'XI IPA 2',
  'XI IPS 1',
  'XI IPS 2',
  'X 1',
  'X 2',
  'X 3',
  'X 4'
];
?>

<div class="space-y-6 animate-fade-in">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Log Absensi</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Riwayat kehadiran siswa.</p>
    </div>

    <!-- Summary Cards (Mini) -->
    <div class="flex gap-4">
      <div class="bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-xl border border-green-100 dark:border-green-800">
        <span class="text-xs font-bold text-green-600 dark:text-green-400 uppercase">Hadir</span>
        <div class="text-xl font-bold text-gray-900 dark:text-white"><?= number_format($summary['ontime']) ?></div>
      </div>
      <div class="bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-xl border border-red-100 dark:border-red-800">
        <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase">Telat</span>
        <div class="text-xl font-bold text-gray-900 dark:text-white"><?= number_format($summary['late']) ?></div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
    <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <input type="hidden" name="page" value="attendance_log">

      <!-- Date Range -->
      <div class="sm:col-span-2 space-y-1">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Rentang Tanggal</label>
        <div class="flex items-center gap-2">
          <input type="date" name="from" value="<?= esc($from) ?>"
            class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
          <span class="text-gray-400">-</span>
          <input type="date" name="to" value="<?= esc($to) ?>"
            class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        </div>
      </div>

      <!-- Name Search -->
      <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nama Siswa</label>
        <input type="text" name="name" value="<?= esc($name) ?>" placeholder="Cari nama..."
          class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
      </div>

      <!-- Class Filter -->
      <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
        <select name="class"
          class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
          <option value="">Semua Kelas</option>
          <?php foreach ($kelasList as $k): ?>
            <option value="<?= esc($k) ?>" <?= $classFilter == $k ? 'selected' : '' ?>><?= esc($k) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- RFID Search -->
      <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">RFID / Card ID</label>
        <input type="text" name="rfid" value="<?= esc($rfidFilter) ?>" placeholder="ID Kartu..."
          class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono">
      </div>

      <!-- Status Filter -->
      <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
        <select name="status"
          class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
          <option value="">Semua Status</option>
          <option value="On Time" <?= $status == 'On Time' ? 'selected' : '' ?>>Tepat Waktu</option>
          <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Terlambat</option>
          <option value="Toleransi" <?= $status == 'Toleransi' ? 'selected' : '' ?>>Toleransi</option>
          <option value="Sakit" <?= $status == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
          <option value="Izin" <?= $status == 'Izin' ? 'selected' : '' ?>>Izin</option>
          <option value="Alpha" <?= $status == 'Alpha' ? 'selected' : '' ?>>Alpha</option>
        </select>
      </div>

      <!-- Buttons -->
      <div
        class="sm:col-span-2 lg:col-span-4 xl:col-span-6 flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 mt-2">
        <a href="index.php?page=attendance_log"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors text-sm">
          Reset Filter
        </a>
        <button type="submit"
          class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-500/30 text-sm">
          Terapkan Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Table Section -->
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
            <th class="p-4 font-semibold">Waktu</th>
            <th class="p-4 font-semibold">Siswa</th>
            <th class="p-4 font-semibold">Kelas</th>
            <th class="p-4 font-semibold">Status</th>
            <th class="p-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <?php if ($q && $q->num_rows > 0): ?>
            <?php while ($row = $q->fetch_assoc()):
              $statusColor = match ($row['schedule_status']) {
                'On Time' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'Late' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'Toleransi' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'
              };
              $profile = $row['profile_pic'] ? 'uploads/' . $row['profile_pic'] : 'assets/img/default-avatar.png';
              ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                <td class="p-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <span
                      class="font-bold text-gray-900 dark:text-white"><?= date('H:i', strtotime($row['timestamp'])) ?></span>
                    <span
                      class="text-xs text-gray-500 dark:text-gray-400"><?= date('d M Y', strtotime($row['timestamp'])) ?></span>
                  </div>
                </td>
                <td class="p-4">
                  <div class="flex items-center gap-3">
                    <img src="<?= esc($profile) ?>"
                      class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                      onerror="this.src='assets/img/default-avatar.png'">
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white"><?= esc($row['name']) ?></div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 font-mono"><?= esc($row['card_id']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                  <?= esc($row['class']) ?>
                </td>
                <td class="p-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                    <?= esc($row['schedule_status']) ?>
                  </span>
                </td>
                <td class="p-4 text-right">
                  <?php if ($role === 'admin'): ?>
                    <button onclick="openEditStatusModal(<?= $row['id'] ?>, '<?= esc($row['schedule_status']) ?>')"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      Edit Status
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                  </svg>
                  <p>Tidak ada data absensi yang ditemukan.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> sampai <span
                class="font-medium"><?= min($offset + $limit, $totalRows) ?></span> dari <span
                class="font-medium"><?= $totalRows ?></span> hasil
            </p>
          </div>
          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <?php for ($i = 1; $i <= $totalPages; $i++):
                $isActive = $i == $pageNo;
                $url = "index.php?page=attendance_log&p=$i&from=$from&to=$to&name=$name&class=$classFilter&rfid=$rfidFilter&status=$status";
                ?>
                <a href="<?= $url ?>"
                  class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $isActive ? 'z-10 bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>
            </nav>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Status Modal -->
<div id="editStatusModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
  aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
      onclick="closeEditStatusModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div
      class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <form action="action_attendance.php" method="POST">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" id="edit_id">
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Edit Status
                Absensi</h3>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Baru</label>
                <select name="new_status" id="edit_status"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm p-2 border">
                  <option value="On Time">On Time</option>
                  <option value="Late">Late</option>
                  <option value="Toleransi">Toleransi</option>
                  <option value="Sakit">Sakit</option>
                  <option value="Izin">Izin</option>
                  <option value="Alpha">Alpha</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="submit"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Simpan
          </button>
          <button type="button" onclick="closeEditStatusModal()"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Batal
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openEditStatusModal(id, currentStatus) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_status').value = currentStatus;
    document.getElementById('editStatusModal').classList.remove('hidden');
  }

  function closeEditStatusModal() {
    document.getElementById('editStatusModal').classList.add('hidden');
  }
</script>