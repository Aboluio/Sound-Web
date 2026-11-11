<?php
require 'config.php';
if (logged_in()) { header('Location: ' . (is_admin() ? '../admin/' : 'index.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT * FROM users WHERE username=? OR email=? LIMIT 1');
    $st->execute([$u, $u]);
    $user = $st->fetch();
    if ($user && password_verify($p, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $user['role'], 'full_name' => $user['full_name']];
        header('Location: ' . ($user['role'] === 'admin' ? '../admin/' : 'index.php'));
        exit;
    }
    $error = 'Invalid username/email or password.';
}
$pageTitle = 'Login'; $active = '';
require 'header.php';
?>
<section class="auth-wrap">
  <div class="pm-auth">
    <aside class="pm-auth-art">
      <span class="pm-auth-badge"><i class="bi bi-soundwave"></i> <?= h($SITE['name']) ?></span>
      <h2>Your music, right where you left it.</h2>
      <ul>
        <li><i class="bi bi-music-note-list"></i> Playlists saved to your account</li>
        <li><i class="bi bi-star"></i> Rate and review songs and videos</li>
        <li><i class="bi bi-headphones"></i> Pick up where you stopped</li>
      </ul>
    </aside>
    <div class="auth-card">
      <span class="kicker"><?= isset($_GET['admin']) ? 'Administrator' : 'Welcome back' ?></span>
      <h1>Log in to <?= h($SITE['name']) ?></h1>
      <p class="text-secondary">Users and administrators log in here with their email and password.</p>
      <?php if ($error): ?><div class="alert sg-alert alert-danger" role="alert"><?= h($error) ?></div><?php endif; ?>
      <?php if (isset($_GET['registered'])): ?><div class="alert sg-alert alert-success" role="status">Account created. Log in to continue.</div><?php endif; ?>
      <form method="post" class="mt-4">
        <?= csrf_field() ?>
        <label class="form-label" for="lgUser">Email (or username)</label>
        <div class="pm-field"><i class="bi bi-person"></i><input class="form-control" id="lgUser" name="username" required autofocus autocomplete="username"></div>
        <label class="form-label mt-3" for="lgPass">Password</label>
        <div class="pm-field has-eye"><i class="bi bi-lock"></i><input class="form-control" id="lgPass" type="password" name="password" required autocomplete="current-password"><button type="button" class="pm-eye" data-pm-toggle="lgPass" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div>
        <button class="btn btn-sg w-100 mt-4">Log in</button>
      </form>
      <p class="text-secondary mt-4 mb-0">New here? <a href="register.php">Create an account</a></p>
    </div>
  </div>
</section>
<?php require 'footer.php'; ?>
