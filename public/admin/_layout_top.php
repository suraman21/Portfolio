<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';
require_once dirname(__DIR__, 2) . '/includes/admin_parsers.php';

require_admin();

$site = get_site_settings();
$counts = get_dashboard_counts();
$adminPage = $adminPage ?? 'dashboard';
$adminTitle = $adminTitle ?? 'Admin Dashboard';
$adminEyebrow = $adminEyebrow ?? 'PORTFOLIO CMS';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($adminTitle) ?> — <?= e($site['name'] ?? 'Portfolio') ?> Admin</title>
  <link rel="stylesheet" href="/admin.css" />
</head>
<body>
  <div class="admin-shell" id="adminShell">
    <aside class="admin-sidebar" aria-label="CMS Sidebar Navigation">
      <a href="/admin/" class="sidebar-brand">
        <span class="auth-logo-tile" style="width:36px;height:36px;border-radius:10px;">
          <svg viewBox="0 0 32 32" width="22" height="22" fill="none" aria-hidden="true">
            <path d="M16 4.5L5.8 26.2H11.3L16 15.2L20.7 26.2H26.2L16 4.5Z" fill="#0b0d17" stroke="#0b0d17" stroke-width="1" stroke-linejoin="round"/>
            <polygon points="16,19.4 13.4,25.0 18.6,25.0" fill="#0b0d17"/>
          </svg>
        </span>
        <div>
          <div class="sidebar-brand-title"><?= e($site['name'] ?? 'Aarav Singh') ?></div>
          <div class="sidebar-brand-sub">PHP Portfolio CMS</div>
        </div>
      </a>

      <nav class="sidebar-nav">
        <div class="sidebar-section-label">Core Modules</div>
        <a href="/admin/" class="sidebar-link<?= $adminPage === 'dashboard' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>◈</span> <span>Dashboard</span></span>
        </a>
        <a href="/admin/services.php" class="sidebar-link<?= $adminPage === 'services' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>✦</span> <span>Services &amp; FAQs</span></span>
          <span class="adm-badge badge-slate"><?= (int)$counts['services'] ?></span>
        </a>
        <a href="/admin/projects.php" class="sidebar-link<?= $adminPage === 'projects' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>❖</span> <span>Selected Works</span></span>
          <span class="adm-badge badge-slate"><?= (int)$counts['projects'] ?></span>
        </a>
        <a href="/admin/messages.php" class="sidebar-link<?= $adminPage === 'messages' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>✉</span> <span>Contact Inbox</span></span>
          <?php if ((int)$counts['messages_unread'] > 0): ?>
            <span class="nav-count-badge"><?= (int)$counts['messages_unread'] ?></span>
          <?php else: ?>
            <span class="adm-badge badge-slate"><?= (int)$counts['messages_total'] ?></span>
          <?php endif; ?>
        </a>
        <a href="/admin/testimonials.php" class="sidebar-link<?= $adminPage === 'testimonials' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>❝</span> <span>Testimonials</span></span>
          <span class="adm-badge badge-slate"><?= (int)$counts['testimonials'] ?></span>
        </a>

        <div class="sidebar-section-label" style="margin-top:6px;">Profile &amp; Content</div>
        <a href="/admin/profile.php" class="sidebar-link<?= $adminPage === 'profile' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>⚙</span> <span>Hero, Bio &amp; Settings</span></span>
        </a>
        <a href="/admin/skills.php" class="sidebar-link<?= $adminPage === 'skills' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>⚡</span> <span>Skills &amp; Process</span></span>
        </a>
        <a href="/admin/experience.php" class="sidebar-link<?= $adminPage === 'experience' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>⌘</span> <span>Career &amp; Education</span></span>
        </a>
        <a href="/admin/blog.php" class="sidebar-link<?= $adminPage === 'blog' ? ' active' : '' ?>">
          <span class="sidebar-link-left"><span>✎</span> <span>Developer Blog</span></span>
          <span class="adm-badge badge-slate"><?= (int)$counts['blog_posts'] ?></span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="/" target="_blank" class="adm-btn adm-btn-glass adm-btn-sm" style="width:100%;">
          <span>View Live Website ↗</span>
        </a>
        <a href="/admin/logout.php" class="adm-btn adm-btn-danger adm-btn-sm" style="width:100%;">
          <span>Sign Out (<?= e(current_admin_username()) ?>)</span>
        </a>
      </div>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:12px;">
          <button type="button" class="admin-mobile-toggle" id="adminSidebarToggle" aria-label="Toggle CMS Menu">☰</button>
          <div>
            <div class="topbar-eyebrow"><?= e($adminEyebrow) ?></div>
            <h1 class="topbar-title"><?= e($adminTitle) ?></h1>
          </div>
        </div>
        <div class="topbar-actions">
          <span class="adm-badge badge-purple">DB: <?= strtoupper(e(DB_DRIVER)) ?></span>
          <a href="/" class="adm-btn adm-btn-glass adm-btn-sm">Live Site ↗</a>
        </div>
      </header>

      <?php if ($flash): ?>
        <div class="adm-flash <?= $flash['type'] === 'error' ? 'adm-flash-error' : 'adm-flash-success' ?>" role="status">
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>
