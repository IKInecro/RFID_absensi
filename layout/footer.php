<?php
// layout/footer.php
?>
</main>
</div> <!-- End Main Content Wrapper -->

<!-- Theme Toggle Script -->
<script>
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const themeSun = document.getElementById('themeSun');
    const themeMoon = document.getElementById('themeMoon');

    // Check local storage or system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }

    // Toggle logic
    themeToggle.addEventListener('click', () => {
        html.classList.toggle('dark');
        if (html.classList.contains('dark')) {
            localStorage.theme = 'dark';
        } else {
            localStorage.theme = 'light';
        }
    });

    // Mobile Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }

    // Clock Logic
    function updateClock() {
        const clockEl = document.getElementById('clock');
        if (clockEl) {
            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', { hour12: false });
            clockEl.textContent = time + ' WIB';
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
</body>

</html>