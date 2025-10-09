<?php
// pages/live_feed.php
// Full replace - cleaned up queries (alias 'al' used explicitly) and long-polling client
// Include DB (safe relative path)
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Read settings (reg_mode, test_mode) for UI indicators
$reg_mode = 0;
$test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
    $s = $set_q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
}

// Initial load: last 10 attendance entries (most recent)
$initial_entries = [];
$last_id = 0;
$sql = "SELECT
            al.id,
            al.student_id,
            al.card_id,
            al.timestamp,
            s.name AS student_name,
            s.class AS student_class
        FROM attendance_log AS al
        LEFT JOIN students AS s ON s.id = al.student_id
        ORDER BY al.timestamp DESC
        LIMIT 10";
if ($res = $conn->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $initial_entries[] = $r;
        if ((int)$r['id'] > $last_id) $last_id = (int)$r['id'];
    }
    $res->free();
}

// helper for safe output
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
  --bg:#0f1724; --panel:#111827; --text:#e6eef8; --muted:#9bb0c9;
  --accent:#06b6d4;
}
html,body{background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;margin:0;padding:0}
.wrap{max-width:1100px;margin:28px auto;padding:18px}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.title{font-size:20px;font-weight:700}
.status{display:flex;gap:10px;align-items:center;font-size:13px;color:var(--muted)}
.card{background:var(--panel);border-radius:10px;padding:12px;border:1px solid rgba(255,255,255,0.03)}
.feed{display:flex;flex-direction:column;gap:10px;margin-top:12px}
.item{display:flex;align-items:center;gap:12px;padding:12px;border-radius:8px;background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent);border:1px solid rgba(255,255,255,0.02)}
.avatar{width:56px;height:56px;border-radius:999px;overflow:hidden;background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--muted)}
.info{flex:1}
.name{font-weight:700}
.meta{font-size:13px;color:var(--muted)}
.placeholder{color:var(--muted);padding:18px;text-align:center}
.controls{display:flex;gap:8px;align-items:center}
.badge{padding:6px 8px;border-radius:999px;background:rgba(255,255,255,0.03);font-size:13px;color:var(--muted)}
.small{font-size:12px;color:var(--muted)}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div>
      <div class="title">Live Feed Absensi</div>
      <div class="small">Menampilkan entri absensi terbaru secara realtime</div>
    </div>
    <div class="status">
      <div class="badge">Reg Mode: <?php echo $reg_mode ? 'ON' : 'OFF'; ?></div>
      <div class="badge">Test Mode: <?php echo $test_mode ? 'ON' : 'OFF'; ?></div>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div style="font-weight:700">Aktivitas Terbaru</div>
      <div class="controls">
        <div id="lastSeen" class="small">Last ID: <span id="lastId"><?php echo $last_id; ?></span></div>
      </div>
    </div>

    <div id="feed" class="feed">
      <?php if (count($initial_entries) === 0): ?>
        <div class="placeholder card">Belum ada data absensi.</div>
      <?php else: ?>
        <?php foreach ($initial_entries as $entry): ?>
          <div class="item" data-entry-id="<?php echo (int)$entry['id']; ?>">
            <div class="avatar"><?php echo isset($entry['student_name']) && trim($entry['student_name']) !== '' ? strtoupper(substr(h($entry['student_name']),0,1)) : '—'; ?></div>
            <div class="info">
              <div class="name"><?php echo h($entry['student_name'] ?: ('Card: ' . $entry['card_id'])); ?></div>
              <div class="meta">
                ID: <?php echo (int)$entry['student_id']; ?> • Kelas: <?php echo h($entry['student_class']); ?> • Waktu: <?php echo h($entry['timestamp']); ?>
              </div>
            </div>
            <div class="small"><?php echo h($entry['card_id']); ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
(function(){
  // API endpoint (relative) - matches repo layout: api/updates.php
  const API_UPDATES = 'api/updates.php?mode=live';
  let lastId = parseInt(document.getElementById('lastId').textContent || '0', 10) || 0;
  const feedEl = document.getElementById('feed');
  const lastIdEl = document.getElementById('lastId');

  // Render a single entry (prepended)
  function renderEntry(e) {
    // create element
    const wrap = document.createElement('div');
    wrap.className = 'item';
    wrap.setAttribute('data-entry-id', e.id);

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.textContent = e.student_name ? (e.student_name.charAt(0) || '—').toUpperCase() : (e.card_id ? e.card_id.charAt(0).toUpperCase() : '—');

    const info = document.createElement('div');
    info.className = 'info';
    const name = document.createElement('div');
    name.className = 'name';
    name.textContent = e.student_name ? e.student_name : ('Card: ' + e.card_id);
    const meta = document.createElement('div');
    meta.className = 'meta';
    meta.textContent = 'ID: ' + (e.student_id || '-') + ' • Kelas: ' + (e.student_class || '-') + ' • Waktu: ' + (e.timestamp || '-');

    info.appendChild(name);
    info.appendChild(meta);

    const card = document.createElement('div');
    card.className = 'small';
    card.textContent = e.card_id || '-';

    wrap.appendChild(avatar);
    wrap.appendChild(info);
    wrap.appendChild(card);

    // prepend to feed
    if (feedEl.firstChild && feedEl.firstChild.classList && feedEl.firstChild.classList.contains('placeholder')) {
      feedEl.innerHTML = ''; // remove placeholder
    }
    feedEl.insertBefore(wrap, feedEl.firstChild);
    // optionally limit list length (keep last 50 items)
    while (feedEl.children.length > 50) {
      feedEl.removeChild(feedEl.lastChild);
    }
  }

  // Polling / long-poll loop
  let stopped = false;
  function startLongPoll(){
    if (stopped) return;
    const url = API_UPDATES + '&last_id=' + encodeURIComponent(lastId);
    fetch(url, {cache: 'no-store'})
      .then(resp => {
        if (!resp.ok) throw new Error('Network response was not ok');
        return resp.json();
      })
      .then(data => {
        // expected { success: true, entries: [{id,student_id,card_id,timestamp,student_name,student_class}, ...], last_id: N }
        if (data && Array.isArray(data.entries)) {
          data.entries.forEach(en => {
            // ensure we don't duplicate (if id <= lastId skip)
            const idNum = parseInt(en.id || 0, 10);
            if (idNum > lastId) {
              renderEntry(en);
              lastId = idNum;
              lastIdEl.textContent = lastId;
            }
          });
        }
        // immediately start next poll
        setTimeout(startLongPoll, 200); // tiny delay to avoid hammering
      })
      .catch(err => {
        console.error('Live feed error:', err);
        // on error, retry after short backoff
        setTimeout(startLongPoll, 2000);
      });
  }

  // start polling after DOM ready
  document.addEventListener('DOMContentLoaded', function(){
    // start loop
    startLongPoll();
  });

  // Expose stop function if needed
  window._liveFeedStop = function(){ stopped = true; };
})();
</script>
</body>
</html>
