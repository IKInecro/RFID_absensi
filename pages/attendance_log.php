<?php
// pages/attendance_log.php
// Polished version: improved UI + stable refresh + better dark theme
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Pagination setup
$limit  = 10;
$page   = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$name   = trim($_GET['name'] ?? '');
$class  = trim($_GET['class'] ?? '');
$card   = trim($_GET['card'] ?? '');
$device = trim($_GET['device'] ?? '');
$status = trim($_GET['status'] ?? '');

// WHERE builder
$whereParts = [];
$types = '';
$params = [];

function valid_date($d) {
  return DateTime::createFromFormat('Y-m-d', $d) && DateTime::createFromFormat('Y-m-d', $d)->format('Y-m-d') === $d;
}

if (valid_date($from) && valid_date($to)) {
  $whereParts[] = "DATE(al.timestamp) BETWEEN ? AND ?";
  $types .= 'ss';
  $params[] = $from;
  $params[] = $to;
}

$filters = [
  ['s.name', $name],
  ['s.class', $class],
  ['al.card_id', $card],
  ['al.device_id', $device],
];
foreach ($filters as [$col, $val]) {
  if ($val !== '') {
    $whereParts[] = "$col LIKE ?";
    $types .= 's';
    $params[] = "%$val%";
  }
}

if ($status !== '') {
  $whereParts[] = "al.schedule_status = ?";
  $types .= 's';
  $params[] = $status;
}

$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// helper for binding
function bind_stmt($stmt, $types, &$params) {
  if ($types === '') return;
  $bind = [$types];
  foreach ($params as &$p) $bind[] = &$p;
  call_user_func_array([$stmt, 'bind_param'], $bind);
}

// count rows
$totalRows = 0;
$countSql = "SELECT COUNT(*) AS c
             FROM attendance_log AS al
             LEFT JOIN students AS s ON s.id = al.student_id
             $whereSql";
