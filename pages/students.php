<?php
// pages/students.php
// FULL REPLACE - Tailwind-based UI (vertical list), preserve features: filter, pagination, create/edit/delete, ajax, reg_mode toggle.
// If your project doesn't actually include Tailwind, tell me and I'll provide a non-Tailwind (custom CSS) version.

include_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// helper escape
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// --- CONFIG / FILTER / PAGINATION ---
$limit = 15;
$pageNo = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : '';

$search_sql = $conn->real_escape_string($search);
$class_sql = $conn->real_escape_string($classFilter);

$where = [];
if ($class_sql !== '') $where[] = "class = '{$class_sql}'";
if ($search_sql !== '') $where[] = "(name LIKE '%{$search_sql}%' OR card_id LIKE '%{$search_sql}%')";
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
$totalPages = max(1, (int)ceil($totalRows / max(1,$perPage)));

$q = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");

// Edit support
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM students WHERE id=" . intval($edit_id) . " LIMIT 1");
    if ($r && $r->num_rows) $edit_data = $r->fetch_assoc();
}

// class list
$kelasList = [
  'XII IPA 1','XII IPA 2','XII IPS 1','XII IPS 2',
  'XI IPA 1','XI IPA 2','XI IPS 1','XI IPS 2',
  'X 1','X 2','X 3','X 4'
];

// modes
$reg_mode = 0; $test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
    $s = $set_q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
}
if ($reg_mode === 1) $test_mode = 0;

// AJAX endpoint (for settings + students)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $students = [];
    $qq = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
    if ($qq) while ($r = $qq->fetch_assoc()) $students[] = $r;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>true,'modes'=>['reg_mode'=>$reg_mode,'test_mode'=>$test_mode],'total'=>$totalRows,'page'=>$pageNo,'students'=>$students], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!doctype html>
<html lang="id" class="antialiased">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data Siswa</title>
<!--
  NOTE:
  This file uses Tailwind CSS utility classes for styling.
  If your project doesn't include Tailwind, tell me and I'll provide an equivalent using custom CSS or Bootstrap.
