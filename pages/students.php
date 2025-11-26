<?php
// pages/students.php
include_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// helper escape
function esc($s)
{
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// --- CONFIG / FILTER / PAGINATION ---
$limit = 15;
$pageNo = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';

$search_sql = $conn->real_escape_string($search);
$class_sql = $conn->real_escape_string($classFilter);

$where = [];
if ($class_sql !== '')
  $where[] = "class = '{$class_sql}'";
if ($search_sql !== '')
  $where[] = "(name LIKE '%{$search_sql}%' OR card_id LIKE '%{$search_sql}%')";
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$hasFilter = ($class_sql !== '' || $search_sql !== '');
$perPage = $hasFilter ? 50 : $limit;
$offset = ($pageNo - 1) * $perPage;

$totalRows = 0;
$qTotal = $conn->query("SELECT COUNT(*) AS c FROM students {$whereSql}");
if ($qTotal) {
  $rt = $qTotal->fetch_assoc();
  $totalRows = intval($rt['c'] ?? 0);
}
$totalPages = max(1, (int) ceil($totalRows / max(1, $perPage)));

$q = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");

// Edit support
$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$edit_data = null;
if ($edit_id) {
  $r = $conn->query("SELECT * FROM students WHERE id=" . intval($edit_id) . " LIMIT 1");
  if ($r && $r->num_rows)
    $edit_data = $r->fetch_assoc();
}

// class list
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

// modes
$reg_mode = 0;
$test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
  $s = $set_q->fetch_assoc();
  $reg_mode = intval($s['reg_mode']);
  $test_mode = intval($s['test_mode']);
}
if ($reg_mode === 1)
  $test_mode = 0;

// AJAX endpoint (for settings + students)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
  $students = [];
  $qq = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
  if ($qq)
    while ($r = $qq->fetch_assoc())
      $students[] = $r;
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => true, 'modes' => ['reg_mode' => $reg_mode, 'test_mode' => $test_mode], 'total' => $totalRows, 'page' => $pageNo, 'students' => $students], JSON_UNESCAPED_UNICODE);
  exit;
}
?>

