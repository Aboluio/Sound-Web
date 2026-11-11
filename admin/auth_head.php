<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($authTitle) ?> | Sound Web Admin</title>
  <link rel="icon" type="image/png" href="<?= BASE ?>/assets/images/favicon.ico">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE ?>/assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="<?= BASE ?>/assets/css/main.css?v=<?= @filemtime(__DIR__ . '/../assets/css/main.css') ?>">
  <link rel="stylesheet" href="<?= BASE ?>/admin/admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?>">
<script>try{document.documentElement.setAttribute("data-theme",localStorage.getItem("sgTheme")||"dark")}catch(e){document.documentElement.setAttribute("data-theme","dark")}</script>
</head>
<body>
<button type="button" id="btn-theme" class="sg-theme-float" aria-label="Toggle dark / light mode" title="Dark / Light mode"><i class="bi bi-moon-stars-fill"></i></button>
<div class="login-wrapper">
  <div class="login-bg-shape login-bg-shape-1"></div>
  <div class="login-bg-shape login-bg-shape-2"></div>
  <div class="login-card">
    <a href="login.php" class="login-brand text-decoration-none"><img src="<?= BASE ?>/assets/images/soundweb-logo-sm.png" alt="" class="sw-logo"><span>Sound Web</span></a>
