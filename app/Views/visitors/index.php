<?php $pageTitle = 'Visitors'; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">👀 Visitor Management</h3>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" data-modal="addVisitorModal">➕ Add Visitor</button>
    </div>
  </div>

  <!-- Filters -->
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--bg-main)">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div class="search-bar" style="max-width:260px">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Search name, phone..." value="<?= h($filters['search']) ?>">
      </div>
      <select name="status" class="form-control" style="width:140px" data-auto-submit>
        <option value="">All Status</option>
        <option value="cold" <?= $filters['status']==='cold'?'selected':'' ?>>❄️ Cold</option>
        <option value="warm" <?= $filters['status']==='warm'?'selected':'' ?>>🌡️ Warm</option>
        <option value="hot" <?= $filters['status']==='hot'?'selected':'' ?>>🔥 Hot</option>
      </select>
      <input type="date" name="date_from" class="form-control" style="width:145px" value="<?= h($filters['date_from']) ?>" placeholder="From date">
      <input type="date" name="date_to" class="form-control" style="width:145px" value="<?= h($filters['date_to']) ?>" placeholder="To date">
      <button type="submit" class="btn btn-primary">🔍</button>
      <a href="<?= APP_URL ?>/visitors" class="btn btn-ghost">↺</a>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Visitor</th><th>Contact</th><th>Source</th><th>Interested In</th><th>Visit Date</th><th>Status</th><th>Operator</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">👤</div><h4>No visitors found</h4><p>Add your first visitor using the button above</p></div></td></tr>
        <?php else: ?>
        <?php foreach ($result['data'] as $v): ?>
        <tr>
          <td><?= $v['id'] ?></td>
          <td>
            <div style="font-weight:600"><?= h($v['name']) ?></div>
            <?php if ($v['address']): ?><div class="text-muted text-small"><?= h($v['address']) ?></div><?php endif; ?>
          </td>
          <td>
            <div><?= h($v['phone']) ?></div>
            <?php if ($v['email']): ?><div class="text-muted text-small"><?= h($v['email']) ?></div><?php endif; ?>
          </td>
          <td><?= $v['source_name'] ? '<span class="badge badge-info">' . h($v['source_name']) . '</span>' : '<span class="text-muted">—</span>' ?></td>
          <td><?= $v['interested_model'] ? h($v['interested_model']) : '<span class="text-muted">—</span>' ?></td>
          <td><?= formatDate($v['visit_date']) ?></td>
          <td>
            <?php $badges = ['cold'=>'badge-info','warm'=>'badge-warning','hot'=>'badge-danger']; ?>
            <?php $icons = ['cold'=>'❄️','warm'=>'🌡️','hot'=>'🔥']; ?>
            <span class="badge <?= $badges[$v['status']] ?? 'badge-secondary' ?>"><?= ($icons[$v['status']] ?? '').' '.ucfirst($v['status']) ?></span>
          </td>
          <td><?= $v['operator_name'] ? h($v['operator_name']) : '<span class="text-muted">—</span>' ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <button class="btn btn-ghost btn-sm" data-modal="editVisitorModal"
                data-id="<?= $v['id'] ?>"
                data-name="<?= h($v['name']) ?>"
                data-phone="<?= h($v['phone']) ?>"
                data-email="<?= h($v['email']) ?>"
                data-address="<?= h($v['address']) ?>"
                data-status="<?= h($v['status']) ?>"
                data-source_id="<?= $v['source_id'] ?>"
                data-visit_date="<?= $v['visit_date'] ?>"
                data-notes="<?= h($v['notes']) ?>">✏️</button>
              <a href="<?= APP_URL ?>/visitors/convert-lead/<?= $v['id'] ?>" class="btn btn-success btn-sm" title="Convert to Lead">🎯</a>
              <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($v['phone']) ?>')">💬</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($result['last_page'] > 1): ?>
  <div style="padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div class="text-muted text-small">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> visitors</div>
    <div class="pagination">
      <?php for ($p = 1; $p <= $result['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&<?= http_build_query($filters) ?>" class="page-link <?= $p === $result['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Add Visitor Modal -->
<div class="modal-overlay" id="addVisitorModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">➕ Add New Visitor</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/visitors/store" method="POST">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="John Doe">
          </div>
          <div class="form-group">
            <label class="form-label">Phone *</label>
            <input type="tel" name="phone" class="form-control" required placeholder="+91 99999 99999">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="john@email.com">
          </div>
          <div class="form-group">
            <label class="form-label">Visit Date *</label>
            <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" placeholder="Area, City">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Source</label>
            <select name="source_id" class="form-control">
              <option value="">Select source</option>
              <?php foreach ($sources as $s): ?>
              <option value="<?= $s['id'] ?>"><?= h($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Interested Model</label>
            <select name="interested_model_id" class="form-control">
              <option value="">Select model</option>
              <?php foreach ($inventory as $m): ?>
              <option value="<?= $m['id'] ?>"><?= h($m['model_name']) ?> — <?= h($m['color']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Lead Status</label>
            <select name="status" class="form-control">
              <option value="cold">❄️ Cold</option>
              <option value="warm">🌡️ Warm</option>
              <option value="hot">🔥 Hot</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Assign To</label>
            <select name="assigned_operator_id" class="form-control">
              <option value="">Assign operator</option>
              <?php foreach ($operators as $op): ?>
              <option value="<?= $op['id'] ?>"><?= h($op['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" placeholder="Any observations..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Visitor</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Visitor Modal -->
<div class="modal-overlay" id="editVisitorModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">✏️ Edit Visitor</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form method="POST" data-action-template="<?= APP_URL ?>/visitors/update/{id}">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone *</label>
            <input type="tel" name="phone" class="form-control" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Visit Date</label>
            <input type="date" name="visit_date" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Source</label>
            <select name="source_id" class="form-control">
              <option value="">Select source</option>
              <?php foreach ($sources as $s): ?>
              <option value="<?= $s['id'] ?>"><?= h($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="cold">❄️ Cold</option>
              <option value="warm">🌡️ Warm</option>
              <option value="hot">🔥 Hot</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Update</button>
      </div>
    </form>
  </div>
</div>
