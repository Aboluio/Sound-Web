<?php
require 'config.php';

/* First run: no administrator yet -> go create one */
if ((int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() === 0) { header('Location: setup.php'); exit; }
if (is_admin()) { header('Location: index.php'); exit; }

$error = '';
if (logged_in() && !is_admin()) $error = 'You are signed in as a normal user. Sign in with an administrator account to continue.';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT * FROM users WHERE username=? OR email=? LIMIT 1'); $st->execute([$u, $u]);
    $user = $st->fetch();
    if ($user && password_verify($p, $user['password'])) {
        if ($user['role'] !== 'admin') { $error = 'This account is not an administrator.'; }
        else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $user['role'], 'full_name' => $user['full_name']];
            header('Location: index.php'); exit;
        }
    } else $error = 'Invalid username/email or password.';
}
$authTitle = 'Admin Login';
require 'auth_head.php';
?>
    <p class="login-subtitle">Please sign in to access the admin dashboard</p>
    <?php if ($error): ?><div class="alert-custom alert-custom-danger sg-alert-inline"><i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i><div class="alert-custom-content"><?= h($error) ?></div></div><?php endif; ?>
    <form method="post" id="loginForm">
      <?= csrf_field() ?>
      <div class="login-form-group">
        <label for="username" class="login-form-label">Email</label>
        <div class="login-input-group">
          <i class="bi bi-envelope input-icon"></i>
          <input type="email" id="username" name="username" class="login-input" placeholder="name@email.com" required autofocus autocomplete="email">
        </div>
      </div>
      <div class="login-form-group">
        <label for="password" class="login-form-label">Password</label>
        <div class="login-input-group">
          <i class="bi bi-shield-lock input-icon"></i>
          <input type="password" id="password" name="password" class="login-input login-input-password" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-login" id="btn-submit"><span>Sign In to Dashboard</span><i class="bi bi-arrow-right"></i></button>
    </form>
    <p class="login-footer-text"><a href="../index.php">&larr; Back to home</a></p>
<?php require 'auth_foot.php'; ?>
