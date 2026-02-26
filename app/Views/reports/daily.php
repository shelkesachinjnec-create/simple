<?php $pageTitle = 'Daily Report — ' . formatDate($date); ?>

<div class="no-print" style="margin-bottom:16px">
  <form method="GET" style="display:flex;gap:10px;align-items:center">
    <input type="date" name="date" class="form-control" value="<?= h($date) ?>" style="width:160px">
    <button type="submit" class="btn btn-primary">📅 Load Report</button>
    <button onclick="window.print()" class="btn btn-ghost">🖨️ Print</button>
  </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-4" style="margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-icon" style="background:#e8f0fe">🧾</div>
    <div class="stat-info">
      <div class="stat-label">Sales Today</div>
      <div class="stat-value"><?= count($sales) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fff8e1">💰</div>
    <div class="stat-info">
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value" style="font-size:18px"><?= formatCurrency($totalRevenue) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#e6f9f0">✅</div>
    <div class="stat-info">
      <div class="stat-label">Collected</div>
      <div class="stat-value" style="font-size:18px"><?= formatCurrency($totalCollected) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fdecea">📋</div>
    <div class="stat-info">
      <div class="stat-label">Transactions</div>
      <div class="stat-value"><?= count($payments) ?></div>
    </div>
  </div>
</div>

<!-- Sales Table -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h3 class="card-title">🧾 Sales on <?= formatDate($date) ?></h3></div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Invoice</th><th>Customer</th><th>Model</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($sales)): ?>
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🧾</div><p>No sales on this date</p></div></td></tr>
        <?php else: ?>
        <?php foreach ($sales as $s): ?>
        <tr>
          <td><a href="<?= APP_URL ?>/sales/invoice/<?= $s['id'] ?>" style="color:var(--primary)"><?= h($s['invoice_number']) ?></a></td>
          <td><?= h($s['customer_name']) ?></td>
          <td><?= h($s['model_name']) ?></td>
          <td style="font-weight:600"><?= formatCurrency($s['total_amount']) ?></td>
          <td style="color:var(--secondary)"><?= formatCurrency($s['amount_paid']) ?></td>
          <td style="color:<?= $s['balance_due']>0?'var(--danger)':'var(--secondary)' ?>"><?= formatCurrency($s['balance_due']) ?></td>
          <td><?php $b=['paid'=>'badge-success','partial'=>'badge-warning','pending'=>'badge-danger'];?><span class="badge <?= $b[$s['payment_status']] ?>"><?= ucfirst($s['payment_status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Collections -->
<div class="card">
  <div class="card-header"><h3 class="card-title">💰 Collections on <?= formatDate($date) ?></h3></div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Invoice</th><th>Customer</th><th>Mode</th><th>Txn ID</th><th>Amount</th><th>Received By</th></tr></thead>
      <tbody>
        <?php if (empty($payments)): ?>
        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">💰</div><p>No payments on this date</p></div></td></tr>
        <?php else: ?>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= h($p['invoice_number']) ?></td>
          <td><?= h($p['customer_name']) ?></td>
          <td><?= ucfirst($p['payment_mode']) ?></td>
          <td><?= $p['transaction_id'] ? h($p['transaction_id']) : '—' ?></td>
          <td style="font-weight:600;color:var(--secondary)"><?= formatCurrency($p['amount']) ?></td>
          <td><?= h($p['received_by_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
