<?php
require 'config.php';
require_login();
if (is_admin()) { header('Location: ../admin/'); exit; }

$uid = (int)$_SESSION['user']['id'];

/* ---------- avatar helpers ---------- */
if(!defined('AV_MAX_BYTES')) define('AV_MAX_BYTES', 2097152); // 2 MB
$AV_DIR = avatar_dir_fs();
$AV_URL = avatar_dir_url();
if (!is_dir($AV_DIR)) { @mkdir($AV_DIR, 0755, true); }


/* ---------- CSRF + flash ---------- */
$accFlash = $_SESSION['acc_flash'] ?? null; unset($_SESSION['acc_flash']);
function flash_go(string $type, string $msg): void { $_SESSION['acc_flash'] = [$type, $msg]; header('Location: account.php'); exit; }

$errors = []; $old = null; $openEdit = false;

/* ---------- POST handling ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403); flash_go('error', 'Your session expired. Please try again.');
    }
    $action = $_POST['action'] ?? '';
    $cur = $pdo->prepare('SELECT avatar FROM users WHERE id=?'); $cur->execute([$uid]);
    $curAvatar = $cur->fetchColumn() ?: null;

    if ($action === 'profile') {
        $old = [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'email'     => trim((string)($_POST['email'] ?? '')),
            'phone'     => trim((string)($_POST['phone'] ?? '')),
            'address'   => trim((string)($_POST['address'] ?? '')),
        ];
        if ($old['full_name'] === '' ) $errors['full_name'] = 'Full name is required.';
        elseif (mb_strlen($old['full_name']) > 120) $errors['full_name'] = 'Full name is too long (max 120).';
        if ($old['email'] === '') $errors['email'] = 'Email is required.';
        elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 190) $errors['email'] = 'Enter a valid email address.';
        if ($old['phone'] !== '' && !preg_match('/^\+?[0-9][0-9\s\-()]{6,19}$/', $old['phone'])) $errors['phone'] = 'Enter a valid phone number (7–20 digits; + - ( ) and spaces allowed).';
        if (mb_strlen($old['address']) > 255) $errors['address'] = 'Address is too long (max 255).';
        if (!isset($errors['email'])) {
            $dup = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?'); $dup->execute([$old['email'], $uid]);
            if ($dup->fetch()) $errors['email'] = 'This email is already used by another account.';
        }
        if (!$errors) {
            $up = $pdo->prepare('UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?');
            $up->execute([$old['full_name'], $old['email'], $old['phone'], $old['address'], $uid]);
            if (isset($_SESSION['user']['full_name'])) $_SESSION['user']['full_name'] = $old['full_name'];
            if (isset($_SESSION['user']['email']))     $_SESSION['user']['email']     = $old['email'];
            flash_go('success', 'Your profile has been updated.');
        }
        $openEdit = true;

    } elseif ($action === 'photo') {
        $f = $_FILES['avatar'] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) flash_go('error', 'Please choose a picture first.');
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > AV_MAX_BYTES) flash_go('error', 'Picture is too large (max 2 MB).');
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) flash_go('error', 'Upload failed. Please try again.');

        $extOk = in_array(strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp'], true);
        $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $map   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $info  = @getimagesize($f['tmp_name']);
        if (!$extOk || !isset($map[$mime]) || !$info || !isset($map[$info['mime']]) || ($info[0] * $info[1]) > 25000000)
            flash_go('error', 'Only JPG, JPEG, PNG or WEBP images are allowed.');

        $name = 'u' . $uid . '_' . bin2hex(random_bytes(16)) . '.' . $map[$mime]; // unique + bound to user id
        if (!move_uploaded_file($f['tmp_name'], $AV_DIR . $name)) flash_go('error', 'Could not save the picture. Check folder permissions.');
        @chmod($AV_DIR . $name, 0644);

        $up = $pdo->prepare('UPDATE users SET avatar=? WHERE id=?'); // WHERE id = logged-in user only
        $up->execute([$name, $uid]);
        avatar_delete($curAvatar, $uid, $AV_DIR); // remove old file only after DB updated
        flash_go('success', 'Profile picture updated.');

    } elseif ($action === 'remove_photo') {
        $pdo->prepare('UPDATE users SET avatar=NULL WHERE id=?')->execute([$uid]);
        avatar_delete($curAvatar, $uid, $AV_DIR);
        flash_go('success', 'Profile picture removed.');
    }
}

/* ---------- data ---------- */
$s = $pdo->prepare("SELECT r.*, m.title, m.type FROM reviews r JOIN media m ON m.id=r.media_id WHERE r.user_id=? ORDER BY r.updated_at DESC");
$s->execute([$uid]);
$reviews = $s->fetchAll();
$u = $pdo->prepare('SELECT * FROM users WHERE id=?'); $u->execute([$uid]); $u = $u->fetch();
$plCount = count($MY_PL);
$revCount = count($reviews);
$avg = $revCount ? array_sum(array_map(fn($r) => (int)$r['rating'], $reviews)) / $revCount : 0;
$avatarSrc = avatar_url($u['avatar'] ?? null, $uid, $AV_DIR, $AV_URL); // null => default avatar
$val = $old ?? ['full_name'=>$u['full_name'],'email'=>$u['email'],'phone'=>$u['phone'],'address'=>$u['address'] ?? ''];
$csrf = csrf_token();
$pageTitle = 'My Account'; $active = '';
require 'header.php';
layout_open();
?>
<style>
.acc-toast{position:fixed;top:84px;right:20px;z-index:1080;max-width:360px;padding:.85rem 1.1rem;border-radius:12px;color:#fff;font-weight:500;box-shadow:0 10px 30px rgba(0,0,0,.35);display:flex;gap:.6rem;align-items:center;animation:accIn .35s ease}
.acc-toast.success{background:#198754}.acc-toast.error{background:#c0392b}
@keyframes accIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}
.pm-avatar{position:relative;width:112px;height:112px;border-radius:50%;flex:none}
.pm-avatar img,.pm-avatar .av-default{width:112px;height:112px;border-radius:50%;object-fit:cover;display:block;border:3px solid rgba(255,255,255,.15);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.av-default{background:linear-gradient(135deg,#3a3f5c,#1f2236);display:flex;align-items:center;justify-content:center;font-size:3rem;color:#c9cbe0}
.av-cam{position:absolute;right:-2px;bottom:-2px;width:36px;height:36px;border-radius:50%;border:2px solid #14151f;background:var(--accent,#7c5cff);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .2s}
.av-cam:hover{transform:scale(1.1)}
.av-bar{display:none;gap:.5rem;flex-wrap:wrap;margin-top:.75rem}
.av-bar.on{display:flex}
.pd-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
.acc-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:1.25rem;transition:box-shadow .25s,border-color .25s}
.acc-card:hover{border-color:rgba(255,255,255,.16);box-shadow:0 8px 28px rgba(0,0,0,.25)}
.acc-form{display:none;margin-top:1rem}.acc-form.on{display:block}
.acc-view.off{display:none}
.acc-form .fg{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.acc-form .full{grid-column:1/-1}
.acc-form label{font-size:.8rem;opacity:.75;margin-bottom:.3rem;display:block}
.acc-form input,.acc-form textarea{width:100%;padding:.7rem .9rem;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:rgba(0,0,0,.25);color:inherit;transition:border-color .2s,box-shadow .2s}
.acc-form input:focus,.acc-form textarea:focus{outline:0;border-color:var(--accent,#7c5cff);box-shadow:0 0 0 3px rgba(124,92,255,.25)}
.acc-form .bad{border-color:#e5584a}.acc-form .err{color:#ff8a7e;font-size:.8rem;margin-top:.25rem}
.acc-actions{display:flex;gap:.6rem;margin-top:1.2rem;flex-wrap:wrap}
.btn.loading{pointer-events:none;opacity:.75}
.btn .spin{display:none;width:1em;height:1em;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:accSp .7s linear infinite;vertical-align:-2px;margin-right:.35rem}
.btn.loading .spin{display:inline-block}
@keyframes accSp{to{transform:rotate(360deg)}}
@media(max-width:640px){.acc-form .fg{grid-template-columns:1fr}.acc-toast{left:12px;right:12px;max-width:none}}

/* --- profile header layout fix --- */
.pm-av-wrap{display:flex;flex-direction:column;align-items:center;gap:.75rem}
.pm-profile .pm-avatar{width:112px;height:112px;margin:0;padding:0;background:none;box-shadow:none;overflow:visible}
.pm-profile .pm-avatar::before,.pm-profile .pm-avatar::after{display:none!important}
.pm-profile .pm-avatar img,.pm-profile .pm-avatar .av-default{width:112px;height:112px;margin:0;transform:none;box-shadow:0 8px 24px rgba(0,0,0,.35);border:3px solid rgba(255,255,255,.15)}
.pm-profile .pm-avatar img[hidden]{display:none}
.av-cam{width:34px;height:34px;font-size:.95rem;line-height:1;right:0;bottom:2px;padding:0}
.av-cam i{display:block;line-height:1;font-size:.95rem}
.av-bar{justify-content:center}
@media(max-width:767px){
  .pm-profile{display:flex!important;flex-direction:column;align-items:center;justify-content:flex-start!important;text-align:center;gap:1rem;min-height:0!important;height:auto!important}
  .pm-profile-txt{text-align:center;margin:0}
  .pm-profile-act{display:flex;flex-direction:column;width:100%;gap:.6rem;margin:.5rem 0 0!important}
  .pm-profile-act .btn{width:100%}
}

/* --- preview bug fix: preview replaces default, never stacks --- */
.pm-profile .pm-avatar .av-default[hidden]{display:none!important}
.pm-profile .pm-avatar #avImg:not([hidden]){display:block}
.av-bar .btn{color:#fff;white-space:nowrap}
.av-bar .btn-sg-outline{background:rgba(255,255,255,.06)!important;border:1px solid rgba(255,255,255,.18)!important;color:#fff!important}
.av-bar .btn-sg{color:#fff!important}

/* --- force exact centering of default avatar icon --- */
.pm-profile .pm-avatar .av-default{position:relative!important;display:block!important;padding:0!important;overflow:hidden}
.pm-profile .pm-avatar .av-default[hidden]{display:none!important}
.pm-profile .pm-avatar .av-default svg{position:absolute!important;left:50%!important;top:50%!important;transform:translate(-50%,-50%)!important;margin:0!important;width:56px!important;height:50px!important}
.pm-av-wrap{width:100%}
.pm-av-wrap .pm-avatar{margin-left:auto!important;margin-right:auto!important}
</style>

<?php if ($accFlash): ?>
  <div class="acc-toast <?= h($accFlash[0]) ?>" role="status" id="accToast"><i class="bi <?= $accFlash[0]==='success'?'bi-check-circle-fill':'bi-exclamation-triangle-fill' ?>"></i><span><?= h($accFlash[1]) ?></span></div>
<?php endif; ?>

<div class="ac-layout">
<section class="pm-profile" aria-labelledby="pmHello">
  <div class="pm-av-wrap">
    <div class="pm-avatar">
      <?php if ($avatarSrc): ?>
        <img id="avImg" src="<?= h($avatarSrc) ?>" alt="Your profile picture" width="112" height="112">
      <?php else: ?>
        <div id="avDef" class="av-default" aria-label="Default avatar"><svg viewBox="2 3.5 20 18" width="56" height="50" fill="currentColor" aria-hidden="true" style="display:block"><circle cx="12" cy="8.5" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></div>
        <img id="avImg" src="" alt="Preview" width="112" height="112" hidden>
      <?php endif; ?>
      <label class="av-cam" for="avFile" title="Change photo"><i class="bi bi-camera-fill"></i></label>
    </div>
    <form id="avForm" method="post" enctype="multipart/form-data" class="av-bar">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="photo">
      <input type="file" id="avFile" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
      <button class="btn btn-sg btn-sm" type="submit"><span class="spin"></span>Save photo</button>
      <button class="btn btn-sg-outline btn-sm" type="button" id="avCancel">Cancel</button>
    </form>
    <div class="av-bar on" id="avIdle">
      <label class="btn btn-sg-outline btn-sm" for="avFile"><i class="bi bi-image"></i> Change photo</label>
      <?php if ($avatarSrc): ?>
      <form method="post" onsubmit="return confirm('Remove your profile picture?')">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="action" value="remove_photo">
        <button class="btn btn-sg-outline btn-sm" type="submit"><i class="bi bi-trash"></i> Remove</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="pm-profile-txt">
    <span class="kicker">My account</span>
    <h1 id="pmHello">Hello, <?= h($u['full_name']) ?></h1>
    <p>@<?= h($u['username']) ?></p>
  </div>
  <div class="pm-profile-act">
    <a class="btn btn-sg" href="playlists.php"><i class="bi bi-music-note-list"></i> My playlist</a>
    <a class="btn btn-sg-outline" href="logout.php"><i class="bi bi-box-arrow-right"></i> Log out</a>
  </div>
</section>

<div class="ac-main">
<div class="pm-stats">
  <a class="pm-stat" href="playlists.php"><i class="bi bi-collection-play-fill"></i><span><strong><?= (int)$plCount ?></strong><small>Playlist items</small></span></a>
  <div class="pm-stat"><i class="bi bi-chat-heart-fill"></i><span><strong><?= (int)$revCount ?></strong><small>Reviews written</small></span></div>
  <div class="pm-stat"><i class="bi bi-star-fill"></i><span><strong><?= $revCount ? number_format($avg, 1) : '–' ?></strong><small>Average rating you give</small></span></div>
</div>

<div class="acc-card">
  <div class="pd-head">
    <h2 class="pm-detail-title" style="margin:0">Profile details</h2>
    <button type="button" class="btn btn-sg mb-2" id="editBtn"<?= $openEdit ? ' hidden' : '' ?>><i class="bi bi-pencil-square"></i> Edit profile</button>
  </div>

  <div class="acc-view<?= $openEdit ? ' off' : '' ?>" id="accView">
    <div class="acc-grid">
      <div><small>Username</small><b><?= h($u['username']) ?></b></div>
      <div><small>Full name</small><b><?= h($u['full_name']) ?></b></div>
      <div><small>Email</small><b><?= h($u['email']) ?></b></div>
      <div><small>Phone</small><b><?= h($u['phone']) ?></b></div>
      <div><small>Address</small><b><?= h($u['address'] ?? '') ?></b></div>
    </div>
  </div>

  <form class="acc-form<?= $openEdit ? ' on' : '' ?>" id="accForm" method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
    <input type="hidden" name="action" value="profile">
    <div class="fg">
      <div class="full"><label>Username (cannot be changed)</label><input type="text" value="<?= h($u['username']) ?>" disabled></div>
      <div><label for="f_name">Full name *</label><input id="f_name" name="full_name" maxlength="120" required value="<?= h($val['full_name']) ?>" class="<?= isset($errors['full_name'])?'bad':'' ?>"><?php if(isset($errors['full_name'])): ?><div class="err"><?= h($errors['full_name']) ?></div><?php endif; ?></div>
      <div><label for="f_email">Email *</label><input id="f_email" type="email" name="email" maxlength="190" required value="<?= h($val['email']) ?>" class="<?= isset($errors['email'])?'bad':'' ?>"><?php if(isset($errors['email'])): ?><div class="err"><?= h($errors['email']) ?></div><?php endif; ?></div>
      <div><label for="f_phone">Phone</label><input id="f_phone" type="tel" name="phone" maxlength="40" value="<?= h($val['phone']) ?>" class="<?= isset($errors['phone'])?'bad':'' ?>"><?php if(isset($errors['phone'])): ?><div class="err"><?= h($errors['phone']) ?></div><?php endif; ?></div>
      <div><label for="f_addr">Address</label><input id="f_addr" name="address" maxlength="255" value="<?= h($val['address']) ?>" class="<?= isset($errors['address'])?'bad':'' ?>"><?php if(isset($errors['address'])): ?><div class="err"><?= h($errors['address']) ?></div><?php endif; ?></div>
    </div>
    <div class="acc-actions">
      <button class="btn btn-sg" type="submit" id="saveBtn"><span class="spin"></span><i class="bi bi-check2-circle"></i> Update profile</button>
      <button class="btn btn-sg-outline" type="button" id="cancelBtn">Cancel</button>
    </div>
  </form>
</div>

<section class="sec">
  <div class="sec-head"><h2><span class="sec-ic"><i class="bi bi-star-fill"></i></span> My reviews &amp; ratings</h2></div>
  <?php if (!$reviews): ?>
    <div class="empty"><i class="bi bi-star"></i><p>You have not reviewed anything yet. <a href="browse.php">Explore the library</a></p></div>
  <?php endif; ?>
  <?php foreach ($reviews as $r): ?>
    <div class="review"><div class="d-flex justify-content-between gap-3"><strong><?= h($r['title']) ?></strong><span class="stars" aria-label="<?= (int)$r['rating'] ?> out of 5"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span></div>
      <?php if (trim((string)$r['review']) !== ''): ?><p><?= nl2br(h($r['review'])) ?></p><?php endif; ?>
      <a href="detail.php?id=<?= (int)$r['media_id'] ?>#reviews">Edit review</a></div>
  <?php endforeach; ?>
</section>
</div>
</div>

<script>
(function(){
  var $=function(i){return document.getElementById(i)};
  var form=$('accForm'),view=$('accView'),edit=$('editBtn');
  var orig=new FormData(form);
  function open(){view.classList.add('off');form.classList.add('on');edit.hidden=true;$('f_name').focus();}
  function close(){ // restore original values -> nothing lost
    ['full_name','email','phone','address'].forEach(function(n){form.elements[n].value=orig.get(n);form.elements[n].classList.remove('bad');});
    form.querySelectorAll('.err').forEach(function(e){e.remove();});
    form.classList.remove('on');view.classList.remove('off');edit.hidden=false;
  }
  edit.addEventListener('click',open);
  $('cancelBtn').addEventListener('click',close);
  <?php if ($openEdit): ?>orig=new FormData();['full_name','email','phone','address'].forEach(function(n){orig.set(n,<?= json_encode([
      'full_name'=>$u['full_name'],'email'=>$u['email'],'phone'=>$u['phone'],'address'=>$u['address'] ?? ''], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>[n]);});<?php endif; ?>

  form.addEventListener('submit',function(e){
    var bad=false;
    if(!form.full_name.value.trim()){bad=true;form.full_name.classList.add('bad');}
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.value.trim())){bad=true;form.email.classList.add('bad');}
    var p=form.phone.value.trim();
    if(p&&!/^\+?[0-9][0-9\s\-()]{6,19}$/.test(p)){bad=true;form.phone.classList.add('bad');}
    if(bad){e.preventDefault();return;}
    $('saveBtn').classList.add('loading');
  });

  // avatar preview
  var file=$('avFile'),img=$('avImg'),def=$('avDef'),bar=$('avForm'),idle=$('avIdle'),prevSrc=img.getAttribute('src');
  file.addEventListener('change',function(){
    var f=file.files[0]; if(!f)return;
    if(['image/jpeg','image/png','image/webp'].indexOf(f.type)<0){alert('Only JPG, JPEG, PNG or WEBP images are allowed.');file.value='';return;}
    if(f.size>2*1024*1024){alert('Picture is too large (max 2 MB).');file.value='';return;}
    var r=new FileReader();
    r.onload=function(ev){img.src=ev.target.result;img.hidden=false;if(def)def.hidden=true;bar.classList.add('on');idle.classList.remove('on');};
    r.readAsDataURL(f);
  });
  $('avCancel').addEventListener('click',function(){
    file.value='';bar.classList.remove('on');idle.classList.add('on');
    if(prevSrc){img.src=prevSrc;}else{img.hidden=true;img.removeAttribute('src');if(def)def.hidden=false;}
  });
  bar.addEventListener('submit',function(){bar.querySelector('button[type=submit]').classList.add('loading');});
  var t=$('accToast');if(t)setTimeout(function(){t.style.transition='opacity .4s';t.style.opacity=0;setTimeout(function(){t.remove()},450)},4500);
})();
</script>
<?php layout_close(); require 'footer.php'; ?>
