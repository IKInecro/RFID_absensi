</main>
  </div> <!-- end content wrapper -->
</div> <!-- end flex -->

<script>
document.addEventListener("DOMContentLoaded", () => {
  const html = document.documentElement;
  const btn = document.getElementById("toggle-theme");

  // === Clock WIB ===
  function updateClock(){
    const el = document.getElementById("clock");
    if(!el) return;
    const now = new Date();
    const time = now.toLocaleTimeString("id-ID", { timeZone: "Asia/Jakarta", hour12: false });
    const date = now.toLocaleDateString("id-ID", { timeZone: "Asia/Jakarta" });
    el.textContent = `${time} | ${date} WIB`;
  }
  setInterval(updateClock, 1000);
  updateClock();

  // === Theme Toggle ===
  const saved = localStorage.getItem("theme");
  if(saved === "dark"){
    html.classList.add("dark");
  } else {
    html.classList.remove("dark");
  }

  btn.addEventListener("click", () => {
    html.classList.toggle("dark");
    if(html.classList.contains("dark")){
      localStorage.setItem("theme", "dark");
    } else {
      localStorage.setItem("theme", "light");
    }
  });
});
</script>

</body>
</html>
