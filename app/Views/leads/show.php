<?php $pageTitle = 'Lead: ' . $lead['name']; ?>

<div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
  <!-- Lead Details -->
  <div style="flex:1;min-width:300px">
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <h3 class="card-title">🎯 Lead Information</h3>
        <div style="display:flex;gap:8px">
          <?php if ($lead['status'] !== 'converted' && Auth::isAdmin()): ?>
          <a href="<?= APP_URL ?>/leads/<?= $lead['id'] ?>/convert-customer" class="btn btn-success btn-sm" onclick="return confirm('Convert to customer?')">✅ Convert</a>
          <?php endif; ?>
          <button class="btn btn-outline btn-sm" data-modal="editLeadModal">✏️ Edit</button>
        </div>
      </div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <div class="text-muted text-small">Name</div>
            <div style="font-weight:600"><?= h($lead['name']) ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Phone</div>
            <div style="font-weight:600">
              <?= h($lead['phone']) ?>
              <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($lead['phone']) ?>')" style="margin-left:6px">💬</button>
            </div>
          </div>
          <div>
            <div class="text-muted text-small">Email</div>
            <div><?= $lead['email'] ? h($lead['email']) : '—' ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Budget</div>
            <div><?= $lead['budget'] ? formatCurrency($lead['budget']) : '—' ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Status</div>
            <?php
              $sb = ['new'=>'badge-info','contacted'=>'badge-primary','negotiating'=>'badge-warning','converted'=>'badge-success','lost'=>'badge-danger'][$lead['status']] ?? 'badge-secondary';
            ?>
            <span class="badge <?= $sb ?>"><?= ucfirst($lead['status']) ?></span>
          </div>
          <div>
            <div class="text-muted text-small">Priority</div>
            <?php $pb = ['high'=>'badge-danger','medium'=>'badge-warning','low'=>'badge-success'][$lead['priority']] ?? ''; ?>
            <span class="badge <?= $pb ?>"><?= ucfirst($lead['priority']) ?></span>
          </div>
          <div>
            <div class="text-muted text-small">Interested In</div>
            <div><?= $lead['interested_model'] ?? '—' ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Next Follow-up</div>
            <div><?= $lead['next_followup_date'] ? formatDate($lead['next_followup_date']) : '—' ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Assigned To</div>
            <div><?= $lead['assigned_to_name'] ?? '—' ?></div>
          </div>
          <div>
            <div class="text-muted text-small">Created</div>
            <div><?= formatDate($lead['created_at'], 'd M Y H:i') ?></div>
          </div>
        </div>
        <?php if ($lead['notes']): ?>
        <hr style="border-color:var(--border);margin:16px 0">
        <div class="text-muted text-small">Notes</div>
        <div style="margin-top:4px"><?= nl2br(h($lead['notes'])) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Add Follow-up -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">📅 Schedule Follow-up</h3>
      </div>
      <form action="<?= APP_URL ?>/leads/<?= $lead['id'] ?>/followup" method="POST">
        <?= Auth::csrfField() ?>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Date *</label>
              <input type="date" name="followup_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Time</label>
              <input type="time" name="followup_time" class="form-control" value="10:00">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
              <option value="call">📞 Phone Call</option>
              <option value="whatsapp">💬 WhatsApp</option>
              <option value="email">📧 Email</option>
              <option value="visit">🏪 Visit</option>
              <option value="sms">📱 SMS</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" placeholder="Follow-up agenda..."></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 20px">
          <button type="submit" class="btn btn-primary">📅 Schedule</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Follow-up History -->
  <div style="width:340px;flex-shrink:0">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">📋 Follow-up History</h3>
        <span class="badge badge-primary"><?= count($followups) ?></span>
      </div>
      <div style="padding:0">
        <?php if (empty($followups)): ?>
          <div class="empty-state" style="padding:30px"><div class="empty-icon">📋</div><p>No follow-ups yet</p></div>
        <?php else: ?>
        <?php foreach ($followups as $f): ?>
        <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
            <div>
              <span class="badge <?= $f['status']==='completed'?'badge-success':($f['status']==='pending'?'badge-warning':'badge-secondary') ?>" style="font-size:10px"><?= ucfirst($f['status']) ?></span>
              <span class="badge badge-info" style="font-size:10px;margin-left:4px"><?= ucfirst($f['type']) ?></span>
            </div>
            <div class="text-muted text-small"><?= formatDate($f['followup_date']) ?><?= $f['followup_time'] ? ' '.date('g:i A', strtotime($f['followup_time'])) : '' ?></div>
          </div>
          <?php if ($f['notes']): ?><div style="font-size:13px;margin-bottom:4px"><?= h($f['notes']) ?></div><?php endif; ?>
          <?php if ($f['result']): ?><div style="font-size:12px;color:var(--secondary)">✅ <?= h($f['result']) ?></div><?php endif; ?>
          <div class="text-muted text-small" style="margin-top:4px">by <?= h($f['conducted_by_name'] ?? '—') ?></div>
          <?php if ($f['status'] === 'pending'): ?>
          <form action="<?= APP_URL ?>/followups/complete/<?= $f['id'] ?>" method="POST" style="margin-top:10px">
            <?= Auth::csrfField() ?>
            <input type="text" name="result" class="form-control" placeholder="Result/outcome..." style="font-size:12px;padding:6px 10px;margin-bottom:6px">
            <input type="date" name="next_followup_date" class="form-control" placeholder="Next follow-up date" style="font-size:12px;padding:6px 10px;margin-bottom:6px">
            <button type="submit" class="btn btn-success btn-sm w-100">✅ Mark Complete</button>
          </form>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal-overlay" id="editLeadModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">✏️ Edit Lead</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/leads/update/<?= $lead['id'] ?>" method="POST">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= h($lead['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= h($lead['phone']) ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <?php foreach (['new','contacted','negotiating','converted','lost'] as $s): ?>
              <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control">
              <?php foreach (['low','medium','high'] as $p): ?>
              <option value="<?= $p ?>" <?= $lead['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Interested Model</label>
            <select name="interested_model_id" class="form-control">
              <option value="">Select model</option>
              <?php foreach ($inventory as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $lead['interested_model_id']==$m['id']?'selected':'' ?>><?= h($m['model_name']) ?> — <?= h($m['color']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Next Follow-up</label>
            <input type="date" name="next_followup_date" class="form-control" value="<?= $lead['next_followup_date'] ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control"><?= h($lead['notes']) ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Update</button>
      </div>
    </form>
  </div>
</div>
