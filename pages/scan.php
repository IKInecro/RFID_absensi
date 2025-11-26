<?php
// pages/scan.php - Registration Mode Page
// UI Premium & Real-time Updates
include_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Jakarta');

// Helper escape
function esc($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Get current modes
$reg_mode = 0;
$test_mode = 0;
$set_q = $conn->query("SELECT reg_mode, test_mode FROM settings WHERE id=1 LIMIT 1");
if ($set_q && $set_q->num_rows) {
    $s = $set_q->fetch_assoc();
    $reg_mode = intval($s['reg_mode']);
    $test_mode = intval($s['test_mode']);
}
if ($reg_mode === 1)
    $test_mode = 0;

// Get max student ID for polling
$max_id = 0;
$qMax = $conn->query("SELECT MAX(id) as m FROM students");
if ($qMax) {
    $row = $qMax->fetch_assoc();
    $max_id = intval($row['m']);
}

// AJAX endpoint for mode status (used by JS to sync)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'modes' => ['reg_mode' => $reg_mode, 'test_mode' => $test_mode]], JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<style>
    /* Custom Toggle Switch */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #2563EB;
    }

    .toggle-checkbox:checked+.toggle-label {
        background-color: #2563EB;
    }

    .toggle-checkbox:checked+.toggle-label:before {
        transform: translateX(100%);
    }

    /* Pulse Animation for new card */
    @keyframes highlight-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
        }
    }

    .new-card-highlight {
        animation: highlight-pulse 2s infinite;
    }
</style>

<div class="space-y-8 animate-fade-in max-w-4xl mx-auto pb-10">

    <!-- Header -->
    <div class="text-center space-y-3 pt-4">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            Mode Registrasi
        </h1>
        <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
            Aktifkan mode ini untuk mendaftarkan kartu RFID baru. Kartu yang ditempelkan akan otomatis tersimpan ke
            database.
        </p>
    </div>

    <!-- Main Control Card -->
    <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-8 shadow-xl relative overflow-hidden">
        <!-- Background decoration -->
        <div
            class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-50 dark:bg-blue-900/10 blur-3xl opacity-50 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-purple-50 dark:bg-purple-900/10 blur-3xl opacity-50 pointer-events-none">
        </div>

        <div class="relative z-10 flex flex-col items-center space-y-8">

            <!-- Status Badge -->
            <div class="flex flex-col items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status
                    Sistem</span>
                <span id="modeLabel"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold uppercase tracking-wide transition-all duration-300 shadow-sm border <?= $reg_mode ? 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800' : ($test_mode ? 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600') ?>">
                    <span
                        class="w-2.5 h-2.5 rounded-full <?= $reg_mode ? 'bg-blue-500 animate-pulse' : ($test_mode ? 'bg-yellow-500' : 'bg-gray-400') ?>"></span>
                    <?= $reg_mode ? 'Registrasi Aktif' : ($test_mode ? 'Tester Mode' : 'Normal Mode') ?>
                </span>
            </div>

            <!-- Big Toggle Switch -->
            <div class="flex flex-col items-center gap-4">
                <label for="regSwitchInput" class="flex items-center cursor-pointer relative">
                    <input type="checkbox" id="regSwitchInput" class="sr-only" <?= $reg_mode ? 'checked' : '' ?>>
                    <div
                        class="w-20 h-11 bg-gray-200 dark:bg-gray-700 rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus-within:ring-4 focus-within:ring-blue-500/20 toggle-bg">
                    </div>
                    <div
                        class="absolute left-1 top-1 bg-white w-9 h-9 rounded-full shadow-md transform transition-transform duration-300 ease-in-out toggle-dot">
                    </div>
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium" id="toggleText">
                    <?= $reg_mode ? 'Klik untuk mematikan' : 'Klik untuk mengaktifkan' ?>
                </p>
            </div>

            <!-- Instructions -->
            <div
                class="w-full max-w-lg bg-gray-50 dark:bg-gray-900/50 rounded-xl p-5 border border-gray-100 dark:border-gray-700/50">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Panduan Singkat
                </h3>
                <ol class="text-sm text-gray-600 dark:text-gray-400 space-y-2 list-decimal list-inside">
                    <li>Aktifkan <strong>Mode Registrasi</strong> di atas.</li>
                    <li>Tempelkan kartu RFID baru ke reader.</li>
                    <li>Kartu akan otomatis terdaftar dengan nama <em>"Baru"</em>.</li>
                    <li>Klik tombol <strong>Edit</strong> yang muncul untuk mengubah nama siswa.</li>
                    <li>Matikan mode ini setelah selesai.</li>
                </ol>
            </div>

        </div>
    </div>

    <!-- Live Feed Section -->
    <div id="feedSection"
        class="transition-all duration-500 <?= $reg_mode ? 'opacity-100 translate-y-0' : 'opacity-50 translate-y-4 grayscale' ?>">
        <div class="flex items-center justify-between mb-4 px-2">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75 <?= $reg_mode ? '' : 'hidden' ?>"></span>
                    <span
                        class="relative inline-flex rounded-full h-3 w-3 <?= $reg_mode ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                </span>
                Aktivitas Registrasi
            </h3>
            <span class="text-xs font-mono text-gray-400 dark:text-gray-500">Live Feed</span>
        </div>

        <div id="regFeed" class="space-y-4">
            <!-- Placeholder -->
            <div id="feedPlaceholder"
                class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center">
                <div
                    class="mx-auto w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Menunggu kartu ditempelkan...</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Data siswa baru akan muncul di sini.</p>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="assets/js/live_update.js"></script>
