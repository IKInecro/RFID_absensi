// assets/js/theme.js
document.addEventListener("DOMContentLoaded", () => {
    const html = document.documentElement;
    const toggleBtn = document.getElementById("toggle-theme");
  
    // Load dari localStorage
    if(localStorage.getItem("theme") === "dark"){
      html.classList.add("dark");
    } else {
      html.classList.remove("dark");
    }
  
    // Toggle
    if(toggleBtn){
      toggleBtn.addEventListener("click", () => {
        html.classList.toggle("dark");
        if(html.classList.contains("dark")){
          localStorage.setItem("theme","dark");
        } else {
          localStorage.setItem("theme","light");
        }
      });
    }
  });
  