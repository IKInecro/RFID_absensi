<?php
// pages/tester.php - Clean Professional Tester UI
require_once __DIR__ . '/../db.php';
$setting = $conn->query("SELECT test_mode FROM settings WHERE id = 1")->fetch_assoc();
$test_mode = $setting['test_mode'] ?? 0;
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div
    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-200 dark:border-gray-700 pb-5">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Tester Mode</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Simulate RFID taps for debugging.</p>
    </div>

    <div class="flex items-center gap-3">
      <div
        class="flex items-center gap-2 px-3 py-1.5 rounded-full border <?= $test_mode ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400' : 'bg-gray-50 border-gray-200 text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' ?>">
        <span class="relative flex h-2 w-2">
          <span
            class="<?= $test_mode ? 'animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75' : 'hidden' ?>"></span>
          <span
            class="relative inline-flex rounded-full h-2 w-2 <?= $test_mode ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
        </span>
        <span class="text-xs font-bold uppercase tracking-wider"><?= $test_mode ? 'Active' : 'Inactive' ?></span>
      </div>

      <button onclick="clearData()"
        class="text-sm text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
        Clear Data
      </button>
    </div>
  </div>

  <?php if ($test_mode): ?>
    <!-- Simulation Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Simulator</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate fake attendance records.</p>
          </div>
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <button onclick="simulate('random')"
            class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-sm shadow-sm">
            Random Student
          </button>
          <button onclick="simulate('unknown')"
            class="flex-1 sm:flex-none px-4 py-2 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg font-medium transition-colors text-sm shadow-sm">
            Unknown Card
          </button>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div
      class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 flex items-center gap-4">
      <div class="p-2 bg-yellow-100 dark:bg-yellow-900/40 rounded-full text-yellow-600 dark:text-yellow-400">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div>
        <h3 class="font-bold text-yellow-800 dark:text-yellow-300">Tester Mode Inactive</h3>
        <p class="text-sm text-yellow-700 dark:text-yellow-400">Enable Tester Mode in Dashboard to use the simulator.</p>
      </div>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Feed List (Left 2/3) -->
    <div class="lg:col-span-2">
      <div
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/30">
          <h2 class="font-semibold text-gray-900 dark:text-white">Live Results</h2>
          <span class="text-xs text-gray-500 dark:text-gray-400">Real-time</span>
        </div>

        <div id="feed-container" class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[500px] overflow-y-auto">
          <div class="p-12 text-center empty">
            <div
              class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 text-gray-400">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">No data yet.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Latest Scan (Right 1/3) -->
    <div class="lg:col-span-1">
      <div class="sticky top-6">
        <div
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-center">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Latest Result</h3>

          <div id="latest-scan">
            <div
              class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
              <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Waiting for simulation...</p>
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
    const osc = audioCtx.createOscillator(), gain = audioCtx.createGain();
    osc.connect(gain); gain.connect(audioCtx.destination);
    osc.type = 'sine'; osc.frequency.setValueAtTime(900, audioCtx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(1400, audioCtx.currentTime + 0.1);
    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
    osc.start(); osc.stop(audioCtx.currentTime + 0.3);
  }

  const feedEl = document.getElementById('feed-container');
  const latestEl = document.getElementById('latest-scan');
  const seenIds = new Set();
  let last_ts = '';

  function updateLatestScan(e) {
    const profile = e.profile_pic ? 'uploads/' + encodeURIComponent(e.profile_pic) : 'assets/img/default-avatar.png';
    const status = e.status || e.schedule_status || 'Hadir';
    const statusColor = status === 'On Time' ? 'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400' : 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400';

    latestEl.innerHTML = `
      <div class="w-24 h-24 mx-auto rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 mb-4 ring-4 ring-white dark:ring-gray-800 shadow-lg">
        <img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">
      </div>
      <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-1 truncate">${e.student_name || '-'}</h4>
      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">${e.student_class || '-'}</p>
      <div class="inline-flex items-center px-3 py-1 rounded-full ${statusColor} text-sm font-bold">
        ${e.timestamp || '-'}
      </div>
    `;
  }

  function buildItemHtml(item) {
    const profile = item.profile_pic ? 'uploads/' + encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    const status = item.status || 'Unknown';
    const statusCls = status === 'On Time' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';

    return `
      <div class="flex-shrink-0">
        <img src="${profile}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" onerror="this.src='assets/img/default-avatar.png'">
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-baseline">
          <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">${item.student_name || ('Card: ' + item.card_id)}</h4>
          <span class="text-xs font-mono text-gray-500 dark:text-gray-400">${item.timestamp || '-'}</span>
        </div>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="text-xs font-medium text-gray-600 dark:text-gray-400">${item.student_class || '-'}</span>
          <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase ${statusCls}">${status}</span>
        </div>
      </div>
    `;
  }

  function addItem(item, animate = true) {
    if (item.id && seenIds.has(String(item.id))) return;
    if (item.timestamp && last_ts && item.timestamp === last_ts) return;

    // Remove empty state
    const empty = feedEl.querySelector('.empty');
    if (empty) empty.remove();

    const node = document.createElement('div');
    node.className = 'flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors animate-fade-in';
    node.innerHTML = buildItemHtml(item);

    feedEl.insertBefore(node, feedEl.firstChild);
    if (animate) { playBeep(); updateLatestScan(item); }
    if (item.id) seenIds.add(String(item.id));
    if (item.timestamp) last_ts = item.timestamp;
    while (feedEl.children.length > 20) feedEl.removeChild(feedEl.lastChild);
  }

  setInterval(async () => {
    try {
      const res = await fetch('pages/test_data.json', { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      if (!Array.isArray(data)) return;
      for (let i = data.length - 1; i >= 0; i--) {
        const it = data[i];
        if (it.timestamp && last_ts && it.timestamp <= last_ts) continue;
        if (it.id && seenIds.has(String(it.id))) continue;
        addItem(it, true);
      }
    } catch (e) { console.error(e); }
  }, 3000);

  async function clearData() {
    if (!confirm('Clear all test data?')) return;
    try {
      const res = await fetch('pages/tester_clear.php', { method: 'POST' });
      const json = await res.json();
      if (res.ok && json.success) {
        feedEl.innerHTML = '<div class="p-12 text-center empty"><div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 text-gray-400"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div><p class="text-gray-500 dark:text-gray-400 font-medium">Data cleared.</p></div>';
        seenIds.clear(); last_ts = '';
        latestEl.innerHTML = '<div class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400"><svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg></div><p class="text-sm text-gray-500 dark:text-gray-400">Waiting for simulation...</p>';
      }
    } catch (e) { console.error(e); }
  }

  window.simulate = async (type) => {
    try {
      const res = await fetch('pages/tester_simulate.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'type=' + type });
      const json = await res.json();
      if (json.success) {
        addItem(json.data, true);
      }
    } catch (e) { console.error(e); }
  };
</script>