<?php
/* Sound Web - song diagnostics. Open: http://localhost/syedsoundbar/songcheck.php  (delete this file when done) */
require __DIR__ . '/config.php';
header('Content-Type: text/html; charset=utf-8');
$root = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/songcheck.php')), '/');
try { $rows = $pdo->query('SELECT id,type,title,file_path FROM media ORDER BY id DESC LIMIT 30')->fetchAll(); $dberr = ''; }
catch (Throwable $e) { $rows = []; $dberr = $e->getMessage(); }
?><!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Song check</title>
<body style="margin:0;background:#0a0908;color:#f6f0e4;font-family:system-ui,sans-serif;padding:24px;max-width:900px">
<h1 style="color:#e9c97d;margin-top:0">Song check</h1>
<p style="color:#c9c0ad">Har song ke neeche ek simple player hai. Usay play karo aur neeche likha result dekho.</p>
<?php if ($dberr): ?><p style="color:#ff7d8d">Database error: <?= h($dberr) ?></p><?php endif; ?>
<?php if (!$rows && !$dberr): ?><p style="color:#ff7d8d">Database mein koi song nahi hai. Admin Panel &gt; Add Music se upload karo.</p><?php endif; ?>
<?php foreach ($rows as $m):
    $rel  = ltrim(preg_replace('#^(\.\./)+#', '', str_replace('\\', '/', (string)$m['file_path'])), '/');
    $disk = __DIR__ . '/' . $rel;
    $url  = $root . '/' . implode('/', array_map('rawurlencode', explode('/', $rel)));
    $exists = is_file($disk); $size = $exists ? filesize($disk) : 0;
    $head = ''; if ($exists) { $fh = fopen($disk, 'rb'); $head = fread($fh, 3); fclose($fh); }
    $looksMp3 = $head === 'ID3' || (strlen($head) >= 2 && ord($head[0]) === 0xFF && (ord($head[1]) & 0xE0) === 0xE0);
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
    $isAudio = in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'], true);
?>
<div style="background:#14110d;border:1px solid #2a2418;border-radius:16px;padding:16px 18px;margin:16px 0">
  <b style="font-size:18px">#<?= (int)$m['id'] ?> &mdash; <?= h($m['title']) ?></b>
  <table style="margin:10px 0;font-size:14px;border-collapse:collapse">
    <tr><td style="padding:3px 14px 3px 0;color:#8c8472">Type in database</td><td><?= h($m['type']) ?><?= ($isAudio && $m['type'] !== 'music') ? ' <span style="color:#ffb86b">(audio file, but saved as video &mdash; site ab isay khud theek kar leti hai)</span>' : '' ?></td></tr>
    <tr><td style="padding:3px 14px 3px 0;color:#8c8472">file_path (database)</td><td><code><?= h($m['file_path']) ?></code></td></tr>
    <tr><td style="padding:3px 14px 3px 0;color:#8c8472">File on server</td><td style="color:<?= $exists ? '#5fd3a0' : '#ff7d8d' ?>"><?= $exists ? 'FOUND (' . round($size / 1048576, 2) . ' MB)' : 'NOT FOUND &mdash; is jagah file nahi hai: ' . h($disk) ?></td></tr>
    <?php if ($exists): ?>
    <tr><td style="padding:3px 14px 3px 0;color:#8c8472">File content</td><td style="color:<?= ($looksMp3 || $ext !== 'mp3') ? '#5fd3a0' : '#ff7d8d' ?>"><?= ($looksMp3 || $ext !== 'mp3') ? 'looks OK' : 'ye asli MP3 nahi lagti (file kharab ho sakti hai)' ?></td></tr>
    <?php endif; ?>
    <tr><td style="padding:3px 14px 3px 0;color:#8c8472">Browser link</td><td><a style="color:#e9c97d" href="<?= h($url) ?>" target="_blank"><?= h($url) ?></a></td></tr>
  </table>
  <audio class="t" controls preload="metadata" src="<?= h($url) ?>" style="width:100%"></audio>
  <div class="res" style="margin-top:8px;font-size:14px;color:#c9c0ad">Result: player load ho raha hai...</div>
  <div class="srv" style="font-size:13px;color:#8c8472"></div>
</div>
<?php endforeach; ?>

<div style="background:#14110d;border:1px solid #2a2418;border-radius:16px;padding:16px 18px;margin:16px 0">
  <b>Browser settings</b>
  <div id="ls" style="font-size:14px;color:#c9c0ad;margin:8px 0"></div>
  <button id="reset" style="background:#e9c97d;color:#1b1406;border:0;border-radius:999px;padding:9px 18px;font-weight:700;cursor:pointer">Player reset karo (volume + purani queue)</button>
</div>

<script>
(function () {
  var names = {1: 'play rok diya gaya', 2: 'network error', 3: 'file kharab / decode nahi hui', 4: 'file nahi mili ya format support nahi'};
  document.querySelectorAll('audio.t').forEach(function (a) {
    var res = a.parentNode.querySelector('.res'), srv = a.parentNode.querySelector('.srv');
    function say(t, ok) { res.textContent = 'Result: ' + t; res.style.color = ok ? '#5fd3a0' : '#ff7d8d'; }
    a.addEventListener('loadedmetadata', function () { say('file load ho gayi, length ' + Math.round(a.duration) + ' sec. Ab play dabao.', true); });
    a.addEventListener('playing', function () { say('CHAL RAHA HAI (volume ' + Math.round(a.volume * 100) + '%, muted: ' + a.muted + '). Awaaz na aaye to laptop/tab ka volume dekho.', true); });
    a.addEventListener('error', function () { say('ERROR - ' + (names[a.error ? a.error.code : 0] || 'unknown'), false); });
    fetch(a.getAttribute('src'), { method: 'HEAD' }).then(function (r) {
      srv.textContent = 'Server answer: HTTP ' + r.status + ' | type: ' + (r.headers.get('content-type') || '-') + ' | size: ' + (r.headers.get('content-length') || '-');
      if (!r.ok) say('server ne file nahi di (HTTP ' + r.status + ')', false);
    }).catch(function () { srv.textContent = 'Server answer: request fail ho gayi'; });
  });
  function show() {
    var v = null, q = null; try { v = localStorage.getItem('sg_vol'); q = localStorage.getItem('sg_player_v1'); } catch (e) {}
    document.getElementById('ls').textContent = 'Saved volume: ' + (v === null ? 'none' : v) + '  |  Purani queue: ' + (q ? 'saved' : 'none');
  }
  show();
  document.getElementById('reset').onclick = function () { try { localStorage.removeItem('sg_vol'); localStorage.removeItem('sg_player_v1'); } catch (e) {} show(); alert('Reset ho gaya. Ab website par Ctrl+F5 karo.'); };
})();
</script>
</body>
