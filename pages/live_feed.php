<?php
include 'db.php';

// ambil data awal (biar tabel gak kosong pas load pertama)
$res = $conn->query("SELECT a.id, a.timestamp, a.card_id, a.device_id, a.schedule_status, s.name, s.class
                     FROM attendance_log a
                     LEFT JOIN students s ON a.student_id=s.id
                     ORDER BY a.timestamp DESC LIMIT 20");
?>
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
  <h2 class="text-xl font-semibold mb-4">Live Feed Absensi</h2>

  <div class="overflow-x-auto">
    <table class="min-w-full border">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Nama</th>
          <th class="p-2">Kelas</th>
          <th class="p-2">Card ID</th>
          <th class="p-2">Waktu</th>
          <th class="p-2">Status Jadwal</th>
        </tr>
      </thead>
      <tbody id="liveBody">
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
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

<script>
async function fetchFeed() {
  try {
    const res = await fetch('fetch_live_feed.php');
    const json = await res.json();
    const tbody = document.getElementById('liveBody');
    tbody.innerHTML = '';
    if(Array.isArray(json)){
      json.forEach(r=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="p-2">${r.id}</td>
                        <td class="p-2">${r.name || ''}</td>
                        <td class="p-2">${r.class || ''}</td>
                        <td class="p-2">${r.card_id || ''}</td>
                        <td class="p-2">${r.timestamp || ''}</td>
                        <td class="p-2">${r.schedule_status || ''}</td>`;
        tbody.appendChild(tr);
      });
    } else if(json && json.records){
      json.records.forEach(r=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="p-2">${r.id}</td>
                        <td class="p-2">${r.name || ''}</td>
                        <td class="p-2">${r.class || ''}</td>
                        <td class="p-2">${r.card_id || ''}</td>
                        <td class="p-2">${r.timestamp || ''}</td>
                        <td class="p-2">${r.schedule_status || ''}</td>`;
        tbody.appendChild(tr);
      });
    }
  } catch(e) {
    console.error(e);
  }
}

// Auto-refresh tiap 3 detik
setInterval(fetchFeed, 3000);
</script>
