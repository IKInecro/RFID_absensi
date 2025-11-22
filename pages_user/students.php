<?php
// pages_user/students.php
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
?>

<div class="space-y-4 animate-fade-in">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Data Siswa</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Lihat data siswa dan kartu RFID.</p>
        </div>
    </div>

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
        <div id="grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" aria-live="polite"
            aria-busy="false">
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
                                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate"><?= esc($row['name']) ?>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate mb-1"><?= esc($row['class']) ?></p>
                                <div
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-mono">
                                    <?= esc($row['card_id']) ?>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>ID: #<?= intval($row['id']) ?></span>
                            <span><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                        </div>
                    </article>
                <?php endwhile; else: ?>
                <div class="col-span-full py-12 text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada data siswa</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Silakan ubah filter pencarian.</p>
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

<!-- Live Update Script (Read-only) -->
<script src="assets/js/live_update.js"></script>
<script>
    (function () {
        const grid = document.getElementById('grid');
        const LIMIT = <?= intval($limit) ?>;

        function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

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
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>ID: #${id}</span>
            <span>${escapeHtml(r.created_at || '')}</span>
          </div>
        </article>`;

                    grid.insertAdjacentHTML('afterbegin', html);
                    while (grid.children.length > LIMIT) grid.removeChild(grid.lastChild);
                }
            });
        }

        // Start polling immediately for users
        if (window.LiveUpdates) startStudentPolling();

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
    })();
</script>