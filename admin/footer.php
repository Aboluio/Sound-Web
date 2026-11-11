    <footer class="footer-custom">
      <div class="footer-left">
        <span class="footer-logo"><img src="<?= BASE ?>/assets/images/soundweb-logo-sm.png" alt="" class="sw-logo"> Sound Web</span>
        <span class="footer-separator">|</span>
        <span class="footer-copy">&copy; <?= date('Y') ?> Sound Web Admin &bull; Powered by <a href="https://themewagon.com/themes/spark-admin/" target="_blank" rel="noopener">Spark Admin</a></span>
      </div>
      <div class="footer-right">
        <ul class="footer-links">
          <li><a href="index.php" class="footer-link">Overview</a></li>
          <li><a href="media.php" class="footer-link">Library</a></li>
          <li><a href="../user/index.php" class="footer-link">Website</a></li>
          <li><a href="../check.php" class="footer-link">Status <span class="status-dot"></span></a></li>
        </ul>
      </div>
    </footer>

  </div>

  <script src="<?= BASE ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <?php if (!empty($useCharts)): ?>
  <script src="<?= BASE ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
  <?php endif; ?>
  <?php if (!empty($pageData)): ?>
  <script>window.SG = <?= json_encode($pageData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
  <?php endif; ?>
  <script src="<?= BASE ?>/admin/admin.js?v=<?= @filemtime(__DIR__ . '/admin.js') ?>"></script>
</body>
</html>
