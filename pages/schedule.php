<?php
// pages/schedules.php
include 'db.php';

// fetch all schedules
$res = $conn->query("SELECT * FROM schedules ORDER BY FIELD(day,'Mon','Tue','Wed','Thu','Fri','Sat','Sun')");
$days = ['Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu','Sun'=>'Minggu'];
?>
<div class="space-y-6">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4">Tambah / Edit Jadwal</h2>
    <form action="action_schedule.php" method="POST" class="grid grid-cols-1 sm:grid-cols-6 gap-4">
      <select name="day" required class="p-2 border rounded dark:bg-gray-700">
        <?php foreach($days as $d=>$n): ?>
          <option value="<?= $d ?>"><?= $n ?></option>
        <?php endforeach; ?>
      </select>
      <input type="time" name="time_in" required class="p-2 border rounded dark:bg-gray-700">
      <input type="time" name="time_out" required class="p-2 border rounded dark:bg-gray-700">
      <input type="number" name="grace_period" placeholder="Grace (mnt)" class="p-2 border rounded dark:bg-gray-700">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="is_holiday" value="1"> Holiday
      </label>
      <button type="submit" name="create" class="bg-blue-600 text-white px-3 py-2 rounded">Simpan</button>
    </form>
  </div>

  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4">Daftar Jadwal</h2>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-100 dark:bg-gray-700">
          <th class="p-2">Hari</th><th class="p-2">Jam Masuk</th><th class="p-2">Jam Pulang</th><th class="p-2">Grace</th><th class="p-2">Holiday</th><th class="p-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$res->fetch_assoc()): ?>
          <tr class="border-b dark:border-gray-700">
            <td class="p-2"><?= $days[$row['day']] ?></td>
            <td class="p-2"><?= $row['time_in'] ?></td>
            <td class="p-2"><?= $row['time_out'] ?></td>
            <td class="p-2"><?= $row['grace_period'] ?> mnt</td>
            <td class="p-2"><?= $row['is_holiday'] ? 'Ya' : 'Tidak' ?></td>
            <td class="p-2">
              <a href="action_schedule.php?delete=<?= $row['id'] ?>" class="bg-red-600 text-white px-2 py-1 rounded" onclick="return confirm('Hapus jadwal?')">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