-->
<style>
/* Minimal fallback to ensure fonts & smoothness even without Tailwind; doesn't replace Tailwind utilities */
body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;color:#e6f0fb;background:#071026}
</style>
</head>
<body class="leading-normal text-slate-100 bg-[#071026]">
  <main class="max-w-5xl mx-auto p-6">
    <header class="flex items-start justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-semibold">Data Siswa</h1>
        <p class="text-sm text-slate-400 mt-1">Mode: <span id="modeLabel" class="font-medium"><?= $reg_mode ? 'Register' : ($test_mode ? 'Tester' : 'Normal') ?></span></p>
      </div>

      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2" title="Mode Registrasi">
          <button id="regSwitch" role="switch" aria-checked="<?= $reg_mode ? 'true' : 'false' ?>" aria-label="Toggle Mode Registrasi"
            class="relative inline-flex items-center h-7 w-12 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $reg_mode ? 'bg-blue-600' : 'bg-slate-700' ?>"
            data-checked="<?= $reg_mode ? '1' : '0' ?>" tabindex="0">
            <span class="sr-only">Toggle Registrasi</span>
            <span class="dot absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition-transform <?= $reg_mode ? 'translate-x-5' : '' ?>"></span>
          </button>
          <span class="text-sm text-slate-400 font-medium ml-1">Mode Registrasi</span>
        </div>
      </div>
    </header>

    <!-- card: form -->
    <section class="bg-[#071a2b] border border-slate-800 rounded-xl p-4 mb-4">
      <h2 class="text-lg font-semibold mb-3"><?= $edit_data ? 'Edit Siswa' : 'Tambah Siswa' ?></h2>
      <form action="<?= esc('action_student.php') ?>" method="post" enctype="multipart/form-data" class="grid grid-cols-1 gap-3" novalidate>
        <input type="hidden" name="id" value="<?= esc($edit_data['id'] ?? '') ?>">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="md:col-span-2">
            <label class="block text-sm text-slate-300 mb-1" for="name">Nama</label>
            <input id="name" name="name" required value="<?= esc($edit_data['name'] ?? '') ?>" class="w-full rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100" placeholder="Nama lengkap" aria-required="true">
          </div>

          <div>
            <label class="block text-sm text-slate-300 mb-1" for="class">Kelas</label>
            <select id="class" name="class" required class="w-full rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100" aria-required="true">
              <option value="">Pilih Kelas</option>
              <?php foreach($kelasList as $k): ?>
                <option value="<?= esc($k) ?>" <?= (isset($edit_data['class']) && $edit_data['class']==$k)?'selected':'' ?>><?= esc($k) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
          <div>
            <label class="block text-sm text-slate-300 mb-1" for="card_id">Card ID</label>
            <input id="card_id" name="card_id" required value="<?= esc($edit_data['card_id'] ?? '') ?>" class="w-full rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100" placeholder="123456789" aria-required="true">
          </div>

          <div>
            <label class="block text-sm text-slate-300 mb-1" for="status">Status</label>
            <select id="status" name="status" class="w-full rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100">
              <option value="active" <?= (isset($edit_data['status']) && $edit_data['status']=='active') ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= (isset($edit_data['status']) && $edit_data['status']=='inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <div class="flex items-center gap-3">
            <div class="w-20 h-20 rounded-md overflow-hidden bg-slate-800 flex items-center justify-center">
              <?php if($edit_data && !empty($edit_data['profile_pic']) && $edit_data['profile_pic']!=='default.png'): ?>
                <img src="<?= esc('uploads/'.$edit_data['profile_pic']) ?>" alt="Foto <?= esc($edit_data['name']) ?>" class="object-cover w-full h-full" onerror="this.src='assets/img/default-avatar.png'">
              <?php else: ?>
                <img src="assets/img/default-avatar.png" alt="Default avatar" class="object-cover w-full h-full">
              <?php endif; ?>
            </div>
            <div class="flex-1">
              <label class="block text-sm text-slate-300 mb-1" for="profile_pic">Foto Profil</label>
              <input id="profile_pic" type="file" name="profile_pic" accept="image/*" class="text-sm text-slate-300">
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 mt-1">
          <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md">
            <?= $edit_data ? 'Update' : 'Simpan' ?>
          </button>
          <?php if($edit_data): ?>
            <a href="index.php?page=students" class="text-slate-300 hover:text-white">Batal</a>
          <?php endif; ?>
          <div class="text-sm text-slate-400 ml-auto">Total siswa: <span class="font-medium text-slate-100"><?= intval($totalRows) ?></span></div>
        </div>
      </form>
    </section>

    <!-- Filter -->
    <section class="flex flex-col md:flex-row items-start md:items-center gap-3 mb-4">
      <form method="GET" action="index.php" class="flex gap-3 items-center w-full md:w-auto" aria-label="Filter siswa">
        <input type="hidden" name="page" value="students">
        <select name="class" class="rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100">
          <option value="">Semua Kelas</option>
          <?php foreach($kelasList as $k): ?>
            <option value="<?= esc($k) ?>" <?= $classFilter==$k ? 'selected' : '' ?>><?= esc($k) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="search" placeholder="Cari nama / ID kartu..." value="<?= esc($search) ?>" class="rounded-md bg-[#0b1624] border border-slate-700 px-3 py-2 text-slate-100 w-full md:w-64">
        <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-slate-100 px-3 py-2 rounded-md">Filter</button>
        <a href="index.php?page=students" class="text-sm text-blue-400 ml-2">Reset</a>
      </form>
    </section>

    <!-- Students list -->
    <section class="bg-[#071a2b] border border-slate-800 rounded-xl p-3">
      <div id="grid" class="space-y-3" aria-live="polite" aria-busy="false">
        <?php if($q && $q->num_rows): while($row = $q->fetch_assoc()): 
            $profile = $row['profile_pic'] ?? '';
            $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/'.$profile;
        ?>
          <article class="student-card flex gap-4 items-center p-3 rounded-lg bg-gradient-to-b from-white/1 to-black/2 border border-transparent hover:border-slate-700 transition" data-id="<?= intval($row['id']) ?>" role="article" aria-label="Siswa <?= esc($row['name']) ?>">
            <div class="w-20 h-20 flex-shrink-0 rounded-md overflow-hidden bg-slate-800 flex items-center justify-center">
              <img src="<?= esc($profilePath) ?>" alt="Foto <?= esc($row['name']) ?>" class="object-cover w-full h-full" onerror="this.src='assets/img/default-avatar.png'">
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <h3 class="text-base font-semibold truncate"><?= esc($row['name']) ?></h3>
                  <div class="text-sm text-slate-400 truncate"><?= esc($row['class']) ?> · <span class="text-slate-400"><?= esc($row['card_id']) ?></span></div>
                </div>

                <div class="flex-shrink-0 flex items-center gap-2">
                  <a href="index.php?page=students&edit=<?= intval($row['id']) ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-700 hover:bg-slate-700" title="Edit">
                    ✏️
                  </a>
                  <a href="action_student.php?delete=<?= intval($row['id']) ?>" onclick="return confirm('Hapus siswa ini?')" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-700 hover:bg-red-700" title="Hapus">
                    🗑️
                  </a>
                </div>
              </div>

              <div class="mt-2 flex items-center justify-between gap-3">
                <div class="text-sm text-slate-400">ID: <span class="text-slate-200"><?= intval($row['id']) ?></span></div>
                <div class="text-sm text-slate-400">Terdaftar: <span class="text-slate-200"><?= esc($row['created_at']) ?></span></div>
              </div>
            </div>
          </article>
        <?php endwhile; else: ?>
          <div class="py-8 text-center text-slate-400">Belum ada data</div>
        <?php endif; ?>
      </div>

      <!-- pagination -->
      <?php if (!$hasFilter): ?>
        <nav class="mt-4 flex justify-center" aria-label="Pagination">
          <ul class="inline-flex items-center gap-2">
            <?php for($i=1;$i<=$totalPages;$i++):
              $href = 'index.php?page=students&p='.intval($i).($classFilter ? '&class='.urlencode($classFilter) : '').($search ? '&search='.urlencode($search) : '');
            ?>
              <li>
                <a href="<?= esc($href) ?>" class="px-3 py-1 rounded-md <?= $i==$pageNo ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' ?>" aria-current="<?= $i==$pageNo ? 'page' : 'false' ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </section>
  </main>

  <!-- live update script (repo had live_update.js) -->
  <script src="assets/js/live_update.js"></script>
  <script>
  (function(){
    const regSwitch = document.getElementById('regSwitch');
    const modeLabel = document.getElementById('modeLabel');
    const grid = document.getElementById('grid');
    const LIMIT = <?= intval($limit) ?>;

    function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    async function refreshSettingsAndState(){
      try {
        // keep existing query params (class/search/p)
        const params = new URLSearchParams(window.location.search);
        params.set('ajax','1');
        const url = 'index.php?page=students&' + params.toString();
        const res = await fetch(url, { cache: 'no-store' });
        if (!res.ok) return;
        const j = await res.json();
        const modes = j.modes || { reg_mode:0, test_mode:0 };
        if (regSwitch) {
          regSwitch.setAttribute('data-checked', modes.reg_mode ? '1' : '0');
          regSwitch.setAttribute('aria-checked', modes.reg_mode ? 'true' : 'false');
          modeLabel.innerText = modes.reg_mode ? 'Register' : (modes.test_mode ? 'Tester' : 'Normal');
          // move dot
          const dot = regSwitch.querySelector('.dot');
          if (dot) {
            if (modes.reg_mode) dot.classList.add('translate-x-5'); else dot.classList.remove('translate-x-5');
            if (modes.reg_mode) regSwitch.classList.add('bg-blue-600'); else regSwitch.classList.remove('bg-blue-600');
          }
        }

        const reg = parseInt(modes.reg_mode) === 1;
        if (reg && window.LiveUpdates) startStudentPolling();
        else stopStudentPolling();
      } catch(e){ console.error(e); }
    }

    let studentPoll = null;
    function startStudentPolling(){
      if (studentPoll) return;
      if (!window.LiveUpdates) return;
      studentPoll = LiveUpdates.startLongPoll({
        url: 'api/updates.php?mode=students',
        paramNameForLast: 'last_id',
        getLastValue: function(){
          const first = grid.querySelector('.student-card');
          return first ? parseInt(first.getAttribute('data-id')) || 0 : 0;
        },
        onNew: function(payload){
          if (!payload || !payload.item) return;
          const r = payload.item;
          const profile = r.profile_pic ? ('uploads/'+encodeURIComponent(r.profile_pic)) : 'assets/img/default-avatar.png';
          const id = parseInt(r.id) || 0;
          const html = `<article class="student-card flex gap-4 items-center p-3 rounded-lg bg-gradient-to-b from-white/1 to-black/2 border border-transparent hover:border-slate-700 transition flash" data-id="${id}" role="article" aria-label="Siswa ${escapeHtml(r.name)}">
            <div class="w-20 h-20 flex-shrink-0 rounded-md overflow-hidden bg-slate-800 flex items-center justify-center">
              <img src="${profile}" alt="Foto ${escapeHtml(r.name)}" class="object-cover w-full h-full" onerror="this.src='assets/img/default-avatar.png'">
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <h3 class="text-base font-semibold truncate">${escapeHtml(r.name)}</h3>
                  <div class="text-sm text-slate-400 truncate">${escapeHtml(r.class)} · <span class="text-slate-400">${escapeHtml(r.card_id)}</span></div>
                </div>
                <div class="flex-shrink-0 flex items-center gap-2">
                  <a href="index.php?page=students&edit=${id}" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-700 hover:bg-slate-700" title="Edit">✏️</a>
                  <a href="action_student.php?delete=${id}" onclick="return confirm('Hapus siswa ini?')" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-700 hover:bg-red-700" title="Hapus">🗑️</a>
                </div>
              </div>
              <div class="mt-2 flex items-center justify-between gap-3">
                <div class="text-sm text-slate-400">ID: <span class="text-slate-200">${id}</span></div>
                <div class="text-sm text-slate-400">Terdaftar: <span class="text-slate-200">${escapeHtml(r.created_at || '')}</span></div>
              </div>
            </div>
          </article>`;
          grid.insertAdjacentHTML('afterbegin', html);
          while (grid.children.length > LIMIT) grid.removeChild(grid.lastChild);
          setTimeout(()=> {
            const el = grid.querySelector('.student-card.flash');
            if (el) el.classList.remove('flash');
          }, 1200);
        }
      });
    }
    function stopStudentPolling(){
      if (studentPoll && studentPoll.stop) studentPoll.stop();
      studentPoll = null;
    }

    async function toggleRegisterMode(newState){
      try {
        const form = new FormData();
        form.append('toggle_reg_mode', newState ? '1' : '0');
        await fetch('action_register.php', { method:'POST', body: form });
        await refreshSettingsAndState();
      } catch(e){ console.error(e); }
    }

    if (regSwitch){
      regSwitch.addEventListener('click', (e)=>{
        e.preventDefault();
        const cur = regSwitch.getAttribute('data-checked') === '1' ? 1 : 0;
        const next = cur ? 0 : 1;
        regSwitch.setAttribute('data-checked', next ? '1' : '0');
        regSwitch.setAttribute('aria-checked', next ? 'true' : 'false');
        // UI optimistic
        const dot = regSwitch.querySelector('.dot');
        if (dot) {
          if (next) dot.classList.add('translate-x-5'); else dot.classList.remove('translate-x-5');
        }
        if (next) regSwitch.classList.add('bg-blue-600'); else regSwitch.classList.remove('bg-blue-600');
        document.getElementById('modeLabel').innerText = next ? 'Register' : 'Normal';
        toggleRegisterMode(next);
      });
      regSwitch.addEventListener('keydown', (e)=>{
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); regSwitch.click(); }
      });
    }

    // client-side quick filter on existing DOM items when user types into the search input inside the filter form
    (function clientFilter(){
      const input = document.querySelector('input[name="search"]');
      if (!input) return;
      input.addEventListener('input', function(){
        const term = (this.value || '').toLowerCase();
        const cards = grid.querySelectorAll('.student-card');
        cards.forEach(card=>{
          const text = (card.textContent || '').toLowerCase();
          card.style.display = text.indexOf(term) !== -1 ? '' : 'none';
        });
      });
    })();

    // init
    (async function init(){ await refreshSettingsAndState(); })();
  })();
  </script>
</body>
</html>
