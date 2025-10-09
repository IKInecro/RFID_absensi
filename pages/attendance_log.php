<?php
// pages/attendance_log.php
// FULL REPLACE - Prepared statements, safe filtering, pagination, partial AJAX refresh support
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Pagination
$limit = 10;
$page  = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// Raw inputs (trimmed)
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$name   = trim($_GET['name'] ?? '');
$class  = trim($_GET['class'] ?? '');
$card   = trim($_GET['card'] ?? '');
$device = trim($_GET['device'] ?? '');
$status = trim($_GET['status'] ?? '');

// Build WHERE dynamically with prepared params
$whereParts = [];
$types = '';
$params = [];

// validate dates (YYYY-MM-DD)
$validFrom = false; $validTo = false;
if ($from) {
    $d = DateTime::createFromFormat('Y-m-d', $from);
    if ($d && $d->format('Y-m-d') === $from) $validFrom = true;
}
if ($to) {
    $d = DateTime::createFromFormat('Y-m-d', $to);
    if ($d && $d->format('Y-m-d') === $to) $validTo = true;
}
if ($validFrom && $validTo) {
    $whereParts[] = "DATE(al.timestamp) BETWEEN ? AND ?";
    $types .= 'ss';
    $params[] = $from;
    $params[] = $to;
}

// name, class, card, device -> LIKE
if ($name !== '') {
    $whereParts[] = "s.name LIKE ?";
    $types .= 's';
    $params[] = '%' . $name . '%';
}
if ($class !== '') {
    $whereParts[] = "s.class LIKE ?";
    $types .= 's';
    $params[] = '%' . $class . '%';
}
if ($card !== '') {
    $whereParts[] = "al.card_id LIKE ?";
    $types .= 's';
    $params[] = '%' . $card . '%';
}
if ($device !== '') {
    $whereParts[] = "al.device_id LIKE ?";
    $types .= 's';
    $params[] = '%' . $device . '%';
}
if ($status !== '') {
    $whereParts[] = "al.schedule_status = ?";
    $types .= 's';
    $params[] = $status;
}

$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// helper to bind params dynamically (mysqli requires references)
function bind_params_stmt($stmt, $types, &$params) {
    if ($types === '') return;
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        // ensure param is passed by reference
        $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// 1) Count total rows
$totalRows = 0;
$countSql = "SELECT COUNT(*) AS c
             FROM attendance_log AS al
             LEFT JOIN students AS s ON s.id = al.student_id
             $whereSql";
$stmt = $conn->prepare($countSql);
if ($stmt === false) {
    die("DB prepare failed: " . htmlspecialchars($conn->error));
}
if ($types) bind_params_stmt($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    $row = $res->fetch_assoc();
    $totalRows = intval($row['c'] ?? 0);
}
$stmt->close();

$totalPages = $totalRows > 0 ? ceil($totalRows / $limit) : 1;

// 2) Query data with same where + limit/offset
$dataSql = "SELECT al.*, s.name, s.class, s.profile_pic
            FROM attendance_log AS al
            LEFT JOIN students AS s ON s.id = al.student_id
            $whereSql
            ORDER BY al.timestamp DESC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($dataSql);
if ($stmt === false) {
    die("DB prepare failed: " . htmlspecialchars($conn->error));
}

// bind params: first the filter params, then limit & offset as integers
$dataParams = $params;
$typesData = $types . 'ii';
$dataParams[] = $limit;
$dataParams[] = $offset;

bind_params_stmt($stmt, $typesData, $dataParams);
$stmt->execute();
$result = $stmt->get_result();

// Build rows HTML (used both for full page and partial AJAX)
$rowsHtml = '';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timestamp = htmlspecialchars($row['timestamp'] ?? '');
        $nameOut = htmlspecialchars($row['name'] ?? '-');
        $classOut = htmlspecialchars($row['class'] ?? '-');
        $cardOut = htmlspecialchars($row['card_id'] ?? '-');
        $deviceOut = htmlspecialchars($row['device_id'] ?? '-');
        $statusTxt = htmlspecialchars($row['schedule_status'] ?: 'Tidak Diketahui');

        // profile pic check - use basename to avoid traversal
        $profilePicRaw = $row['profile_pic'] ?? '';
        $profileFile = $profilePicRaw ? basename($profilePicRaw) : '';
        $uploadFullPath = __DIR__ . '/../uploads/' . $profileFile;
        $webProfilePath = 'assets/img/default-avatar.png';
        if ($profileFile && file_exists($uploadFullPath)) {
            // path relative to root (index.php is in root)
            $webProfilePath = 'uploads/' . $profileFile;
        }

        $rowsHtml .= '<tr class="border-b border-gray-200 dark:border-gray-700">';
        $rowsHtml .= '<td class="border p-2">' . $timestamp . '</td>';
        $rowsHtml .= '<td class="border p-2"><img src="' . htmlspecialchars($webProfilePath) . '" alt="Foto" class="w-10 h-10 rounded-full object-cover"></td>';
        $rowsHtml .= '<td class="border p-2">' . $nameOut . '</td>';
        $rowsHtml .= '<td class="border p-2">' . $classOut . '</td>';
        $rowsHtml .= '<td class="border p-2">' . $cardOut . '</td>';
        $rowsHtml .= '<td class="border p-2">' . $deviceOut . '</td>';
        $rowsHtml .= '<td class="border p-2 font-semibold">' . $statusTxt . '</td>';
        $rowsHtml .= '</tr>';
    }
} else {
    $rowsHtml .= '<tr><td colspan="7" class="text-center p-4">Tidak ada data</td></tr>';
}

