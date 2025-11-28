<?php
// pages/tester.php - Clean Professional Tester UI
require_once __DIR__ . '/../db.php';
$setting = $conn->query("SELECT test_mode FROM settings WHERE id = 1")->fetch_assoc();
$test_mode = $setting['test_mode'] ?? 0;
?>

<div class="max-w-7xl mx-auto space-y-8 animate-fade-in">
  <!-- Header Section -->
  <div
    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Tester Mode</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Real-time RFID simulation & debugging environment</p>
    </div>

    <div class="flex items-center gap-4">
      <div
        class="flex items-center gap-2 px-4 py-2 rounded-full border backdrop-blur-sm <?= $test_mode ? 'bg-green-50/50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400' : 'bg-gray-50/50 border-gray-200 text-gray-600 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400' ?>">
        <span class="relative flex h-2.5 w-2.5">
          <span
            class="<?= $test_mode ? 'animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75' : 'hidden' ?>"></span>
          <span
            class="relative inline-flex rounded-full h-2.5 w-2.5 <?= $test_mode ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
        </span>
        <span
          class="text-xs font-bold uppercase tracking-wider"><?= $test_mode ? 'System Active' : 'System Inactive' ?></span>
      </div>

      <button onclick="clearData()"
        class="group flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/10 dark:text-red-400 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200">
        <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Clear Data
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Left Column: Live Feed (8 cols) -->
    <div class="lg:col-span-8 space-y-6">
      <div
        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/30 dark:bg-gray-800/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h2 class="font-semibold text-gray-900 dark:text-white">Live Activity Feed</h2>
          </div>
          <span
            class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Real-time</span>
        </div>

        <div id="feed-container"
          class="divide-y divide-gray-100 dark:divide-gray-800 max-h-[600px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
          <div class="p-16 text-center empty flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Waiting for incoming data...</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Simulate a tap or use the hardware reader</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Sidebar (4 cols) -->
    <div class="lg:col-span-4 space-y-6">

      <!-- Control Panel -->
      <?php if ($test_mode): ?>
        <div
          class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20">
          <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Simulator Controls
          </h3>
          <p class="text-indigo-100 text-sm mb-6 opacity-90">Generate simulated RFID taps to test the system without
            hardware.</p>

          <button onclick="simulate('random')"
            class="w-full py-3 px-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-semibold text-white transition-all duration-200 flex items-center justify-center gap-2 group">
            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Simulate Student Tap
          </button>
        </div>
      <?php else: ?>
        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl p-6">
          <div class="flex items-start gap-4">
            <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400 shrink-0">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-amber-800 dark:text-amber-200">Simulation Disabled</h3>
              <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">Enable "Tester Mode" in your dashboard settings
                to use the simulator tools.</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Latest Result Card -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
          Latest Scan Result
        </h3>

        <div id="latest-scan">
          <!-- Empty State for Latest Scan -->
          <div class="text-center py-8">
            <div
              class="w-24 h-24 mx-auto bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-4 border-2 border-dashed border-gray-200 dark:border-gray-700">
              <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
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
    const isLate = status !== 'On Time';
    const statusColor = !isLate ? 'text-green-700 bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400' : 'text-red-700 bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400';
    const cardBorder = !isLate ? 'border-green-500' : 'border-red-500';

    latestEl.innerHTML = `
      <div class="animate-fade-in">
        <div class="relative w-32 h-32 mx-auto mb-6">
          <div class="absolute inset-0 rounded-full border-4 ${cardBorder} opacity-20 animate-ping"></div>
          <img src="${profile}" class="w-full h-full rounded-full object-cover border-4 ${cardBorder} shadow-xl" onerror="this.src='assets/img/default-avatar.png'">
          <div class="absolute bottom-0 right-0 p-2 bg-white dark:bg-gray-800 rounded-full shadow-md border border-gray-100 dark:border-gray-700">
             <svg class="w-5 h-5 ${!isLate ? 'text-green-500' : 'text-red-500'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${!isLate ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}" />
             </svg>
          </div>
        </div>
        
        <div class="text-center space-y-2">
          <h4 class="text-2xl font-bold text-gray-900 dark:text-white truncate">${e.student_name || 'Unknown'}</h4>
          <p class="text-gray-500 dark:text-gray-400 font-medium">${e.student_class || 'No Class'}</p>
          
          <div class="pt-4 flex justify-center">
            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border ${statusColor} text-sm font-bold shadow-sm">
              <span class="w-2 h-2 rounded-full ${!isLate ? 'bg-green-500' : 'bg-red-500'}"></span>
              ${status} • ${e.timestamp.split(' ')[1] || ''}
            </span>
          </div>
        </div>
      </div>
    `;
  }

  function buildItemHtml(item) {
    const profile = item.profile_pic ? 'uploads/' + encodeURIComponent(item.profile_pic) : 'assets/img/default-avatar.png';
    const status = item.status || 'Unknown';
    const isLate = status !== 'On Time';
    const statusCls = !isLate ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    const icon = !isLate ?
      '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' :
      '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

    return `
      <div class="flex items-center gap-4 group">
        <div class="relative flex-shrink-0">
          <img src="${profile}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-200" onerror="this.src='assets/img/default-avatar.png'">
          <div class="absolute -bottom-1 -right-1 p-0.5 bg-white dark:bg-gray-800 rounded-full">
            <div class="w-3 h-3 rounded-full ${!isLate ? 'bg-green-500' : 'bg-red-500'} border-2 border-white dark:border-gray-800"></div>
          </div>
        </div>
        
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">${item.student_name || ('Card: ' + item.card_id)}</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${item.student_class || '-'}</p>
            </div>
            <span class="text-xs font-mono text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded-md">${item.timestamp.split(' ')[1] || '-'}</span>
          </div>
        </div>
        
        <div class="flex-shrink-0">
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wide ${statusCls}">
            ${icon}
            ${status}
          </span>
        </div>
      </div>
    `;
  }

  function addItem(item, animate = true) {
    if (seenIds.has(item.id)) return;
    seenIds.add(item.id);

    // Remove empty state if present
    const emptyState = feedEl.querySelector('.empty');
    if (emptyState) emptyState.remove();

    const div = document.createElement('div');
    div.className = 'p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-200';
    if (animate) div.classList.add('animate-slide-in');
    div.innerHTML = buildItemHtml(item);

    feedEl.insertBefore(div, feedEl.firstChild);
    
    // Keep list manageable
    if (feedEl.children.length > 50) {
      feedEl.lastChild.remove();
    }

    updateLatestScan(item);
    if (animate) playBeep();
  }

  async function fetchUpdates() {
    try {
      const res = await fetch(`../api/updates.php?mode=test&last_ts=${encodeURIComponent(last_ts)}`);
      const data = await res.json();

      if (data.new && data.entries) {
        // Process oldest to newest so they appear in correct order
        data.entries.sort((a, b) => a.timestamp.localeCompare(b.timestamp));
        
        data.entries.forEach(entry => {
          addItem(entry);
          if (entry.timestamp > last_ts) last_ts = entry.timestamp;
        });
      }
    } catch (e) {
      console.error("Polling error:", e);
    } finally {
      // Continue polling
      setTimeout(fetchUpdates, 1000);
    }
  }

  async function clearData() {
    if (!confirm('Are you sure you want to clear all test data?')) return;
    
    try {
      await fetch('tester_clear.php', { method: 'POST' });
      feedEl.innerHTML = `
        <div class="p-16 text-center empty flex flex-col items-center justify-center">
          <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-gray-500 dark:text-gray-400 font-medium">Waiting for incoming data...</p>
          <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Simulate a tap or use the hardware reader</p>
        </div>
      `;
      latestEl.innerHTML = `
        <div class="text-center py-8">
          <div class="w-24 h-24 mx-auto bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-4 border-2 border-dashed border-gray-200 dark:border-gray-700">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
        </div>
      `;
      seenIds.clear();
      last_ts = '';
    } catch (e) {
      alert('Failed to clear data');
    }
  }

  async function simulate(type) {
    const ts = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const dummy = {
      device_id: 'SIMULATOR',
      uid: 'SIM-' + Math.floor(1000 + Math.random() * 9000),
      timestamp: ts
    };

    try {
      await fetch('../api/attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dummy)
      });
      // The polling loop will pick this up
    } catch (e) {
      console.error("Simulation failed:", e);
    }
  }

  // Start polling
  fetchUpdates();
</script>