<script>
    (function () {
        const regSwitchInput = document.getElementById('regSwitchInput');
        const modeLabel = document.getElementById('modeLabel');
        const toggleText = document.getElementById('toggleText');
        const regFeed = document.getElementById('regFeed');
        const feedPlaceholder = document.getElementById('feedPlaceholder');
        const feedSection = document.getElementById('feedSection');
        const toggleBg = document.querySelector('.toggle-bg');
        const toggleDot = document.querySelector('.toggle-dot');

        let currentMaxId = <?= intval($max_id) ?>;
        let isRegMode = <?= $reg_mode ? 'true' : 'false' ?>;
        let polling = null;

        // UI Update Function
        function updateUI(active) {
            isRegMode = active;

            // Toggle Switch UI
            if (active) {
                toggleBg.classList.remove('bg-gray-200', 'dark:bg-gray-700');
                toggleBg.classList.add('bg-blue-600');
                toggleDot.classList.add('translate-x-10');
                toggleText.innerText = 'Klik untuk mematikan';

                // Badge
                modeLabel.className = 'inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold uppercase tracking-wide transition-all duration-300 shadow-sm border bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800';
                modeLabel.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span> Registrasi Aktif';

                // Feed Section
                feedSection.classList.remove('opacity-50', 'grayscale');
                feedSection.classList.add('opacity-100');
            } else {
                toggleBg.classList.add('bg-gray-200', 'dark:bg-gray-700');
                toggleBg.classList.remove('bg-blue-600');
                toggleDot.classList.remove('translate-x-10');
                toggleText.innerText = 'Klik untuk mengaktifkan';

                // Badge
                modeLabel.className = 'inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold uppercase tracking-wide transition-all duration-300 shadow-sm border bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
                modeLabel.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> Normal Mode';

                // Feed Section
                feedSection.classList.add('opacity-50', 'grayscale');
                feedSection.classList.remove('opacity-100');
            }

            // Manage Polling
            if (active) {
                startPolling();
            } else {
                stopPolling();
            }
        }

        // Toggle Action
        async function toggleMode(newState) {
            try {
                const form = new FormData();
                form.append('toggle_reg_mode', newState ? '1' : '0');

                // Optimistic Update
                updateUI(newState);

                await fetch('action_register.php', { method: 'POST', body: form });
            } catch (e) {
                console.error('Toggle failed', e);
                // Revert on failure
                updateUI(!newState);
                regSwitchInput.checked = !newState;
            }
        }

        if (regSwitchInput) {
            regSwitchInput.addEventListener('change', (e) => {
                toggleMode(e.target.checked);
            });
        }

        // Polling Logic
        function startPolling() {
            if (polling) return;
            if (!window.LiveUpdates) return;

            polling = LiveUpdates.startLongPoll({
                url: 'api/updates.php?mode=students',
                paramNameForLast: 'last_id',
                getLastValue: () => currentMaxId,
                onNew: (payload) => {
                    if (payload && payload.item) {
                        const s = payload.item;
                        currentMaxId = Math.max(currentMaxId, parseInt(s.id));
                        addStudentCard(s);
                    }
                }
            });
        }

        function stopPolling() {
            if (polling && polling.stop) polling.stop();
            polling = null;
        }

        function addStudentCard(student) {
            if (feedPlaceholder) feedPlaceholder.style.display = 'none';

            const profile = student.profile_pic ? ('uploads/' + encodeURIComponent(student.profile_pic)) : 'assets/img/default-avatar.png';

            const html = `
                <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-900/50 rounded-2xl p-5 shadow-lg flex items-center gap-5 animate-fade-in-up new-card-highlight relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500"></div>
                    
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
                        <img src="${profile}" alt="Foto" class="object-cover w-full h-full" onerror="this.src='assets/img/default-avatar.png'">
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white truncate">${escapeHtml(student.name)}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Baru</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">${escapeHtml(student.class)}</p>
                        <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            ${escapeHtml(student.card_id)}
                        </div>
                    </div>

                    <a href="index.php?page=students&edit=${student.id}" 
                       class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-md shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </a>
                </div>
            `;

            regFeed.insertAdjacentHTML('afterbegin', html);

            // Remove highlight after 2s
            setTimeout(() => {
                const el = regFeed.firstElementChild;
                if (el) el.classList.remove('new-card-highlight');
            }, 3000);
        }

        function escapeHtml(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Initialize
        if (isRegMode) {
            updateUI(true);
        } else {
            updateUI(false);
        }

    })();
</script>