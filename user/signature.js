/* Sound Group — Signature layer v14: scroll progress, section reveal, image fade-in. Progressive: the page works fully without it. */
(function () {
  var root = document.documentElement;
  var calm = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* thin progress hairline under the very top edge */
  var bar = document.createElement('div'); bar.className = 'sg-progress'; bar.setAttribute('aria-hidden', 'true'); document.body.appendChild(bar);
  var tick = false;
  function prog() {
    var h = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, window.scrollY / h) : 0) + ')'; tick = false;
  }
  window.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(prog); } }, { passive: true });
  window.addEventListener('resize', prog); prog();

  /* images fade in once loaded (no layout change) */
  document.querySelectorAll('.m-thumb img, .tt-thumb img').forEach(function (img) {
    if (img.complete) return;
    img.classList.add('sg-fade');
    var done = function () { img.classList.add('sg-in'); };
    img.addEventListener('load', done); img.addEventListener('error', done);
  });

  /* sections rise into view; everything is shown after 2.5s no matter what */
  if (calm || !('IntersectionObserver' in window)) return;
  var items = document.querySelectorAll('.sec, .el-foot-top');
  if (!items.length) return;
  root.classList.add('sg-reveal');
  var io = new IntersectionObserver(function (list) {
    list.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('sg-in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
  items.forEach(function (el) { io.observe(el); });
  setTimeout(function () { items.forEach(function (el) { el.classList.add('sg-in'); }); }, 2500);
})();
