<?php $pageTitle = 'Customer: ' . $customer['name']; ?>

<div class="grid grid-2">
  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <h3 class="card-title">👤 Customer Profile</h3>
        <button class="btn btn-outline btn-sm" data-modal="editCustomerModal">✏️ Edit</button>
      </div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div><div class="text-muted text-small">Name</div><div style="font-weight:600"><?= h($customer['name']) ?></div></div>
          <div><div class="text-muted text-small">Phone</div>
            <div style="font-weight:600"><?= h($customer['phone']) ?>
              <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($customer['phone']) ?>')" style="margin-left:6px">💬</button>
            </div>
          </div>
          <div><div class="text-muted text-small">Email</div><div><?= $customer['email'] ? h($customer['email']) : '—' ?></div></div>
          <div><div class="text-muted text-small">Date of Birth</div><div><?= $customer['date_of_birth'] ? formatDate($customer['date_of_birth']) : '—' ?></div></div>
          <div><div class="text-muted text-small">Aadhar</div><div><?= $customer['aadhar_number'] ? h($customer['aadhar_number']) : '—' ?></div></div>
          <div><div class="text-muted text-small">PAN</div><div><?= $customer['pan_number'] ? h($customer['pan_number']) : '—' ?></div></div>
          <div><div class="text-muted text-small">City / State</div><div><?= implode(', ', array_filter([$customer['city']??'', $customer['state']??''])) ?: '—' ?></div></div>
          <div><div class="text-muted text-small">KYC Status</div>
            <span class="badge <?= $customer['kyc_verified']?'badge-success':'badge-warning' ?>"><?= $customer['kyc_verified']?'✅ Verified':'⏳ Pending' ?></span>
          </div>
        </div>
        <?php if ($customer['address']): ?>
        <hr style="border-color:var(--border);margin:14px 0">
        <div class="text-muted text-small">Address</div>
        <div><?= h($customer['address']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Documents -->
    <?php if ($customer['document_aadhar'] || $customer['document_pan'] || $customer['document_photo']): ?>
    <div class="card">
      <div class="card-header"><h3 class="card-title">📄 KYC Documents</h3></div>
      <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap">
        <?php foreach (['document_aadhar'=>'Aadhar','document_pan'=>'PAN','document_photo'=>'Photo'] as $field=>$label): ?>
          <?php if ($customer[$field]): ?>
          <div style="text-align:center">
            <?php $ext = pathinfo($customer[$field], PATHINFO_EXTENSION); ?>
            <?php if (in_array(strtolower($ext), ['jpg','jpeg','png','webp'])): ?>
            <img src="<?= APP_URL ?>/uploads/<?= h($customer[$field]) ?>" style="width:100px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
            <?php else: ?>
            <a href="<?= APP_URL ?>/uploads/<?= h($customer[$field]) ?>" target="_blank" class="btn btn-ghost">📄 <?= $label ?></a>
            <?php endif; ?>
            <div class="text-muted text-small" style="margin-top:4px"><?= $label ?></div>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sales History -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">🧾 Purchase History</h3>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= APP_URL ?>/sales/create?customer_id=<?= $customer['id'] ?>" class="btn btn-primary btn-sm">➕ New Sale</a>
        <?php endif; ?>
      </div>
      <?php if (empty($sales)): ?>
      <div class="empty-state" style="padding:40px"><div class="empty-icon">🛵</div><p>No purchases yet</p></div>
      <?php else: ?>
      <?php foreach ($sales as $s): ?>
      <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-weight:700"><?= h($s['invoice_number']) ?></div>
            <div style="font-weight:600;margin-top:2px"><?= h($s['model_name']) ?> — <?= h($s['color']) ?></div>
            <div class="text-muted text-small"><?= formatDate($s['sale_date']) ?></div>
          </div>
          <div style="text-align:right">
            <div style="font-weight:700;color:var(--primary)"><?= formatCurrency($s['total_amount']) ?></div>
            <span class="badge <?= $s['payment_status']==='paid'?'badge-success':($s['payment_status']==='partial'?'badge-warning':'badge-danger') ?>"><?= ucfirst($s['payment_status']) ?></span>
          </div>
        </div>
        <?php if ($s['balance_due'] > 0): ?>
        <div style="margin-top:8px;font-size:12px;color:var(--danger)">Balance due: <?= formatCurrency($s['balance_due']) ?></div>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/sales/invoice/<?= $s['id'] ?>" class="btn btn-ghost btn-sm" style="margin-top:8px">🧾 View Invoice</a>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal-overlay" id="editCustomerModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 class="modal-title">✏️ Edit Customer</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/customers/update/<?= $customer['id'] ?>" method="POST" enctype="multipart/form-data">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= h($customer['name']) ?>" required></div>
          <div class="form-group"><label class="form-label">Phone *</label><input type="tel" name="phone" class="form-control" value="<?= h($customer['phone']) ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= h($customer['email']) ?>"></div>
          <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= $customer['date_of_birth'] ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control"><?= h($customer['address']) ?></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= h($customer['city']) ?>"></div>
          <div class="form-group"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= h($customer['state']) ?>"></div>
          <div class="form-group"><label class="form-label">Pincode</label><input type="text" name="pincode" class="form-control" value="<?= h($customer['pincode']) ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Aadhar Number</label><input type="text" name="aadhar_number" class="form-control" value="<?= h($customer['aadhar_number']) ?>" maxlength="12"></div>
          <div class="form-group"><label class="form-label">PAN Number</label><input type="text" name="pan_number" class="form-control" value="<?= h($customer['pan_number']) ?>" maxlength="10"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Upload Aadhar</label><input type="file" name="document_aadhar" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
          <div class="form-group"><label class="form-label">Upload PAN</label><input type="file" name="document_pan" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
          <div class="form-group"><label class="form-label">Upload Photo</label><input type="file" name="document_photo" class="form-control" accept=".jpg,.jpeg,.png"></div>
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="kyc_verified" <?= $customer['kyc_verified']?'checked':'' ?>>
            <span>Mark KYC as Verified</span>
          </label>
        </div>
        <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control"><?= h($customer['notes']) ?></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>
