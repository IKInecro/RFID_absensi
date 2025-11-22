<?php
// pages_user/schedule.php — Read-only version for Users
include __DIR__ . '/../db.php';

// Fetch Schedules
$schedules = $conn->query("SELECT * FROM schedules ORDER BY FIELD(day, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')");
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Jadwal Absensi</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Jadwal jam masuk dan pulang.</p>
        </div>
    </div>

    <!-- Schedule List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($s = $schedules->fetch_assoc()): ?>
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                <?php if ($s['is_holiday']): ?>
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl">LIBUR
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold
              <?= $s['is_holiday'] ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' ?>">
                            <?= substr($s['day'], 0, 3) ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= $s['day'] ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?= $s['is_holiday'] ? 'Tidak ada absensi' : 'Jam Operasional' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (!$s['is_holiday']): ?>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <span class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Masuk
                            </span>
                            <span
                                class="font-mono font-bold text-gray-900 dark:text-white"><?= date('H:i', strtotime($s['time_in'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <span class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Pulang
                            </span>
                            <span
                                class="font-mono font-bold text-gray-900 dark:text-white"><?= date('H:i', strtotime($s['time_out'])) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        class="h-24 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-6 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-600">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Libur
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>