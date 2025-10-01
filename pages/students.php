<?php
// pages/students.php
include 'db.php';

// pagination
$limit = 10;
$pageNo = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($pageNo-1) * $limit;

// filters
$kelas = isset($_GET['kelas']) ? $conn->real_escape_string($_GET['kelas']) : '';
$sub   = isset($_GET['sub']) ? $conn->real_escape_string($_GET['sub']) : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = [];
if($kelas) $where[] = "class LIKE '{$kelas}%'";
if($sub) $where[] = "class = '{$kelas}-{$sub}'";
if($search) $where[] = "(name LIKE '%$search%' OR card_id LIKE '%$search%')";

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// total rows
$totalRows = $conn->query("SELECT COUNT(*) as c FROM students $whereSql")->fetch_assoc()['c'];
$totalPages = ceil($totalRows / $limit);

// fetch data
$res = $conn->query("SELECT * FROM students $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset");

// for edit
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if($edit_id){
    $r = $conn->query("SELECT * FROM students WHERE id=$edit_id");
    $edit_data = $r->fetch_assoc();
}
?>
<div class="space-y-6">
  <!-- Form Add/Edit -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200"><?= $edit_data ? "Edit Siswa" : "Tambah Siswa" ?></h2>
    <form action="action_student.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1">Nama</label>
          <input name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-gray-700">
        </div>
        <div>
          <label class="block mb-1">Kelas (contoh: 11-A)</label>
          <input name="class" required value="<?= htmlspecialchars($edit_data['class'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-gray-700">
        </div>
        <div>
          <label class="block mb-1">Card ID</label>
          <input name="card_id" required value="<?= htmlspecialchars($edit_data['card_id'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-gray-700">
        </div>
      </div>

      <div class="flex items-center gap-4">
        <div>
          <label class="block mb-1">Foto Profil (jpg/png/webp)</label>
          <input type="file" name="profile_pic" accept="image/*" class="block">
        </div>
        <?php if($edit_data): ?>
          <?php
          $profile = $edit_data['profile_pic'];
          if(!$profile || $profile === 'default.png'){
              $profilePath = 'assets/img/default-avatar.png';
          } else {
              $profilePath = 'uploads/' . $profile;
          }
          ?>
          <div>
            <img src="<?= $profilePath ?>" alt="profile" class="w-16 h-16 rounded-full object-cover">
          </div>
        <?php endif; ?>

        <div>
          <label class="block mb-1">Status</label>
          <select name="status" class="p-2 border rounded dark:bg-gray-700">
            <option value="active" <?= (isset($edit_data['status']) && $edit_data['status']=='active')?'selected':'' ?>>Active</option>
            <option value="inactive" <?= (isset($edit_data['status']) && $edit_data['status']=='inactive')?'selected':'' ?>>Inactive</option>
          </select>
        </div>
      </div>

      <div class="pt-3">
        <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>" class="bg-blue-600 text-white px-4 py-2 rounded">
          <?= $edit_data ? 'Update' : 'Simpan' ?>
        </button>
        <?php if($edit_data): ?>
          <a href="index.php?page=students" class="ml-3 text-gray-500">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Filters -->
  <div class="flex gap-3 items-center">
    <form method="GET" action="index.php" class="flex gap-2 items-center">
      <input type="hidden" name="page" value="students">
      <select name="kelas" class="p-2 border rounded dark:bg-gray-700">
        <option value="">Semua Kelas</option>
        <option value="10" <?= $kelas=='10'?'selected':'' ?>>Kelas 10</option>
        <option value="11" <?= $kelas=='11'?'selected':'' ?>>Kelas 11</option>
        <option value="12" <?= $kelas=='12'?'selected':'' ?>>Kelas 12</option>
      </select>
      <input type="text" name="sub" placeholder="Subkelas (A/B/C)" value="<?= htmlspecialchars($sub) ?>" class="p-2 border rounded dark:bg-gray-700">
      <input type="text" name="search" placeholder="Cari nama / card id" value="<?= htmlspecialchars($search) ?>" class="p-2 border rounded dark:bg-gray-700">
      <button class="bg-green-600 text-white px-3 py-2 rounded">Filter</button>
      <a href="index.php?page=students" class="ml-2 text-sm text-blue-400">Reset</a>
    </form>
  </div>

  <!-- Table -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4">Daftar Siswa</h2>
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-100 dark:bg-gray-700 text-left">
          <th class="p-2">#</th>
          <th class="p-2">Foto</th>
          <th class="p-2">Nama</th>
          <th class="p-2">Kelas</th>
          <th class="p-2">Card ID</th>
          <th class="p-2">Status</th>
          <th class="p-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows): $no = $offset+1; ?>
          <?php while($row = $res->fetch_assoc()): ?>
            <?php
            $profile = $row['profile_pic'];
            if(!$profile || $profile === 'default.png'){
                $profilePath = 'assets/img/default-avatar.png';
            } else {
                $profilePath = 'uploads/' . $profile;
            }
            ?>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <td class="p-2"><?= $no++ ?></td>
              <td class="p-2">
                <img src="<?= $profilePath ?>" alt="" class="w-10 h-10 rounded-full object-cover">
              </td>
              <td class="p-2"><?= htmlspecialchars($row['name']) ?></td>
              <td class="p-2"><?= htmlspecialchars($row['class']) ?></td>
              <td class="p-2"><?= htmlspecialchars($row['card_id']) ?></td>
              <td class="p-2"><?= htmlspecialchars($row['status']) ?></td>
              <td class="p-2">
                <a href="index.php?page=students&edit=<?= $row['id'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
                <a href="action_student.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus?')" class="bg-red-600 text-white px-2 py-1 rounded">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="p-4 text-center">Tidak ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
