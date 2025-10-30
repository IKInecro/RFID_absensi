// NEW FILE: assets/js/live_update.js
// Simple long-poll helper. API: LiveUpdates.startLongPoll(options)
// options:
//  - url: endpoint (string) e.g. 'api/updates.php?mode=test'
//  - paramNameForLast: name of query param to send last value, e.g. 'last_id' or 'last_ts'
//  - getLastValue: function returning last value to send
//  - onNew: callback(payload) when new item arrives
//  - onError: optional callback(err)
// It will re-issue the request after each response (long-poll loop). Keeps a backoff on errors.

var LiveUpdates = (function(){
    var instances = [];
  
    function startLongPoll(opts) {
      if (!opts || !opts.url || !opts.paramNameForLast || !opts.getLastValue || !opts.onNew) {
        console.error('LiveUpdates: missing options');
        return;
      }
      var running = true;
      var backoff = 1000;
      async function loop(){
        while (running) {
          try {
            var last = opts.getLastValue();
            var url = opts.url + (opts.url.indexOf('?') === -1 ? '?' : '&') + encodeURIComponent(opts.paramNameForLast) + '=' + encodeURIComponent(last || '');
            var controller = new AbortController();
            var signal = controller.signal;
            // fetch with no-cache so server long-poll works
            var res = await fetch(url, {cache:'no-store', signal: signal, credentials:'same-origin'});
            if (!res.ok) throw new Error('Network response not OK: ' + res.status);
            var json = await res.json();
            if (json && json.new) {
              try { opts.onNew(json); } catch(e){ console.error('LiveUpdates.onNew error', e); }
            }
            // reset backoff on success
            backoff = 1000;
          } catch (err) {
            if (opts.onError) try { opts.onError(err); } catch(e){}
            console.error('LiveUpdates error:', err);
            await new Promise(r => setTimeout(r, backoff));
            backoff = Math.min(30000, backoff * 1.6);
          }
        }
      }
      loop();
      var api = { stop: function(){ running = false; } };
      instances.push(api);
      return api;
    }
  
    return { startLongPoll: startLongPoll };
  })();
  