$stmt->close();

// Support partial AJAX refresh: return only tbody HTML when ?partial=1
if (isset($_GET['partial']) && $_GET['partial'] == '1') {
    echo $rowsHtml;
    exit;
}

// Build params for links/export (preserve current GET)
$params = http_build_query($_GET);
$baseParams = $_GET;
unset($baseParams['p']);
$prevPage = max(1, $page - 1);
$nextPage = min($totalPages, $page + 1);
$prevUrl = 'index.php?' . http_build_query(array_merge($baseParams, ['p' => $prevPage]));
$nextUrl = 'index.php?' . http_build_query(array_merge($baseParams, ['p' => $nextPage]));
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
      <a href="export_attendance_csv.php?<?= $params ?>" class="bg-blue-600 text-white px-4 py-2 rounded">⬇️ Export CSV</a>
    </div>
  </form>

  <!-- Table -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Riwayat Absensi</h2>
    <div class="mb-3 text-sm text-gray-600 dark:text-gray-300">
      Menampilkan halaman <?= $page ?> dari <?= $totalPages ?> — total <?= $totalRows ?> entri.
    </div>

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
        <?= $rowsHtml ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <div>
        <?php if ($page > 1): ?>
          <a href="<?= htmlspecialchars($prevUrl) ?>" class="px-3 py-1 bg-gray-200 rounded">← Prev</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= htmlspecialchars($nextUrl) ?>" class="px-3 py-1 bg-gray-200 rounded ml-2">Next →</a>
        <?php endif; ?>
      </div>
      <div class="text-sm text-gray-600 dark:text-gray-300">Halaman <?= $page ?> / <?= $totalPages ?></div>
    </div>
  </div>
</div>

<!-- Auto refresh (partial update) -->
<script>
setInterval(() => {
  fetch('pages/attendance_log.php?partial=1', { cache: 'no-store' })
    .then(res => res.text())
    .then(html => {
      const tbody = document.querySelector('#attendance-table');
      if (tbody && html.startsWith('<tr')) tbody.innerHTML = html;
    })
    .catch(err => console.error('Auto-refresh error:', err));
}, 5000);
</script>

