<?php
include 'db.php';

// ambil data awal (biar tabel gak kosong pas load pertama)
$res = $conn->query("SELECT a.*, s.name, s.class, s.profile_pic 
                     FROM attendance_log a 
                     LEFT JOIN students s ON a.student_id=s.id 
                     ORDER BY a.timestamp DESC LIMIT 20");
?>
<div class="relative">
  <!-- Container untuk bubble notifikasi -->
  <div id="bubble-container" class="fixed inset-0 flex items-start justify-center pointer-events-none z-50 p-4 flex-wrap gap-4"></div>

  <!-- Live Feed Table -->
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-semibold mb-4">Live Feed Absensi</h2>
    <table id="feed-table" class="w-full text-sm">
      <thead>
        <tr class="bg-gray-100 dark:bg-gray-700">
          <th class="p-2">Foto</th>
          <th class="p-2">Nama</th>
          <th class="p-2">Kelas</th>
          <th class="p-2">UID</th>
          <th class="p-2">Status</th>
          <th class="p-2">Waktu</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr class="border-b dark:border-gray-700 <?= $row['status']==='Unknown' ? 'bg-red-100 dark:bg-red-900' : '' ?>">
          <td class="p-2">
            <img src="uploads/<?= $row['profile_pic'] ?: 'default.png' ?>" 
                 class="w-10 h-10 rounded-full border"
                 onerror="this.src='uploads/default.png'">
          </td>
          <td class="p-2"><?= $row['student_id'] ? htmlspecialchars($row['name']) : 'Kartu Tidak Dikenal' ?></td>
          <td class="p-2"><?= $row['student_id'] ? htmlspecialchars($row['class']) : '-' ?></td>
          <td class="p-2"><?= htmlspecialchars($row['card_id']) ?></td>
          <td class="p-2"><?= htmlspecialchars($row['status']) ?></td>
          <td class="p-2"><?= $row['timestamp'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Import Bubble JS -->
<script>
function showBubble(student) {
  const container = document.getElementById("bubble-container");

  const bubble = document.createElement("div");
  bubble.className = "bg-white dark:bg-gray-700 shadow-xl rounded-2xl p-4 w-64 flex items-center gap-3 animate-fade-in";

  bubble.innerHTML = `
    <img src="uploads/${student.profile_pic || 'default.png'}"
         class="w-12 h-12 rounded-full border"
         onerror="this.src='uploads/default.png'">
    <div>
      <p class="font-semibold">${student.name || 'Kartu Tidak Dikenal'}</p>
      <p class="text-sm text-gray-500">${student.class || '-'}</p>
      <span class="text-xs px-2 py-1 rounded-full ${
        student.status === 'On Time' ? 'bg-green-500 text-white' : 
        student.status === 'Late' ? 'bg-yellow-500 text-white' : 
        student.status === 'Holiday' ? 'bg-gray-500 text-white' :
        'bg-red-600 text-white'
      }">${student.status}</span>
    </div>
  `;

  container.appendChild(bubble);

  setTimeout(() => {
    bubble.classList.add("animate-fade-out");
    setTimeout(() => bubble.remove(), 500);
  }, 3000);
}

function fetchFeed(){
  fetch('api/get_feed.php')
    .then(res=>res.json())
    .then(data=>{
      const tbody = document.querySelector("#feed-table tbody");
      tbody.innerHTML = "";
      data.forEach(item=>{
        tbody.innerHTML += `
          <tr class="border-b dark:border-gray-700 ${item.status==='Unknown' ? 'bg-red-100 dark:bg-red-900' : ''}">
            <td class="p-2">
              <img src="uploads/${item.profile_pic || 'default.png'}"
                   class="w-10 h-10 rounded-full border"
                   onerror="this.src='uploads/default.png'">
            </td>
            <td class="p-2">${item.name}</td>
            <td class="p-2">${item.class}</td>
            <td class="p-2">${item.card_id}</td>
            <td class="p-2">${item.status}</td>
            <td class="p-2">${item.timestamp}</td>
          </tr>
        `;
        showBubble(item);
      });
    });
}

setInterval(fetchFeed, 5000);

// CSS animasi bubble
const style = document.createElement("style");
style.innerHTML = `
  .animate-fade-in {
    animation: fadeIn 0.5s ease forwards;
  }
  .animate-fade-out {
    animation: fadeOut 0.5s ease forwards;
  }
  @keyframes fadeIn {
    from { opacity:0; transform: translateY(20px); }
    to { opacity:1; transform: translateY(0); }
  }
  @keyframes fadeOut {
    from { opacity:1; transform: translateY(0); }
    to { opacity:0; transform: translateY(-20px); }
  }
`;
document.head.appendChild(style);
</script>