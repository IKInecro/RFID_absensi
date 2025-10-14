<?php
// pages/students.php
// FULL REPLACE - preserve semua fitur lama, hanya memperindah UI/UX (vertical list), improve escaping & accessibility
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
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
:root{
  --bg:#071026; --panel:#071a2b; --muted:#9bb0c9; --text:#e6f0fb; --accent:#0D47A1;
  --card-bg: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.02));
  --glass: rgba(255,255,255,0.02);
}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.container{max-width:1100px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:12px}
.title{font-size:20px;font-weight:700}
.small{font-size:13px;color:var(--muted)}
.card{background:var(--panel);border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,0.03)}
.form-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
input[type="text"], select, input[type="file"], .input {
  background:#0b1624;color:var(--text);border:1px solid rgba(255,255,255,0.04);padding:8px;border-radius:8px;
  font-size:14px;
}
.btn{background:var(--accent);color:white;padding:8px 12px;border-radius:8px;border:0;cursor:pointer;font-weight:600}
.btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--text);padding:6px 10px}
.controls{display:flex;gap:8px;align-items:center}

/* card list vertical */
.grid{display:flex;flex-direction:column;gap:12px;margin-top:6px}
.student-card{display:flex;gap:12px;align-items:center;background:var(--card-bg);padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,0.02);transition:transform .12s ease, box-shadow .12s ease; position:relative;}
.student-card:hover{transform:translateY(-6px);box-shadow:0 14px 30px rgba(6,20,40,.6)}
.avatar{width:88px;height:88px;border-radius:10px;overflow:hidden;flex:0 0 88px;background:var(--glass);display:flex;align-items:center;justify-content:center}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}
.sinfo{flex:1;min-width:0}
.name{font-weight:700;font-size:16px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.meta{color:var(--muted);font-size:13px;margin-top:4px;display:block}
.actions{display:flex;gap:8px;align-items:center}

/* smaller profile display */
.row-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap}

/* new badge and flash animation */
.flash{animation:flashin .9s ease}
@keyframes flashin{from{opacity:0;transform:translateX(8px)}to{opacity:1;transform:none}}
.new-badge{position:absolute;left:14px;top:14px;background:var(--accent);color:white;padding:4px 8px;border-radius:999px;font-size:12px;display:none}

/* pagination */
.pagination{display:flex;gap:8px;justify-content:center;margin-top:12px}
.pg{padding:8px 10px;border-radius:8px;background:rgba(255,255,255,0.03);color:var(--text);text-decoration:none}
.pg.active{background:var(--accent);color:white}

/* top-right toggle area (switch) */
.toggle-area{display:flex;gap:12px;align-items:center}

/* custom switch */
.switch {
  --w:48px; --h:26px; --bg-off:#22303f; --bg-on:var(--accent); --dot:#fff;
  position:relative; width:var(--w); height:var(--h); border-radius:999px; background:var(--bg-off); cursor:pointer; transition:all .18s ease;
  box-shadow: inset 0 -2px 0 rgba(0,0,0,0.25);
}
.switch[data-checked="1"]{ background:var(--bg-on); }
.switch .dot { position:absolute; top:3px; left:3px; width:20px; height:20px; border-radius:50%; background:var(--dot); transition:all .18s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.4); }
.switch[data-checked="1"] .dot { transform: translateX(22px); }

/* small helpers */
.placeholder{padding:20px;text-align:center;color:var(--muted)}
.helper{font-size:13px;color:var(--muted);margin-top:8px}

