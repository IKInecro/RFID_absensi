<?php
include 'db.php';

// daftar nama hari biar rapi
$dayNames = [
  'Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu',
  'Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu','Sun'=>'Minggu'
];

// Ambil semua jadwal
$schedules = $conn->query("SELECT * FROM schedules ORDER BY 
   FIELD(day,'Mon','Tue','Wed','Thu','Fri','Sat','Sun')");

// Edit mode
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if($edit_id){
    $res = $conn->query("SELECT * FROM schedules WHERE id=$edit_id");
    $edit_data = $res->fetch_assoc();
}
?>
<div class="space-y-6">

  <!-- Form Tambah / Edit -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">
      <?= $edit_data ? "Edit Jadwal" : "Tambah Jadwal" ?>
    </h2>
    <form action="action_schedule.php" method="POST" class="space-y-4">
      <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

      <div>
        <label class="block mb-1 text-gray-700 dark:text-gray-300">Hari</label>
        <select name="day" class="w-full p-2 border rounded dark:bg-gray-700 dark:text-gray-100" required>
          <option value="">-- Pilih Hari --</option>
          <?php foreach($dayNames as $key=>$val): ?>
            <option value="<?= $key ?>" 
              <?= ($edit_data && $edit_data['day']==$key)?'selected':'' ?>>
              <?= $val ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1 text-gray-700 dark:text-gray-300">Jam Masuk</label>
          <input type="time" name="time_in"
                 value="<?= $edit_data['time_in'] ?? '' ?>"
                 class="w-full p-2 border rounded dark:bg-gray-700 dark:text-gray-100" required>
        </div>
        <div>
          <label class="block mb-1 text-gray-700 dark:text-gray-300">Jam Keluar</label>
          <input type="time" name="time_out"
                 value="<?= $edit_data['time_out'] ?? '' ?>"
                 class="w-full p-2 border rounded dark:bg-gray-700 dark:text-gray-100" required>
        </div>
        <div>
          <label class="block mb-1 text-gray-700 dark:text-gray-300">Toleransi (menit)</label>
          <input type="number" name="grace_period"
                 value="<?= $edit_data['grace_period'] ?? '10' ?>"
                 class="w-full p-2 border rounded dark:bg-gray-700 dark:text-gray-100" required>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_holiday" value="1"
               <?= ($edit_data && $edit_data['is_holiday'])?'checked':'' ?>
               class="w-4 h-4">
        <label class="text-gray-700 dark:text-gray-300">Tandai sebagai Hari Libur</label>
      </div>

      <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
        <?= $edit_data ? "Update" : "Simpan" ?>
      </button>
      <?php if($edit_data): ?>
        <a href="index.php?page=schedule" class="ml-3 text-gray-600 dark:text-gray-300">Batal</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Tabel Jadwal -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Daftar Jadwal</h2>
    <table class="w-full border border-gray-300 dark:border-gray-700 border-collapse">
      <thead>
        <tr class="bg-blue-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
          <th class="border p-2">Hari</th>
          <th class="border p-2">Jam Masuk</th>
          <th class="border p-2">Jam Keluar</th>
          <th class="border p-2">Toleransi</th>
          <th class="border p-2">Libur?</th>
          <th class="border p-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if($schedules->num_rows): ?>
      <?php while($row = $schedules->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
          <td class="border p-2 dark:border-gray-700"><?= $dayNames[$row['day']] ?></td>
          <td class="border p-2 dark:border-gray-700"><?= $row['time_in'] ?></td>
          <td class="border p-2 dark:border-gray-700"><?= $row['time_out'] ?></td>
          <td class="border p-2 dark:border-gray-700"><?= $row['grace_period'] ?> menit</td>
          <td class="border p-2 dark:border-gray-700">
            <?= $row['is_holiday'] ? "✅" : "❌" ?>
          </td>
          <td class="border p-2 dark:border-gray-700 text-center">
            <a href="index.php?page=schedule&edit=<?= $row['id'] ?>"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded transition">Edit</a>
            <a href="action_schedule.php?delete=<?= $row['id'] ?>"
               onclick="return confirm('Hapus jadwal ini?')"
               class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center p-4 text-gray-600 dark:text-gray-300">Belum ada jadwal</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
