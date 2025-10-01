<div class="space-y-6">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4">Live Feed Absensi</h2>
    <div id="live-table" class="overflow-x-auto">
      <p class="text-gray-600">Memuat data...</p>
    </div>
  </div>
</div>

<!-- Notif area -->
<div id="notif-area" class="fixed inset-0 flex items-center justify-center pointer-events-none z-50 p-6">
  <div id="notif-grid" class="grid gap-4 max-w-3xl"></div>
</div>

<script>
let lastTimestamp = null;
const notifGrid = document.getElementById('notif-grid');

async function loadLiveFeed(){
  try{
    const res = await fetch('fetch_live_feed.php');
    const logs = await res.json();

    if(!logs.length){
      document.getElementById('live-table').innerHTML = "<p class='text-gray-600'>Belum ada data</p>";
      return;
    }

    // render table
    let html = `<table class="w-full text-sm"><thead><tr class="bg-gray-100 dark:bg-gray-700">
      <th class="p-2">Waktu</th><th class="p-2">Nama</th><th class="p-2">Kelas</th><th class="p-2">Card</th><th class="p-2">Device</th><th class="p-2">Status</th>
    </tr></thead><tbody>`;
    logs.forEach(row=>{
      html += `<tr class="border-b dark:border-gray-700">
        <td class="p-2">${row.timestamp}</td>
        <td class="p-2">${row.name ?? ''}</td>
        <td class="p-2">${row.class ?? ''}</td>
        <td class="p-2">${row.card_id}</td>
        <td class="p-2">${row.device_id}</td>
        <td class="p-2 font-semibold ${row.schedule_status==='Late'?'text-red-500':'text-green-500'}">${row.schedule_status}</td>
      </tr>`;
    });
    html += "</tbody></table>";
    document.getElementById('live-table').innerHTML = html;

    // detect new entry
    if(lastTimestamp && logs[0].timestamp !== lastTimestamp){
      showNotif(logs[0]);
    }
    lastTimestamp = logs[0].timestamp;

  } catch(e){
    document.getElementById('live-table').innerHTML = "<p class='text-red-500'>Gagal memuat</p>";
  }
}

function showNotif(row){
  const card = document.createElement('div');
  card.className = "notif-card bg-gray-800 text-white p-4 rounded-xl shadow-lg w-64 text-center pointer-events-auto";

  // handle default.png → use assets/img/default-avatar.png
  let imgSrc;
  if(!row.profile_pic || row.profile_pic === 'default.png'){
    imgSrc = 'assets/img/default-avatar.png';
  } else {
    imgSrc = `uploads/${row.profile_pic}`;
  }

  card.innerHTML = `
    <img src="${imgSrc}" class="w-16 h-16 rounded-full mx-auto mb-2 object-cover" alt="profile">
    <div class="font-bold">${row.name ?? 'Unknown'}</div>
    <div class="text-sm">${row.class ?? '-'}</div>
    <div class="mt-2 text-xs">${row.timestamp} — <span class="${row.schedule_status==='Late'?'text-red-400':'text-green-400'} font-semibold">${row.schedule_status}</span></div>
  `;
  notifGrid.appendChild(card);

  // limit max cards
  const max = 9;
  while(notifGrid.children.length > max){
    notifGrid.removeChild(notifGrid.firstChild);
  }

  // auto remove after 3s
  setTimeout(()=> {
    card.style.transition = "opacity 0.3s, transform 0.3s";
    card.style.opacity = 0;
    card.style.transform = "scale(0.95)";
    setTimeout(()=> card.remove(), 300);
  }, 3000);
}

// initial
loadLiveFeed();
setInterval(loadLiveFeed, 4000);
</script>

<style>
#notif-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
  justify-items: center;
  align-items: center;
}
.notif-card img { object-fit: cover; }
</style>
