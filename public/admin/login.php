<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';

if (is_admin_logged_in()) {
    redirect_to('/admin/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (attempt_admin_login($username, $password)) {
        set_flash('success', 'Welcome back to your Portfolio Admin Panel!');
        redirect_to('/admin/');
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}

$site = get_site_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Sign In — <?= e($site['name'] ?? 'Jordan Lee') ?></title>
  <link rel="stylesheet" href="/admin.css" />
</head>
<body>
  <div class="admin-auth-wrap">
    <div class="admin-auth-card">
      <div class="auth-brand-row">
        <div class="auth-logo-tile">
          <svg viewBox="0 0 32 32" width="24" height="24" fill="none" aria-hidden="true">
            <path d="M16 4.5L5.8 26.2H11.3L16 15.2L20.7 26.2H26.2L16 4.5Z" fill="#0b0d17" stroke="#0b0d17" stroke-width="1" stroke-linejoin="round"/>
            <polygon points="16,19.4 13.4,25.0 18.6,25.0" fill="#0b0d17"/>
          </svg>
        </div>
        <div>
          <div style="font-size:17px;font-weight:750;color:#111424;"><?= e($site['name'] ?? 'Jordan Lee') ?></div>
          <div style="font-size:12px;color:#6d7288;">Portfolio CMS Admin Sign In</div>
        </div>
      </div>

      <?php if ($error !== ''): ?>
        <div class="adm-flash adm-flash-error" style="margin-bottom:16px;">
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="/admin/login.php" style="display:flex;flex-direction:column;gap:15px;">
        <?= csrf_field() ?>
        <div class="adm-field">
          <label class="adm-label" for="username">Admin Username</label>
          <input type="text" id="username" name="username" class="adm-input" value="admin" required autocomplete="username" />
        </div>
        <div class="adm-field">
          <label class="adm-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="adm-input" placeholder="Enter admin password" required autocomplete="current-password" />
        </div>
        <button type="submit" class="adm-btn adm-btn-primary" style="width:100%;margin-top:4px;padding:11.5px 18px;">
          <span>Sign In to Dashboard →</span>
        </button>
      </form>

      <div style="margin-top:18px;padding-top:14px;border-top:1px solid rgba(190,194,222,0.5);display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#686d84;">
        <a href="/" style="font-weight:600;color:#0284c7;text-decoration:none;">← Back to Site</a>
      </div>
    </div>
  </div>
</body>
</html>
