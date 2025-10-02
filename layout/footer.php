<?php
// layout/footer.php
?>
  </main>

  <script>
    // WIB Clock
    function updateClock() {
      let now = new Date();
      let utc = now.getTime() + (now.getTimezoneOffset() * 60000);
      let wib = new Date(utc + (3600000 * 7));
      let jam = String(wib.getHours()).padStart(2, '0');
      let menit = String(wib.getMinutes()).padStart(2, '0');
      let detik = String(wib.getSeconds()).padStart(2, '0');
      document.getElementById('wibClock').innerText = jam+":"+menit+":"+detik+" WIB";
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    document.getElementById('btnToggle').addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });

    // Active nav
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'dashboard';
    document.querySelectorAll('.nav-link').forEach(link => {
      if (link.href.includes(`page=${page}`)) {
        link.classList.add('active');
      }
    });
  </script>
</body>
</html>
