<?php
// pages/live_feed.php
// FULL REPLACE — versi UI modern dengan foto profil & animasi halus
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// === Ambil mode setting ===
$reg_mode = 0;
$test_mode = 0;
if ($q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1")) {
  if ($q->num_rows) {
    $s = $q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
  }
}

// === Ambil 10 entri terakhir ===
$initial_entries = [];
$last_id = 0;
$sql = "SELECT 
          al.id,
          al.student_id,
          al.card_id,
          al.timestamp,
          s.name AS student_name,
          s.class AS student_class,
          s.profile_pic
        FROM attendance_log al
        LEFT JOIN students s ON s.id = al.student_id
        ORDER BY al.timestamp DESC
        LIMIT 10";
if ($res = $conn->query($sql)) {
  while ($r = $res->fetch_assoc()) {
    $initial_entries[] = $r;
    if ((int)$r['id'] > $last_id) $last_id = (int)$r['id'];
  }
  $res->free();
}

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Live Feed Absensi</title>
<style>
:root{
  --bg:#0a1120; --panel:#111827; --text:#e6eef8; --muted:#9bb0c9;
  --success:#16a34a; --danger:#ef4444; --accent:#06b6d4;
}
html,body{
  background:var(--bg); color:var(--text);
  font-family:Inter,system-ui,Segoe UI,Roboto,Arial;
  margin:0; padding:0;
}
.wrap{max-width:1100px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.title{font-size:22px;font-weight:700}
.small{font-size:13px;color:var(--muted)}
.badge{padding:6px 10px;border-radius:999px;background:rgba(255,255,255,0.05);font-size:13px;color:var(--muted)}
.status{display:flex;gap:10px;align-items:center}
.card{background:var(--panel);border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 2px 5px rgba(0,0,0,0.2)}
.feed-wrap{max-height:600px;overflow-y:auto;scroll-behavior:smooth;margin-top:10px;padding-right:6px;}
.feed{display:flex;flex-direction:column;gap:10px;}
.item{
  display:flex;align-items:center;gap:14px;
  padding:12px 14px;
  border-radius:10px;
  background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);
  border:1px solid rgba(255,255,255,0.03);
  transition:background 0.2s ease,transform 0.2s ease;
}
.item:hover{background:rgba(255,255,255,0.04);transform:translateY(-2px);}
.avatar{
  width:60px;height:60px;border-radius:50%;
  overflow:hidden;flex-shrink:0;
  background:rgba(255,255,255,0.05);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted);font-weight:700;font-size:20px;
}
.avatar img{width:100%;height:100%;object-fit:cover;}
.info{flex:1;min-width:0;}
.name{font-weight:700;font-size:15px;margin-bottom:2px;}
.meta{font-size:13px;color:var(--muted);}
.card-id{font-size:13px;color:var(--muted);}
.placeholder{padding:40px;text-align:center;color:var(--muted);}
.flash{animation:flashIn 0.7s ease;}
@keyframes flashIn{from{opacity:0;transform:translateY(-6px);}to{opacity:1;transform:none;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <div class="title">Live Feed Absensi</div>
      <div class="small">Realtime update data absensi siswa</div>
    </div>
    <div class="status">
      <div class="badge">Reg Mode: <?= $reg_mode ? 'ON' : 'OFF' ?></div>
      <div class="badge">Test Mode: <?= $test_mode ? 'ON' : 'OFF' ?></div>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <div style="font-weight:700">Aktivitas Terbaru</div>
      <div class="small">Last ID: <span id="lastId"><?= $last_id ?></span></div>
    </div>

    <div class="feed-wrap">
      <div id="feed" class="feed">
        <?php if (empty($initial_entries)): ?>
          <div class="placeholder">Belum ada data absensi.</div>
        <?php else: ?>
          <?php foreach ($initial_entries as $e): ?>
            <div class="item" data-entry-id="<?= (int)$e['id'] ?>">
              <div class="avatar">
                <?php if (!empty($e['profile_pic']) && file_exists(__DIR__."/../uploads/{$e['profile_pic']}")): ?>
                  <img src="../uploads/<?= h($e['profile_pic']) ?>" onerror="this.src='../assets/img/default-avatar.png'">
                <?php else: ?>
                  <?= strtoupper(substr(h($e['student_name'] ?? ''),0,1)) ?: '—' ?>
                <?php endif; ?>
              </div>
              <div class="info">
                <div class="name"><?= h($e['student_name'] ?: 'Card: '.$e['card_id']) ?></div>
                <div class="meta">
                  ID: <?= (int)$e['student_id'] ?> • Kelas: <?= h($e['student_class'] ?: '-') ?> • Waktu: <?= h($e['timestamp']) ?>
                </div>
              </div>
              <div class="card-id"><?= h($e['card_id']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const API_UPDATES = 'api/updates.php?mode=live';
  let lastId = parseInt(document.getElementById('lastId').textContent || '0');
  const feedEl = document.getElementById('feed');
  const lastIdEl = document.getElementById('lastId');

  function createEntry(e) {
    const div = document.createElement('div');
    div.className = 'item flash';
    div.dataset.entryId = e.id;

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    if (e.profile_pic) {
      avatar.innerHTML = `<img src="../uploads/${encodeURIComponent(e.profile_pic)}" onerror="this.src='../assets/img/default-avatar.png'">`;
    } else {
      avatar.textContent = e.student_name ? e.student_name.charAt(0).toUpperCase() : '—';
    }

    const info = document.createElement('div');
    info.className = 'info';
    info.innerHTML = `
      <div class="name">${e.student_name || ('Card: ' + e.card_id)}</div>
      <div class="meta">ID: ${e.student_id || '-'} • Kelas: ${e.student_class || '-'} • Waktu: ${e.timestamp || '-'}</div>
    `;

    const cardid = document.createElement('div');
    cardid.className = 'card-id';
    cardid.textContent = e.card_id || '-';

    div.append(avatar, info, cardid);
    return div;
  }

  function prependEntry(e) {
    if (feedEl.querySelector('.placeholder')) feedEl.innerHTML = '';
    const node = createEntry(e);
    feedEl.prepend(node);
    while (feedEl.children.length > 80) feedEl.removeChild(feedEl.lastChild);
  }

  async function poll() {
    try {
      const res = await fetch(`${API_UPDATES}&last_id=${encodeURIComponent(lastId)}`, {cache:'no-store'});
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();
      if (data && Array.isArray(data.entries)) {
        data.entries.forEach(en => {
          const idNum = parseInt(en.id || 0);
          if (idNum > lastId) {
            prependEntry(en);
            lastId = idNum;
            lastIdEl.textContent = lastId;
          }
        });
      }
      setTimeout(poll, 250);
    } catch (err) {
      console.error('Live feed error:', err);
      setTimeout(poll, 2000);
    }
  }

  document.addEventListener('DOMContentLoaded', poll);
})();
</script>
</body>
</html>
