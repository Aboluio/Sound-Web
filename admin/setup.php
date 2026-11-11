<?php
// One-time creation of the first administrator. Disabled as soon as any admin exists.
require 'config.php';
if ((int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0) { header('Location: login.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? ''); $e = trim($_POST['email'] ?? ''); $n = trim($_POST['full_name'] ?? ''); $p = $_POST['password'] ?? '';
    if (!$u || !filter_var($e, FILTER_VALIDATE_EMAIL) || !$n || strlen($p) < 8) $error = 'Fill all fields. Password must be at least 8 characters.';
    else {
        try {
            $pdo->prepare("INSERT INTO users(username,email,full_name,address,phone,password,role) VALUES(?,?,?,?,?,?, 'admin')")
                ->execute([$u, $e, $n, 'Admin Office', '-', password_hash($p, PASSWORD_DEFAULT)]);
            header('Location: login.php'); exit;
        } catch (PDOException $x) { $error = 'That username or email is already used. Choose another.'; }
    }
}
$authTitle = 'Create Administrator';
require 'auth_head.php';
?>
    <p class="login-subtitle">Create the first administrator. This page stops working once an admin exists.</p>
    <?php if ($error): ?><div class="alert-custom alert-custom-danger sg-alert-inline"><i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i><div class="alert-custom-content"><?= h($error) ?></div></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="login-form-group">
        <label for="full_name" class="login-form-label">Full name</label>
        <div class="login-input-group"><i class="bi bi-person-badge input-icon"></i><input type="text" id="full_name" name="full_name" class="login-input" required></div>
      </div>
      <div class="login-form-group">
        <label for="username" class="login-form-label">Username</label>
        <div class="login-input-group"><i class="bi bi-person input-icon"></i><input type="text" id="username" name="username" class="login-input" required autocomplete="username"></div>
      </div>
      <div class="login-form-group">
        <label for="email" class="login-form-label">Email</label>
        <div class="login-input-group"><i class="bi bi-envelope input-icon"></i><input type="email" id="email" name="email" class="login-input" required autocomplete="email"></div>
      </div>
      <div class="login-form-group">
        <label for="password" class="login-form-label">Password (8+ characters)</label>
        <div class="login-input-group">
          <i class="bi bi-shield-lock input-icon"></i>
          <input type="password" id="password" name="password" class="login-input login-input-password" minlength="8" required autocomplete="new-password">
          <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-login"><span>Create administrator</span><i class="bi bi-arrow-right"></i></button>
    </form>
<?php require 'auth_foot.php'; ?>
