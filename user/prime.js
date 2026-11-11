/* Sound Group — Prime layer v12: show/hide password toggles */
(function () {
  document.querySelectorAll('[data-pm-toggle]').forEach(function (btn) {
    var input = document.getElementById(btn.getAttribute('data-pm-toggle'));
    if (!input) return;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.firstElementChild.className = 'bi bi-' + (show ? 'eye-slash' : 'eye');
      input.focus();
    });
  });
})();
