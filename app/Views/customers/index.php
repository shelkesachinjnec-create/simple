<?php $pageTitle = 'Customers'; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">👥 Customer Management</h3>
  </div>
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--bg-main)">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Search name, phone, email..." value="<?= h($filters['search']) ?>">
      </div>
      <select name="kyc_verified" class="form-control" style="width:150px" data-auto-submit>
        <option value="">All KYC</option>
        <option value="1" <?= $filters['kyc_verified']==='1'?'selected':'' ?>>✅ KYC Verified</option>
        <option value="0" <?= $filters['kyc_verified']==='0'?'selected':'' ?>>⏳ KYC Pending</option>
      </select>
      <button type="submit" class="btn btn-primary">🔍</button>
      <a href="<?= APP_URL ?>/customers" class="btn btn-ghost">↺</a>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th>#</th><th>Customer</th><th>Contact</th><th>City</th><th>KYC</th><th>Purchases</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👥</div><h4>No customers yet</h4><p>Customers are added when leads are converted</p></div></td></tr>
        <?php else: ?>
        <?php foreach ($result['data'] as $c): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td>
            <div style="font-weight:600"><?= h($c['name']) ?></div>
            <?php if ($c['aadhar_number']): ?><div class="text-muted text-small">Aadhar: <?= h(substr($c['aadhar_number'],0,4).'XXXX'.substr($c['aadhar_number'],-4)) ?></div><?php endif; ?>
          </td>
          <td>
            <div><?= h($c['phone']) ?></div>
            <?php if ($c['email']): ?><div class="text-muted text-small"><?= h($c['email']) ?></div><?php endif; ?>
          </td>
          <td><?= $c['city'] ? h($c['city']) : '—' ?></td>
          <td>
            <?php if ($c['kyc_verified']): ?>
              <span class="badge badge-success">✅ Verified</span>
            <?php else: ?>
              <span class="badge badge-warning">⏳ Pending</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-primary"><?= $c['total_purchases'] ?> purchase<?= $c['total_purchases']!=1?'s':'' ?></span></td>
          <td><?= formatDate($c['created_at']) ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="<?= APP_URL ?>/customers/<?= $c['id'] ?>" class="btn btn-primary btn-sm">👁️</a>
              <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($c['phone']) ?>')">💬</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['last_page'] > 1): ?>
  <div style="padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div class="text-muted text-small">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> customers</div>
    <div class="pagination">
      <?php for ($p = 1; $p <= $result['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&<?= http_build_query($filters) ?>" class="page-link <?= $p===$result['current_page']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
