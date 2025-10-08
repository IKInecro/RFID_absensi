<?php
// pages/students.php
// FULL REPLACE - preserve semua fitur lama, hanya mengganti tombol mode menjadi satu switch "Mode Registrasi".
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// --- CONFIG / FILTER / PAGINATION (preserve fitur lama) ---
$limit = 15; // max list when no filter
$pageNo = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : '';

// Build WHERE
$where = [];
if ($classFilter) $where[] = "class = '{$classFilter}'";
if ($search) $where[] = "(name LIKE '%{$search}%' OR card_id LIKE '%{$search}%')";
$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Decide page size: if filter active, allow larger per page (so user sees all results)
$hasFilter = ($classFilter !== '' || $search !== '');
$perPage = $hasFilter ? 50 : $limit;
$offset = ($pageNo - 1) * $perPage;

// Totals
$totalRows = 0;
$qTotal = $conn->query("SELECT COUNT(*) as c FROM students $whereSql");
if ($qTotal && $qTotal->num_rows) $totalRows = intval($qTotal->fetch_assoc()['c']);
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch students (paginated)
$q = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students $whereSql ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");

// Edit mode support
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM students WHERE id={$edit_id} LIMIT 1");
    if ($r && $r->num_rows) $edit_data = $r->fetch_assoc();
}

// Class list (preserve)
$kelasList = [
  'XII IPA 1','XII IPA 2','XII IPS 1','XII IPS 2',
  'XI IPA 1','XI IPA 2','XI IPS 1','XI IPS 2',
  'X 1','X 2','X 3','X 4'
];

// Mode flags
$reg_mode = 0; $test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) { $s = $set_q->fetch_assoc(); $reg_mode = intval($s['reg_mode']); $test_mode = intval($s['test_mode']); }
if ($reg_mode == 1) $test_mode = 0; // visual mutual exclusivity

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// AJAX endpoint for auto-refresh (same file)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $students = [];
    $qq = $conn->query("SELECT id, name, class, card_id, profile_pic, status, created_at FROM students $whereSql ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
    while ($r = $qq->fetch_assoc()) $students[] = $r;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>true,'modes'=>['reg_mode'=>$reg_mode,'test_mode'=>$test_mode],'total'=>$totalRows,'page'=>$pageNo,'students'=>$students], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Students — Daftar Siswa</title>
<style>
:root{
  --bg:#071026; --panel:#071a2b; --muted:#9bb0c9; --text:#e6f0fb; --accent:#0D47A1;
  --card-bg: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.02));
}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.container{max-width:1200px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px}
.title{font-size:20px;font-weight:700}
.small{font-size:13px;color:var(--muted)}
.card{background:var(--panel);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,0.03)}
.form-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
input[type="text"], select, input[type="file"]{
  background:#0b1624;color:var(--text);border:1px solid rgba(255,255,255,0.04);padding:8px;border-radius:8px;
}
.btn{background:var(--accent);color:white;padding:8px 12px;border-radius:8px;border:0;cursor:pointer}
.btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.04)}
.controls{display:flex;gap:8px;align-items:center}

/* card list vertical */
.grid{display:flex;flex-direction:column;gap:12px}
.student-card{display:flex;gap:12px;align-items:center;background:var(--card-bg);padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.02);transition:transform .12s ease, box-shadow .12s ease}
.student-card:hover{transform:translateY(-6px);box-shadow:0 14px 40px rgba(6,20,40,.6)}
.avatar{width:84px;height:84px;border-radius:10px;overflow:hidden;flex:0 0 84px;background:rgba(255,255,255,0.02)}
.sinfo{flex:1}
.name{font-weight:700}
.meta{color:var(--muted);font-size:13px}
.actions{display:flex;gap:8px}

/* new badge and flash animation */
.flash{animation:flashin .9s ease}
@keyframes flashin{from{opacity:0;transform:translateX(8px)}to{opacity:1;transform:none}}
.new-badge{position:absolute;left:14px;top:14px;background:var(--accent);color:white;padding:4px 8px;border-radius:999px;font-size:12px;display:none}

/* pagination */
.pagination{display:flex;gap:6px;justify-content:center;margin-top:12px}
.pg{padding:6px 10px;border-radius:8px;background:rgba(255,255,255,0.03);color:var(--text);text-decoration:none}
.pg.active{background:var(--accent);color:white}

/* top-right toggle area (switch) */
.toggle-area{display:flex;gap:8px;align-items:center}

/* custom switch */
.switch {
  --w:48px; --h:26px; --bg-off:#22303f; --bg-on:var(--accent); --dot:#fff;
  position:relative; width:var(--w); height:var(--h); border-radius:999px; background:var(--bg-off); cursor:pointer; transition:all .18s ease;
  box-shadow: inset 0 -2px 0 rgba(0,0,0,0.25);
}
.switch[data-checked="1"]{ background:var(--bg-on); }
.switch .dot { position:absolute; top:3px; left:3px; width:20px; height:20px; border-radius:50%; background:var(--dot); transition:all .18s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.4); }
.switch[data-checked="1"] .dot { transform: translateX(22px); }