$stmt = $conn->prepare($countSql);
if ($types) bind_stmt($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();
$totalRows = $res ? intval($res->fetch_assoc()['c'] ?? 0) : 0;
$stmt->close();

$totalPages = max(1, ceil($totalRows / $limit));

// data query
$dataSql = "SELECT al.*, s.name, s.class, s.profile_pic
            FROM attendance_log AS al
            LEFT JOIN students AS s ON s.id = al.student_id
            $whereSql
            ORDER BY al.timestamp DESC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($dataSql);
$dataParams = $params;
$typesData = $types . 'ii';
$dataParams[] = $limit;
$dataParams[] = $offset;
bind_stmt($stmt, $typesData, $dataParams);
$stmt->execute();
$result = $stmt->get_result();

$rowsHtml = '';
if ($result && $result->num_rows > 0) {
  while ($r = $result->fetch_assoc()) {
    $pic = $r['profile_pic'] ? 'uploads/' . basename($r['profile_pic']) : 'assets/img/default-avatar.png';
    if (!file_exists(__DIR__ . '/../' . $pic)) $pic = 'assets/img/default-avatar.png';

    $statusTxt = htmlspecialchars($r['schedule_status'] ?: 'Tidak Diketahui');
    $statusClass = match ($statusTxt) {
      'On Time' => 'bg-green-100 text-green-700',
      'Toleransi' => 'bg-yellow-100 text-yellow-700',
      'Late' => 'bg-red-100 text-red-700',
      'Libur' => 'bg-blue-100 text-blue-700',
      default => 'bg-gray-200 text-gray-700'
    };

    $rowsHtml .= "
      <tr class='hover:bg-gray-100 dark:hover:bg-gray-700 transition'>
        <td class='p-3 border-b border-gray-700 text-sm'>" . htmlspecialchars($r['timestamp']) . "</td>
        <td class='p-3 border-b border-gray-700'><img src='" . htmlspecialchars($pic) . "' class='w-10 h-10 rounded-full object-cover'></td>
        <td class='p-3 border-b border-gray-700 font-medium text-gray-800 dark:text-gray-200'>" . htmlspecialchars($r['name'] ?? '-') . "</td>
        <td class='p-3 border-b border-gray-700 text-gray-600 dark:text-gray-300'>" . htmlspecialchars($r['class'] ?? '-') . "</td>
        <td class='p-3 border-b border-gray-700 text-gray-600 dark:text-gray-300'>" . htmlspecialchars($r['card_id'] ?? '-') . "</td>
        <td class='p-3 border-b border-gray-700 text-gray-600 dark:text-gray-300'>" . htmlspecialchars($r['device_id'] ?? '-') . "</td>
        <td class='p-3 border-b border-gray-700'>
          <span class='px-2 py-1 rounded-lg text-xs font-semibold $statusClass'>$statusTxt</span>
        </td>
      </tr>
    ";
  }
} else {
  $rowsHtml .= "<tr><td colspan='7' class='text-center p-6 text-gray-500 dark:text-gray-400'>Tidak ada data</td></tr>";
}
$stmt->close();

// partial AJAX refresh
if (isset($_GET['partial']) && $_GET['partial'] == '1') {
  echo $rowsHtml;
  exit;
}

$params = http_build_query($_GET);
$base = $_GET;
unset($base['p']);
$prev = max(1, $page - 1);
$next = min($totalPages, $page + 1);
$prevUrl = 'index.php?' . http_build_query(array_merge($base, ['p' => $prev]));
$nextUrl = 'index.php?' . http_build_query(array_merge($base, ['p' => $next]));
?>
<style>
  .filter-form input, .filter-form select {
    background-color: #1f2937;
    color: #e5e7eb;
    border: 1px solid #374151;
    transition: all .2s;
  }
  .filter-form input:focus, .filter-form select:focus {
    outline: none;
    border-color: #3b82f6;
    background-color: #111827;
  }
  .table-wrapper {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
  }
  .pagination a {
    background: #374151;
    color: #e5e7eb;
    padding: 6px 10px;
    border-radius: 6px;
    margin: 0 2px;
    font-size: 14px;
    transition: 0.2s;
  }
  .pagination a:hover {
    background: #2563eb;
    color: #fff;
  }
</style>

<div class="space-y-8">
  <!-- Filter -->
  <form method="GET" action="index.php" class="filter-form bg-gray-800 p-5 rounded-xl grid grid-cols-1 sm:grid-cols-3 md:grid-cols-6 gap-3 shadow-md">
    <input type="hidden" name="page" value="attendance_log">
    <div><label>Dari</label><input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="p-2 rounded w-full"></div>
    <div><label>Sampai</label><input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="p-2 rounded w-full"></div>
    <div><label>Nama</label><input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="p-2 rounded w-full"></div>
    <div><label>Kelas</label><input type="text" name="class" value="<?= htmlspecialchars($class) ?>" class="p-2 rounded w-full"></div>
    <div><label>Card ID</label><input type="text" name="card" value="<?= htmlspecialchars($card) ?>" class="p-2 rounded w-full"></div>
    <div><label>Device</label><input type="text" name="device" value="<?= htmlspecialchars($device) ?>" class="p-2 rounded w-full"></div>
    <div><label>Status</label>
      <select name="status" class="p-2 rounded w-full">
        <option value="">Semua</option>
        <?php foreach (['On Time','Toleransi','Late','Libur','Out of Schedule'] as $s): ?>
          <option value="<?= $s ?>" <?= $status == $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Filter</button>
    </div>
    <div class="flex items-end">
      <a href="export_attendance_csv.php?<?= $params ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">⬇️ Export CSV</a>
    </div>
  </form>

  <!-- Table -->
  <div class="table-wrapper bg-gray-800 p-6 rounded-xl">
    <h2 class="text-xl font-semibold mb-3 text-gray-200">Riwayat Absensi</h2>
    <div class="mb-3 text-sm text-gray-400">
      Menampilkan halaman <?= $page ?> dari <?= $totalPages ?> — total <?= $totalRows ?> entri.
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-700 text-gray-200">
            <th class="p-3 text-left">Waktu</th>
            <th class="p-3 text-left">Foto</th>
            <th class="p-3 text-left">Nama</th>
            <th class="p-3 text-left">Kelas</th>
            <th class="p-3 text-left">Card ID</th>
            <th class="p-3 text-left">Device</th>
            <th class="p-3 text-left">Status Jadwal</th>
          </tr>
        </thead>
        <tbody id="attendance-table">
          <?= $rowsHtml ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center pagination">
      <div>
        <?php if ($page > 1): ?>
          <a href="<?= htmlspecialchars($prevUrl) ?>">← Prev</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= htmlspecialchars($nextUrl) ?>">Next →</a>
        <?php endif; ?>
      </div>
      <div class="text-sm text-gray-400">Halaman <?= $page ?> / <?= $totalPages ?></div>
    </div>
  </div>
</div>

<script>
setInterval(() => {
  const url = new URL(window.location.href);
  url.searchParams.set('partial', '1');
  fetch(url, { cache: 'no-store' })
    .then(r => r.text())
    .then(html => {
      const tbody = document.querySelector('#attendance-table');
      if (tbody && html.startsWith('<tr')) tbody.innerHTML = html;
    })
    .catch(err => console.error('Auto-refresh error:', err));
}, 5000);
</script>
