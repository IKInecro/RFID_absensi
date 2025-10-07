<?php
include 'db.php';
$setting = $conn->query("SELECT test_mode FROM settings WHERE id=1")->fetch_assoc();
$testMode = intval($setting['test_mode']);

if ($testMode==0){
  echo '<div class="flex items-center justify-center h-96">
          <div class="text-center">
            <h2 class="text-2xl font-bold text-red-600 mb-2">⚠️ Test Mode Nonaktif</h2>
            <p class="text-gray-600 dark:text-gray-300">Aktifkan Test Mode di Dashboard untuk menggunakan halaman ini.</p>
          </div>
        </div>';
  exit;
}
?>
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
  <h2 class="text-xl font-semibold mb-4 text-center">🧪 Mode Tester (Realtime)</h2>
  <p class="text-gray-600 dark:text-gray-300 text-center mb-4">
    Data di bawah berasal langsung dari ESP8266 saat Test Mode aktif. Tidak tersimpan di database.
  </p>
  <div class="text-center mb-6">
    <button onclick="clearData()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
      🧹 Bersihkan Data
    </button>
  </div>

  <div id="testerFeed" class="overflow-x-auto">
    <table class="min-w-full border border-gray-700 text-left">
      <thead class="bg-gray-700 text-gray-100">
        <tr>
          <th class="p-2">Waktu</th>
          <th class="p-2">UID</th>
          <th class="p-2">Nama</th>
          <th class="p-2">Kelas</th>
          <th class="p-2">Device</th>
          <th class="p-2">Status</th>
        </tr>
      </thead>
      <tbody id="testerBody" class="text-gray-200"></tbody>
    </table>
  </div>
</div>

<script>
async function fetchTesterData() {
  const res = await fetch('api/test_feed.php');
  const json = await res.json();
  const tbody = document.getElementById('testerBody');
  tbody.innerHTML = '';

  (json.records || []).forEach(r=>{
    const tr=document.createElement('tr');
    tr.className="border-b border-gray-700 hover:bg-gray-800";
    tr.innerHTML=`
      <td class="p-2">${r.timestamp||''}</td>
      <td class="p-2">${r.uid||''}</td>
      <td class="p-2">${r.name||'-'}</td>
      <td class="p-2">${r.class||'-'}</td>
      <td class="p-2">${r.device_id||'-'}</td>
      <td class="p-2">${r.status||''}</td>`;
    tbody.appendChild(tr);
  });
}

// Bersihkan file test_data.json di server
async function clearData(){
  await fetch('api/test_feed.php?clear=1');
  document.getElementById('testerBody').innerHTML='';
}

// Cek data baru tiap 2 detik
setInterval(fetchTesterData,2000);
fetchTesterData();
</script>
