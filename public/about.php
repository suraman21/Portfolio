<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$workList = get_experiences_by_type('work');
$eduList = get_experiences_by_type('education');
$skillBars = get_skill_bars();
$techItems = get_tech_items(true);

$activeNav = 'about';
$isSubpage = true;
$pageTitle = 'About — ' . ($site['name'] ?? 'Aarav Singh');

$extendedParas = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string)($site['extended_bio'] ?? '')) ?: [])));

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <div class="breadcrumb-row">
            <a href="/">Home</a>
            <span>›</span>
            <span class="crumb-current">About &amp; Career</span>
          </div>
          <span class="eyebrow">SOFTWARE &amp; WEB ENGINEER</span>
          <h1 class="subpage-title">Engineering Scalable Software with Glass-Grade Craft</h1>
          <p class="subpage-lead"><?= e($site['short_bio'] ?? '') ?></p>

          <div class="subpage-hero-meta">
            <span class="availability-pill"><span class="avail-dot"></span> <?= e($site['availability'] ?? 'Open to Work') ?></span>
            <span class="dev-pill">📍 <?= e($site['location'] ?? 'New Delhi, India') ?></span>
            <a href="/resume.php" class="btn-dark-pill btn-sm-pill">
              <span>Download Resume</span>
              <svg viewBox="0 0 16 16" width="13" height="13" fill="none" aria-hidden="true">
                <path d="M8 2.6V10.4M8 10.4L5.1 7.6M8 10.4L10.9 7.6M3 11.6V12.6C3 13.15 3.45 13.6 4 13.6H12C12.55 13.6 13 13.15 13 12.6V11.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          </div>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="about-detail-grid">
            <div class="about-portrait-card">
              <img src="<?= e(optimized_image_path((string)($site['portrait_path'] ?? '/assets/aarav-cutout.png'))) ?>" alt="<?= e($site['name'] ?? 'Aarav Singh') ?>" loading="eager" fetchpriority="high" decoding="async" />
              <div style="margin-top:14px;width:100%;">
                <div style="font-size:17px;font-weight:700;color:#111422;"><?= e($site['name'] ?? 'Aarav Singh') ?></div>
                <div style="font-size:12.5px;color:#595d6f;margin-top:2px;"><?= e($site['role'] ?? 'Software & Web Developer') ?></div>
                <div class="dev-pill-row" style="margin-top:12px;">
                  <?php foreach (array_slice($techItems, 0, 4) as $t): ?>
                    <span class="dev-pill"><?= e($t['name']) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <div class="about-narrative-card">
              <span class="eyebrow">MY ENGINEERING PHILOSOPHY</span>
              <h3>Clean Architecture Meets Tactile Frontend Craft</h3>
              <?php foreach ($extendedParas as $para): ?>
                <p><?= e($para) ?></p>
              <?php endforeach; ?>

              <div class="case-metrics-grid" style="margin-top:18px;margin-bottom:0;">
                <div class="case-metric-card">
                  <div class="case-metric-num"><?= e($site['stat1_val'] ?? '5+') ?></div>
                  <div class="case-metric-label"><?= e($site['stat1_label'] ?? 'Years Experience') ?></div>
                </div>
                <div class="case-metric-card">
                  <div class="case-metric-num"><?= e($site['stat2_val'] ?? '30+') ?></div>
                  <div class="case-metric-label"><?= e($site['stat2_label'] ?? 'Systems Shipped') ?></div>
                </div>
                <div class="case-metric-card">
                  <div class="case-metric-num">99.98%</div>
                  <div class="case-metric-label">Production Uptime</div>
                </div>
                <div class="case-metric-card">
                  <div class="case-metric-num">99/100</div>
                  <div class="case-metric-label">Core Web Vitals</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="section-header" style="margin-bottom:20px;">
            <span class="eyebrow">CAREER TIMELINE</span>
            <h2 class="section-title">Engineering Experience</h2>
          </div>
          <div class="timeline-list">
            <?php foreach ($workList as $exp): ?>
              <article class="timeline-card">
                <div class="timeline-head">
                  <div>
                    <div class="timeline-role"><?= e($exp['title']) ?></div>
                    <div class="timeline-company"><?= e($exp['organization']) ?></div>
                  </div>
                  <span class="timeline-period"><?= e($exp['period']) ?></span>
                </div>
                <p class="timeline-summary"><?= e($exp['summary']) ?></p>
                <?php if (!empty($exp['highlights'])): ?>
                  <ul class="timeline-bullets">
                    <?php foreach ($exp['highlights'] as $h): ?>
                      <li><?= e($h) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="section-header" style="margin-bottom:20px;">
            <span class="eyebrow">CAPABILITIES MATRIX</span>
            <h2 class="section-title">Technical Proficiency &amp; Education</h2>
          </div>

          <div class="skills-matrix-grid">
            <?php foreach ($skillBars as $sb): ?>
              <div class="skill-group-card">
                <div class="skill-group-title"><?= e($sb['group_title']) ?></div>
                <?php foreach ($sb['items'] as $it): ?>
                  <div class="skill-bar-item">
                    <div class="skill-bar-top">
                      <span><?= e($it['name']) ?></span>
                      <?php $proficiency = max(0, min(100, (int)$it['pct'])); ?>
                      <span><?= $proficiency ?>%</span>
                    </div>
                    <div class="skill-bar-track">
                      <div class="skill-bar-fill" style="width:<?= $proficiency ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="timeline-list" style="margin-top:20px;">
            <?php foreach ($eduList as $ed): ?>
              <div class="timeline-card" style="padding:18px 22px;">
                <div class="timeline-head" style="margin-bottom:4px;">
                  <div>
                    <div class="timeline-role" style="font-size:15.5px;"><?= e($ed['title']) ?></div>
                    <div class="timeline-company"><?= e($ed['organization']) ?></div>
                  </div>
                  <span class="timeline-period"><?= e($ed['period']) ?></span>
                </div>
                <p class="timeline-summary" style="margin-bottom:0;"><?= e($ed['summary']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
