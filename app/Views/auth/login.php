<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Simple Scooters CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
<style>
body { background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
.login-card { background: var(--bg-card, #fff); border-radius: 20px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 30px 80px rgba(0,0,0,.3); }
.login-logo { text-align: center; margin-bottom: 28px; }
.login-logo .logo-icon { font-size: 48px; }
.login-logo h1 { font-size: 26px; font-weight: 800; color: #1a1a2e; margin-top: 8px; }
.login-logo p { color: #666; font-size: 13px; }
.form-control { background: #f5f7fa; border: 2px solid #e5e7eb; padding: 12px 16px; }
.form-control:focus { background: #fff; border-color: var(--primary); }
.btn-login { width: 100%; padding: 13px; font-size: 15px; border-radius: 10px; background: var(--primary); color: #fff; border: none; font-weight: 700; cursor: pointer; transition: .2s; margin-top: 8px; }
.btn-login:hover { background: var(--primary-dark); }
.alert-box { padding: 12px 16px; background: #fdecea; color: #c62828; border-radius: 10px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.demo-box { margin-top: 24px; padding: 14px; background: #e8f0fe; border-radius: 10px; font-size: 12px; color: #1a1a2e; }
.demo-box strong { display: block; margin-bottom: 8px; color: var(--primary); }
.demo-cred { display: flex; justify-content: space-between; margin: 4px 0; }
</style>
</head>
<body>
<?php
Auth::start();
$flash_error = getFlash('error');
?>

<div class="login-card">
  <div class="login-logo">
    <div class="logo-icon">🛵</div>
    <h1>Simple Scooters</h1>
    <p>Enterprise Showroom Management System</p>
  </div>

  <?php if ($flash_error): ?>
  <div class="alert-box">⚠️ <?= h($flash_error) ?></div>
  <?php endif; ?>

  <form action="<?= APP_URL ?>/auth/login" method="POST">
    <?= Auth::csrfField() ?>
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" placeholder="you@example.com" required autocomplete="email">
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn-login">🔐 Sign In</button>
  </form>

  <div class="demo-box">
    <strong>🔑 Demo Credentials</strong>
    <div class="demo-cred"><span>Super Admin:</span><span>superadmin@simple.com</span></div>
    <div class="demo-cred"><span>Admin:</span><span>admin@simple.com</span></div>
    <div class="demo-cred"><span>Operator:</span><span>operator@simple.com</span></div>
    <div class="demo-cred" style="margin-top:8px;font-style:italic;color:#666"><span>Password (all):</span><span>Admin@123</span></div>
  </div>
</div>

<script>
const t = localStorage.getItem('theme');
if (t) document.documentElement.setAttribute('data-theme', t);
</script>
</body>
</html>