/* responsive */
@media (max-width:800px){
  .avatar{width:72px;height:72px}
  .student-card{padding:10px}
  .title{font-size:18px}
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
      <div id="regSwitch" class="switch" data-checked="<?= $reg_mode ? '1' : '0' ?>" role="switch" aria-checked="<?= $reg_mode ? 'true' : 'false' ?>">
        <div class="dot" aria-hidden="true"></div>
      </div>
      <div style="margin-left:8px;color:var(--muted);font-weight:600">Mode Registrasi</div>
    </div>
  </div>

  <!-- Form tambah/edit (tetap ada, tema gelap) -->
  <div class="card" id="formCard" style="margin-bottom:12px; display:block;">
    <h3 style="margin:0 0 10px 0;font-size:16px;"><?= $edit_data ? "Edit Siswa" : "Tambah Siswa" ?></h3>
    <form action="action_student.php" method="post" enctype="multipart/form-data" class="space-y-4" novalidate>
      <input type="hidden" name="id" value="<?= esc($edit_data['id'] ?? '') ?>">

      <div class="form-row">
        <div style="flex:1;min-width:200px">
          <label class="small" for="name">Nama</label><br>
          <input id="name" name="name" required value="<?= esc($edit_data['name'] ?? '') ?>" class="input" placeholder="Nama lengkap">
        </div>

        <div style="min-width:180px">
          <label class="small" for="class">Kelas</label><br>
          <select id="class" name="class" required class="input">
            <option value="">Pilih Kelas</option>
            <?php foreach($kelasList as $k): ?>
              <option value="<?= esc($k) ?>" <?= (isset($edit_data['class']) && $edit_data['class'] == $k) ? 'selected' : '' ?>><?= esc($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="min-width:180px">
          <label class="small" for="card_id">Card ID</label><br>
          <input id="card_id" name="card_id" required value="<?= esc($edit_data['card_id'] ?? '') ?>" class="input" placeholder="123456789">
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:12px;margin-top:12px;flex-wrap:wrap">
        <div>
          <label class="small" for="profile_pic">Foto Profil (jpg/png/webp)</label><br>
          <input id="profile_pic" type="file" name="profile_pic" accept="image/*" class="input">
        </div>

        <?php if($edit_data): ?>
          <?php
            $profile = $edit_data['profile_pic'];
            $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/'.$profile;
          ?>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:56px;height:56px;border-radius:8px;overflow:hidden;background:var(--glass)">
              <img src="<?= esc($profilePath) ?>" alt="Foto <?= esc($edit_data['name']) ?>" style="width:100%;height:100%;object-fit:cover">
            </div>
            <div class="small" style="color:var(--muted)"><?= esc($edit_data['name']) ?></div>
          </div>
        <?php endif; ?>

        <div>
          <label class="small" for="status">Status</label><br>
          <select id="status" name="status" class="input">
            <option value="active" <?= (isset($edit_data['status']) && $edit_data['status']=='active')?'selected':'' ?>>Active</option>
            <option value="inactive" <?= (isset($edit_data['status']) && $edit_data['status']=='inactive')?'selected':'' ?>>Inactive</option>
          </select>
        </div>
      </div>

      <div class="pt-3" style="margin-top:12px;display:flex;gap:10px;align-items:center">
        <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>" class="btn">
          <?= $edit_data ? 'Update' : 'Simpan' ?>
        </button>
        <?php if($edit_data): ?>
          <a href="index.php?page=students" class="btn ghost btn.ghost" style="text-decoration:none;padding:8px 10px;border-radius:8px;">Batal</a>
        <?php endif; ?>
        <div class="helper" style="margin-left:8px">Total siswa: <strong><?= intval($totalRows) ?></strong></div>
      </div>
    </form>
  </div>

  <!-- Filter -->
  <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
    <form method="GET" action="index.php" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <input type="hidden" name="page" value="students">

      <select name="class" class="input">
        <option value="">Semua Kelas</option>
        <?php foreach($kelasList as $k): ?>
          <option value="<?= esc($k) ?>" <?= $classFilter==$k?'selected':'' ?>><?= esc($k) ?></option>
        <?php endforeach; ?>
      </select>

      <input type="text" name="search" placeholder="Cari nama / ID kartu..." value="<?= esc($search) ?>" class="input">
      <button class="btn" type="submit">Filter</button>
      <a href="index.php?page=students" class="small" style="margin-left:6px;color:#67b0ff;text-decoration:none">Reset</a>
    </form>
  </div>

  <!-- Students list -->
  <div class="card" aria-live="polite">
    <div id="grid" class="grid" aria-busy="false">
      <?php if($q && $q->num_rows): $no = $offset+1; ?>
        <?php while($row = $q->fetch_assoc()): ?>
          <?php
            $profile = $row['profile_pic'];
            $profilePath = (!$profile || $profile === 'default.png') ? 'assets/img/default-avatar.png' : 'uploads/'.$profile;
          ?>
          <div class="student-card" data-id="<?= intval($row['id']) ?>">
            <div class="avatar" aria-hidden="true">
              <img src="<?= esc($profilePath) ?>" alt="Foto <?= esc($row['name']) ?>" onerror="this.src='assets/img/default-avatar.png'">
            </div>

            <div class="sinfo" title="<?= esc($row['name']) ?>">
              <div class="row-meta" style="align-items:center;gap:10px">
                <div class="name"><?= esc($row['name']) ?></div>
                <div style="color:var(--muted);font-size:13px">· <?= esc($row['class']) ?></div>
              </div>
              <div class="meta" style="margin-top:6px"><?= esc($row['card_id']) ?> · <span style="color:var(--muted);font-size:12px">ID: <?= intval($row['id']) ?></span></div>
              <div class="helper" style="margin-top:6px;color:var(--muted);font-size:12px">Terdaftar: <?= esc($row['created_at']) ?></div>
            </div>

            <div class="actions" aria-hidden="true">
              <a href="index.php?page=students&edit=<?= intval($row['id']) ?>" class="btn.ghost" title="Edit" style="text-decoration:none;padding:8px 10px;border-radius:8px">✏️</a>
              <a href="action_student.php?delete=<?= intval($row['id']) ?>" onclick="return confirm('Hapus siswa ini?')" class="btn.ghost" title="Hapus" style="text-decoration:none;padding:8px 10px;border-radius:8px">🗑️</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="placeholder">Belum ada data</div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (!$hasFilter): ?>
      <div class="pagination" role="navigation" aria-label="Pagination">
        <?php for($i=1; $i<=$totalPages; $i++): 
          $href = '?page=students&p='.intval($i).($classFilter ? '&class='.urlencode($classFilter) : '').($search ? '&search='.urlencode($search) : '');
        ?>
          <a href="<?= esc($href) ?>"
             class="pg <?= $i==$pageNo ? 'active' : '' ?>"
             aria-current="<?= $i==$pageNo ? 'page' : 'false' ?>">
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

  // Helper escape (client-side)
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

  // Polling control (same behavior as before)
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
          const id = parseInt(r.id) || 0;
          const html = `<div class="student-card flash" data-id="${id}" role="article" aria-label="Siswa ${escapeHtml(r.name)}">
              <div class="avatar"><img src="${profile}" alt="Foto ${escapeHtml(r.name)}" onerror="this.src='assets/img/default-avatar.png'"></div>
              <div class="sinfo"><div class="row-meta"><div class="name">${escapeHtml(r.name)}</div><div style="color:var(--muted);font-size:13px">· ${escapeHtml(r.class)}</div></div><div class="meta" style="margin-top:6px">${escapeHtml(r.card_id)} · <span style="color:var(--muted);font-size:12px">ID: ${id}</span></div></div>
              <div class="actions"><a href="index.php?page=students&edit=${id}" class="btn.ghost" style="text-decoration:none;padding:8px 10px;border-radius:8px">✏️</a><a href="action_student.php?delete=${id}" onclick="return confirm('Hapus siswa ini?')" class="btn.ghost" style="text-decoration:none;padding:8px 10px;border-radius:8px">🗑️</a></div>
            </div>`;
          grid.insertAdjacentHTML('afterbegin', html);
          // keep max rows consistent with $limit
          while (grid.children.length > <?= intval($limit) ?>) grid.removeChild(grid.lastChild);
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
