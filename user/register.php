<?php
require 'config.php';
if (logged_in()) { header('Location: ' . (is_admin() ? '../admin/' : 'index.php')); exit; }
$error = ''; $v = ['username' => '', 'email' => '', 'full_name' => '', 'address' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$v['username'] || !filter_var($v['email'], FILTER_VALIDATE_EMAIL) || !$v['full_name'] || !$v['address'] || !$v['phone'] || strlen($password) < 8) {
        $error = 'Please complete all required fields. Password must be at least 8 characters.';
    } else {
        try {
            $pdo->prepare("INSERT INTO users(username,email,full_name,address,phone,password,role) VALUES(?,?,?,?,?,?, 'user')")
                ->execute([$v['username'], $v['email'], $v['full_name'], $v['address'], $v['phone'], password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1'); exit;
        } catch (PDOException $e) { $error = 'Username or email already exists.'; }
    }
}
$pageTitle = 'Register'; $active = '';
require 'header.php';
?>
<section class="auth-wrap">
  <div class="pm-auth wide">
    <aside class="pm-auth-art">
      <span class="pm-auth-badge"><i class="bi bi-soundwave"></i> <?= h($SITE['name']) ?></span>
      <h2>Join free. Your library starts here.</h2>
      <ul>
        <li><i class="bi bi-collection-play"></i> Unlimited songs and videos</li>
        <li><i class="bi bi-music-note-list"></i> Playlists that stay on your account</li>
        <li><i class="bi bi-chat-heart"></i> Reviews and ratings for what you love</li>
      </ul>
    </aside>
    <div class="auth-card wide">
      <span class="kicker">Join <?= h($SITE['name']) ?></span>
      <h1>Create your account</h1>
      <p class="text-secondary">Register once to build playlists and review music and videos.</p>
      <?php if ($error): ?><div class="alert sg-alert alert-danger" role="alert"><?= h($error) ?></div><?php endif; ?>
      <form method="post" class="row g-3 mt-1">
        <?= csrf_field() ?>
      <div class="col-md-6"><label class="form-label" for="rgUser">Unique user ID *</label><div class="pm-field"><i class="bi bi-at"></i><input class="form-control" id="rgUser" type="text" name="username" value="<?= h($v['username']) ?>" maxlength="50" required></div></div>
      <div class="col-md-6"><label class="form-label" for="rgName">Full name *</label><div class="pm-field"><i class="bi bi-person"></i><input class="form-control" id="rgName" type="text" name="full_name" value="<?= h($v['full_name']) ?>" required></div></div>
      <div class="col-md-6"><label class="form-label" for="rgMail">Email *</label><div class="pm-field"><i class="bi bi-envelope"></i><input class="form-control" id="rgMail" type="email" name="email" value="<?= h($v['email']) ?>" required></div></div>
      <div class="col-md-6"><label class="form-label" for="rgPhone">Phone *</label><div class="pm-field"><i class="bi bi-telephone"></i><input class="form-control" id="rgPhone" type="text" name="phone" value="<?= h($v['phone']) ?>" required></div></div>
      <div class="col-12"><label class="form-label" for="rgAddr">Address *</label><div class="pm-field"><i class="bi bi-geo-alt"></i><input class="form-control" id="rgAddr" type="text" name="address" value="<?= h($v['address']) ?>" required></div></div>
      <div class="col-12"><label class="form-label" for="rgPass">Password * (8+ characters)</label><div class="pm-field has-eye"><i class="bi bi-lock"></i><input class="form-control" id="rgPass" type="password" name="password" minlength="8" required autocomplete="new-password"><button type="button" class="pm-eye" data-pm-toggle="rgPass" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div></div>
      <div class="col-12"><button class="btn btn-sg w-100">Create account</button></div>
      </form>
      <p class="text-secondary mt-4 mb-0">Already a member? <a href="login.php">Log in</a></p>
    </div>
  </div>
</section>
<?php require 'footer.php'; ?>
