<?php
include 'db.php';

// ----- CONFIGURASI PAGINATION -----
$limit = 5; // tampil 5 siswa per halaman
$page  = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// ----- PENCARIAN -----
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where  = $search ? "WHERE name LIKE '%$search%' OR class LIKE '%$search%' OR card_id LIKE '%$search%'" : '';

// Hitung total rows untuk pagination
$totalRows = $conn->query("SELECT COUNT(*) as jml FROM students $where")->fetch_assoc()['jml'];
$totalPages = ceil($totalRows / $limit);

// Ambil data siswa sesuai search & pagination
$students = $conn->query("SELECT * FROM students $where ORDER BY id DESC LIMIT $limit OFFSET $offset");

// Edit handler
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;
if($edit_id){
    $res = $conn->query("SELECT * FROM students WHERE id=$edit_id");
    $edit_data = $res->fetch_assoc();
}
?>
<div class="space-y-6">
    <!-- Form Tambah / Edit -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">
            <?= $edit_data ? "Edit Siswa" : "Tambah Siswa Baru" ?>
        </h2>
        <form action="action_student.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            <div>
                <label class="block mb-1 text-gray-700 dark:text-gray-300">Nama</label>
                <input name="name" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                       value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" required>
            </div>
            <div>
                <label class="block mb-1 text-gray-700 dark:text-gray-300">Kelas</label>
                <input name="class" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                       value="<?= htmlspecialchars($edit_data['class'] ?? '') ?>" required>
            </div>
            <div>
                <label class="block mb-1 text-gray-700 dark:text-gray-300">Card ID</label>
                <input name="card_id" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                       value="<?= htmlspecialchars($edit_data['card_id'] ?? '') ?>" required>
            </div>
            <button type="submit" name="<?= $edit_data ? 'update' : 'create' ?>"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                <?= $edit_data ? "Update" : "Simpan" ?>
            </button>
            <?php if($edit_data): ?>
                <a href="index.php?page=students" class="ml-3 text-gray-600 dark:text-gray-300">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
        <form method="GET" action="index.php" class="flex gap-2">
            <input type="hidden" name="page" value="students">
            <input type="text" name="search" placeholder="Cari nama / kelas / card ID"
                   value="<?= htmlspecialchars($search) ?>"
                   class="border p-2 rounded w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                Cari
            </button>
        </form>
        <?php if($search): ?>
            <a href="index.php?page=students" class="text-blue-600 dark:text-blue-400">Reset Pencarian</a>
        <?php endif; ?>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md overflow-x-auto">
        <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Daftar Siswa</h2>
        <table class="w-full border border-gray-300 dark:border-gray-700 border-collapse">
            <thead>
                <tr class="bg-blue-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Nama</th>
                    <th class="border p-2">Kelas</th>
                    <th class="border p-2">Card ID</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if($students->num_rows > 0): ?>
            <?php while($row = $students->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <td class="border p-2 dark:border-gray-700"><?= $row['id'] ?></td>
                    <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['class']) ?></td>
                    <td class="border p-2 dark:border-gray-700"><?= htmlspecialchars($row['card_id']) ?></td>
                    <td class="border p-2 dark:border-gray-700 text-center">
                        <a href="index.php?page=students&edit=<?= $row['id'] ?>"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded transition">Edit</a>
                        <a href="action_student.php?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Hapus siswa ini?')"
                           class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded transition">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center p-4 text-gray-600 dark:text-gray-300">Tidak ada data</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="flex justify-center mt-4 gap-2">
            <?php for($i=1; $i <= $totalPages; $i++): ?>
                <?php
                $active = ($i == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200';
                $query = http_build_query([
                    'page' => 'students',
                    'p'    => $i,
                    'search' => $search
                ]);
                ?>
                <a href="index.php?<?= $query ?>"
                   class="px-3 py-1 rounded <?= $active ?> hover:bg-blue-500 hover:text-white transition">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
