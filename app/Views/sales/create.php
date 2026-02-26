<?php $pageTitle = 'New Sale'; ?>

<div class="grid grid-2">
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">🧾 Create New Sale</h3>
      </div>
      <form action="<?= APP_URL ?>/sales/store" method="POST">
        <?= Auth::csrfField() ?>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Customer *</label>
            <select name="customer_id" class="form-control" required>
              <option value="">Select customer</option>
              <?php
              $preselect = (int)($_GET['customer_id'] ?? 0);
              foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $c['id']==$preselect?'selected':'' ?>><?= h($c['name']) ?> — <?= h($c['phone']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Scooter Model *</label>
            <select name="inventory_id" class="form-control" id="inventory_id" required>
              <option value="">Select model</option>
              <?php foreach ($inventory as $m): ?>
              <option value="<?= $m['id'] ?>" data-price="<?= $m['selling_price'] ?>"><?= h($m['model_name']) ?> — <?= h($m['color']) ?> (Stock: <?= $m['stock_quantity'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Sale Date</label>
              <input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Unit Price (₹)</label>
              <input type="number" name="unit_price" id="unit_price" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
              <label class="form-label">Discount (₹)</label>
              <input type="number" name="discount" id="discount" class="form-control" step="0.01" value="0">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tax (%)</label>
              <input type="number" name="tax_percent" id="tax_percent" class="form-control" step="0.01" value="0">
            </div>
            <div class="form-group">
              <label class="form-label">Payment Mode</label>
              <select name="payment_mode" class="form-control">
                <option value="cash">💵 Cash</option>
                <option value="upi">📱 UPI</option>
                <option value="card">💳 Card</option>
                <option value="bank_transfer">🏦 Bank Transfer</option>
                <option value="emi">📋 EMI</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Amount Paid (₹)</label>
            <input type="number" name="amount_paid" id="amount_paid" class="form-control" step="0.01" value="0">
          </div>
          <div class="form-group">
            <label class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" placeholder="UPI/Bank reference">
          </div>
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="is_emi" id="is_emi" onchange="document.getElementById('emiFields').style.display=this.checked?'block':'none'">
              <span>📋 EMI Payment Plan</span>
            </label>
          </div>
          <div id="emiFields" style="display:none;background:var(--bg-main);padding:14px;border-radius:8px;margin-top:4px">
            <div class="form-group">
              <label class="form-label">EMI Duration (months)</label>
              <input type="number" name="emi_months" id="emi_months" class="form-control" placeholder="12">
            </div>
          </div>
          <input type="hidden" name="tax_amount" id="tax_amount" value="0">
          <input type="hidden" name="total_amount" id="total_amount" value="0">
          <input type="hidden" name="balance_due" id="balance_due" value="0">
          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" placeholder="Any additional notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 20px">
          <a href="<?= APP_URL ?>/sales" class="btn btn-ghost">Cancel</a>
          <button type="submit" class="btn btn-primary btn-lg">🧾 Create Sale & Invoice</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Live Calculator -->
  <div>
    <div class="card" style="position:sticky;top:80px">
      <div class="card-header"><h3 class="card-title">🧮 Sale Summary</h3></div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px">
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <span class="text-muted">Subtotal</span>
            <span id="display_subtotal" style="font-weight:600">₹0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <span class="text-muted">Tax</span>
            <span id="display_tax" style="font-weight:600">₹0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:14px;background:var(--primary-light);border-radius:8px">
            <span style="font-size:16px;font-weight:700;color:var(--primary)">Total</span>
            <span id="display_total" style="font-size:18px;font-weight:800;color:var(--primary)">₹0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <span class="text-muted">Amount Paid</span>
            <span id="display_paid" style="font-weight:600;color:var(--secondary)">₹0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:14px;background:#fdecea;border-radius:8px">
            <span style="font-weight:700;color:var(--danger)">Balance Due</span>
            <span id="display_balance" style="font-size:16px;font-weight:800;color:var(--danger)">₹0.00</span>
          </div>
        </div>
        <hr style="border-color:var(--border);margin:16px 0">
        <div class="text-muted text-small" style="line-height:1.6">
          ✅ Invoice will be generated automatically<br>
          📧 Customer notification email will be sent<br>
          📦 Stock will be reduced automatically
        </div>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>document.addEventListener("DOMContentLoaded", () => { App.initSaleCalculator(); document.getElementById("amount_paid").addEventListener("input", () => { const p = parseFloat(document.getElementById("amount_paid").value||0); document.getElementById("display_paid").textContent = App.formatCurrency(p); }); });</script>'; ?>
