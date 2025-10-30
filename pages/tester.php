<?php
// pages/tester.php — fixed: prevent duplicate spam, POST clear endpoint, toast notifications, improved UI
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
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px}
.title{font-size:20px;font-weight:700}
.muted{color:var(--muted)}
.grid{display:flex;flex-direction:column;gap:10px;max-height:500px;overflow-y:auto;scroll-behavior:smooth;padding-right:6px}
.grid::-webkit-scrollbar{width:6px}
.grid::-webkit-scrollbar-thumb{background:#334155;border-radius:8px}
.test-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent);border:1px solid rgba(255,255,255,0.02)}
.avatar{width:56px;height:56px;border-radius:999px;overflow:hidden;background:rgba(255,255,255,0.02);display:flex;align-items:center;justify-content:center;color:var(--muted)}
.status-pill{padding:6px 10px;border-radius:999px;color:white;font-weight:700;font-size:13px}
.s-On-Time{background:var(--success)} .s-Toleransi{background:var(--warn)} .s-Late{background:var(--danger)}
.s-Libur{background:#6b7280} .s-Out-of-Schedule{background:#334155} .s-Tidak-Diketahui{background:#4b5563}
.flash { animation: flashin .6s ease; }
@keyframes flashin { from { opacity:0; transform:translateY(-6px) } to { opacity:1; transform:none } }
.small{font-size:13px;color:var(--muted)}
.empty{padding:40px;text-align:center;color:var(--muted)}
button.action-btn{background:var(--accent);color:#fff;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-weight:600}
button.action-btn:hover{opacity:0.92}
button.danger-btn{background:var(--danger)}
/* toast */
#toast { position: fixed; right: 20px; bottom: 20px; z-index:9999; display:flex; flex-direction:column; gap:8px; }
.toast { min-width:220px; padding:10px 12px; border-radius:8px; color:#fff; box-shadow:0 6px 18px rgba(0,0,0,.4); font-weight:600; }
.toast.success { background: var(--success); }
.toast.error { background: var(--danger); }
.toast.info { background: #334155; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <div class="title">Tester Mode (Presentation)</div>
      <div class="small muted">Menampilkan data tap dari mode tester. Hanya update jika ada test tap baru.</div>
    </div>
    <div class="controls" style="display:flex;gap:8px">
      <?php
        $set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
        $reg_mode = 0; $test_mode = 0;
        if ($set_q && $set_q->num_rows) {
          $ss = $set_q->fetch_assoc();
          $reg_mode = intval($ss['reg_mode']); $test_mode = intval($ss['test_mode']);
        }
        if ($reg_mode==1) $test_mode = 0;
      ?>
      <form method="post" action="toggle_testmode.php" style="display:inline-block">
        <input type="hidden" name="new_mode" value="<?= $test_mode ? '0' : '1' ?>">
        <button class="action-btn"><?= $test_mode ? 'Disable Tester' : 'Enable Tester' ?></button>
      </form>
      <button class="action-btn danger-btn" id="clearBtn">Clear Data</button>
    </div>
  </div>

  <div style="display:flex;gap:14px;flex-wrap:wrap">
    <div style="flex:1;min-width:300px">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-weight:700">Live Test Feed</div>
          <div class="muted" id="lastUpdate">—</div>
        </div>
        <div id="list" class="grid" aria-live="polite" role="log">
          <div class="empty">Menunggu test tap...</div>
        </div>
      </div>
    </div>

    <div style="width:320px;min-width:280px">
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

<!-- toast container -->
<div id="toast" aria-live="polite" aria-atomic="true"></div>

<script src="assets/js/live_update.js"></script>
<script>
(async function(){
  const listEl = document.getElementById('list');
  const lastUpdateEl = document.getElementById('lastUpdate');
  const latestAvatar = document.getElementById('latestAvatar');
  const latestName = document.getElementById('latestName');
  const latestClass = document.getElementById('latestClass');
  const latestStatus = document.getElementById('latestStatus');
  const clearBtn = document.getElementById('clearBtn');
  const toastBox = document.getElementById('toast');

  // state to prevent duplicates
  let last_ts = '';      // string timestamp of most recent item
  const seenIds = new Set(); // to guard duplicates by id if present

  function showToast(type, msg, timeout=3500){
    const el = document.createElement('div');
    el.className = 'toast ' + (type || 'info');
    el.textContent = msg;
    toastBox.appendChild(el);
    setTimeout(()=> {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(()=> el.remove(), 450);
    }, timeout);
  }

  function escapeHtml(s){ return s ? String(s).replace(/[&<>]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])) : ''; }
  function statusClass(s){ if(!s) s='Tidak Diketahui'; return 's-' + s.replace(/[^a-zA-Z0-9]+/g,'-'); }

  // Load initial data (test_data.json)
  async function loadInitial(){
    try {
      const res = await fetch('test_data.json', {cache:'no-store'});
      if (!res.ok) throw new Error('Tidak bisa memuat initial data');
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        listEl.innerHTML = '';
        for (let i = 0; i < data.length; i++){
          const it = data[i];
          addItem(it, false);
        }
        // set last_ts as first item's timestamp (most recent)
        const first = data[0];
        last_ts = first?.timestamp || '';
      } else {
        listEl.innerHTML = '<div class="empty">Belum ada test tap.</div>';
      }
    } catch (e) {
      listEl.innerHTML = '<div class="empty">Gagal memuat data.</div>';
      console.error(e);
    }
  }

  function buildItemHtml(item){
    const profile = item.profile_pic ? 'uploads/'+encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    const name = escapeHtml(item.name || '-');
    const cls = escapeHtml(item.class || '-');
    const ts = escapeHtml(item.timestamp || '-');
    const status = escapeHtml(item.schedule_status || 'Tidak Diketahui');
    const sclass = statusClass(status);
    return `
      <div class="avatar"><img src="${profile}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'"></div>
      <div style="flex:1">
        <div style="font-weight:700">${name}</div>
        <div class="small muted">${cls} • ${ts}</div>
      </div>
      <div><div class="status-pill ${sclass}">${status}</div></div>`;
  }

  function addItem(item, animate=true){
    // guard: if item has id and already seen, skip
    const id = item.id ? String(item.id) : null;
    if (id && seenIds.has(id)) return;
    // guard: if timestamp equals last_ts, skip (not new)
    if (item.timestamp && last_ts && item.timestamp === last_ts) return;

    const node = document.createElement('div');
    node.className = 'test-item' + (animate ? ' flash' : '');
    if (id) node.setAttribute('data-id', id);
    if (item.timestamp) node.setAttribute('data-ts', item.timestamp);
    node.innerHTML = buildItemHtml(item);

    // remove placeholder if exists
    const ph = listEl.querySelector('.empty');
    if (ph) ph.remove();

    listEl.insertBefore(node, listEl.firstChild);

    if (id) seenIds.add(id);
    if (item.timestamp) last_ts = item.timestamp;

    if (animate) lastUpdateEl.innerText = new Date().toLocaleTimeString('id-ID', {hour12:false});

    // limit list
    while (listEl.children.length > 120) {
      const last = listEl.lastChild;
      const remId = last?.getAttribute?.('data-id');
      if (remId) seenIds.delete(remId);
      last.remove();
    }
  }

  // When new payload arrives (from live_update helper)
  function onNewTest(item){
    // item may be object or array; ensure object
    if (!item) return;
    // check timestamp vs last_ts
    if (item.timestamp && last_ts) {
      if (item.timestamp === last_ts) return; // not new
      // if item.timestamp older than last_ts, but id not seen, still add (rare)
    }
    addItem(item, true);
    // update latest panel
    latestAvatar.innerHTML = `<img src="${item.profile_pic ? 'uploads/'+encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png'}" style="width:100%;height:100%;object-fit:cover" onerror="this.src='assets/img/default-avatar.png'">`;
    latestName.innerText = item.name || '-';
    latestClass.innerText = item.class || '-';
    latestStatus.innerText = item.schedule_status || 'Tidak Diketahui';
    latestStatus.className = 'status-pill ' + statusClass(latestStatus.innerText);
  }

  // Use LiveUpdates long-poll if available; fallback to safe interval polling that checks last_ts
  if (window.LiveUpdates && typeof LiveUpdates.startLongPoll === 'function') {
    LiveUpdates.startLongPoll({
      url: 'api/updates.php?mode=test',
      paramNameForLast: 'last_ts',
      getLastValue: () => last_ts || '',
      onNew: function(payload){
        if (payload && payload.item) {
          // payload.item may be a single item or array
          const it = payload.item;
          if (Array.isArray(it)) {
            it.forEach(i => onNewTest(i));
          } else {
            onNewTest(it);
          }
        }
      },
      onError: function(err){
        console.error('LiveUpdates error', err);
      }
    });
  } else {
    // fallback safe polling (every 3.5s). Pulls test_data.json but only applies new items.
    setInterval(async ()=>{
      try {
        const res = await fetch('test_data.json', {cache:'no-store'});
        if (!res.ok) return;
        const data = await res.json();
        if (!Array.isArray(data) || data.length === 0) return;
        // data[0] is newest — iterate from newest down until we hit last_ts
        for (let i = 0; i < data.length; i++){
          const item = data[i];
          // if timestamp matches last_ts, we already processed newer items, stop loop
          if (item.timestamp && last_ts && item.timestamp === last_ts) break;
          // if id present and seen, skip
          if (item.id && seenIds.has(String(item.id))) continue;
          // else add
          onNewTest(item);
        }
      } catch (e) {
        console.error('Polling error', e);
      }
    }, 3500);
  }

  // Clear handler — use POST to tester_clear.php
  clearBtn.addEventListener('click', async ()=>{
    if (!confirm('Yakin ingin menghapus semua data test tap (hanya list tester)?')) return;
    clearBtn.disabled = true;
    const original = clearBtn.innerText;
    clearBtn.innerText = 'Membersihkan...';
    try {
      const res = await fetch('tester_clear.php', { method: 'POST', headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (res.ok && json.success) {
        // reset UI state
        listEl.innerHTML = '<div class="empty">Data test tap telah dihapus.</div>';
        seenIds.clear();
        last_ts = '';
        latestAvatar.innerHTML = '—';
        latestName.innerText = '-';
        latestClass.innerText = '-';
        latestStatus.innerText = '-';
        latestStatus.className = 'status-pill s-Tidak-Diketahui';
        showToast('success', json.message || 'Data tester dibersihkan.');
      } else {
        showToast('error', json.message || 'Gagal membersihkan data.');
      }
    } catch (e) {
      console.error(e);
      showToast('error', 'Gagal menghubungi server.');
    } finally {
      clearBtn.disabled = false;
      clearBtn.innerText = original;
    }
  });

  // initial load
  await loadInitial();
})();
</script>
</body>
</html>
