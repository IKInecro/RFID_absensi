<?php
include 'db.php';

// Ambil data awal
$res = $conn->query("SELECT a.id, a.timestamp, a.card_id, a.device_id, a.schedule_status, s.name, s.class
                     FROM attendance_log a
                     LEFT JOIN students s ON a.student_id=s.id
                     ORDER BY a.timestamp DESC LIMIT 20");
?>
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
  <h2 class="text-xl font-semibold mb-4">Live Feed Absensi</h2>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-700 text-left">
      <thead class="bg-gray-700 text-gray-100">
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Nama</th>
          <th class="p-2">Kelas</th>
          <th class="p-2">Card ID</th>
          <th class="p-2">Waktu</th>
          <th class="p-2">Status Jadwal</th>
        </tr>
      </thead>
      <tbody id="liveBody" class="text-gray-200">
        <?php while($row = $res->fetch_assoc()): ?>
        <tr class="border-b border-gray-700 hover:bg-gray-800">
          <td class="p-2"><?= htmlspecialchars($row['id']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['class']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['card_id']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['timestamp']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['schedule_status']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Popup kartu tidak dikenal -->
<div id="unknownPopup" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/40 backdrop-blur-sm transition-opacity duration-500">
  <div class="bg-red-600 text-white px-10 py-6 rounded-2xl shadow-2xl text-center transform scale-90 transition-all duration-500">
    <h3 class="text-2xl font-bold mb-2">🚫 Kartu Tidak Dikenal</h3>
    <p class="text-sm opacity-90">Silakan registrasikan kartu ini terlebih dahulu.</p>
  </div>
</div>

<script>
async function fetchFeed() {
  try {
    const res = await fetch('fetch_live_feed.php');
    const json = await res.json();
    const tbody = document.getElementById('liveBody');
    tbody.innerHTML = '';

    const rows = Array.isArray(json) ? json : (json.records || []);
    rows.forEach(r => {
      const tr = document.createElement('tr');
      tr.className = "border-b border-gray-700 hover:bg-gray-800";
      tr.innerHTML = `
        <td class="p-2">${r.id}</td>
        <td class="p-2">${r.name || ''}</td>
        <td class="p-2">${r.class || ''}</td>
        <td class="p-2">${r.card_id || ''}</td>
        <td class="p-2">${r.timestamp || ''}</td>
        <td class="p-2">${r.schedule_status || ''}</td>
      `;
      tbody.appendChild(tr);
    });
  } catch (e) {
    console.error('Error fetch:', e);
  }
}

// Popup kartu tidak dikenal
function showUnknownPopup() {
  const popup = document.getElementById('unknownPopup');
  popup.classList.remove('hidden');
  popup.classList.add('flex', 'opacity-100');
  setTimeout(() => {
    popup.classList.remove('opacity-100');
    popup.classList.add('opacity-0');
    setTimeout(() => popup.classList.add('hidden'), 500);
  }, 2000);
}

// Auto-refresh tiap 3 detik
fetchFeed();
setInterval(fetchFeed, 3000);
</script>
