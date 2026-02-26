<?php $pageTitle = 'Invoice ' . $sale['invoice_number']; ?>

<div class="no-print" style="margin-bottom:16px;display:flex;gap:8px;align-items:center">
  <a href="<?= APP_URL ?>/sales" class="btn btn-ghost">← Back to Sales</a>
  <button onclick="App.printInvoice()" class="btn btn-primary">🖨️ Print Invoice</button>
  <?php if ($sale['balance_due'] > 0): ?>
  <button class="btn btn-success" data-modal="addPaymentModal">💰 Add Payment</button>
  <?php endif; ?>
</div>

<div class="card" id="invoice-print" style="max-width:800px;margin:0 auto">
  <!-- Invoice Header -->
  <div style="padding:30px;border-bottom:3px solid var(--primary)">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <div style="font-size:28px;font-weight:800;color:var(--primary)">🛵 <?= h($settings['company_name'] ?? 'Simple Scooters') ?></div>
        <div style="color:var(--text-muted);margin-top:4px;font-size:13px">
          <?= h($settings['company_address'] ?? '') ?><br>
          📞 <?= h($settings['company_phone'] ?? '') ?> | 📧 <?= h($settings['company_email'] ?? '') ?>
          <?php if (!empty($settings['company_gstin'])): ?>
          <br>GSTIN: <?= h($settings['company_gstin']) ?>
          <?php endif; ?>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:22px;font-weight:800">INVOICE</div>
        <div style="font-size:18px;color:var(--primary);font-weight:700"><?= h($sale['invoice_number']) ?></div>
        <div class="text-muted" style="font-size:13px">Date: <?= formatDate($sale['sale_date']) ?></div>
        <div style="margin-top:8px">
          <?php $bs = ['paid'=>'badge-success','partial'=>'badge-warning','pending'=>'badge-danger']; ?>
          <span class="badge <?= $bs[$sale['payment_status']] ?>" style="font-size:13px;padding:6px 14px"><?= strtoupper($sale['payment_status']) ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Customer & Vehicle Info -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:24px;border-bottom:1px solid var(--border)">
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Bill To</div>
      <div style="font-weight:700;font-size:16px"><?= h($sale['customer_name']) ?></div>
      <div><?= h($sale['customer_phone']) ?></div>
      <?php if ($sale['customer_email']): ?><div><?= h($sale['customer_email']) ?></div><?php endif; ?>
      <?php if ($sale['customer_address']): ?><div class="text-muted"><?= h($sale['customer_address']) ?></div><?php endif; ?>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Vehicle Details</div>
      <div style="font-weight:700;font-size:16px"><?= h($sale['model_name']) ?></div>
      <div>Color: <?= h($sale['color']) ?></div>
      <div class="text-muted">Qty: <?= $sale['quantity'] ?> unit(s)</div>
      <?php if ($sale['warranty_expiry']): ?>
      <div class="text-muted">Warranty until: <?= formatDate($sale['warranty_expiry']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Invoice Table -->
  <div style="padding:24px">
    <table class="table" style="border:1px solid var(--border);border-radius:8px;overflow:hidden">
      <thead>
        <tr>
          <th style="background:var(--primary);color:#fff;text-align:left">Description</th>
          <th style="background:var(--primary);color:#fff;text-align:right">Qty</th>
          <th style="background:var(--primary);color:#fff;text-align:right">Unit Price</th>
          <th style="background:var(--primary);color:#fff;text-align:right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong><?= h($sale['model_name']) ?></strong> — <?= h($sale['color']) ?>
            <?php if ($sale['is_emi']): ?><br><small class="text-muted">EMI: <?= $sale['emi_months'] ?> months @ <?= formatCurrency($sale['emi_amount']) ?>/month</small><?php endif; ?>
          </td>
          <td style="text-align:right"><?= $sale['quantity'] ?></td>
          <td style="text-align:right"><?= formatCurrency($sale['unit_price']) ?></td>
          <td style="text-align:right;font-weight:600"><?= formatCurrency($sale['unit_price'] * $sale['quantity']) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <?php if ($sale['discount'] > 0): ?>
        <tr><td colspan="3" style="text-align:right;color:var(--text-muted)">Discount</td><td style="text-align:right;color:var(--danger)">— <?= formatCurrency($sale['discount']) ?></td></tr>
        <?php endif; ?>
        <?php if ($sale['tax_amount'] > 0): ?>
        <tr><td colspan="3" style="text-align:right;color:var(--text-muted)">Tax (<?= $sale['tax_percent'] ?>%)</td><td style="text-align:right"><?= formatCurrency($sale['tax_amount']) ?></td></tr>
        <?php endif; ?>
        <tr style="background:var(--bg-main)">
          <td colspan="3" style="text-align:right;font-weight:700;font-size:15px">TOTAL</td>
          <td style="text-align:right;font-weight:800;font-size:18px;color:var(--primary)"><?= formatCurrency($sale['total_amount']) ?></td>
        </tr>
        <tr>
          <td colspan="3" style="text-align:right;color:var(--secondary)">Amount Paid (<?= ucfirst($sale['payment_mode']) ?>)</td>
          <td style="text-align:right;color:var(--secondary);font-weight:600"><?= formatCurrency($sale['amount_paid']) ?></td>
        </tr>
        <?php if ($sale['balance_due'] > 0): ?>
        <tr>
          <td colspan="3" style="text-align:right;color:var(--danger);font-weight:700">Balance Due</td>
          <td style="text-align:right;color:var(--danger);font-weight:800"><?= formatCurrency($sale['balance_due']) ?></td>
        </tr>
        <?php endif; ?>
      </tfoot>
    </table>

    <!-- Payment History -->
    <?php if (!empty($sale['payments'])): ?>
    <div style="margin-top:20px">
      <h4 style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Payment History</h4>
      <table style="width:100%;font-size:13px;border-collapse:collapse">
        <tr style="border-bottom:1px solid var(--border)">
          <th style="text-align:left;padding:6px;color:var(--text-muted)">Date</th>
          <th style="text-align:left;padding:6px;color:var(--text-muted)">Mode</th>
          <th style="text-align:left;padding:6px;color:var(--text-muted)">Ref ID</th>
          <th style="text-align:right;padding:6px;color:var(--text-muted)">Amount</th>
        </tr>
        <?php foreach ($sale['payments'] as $p): ?>
        <tr>
          <td style="padding:6px"><?= formatDate($p['payment_date']) ?></td>
          <td style="padding:6px"><?= ucfirst($p['payment_mode']) ?></td>
          <td style="padding:6px"><?= $p['transaction_id'] ? h($p['transaction_id']) : '—' ?></td>
          <td style="padding:6px;text-align:right;font-weight:600;color:var(--secondary)"><?= formatCurrency($p['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if ($sale['notes']): ?>
    <div style="margin-top:20px;padding:14px;background:var(--bg-main);border-radius:8px;font-size:13px">
      <strong>Notes:</strong> <?= h($sale['notes']) ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div style="margin-top:30px;padding-top:20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--text-muted)">
      <div>Thank you for choosing <?= h($settings['company_name'] ?? 'Simple Scooters') ?>!</div>
      <div>Operator: <?= h($sale['operator_name'] ?? '—') ?></div>
    </div>
  </div>
</div>

<!-- Add Payment Modal -->
<div class="modal-overlay" id="addPaymentModal">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 class="modal-title">💰 Record Payment</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/sales/<?= $sale['id'] ?>/payment" method="POST">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Balance Due: <strong><?= formatCurrency($sale['balance_due']) ?></strong></label></div>
        <div class="form-group"><label class="form-label">Amount *</label><input type="number" name="amount" class="form-control" step="0.01" value="<?= $sale['balance_due'] ?>" required></div>
        <div class="form-group"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label class="form-label">Mode</label>
          <select name="payment_mode" class="form-control">
            <option value="cash">💵 Cash</option>
            <option value="upi">📱 UPI</option>
            <option value="card">💳 Card</option>
            <option value="bank_transfer">🏦 Bank Transfer</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Transaction ID</label><input type="text" name="transaction_id" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-success">💾 Record</button>
      </div>
    </form>
  </div>
</div>
