// assets/js/live_update.js - Enhanced version with better error handling and reconnection
// Auto-refresh system for RFID data (Tester Mode, Live Feed, Students)

var LiveUpdates = (function() {
  var instances = [];
  
  function startLongPoll(opts) {
    if (!opts || !opts.url || !opts.paramNameForLast || !opts.getLastValue || !opts.onNew) {
      console.error('LiveUpdates: missing required options');
      return null;
    }
    
    var running = true;
    var backoff = 1000;
    var maxBackoff = 30000;
    var consecutiveErrors = 0;
    
    async function loop() {
      while (running) {
        try {
          var last = opts.getLastValue();
          var separator = opts.url.indexOf('?') === -1 ? '?' : '&';
          var url = opts.url + separator + encodeURIComponent(opts.paramNameForLast) + '=' + encodeURIComponent(last || '0');
          
          var controller = new AbortController();
          var timeoutId = setTimeout(() => controller.abort(), 60000); // 60s timeout
          
          var res = await fetch(url, {
            cache: 'no-store',
            signal: controller.signal,
            credentials: 'same-origin',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          clearTimeout(timeoutId);
          
          if (!res.ok) {
            throw new Error('HTTP ' + res.status + ': ' + res.statusText);
          }
          
          var json = await res.json();
          
          // Handle new data
          if (json && json.new) {
            try {
              opts.onNew(json);
              consecutiveErrors = 0; // Reset error counter on success
            } catch(e) {
              console.error('LiveUpdates.onNew callback error:', e);
            }
          }
          
          // Reset backoff on successful response
          backoff = 1000;
          
        } catch (err) {
          consecutiveErrors++;
          
          if (err.name === 'AbortError') {
            console.warn('LiveUpdates: Request timeout, retrying...');
          } else {
            console.error('LiveUpdates error:', err.message || err);
          }
          
          if (opts.onError) {
            try {
              opts.onError(err, consecutiveErrors);
            } catch(e) {
              console.error('LiveUpdates.onError callback error:', e);
            }
          }
          
          // Exponential backoff with max limit
          await new Promise(resolve => setTimeout(resolve, backoff));
          backoff = Math.min(maxBackoff, backoff * 1.5);
          
          // If too many consecutive errors, increase backoff more aggressively
          if (consecutiveErrors > 5) {
            backoff = Math.min(maxBackoff, backoff * 2);
          }
        }
      }
    }
    
    loop();
    
    var api = {
      stop: function() {
        running = false;
        console.log('LiveUpdates: Polling stopped for', opts.url);
      },
      isRunning: function() {
        return running;
      }
    };
    
    instances.push(api);
    return api;
  }
  
  function stopAll() {
    instances.forEach(function(inst) {
      if (inst && inst.stop) inst.stop();
    });
    instances = [];
    console.log('LiveUpdates: All polling stopped');
  }
  
  return {
    startLongPoll: startLongPoll,
    stopAll: stopAll
  };
})();

// Auto-start cleanup on page unload
if (typeof window !== 'undefined') {
  window.addEventListener('beforeunload', function() {
    LiveUpdates.stopAll();
  });
}