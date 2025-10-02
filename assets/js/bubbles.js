function showBubble(student) {
    const container = document.getElementById("bubble-container");
  
    // buat elemen bubble
    const bubble = document.createElement("div");
    bubble.className = "bg-white dark:bg-gray-700 shadow-xl rounded-2xl p-4 w-64 flex items-center gap-3 animate-fade-in";
    
    bubble.innerHTML = `
      <img src="uploads/${student.profile_pic || 'default.png'}" 
           class="w-12 h-12 rounded-full border" 
           onerror="this.src='uploads/default.png'">
      <div>
        <p class="font-semibold">${student.name || 'Unknown'}</p>
        <p class="text-sm text-gray-500">${student.class || '-'}</p>
        <span class="text-xs px-2 py-1 rounded-full ${student.absen_status === 'On Time' ? 'bg-green-500 text-white' : student.absen_status === 'Late' ? 'bg-yellow-500 text-white' : 'bg-gray-500 text-white'}">
          ${student.absen_status}
        </span>
      </div>
    `;
  
    container.appendChild(bubble);
  
    // auto hilang setelah 3 detik
    setTimeout(() => {
      bubble.classList.add("animate-fade-out");
      setTimeout(() => bubble.remove(), 500);
    }, 3000);
  }
  
  // CSS animasi fade-in/fade-out
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
  