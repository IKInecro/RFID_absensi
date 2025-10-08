<?php
// pages/live_feed.php
// Full replace: live attendance feed (shows real attendance_log updates only).
// Uses api/updates.php?mode=live long-poll to get new entries (no periodic refresh).
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// read current mode status for UI
$reg_mode = 0; $test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
    $s = $set_q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
}
if ($reg_mode==1) $test_mode=0;
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Live Feed — Attendance</title>
<style>
:root{--bg:#071026;--panel:#071a2b;--muted:#9bb0c9;--text:#e6f0fb;--accent:#1E3A8A;}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.wrap{max-width:1200px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.title{font-size:20px;font-weight:700}
.card{background:var(--panel);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,0.03)}
.leftcol{flex:1;margin-right:16px}
.rightcol{width:340px}
.list{display:flex;flex-direction:column;gap:10px;padding:12px}
.list-wrap{max-height:400px;overflow-y:auto;border-top:1px solid rgba(255,255,255,0.02);border-bottom:1px solid rgba(255,255,255,0.02);}
/* scrollbar */
.list-wrap::-webkit-scrollbar{width:10px}
.list-wrap::-webkit-scrollbar-track{background:transparent}
.list-wrap::-webkit-scrollbar-thumb{background:rgba(155,176,201,0.12);border-radius:6px}
.item{display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent);border:1px solid rgba(255,255,255,0.02)}
.avatar{width:64px;height:64px;border-radius:999px;overflow:hidden;background:rgba(255,255,255,0.02)}
.status{padding:6px 10px;border-radius:999px;color:white;font-weight:700}
.s-On-Time{background:#16a34a}.s-Toleransi{background:#f59e0b}.s-Late{background:#ef4444}.s-Libur{background:#6b7280}.s-Out-of-Schedule{background:#334155}.s-Tidak-Diketahui{background:#4b5563}
.flash{animation: flashin .9s ease}@keyframes flashin{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
.controls{display:flex;gap:8px}
.small{color:var(--muted)}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <div class="title">Live Feed (Real Attendance)</div>
      <div class="small">Hanya menampilkan data normal (attendance_log). Update terjadi only when new attendance arrives.</div>
    </div>
    <div class="controls">
      <form method="post" action="action_register.php" style="display:inline-block;margin-right:8px">
        <input type="hidden" name="toggle_reg_mode" value="<?= $reg_mode ? '0' : '1' ?>">
        <button class="card" <?= $test_mode ? 'disabled' : '' ?>><?= $reg_mode ? 'Disable Register' : 'Enable Register' ?></button>
      </form>
      <form method="post" action="toggle_testmode.php" style="display:inline-block">
        <input type="hidden" name="new_mode" value="<?= $test_mode ? '0' : '1' ?>">
        <button class="card" <?= $reg_mode ? 'disabled' : '' ?>><?= $test_mode ? 'Disable Tester' : 'Enable Tester' ?></button>
      </form>
    </div>
  </div>

  <div style="display:flex;gap:14px">
    <div class="leftcol">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-weight:700">Recent Attendance</div>
          <div class="small" id="lastTime">—</div>
        </div>

        <!-- START scrollable container -->
        <div class="list-wrap">
          <div id="list" class="list">
            <?php
              // initial load: last 10 attendance
              $q = $conn->query("SELECT al.id, al.timestamp, al.card_id, al.device_id, al.schedule_status, s.name, s.class, s.profile_pic
                                 FROM attendance_log al LEFT JOIN students s ON s.id=al.student_id
                                 ORDER BY al.id DESC LIMIT 10");
              if ($q && $q->num_rows) {
                  while ($r = $q->fetch_assoc()):
                      $profile = $r['profile_pic'] ? 'uploads/'.urlencode($r['profile_pic']) : 'assets/img/default-avatar.png';
                      $status = $r['schedule_status'] ?? 'Tidak Diketahui';
            ?>
              <div class="item" data-id="<?= intval($r['id']) ?>">
                <div class="avatar"><img src="<?= $profile ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'"></div>
                <div style="flex:1">
                  <div style="font-weight:700"><?= htmlspecialchars($r['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="small"><?= htmlspecialchars($r['class'] ?? '-', ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($r['timestamp'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div><div class="status <?= 's-'.str_replace(' ','-',htmlspecialchars($status)) ?>"><?= htmlspecialchars($status) ?></div></div>
              </div>
            <?php endwhile; } else { ?>
              <div class="small muted" style="padding:12px">Belum ada data absen.</div>
            <?php } ?>
          </div>
        </div>
        <!-- END scrollable container -->

      </div>
    </div>

    <div class="rightcol">
      <div class="card" style="text-align:center;margin-bottom:12px">
        <div style="font-weight:700;margin-bottom:8px">Latest</div>
        <div id="bigAvatar" class="avatar" style="width:120px;height:120px;margin:0 auto">—</div>
        <div id="bigName" style="font-weight:700;margin-top:8px">-</div>
        <div id="bigClass" class="small">-</div>
        <div id="bigStatus" class="status s-Tidak-Diketahui" style="margin-top:10px">-</div>
      </div>

      <div class="card">
        <div style="font-weight:700;margin-bottom:8px">Info</div>
        <div class="small">Updates come only when new attendance items are recorded. Tester mode writes to test_data.json (separate).</div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/live_update.js"></script>
<script>
(function(){
  const listEl = document.getElementById('list');
  const lastTimeEl = document.getElementById('lastTime');
  const bigAvatar = document.getElementById('bigAvatar');
  const bigName = document.getElementById('bigName');
  const bigClass = document.getElementById('bigClass');
  const bigStatus = document.getElementById('bigStatus');

  // get current last id
  let lastId = 0;
  const firstItem = listEl.querySelector('.item');
  if (firstItem) lastId = parseInt(firstItem.getAttribute('data-id')) || 0;

  function onNewLive(item) {
    // build DOM node and prepend
    const node = document.createElement('div');
    node.className = 'item flash';
    node.setAttribute('data-id', item.id);
    const prof = item.profile_pic ? ('uploads/' + encodeURIComponent(item.profile_pic)) : 'assets/img/default-avatar.png';
    const status = item.schedule_status || 'Tidak Diketahui';
    node.innerHTML = `<div class="avatar"><img src="${prof}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'"></div>
      <div style="flex:1"><div style="font-weight:700">${escapeHtml(item.name || 'Unknown')}</div><div class="small">${escapeHtml(item.class || '-') } • ${escapeHtml(item.timestamp || '')}</div></div>
      <div><div class="status ${'s-'+status.replace(/[^a-zA-Z0-9]+/g,'-')}">${escapeHtml(status)}</div></div>`;
    listEl.insertBefore(node, listEl.firstChild);
    // update big panel
    bigAvatar.innerHTML = `<img src="${prof}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'">`;
    bigName.innerText = item.name || '-';
    bigClass.innerText = item.class || '-';
    bigStatus.innerText = status;
    bigStatus.className = 'status ' + 's-' + status.replace(/[^a-zA-Z0-9]+/g,'-');

    lastTimeEl.innerText = new Date().toLocaleTimeString();

    // keep list trimmed (max 30)
    while (listEl.childNodes.length > 30) listEl.removeChild(listEl.lastChild);
  }

  function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  // start long-poll
  LiveUpdates.startLongPoll({
    url: 'api/updates.php?mode=live',
    paramNameForLast: 'last_id',
    getLastValue: ()=> lastId,
    onNew: function(payload){
      if (payload && payload.item) {
        lastId = parseInt(payload.item.id) || lastId;
        onNewLive(payload.item);
      }
    }
  });

})();
</script>
</body>
</html>
