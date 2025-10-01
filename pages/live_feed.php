<div class="space-y-6">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Live Feed Absensi</h2>

    <div id="live-table" class="overflow-x-auto">
      <p class="text-gray-600 dark:text-gray-300">Memuat data...</p>
    </div>
  </div>
</div>

<!-- Container Notifikasi -->
<div id="notif-container" class="fixed top-5 right-5 space-y-2 z-50"></div>

<!-- Suara Beep -->
<audio id="notif-sound" preload="auto">
  <source src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" type="audio/ogg">
</audio>

<script>
let lastTimestamp = null; // simpan data terakhir

async function loadLiveFeed(){
    try{
        const res = await fetch('fetch_live_feed.php');
        const logs = await res.json();

        if(logs.length === 0){
            document.getElementById('live-table').innerHTML =
              "<p class='text-gray-600 dark:text-gray-300'>Belum ada data</p>";
            return;
        }

        // Render tabel
        let html = `
        <table class="w-full border border-gray-300 dark:border-gray-700 border-collapse text-sm">
          <thead>
            <tr class="bg-blue-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
              <th class="border p-2">Tanggal/Waktu</th>
              <th class="border p-2">Nama</th>
              <th class="border p-2">Kelas</th>
              <th class="border p-2">Card ID</th>
              <th class="border p-2">Device</th>
              <th class="border p-2">Status</th>
            </tr>
          </thead>
          <tbody>`;
        logs.forEach(row=>{
            const color = row.schedule_status === 'Late'
                ? 'text-red-600'
                : row.schedule_status === 'On Time'
                    ? 'text-green-600'
                    : 'text-purple-600';
            html += `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <td class="border p-2 dark:border-gray-700">${row.timestamp}</td>
              <td class="border p-2 dark:border-gray-700">${row.name??''}</td>
              <td class="border p-2 dark:border-gray-700">${row.class??''}</td>
              <td class="border p-2 dark:border-gray-700">${row.card_id}</td>
              <td class="border p-2 dark:border-gray-700">${row.device_id}</td>
              <td class="border p-2 dark:border-gray-700 font-semibold ${color}">${row.schedule_status}</td>
            </tr>`;
        });
        html += "</tbody></table>";
        document.getElementById('live-table').innerHTML = html;

        // 🔔 Deteksi data baru
        if(lastTimestamp && logs[0].timestamp !== lastTimestamp){
            showNotification(logs[0]);
        }
        lastTimestamp = logs[0].timestamp;

    }catch(e){
        document.getElementById('live-table').innerHTML =
          "<p class='text-red-500'>Gagal memuat data</p>";
    }
}

// 🔔 Fungsi Tampilkan Notifikasi
function showNotification(row){
    const container = document.getElementById('notif-container');
    const notif = document.createElement('div');
    notif.className = "bg-blue-600 text-white px-4 py-2 rounded shadow-md animate-bounce";
    notif.innerHTML = `
      <strong>${row.name ?? 'Unknown'}</strong> (${row.class ?? '-'})<br>
      <small>${row.timestamp} — ${row.schedule_status}</small>
    `;
    container.appendChild(notif);

    // Suara beep
    document.getElementById('notif-sound').play();

    // Hilang setelah 5 detik
    setTimeout(()=> notif.remove(), 5000);
}

// Jalankan pertama kali
loadLiveFeed();
// Auto refresh tiap 5 detik
setInterval(loadLiveFeed, 5000);
</script>
