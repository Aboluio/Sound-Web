/* Sound Group — player, playlist buttons and small UI helpers (no dependencies) */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var csrfMeta = $('meta[name="csrf"]');
  var csrf = csrfMeta ? csrfMeta.content : '';

  function fmt(s) {
    s = Math.max(0, Math.floor(s || 0));
    return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
  }
  function post(action, id) {
    var body = new URLSearchParams({ action: action, id: id });
    return fetch('api.php', { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .catch(function () { return { ok: false }; });
  }

  /* flash messages fade away */
  $$('.js-flash').forEach(function (el) { setTimeout(function () { el.remove(); }, 4500); });

  /* ================= PLAYER ================= */
  var audio = $('#sgAudio');
  var KEY = 'sg_player_v1';
  var queue = [], idx = -1, shuffle = false, repeat = 0, counted = {}, seeking = false, lastSave = 0;

  var ui = audio ? {
    thumb: $('#npThumb'), title: $('#npTitle2'), artist: $('#npArtist'), range: $('#npRange'),
    cur: $('#npCur'), dur: $('#npDur'), play: $('#btnPlay'), prev: $('#btnPrev'), next: $('#btnNext'),
    shuf: $('#btnShuffle'), rep: $('#btnRepeat'), card: $('#nowPlaying')
  } : null;

  function trackFrom(el) {
    var d = el.dataset;
    return { id: +d.id, type: d.type, title: d.title, artist: d.artist, src: d.src, cover: d.cover, dur: +d.dur || 0 };
  }

  function queueFor(el) {
    var scope = el.closest('[data-queue]') || el.closest('#plList');
    var els = scope ? $$('.js-play', scope) : [el];
    var seen = {}, list = [];
    els.forEach(function (e) {
      var t = trackFrom(e);
      if (t.type === 'music' && !seen[t.id]) { seen[t.id] = 1; list.push(t); }
    });
    return list;
  }

  function save(force) {
    var now = Date.now();
    if (!force && now - lastSave < 1500) return;
    lastSave = now;
    try {
      localStorage.setItem(KEY, JSON.stringify({ q: queue, i: idx, t: audio.currentTime || 0, s: shuffle, r: repeat }));
    } catch (e) { /* storage unavailable */ }
  }

  function setThumb(t) {
    ui.thumb.textContent = '';
    if (t && t.cover) {
      var img = document.createElement('img'); img.src = t.cover; img.alt = ''; ui.thumb.appendChild(img);
    } else {
      var i = document.createElement('i'); i.className = 'bi bi-music-note-beamed'; ui.thumb.appendChild(i);
    }
  }

  function markPlaying() {
    var cur = idx >= 0 ? queue[idx] : null;
    var on = cur && !audio.paused;
    $$('.js-play').forEach(function (e) {
      var host = e.classList.contains('pl-row') ? e : (e.closest('.m-card') || e.closest('.tt-row') || e);
      var active = cur && +e.dataset.id === cur.id;
      host.classList.toggle('is-current', !!active);
      host.classList.toggle('is-playing', !!(active && on));
      if (e.classList.contains('play-fab') || e.classList.contains('btn-icon') || e.classList.contains('btn-sg')) {
        var ic = e.querySelector('i.bi');
        if (ic && (ic.classList.contains('bi-play-fill') || ic.classList.contains('bi-pause-fill'))) {
          ic.className = 'bi ' + (active && on ? 'bi-pause-fill' : 'bi-play-fill');
        }
      }
    });
  }

  function refresh() {
    var t = idx >= 0 ? queue[idx] : null;
    if (!t) return;
    ui.title.textContent = t.title;
    ui.artist.textContent = t.artist;
    setThumb(t);
    ui.range.disabled = false;
    document.body.classList.add('has-track');
    var ic = ui.play.querySelector('i');
    ic.className = 'bi ' + (audio.paused ? 'bi-play-fill' : 'bi-pause-fill');
    markPlaying();
  }

  function load(i, autoplay, startAt) {
    if (i < 0 || i >= queue.length) return;
    idx = i;
    var t = queue[i];
    audio.src = t.src;
    ui.dur.textContent = fmt(t.dur);
    ui.cur.textContent = '0:00';
    ui.range.value = 0;
    if (startAt) {
      audio.addEventListener('loadedmetadata', function once() {
        audio.removeEventListener('loadedmetadata', once);
        try { audio.currentTime = startAt; } catch (e) { }
      });
    }
    refresh();
    if (autoplay) {
      var p = audio.play();
      if (p && p.catch) p.catch(function (err) {
        if (err && err.name === 'NotAllowedError') ui.artist.textContent = 'Press play to start';
        refresh();
      });
    }
    save(true);
  }

  function nextIndex(auto) {
    if (!queue.length) return -1;
    if (shuffle && queue.length > 1) {
      var n; do { n = Math.floor(Math.random() * queue.length); } while (n === idx);
      return n;
    }
    if (idx + 1 < queue.length) return idx + 1;
    return repeat === 1 || !auto ? 0 : -1;
  }

  function next(auto) {
    var n = nextIndex(auto);
    if (n >= 0) load(n, true);
    else { audio.pause(); audio.currentTime = 0; refresh(); }
  }
  function prev() {
    if (audio.currentTime > 3 || queue.length < 2) { audio.currentTime = 0; return; }
    load(idx > 0 ? idx - 1 : queue.length - 1, true);
  }
  function toggle() {
    if (idx < 0) {
      var first = $('.js-play[data-type="music"]');
      if (first) first.click();
      return;
    }
    if (audio.paused) { var p = audio.play(); if (p && p.catch) p.catch(function () { }); } else audio.pause();
  }

  if (audio) {
    document.addEventListener('click', function (e) {
      var el = e.target.closest('.js-play');
      if (!el) return;
      e.preventDefault();
      if (el.dataset.type === 'video') { location.href = 'detail.php?id=' + el.dataset.id; return; }
      var id = +el.dataset.id;
      if (idx >= 0 && queue[idx] && queue[idx].id === id) { toggle(); return; }
      queue = queueFor(el);
      var i = queue.findIndex(function (t) { return t.id === id; });
      load(i < 0 ? 0 : i, true);
    });
    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('pl-row')) { e.preventDefault(); e.target.click(); }
    });

    ui.play.addEventListener('click', toggle);
    ui.next.addEventListener('click', function () { if (idx >= 0) next(false); });
    ui.prev.addEventListener('click', function () { if (idx >= 0) prev(); });
    ui.shuf.addEventListener('click', function () {
      shuffle = !shuffle; ui.shuf.classList.toggle('on', shuffle); ui.shuf.setAttribute('aria-pressed', shuffle); save(true);
    });
    ui.rep.addEventListener('click', function () {
      repeat = (repeat + 1) % 3;
      ui.rep.classList.toggle('on', repeat > 0);
      ui.rep.setAttribute('aria-pressed', repeat > 0);
      ui.rep.querySelector('i').className = 'bi ' + (repeat === 2 ? 'bi-repeat-1' : 'bi-repeat');
      save(true);
    });

    ui.range.addEventListener('input', function () {
      seeking = true;
      var d = audio.duration || (queue[idx] && queue[idx].dur) || 0;
      ui.cur.textContent = fmt(d * ui.range.value / 1000);
    });
    ui.range.addEventListener('change', function () {
      var d = audio.duration || 0;
      if (d) audio.currentTime = d * ui.range.value / 1000;
      seeking = false;
    });

    audio.addEventListener('timeupdate', function () {
      var d = audio.duration || (queue[idx] && queue[idx].dur) || 0;
      if (!seeking) {
        ui.range.value = d ? Math.round(audio.currentTime / d * 1000) : 0;
        ui.cur.textContent = fmt(audio.currentTime);
      }
      ui.range.style.setProperty('--pos', (ui.range.value / 10) + '%');
      save(false);
    });
    audio.addEventListener('loadedmetadata', function () {
      if (isFinite(audio.duration)) ui.dur.textContent = fmt(audio.duration);
    });
    audio.addEventListener('play', function () {
      audio.muted = false;                       // never play silently by accident
      var vi = $('#sxVolIc'); if (vi) vi.style.opacity = 1;
      $$('video').forEach(function (v) { v.pause(); });
      refresh();
      var t = queue[idx];
      if (t && !counted[t.id]) { counted[t.id] = 1; post('play', t.id); }
    });
    audio.addEventListener('pause', function () { refresh(); save(true); });
    audio.addEventListener('ended', function () {
      if (repeat === 2) { audio.currentTime = 0; audio.play(); return; }
      next(true);
    });
    audio.addEventListener('error', function () {
      if (!audio.getAttribute('src')) return;
      var c = audio.error ? audio.error.code : 0;
      ui.artist.textContent = c === 4 ? 'File not found or format not supported (check uploads folder)'
        : c === 3 ? 'Audio file is damaged' : c === 2 ? 'Network error while loading' : 'This file could not be played';
      if (window.console) console.error('Player error', c, audio.src);
      try { localStorage.removeItem(KEY); } catch (e) { }
    });
    window.addEventListener('pagehide', function () { if (idx >= 0) save(true); });

    /* restore the last track (paused) when moving between pages */
    try {
      var s = JSON.parse(localStorage.getItem(KEY) || 'null');
      if (s && Array.isArray(s.q) && s.q.length && s.i >= 0 && s.i < s.q.length) {
        /* only bring back songs that still exist on the server (fresh title / cover / file path) */
        var ids = s.q.map(function (t) { return t && t.id; }).filter(Boolean).join(',');
        fetch('api.php?action=validate&ids=' + encodeURIComponent(ids), { credentials: 'same-origin', cache: 'no-store' })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (idx >= 0) return;                      // the visitor already started a song
            var fresh = {}, q = [], ni = -1;
            ((res && res.items) || []).forEach(function (m) { fresh[m.id] = m; });
            s.q.forEach(function (t, k) {
              var m = t && fresh[t.id];
              if (m && m.type === 'music') {
                if (k === s.i) ni = q.length;
                q.push({ id: m.id, type: m.type, title: m.title, artist: m.artist, src: m.src, cover: m.cover, dur: m.dur });
              }
            });
            if (!q.length || ni < 0) { try { localStorage.removeItem(KEY); } catch (e) { } return; }   // old song is gone
            queue = q; shuffle = !!s.s; repeat = s.r | 0;
            ui.shuf.classList.toggle('on', shuffle);
            ui.rep.classList.toggle('on', repeat > 0);
            ui.rep.querySelector('i').className = 'bi ' + (repeat === 2 ? 'bi-repeat-1' : 'bi-repeat');
            load(ni, false, s.t);
          })
          .catch(function () { /* server unreachable: do not show a song we cannot verify */ });
      }
    } catch (e) { }

    /* "Start Listening" in the hero */
    $$('.js-play-first').forEach(function (b) {
      b.addEventListener('click', function () {
        var first = $('#trending .js-play[data-type="music"]') || $('.js-play[data-type="music"]');
        if (first) first.click(); else location.href = 'browse.php';
      });
    });
  } else {
    $$('.js-play-first').forEach(function (b) { b.addEventListener('click', function () { location.href = 'browse.php'; }); });
  }

  /* videos: count a play, and stop the audio player while a video plays */
  $$('.js-media-el').forEach(function (v) {
    var done = false;
    v.addEventListener('play', function () {
      if (audio) audio.pause();
      if (!done) { done = true; post('play', v.dataset.id); }
    });
  });

  /* ================= PLAYLIST BUTTONS ================= */
  function renderPlaylist(items) {
    var list = $('#plList'); if (!list) return;
    list.textContent = '';
    items.slice(0, 6).forEach(function (m) {
      var row = document.createElement('div');
      row.className = 'pl-row js-play'; row.setAttribute('role', 'button'); row.tabIndex = 0;
      row.dataset.id = m.id; row.dataset.type = m.type; row.dataset.title = m.title; row.dataset.artist = m.artist;
      row.dataset.src = m.src; row.dataset.cover = m.cover || ''; row.dataset.dur = m.dur;
      var th = document.createElement('span'); th.className = 'pl-thumb';
      if (m.cover) { var im = document.createElement('img'); im.src = m.cover; im.alt = ''; th.appendChild(im); }
      else { var ic = document.createElement('i'); ic.className = 'bi bi-' + (m.type === 'video' ? 'play-fill' : 'music-note-beamed'); th.appendChild(ic); }
      var info = document.createElement('span'); info.className = 'pl-info';
      var b = document.createElement('b'); b.textContent = m.title;
      var sm = document.createElement('small'); sm.textContent = m.artist;
      info.appendChild(b); info.appendChild(sm);
      var du = document.createElement('span'); du.className = 'pl-dur'; du.textContent = m.durText || '';
      var eq = document.createElement('span'); eq.className = 'pl-eq'; eq.setAttribute('aria-hidden', 'true'); eq.innerHTML = '<i></i><i></i><i></i>';
      row.appendChild(th); row.appendChild(info); row.appendChild(du); row.appendChild(eq);
      list.appendChild(row);
    });
    var empty = $('#plEmpty'); if (empty) empty.hidden = items.length > 0;
    markPlaying();
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-pl');
    if (!btn) return;
    e.preventDefault();
    var id = btn.dataset.id;
    post('toggle', id).then(function (res) {
      if (res.login) { location.href = 'login.php'; return; }
      if (!res.ok) return;
      $$('.js-pl[data-id="' + id + '"]').forEach(function (b) {
        b.classList.toggle('on', res.added);
        b.title = res.added ? 'Remove from my playlist' : 'Add to my playlist';
        var i = b.querySelector('i'); if (i && !b.classList.contains('js-pl-remove')) i.className = 'bi ' + (res.added ? 'bi-check-lg' : 'bi-plus-lg');
      });
      renderPlaylist(res.items);
      if (btn.classList.contains('js-pl-remove') && !res.added) {
        var row = btn.closest('.tt-row'); if (row) row.remove();
        if (!$('.tt-row')) location.reload();
      }
    });
  });
})();
