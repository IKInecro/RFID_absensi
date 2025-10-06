```php
<?php
include 'db.php';

// cek mode registrasi
$modeRow = $conn->query("SELECT reg_mode FROM settings WHERE id = 1")->fetch_assoc();
$reg_mode = intval($modeRow['reg_mode'] ?? 0);

if ($reg_mode === 1) {
  echo '<div style="text-align:center;margin-top:80px;color:red;font-weight:bold;">Mode registrasi aktif — halaman presenter tidak dapat diakses.</div>';
  return;
}
?>
<div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg" style="max-width:1000px;margin:20px auto;">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Presenter: Live Absensi (Tester)</h1>
    <button id="clearBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
      🧹 Clear Data
    </button>
  </div>
  <p class="mb-4 text-sm text-gray-400">Data diperbarui otomatis setiap 3 detik.</p>

  <div class="overflow-x-auto">
    <table class="min-w-full border" id="testerTable">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-3">#</th>
          <th class="p-3">Nama</th>
          <th class="p-3">Kelas</th>
          <th class="p-3">Card ID</th>
          <th class="p-3">Waktu</th>
          <th class="p-3">Status</th>
          <th class="p-3">Device</th>
        </tr>
      </thead>
      <tbody id="testerBody">
        <tr><td colspan="7" class="p-4">Memuat data...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
async function loadTester(){
  try{
    const res = await fetch('fetch_live_feed.php');
    const data = await res.json();
    const body = document.getElementById('testerBody');
    body.innerHTML = '';
    let rows = Array.isArray(data) ? data : (data.records || []);
    if(rows.length === 0){
      body.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-gray-400">Belum ada data absensi.</td></tr>';
    }
    rows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="p-2">${r.id || ''}</td>
                      <td class="p-2">${r.name || ''}</td>
                      <td class="p-2">${r.class || ''}</td>
                      <td class="p-2">${r.card_id || ''}</td>
                      <td class="p-2">${r.timestamp || ''}</td>
                      <td class="p-2">${r.schedule_status || ''}</td>
                      <td class="p-2">${r.device_id || ''}</td>`;
      body.appendChild(tr);
    });
  }catch(e){
    console.error(e);
  }
}

// fungsi clear data
document.getElementById('clearBtn').addEventListener('click', async ()=>{
  if(!confirm('Yakin ingin menghapus semua data absensi?')) return;
  try{
    const res = await fetch('pages/tester_clear.php', { method: 'POST' });
    const json = await res.json();
    if(json.success){
      alert('Data absensi berhasil dihapus.');
      loadTester();
    }else{
      alert('Gagal menghapus data: ' + (json.error || 'unknown error'));
    }
  }catch(err){
    console.error(err);
    alert('Terjadi kesalahan koneksi.');
  }
});

loadTester();
setInterval(loadTester, 3000);
</script>
```
