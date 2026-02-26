<?php $pageTitle = 'Dashboard'; ?>
<?php
// Prepare chart data
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$revenueByMonth = array_fill(0, 12, 0);
$salesByMonth   = array_fill(0, 12, 0);
foreach ($monthlyData as $row) {
    $idx = $row['month'] - 1;
    $revenueByMonth[$idx] = (float)$row['revenue'];
    $salesByMonth[$idx]   = (int)$row['total_sales'];
}

$sourceLabels = array_column($sourceData, 'name');
$sourceCounts = array_column($sourceData, 'total');
?>

<!-- KPI Cards -->
<div class="grid grid-4" style="margin-bottom:20px">
  <a href="<?= APP_URL ?>/visitors" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#e8f0fe">👀</div>
    <div class="stat-info">
      <div class="stat-label">Visitors (Month)</div>
      <div class="stat-value"><?= number_format($stats['visitors']) ?></div>
      <div class="stat-sub">This month</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/leads" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#fff8e1">🎯</div>
    <div class="stat-info">
      <div class="stat-label">Active Leads</div>
      <div class="stat-value"><?= number_format($stats['leads']) ?></div>
      <div class="stat-sub"><?= $pendingFollowupsCount = $stats['pending_followups'] ?> pending follow-ups</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/customers" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#e6f9f0">👥</div>
    <div class="stat-info">
      <div class="stat-label">Total Customers</div>
      <div class="stat-value"><?= number_format($stats['customers']) ?></div>
      <div class="stat-sub"><?= $stats['conversion_rate'] ?>% conversion rate</div>
    </div>
  </a>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fdecea">🛵</div>
    <div class="stat-info">
      <div class="stat-label">Sales Today</div>
      <div class="stat-value"><?= number_format($stats['today_sales']) ?></div>
      <div class="stat-sub">Units sold</div>
    </div>
  </div>
</div>

<!-- Revenue Row -->
<div class="grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-icon" style="background:#e8f0fe">💰</div>
    <div class="stat-info">
      <div class="stat-label">Monthly Revenue</div>
      <div class="stat-value" style="font-size:20px"><?= formatCurrency($stats['monthly_revenue']) ?></div>
      <div class="stat-sub"><?= date('F Y') ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#e6f9f0">✅</div>
    <div class="stat-info">
      <div class="stat-label">Collected</div>
      <div class="stat-value" style="font-size:20px"><?= formatCurrency($stats['total_collected']) ?></div>
      <div class="stat-sub">Payments received</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fff8e1">⏳</div>
    <div class="stat-info">
      <div class="stat-label">Outstanding</div>
      <div class="stat-value" style="font-size:20px"><?= formatCurrency($stats['outstanding']) ?></div>
      <div class="stat-sub">Balance due</div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="grid grid-2" style="margin-bottom:20px">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">📈 Monthly Revenue <?= date('Y') ?></h3>
    </div>
    <div class="card-body">
      <div class="chart-wrapper">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">🎯 Lead Sources</h3>
    </div>
    <div class="card-body">
      <div class="chart-wrapper">
        <canvas id="sourceChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Bottom Row -->
<div class="grid grid-2">
  <!-- Today's Follow-ups -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">📅 Today's Follow-ups</h3>
      <a href="<?= APP_URL ?>/leads?status=contacted" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($todayFollowups)): ?>
        <div class="empty-state" style="padding:30px">
          <div class="empty-icon">🎉</div>
          <p>No follow-ups scheduled for today!</p>
        </div>
      <?php else: ?>
        <?php foreach (array_slice($todayFollowups, 0, 8) as $f): ?>
        <div style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px">
          <div style="flex:1">
            <div style="font-weight:600;font-size:13px"><?= h($f['name']) ?></div>
            <div class="text-muted text-small"><?= h($f['phone']) ?> · <?= h($f['followup_type'] ?? 'call') ?></div>
          </div>
          <div style="display:flex;gap:6px">
            <a href="<?= APP_URL ?>/leads/<?= $f['id'] ?>" class="btn btn-ghost btn-sm">View</a>
            <button class="btn btn-whatsapp btn-sm" onclick="App.openWhatsApp('<?= h($f['phone']) ?>', 'Hello <?= h($f['name']) ?>, following up regarding your scooter inquiry.')">💬</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Low Stock + Top Models -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <?php if (!empty($lowStock)): ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">⚠️ Low Stock Alert</h3>
        <a href="<?= APP_URL ?>/inventory" class="btn btn-outline btn-sm">Manage</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php foreach ($lowStock as $item): ?>
        <div style="padding:10px 16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center">
          <div>
            <div style="font-weight:600;font-size:13px"><?= h($item['model_name']) ?> — <?= h($item['color']) ?></div>
            <div class="text-muted text-small"><?= h($item['sku']) ?></div>
          </div>
          <span class="badge badge-danger"><?= $item['stock_quantity'] ?> left</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">🏆 Top Selling Models</h3>
      </div>
      <div class="card-body" style="padding:0">
        <?php if (empty($topModels)): ?>
          <div class="empty-state" style="padding:30px"><p>No sales data yet</p></div>
        <?php else: ?>
          <?php foreach ($topModels as $i => $model): ?>
          <div style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center">
            <div style="display:flex;align-items:center;gap:10px">
              <span style="font-size:16px"><?= ['🥇','🥈','🥉','4️⃣','5️⃣'][$i] ?? ($i+1) ?></span>
              <div>
                <div style="font-weight:600;font-size:13px"><?= h($model['model_name']) ?></div>
                <div class="text-muted text-small"><?= $model['total_sales'] ?> units</div>
              </div>
            </div>
            <div style="font-weight:700;color:var(--secondary)"><?= formatCurrency($model['revenue']) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = <<<JS
<script>
// Revenue Chart
const rCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(rCtx, {
  type: 'bar',
  data: {
    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    datasets: [{
      label: 'Revenue (₹)',
      data: <?= json_encode(array_values($revenueByMonth)) ?>,
      backgroundColor: 'rgba(26,115,232,.7)',
      borderColor: 'rgba(26,115,232,1)',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '₹' + ctx.raw.toLocaleString('en-IN') } } },
    scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + (v/1000).toFixed(0) + 'K' } } }
  }
});

// Source Chart
const sCtx = document.getElementById('sourceChart').getContext('2d');
new Chart(sCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($sourceLabels) ?>,
    datasets: [{ data: <?= json_encode($sourceCounts) ?>, backgroundColor: ['#1a73e8','#34a853','#25D366','#ea4335','#fbbc04','#ab47bc','#78909c'], borderWidth: 0, hoverOffset: 10 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } } },
    cutout: '65%'
  }
});
</script>
JS;
?>
