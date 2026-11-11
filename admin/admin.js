/* Sound Web admin: Spark Admin shell interactions + live charts (data comes from window.SG) */
document.addEventListener('DOMContentLoaded', function () {
  /* ---- Dark / Light theme (remembered) ---- */
  const root = document.documentElement;
  function applyTheme(t) {
    root.setAttribute('data-theme', t);
    document.querySelectorAll('#btn-theme i').forEach(function (i) {
      i.className = t === 'light' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    });
    try { localStorage.setItem('sgTheme', t); } catch (e) {}
  }
  applyTheme(root.getAttribute('data-theme') === 'light' ? 'light' : 'dark');
  document.querySelectorAll('#btn-theme').forEach(function (b) {
    b.addEventListener('click', function () {
      applyTheme(root.getAttribute('data-theme') === 'light' ? 'dark' : 'light');
      if (window.SG) setTimeout(function () { location.reload(); }, 150); /* redraw charts with theme colours */
    });
  });

  /* ---- Mobile sidebar + backdrop ---- */
  const sidebar = document.querySelector('.sidebar-wrapper');
  const toggleBtn = document.querySelector('.sidebar-toggle-btn');
  const overlay = document.createElement('div');
  overlay.className = 'sidebar-overlay';
  document.body.appendChild(overlay);
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show', sidebar.classList.contains('show'));
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

  /* ---- Desktop sidebar minimise (remembered) ---- */
  const dBtn = document.querySelector('#desktop-sidebar-toggle');
  function setMin(on) {
    document.body.classList.toggle('sidebar-minimized', on);
    const i = dBtn && dBtn.querySelector('i');
    if (i) i.className = on ? 'bi bi-chevron-bar-right' : 'bi bi-chevron-bar-left';
    try { localStorage.setItem('sgSidebarMin', on ? '1' : '0'); } catch (e) {}
  }
  if (dBtn) {
    try { if (localStorage.getItem('sgSidebarMin') === '1') setMin(true); } catch (e) {}
    dBtn.addEventListener('click', function () {
      setMin(!document.body.classList.contains('sidebar-minimized'));
      setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 300);
    });
  }

  /* ---- Fullscreen ---- */
  const fs = document.querySelector('#btn-fullscreen');
  if (fs) {
    fs.addEventListener('click', function () {
      if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(function () {});
      else document.exitFullscreen().catch(function () {});
    });
    document.addEventListener('fullscreenchange', function () {
      const i = fs.querySelector('i');
      if (i) i.className = document.fullscreenElement ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
    });
  }

  /* ---- Password eye on login/setup ---- */
  const eye = document.getElementById('toggle-password'), pw = document.getElementById('password');
  if (eye && pw) {
    eye.addEventListener('click', function () {
      const show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      const i = eye.querySelector('i');
      if (i) i.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  }

  /* ---- Auto-hide flash ---- */
  const flash = document.getElementById('flash');
  if (flash) setTimeout(function () { flash.remove(); }, 5000);

  /* ---- Live table search (client side) ---- */
  document.querySelectorAll('[data-table-filter]').forEach(function (inp) {
    const rows = document.querySelectorAll(inp.getAttribute('data-table-filter') + ' tbody tr[data-row]');
    inp.addEventListener('input', function () {
      const q = inp.value.trim().toLowerCase();
      rows.forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none'; });
    });
  });

  /* ---- Charts ---- */
  if (!window.SG || typeof ApexCharts === 'undefined') return;
  const font = 'Plus Jakarta Sans, sans-serif';
  const light = root.getAttribute('data-theme') === 'light';
  const soft = light ? '#6b5f7d' : '#A79BB8', strong = light ? '#1b1230' : '#FFFFFF', gridc = light ? 'rgba(124,58,237,.16)' : 'rgba(160,32,240,.14)';

  const growth = document.querySelector('#revenue-chart');
  if (growth && SG.growth) {
    new ApexCharts(growth, {
      series: [{ name: 'Music', data: SG.growth.music }, { name: 'Videos', data: SG.growth.video }],
      chart: { type: 'bar', height: 220, stacked: false, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: font },
      colors: ['#A020F0', '#D946EF'],
      states: { hover: { filter: { type: 'none' } } },
      plotOptions: { bar: { horizontal: false, columnWidth: '48%', borderRadius: 6 } },
      dataLabels: { enabled: false },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      legend: { show: false },
      grid: { borderColor: gridc, strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } }, padding: { top: 0, right: 0, bottom: 0, left: 0 } },
      xaxis: { categories: SG.growth.labels, labels: { style: { colors: soft, fontSize: '11px', fontWeight: 500 } }, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: { labels: { show: false }, min: 0 },
      fill: { opacity: 1 },
      tooltip: { y: { formatter: function (v) { return v + (v === 1 ? ' upload' : ' uploads'); } }, theme: light ? 'light' : 'dark' }
    }).render();
  }

  const mix = document.querySelector('#views-chart');
  if (mix && SG.mix) {
    const empty = (SG.mix.music + SG.mix.video) === 0;
    new ApexCharts(mix, {
      series: empty ? [1] : [SG.mix.music, SG.mix.video],
      chart: { type: 'donut', height: 250, fontFamily: font },
      labels: empty ? ['No media yet'] : ['Music', 'Videos'],
      colors: empty ? ['rgba(160,32,240,.14)'] : ['#A020F0', '#D946EF'],
      states: { hover: { filter: { type: 'none' } } },
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: { pie: { donut: { size: '72%', background: 'transparent', labels: {
        show: true,
        name: { show: true, fontSize: '12px', fontWeight: 500, color: soft, offsetY: -8 },
        value: { show: true, fontSize: '26px', fontWeight: 800, color: strong, offsetY: 8, formatter: function (v) { return empty ? '0' : v; } },
        total: { show: true, label: 'Total Media', fontSize: '11px', fontWeight: 500, color: soft, formatter: function () { return SG.mix.music + SG.mix.video; } }
      } } } },
      tooltip: { enabled: !empty, theme: light ? 'light' : 'dark' }
    }).render();
  }

  function spark(sel, name, data, color) {
    const el = document.querySelector(sel);
    if (!el || !data) return;
    new ApexCharts(el, {
      series: [{ name: name, data: data }],
      chart: { type: 'area', height: 45, sparkline: { enabled: true }, fontFamily: font },
      stroke: { curve: 'smooth', width: 2 },
      fill: { opacity: 0.1, type: 'solid' },
      colors: [color],
      tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: function () { return ''; } } }, marker: { show: false } }
    }).render();
  }
  if (SG.spark) {
    spark('#income-sparkline', 'Total media', SG.spark.media, '#D946EF');
    spark('#return-sparkline', 'Users', SG.spark.users, '#D946EF');
  }
});
