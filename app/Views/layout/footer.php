  </div><!-- end page-body -->
</main><!-- end main-content -->

<!-- User Menu Modal -->
<div class="modal-overlay" id="userMenuModal">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 class="modal-title">My Account</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <div class="modal-body">
      <div style="text-align:center; padding: 8px 0 20px">
        <div class="user-avatar" style="width:60px;height:60px;font-size:22px;margin:0 auto 10px"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
        <div style="font-weight:700;font-size:16px"><?= h($user['name']) ?></div>
        <div class="text-muted text-small"><?= h($user['email']) ?></div>
        <span class="badge badge-primary" style="margin-top:6px"><?= str_replace('_', ' ', ucfirst($user['role'])) ?></span>
      </div>
      <hr style="border-color:var(--border); margin-bottom:16px">
      <a href="<?= APP_URL ?>/auth/logout" class="btn btn-danger w-100" style="justify-content:center">🚪 Logout</a>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= APP_URL ?>/js/app.js"></script>
<?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>
</body>
</html>
