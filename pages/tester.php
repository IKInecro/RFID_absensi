<?php
// pages/tester.php
// Full replace: tester UI that receives test taps only when data arrives (via long-poll updates endpoint).
// Reads test_data.json for initial list, but uses api/updates.php?mode=test long-poll to get new test taps.
// Place at pages/tester.php

include 'db.php';
date_default_timezone_set('Asia/Jakarta');
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tester Mode — Live Demo</title>
<style>
:root{
  --bg:#071026; --panel:#071a2b; --muted:#9bb0c9; --text:#e6f0fb; --accent:#1E3A8A;
  --success:#16a34a; --warn:#f59e0b; --danger:#ef4444;
}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.container{max-width:1100px;margin:28px auto;padding:18px}
.card{background:var(--panel);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,0.03)}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.title{font-size:20px;font-weight:700}
.muted{color:var(--muted)}
.grid{display:flex;flex-direction:column;gap:10px}
.test-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent);border:1px solid rgba(255,255,255,0.02)}
.avatar{width:56px;height:56px;border-radius:999px;overflow:hidden;background:rgba(255,255,255,0.02);display:flex;align-items:center;justify-content:center;color:var(--muted)}
.rightpane{width:320px}
.controls{display:flex;gap:8px}
.status-pill{padding:6px 10px;border-radius:999px;color:white;font-weight:700}
.s-On-Time{background:var(--success)} .s-Toleransi{background:var(--warn)} .s-Late{background:var(--danger)} .s-Libur{background:#6b7280} .s-Out-of-Schedule{background:#334155} .s-Tidak-Diketahui{background:#4b5563}
.flash { animation: flashin .9s ease; }
@keyframes flashin { from { opacity:0; transform:translateY(-6px) } to { opacity:1; transform:none } }
.small{font-size:13px;color:var(--muted)}
.empty{padding:40px;text-align:center;color:var(--muted)}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <div class="title">Tester Mode (Presentation)</div>
      <div class="small muted">Shows only test taps (demo). Updates only when a test tap happens.</div>
    </div>
    <div class="controls">
      <form method="post" action="toggle_testmode.php" style="display:inline-block">
        <?php
          $set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
          $reg_mode = 0; $test_mode = 0;
          if ($set_q && $set_q->num_rows) { $ss = $set_q->fetch_assoc(); $reg_mode = intval($ss['reg_mode']); $test_mode = intval($ss['test_mode']); }
          if ($reg_mode==1) $test_mode = 0;
        ?>
        <input type="hidden" name="new_mode" value="<?= $test_mode ? '0' : '1' ?>">
        <button class="card" style="border-radius:8px"><?= $test_mode ? 'Disable Tester' : 'Enable Tester' ?></button>
      </form>
    </div>
  </div>

  <div style="display:flex;gap:14px">
    <div style="flex:1">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-weight:700">Live Test Feed</div>
          <div class="muted" id="lastUpdate">—</div>
        </div>
        <div id="list" class="grid">
          <div class="empty">Memuat data test... (tunggu tap kartu)</div>
        </div>
      </div>
    </div>

    <div style="width:320px">
      <div class="card" id="latestCard">
        <div style="font-weight:700;margin-bottom:8px">Latest Tap</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px">
          <div class="avatar" id="latestAvatar">—</div>
          <div id="latestName" style="font-weight:700">-</div>
          <div id="latestClass" class="small muted">-</div>
          <div id="latestStatus" class="status-pill s-Tidak-Diketahui">-</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/live_update.js"></script>
<script>
/*
 * Tester page usage:
 *  - initial load: read test_data.json for initial list (if exists) via fetch('test_data.json')
 *  - then start long-poll: api/updates.php?mode=test&last_ts=<ts>
 *  - when new event arrives, prepend to list and update latest panel
 */
(async function(){
  const listEl = document.getElementById('list');
  const lastUpdateEl = document.getElementById('lastUpdate');
  const latestAvatar = document.getElementById('latestAvatar');
  const latestName = document.getElementById('latestName');
  const latestClass = document.getElementById('latestClass');
  const latestStatus = document.getElementById('latestStatus');

  // helper
  function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function statusClass(s){ if(!s) s='Tidak Diketahui'; return 's-' + s.replace(/[^a-zA-Z0-9]+/g,'-'); }

  // load initial list
  try {
    const res = await fetch('test_data.json', {cache:'no-store'});
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data) && data.length>0) {
        renderList(data);
      } else {
        listEl.innerHTML = '<div class="empty">Belum ada test tap.</div>';
      }
    }
  } catch(e){
    // no file or error
    listEl.innerHTML = '<div class="empty">Belum ada test tap.</div>';
  }

  // maintain last_ts
  let last_ts = '';
  const firstItem = listEl.querySelector('.test-item');
  if (firstItem) {
    last_ts = firstItem.getAttribute('data-ts') || '';
  }

  // callback when new test item arrives
  function onNewTest(item) {
    // prepend item to UI
    const node = document.createElement('div');
    node.className = 'test-item flash';
    node.setAttribute('data-ts', item.timestamp || '');
    node.innerHTML = `<div class="avatar"><img src="${item.profile_pic ? 'uploads/'+encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png'}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'"></div>
      <div style="flex:1">
        <div style="font-weight:700">${escapeHtml(item.name)}</div>
        <div class="small muted">${escapeHtml(item.class)} • ${escapeHtml(item.timestamp)}</div>
      </div>
      <div><div class="status-pill ${statusClass(item.schedule_status)}">${escapeHtml(item.schedule_status)}</div></div>`;
    // remove placeholder if present
    const ph = listEl.querySelector('.empty');
    if (ph) ph.remove();
    listEl.insertBefore(node, listEl.firstChild);

    // update latest panel
    latestAvatar.innerHTML = `<img src="${item.profile_pic ? 'uploads/'+encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png'}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'">`;
    latestName.innerText = item.name || '-';
    latestClass.innerText = item.class || '-';
    latestStatus.innerText = item.schedule_status || 'Tidak Diketahui';
    latestStatus.className = 'status-pill ' + statusClass(latestStatus.innerText);
    lastUpdateEl.innerText = new Date().toLocaleTimeString();
    // keep max 100 items
    while (listEl.childNodes.length > 120) listEl.removeChild(listEl.lastChild);
  }

  // start long-poll loop using helper in assets/js/live_update.js
  LiveUpdates.startLongPoll({
    url: 'api/updates.php?mode=test',
    paramNameForLast: 'last_ts',
    getLastValue: ()=> {
      // get latest ts from first item
      const first = listEl.querySelector('.test-item');
      return first ? first.getAttribute('data-ts') || '' : last_ts;
    },
    onNew: function(payload){
      if (payload && payload.item) onNewTest(payload.item);
    },
    onError: function(err){
      console.error('updates error', err);
      // retry managed inside live_update.js
    }
  });

})();
</script>
</body>
</html>
