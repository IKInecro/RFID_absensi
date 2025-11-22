<?php
// pages/schedule.php — redesigned: clean list, modern forms, SVG icons
include 'db.php';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_schedule'])) {
    $day = $_POST['day'];
    $in = $_POST['time_in'];
    $out = $_POST['time_out'];
    $holiday = isset($_POST['is_holiday']) ? 1 : 0;
    $conn->query("INSERT INTO schedules (day, time_in, time_out, is_holiday) VALUES ('$day', '$in', '$out', $holiday)");
  } elseif (isset($_POST['edit_schedule'])) {
    $id = $_POST['id'];
    $day = $_POST['day'];
    $in = $_POST['time_in'];
    $out = $_POST['time_out'];
    $holiday = isset($_POST['is_holiday']) ? 1 : 0;
    $conn->query("UPDATE schedules SET day='$day', time_in='$in', time_out='$out', is_holiday=$holiday WHERE id=$id");
  } elseif (isset($_POST['delete_schedule'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM schedules WHERE id=$id");
  }
  header("Location: index.php?page=schedule");
  exit;
}

// Fetch Schedules
$schedules = $conn->query("SELECT * FROM schedules ORDER BY FIELD(day, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')");
?>

<div class="space-y-6 animate-fade-in">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Jadwal Absensi</h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">Atur jam masuk dan pulang serta hari libur.</p>
    </div>
    <button onclick="openModal('addModal')"
      class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-500/30 active:scale-95">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Tambah Jadwal
    </button>
  </div>

  <!-- Schedule List -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php while ($s = $schedules->fetch_assoc()): ?>
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
        <?php if ($s['is_holiday']): ?>
          <div class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl">LIBUR</div>
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

        <div class="flex gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
          <button onclick="editSchedule(<?= htmlspecialchars(json_encode($s)) ?>)"
            class="flex-1 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>
          <form method="POST" onsubmit="return confirm('Hapus jadwal ini?');" class="flex-1">
            <input type="hidden" name="delete_schedule" value="1">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button type="submit"
              class="w-full py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Hapus
            </button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>