/* responsive */
@media (max-width:800px){
  .avatar{width:64px;height:64px}
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <div class="title">Data Siswa</div>
      <div class="small">Mode: <span id="modeLabel"><?= $reg_mode ? 'Register' : ($test_mode ? 'Tester' : 'Normal') ?></span></div>
    </div>

    <div class="toggle-area" title="Mode Registrasi (klik untuk on/off)">
      <!-- Modern switch: only register mode here -->
      <div id="regSwitch" class="switch" data-checked="<?= $reg_mode ? '1' : '0' ?>" role="switch" aria-checked="<?= $reg_mode ? 'true' : 'false' ?>"></div>
      <div style="margin-left:8px;color:var(--muted);font-weight:600">Mode Registrasi</div>
    </div>
  </div>

  <!-- Form tambah/edit (tetap ada, tema gelap) -->
  <div class="card" id="formCard" style="margin-bottom:12px">
    <h3 style="margin:0 0 8px 0"><?= $edit_data ? "Edit Siswa" : "Tambah Siswa" ?></h3>
    <form action="action_student.php" method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="id" value="<?= esc($edit_data['id'] ?? '') ?>">

      <div class="form-row">
        <div style="flex:1;min-width:200px">
          <label class="small">Nama</label><br>
          <input name="name" required value="<?= esc($edit_data['name'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-gray-700">
        </div>

        <div style="min-width:180px">
          <label class="small">Kelas</label><br>
          <select name="class" required class="p-2 border rounded" style="background:#0b1624;color:var(--text)">
            <option value="">Pilih Kelas</option>
            <?php foreach($kelasList as $k): ?>
              <option value="<?= esc($k) ?>" <?= (isset($edit_data['class']) && $edit_data['class'] == $k) ? 'selected' : '' ?>><?= esc($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="min-width:180px">
          <label class="small">Card ID</label><br>
          <input name="card_id" required value="<?= esc($edit_data['card_id'] ?? '') ?>" class="p-2 border rounded" style="background:#0b1624;color:var(--text)">
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:12px;margin-top:12px">
        <div>
          <label class="small">Foto Profil (jpg/png/webp)</label><br>
          <input type="file" name="profile_pic" accept="image/*" class="block">
        </div>

        <?php if($edit_data): ?>
          <?php
            $profile = $edit_data['profile_pic'];
            $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/'.$profile;
          ?>
          <div>
            <img src="<?= $profilePath ?>" alt="profile" class="w-16 h-16 rounded-full object-cover">
          </div>
        <?php endif; ?>

        <div>
          <label class="small">Status</label><br>
          <select name="status" class="p-2 border rounded" style="background:#0b1624;color:var(--text)">
            <option value="active" <?= (isset($edit_data['status']) && $edit_data['status']=='active')?'selected':'' ?>>Active</option>
            <option value="inactive" <?= (isset($edit_data['status']) && $edit_data['status']=='inactive')?'selected':'' ?>>Inactive</option>
          </select>
        </div>
      </div>

      <div class="pt-3">
        <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>" class="btn">
          <?= $edit_data ? 'Update' : 'Simpan' ?>
        </button>
        <?php if($edit_data): ?>
          <a href="index.php?page=students" class="ml-3 text-gray-500">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Filter -->
  <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
    <form method="GET" action="index.php" style="display:flex;gap:8px;align-items:center">
      <input type="hidden" name="page" value="students">

      <select name="class" style="background:#0b1624;color:var(--text)">
        <option value="">Semua Kelas</option>
        <?php foreach($kelasList as $k): ?>
          <option value="<?= esc($k) ?>" <?= $classFilter==$k?'selected':'' ?>><?= esc($k) ?></option>
        <?php endforeach; ?>
      </select>

      <input type="text" name="search" placeholder="Cari nama / ID kartu..." value="<?= esc($search) ?>" style="background:#0b1624;color:var(--text)">
      <button class="btn" type="submit">Filter</button>
      <a href="index.php?page=students" class="ml-2 text-sm text-blue-400">Reset</a>
    </form>

    <div style="margin-left:auto" class="small">Total: <?= $totalRows ?> siswa</div>
  </div>

  <!-- Students list -->
  <div class="card">
    <div id="grid" class="grid">
      <?php if($q->num_rows): $no = $offset+1; ?>
        <?php while($row = $q->fetch_assoc()): ?>
          <?php
            $profile = $row['profile_pic'];
            $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/'.$profile;
          ?>
          <div class="student-card" data-id="<?= intval($row['id']) ?>">
            <div class="avatar"><img src="<?= $profilePath ?>" alt="" class="w-10 h-10 rounded-full object-cover"></div>
            <div class="sinfo">
              <div class="name"><?= esc($row['name']) ?></div>
              <div class="meta"><?= esc($row['class']) ?> • <?= esc($row['card_id']) ?></div>
            </div>
            <div class="actions">
              <a href="index.php?page=students&edit=<?= $row['id'] ?>" class="btn btn.ghost" title="Edit">✏️</a>
              <a href="action_student.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus siswa ini?')" class="btn btn.ghost" title="Hapus">🗑️</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="placeholder">Belum ada data</div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (!$hasFilter): ?>
      <div class="pagination">
        <?php for($i=1; $i<=$totalPages; $i++): ?>
          <a href="?page=students&p=<?= $i ?><?= $classFilter ? "&class=$classFilter" : "" ?><?= $search ? "&search=$search" : "" ?>"
             class="px-3 py-1 rounded <?= $i==$pageNo ? 'bg-blue-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200' ?>">
             <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="assets/js/live_update.js"></script>
<script>
(function(){
  const regSwitch = document.getElementById('regSwitch');
  const modeLabel = document.getElementById('modeLabel');
  const grid = document.getElementById('grid');

  // Helper escape
  function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  // Update UI based on modes fetched from server
  async function refreshSettingsAndState(){
    try {
      const res = await fetch('pages/students.php?ajax=1', { cache:'no-store' });
      if (!res.ok) return;
      const j = await res.json();
      const modes = j.modes || { reg_mode:0, test_mode:0 };
      // set switch
      if (regSwitch) {
        regSwitch.setAttribute('data-checked', modes.reg_mode ? '1' : '0');
        regSwitch.setAttribute('aria-checked', modes.reg_mode ? 'true' : 'false');
        modeLabel.innerText = modes.reg_mode ? 'Register' : (modes.test_mode ? 'Tester' : 'Normal');
      }
      // If reg_mode active and no filter -> start polling
      const reg = parseInt(modes.reg_mode) === 1;
      if (reg && window.LiveUpdates) startStudentPolling();
      else stopStudentPolling();
    } catch (e) { console.error(e); }
  }

  // Polling control (same as before)
  let studentPoll = null;
  function startStudentPolling(){
    if (studentPoll) return;
    studentPoll = LiveUpdates.startLongPoll({
      url: 'api/updates.php?mode=students',
      paramNameForLast: 'last_id',
      getLastValue: function(){
        const first = grid.querySelector('.student-card');
        return first ? parseInt(first.getAttribute('data-id')) || 0 : 0;
      },
      onNew: function(payload){
        if (payload && payload.item) {
          const r = payload.item;
          const profile = r.profile_pic ? ('uploads/'+encodeURIComponent(r.profile_pic)) : 'assets/img/default-avatar.png';
          const html = `<div class="student-card flash" data-id="${r.id}">
              <div class="avatar"><img src="${profile}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'"></div>
              <div class="sinfo"><div class="name">${escapeHtml(r.name)}</div><div class="meta">${escapeHtml(r.class)} • ${escapeHtml(r.card_id)}</div></div>
              <div class="actions"><a href="index.php?page=students&edit=${r.id}" class="btn btn.ghost">✏️</a><a href="action_student.php?delete=${r.id}" onclick="return confirm('Hapus siswa ini?')" class="btn btn.ghost">🗑️</a></div>
            </div>`;
          grid.insertAdjacentHTML('afterbegin', html);
          while (grid.children.length > <?= $limit ?>) grid.removeChild(grid.lastChild);
          setTimeout(()=> {
            const el = grid.querySelector('.student-card.flash');
            if (el) el.classList.remove('flash');
          }, 1200);
        }
      }
    });
  }
  function stopStudentPolling(){
    if (studentPoll && studentPoll.stop) studentPoll.stop();
    studentPoll = null;
  }

  // Toggle handler: call action_register.php via POST (FormData)
  async function toggleRegisterMode(newState) {
    try {
      const form = new FormData();
      form.append('toggle_reg_mode', newState ? '1' : '0');
      const res = await fetch('action_register.php', { method:'POST', body: form });
      // ignore body, just re-fetch state
      await refreshSettingsAndState();
    } catch (err) {
      console.error('Toggle register error', err);
    }
  }

  // Switch click
  if (regSwitch) {
    regSwitch.addEventListener('click', function(e){
      e.preventDefault();
      const cur = regSwitch.getAttribute('data-checked') === '1' ? 1 : 0;
      const next = cur ? 0 : 1;
      // optimistically set UI
      regSwitch.setAttribute('data-checked', next ? '1' : '0');
      regSwitch.setAttribute('aria-checked', next ? 'true' : 'false');
      modeLabel.innerText = next ? 'Register' : 'Normal';
      toggleRegisterMode(next);
    });
  }

  // initial state
  (async function init(){
    await refreshSettingsAndState();
  })();

})();
</script>
</body>
</html>
