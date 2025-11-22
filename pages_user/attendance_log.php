<?php
// pages_user/attendance_log.php
// Read-only version for Users
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Pagination setup
$limit = 10;
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$name = trim($_GET['name'] ?? '');
$class = trim($_GET['class'] ?? '');
$card = trim($_GET['card'] ?? '');
$device = trim($_GET['device'] ?? '');
$status = trim($_GET['status'] ?? '');

// WHERE builder
$whereParts = [];
$types = '';
$params = [];

function valid_date($d)
{
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
function bind_stmt($stmt, $types, &$params)
{
    if ($types === '')
        return;
    $bind = [$types];
    foreach ($params as &$p)
        $bind[] = &$p;
    call_user_func_array([$stmt, 'bind_param'], $bind);
}

// count rows
$totalRows = 0;
$countSql = "SELECT COUNT(*) AS c
             FROM attendance_log AS al
             LEFT JOIN students AS s ON s.id = al.student_id
             $whereSql";
$stmt = $conn->prepare($countSql);
if ($types)
    bind_stmt($stmt, $types, $params);
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
        if (!file_exists(__DIR__ . '/../' . $pic))
            $pic = 'assets/img/default-avatar.png';

        $statusTxt = htmlspecialchars($r['schedule_status'] ?: 'Tidak Diketahui');
        $statusClass = match ($statusTxt) {
            'On Time' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'Toleransi' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'Late' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'Libur' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
        };

        $rowsHtml .= "
      <tr class='hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0'>
        <td class='p-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap'>" . htmlspecialchars($r['timestamp']) . "</td>
        <td class='p-4'><div class='w-10 h-10 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700'><img src='" . htmlspecialchars($pic) . "' class='w-full h-full object-cover'></div></td>
        <td class='p-4 font-medium text-gray-900 dark:text-white'>" . htmlspecialchars($r['name'] ?? '-') . "</td>
        <td class='p-4 text-gray-600 dark:text-gray-400'>" . htmlspecialchars($r['class'] ?? '-') . "</td>
        <td class='p-4 text-gray-500 dark:text-gray-400 font-mono text-xs'>" . htmlspecialchars($r['card_id'] ?? '-') . "</td>
        <td class='p-4 text-gray-500 dark:text-gray-400 text-xs'>" . htmlspecialchars($r['device_id'] ?? '-') . "</td>
        <td class='p-4'>
          <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $statusClass'>$statusTxt</span>
        </td>
      </tr>
    ";
    }
} else {
    $rowsHtml .= "<tr><td colspan='7' class='text-center p-8 text-gray-500 dark:text-gray-400'>Tidak ada data absensi yang ditemukan</td></tr>";
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

<div class="space-y-4 animate-fade-in">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Riwayat Absensi</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Log lengkap aktivitas absensi siswa.</p>
        </div>
        <a href="export_attendance_csv.php?<?= $params ?>"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition-colors shadow-sm shadow-green-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Summary Cards -->
    <?php
    // Calculate summary stats based on current filter
    $summarySql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN al.schedule_status = 'On Time' THEN 1 ELSE 0 END) as on_time,
    SUM(CASE WHEN al.schedule_status = 'Late' THEN 1 ELSE 0 END) as late
    FROM attendance_log AS al
    LEFT JOIN students AS s ON s.id = al.student_id
    $whereSql";

    $summary = $conn->query($summarySql)->fetch_assoc();
    ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Data</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?= number_format($summary['total'] ?? 0) ?></h3>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl text-green-600 dark:text-green-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tepat Waktu</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?= number_format($summary['on_time'] ?? 0) ?></h3>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-xl text-red-600 dark:text-red-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Terlambat</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($summary['late'] ?? 0) ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form method="GET" action="index.php"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
            <input type="hidden" name="page" value="attendance_log">

            <div class="xl:col-span-2 space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Rentang Tanggal</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
            </div>

            <div class="xl:col-span-2 space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari Nama / Kelas</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Nama Siswa..."
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <option value="">Semua</option>
                    <option value="On Time" <?= $status == 'On Time' ? 'selected' : '' ?>>Tepat Waktu</option>
                    <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="Toleransi" <?= $status == 'Toleransi' ? 'selected' : '' ?>>Toleransi</option>
                </select>
            </div>

            <div class="xl:col-span-2 flex items-end gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
                    Filter
                </button>
                <a href="index.php?page=attendance_log"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 font-semibold">Waktu</th>
                        <th class="p-3 font-semibold">Nama Siswa</th>
                        <th class="p-3 font-semibold">Kelas</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php while ($r = $result->fetch_assoc()):
                        $statusClass = $r['status'] == 'On Time'
                            ? 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800'
                            : 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';

                        $icon = $r['status'] == 'On Time'
                            ? '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>'
                            : '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="p-3 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span
                                        class="font-bold text-gray-900 dark:text-white"><?= date('H:i:s', strtotime($r['timestamp'])) ?></span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400"><?= date('d M Y', strtotime($r['timestamp'])) ?></span>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">
                                        <?= strtoupper(substr($r['student_name'], 0, 1)) ?>
                                    </div>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($r['student_name']) ?></span>
                                </div>
                            </td>
                            <td class="p-3">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    <?= htmlspecialchars($r['student_class']) ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border <?= $statusClass ?>">
                                    <?= $icon ?>
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td class="p-3 text-sm text-gray-500 dark:text-gray-400">
                                <?= htmlspecialchars($r['schedule_status'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?page=attendance_log&p=<?= $i ?>&from=<?= $from ?>&to=<?= $to ?>&name=<?= $name ?>&status=<?= $status ?>"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors
             <?= $i == $page
                 ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                 : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php if ($totalRows == 0): ?>
            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <p class="text-lg font-medium">Tidak ada data absensi</p>
                <p class="text-sm mt-1">Coba ubah filter atau tanggal pencarian.</p>
            </div>
        <?php endif; ?>
    </div>
</div>