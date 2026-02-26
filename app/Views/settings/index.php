<?php $pageTitle = 'Settings'; ?>

<div class="grid grid-2">
  <!-- General Settings -->
  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><h3 class="card-title">⚙️ General Settings</h3></div>
      <form action="<?= APP_URL ?>/settings/general" method="POST">
        <?= Auth::csrfField() ?>
        <div class="card-body">
          <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="<?= h($general['company_name']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Phone</label><input type="text" name="company_phone" class="form-control" value="<?= h($general['company_phone']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Email</label><input type="email" name="company_email" class="form-control" value="<?= h($general['company_email']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Address</label><textarea name="company_address" class="form-control"><?= h($general['company_address']??'') ?></textarea></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">GSTIN</label><input type="text" name="company_gstin" class="form-control" value="<?= h($general['company_gstin']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Invoice Prefix</label><input type="text" name="invoice_prefix" class="form-control" value="<?= h($general['invoice_prefix']??'SMP') ?>"></div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 20px">
          <button type="submit" class="btn btn-primary">💾 Save General</button>
        </div>
      </form>
    </div>

    <!-- Email Settings -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><h3 class="card-title">📧 Email / SMTP Settings</h3></div>
      <form action="<?= APP_URL ?>/settings/email" method="POST">
        <?= Auth::csrfField() ?>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group"><label class="form-label">SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?= h($email['smtp_host']??'') ?>" placeholder="smtp.gmail.com"></div>
            <div class="form-group"><label class="form-label">SMTP Port</label><input type="number" name="smtp_port" class="form-control" value="<?= h($email['smtp_port']??'587') ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Username</label><input type="text" name="smtp_user" class="form-control" value="<?= h($email['smtp_user']??'') ?>"></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="smtp_pass" class="form-control" placeholder="••••••••"></div>
          </div>
          <div class="form-group"><label class="form-label">From Name</label><input type="text" name="smtp_from_name" class="form-control" value="<?= h($email['smtp_from_name']??'') ?>"></div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 20px">
          <button type="submit" class="btn btn-primary">💾 Save Email</button>
        </div>
      </form>
    </div>

    <!-- Marketing Settings -->
    <div class="card">
      <div class="card-header"><h3 class="card-title">📣 Marketing Integrations</h3></div>
      <form action="<?= APP_URL ?>/settings/marketing" method="POST">
        <?= Auth::csrfField() ?>
        <div class="card-body">
          <div style="font-weight:600;color:var(--primary);margin-bottom:10px">🔵 Facebook</div>
          <div class="form-group"><label class="form-label">Pixel ID</label><input type="text" name="facebook_pixel_id" class="form-control" value="<?= h($marketing['facebook_pixel_id']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Access Token</label><input type="text" name="facebook_access_token" class="form-control" value="<?= h($marketing['facebook_access_token']??'') ?>"></div>
          <hr style="border-color:var(--border);margin:16px 0">
          <div style="font-weight:600;color:#25D366;margin-bottom:10px">🟢 WhatsApp Business</div>
          <div class="form-group"><label class="form-label">API Key</label><input type="text" name="whatsapp_api_key" class="form-control" value="<?= h($marketing['whatsapp_api_key']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Business Phone Number</label><input type="text" name="whatsapp_phone_number" class="form-control" value="<?= h($marketing['whatsapp_phone_number']??'') ?>" placeholder="+919999999999"></div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 20px">
          <button type="submit" class="btn btn-primary">💾 Save Marketing</button>
        </div>
      </form>
    </div>
  </div>

  <!-- User Management -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">👥 User Management</h3>
        <button class="btn btn-primary btn-sm" data-modal="addUserModal">➕ Add User</button>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div style="font-weight:600"><?= h($u['name']) ?></div>
                <?php if ($u['phone']): ?><div class="text-muted text-small"><?= h($u['phone']) ?></div><?php endif; ?>
              </td>
              <td><?= h($u['email']) ?></td>
              <td>
                <?php $rb=['super_admin'=>'badge-danger','admin'=>'badge-primary','operator'=>'badge-secondary']; ?>
                <span class="badge <?= $rb[$u['role']] ?? 'badge-secondary' ?>"><?= str_replace('_',' ',ucfirst($u['role'])) ?></span>
              </td>
              <td><?= $u['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
              <td class="text-muted text-small"><?= $u['last_login'] ? formatDate($u['last_login'], 'd M y H:i') : 'Never' ?></td>
              <td>
                <?php if ($u['id'] !== Auth::id()): ?>
                <a href="<?= APP_URL ?>/settings/users/toggle/<?= $u['id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Toggle this user?')">
                  <?= $u['is_active'] ? '🔒' : '🔓' ?>
                </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">➕ Add New User</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/settings/users/create" method="POST">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters"></div>
        <div class="form-group"><label class="form-label">Role *</label>
          <select name="role" class="form-control" required>
            <option value="operator">Operator</option>
            <option value="admin">Admin</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Create User</button>
      </div>
    </form>
  </div>
</div>
