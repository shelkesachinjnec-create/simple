<?php $pageTitle = 'Monthly Report — ' . $year; ?>
<?php
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$chartRevenue = array_fill(0, 12, 0);
$chartSales   = array_fill(0, 12, 0);
$totalRevenue = 0; $totalSales = 0;
foreach ($monthlyData as $row) {
  $i = $row['month'] - 1;
  $chartRevenue[$i] = (float)$row['revenue'];
  $chartSales[$i]   = (int)$row['total_sales'];
  $totalRevenue += $row['revenue'];
  $totalSales   += $row['total_sales'];
}
?>

<div style="margin-bottom:16px">
  <form method="GET" style="display:flex;gap:10px;align-items:center">
    <select name="year" class="form-control" style="width:120px" onchange="this.form.submit()">
      <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
      <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <button type="submit" class="btn btn-primary">📆 Load</button>
  </form>
</div>

<div class="grid grid-3" style="margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-icon" style="background:#e8f0fe">🧾</div>
    <div class="stat-info"><div class="stat-label">Total Sales <?= $year ?></div><div class="stat-value"><?= $totalSales ?></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#e6f9f0">💰</div>
    <div class="stat-info"><div class="stat-label">Total Revenue</div><div class="stat-value" style="font-size:18px"><?= formatCurrency($totalRevenue) ?></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fff8e1">📊</div>
    <div class="stat-info"><div class="stat-label">Avg/Month</div><div class="stat-value" style="font-size:18px"><?= formatCurrency($totalRevenue / 12) ?></div></div>
  </div>
</div>

<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h3 class="card-title">📈 Monthly Revenue <?= $year ?></h3></div>
  <div class="card-body"><div class="chart-wrapper"><canvas id="monthlyChart"></canvas></div></div>
</div>

<div class="grid grid-2">
  <div class="card">
    <div class="card-header"><h3 class="card-title">📆 Month-by-Month Breakdown</h3></div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>Month</th><th>Sales</th><th>Revenue</th><th>Collected</th><th>Outstanding</th></tr></thead>
        <tbody>
          <?php foreach ($monthlyData as $row): ?>
          <tr>
            <td><?= $months[$row['month']-1] ?></td>
            <td><?= $row['total_sales'] ?></td>
            <td><?= formatCurrency($row['revenue']) ?></td>
            <td style="color:var(--secondary)"><?= formatCurrency($row['collected']) ?></td>
            <td style="color:<?= $row['outstanding']>0?'var(--danger)':'var(--secondary)' ?>"><?= formatCurrency($row['outstanding']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($monthlyData)): ?>
          <tr><td colspan="5"><div class="empty-state"><p>No sales data for <?= $year ?></p></div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><h3 class="card-title">🏆 Top Selling Models</h3></div>
      <div class="card-body" style="padding:0">
        <?php foreach (array_slice($topModels,0,5) as $i=>$m): ?>
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between">
          <div><span style="font-size:14px"><?= ['🥇','🥈','🥉','4️⃣','5️⃣'][$i] ?></span> <strong><?= h($m['model_name']) ?></strong></div>
          <div><?= $m['total_sales'] ?> units · <?= formatCurrency($m['revenue']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($topModels)): ?><div class="empty-state" style="padding:20px"><p>No data</p></div><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">👤 Operator Performance</h3></div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Operator</th><th>Sales</th><th>Revenue</th></tr></thead>
          <tbody>
            <?php foreach ($operatorStats as $op): ?>
            <tr>
              <td><?= h($op['name']) ?></td>
              <td><?= $op['total_sales'] ?? 0 ?></td>
              <td><?= formatCurrency($op['revenue'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($operatorStats)): ?><tr><td colspan="3" class="text-center text-muted">No data</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
const ctx = document.getElementById("monthlyChart").getContext("2d");
new Chart(ctx, {
  type: "bar",
  data: {
    labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
    datasets: [
      { label: "Revenue (₹)", data: ' . json_encode(array_values($chartRevenue)) . ', backgroundColor: "rgba(26,115,232,.7)", borderColor: "rgba(26,115,232,1)", borderWidth:2, borderRadius:6, yAxisID:"y" },
      { label: "Units Sold", data: ' . json_encode(array_values($chartSales)) . ', type: "line", borderColor: "rgba(52,168,83,1)", backgroundColor: "rgba(52,168,83,.1)", borderWidth:2, fill:true, yAxisID:"y1", tension:.4 }
    ]
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{ position:"top" } },
    scales: {
      y: { beginAtZero:true, ticks:{ callback: v => "₹"+(v/1000).toFixed(0)+"K" } },
      y1: { position:"right", beginAtZero:true, grid:{ drawOnChartArea:false } }
    }
  }
});
</script>'; ?>
