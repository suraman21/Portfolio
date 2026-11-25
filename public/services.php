<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$services = get_services(true);
$faqs = get_faqs();

$activeNav = 'services';
$isSubpage = true;
$pageTitle = 'Services — ' . ($site['name'] ?? 'Jordan Lee');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <div class="breadcrumb-row">
            <a href="/">Home</a>
            <span>›</span>
            <span class="crumb-current">Engineering Services</span>
          </div>
          <span class="eyebrow">END-TO-END SOFTWARE DELIVERY</span>
          <h1 class="subpage-title">Full-Stack Web &amp; Software Engineering Services</h1>
          <p class="subpage-lead">
            From greenfield SaaS MVPs to high-throughput distributed APIs and interactive design systems, every engagement ships with clean TypeScript/PHP/SQL code, automated tests, and measurable performance guarantees.
          </p>
          <div class="subpage-hero-meta">
            <a href="/contact" class="btn-dark-pill btn-sm-pill"><span>Request Architecture Call ↗</span></a>
            <a href="/projects" class="btn-glass-pill btn-sm-pill"><span>Explore Shipped Systems</span></a>
          </div>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="services-detail-grid">
            <?php foreach ($services as $srv): ?>
              <article class="service-detail-card" id="<?= e($srv['slug']) ?>">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                  <div class="service-icon-tile <?= e($srv['tile_class']) ?>" style="margin-bottom:0;">
                    <?= render_service_svg((string)$srv['icon_key'], (string)$srv['tile_class']) ?>
                  </div>
                  <span class="dev-pill dev-pill-accent"><?= e($srv['metrics_badge']) ?></span>
                </div>
                <div>
                  <span class="eyebrow" style="margin-bottom:4px;"><?= e($srv['tagline']) ?></span>
                  <h3><?= e($srv['title']) ?></h3>
                </div>
                <p><?= e($srv['description']) ?></p>

                <?php if (!empty($srv['deliverables'])): ?>
                  <ul class="service-deliverables">
                    <?php foreach ($srv['deliverables'] as $d): ?>
                      <li><?= e($d) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <div style="margin-top:auto;padding-top:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                  <div class="dev-pill-row">
                    <?php foreach ($srv['stack'] as $st): ?>
                      <span class="dev-pill"><?= e($st) ?></span>
                    <?php endforeach; ?>
                  </div>
                  <a href="/contact?service=<?= urlencode((string)$srv['title']) ?>" class="btn-glass-pill btn-sm-pill">
                    <span>Discuss Scope ↗</span>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="section-header" style="margin-bottom:20px;">
            <span class="eyebrow">COMMON QUESTIONS</span>
            <h2 class="section-title">Engineering Engagement FAQ</h2>
          </div>
          <div class="faq-list">
            <?php foreach ($faqs as $idx => $faq): ?>
              <div class="faq-item">
                <button type="button" class="faq-trigger" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>">
                  <span><?= e($faq['question']) ?></span>
                  <span class="faq-chevron"><?= $idx === 0 ? '−' : '+' ?></span>
                </button>
                <div class="faq-answer"<?= $idx === 0 ? '' : ' hidden' ?>><?= e($faq['answer']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
