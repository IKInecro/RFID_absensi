// NEW: assets/js/mode_control.js
// Helper to toggle register/tester mode without leaving current page.
// Gunakan: ModeControl.toggleRegister(), ModeControl.toggleTester()
// Mengasumsikan endpoint: action_register.php (POST toggle_reg_mode) and toggle_testmode.php (POST new_mode)

var ModeControl = (function(){
    async function postForm(url, data) {
      try {
        const res = await fetch(url, { method: 'POST', body: data, credentials: 'same-origin' });
        // try to parse json if returned, else ignore
        try { return await res.json(); } catch(e) { return null; }
      } catch (err) {
        console.error('ModeControl error', err);
        return null;
      }
    }
  
    return {
      toggleRegister: async function(enable) {
        const form = new FormData();
        form.append('toggle_reg_mode', enable ? '1' : '0');
        return await postForm('action_register.php', form);
      },
      toggleTester: async function(enable) {
        const form = new FormData();
        form.append('new_mode', enable ? '1' : '0');
        return await postForm('toggle_testmode.php', form);
      },
      fetchSettings: async function() {
        try {
          const res = await fetch('pages/students.php?ajax=1', { cache:'no-store' });
          if (!res.ok) return null;
          return await res.json();
        } catch (e) { return null; }
      }
    };
  })();
  