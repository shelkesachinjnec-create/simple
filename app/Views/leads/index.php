<?php $pageTitle = 'Leads & CRM'; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">🎯 Lead Management</h3>
    <button class="btn btn-primary" data-modal="addLeadModal">➕ Add Lead</button>
  </div>

  <!-- Filters -->
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--bg-main)">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Search name, phone..." value="<?= h($filters['search']) ?>">
      </div>
      <select name="status" class="form-control" style="width:150px" data-auto-submit>
        <option value="">All Status</option>
        <option value="new" <?= $filters['status']==='new'?'selected':'' ?>>🆕 New</option>
        <option value="contacted" <?= $filters['status']==='contacted'?'selected':'' ?>>📞 Contacted</option>
        <option value="negotiating" <?= $filters['status']==='negotiating'?'selected':'' ?>>💬 Negotiating</option>
        <option value="converted" <?= $filters['status']==='converted'?'selected':'' ?>>✅ Converted</option>
        <option value="lost" <?= $filters['status']==='lost'?'selected':'' ?>>❌ Lost</option>
      </select>
      <select name="priority" class="form-control" style="width:130px" data-auto-submit>
        <option value="">All Priority</option>
        <option value="high" <?= $filters['priority']==='high'?'selected':'' ?>>🔴 High</option>
        <option value="medium" <?= $filters['priority']==='medium'?'selected':'' ?>>🟡 Medium</option>
        <option value="low" <?= $filters['priority']==='low'?'selected':'' ?>>🟢 Low</option>
      </select>
      <button type="submit" class="btn btn-primary">🔍</button>
      <a href="<?= APP_URL ?>/leads" class="btn btn-ghost">↺</a>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th>#</th><th>Lead</th><th>Contact</th><th>Source</th><th>Interested In</th><th>Priority</th><th>Status</th><th>Next Follow-up</th><th>Assigned To</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
        <tr><td colspan="10"><div class="empty-state"><div class="empty-icon">🎯</div><h4>No leads found</h4></div></td></tr>
        <?php else: ?>
        <?php foreach ($result['data'] as $l): ?>
        <?php
          $statusBadge = ['new'=>'badge-info','contacted'=>'badge-primary','negotiating'=>'badge-warning','converted'=>'badge-success','lost'=>'badge-danger'][$l['status']] ?? 'badge-secondary';
          $priBadge    = ['high'=>'badge-danger','medium'=>'badge-warning','low'=>'badge-success'][$l['priority']] ?? 'badge-secondary';
          $isOverdue   = $l['next_followup_date'] && $l['next_followup_date'] < date('Y-m-d');
        ?>
        <tr>
          <td><?= $l['id'] ?></td>
          <td>
            <div style="font-weight:600"><?= h($l['name']) ?></div>
            <?php if ($l['notes']): ?><div class="text-muted text-small" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($l['notes']) ?></div><?php endif; ?>
          </td>
          <td>
            <div><?= h($l['phone']) ?></div>
            <?php if ($l['email']): ?><div class="text-muted text-small"><?= h($l['email']) ?></div><?php endif; ?>
          </td>
          <td><?= $l['source_name'] ? '<span class="badge badge-info">' . h($l['source_name']) . '</span>' : '—' ?></td>
          <td><?= $l['interested_model'] ? h($l['interested_model']) : '—' ?></td>
          <td><span class="badge <?= $priBadge ?>"><?= ucfirst($l['priority']) ?></span></td>
          <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($l['status']) ?></span></td>
          <td>
            <?php if ($l['next_followup_date']): ?>
              <span style="color:<?= $isOverdue ? 'var(--danger)' : 'inherit' ?>;font-weight:<?= $isOverdue ? '700' : '400' ?>">
                <?= $isOverdue ? '⚠️ ' : '' ?><?= formatDate($l['next_followup_date']) ?>
              </span>
            <?php else: ?><span class="text-muted">Not set</span><?php endif; ?>
          </td>
          <td><?= $l['assigned_to_name'] ? h($l['assigned_to_name']) : '—' ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="<?= APP_URL ?>/leads/<?= $l['id'] ?>" class="btn btn-primary btn-sm">👁️</a>
              <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($l['phone']) ?>', 'Hello <?= h($l['name']) ?>, following up regarding your scooter interest.')">💬</button>
              <?php if ($l['status'] !== 'converted' && Auth::isAdmin()): ?>
              <a href="<?= APP_URL ?>/leads/<?= $l['id'] ?>/convert-customer" class="btn btn-success btn-sm" title="Convert to Customer" onclick="return confirm('Convert this lead to customer?')">✅</a>
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
    <div class="text-muted text-small">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> leads</div>
    <div class="pagination">
      <?php for ($p = 1; $p <= $result['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&<?= http_build_query($filters) ?>" class="page-link <?= $p === $result['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Add Lead Modal -->
<div class="modal-overlay" id="addLeadModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">➕ Add New Lead</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/leads/store" method="POST">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Full Name">
          </div>
          <div class="form-group">
            <label class="form-label">Phone *</label>
            <input type="tel" name="phone" class="form-control" required placeholder="+91 99999 99999">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Budget</label>
            <input type="number" name="budget" class="form-control" placeholder="Approx budget">
          </div>
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
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control">
              <option value="low">🟢 Low</option>
              <option value="medium" selected>🟡 Medium</option>
              <option value="high">🔴 High</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Next Follow-up</label>
            <input type="date" name="next_followup_date" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Assign To</label>
          <select name="assigned_to" class="form-control">
            <?php foreach ($operators as $op): ?>
            <option value="<?= $op['id'] ?>" <?= $op['id'] === Auth::id() ? 'selected' : '' ?>><?= h($op['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" placeholder="Lead details, requirements..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Lead</button>
      </div>
    </form>
  </div>
</div>