<div class="space-y-4 animate-fade-in">

  <!-- Header & Mode Toggle -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Data Siswa</h1>
      <p class="text-gray-500 dark:text-gray-400 text-sm">Kelola data siswa dan kartu RFID.</p>
    </div>
  </div>
  <!-- Form Section -->
  <?php if ($role === 'admin'): ?>
    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
      <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <?php if ($edit_data): ?>
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
          </svg> Edit Siswa
        <?php else: ?>
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
          </svg> Tambah Siswa Baru
        <?php endif; ?>
      </h2>

      <form action="<?= esc('action_student.php') ?>" method="post" enctype="multipart/form-data" class="space-y-3"
        novalidate>
        <input type="hidden" name="id" value="<?= esc($edit_data['id'] ?? '') ?>">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2 space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">Nama Lengkap</label>
            <input id="name" name="name" required value="<?= esc($edit_data['name'] ?? '') ?>"
              class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              placeholder="Masukkan nama lengkap">
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="class">Kelas</label>
            <select id="class" name="class" required
              class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              <option value="">Pilih Kelas</option>
              <?php foreach ($kelasList as $k): ?>
                <option value="<?= esc($k) ?>" <?= (isset($edit_data['class']) && $edit_data['class'] == $k) ? 'selected' : '' ?>>
                  <?= esc($k) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="card_id">Card ID (RFID)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.25 8.25h19.5M2.25 9.75h19.5M2.25 12h19.5m-16.5 5.25h6m-6 2.25h6M12 21.75h7.5a2.25 2.25 0 002.25-2.25V5.25a2.25 2.25 0 00-2.25-2.25H4.5A2.25 2.25 0 002.25 5.25v14.25A2.25 2.25 0 004.5 21.75h7.5" />
                </svg>
              </div>
              <input id="card_id" name="card_id" required value="<?= esc($edit_data['card_id'] ?? '') ?>"
                class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 pl-10 pr-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono"
                placeholder="Scan kartu...">
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Scan kartu pada reader untuk mengisi otomatis.</p>
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="status">Status Siswa</label>
            <select id="status" name="status"
              class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              <option value="active" <?= (isset($edit_data['status']) && $edit_data['status'] == 'active') ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= (isset($edit_data['status']) && $edit_data['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <div
            class="flex items-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
            <div
              class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
              <?php if ($edit_data && !empty($edit_data['profile_pic']) && $edit_data['profile_pic'] !== 'default.png'): ?>
                <img src="<?= esc('uploads/' . $edit_data['profile_pic']) ?>" alt="Foto" class="object-cover w-full h-full">
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-2xl">
                  <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="profile_pic">Foto
                Profil</label>
              <input id="profile_pic" type="file" name="profile_pic" accept="image/*"
                class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700 mt-4">
          <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors shadow-sm shadow-blue-500/30">
            <?= $edit_data ? 'Update Data' : 'Simpan Data' ?>
          </button>
          <?php if ($edit_data): ?>
            <a href="index.php?page=students"
              class="px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition font-medium">Batal</a>
          <?php endif; ?>
          <div class="text-sm text-gray-500 dark:text-gray-400 ml-auto hidden sm:block">Total siswa: <span
              class="font-bold text-gray-900 dark:text-white"><?= intval($totalRows) ?></span></div>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <!-- Filter Section -->
  <section
    class="flex flex-col md:flex-row items-start md:items-center gap-3 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
    <form method="GET" action="index.php" class="flex flex-col sm:flex-row gap-3 w-full" aria-label="Filter siswa">
      <input type="hidden" name="page" value="students">

      <div class="relative w-full sm:w-48">
        <select name="class"
          class="w-full appearance-none rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
          <option value="">Semua Kelas</option>
          <?php foreach ($kelasList as $k): ?>
            <option value="<?= esc($k) ?>" <?= $classFilter == $k ? 'selected' : '' ?>><?= esc($k) ?></option>
          <?php endforeach; ?>
        </select>
        <div
          class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500 dark:text-gray-400">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </div>
      </div>

      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input type="text" name="search" placeholder="Cari nama atau ID kartu..." value="<?= esc($search) ?>"
          class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 pl-10 pr-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
      </div>

      <button type="submit"
        class="bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
        Filter
      </button>
      <?php if ($hasFilter): ?>
        <a href="index.php?page=students"
          class="flex items-center justify-center px-4 py-2.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition font-medium border border-transparent hover:border-red-200 dark:hover:border-red-800">
          Reset
        </a>
      <?php endif; ?>
    </form>
  </section>

  <!-- Students Grid -->
  <section>
    <div id="grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" aria-live="polite" aria-busy="false">
      <?php if ($q && $q->num_rows):
        while ($row = $q->fetch_assoc()):
          $profile = $row['profile_pic'] ?? '';
          $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/' . $profile;
          ?>
          <article
            class="student-card group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-200"
            data-id="<?= intval($row['id']) ?>">
            <div class="flex items-start gap-4">
              <div
                class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
                <img src="<?= esc($profilePath) ?>" alt="Foto"
                  class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300"
                  onerror="this.src='assets/img/default-avatar.png'">
              </div>

              <div class="flex-1 min-w-0">
                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate"><?= esc($row['name']) ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 truncate mb-1"><?= esc($row['class']) ?></p>
                <div
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-mono">
                  <?= esc($row['card_id']) ?>
                </div>
              </div>

              <?php if ($role === 'admin'): ?>
                <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <a href="index.php?page=students&edit=<?= intval($row['id']) ?>"
                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 transition"
                    title="Edit">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </a>
                  <a href="action_student.php?delete=<?= intval($row['id']) ?>" onclick="return confirm('Hapus siswa ini?')"
                    class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition"
                    title="Hapus">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endwhile; ?>
      <?php else: ?>
        <div
          class="col-span-1 md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center p-12 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 border-dashed">
          <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-full mb-4">
            <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada data siswa</h3>
          <p class="text-gray-500 dark:text-gray-400 mt-1">Silakan tambahkan siswa baru atau ubah filter pencarian.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (!$hasFilter && $totalPages > 1): ?>
      <div class="mt-8 flex justify-center">
        <nav class="inline-flex rounded-md shadow-sm isolate">
          <?php for ($i = 1; $i <= $totalPages; $i++):
            $href = 'index.php?page=students&p=' . intval($i) . ($classFilter ? '&class=' . urlencode($classFilter) : '') . ($search ? '&search=' . urlencode($search) : '');
            $isActive = $i == $pageNo;
            ?>
            <a href="<?= esc($href) ?>"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $isActive ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0' ?> <?= $i === 1 ? 'rounded-l-md' : '' ?> <?= $i === $totalPages ? 'rounded-r-md' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </nav>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Live Update Script -->
<script src="assets/js/live_update.js"></script>
<script>
  (function () {
    const regSwitch = document.getElementById('regSwitch');
    const modeLabel = document.getElementById('modeLabel');
    const grid = document.getElementById('grid');
    const LIMIT = <?= intval($limit) ?>;

    function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    async function refreshSettingsAndState() {
      try {
        const params = new URLSearchParams(window.location.search);
        params.set('ajax', '1');
        const url = 'index.php?page=students&' + params.toString();
        const res = await fetch(url, { cache: 'no-store' });
        if (!res.ok) return;
        const j = await res.json();
        const modes = j.modes || { reg_mode: 0, test_mode: 0 };

        if (regSwitch) {
          regSwitch.setAttribute('data-checked', modes.reg_mode ? '1' : '0');
          regSwitch.setAttribute('aria-checked', modes.reg_mode ? 'true' : 'false');
          modeLabel.innerText = modes.reg_mode ? 'Register' : (modes.test_mode ? 'Tester' : 'Normal');

          const isRegMode = parseInt(modes.reg_mode) === 1;
          const isTestMode = parseInt(modes.test_mode) === 1;

          // Update UI classes
          const dot = regSwitch.querySelector('.dot');
          if (dot) {
            if (isRegMode) dot.classList.add('translate-x-4');
            else dot.classList.remove('translate-x-4');
          }
          if (isRegMode) {
            regSwitch.classList.remove('bg-gray-200', 'dark:bg-gray-700');
            regSwitch.classList.add('bg-blue-600');

            modeLabel.className = 'text-xs font-bold uppercase tracking-wider px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
          } else {
            regSwitch.classList.remove('bg-blue-600');
            regSwitch.classList.add('bg-gray-200', 'dark:bg-gray-700');

            if (isTestMode) {
              modeLabel.className = 'text-xs font-bold uppercase tracking-wider px-2 py-1 rounded bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            } else {
              modeLabel.className = 'text-xs font-bold uppercase tracking-wider px-2 py-1 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
            }
          }
        }

        const reg = parseInt(modes.reg_mode) === 1;
        if (reg && window.LiveUpdates) startStudentPolling();
        else stopStudentPolling();
      } catch (e) { console.error(e); }
    }

    let studentPoll = null;
    function startStudentPolling() {
      if (studentPoll) return;
      if (!window.LiveUpdates) return;
      studentPoll = LiveUpdates.startLongPoll({
        url: 'api/updates.php?mode=students',
        paramNameForLast: 'last_id',
        getLastValue: function () {
          const first = grid.querySelector('.student-card');
          return first ? parseInt(first.getAttribute('data-id')) || 0 : 0;
        },
        onNew: function (payload) {
          if (!payload || !payload.item) return;
          const r = payload.item;
          const profile = r.profile_pic ? ('uploads/' + encodeURIComponent(r.profile_pic)) : 'assets/img/default-avatar.png';
          const id = parseInt(r.id) || 0;

          const html = `
        <article class="student-card group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-200 animate-pulse-once" data-id="${id}">
          <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
              <img src="${profile}" alt="Foto" class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-300" onerror="this.src='assets/img/default-avatar.png'">
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">${escapeHtml(r.name)}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 truncate mb-1">${escapeHtml(r.class)}</p>
              <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-mono">
                ${escapeHtml(r.card_id)}
              </div>
            </div>
            <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <a href="index.php?page=students&edit=${id}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              </a>
              <a href="action_student.php?delete=${id}" onclick="return confirm('Hapus siswa ini?')" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </a>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end text-xs text-gray-500 dark:text-gray-400">
            <span>${escapeHtml(r.created_at || '')}</span>
          </div>
        </article>`;

          grid.insertAdjacentHTML('afterbegin', html);
          while (grid.children.length > LIMIT) grid.removeChild(grid.lastChild);
        }
      });
    }
    function stopStudentPolling() {
      if (studentPoll && studentPoll.stop) studentPoll.stop();
      studentPoll = null;
    }

    async function toggleRegisterMode(newState) {
      try {
        const form = new FormData();
        form.append('toggle_reg_mode', newState ? '1' : '0');
        await fetch('action_register.php', { method: 'POST', body: form });
        await refreshSettingsAndState();
      } catch (e) { console.error(e); }
    }

    if (regSwitch) {
      regSwitch.addEventListener('click', (e) => {
        e.preventDefault();
        const cur = regSwitch.getAttribute('data-checked') === '1' ? 1 : 0;
        const next = cur ? 0 : 1;
        // Optimistic UI update handled in refreshSettingsAndState but we can do immediate feedback if needed
        toggleRegisterMode(next);
      });
      regSwitch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); regSwitch.click(); }
      });
    }

    // client-side quick filter
    (function clientFilter() {
      const input = document.querySelector('input[name="search"]');
      if (!input) return;
      input.addEventListener('input', function () {
        const term = (this.value || '').toLowerCase();
        const cards = grid.querySelectorAll('.student-card');
        cards.forEach(card => {
          const text = (card.textContent || '').toLowerCase();
          card.style.display = text.indexOf(term) !== -1 ? '' : 'none';
        });
      });
    })();

    // init
    (async function init() { await refreshSettingsAndState(); })();
  })();
</script>