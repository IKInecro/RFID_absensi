<?php
// pages/live_feed.php
// FULL REPLACE — modern UI with profile pics & smooth animations
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
    if ((int) $r['id'] > $last_id)
      $last_id = (int) $r['id'];
  }
  $res->free();
}

function h($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Live Feed Absensi</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Realtime update data absensi siswa.</p>
    </div>
    <div class="flex gap-3">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
        <span class="relative flex h-4 w-4">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500"></span>
        </span>
        Live Feed
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Memantau aktivitas absensi secara real-time.</p>
    </div>
    
    <div class="flex items-center gap-3 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
        LAST ID: <span id="lastId" class="font-bold text-blue-600 dark:text-blue-400"><?= $last_id ?></span>
      </div>
    </div>
  </div>

  <!-- Feed List -->
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl shadow-sm overflow-hidden min-h-[500px] flex flex-col">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50 backdrop-blur-xl sticky top-0 z-10">
      <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
        Aktivitas Terbaru
      </h2>
    </div>
    
    <div id="feed" class="flex-1 p-6 space-y-4 overflow-y-auto max-h-[600px] scroll-smooth">
      <?php if (empty($initial_data)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
          <svg class="w-16 h-16 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>Belum ada aktivitas hari ini.</p>
        </div>
      <?php else: ?>
        <?php foreach ($initial_data as $d): ?>
          <div class="flex items-center gap-5 p-4 rounded-2xl bg-white dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700/50 hover:shadow-lg hover:scale-[1.01] transition-all duration-300 group" data-entry-id="<?= $d['id'] ?>">
            <!-- Avatar -->
            <div class="flex-shrink-0 relative">
              <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-600 shadow-sm ring-2 ring-white dark:ring-gray-700 group-hover:ring-blue-400 dark:group-hover:ring-blue-500 transition-all">
                <?php if ($d['profile_pic']): ?>
                  <img src="uploads/<?= urlencode($d['profile_pic']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center text-xl font-bold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800">
                    <?= strtoupper(substr($d['student_name'] ?? '?', 0, 1)) ?>
                  </div>
                <?php endif; ?>
              </div>
              <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-1">
                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  <?= htmlspecialchars($d['student_name'] ?: 'Unknown Card') ?>
                </h3>
                <span class="text-xs font-mono font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg">
                  <?= $d['timestamp'] ?>
                </span>
              </div>
              
              <div class="flex items-center gap-3 text-sm">
                <span class="font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded-md">
                  <?= htmlspecialchars($d['student_class'] ?: 'N/A') ?>
                </span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
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

<style>
@keyframes slideDownFade {
  from { opacity: 0; transform: translateY(-20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slide-down {
  animation: slideDownFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

<script>
// Audio Context for Beeps
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
function playBeep() {
  if (audioCtx.state === 'suspended') audioCtx.resume();
  const osc = audioCtx.createOscillator();
  const gain = audioCtx.createGain();
  osc.connect(gain);
  gain.connect(audioCtx.destination);
  
  // Pleasant "ding" sound
  osc.type = 'sine';
  osc.frequency.setValueAtTime(800, audioCtx.currentTime);
  osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
  gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
  
  osc.start();
  osc.stop(audioCtx.currentTime + 0.5);
}

(() => {
  const API_UPDATES = 'api/updates.php?mode=live';
  let lastId = parseInt(document.getElementById('lastId').textContent || '0');
  const feedEl = document.getElementById('feed');
  const lastIdEl = document.getElementById('lastId');
  
  // Pop-up Elements
  const popupOverlay = document.getElementById('popupOverlay');
  const popupCard = document.getElementById('popupCard');
  const popupImg = document.getElementById('popupImg');
  const popupName = document.getElementById('popupName');
  const popupClass = document.getElementById('popupClass');
  const popupTime = document.getElementById('popupTime');
  const popupStatus = document.getElementById('popupStatus');
  
  let popupTimeout;

  function showPopup(e) {
    // Populate data
    popupName.textContent = e.student_name || 'Unknown Card';
    popupClass.textContent = e.student_class || 'N/A';
    popupTime.textContent = e.timestamp || new Date().toLocaleTimeString();
    popupStatus.textContent = e.status || 'Hadir';
    
    if (e.profile_pic) {
      popupImg.src = `uploads/${encodeURIComponent(e.profile_pic)}`;
    } else {
      // Generate initial avatar if no image
      // For simplicity just use default, or could generate a canvas
      popupImg.src = 'assets/img/default-avatar.png';
    }

    // Show
    popupOverlay.classList.remove('pointer-events-none', 'opacity-0');
    popupCard.classList.remove('scale-90');
    popupCard.classList.add('scale-100');

    // Hide after 3s
    clearTimeout(popupTimeout);
    popupTimeout = setTimeout(() => {
      popupOverlay.classList.add('opacity-0', 'pointer-events-none');
      popupCard.classList.remove('scale-100');
      popupCard.classList.add('scale-90');
    }, 3000);
  }

  function createEntry(e) {
    const div = document.createElement('div');
    div.className = 'flex items-center gap-5 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-lg hover:scale-[1.01] transition-all duration-300 group animate-slide-down';
    div.dataset.entryId = e.id;

    // Avatar
    const avatarContainer = document.createElement('div');
    avatarContainer.className = 'flex-shrink-0 relative';
    
    let avatarContent = '';
    if (e.profile_pic) {
      avatarContent = `<img src="uploads/${encodeURIComponent(e.profile_pic)}" class="w-full h-full object-cover">`;
    } else {
      const initial = e.student_name ? e.student_name.charAt(0).toUpperCase() : '—';
      avatarContent = `<div class="w-full h-full flex items-center justify-center text-xl font-bold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800">${initial}</div>`;
    }
    
    avatarContainer.innerHTML = `
      <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-600 shadow-sm ring-2 ring-blue-400 dark:ring-blue-500">
        ${avatarContent}
      </div>
      <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full flex items-center justify-center">
        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
      </div>
    `;

    // Content
    const content = document.createElement('div');
    content.className = 'flex-1 min-w-0';
    content.innerHTML = `
      <div class="flex justify-between items-start mb-1">
        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
          ${e.student_name || ('Card: ' + e.card_id)}
        </h3>
        <span class="text-xs font-mono font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 px-2 py-1 rounded-lg">
          ${e.timestamp || '-'}
        </span>
      </div>
      
      <div class="flex items-center gap-3 text-sm">
        <span class="font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 rounded-md">
          ${e.student_class || 'N/A'}
        </span>
        <span class="text-gray-300 dark:text-gray-600">|</span>
        <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
          ${e.card_id || '-'}
        </span>
      </div>
    `;

    div.append(avatarContainer, content);
    return div;
  }

  function prependEntry(e) {
    // Remove placeholder if exists
    const placeholder = feedEl.querySelector('.flex.flex-col.items-center');
    if (placeholder) placeholder.remove();

    const node = createEntry(e);
    feedEl.prepend(node);
    
    // Play sound
    playBeep();
    
    // Show Pop-up
    showPopup(e);
    
    // Remove highlight class after animation
    setTimeout(() => {
        node.classList.remove('bg-blue-50', 'dark:bg-blue-900/20', 'border-blue-100', 'dark:border-blue-800');
        node.classList.add('bg-white', 'dark:bg-gray-700/30', 'border-gray-100', 'dark:border-gray-700/50');
    }, 2000);

    while (feedEl.children.length > 50) feedEl.removeChild(feedEl.lastChild);
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
      setTimeout(poll, 1000); // Poll every 1s
    } catch (err) {
      console.error('Live feed error:', err);
      setTimeout(poll, 3000);
    }
  }

  document.addEventListener('DOMContentLoaded', poll);
})();
</script>