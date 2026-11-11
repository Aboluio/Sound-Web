/* Sound Group — Elite layer v13: expanding search + small page helpers */
(function () {
  var f = document.getElementById('elSearch'), b = document.getElementById('elSearchBtn'), i = document.getElementById('elSearchIn');
  if (f && b && i) {
    function set(open) {
      f.classList.toggle('open', open);
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) i.focus();
    }
    b.addEventListener('click', function () {
      if (f.classList.contains('open') && i.value.trim()) { f.submit(); return; }
      set(!f.classList.contains('open'));
    });
    document.addEventListener('keydown', function (e) {
      var t = e.target && e.target.closest ? e.target.closest('input,textarea,select') : null;
      if (e.key === '/' && !t) { e.preventDefault(); set(true); }
      if (e.key === 'Escape' && f.classList.contains('open')) { set(false); b.focus(); }
    });
    document.addEventListener('click', function (e) {
      if (f.classList.contains('open') && !f.contains(e.target) && !i.value.trim()) set(false);
    });
    if (i.value.trim()) f.classList.add('open');
  }
  /* sticky A-Z index on the Artists page highlights the letter in view */
  var idx = document.querySelectorAll('.ar-index a');
  if (idx.length && 'IntersectionObserver' in window) {
    var map = {};
    idx.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (l) {
      l.forEach(function (x) {
        if (x.isIntersecting) { idx.forEach(function (a) { a.classList.remove('on'); }); var a = map[x.target.id]; if (a) a.classList.add('on'); }
      });
    }, { rootMargin: '-30% 0px -60% 0px' });
    document.querySelectorAll('.ar-group').forEach(function (g) { io.observe(g); });
  }
})();
