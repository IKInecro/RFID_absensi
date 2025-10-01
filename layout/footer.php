</main>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Clock WIB
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
});
</script>

</body>
</html>
