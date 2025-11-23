<?php
// pages/tester.php - Premium Tester Mode UI
require_once __DIR__ . '/../db.php';
$setting = $conn->query("SELECT test_mode FROM settings WHERE id = 1")->fetch_assoc();
$test_mode = $setting['test_mode'] ?? 0;
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header with Gradient -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1
        class="text-4xl font-black bg-gradient-to-r from-purple-600 to-pink-600 dark:from-purple-400 dark:to-pink-400 bg-clip-text text-transparent">
        Tester Mode</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Simulasi dan debugging perangkat RFID dengan UI premium</p>
    </div>

    <div class="flex items-center gap-3">
      <div
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r <?= $test_mode ? 'from-green-500 to-emerald-600' : 'from-gray-500 to-gray-600' ?> text-white shadow-lg">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
        </span>
        <span class="text-sm font-bold"><?= $test_mode ? 'Mode Aktif' : 'Mode Non-Aktif' ?></span>
      </div>

      <button onclick="clearData()"
        class="flex items-center gap-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-red-500/30 active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Clear Data
      </button>
    </div>
  </div>

  <?php if ($test_mode): ?>
    <!-- Premium Simulation Controls -->
    <div
      class="relative overflow-hidden bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-2 border-purple-200 dark:border-purple-800 rounded-3xl p-8 shadow-xl">
      <div
        class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-purple-300/20 to-pink-300/20 rounded-full blur-3xl">
      </div>
      <div class="relative z-10 flex flex-wrap items-center gap-6">
        <div class="flex items-center gap-4 text-purple-800 dark:text-purple-300">
          <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-black">Simulasi Tap RFID</h3>
            <p class="text-sm opacity-75">Klik tombol untuk mensimulasikan tap kartu</p>
          </div>
        </div>

        <div class="flex gap-4 ml-auto">
          <button onclick="simulate('random')"
            class="group relative flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold transition-all shadow-lg shadow-purple-500/30 active:scale-95 overflow-hidden">
            <div
              class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
            </div>
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Random Student
          </button>
          <button onclick="simulate('unknown')"
            class="flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-gray-700 to-gray-800 hover:from-gray-800 hover:to-gray-900 text-white font-bold transition-all shadow-lg shadow-gray-500/30 active:scale-95">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Unknown Card
          </button>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div
      class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-3xl p-8 flex items-center gap-6 shadow-xl">
      <div class="p-4 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl text-white shadow-lg">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div>
        <h3 class="text-2xl font-black text-yellow-800 dark:text-yellow-300 mb-1">Tester Mode Non-Aktif</h3>
        <p class="text-yellow-700 dark:text-yellow-400">Aktifkan Tester Mode di Dashboard untuk menggunakan fitur simulasi
        </p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Live Feed & Latest Scan - Premium 2 Column Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Latest Scan Card (Left 1/3) -->
    <div class="lg:col-span-1">
      <div
        class="relative overflow-hidden bg-gradient-to-br from-purple-500 to-pink-600 dark:from-purple-900 dark:to-pink-900 rounded-3xl shadow-2xl p-6 sticky top-6">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative z-10">
          <h3 class="text-2xl font-black text-white mb-6 flex items-center gap-3">
            <div class="p-2 bg-white/20 backdrop-blur rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            Scan Terakhir
          </h3>

          <div id="latest-scan" class="text-center py-8 bg-white/10 backdrop-blur rounded-2xl">
            <div class="w-28 h-28 mx-auto bg-white/20 rounded-full flex items-center justify-center mb-6 animate-pulse">
              <svg class="w-14 h-14 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <p class="text-lg font-bold text-white/90">Menunggu data...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Feed List (Right 2/3) -->
    <div class="lg:col-span-2">
      <div
        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 rounded-3xl shadow-2xl overflow-hidden">
        <div
          class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
          <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <span class="relative flex h-3 w-3">
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
            </span>
            Live Feed
            <span
              class="ml-auto text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg">Realtime
              Monitor</span>
          </h3>
        </div>

        <div id="feed-container" class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[650px] overflow-y-auto">
          <div class="p-10 text-center text-gray-500 dark:text-gray-400 empty">
            <div
              class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-full flex items-center justify-center">
              <svg class="w-10 h-10 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <p class="text-lg font-semibold">Belum ada data masuk</p>
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
    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
    osc.start(); osc.stop(audioCtx.currentTime + 0.5);
  }

  const feedEl = document.getElementById('feed-container');
  const latestEl = document.getElementById('latest-scan');
  const seenIds = new Set();
  let last_ts = '';

  function updateLatestScan(e) {
    const profile = e.profile_pic ? 'uploads/' + encodeURIComponent(e.profile_pic) : 'assets/img/default-avatar.png';
    const status = e.status || e.schedule_status || 'Hadir';
    const statusCls = status === 'On Time' ? 'bg-green-500' : 'bg-red-500';
    latestEl.innerHTML = `
    <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-white/20 mb-4 ring-4 ring-white/50 shadow-2xl">
      <img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">
    </div>
    <h4 class="text-3xl font-black text-white mb-2">${e.student_name || '-'}</h4>
    <p class="text-lg font-semibold text-white/80 mb-3">${e.student_class || '-'}</p>
    <div class="inline-block px-4 py-2 ${statusCls} text-white font-bold rounded-xl shadow-lg">${e.timestamp || '-'}</div>
  `;
  }

  function buildItemHtml(item) {
    const profile = item.profile_pic ? 'uploads/' + encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    const status = item.status || 'Tidak Diketahui';
    const statusCls = status === 'On Time' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
    return `
    <div class="group flex items-center gap-5 p-5 hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-all duration-300">
      <div class="relative">
        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/50 dark:to-pink-900/50 ring-2 ring-purple-200 dark:ring-purple-800 group-hover:ring-4 group-hover:ring-purple-400 dark:group-hover:ring-purple-600 transition-all">
          <img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">
        </div>
        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-br from-purple-500 to-pink-600 border-2 border-white dark:border-gray-800 rounded-full"></div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-start mb-1">
          <h4 class="text-base font-black text-gray-900 dark:text-white truncate group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">${item.student_name || ('Card: ' + item.card_id)}</h4>
          <span class="text-xs text-gray-500 dark:text-gray-400 font-mono ml-2">${item.timestamp || '-'}</span>
        </div>
        <div class="flex items-center gap-2 text-sm">
          <span class="font-semibold text-purple-600 dark:text-purple-400">${item.student_class || '-'}</span>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${statusCls}">${status}</span>
        </div>
      </div>
    </div>
  `;
  }

  function addItem(item, animate = true) {
    if (item.id && seenIds.has(String(item.id))) return;
    if (item.timestamp && last_ts && item.timestamp === last_ts) return;
    const node = document.createElement('div');
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
    if (!confirm('Yakin ingin menghapus semua data test tap?')) return;
    try {
      const res = await fetch('pages/tester_clear.php', { method: 'POST' });
      const json = await res.json();
      if (res.ok && json.success) {
        feedEl.innerHTML = '<div class="p-10 text-center text-gray-500 dark:text-gray-400 empty"><div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-full flex items-center justify-center"><svg class="w-10 h-10 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><p class="text-lg font-semibold">Data telah dihapus</p></div>';
        seenIds.clear(); last_ts = ''; updateLatestScan({ student_name: '-', student_class: '-', timestamp: '-' });
        alert(json.message || 'Data tester dibersihkan.');
      }
    } catch (e) { console.error(e); }
  }

  window.simulate = async (type) => {
    try {
      const res = await fetch('pages/tester_simulate.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'type=' + type });
      const json = await res.json();
      if (json.success) {
        addItem(json.data, true);
        alert('Simulasi berhasil: ' + json.message);
      }
    } catch (e) { console.error(e); }
  };
</script>