<?php $pageTitle = 'Inventory'; ?>

<?php if (!empty($lowStock)): ?>
<div class="alert alert-warning" style="margin-bottom:16px">
  ⚠️ <strong><?= count($lowStock) ?> item(s)</strong> are running low on stock!
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">🛵 Inventory Management</h3>
    <?php if (Auth::isAdmin()): ?>
    <button class="btn btn-primary" data-modal="addInventoryModal">➕ Add Scooter</button>
    <?php endif; ?>
  </div>
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--bg-main)">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Model, brand, SKU..." value="<?= h($filters['search']) ?>">
      </div>
      <select name="is_active" class="form-control" style="width:130px" data-auto-submit>
        <option value="">All Items</option>
        <option value="1" <?= $filters['is_active']==='1'?'selected':'' ?>>✅ Active</option>
        <option value="0" <?= $filters['is_active']==='0'?'selected':'' ?>>❌ Inactive</option>
      </select>
      <button type="submit" class="btn btn-primary">🔍</button>
      <a href="<?= APP_URL ?>/inventory" class="btn btn-ghost">↺</a>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th>#</th><th>Model</th><th>SKU</th><th>Color</th><th>Purchase Price</th><th>Selling Price</th><th>Margin</th><th>Stock</th><th>Specs</th><th>Status</th><?php if(Auth::isAdmin()): ?><th>Actions</th><?php endif; ?></tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
        <tr><td colspan="11"><div class="empty-state"><div class="empty-icon">🛵</div><h4>No inventory items</h4></div></td></tr>
        <?php else: ?>
        <?php foreach ($result['data'] as $item): ?>
        <?php
          $margin = $item['selling_price'] > 0 ? (($item['selling_price']-$item['purchase_price'])/$item['selling_price'])*100 : 0;
          $isLow  = $item['stock_quantity'] <= $item['low_stock_alert'];
        ?>
        <tr>
          <td><?= $item['id'] ?></td>
          <td>
            <div style="font-weight:600"><?= h($item['model_name']) ?></div>
            <?php if ($item['brand']): ?><div class="text-muted text-small"><?= h($item['brand']) ?></div><?php endif; ?>
          </td>
          <td><span class="text-muted text-small"><?= h($item['sku']) ?></span></td>
          <td><?= h($item['color']) ?></td>
          <td><?= formatCurrency($item['purchase_price']) ?></td>
          <td style="font-weight:600"><?= formatCurrency($item['selling_price']) ?></td>
          <td><span class="badge badge-success"><?= round($margin, 1) ?>%</span></td>
          <td>
            <span class="badge <?= $isLow ? 'badge-danger' : 'badge-success' ?>">
              <?= $isLow ? '⚠️ ' : '' ?><?= $item['stock_quantity'] ?> units
            </span>
          </td>
          <td>
            <div class="text-small">
              <?= $item['range_km'] ? $item['range_km'].'km' : '' ?>
              <?= $item['top_speed'] ? '· '.$item['top_speed'].'km/h' : '' ?>
              <?= $item['battery_capacity'] ? '· '.$item['battery_capacity'] : '' ?>
            </div>
          </td>
          <td><?= $item['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
          <?php if (Auth::isAdmin()): ?>
          <td>
            <button class="btn btn-ghost btn-sm" data-modal="editInventoryModal"
              data-id="<?= $item['id'] ?>"
              data-model_name="<?= h($item['model_name']) ?>"
              data-brand="<?= h($item['brand']) ?>"
              data-color="<?= h($item['color']) ?>"
              data-sku="<?= h($item['sku']) ?>"
              data-purchase_price="<?= $item['purchase_price'] ?>"
              data-selling_price="<?= $item['selling_price'] ?>"
              data-stock_quantity="<?= $item['stock_quantity'] ?>"
              data-warranty_months="<?= $item['warranty_months'] ?>">✏️</button>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($result['last_page'] > 1): ?>
  <div style="padding:14px 16px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div class="text-muted text-small">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> items</div>
    <div class="pagination">
      <?php for ($p = 1; $p <= $result['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&<?= http_build_query($filters) ?>" class="page-link <?= $p===$result['current_page']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php if (Auth::isAdmin()): ?>
<!-- Add Inventory Modal -->
<div class="modal-overlay" id="addInventoryModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 class="modal-title">➕ Add Scooter</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form action="<?= APP_URL ?>/inventory/store" method="POST" enctype="multipart/form-data">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Model Name *</label><input type="text" name="model_name" class="form-control" required placeholder="Simple One"></div>
          <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" placeholder="Simple Energy"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Color *</label><input type="text" name="color" class="form-control" required placeholder="Brazen Black"></div>
          <div class="form-group"><label class="form-label">SKU *</label><input type="text" name="sku" class="form-control" required placeholder="SE-ONE-BLK"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Purchase Price *</label><input type="number" name="purchase_price" class="form-control" required step="0.01" placeholder="95000"></div>
          <div class="form-group"><label class="form-label">Selling Price *</label><input type="number" name="selling_price" class="form-control" required step="0.01" placeholder="115000"></div>
          <div class="form-group"><label class="form-label">Stock Qty</label><input type="number" name="stock_quantity" class="form-control" value="0" min="0"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Battery</label><input type="text" name="battery_capacity" class="form-control" placeholder="4.8 kWh"></div>
          <div class="form-group"><label class="form-label">Range (km)</label><input type="number" name="range_km" class="form-control" placeholder="203"></div>
          <div class="form-group"><label class="form-label">Top Speed (km/h)</label><input type="number" name="top_speed" class="form-control" placeholder="80"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Dealer Name</label><input type="text" name="dealer_name" class="form-control"></div>
          <div class="form-group"><label class="form-label">Dealer Contact</label><input type="text" name="dealer_contact" class="form-control"></div>
          <div class="form-group"><label class="form-label">Warranty (months)</label><input type="number" name="warranty_months" class="form-control" value="12"></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
        <div class="form-group"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Add to Inventory</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Inventory Modal -->
<div class="modal-overlay" id="editInventoryModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">✏️ Edit Inventory Item</h3>
      <button class="btn-close" data-modal-close>✕</button>
    </div>
    <form method="POST" data-action-template="<?= APP_URL ?>/inventory/update/{id}">
      <?= Auth::csrfField() ?>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Model Name *</label><input type="text" name="model_name" class="form-control" required></div>
          <div class="form-group"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Color</label><input type="text" name="color" class="form-control"></div>
          <div class="form-group"><label class="form-label">SKU</label><input type="text" name="sku" class="form-control"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Purchase Price</label><input type="number" name="purchase_price" class="form-control" step="0.01"></div>
          <div class="form-group"><label class="form-label">Selling Price</label><input type="number" name="selling_price" class="form-control" step="0.01"></div>
          <div class="form-group"><label class="form-label">Stock Qty</label><input type="number" name="stock_quantity" class="form-control" min="0"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Warranty (months)</label><input type="number" name="warranty_months" class="form-control"></div>
          <div class="form-group" style="display:flex;align-items:center;padding-top:26px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="is_active"> <span>Active</span></label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Update</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
