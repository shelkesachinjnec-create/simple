<?php $pageTitle = 'Reports'; ?>

<div class="grid grid-3">
  <a href="<?= APP_URL ?>/reports/daily" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#e8f0fe">📅</div>
    <div class="stat-info">
      <div class="stat-label">Daily Report</div>
      <div style="font-size:14px;margin-top:4px">Today's sales & collections</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/reports/monthly" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#e6f9f0">📆</div>
    <div class="stat-info">
      <div class="stat-label">Monthly Report</div>
      <div style="font-size:14px;margin-top:4px">Revenue trends & analytics</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/sales" class="stat-card" style="text-decoration:none">
    <div class="stat-icon" style="background:#fff8e1">🧾</div>
    <div class="stat-info">
      <div class="stat-label">All Sales</div>
      <div style="font-size:14px;margin-top:4px">Complete sales history</div>
    </div>
  </a>
</div>
