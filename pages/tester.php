<?php
// pages/tester.php — redesigned: clean UI, SVG icons, sound effects
$setting = $conn->query("SELECT test_mode FROM settings WHERE id = 1")->fetch_assoc();
$test_mode = $setting['test_mode'] ?? 0;
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Tester Mode</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Simulasi dan debugging perangkat RFID.</p>
    </div>

    <div class="flex items-center gap-3">
      <div
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
        <span class="relative flex h-3 w-3">
          <span
            class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $test_mode ? 'bg-green-400' : 'bg-gray-400' ?> opacity-75"></span>
          <span
            class="relative inline-flex rounded-full h-3 w-3 <?= $test_mode ? 'bg-green-500' : 'bg-gray-500' ?>"></span>
        </span>
        <span
          class="text-sm font-medium <?= $test_mode ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' ?>">
          <?= $test_mode ? 'Mode Aktif' : 'Mode Non-Aktif' ?>
        </span>
      </div>

      <button onclick="clearData()"
        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl font-medium transition-all shadow-lg shadow-red-500/30 active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Clear Data
      </button>
    </div>
  </div>

  <?php if ($test_mode): ?>
    <!-- Simulation Controls -->
    <div
      class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl p-6 flex flex-wrap items-center gap-6">
      <div class="flex items-center gap-3 text-blue-800 dark:text-blue-300 font-semibold">
        <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
        </div>
        <span>Simulasi Tap:</span>
      </div>

      <div class="flex gap-3">
        <button onclick="simulate('random')"
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-all shadow-lg shadow-blue-500/30 active:scale-95">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Random Student
        </button>
        <button onclick="simulate('unknown')"
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-600 hover:bg-gray-700 text-white font-medium transition-all shadow-lg shadow-gray-500/30 active:scale-95">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Unknown Card
        </button>
      </div>
    </div>
  <?php else: ?>
    <div
      class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 flex items-center gap-4">
      <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl text-yellow-600 dark:text-yellow-400">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div>
        <h3 class="text-lg font-bold text-yellow-800 dark:text-yellow-300">Tester Mode Non-Aktif</h3>
        <p class="text-yellow-700 dark:text-yellow-400 mt-1">Aktifkan Tester Mode di Dashboard untuk menggunakan fitur
          simulasi.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Live Feed & Latest Scan -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Latest Scan Card -->
    <div class="lg:col-span-1">
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          Scan Terakhir
        </h3>

        <div id="latest-scan" class="text-center py-8">
          <div
            class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 animate-pulse">
            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400">Menunggu data...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Feed List -->
    <div class="lg:col-span-2">
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
            Live Feed
          </h3>
          <span
            class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">JSON
            Polling</span>
        </div>

        <div id="feed-container" class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
          <!-- Items will be injected here -->
          <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p>Belum ada data masuk</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let lastData = [];
  const feedContainer = document.getElementById('feed-container');
  const latestScan = document.getElementById('latest-scan');

  // Sound Effects
  const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  function playBeep(type = 'success') {
    if (audioCtx.state === 'suspended') audioCtx.resume();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);

    if (type === 'success') {
      osc.type = 'sine';
      osc.frequency.setValueAtTime(800, audioCtx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
      gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
      osc.start();
      osc.stop(audioCtx.currentTime + 0.1);
    } else {
      osc.frequency.linearRampToValueAtTime(100, audioCtx.currentTime + 0.2);
      gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
      gain.gain.linearRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
      osc.start();
      osc.stop(audioCtx.currentTime + 0.2);
    }
  }

  function buildItemHtml(item) {
    const profile = item.profile_pic ? 'uploads/' + encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    const name = item.student_name || 'Unknown';
    const cls = item.student_class || '-';
    const ts = item.time || '-';
    const status = item.status || 'Tidak Diketahui';

    const statusClasses = status === 'On Time' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';

    return `
      <div class="flex-shrink-0">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
          <img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">
        </div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-start">
          <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">${name}</h4>
          <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap ml-2">${ts}</span>
        </div>
        <div class="flex items-center gap-2 mt-1">
          <span class="text-xs text-gray-500 dark:text-gray-400">${cls}</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ${statusClasses}">${status}</span>
        </div>
      </div>
    `;
  }

  function addItem(item, animate = true) {
    // guard: if item has id and already seen, skip
    const id = item.id ? String(item.id) : null;
    if (id && seenIds.has(id)) return;
    // guard: if timestamp equals last_ts, skip (not new)
    if (item.timestamp && last_ts && item.timestamp === last_ts && animate) return;

    const node = document.createElement('div');
    node.className = 'flex items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-200' + (animate ? ' animate-slide-down bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800' : '');

    if (id) node.setAttribute('data-id', id);
    if (item.timestamp) node.setAttribute('data-ts', item.timestamp);
    node.innerHTML = buildItemHtml(item);

    // remove placeholder if exists
    const ph = listEl.querySelector('.empty');
    if (ph) ph.remove();

    listEl.insertBefore(node, listEl.firstChild);

    if (animate) {
      playBeep('success'); // Play sound
      setTimeout(() => {
        node.classList.remove('bg-blue-50', 'dark:bg-blue-900/20', 'border-blue-100', 'dark:border-blue-800');
        node.classList.add('bg-white', 'dark:bg-gray-700/50', 'border-gray-100', 'dark:border-gray-700');
      }, 2000);
    }

    if (id) seenIds.add(id);
    if (item.timestamp) last_ts = item.timestamp;

    if (animate) lastUpdateEl.innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });

    // limit list
    while (listEl.children.length > 120) {
      const last = listEl.lastChild;
      const remId = last?.getAttribute?.('data-id');
      if (remId) seenIds.delete(remId);
      last.remove();
    }
  }

  // When new payload arrives (from live_update helper)
  function onNewTest(item) {
    if (!item) return;
    if (item.timestamp && last_ts) {
      if (item.timestamp === last_ts) return;
    }
    addItem(item, true);

    // update latest panel
    const profile = item.profile_pic ? 'uploads/' + encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    latestAvatar.innerHTML = `<img src="${profile}" class="w-full h-full object-cover" onerror="this.src='assets/img/default-avatar.png'">`;
    latestName.innerText = item.name || '-';
    latestClass.innerText = item.class || '-';
    latestStatus.innerText = item.schedule_status || 'Tidak Diketahui';
    latestStatus.className = `px-4 py-1.5 rounded-full text-sm font-semibold ${getStatusClasses(latestStatus.innerText)}`;
  }

  // Use LiveUpdates long-poll if available; fallback to safe interval polling that checks last_ts
  if (window.LiveUpdates && typeof LiveUpdates.startLongPoll === 'function') {
    LiveUpdates.startLongPoll({
      url: 'api/updates.php?mode=test',
      paramNameForLast: 'last_ts',
      getLastValue: () => last_ts || '',
      onNew: function (payload) {
        if (payload && payload.item) {
          const it = payload.item;
          if (Array.isArray(it)) {
            // Add oldest first so newest ends up at top
            for (let i = it.length - 1; i >= 0; i--) onNewTest(it[i]);
          } else {
            onNewTest(it);
          }
        }
      },
      onError: function (err) {
        console.error('LiveUpdates error', err);
      }
    });
  } else {
    // fallback safe polling (every 3.5s)
    setInterval(async () => {
      try {
        // FIX: use pages/test_data.json
        const res = await fetch('pages/test_data.json', { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        if (!Array.isArray(data) || data.length === 0) return;

        for (let i = data.length - 1; i >= 0; i--) {
          const item = data[i];
          if (item.timestamp && last_ts && item.timestamp <= last_ts) continue; // Skip if older or same
          if (item.id && seenIds.has(String(item.id))) continue;
          onNewTest(item);
        }
      } catch (e) {
        console.error('Polling error', e);
      }
    }, 3500);
  }

  // Clear handler
  clearBtn.addEventListener('click', async () => {
    if (!confirm('Yakin ingin menghapus semua data test tap (hanya list tester)?')) return;
    clearBtn.disabled = true;
    const original = clearBtn.innerText;
    clearBtn.innerText = 'Membersihkan...';
    try {
      // FIX: use pages/tester_clear.php
      const res = await fetch('pages/tester_clear.php', { method: 'POST', headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (res.ok && json.success) {
        // reset UI state
        listEl.innerHTML = `
          <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500 empty">
            <svg class="w-12 h-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>Data test tap telah dihapus.</p>
          </div>
        `;
        seenIds.clear();
        last_ts = '';
        latestAvatar.innerHTML = '—';
        latestName.innerText = '-';
        latestClass.innerText = '-';
        latestStatus.innerText = '-';
        latestStatus.className = 'px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
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
  }) ();
</script>