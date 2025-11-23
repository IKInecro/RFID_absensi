<?php
// pages/live_feed.php - Clean Professional UI
include __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

$initial_entries = [];
$last_id = 0;
$sql = "SELECT al.id, al.student_id, al.card_id, al.timestamp, s.name AS student_name, s.class AS student_class, s.profile_pic 
        FROM attendance_log al LEFT JOIN students s ON s.id = al.student_id 
        ORDER BY al.timestamp DESC LIMIT 10";
if ($res = $conn->query($sql)) {
  while ($r = $res->fetch_assoc()) {
    $initial_entries[] = $r;
    if ((int) $r['id'] > $last_id)
      $last_id = (int) $r['id'];
  }
  $res->free();
}
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-5">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Live Feed</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time attendance monitoring.</p>
    </div>
    <div
      class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full border border-green-200 dark:border-green-800">
      <span class="relative flex h-2.5 w-2.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
      </span>
      <span class="text-xs font-bold uppercase tracking-wider">Live</span>
      <span class="hidden" id="lastId"><?= $last_id ?></span>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Feed List (Left 2/3) -->
    <div class="lg:col-span-2">
      <div
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/30">
          <h2 class="font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
          <span class="text-xs text-gray-500 dark:text-gray-400">Latest 50 entries</span>
        </div>

        <div id="feed" class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
          <?php if (empty($initial_entries)): ?>
            <div class="p-12 text-center">
              <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 text-gray-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-gray-500 dark:text-gray-400 font-medium">No activity yet.</p>
            </div>
          <?php else: ?>
            <?php foreach ($initial_entries as $d):
              $initial = $d['student_name'] ? strtoupper(substr($d['student_name'], 0, 1)) : '?';
              $profile = $d['profile_pic'] ? 'uploads/' . urlencode($d['profile_pic']) : null;
              ?>
              <div class="group flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                data-entry-id="<?= $d['id'] ?>">
                <div class="flex-shrink-0">
                  <?php if ($profile): ?>
                    <img src="<?= $profile ?>"
                      class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                      onerror="this.src='assets/img/default-avatar.png'">
                  <?php else: ?>
                    <div
                      class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm border border-blue-200 dark:border-blue-800">
                      <?= $initial ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-baseline">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                      <?= htmlspecialchars($d['student_name'] ?: 'Unknown Card') ?>
                    </h3>
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                      <?= date('H:i:s', strtotime($d['timestamp'])) ?>
                    </span>
                  </div>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                      <?= htmlspecialchars($d['student_class'] ?: '-') ?>
                    </span>
                    <span class="text-xs text-gray-400 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      <?= htmlspecialchars($d['card_id']) ?>
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Latest Scan (Right 1/3) -->
    <div class="lg:col-span-1">
      <div class="sticky top-6">
        <div
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-center">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Latest Scan</h3>

          <div id="latest-scan">
            <div
              class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
              <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Waiting for data...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  function playBeep() {
    if (audioCtx.state === 'suspended') audioCtx.resume();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.type = 'sine';
    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
    osc.start(); osc.stop(audioCtx.currentTime + 0.3);
  }

  (() => {
    const API_UPDATES = 'api/updates.php?mode=live';
    let lastId = parseInt(document.getElementById('lastId').textContent || '0');
    const feedEl = document.getElementById('feed');
    const lastIdEl = document.getElementById('lastId');
    const latestScan = document.getElementById('latest-scan');
    const seenIds = new Set();

    document.querySelectorAll('[data-entry-id]').forEach(el => {
      const entryId = el.getAttribute('data-entry-id');
      if (entryId) seenIds.add(String(entryId));
    });

    function updateLatestScan(e) {
      const profile = e.profile_pic ? 'uploads/' + encodeURIComponent(e.profile_pic) : 'assets/img/default-avatar.png';
      const initial = e.student_name ? e.student_name.charAt(0).toUpperCase() : '?';

      let avatarHtml = '';
      if (e.profile_pic) {
        avatarHtml = `<img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">`;
      } else {
        avatarHtml = `<div class="w-full h-full flex items-center justify-center text-3xl font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20">${initial}</div>`;
      }

      latestScan.innerHTML = `
        <div class="w-24 h-24 mx-auto rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 mb-4 ring-4 ring-white dark:ring-gray-800 shadow-lg">
          ${avatarHtml}
        </div>
        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-1 truncate">${e.student_name || 'Unknown'}</h4>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">${e.student_class || '-'}</p>
        <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-sm font-bold">
          ${e.timestamp || '-'}
        </div>
      `;
    }

    function createEntry(e) {
      const div = document.createElement('div');
      div.className = 'group flex items-center gap-4 p-4 bg-blue-50/50 dark:bg-blue-900/10 border-l-4 border-blue-500 transition-all duration-500';
      div.dataset.entryId = e.id;

      const profile = e.profile_pic ? 'uploads/' + encodeURIComponent(e.profile_pic) : null;
      const initial = e.student_name ? e.student_name.charAt(0).toUpperCase() : '?';

      let avatarHtml = '';
      if (profile) {
        avatarHtml = `<img src="${profile}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" onerror="this.src='assets/img/default-avatar.png'">`;
      } else {
        avatarHtml = `<div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm border border-blue-200 dark:border-blue-800">${initial}</div>`;
      }

      div.innerHTML = `
        <div class="flex-shrink-0">${avatarHtml}</div>
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-baseline">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
              ${e.student_name || ('Card: ' + e.card_id)}
            </h3>
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
              ${e.timestamp || '-'}
            </span>
          </div>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-300">
              ${e.student_class || 'N/A'}
            </span>
            <span class="text-xs text-gray-400 flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              ${e.card_id || '-'}
            </span>
          </div>
        </div>
      `;
      return div;
    }

    function prependEntry(e) {
      if (e.id && seenIds.has(String(e.id))) return;

      // Remove empty state if exists
      const emptyState = feedEl.querySelector('.text-center');
      if (emptyState) emptyState.remove();

      const node = createEntry(e);
      feedEl.prepend(node);
      playBeep();
      updateLatestScan(e);

      // Remove highlight after 2 seconds
      setTimeout(() => {
        node.className = 'group flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-l-4 border-transparent';
      }, 2000);

      if (e.id) seenIds.add(String(e.id));
      while (feedEl.children.length > 50) feedEl.removeChild(feedEl.lastChild);
    }

    async function poll() {
      try {
        const res = await fetch(`${API_UPDATES}&last_id=${encodeURIComponent(lastId)}`, { cache: 'no-store' });
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();
        if (data && data.new && Array.isArray(data.entries)) {
          const newEntries = data.entries.filter(en => {
            const idNum = parseInt(en.id || 0);
            return idNum > lastId && !seenIds.has(String(en.id));
          });
          newEntries.reverse().forEach(en => {
            prependEntry(en);
            const idNum = parseInt(en.id || 0);
            if (idNum > lastId) {
              lastId = idNum;
              lastIdEl.textContent = lastId;
            }
          });
        }
        setTimeout(poll, 1000); // 1s polling
      } catch (err) {
        console.error('Live feed error:', err);
        setTimeout(poll, 3000);
      }
    }

    document.addEventListener('DOMContentLoaded', poll);
  })();
</script>