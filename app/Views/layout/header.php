<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="base-url" content="<?= APP_URL ?>">
<meta name="csrf-token" content="<?= Auth::generateCsrfToken() ?>">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Simple Scooters CRM</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛵</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body>

<?php
$pendingFollowupsCount = 0;
$lowStockCount = 0;
try {
    $db = Database::getInstance();
    $pendingFollowupsCount = (int)($db->fetch("SELECT COUNT(*) as c FROM followups WHERE status='pending' AND followup_date<=CURDATE()")['c'] ?? 0);
    $lowStockCount = (int)($db->fetch("SELECT COUNT(*) as c FROM inventory WHERE stock_quantity <= low_stock_alert AND is_active=1")['c'] ?? 0);
} catch(Exception $e) {}
?>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
  <a href="<?= APP_URL ?>/dashboard" class="sidebar-brand">
    <div class="brand-icon">🛵</div>
    <div>
      <div class="brand-text">Simple</div>
      <div class="brand-sub">Scooter CRM</div>
    </div>
  </a>

  <div class="nav-section">
    <div class="nav-section-label">Overview</div>
    <a href="<?= APP_URL ?>/dashboard" class="nav-link">
      <span class="nav-icon">📊</span> Dashboard
    </a>
  </div>

  <div class="nav-section">
    <div class="nav-section-label">CRM</div>
    <a href="<?= APP_URL ?>/visitors" class="nav-link">
      <span class="nav-icon">👀</span> Visitors
    </a>
    <a href="<?= APP_URL ?>/leads" class="nav-link">
      <span class="nav-icon">🎯</span> Leads & Follow-ups
      <?php if ($pendingFollowupsCount > 0): ?>
        <span class="badge-count"><?= $pendingFollowupsCount ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/customers" class="nav-link">
      <span class="nav-icon">👥</span> Customers
    </a>
  </div>

  <?php if (Auth::isAdmin()): ?>
  <div class="nav-section">
    <div class="nav-section-label">Sales & Inventory</div>
    <a href="<?= APP_URL ?>/inventory" class="nav-link">
      <span class="nav-icon">🛵</span> Inventory
      <?php if ($lowStockCount > 0): ?>
        <span class="badge-count"><?= $lowStockCount ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/sales" class="nav-link">
      <span class="nav-icon">🧾</span> Sales
    </a>
    <a href="<?= APP_URL ?>/sales/create" class="nav-link">
      <span class="nav-icon">➕</span> New Sale
    </a>
  </div>
  <?php endif; ?>

  <?php if (Auth::isAdmin()): ?>
  <div class="nav-section">
    <div class="nav-section-label">Reports</div>
    <a href="<?= APP_URL ?>/reports" class="nav-link">
      <span class="nav-icon">📈</span> Reports
    </a>
    <a href="<?= APP_URL ?>/reports/daily" class="nav-link">
      <span class="nav-icon">📅</span> Daily Report
    </a>
    <a href="<?= APP_URL ?>/reports/monthly" class="nav-link">
      <span class="nav-icon">📆</span> Monthly Report
    </a>
  </div>
  <?php endif; ?>

  <?php if (Auth::isSuperAdmin()): ?>
  <div class="nav-section">
    <div class="nav-section-label">Administration</div>
    <a href="<?= APP_URL ?>/settings" class="nav-link">
      <span class="nav-icon">⚙️</span> Settings
    </a>
  </div>
  <?php endif; ?>

  <div class="nav-section" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,.06); padding-top: 12px;">
    <a href="<?= APP_URL ?>/auth/logout" class="nav-link" style="color: rgba(255,100,100,.7)">
      <span class="nav-icon">🚪</span> Logout
    </a>
  </div>
</nav>

<!-- Topbar -->
<header class="topbar">
  <button class="btn-icon sidebar-toggle" title="Toggle Sidebar">☰</button>
  <h1 class="topbar-title"><?= isset($pageTitle) ? h($pageTitle) : 'Dashboard' ?></h1>
  <div class="topbar-actions">
    <button class="btn-icon theme-toggle" title="Toggle Theme">🌙</button>
    <?php if (Auth::isAdmin()): ?>
    <a href="<?= APP_URL ?>/sales/create" class="btn btn-primary btn-sm" style="text-decoration:none">
      ➕ New Sale
    </a>
    <?php endif; ?>
    <div class="user-menu" onclick="App.openModal('userMenuModal')">
      <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
      <div class="user-menu-info">
        <div class="user-name"><?= h($user['name']) ?></div>
        <div class="user-role"><?= str_replace('_', ' ', $user['role']) ?></div>
      </div>
    </div>
  </div>
</header>

<!-- Main Content -->
<main class="main-content">
  <div class="page-body">

<?php if ($flash_success): ?>
<script>document.addEventListener('DOMContentLoaded', () => App.toast(<?= json_encode($flash_success) ?>, 'success'));</script>
<?php endif; ?>
<?php if ($flash_error): ?>
<script>document.addEventListener('DOMContentLoaded', () => App.toast(<?= json_encode($flash_error) ?>, 'error'));</script>
<?php endif; ?>
<?php if ($flash_info): ?>
<script>document.addEventListener('DOMContentLoaded', () => App.toast(<?= json_encode($flash_info) ?>, 'info'));</script>
<?php endif; ?>
