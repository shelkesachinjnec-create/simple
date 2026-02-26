<?php $pageTitle = 'Sales'; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">🧾 Sales Management</h3>
    <a href="<?= APP_URL ?>/sales/create" class="btn btn-primary">➕ New Sale</a>
  </div>
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--bg-main)">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Invoice, customer..." value="<?= h($filters['search']) ?>">
      </div>
      <select name="payment_status" class="form-control" style="width:150px" data-auto-submit>
        <option value="">All Payments</option>
        <option value="paid" <?= $filters['payment_status']==='paid'?'selected':'' ?>>✅ Paid</option>
        <option value="partial" <?= $filters['payment_status']==='partial'?'selected':'' ?>>🟡 Partial</option>
        <option value="pending" <?= $filters['payment_status']==='pending'?'selected':'' ?>>⏳ Pending</option>
      </select>
      <input type="date" name="date_from" class="form-control" style="width:145px" value="<?= h($filters['date_from']) ?>">
      <input type="date" name="date_to" class="form-control" style="width:145px" value="<?= h($filters['date_to']) ?>">
      <button type="submit" class="btn btn-primary">🔍</button>
      <a href="<?= APP_URL ?>/sales" class="btn btn-ghost">↺</a>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th>Invoice</th><th>Customer</th><th>Scooter</th><th>Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">🧾</div><h4>No sales yet</h4></div></td></tr>
        <?php else: ?>
        <?php foreach ($result['data'] as $s): ?>
        <tr>
          <td><strong><?= h($s['invoice_number']) ?></strong></td>
          <td>
            <div style="font-weight:600"><?= h($s['customer_name']) ?></div>
            <div class="text-muted text-small"><?= h($s['customer_phone']) ?></div>
          </td>
          <td><?= h($s['model_name']) ?> <span class="text-muted">— <?= h($s['color']) ?></span></td>
          <td><?= formatDate($s['sale_date']) ?></td>
          <td style="font-weight:700"><?= formatCurrency($s['total_amount']) ?></td>
          <td style="color:var(--secondary)"><?= formatCurrency($s['amount_paid']) ?></td>
          <td style="color:<?= $s['balance_due']>0?'var(--danger)':'var(--secondary)' ?>;font-weight:<?= $s['balance_due']>0?'700':'400' ?>"><?= formatCurrency($s['balance_due']) ?></td>
          <td><?php $badges = ['paid'=>'badge-success','partial'=>'badge-warning','pending'=>'badge-danger'];?>
            <span class="badge <?= $badges[$s['payment_status']] ?>"><?= ucfirst($s['payment_status']) ?></span>
          </td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="<?= APP_URL ?>/sales/invoice/<?= $s['id'] ?>" class="btn btn-primary btn-sm">🧾</a>
              <?php if ($s['balance_due'] > 0): ?>
              <button class="btn btn-success btn-sm" data-modal="addPaymentModal" data-id="<?= $s['id'] ?>">💰</button>
              <?php endif; ?>
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
    <div class="text-muted text-small">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> sales</div>
    <div class="pagination">
      <?php for ($p = 1; $p <= $result['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&<?= http_build_query($filters) ?>" class="page-link <?= $p===$result['current_page']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="addPaymentModal">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 class="modal-title">💰 Record Payment</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form method="POST" data-action-template="<?= APP_URL ?>/sales/{id}/payment">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Amount *</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
        <div class="form-group"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label class="form-label">Payment Mode</label>
          <select name="payment_mode" class="form-control">
            <option value="cash">💵 Cash</option>
            <option value="upi">📱 UPI</option>
            <option value="card">💳 Card</option>
            <option value="bank_transfer">🏦 Bank Transfer</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Transaction ID</label><input type="text" name="transaction_id" class="form-control" placeholder="Optional"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-success">💾 Record Payment</button>
      </div>
    </form>
  </div>
</div>
