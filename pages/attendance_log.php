<?php
// pages/attendance_log.php — Class Grouping View
include_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

function esc($s)
{
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// --- CONFIG / FILTER ---
$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d');
$to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';

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

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch All Data (No Limit)
$sql = "SELECT a.*, s.name, s.class, s.profile_pic 
        FROM attendance_log a 
        JOIN students s ON a.card_id = s.card_id 
        $whereSql 
        ORDER BY s.class ASC, a.timestamp DESC";
$q = $conn->query($sql);

// Group by Class
$groupedData = [];
$totalRows = 0;
if ($q) {
  while ($row = $q->fetch_assoc()) {
    $cls = $row['class'] ?: 'Tanpa Kelas';
    if (!isset($groupedData[$cls])) {
      $groupedData[$cls] = [];
    }
    $groupedData[$cls][] = $row;
    $totalRows++;
  }
}

// Summary Stats (Today)
$today = date('Y-m-d');
$summary = ['total' => 0, 'late' => 0, 'ontime' => 0];
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

$kelasList = ['XII IPA 1', 'XII IPA 2', 'XII IPS 1', 'XII IPS 2', 'XI IPA 1', 'XI IPA 2', 'XI IPS 1', 'XI IPS 2', 'X 1', 'X 2', 'X 3', 'X 4'];

// Helper for Active Button State
function isRange($f, $t, $type)
{
  $today = date('Y-m-d');
  if ($type == 'today')
    return $f == $today && $t == $today;
  if ($type == 'yesterday') {
    $y = date('Y-m-d', strtotime('-1 day'));
    return $f == $y && $t == $y;
  }
  if ($type == 'week') {
    $w = date('Y-m-d', strtotime('-7 days'));
    return $f == $w && $t == $today; // rough check
  }
  if ($type == 'month') {
    $m = date('Y-m-01');
    return $f == $m && $t == $today; // rough check
  }
  return false;
}
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Log Absensi</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Riwayat kehadiran per kelas.</p>
    </div>
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
    <form id="filterForm" method="GET" action="index.php" class="space-y-4">
      <input type="hidden" name="page" value="attendance_log">

      <!-- Quick Date Buttons -->
      <div class="flex flex-wrap gap-2">
        <?php
        $btnClass = "px-3 py-1.5 text-xs font-medium rounded-lg transition-colors";
        $activeClass = "bg-blue-600 text-white shadow-md shadow-blue-500/30";
        $inactiveClass = "bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600";
        ?>
        <button type="button" onclick="setDateRange('today')"
          class="<?= $btnClass ?> <?= isRange($from, $to, 'today') ? $activeClass : $inactiveClass ?>">Hari Ini</button>
        <button type="button" onclick="setDateRange('yesterday')"
          class="<?= $btnClass ?> <?= isRange($from, $to, 'yesterday') ? $activeClass : $inactiveClass ?>">Kemarin</button>
        <button type="button" onclick="setDateRange('week')"
          class="<?= $btnClass ?> <?= isRange($from, $to, 'week') ? $activeClass : $inactiveClass ?>">7 Hari
          Terakhir</button>
        <button type="button" onclick="setDateRange('month')"
          class="<?= $btnClass ?> <?= isRange($from, $to, 'month') ? $activeClass : $inactiveClass ?>">Bulan
          Ini</button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Date Range -->
        <div class="sm:col-span-2 space-y-1">
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Rentang Tanggal</label>
          <div class="flex items-center gap-2">
            <input type="date" id="dateFrom" name="from" value="<?= esc($from) ?>"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            <span class="text-gray-400">-</span>
            <input type="date" id="dateTo" name="to" value="<?= esc($to) ?>"
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
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
        <button type="button" onclick="openExportModal()"
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export CSV
        </button>
        <a href="index.php?page=attendance_log"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors text-sm">Reset</a>
        <button type="submit"
          class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-500/30 text-sm">Filter</button>
      </div>
    </form>
  </div>

  <!-- Class Cards -->
  <?php if (empty($groupedData)): ?>
    <div
      class="p-12 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
      <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
      </svg>
      <p class="text-lg font-medium">Tidak ada data absensi.</p>
    </div>
  <?php else: ?>
    <!-- 2 Column Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($groupedData as $className => $students):
        $count = count($students);
        $lateCount = 0;
        foreach ($students as $s)
          if ($s['schedule_status'] == 'Late')
            $lateCount++;
        ?>
        <div
          class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md h-fit">
          <!-- Card Header -->
          <button onclick="toggleCard('<?= md5($className) ?>')"
            class="w-full flex items-center justify-between p-5 bg-gray-50/50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-4">
              <div
                class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg">
                <?= substr($className, 0, 2) ?>
              </div>
              <div class="text-left">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= esc($className) ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= $count ?> Siswa Hadir</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <?php if ($lateCount > 0): ?>
                <span
                  class="px-3 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full text-xs font-bold">
                  <?= $lateCount ?> Telat
                </span>
              <?php endif; ?>
              <svg id="icon-<?= md5($className) ?>"
                class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </button>

          <!-- Card Body -->
          <div id="body-<?= md5($className) ?>" class="hidden border-t border-gray-100 dark:border-gray-700">
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/20 text-xs uppercase text-gray-500 dark:text-gray-400">
                  <tr>
                    <th class="p-4">Waktu</th>
                    <th class="p-4">Siswa</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                  <?php foreach ($students as $row):
                    $statusColor = match ($row['schedule_status']) {
                      'On Time' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                      'Late' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                      'Toleransi' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                      default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'
                    };
                    $profile = $row['profile_pic'] ? 'uploads/' . $row['profile_pic'] : 'assets/img/default-avatar.png';
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                      <td class="p-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900 dark:text-white"><?= date('H:i', strtotime($row['timestamp'])) ?>
                        </div>
                        <div class="text-xs text-gray-500"><?= date('d M', strtotime($row['timestamp'])) ?></div>
                      </td>
                      <td class="p-4">
                        <div class="flex items-center gap-3">
                          <img src="<?= esc($profile) ?>"
                            class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                            onerror="this.src='assets/img/default-avatar.png'">
                          <div>
                            <div class="font-medium text-gray-900 dark:text-white"><?= esc($row['name']) ?></div>
                            <div class="text-xs text-gray-500 font-mono"><?= esc($row['card_id']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="p-4">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                          <?= esc($row['schedule_status']) ?>
                        </span>
                      </td>
                      <td class="p-4 text-right">
                        <button onclick="openEditStatusModal(<?= $row['id'] ?>, '<?= esc($row['schedule_status']) ?>')"
                          class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">Edit</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
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
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Edit Status Absensi</h3>
          <select name="new_status" id="edit_status"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="On Time">On Time</option>
            <option value="Late">Late</option>
            <option value="Toleransi">Toleransi</option>
            <option value="Sakit">Sakit</option>
            <option value="Izin">Izin</option>
            <option value="Alpha">Alpha</option>
          </select>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
          <button type="submit"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
          <button type="button" onclick="closeEditStatusModal()"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
  aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
      onclick="closeExportModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div
      class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <form action="export_attendance_csv.php" method="GET">
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Export Data Absensi</h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
              <select name="class"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelasList as $k): ?>
                  <option value="<?= esc($k) ?>"><?= esc($k) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="<?= date('Y-m-d') ?>"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="<?= date('Y-m-d') ?>"
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
          <button type="submit"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">Download
            CSV</button>
          <button type="button" onclick="closeExportModal()"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function toggleCard(id) {
    const body = document.getElementById('body-' + id);
    const icon = document.getElementById('icon-' + id);
    body.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
  }
  function openEditStatusModal(id, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_status').value = status;
    document.getElementById('editStatusModal').classList.remove('hidden');
  }
  function closeEditStatusModal() {
    document.getElementById('editStatusModal').classList.add('hidden');
  }

  function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
  }
  function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
  }

  function setDateRange(type) {
    const today = new Date();
    let from = new Date();
    let to = new Date();

    if (type === 'today') {
      // defaults are already today
    } else if (type === 'yesterday') {
      from.setDate(today.getDate() - 1);
      to.setDate(today.getDate() - 1);
    } else if (type === 'week') {
      from.setDate(today.getDate() - 7);
    } else if (type === 'month') {
      from.setDate(1);
    }

    // Format YYYY-MM-DD
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = fmt(from);
    document.getElementById('dateTo').value = fmt(to);

    // Auto submit
    document.getElementById('filterForm').submit();
  }
</script>