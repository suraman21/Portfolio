<?php
declare(strict_types=1);

require_once __DIR__ . '/repository.php';

$site = $site ?? get_site_settings();
$activeNav = $activeNav ?? 'home';
$isSubpage = $isSubpage ?? ($activeNav !== 'home');
$pageTitle = $pageTitle ?? (($site['name'] ?? 'Jordan Lee') . ' — ' . ($site['role'] ?? 'Software & Web Developer'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?></title>
  <link rel="preload" as="image" href="/assets/page-bg-exact.webp" fetchpriority="high" />
  <link rel="preload" as="font" href="/fonts/Inter-Opsz.woff2" type="font/woff2" crossorigin />
  <?php if (!$isSubpage): ?>
    <link rel="preload" as="image" href="<?= e(optimized_image_path((string)($site['portrait_path'] ?? '/assets/profile-cutout.png'))) ?>" fetchpriority="high" />
  <?php endif; ?>
  <link rel="stylesheet" href="/styles.css?v=17" />
</head>
<body<?= $isSubpage ? ' class="is-subpage"' : '' ?>>
  <div class="page-preloader" id="pagePreloader" role="status" aria-live="polite" aria-hidden="true">
    <div class="preloader-card">
      <span class="preloader-mark" aria-hidden="true">A</span>
      <span class="preloader-title">Preparing your experience</span>
      <span class="preloader-caption">Loading the details that bring this page to life</span>
      <span class="preloader-progress" aria-hidden="true"><span></span></span>
    </div>
  </div>
  <div class="page-wrapper" id="top">
    <div class="bg-silk-layer" aria-hidden="true"></div>
    <div class="bg-sheen-streaks" aria-hidden="true">
      <canvas class="ambient-dot-canvas" id="ambientDotField"></canvas>
    </div>

    <div class="main-shell">
      <!-- ==================== 1. NAVBAR ==================== -->
      <header class="glass-nav" id="navbar">
        <a href="/" class="brand" aria-label="<?= e($site['name'] ?? 'Home') ?>">
          <span class="brand-logo-box neon-logo-box">
            <span class="brand-initials"><?php
              $nm = trim((string)($site['name'] ?? 'Portfolio'));
              $parts = preg_split('/\s+/', $nm);
              $ini = strtoupper(substr($parts[0] ?? 'P', 0, 1) . substr($parts[1] ?? ($parts[0] ?? 'P'), 0, 1));
              echo e($ini);
            ?></span>
          </span>
          <span class="brand-text">
            <span class="brand-name"><?= e($site['name'] ?? 'Jordan Lee') ?></span>
            <span class="brand-role"><?= e($site['role'] ?? 'Software & Web Developer') ?></span>
          </span>
        </a>

        <nav class="nav-links" id="primaryNav" aria-label="Primary Navigation">
          <?php
          $navItems = [
              'home' => ['label' => 'Home', 'href' => '/'],
              'about' => ['label' => 'About', 'href' => '/about'],
              'services' => ['label' => 'Services', 'href' => '/services'],
              'projects' => ['label' => 'Projects', 'href' => '/projects'],
              'blog' => ['label' => 'Blog', 'href' => '/blog'],
              'contact' => ['label' => 'Contact', 'href' => '/contact'],
          ];
          foreach ($navItems as $key => $item):
              $isActive = ($activeNav === $key);
          ?>
            <a href="<?= e($item['href']) ?>" class="nav-link<?= $isActive ? ' active' : '' ?>">
              <?= e($item['label']) ?>
              <?php if ($isActive): ?>
                <span class="nav-dot" aria-hidden="true"></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="nav-right-actions">
          <a href="/contact" class="btn-lets-talk">
            <span>Let's Talk</span>
            <svg viewBox="0 0 14 14" width="13" height="13" fill="none" aria-hidden="true">
              <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
          <button
            type="button"
            class="mobile-menu-btn"
            id="mobileMenuBtn"
            aria-label="Toggle navigation menu"
            aria-expanded="false"
            aria-controls="primaryNav"
          >
            <svg class="icon-menu-bars" viewBox="0 0 20 20" width="18" height="18" fill="none" aria-hidden="true">
              <path d="M3.2 5.8H16.8M3.2 10H16.8M3.2 14.2H16.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
            <svg class="icon-menu-close" viewBox="0 0 20 20" width="18" height="18" fill="none" aria-hidden="true">
              <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </header>
