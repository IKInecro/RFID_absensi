<?php
// pages/live_feed.php - Green-Blue Gradient dengan Latest Scan Card
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
  <div class="flex justify-between items-center">
    <div>
      <h1
        class="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 dark:from-green-400 dark:to-blue-400 bg-clip-text text-transparent">
        Live Feed Absensi</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Realtime update data absensi siswa</p>
    </div>
    <div
      class="flex items-center gap-3 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <span class="relative flex h-3 w-3">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
      </span>
      <span class="text-xs font-mono text-gray-500 dark:text-gray-400">LIVE</span>
      <span class="text-xs font-mono font-bold text-green-600 dark:text-green-400" id="lastId"><?= $last_id ?></span>
    </div>
  </div>

  <!-- 2 Column Layout: Feed List (Left) + Latest Scan (Right) -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Feed List (2/3) -->
    <div class="lg:col-span-2">
      <div
        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 rounded-3xl shadow-2xl overflow-hidden">
        <div
          class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20">
          <h2 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            Recent Activity
          </h2>
        </div>

        <div id="feed" class="p-6 space-y-3 overflow-y-auto max-h-[700px] scroll-smooth">
          <?php if (empty($initial_entries)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
              <div
                class="p-6 bg-gradient-to-br from-green-100 to-blue-100 dark:from-green-900/20 dark:to-blue-900/20 rounded-full mb-6">
                <svg class="w-16 h-16 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-xl font-semibold">Waiting for activity...</p>
            </div>
          <?php else: ?>
            <?php foreach ($initial_entries as $d): ?>
              <div
                class="group relative flex items-center gap-5 p-5 rounded-2xl bg-gradient-to-r from-white to-gray-50 dark:from-gray-700/50 dark:to-gray-800/50 border border-gray-200/50 dark:border-gray-600/50 hover:shadow-xl hover:scale-[1.02] hover:border-green-400/50 dark:hover:border-blue-500/50 transition-all duration-300"
                data-entry-id="<?= $d['id'] ?>">
                <div
                  class="absolute inset-0 rounded-2xl bg-gradient-to-r from-green-500/0 via-blue-500/0 to-teal-500/0 group-hover:from-green-500/10 group-hover:via-blue-500/10 group-hover:to-teal-500/10 transition-all duration-300">
                </div>

                <div class="relative z-10 flex-shrink-0">
                  <div
                    class="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-br from-green-100 to-blue-100 dark:from-green-900/50 dark:to-blue-900/50 shadow-lg ring-4 ring-white dark:ring-gray-700 group-hover:ring-green-400 dark:group-hover:ring-blue-500 transition-all duration-300">
                    <?php if ($d['profile_pic']): ?>
                      <img src="uploads/<?= urlencode($d['profile_pic']) ?>" class="w-full h-full object-cover"
                        onerror="this.src='assets/img/default-avatar.png'">
                    <?php else: ?>
                      <div
                        class="w-full h-full flex items-center justify-center text-2xl font-black text-green-600 dark:text-blue-400">
                        <?= strtoupper(substr($d['student_name'] ?? '?', 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div
                    class="absolute -bottom-2 -right-2 p-2 bg-gradient-to-br from-green-400 to-emerald-500 border-4 border-white dark:border-gray-800 rounded-xl shadow-lg">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>

                <div class="relative z-10 flex-1 min-w-0">
                  <div class="flex justify-between items-start mb-2">
                    <h3
                      class="text-lg font-black text-gray-900 dark:text-white truncate group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-green-600 group-hover:to-blue-600 dark:group-hover:from-green-400 dark:group-hover:to-blue-400 transition-all">
                      <?= htmlspecialchars($d['student_name'] ?: 'Unknown Card') ?>
                    </h3>
                    <span
                      class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg">
                      <?= $d['timestamp'] ?>
                    </span>
                  </div>

                  <div class="flex items-center gap-3 text-sm">
                    <span
                      class="font-bold text-white bg-gradient-to-r from-green-500 to-blue-600 px-3 py-1 rounded-lg shadow-md">
                      <?= htmlspecialchars($d['student_class'] ?: 'N/A') ?>
                    </span>
                    <span class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2 font-semibold">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
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

    <!-- Latest Scan Card (Right 1/3) -->
    <div class="lg:col-span-1">
      <div
        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 rounded-3xl shadow-2xl p-6 sticky top-6">
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          Latest Scan
        </h3>

        <div id="latest-scan" class="text-center py-8">
          <div
            class="w-32 h-32 mx-auto bg-gradient-to-br from-green-100 to-blue-100 dark:from-green-900/20 dark:to-blue-900/20 rounded-full flex items-center justify-center mb-6 ring-4 ring-green-200 dark:ring-green-800/30">
            <svg class="w-16 h-16 text-green-400 dark:text-green-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">Waiting for data...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes slideDownFade {
    from {
      opacity: 0;
      transform: translateY(-30px) scale(0.95);
    }

    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .animate-slide-down {
    animation: slideDownFade 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }

  @keyframes pulse-glow {

    0%,
    100% {
      box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
    }

    50% {
      box-shadow: 0 0 40px rgba(20, 184, 166, 0.8);
    }
  }

  .pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
  }
</style>

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
    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
    osc.start(); osc.stop(audioCtx.currentTime + 0.5);
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
      latestScan.innerHTML = `
      <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-gradient-to-br from-green-100 to-blue-100 dark:from-green-900/50 dark:to-blue-900/50 mb-6 ring-4 ring-green-400 dark:ring-blue-500 shadow-xl">
        <img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">
      </div>
      <h4 class="text-2xl font-black text-gray-900 dark:text-white mb-2">${e.student_name || 'Unknown'}</h4>
      <p class="text-base font-semibold text-gray-600 dark:text-gray-400 mb-3">${e.student_class || '-'}</p>
      <div class="inline-block px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white font-bold rounded-xl shadow-lg">
        ${e.timestamp || '-'}
      </div>
    `;
    }

    function createEntry(e) {
      const div = document.createElement('div');
      div.className = 'group relative flex items-center gap-5 p-5 rounded-2xl bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/30 dark:to-blue-900/30 border-2 border-green-400/50 dark:border-blue-500/50 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 animate-slide-down pulse-glow';
      div.dataset.entryId = e.id;

      const avatarContainer = document.createElement('div');
      avatarContainer.className = 'relative z-10 flex-shrink-0';

      let avatarContent = '';
      if (e.profile_pic) {
        avatarContent = `<img src="uploads/${encodeURIComponent(e.profile_pic)}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">`;
      } else {
        const initial = e.student_name ? e.student_name.charAt(0).toUpperCase() : '—';
        avatarContent = `<div class="w-full h-full flex items-center justify-center text-2xl font-black text-green-600 dark:text-blue-400">${initial}</div>`;
      }

      avatarContainer.innerHTML = `
      <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-br from-green-100 to-blue-100 dark:from-green-900/50 dark:to-blue-900/50 shadow-xl ring-4 ring-green-400 dark:ring-blue-500">
        ${avatarContent}
      </div>
      <div class="absolute -bottom-2 -right-2 p-2 bg-gradient-to-br from-green-400 to-emerald-500 border-4 border-white dark:border-gray-800 rounded-xl shadow-lg">
        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
      </div>
    `;

      const content = document.createElement('div');
      content.className = 'relative z-10 flex-1 min-w-0';
      content.innerHTML = `
      <div class="flex justify-between items-start mb-2">
        <h3 class="text-lg font-black text-gray-900 dark:text-white truncate">
          ${e.student_name || ('Card: ' + e.card_id)}
        </h3>
        <span class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg">
          ${e.timestamp || '-'}
        </span>
      </div>
      
      <div class="flex items-center gap-3 text-sm">
        <span class="font-bold text-white bg-gradient-to-r from-green-500 to-blue-600 px-3 py-1 rounded-lg shadow-md">
          ${e.student_class || 'N/A'}
        </span>
        <span class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
        <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2 font-semibold">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
          ${e.card_id || '-'}
        </span>
      </div>
    `;

      div.append(avatarContainer, content);
      return div;
    }

    function prependEntry(e) {
      if (e.id && seenIds.has(String(e.id))) return;
      const placeholder = feedEl.querySelector('.flex.flex-col.items-center');
      if (placeholder) placeholder.remove();
      const node = createEntry(e);
      feedEl.prepend(node);
      playBeep();
      updateLatestScan(e);
      setTimeout(() => {
        node.classList.remove('from-green-50', 'to-blue-50', 'dark:from-green-900/30', 'dark:to-blue-900/30', 'border-green-400/50', 'dark:border-blue-500/50', 'pulse-glow');
        node.classList.add('from-white', 'to-gray-50', 'dark:from-gray-700/50', 'dark:to-gray-800/50', 'border-gray-200/50', 'dark:border-gray-600/50');
      }, 3000);
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
        poll();
      } catch (err) {
        console.error('Live feed error:', err);
        setTimeout(poll, 3000);
      }
    }

    document.addEventListener('DOMContentLoaded', poll);
  })();
</script>