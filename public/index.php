<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$services = get_services(true);
$techItems = get_tech_items(true);
$featuredProjects = array_slice(get_projects(true, true), 0, 3);
if (count($featuredProjects) < 3) {
    $featuredProjects = array_slice(get_projects(true, false), 0, 3);
}
$processSteps = array_slice(get_process_steps(), 0, 5);
$testimonials = get_testimonials(true);

$activeNav = 'home';
$isSubpage = false;
$pageTitle = ($site['name'] ?? 'Jordan Lee') . ' — ' . ($site['role'] ?? 'Software & Web Developer');

require dirname(__DIR__) . '/includes/header.php';
?>
      <!-- ==================== 2. HERO SECTION ==================== -->
      <section class="glass-panel hero-panel" id="hero">
        <div class="hero-ambient-mist" aria-hidden="true"></div>

        <!-- Left Content -->
        <div class="hero-left">
          <span class="eyebrow hero-eyebrow"><?= e($site['eyebrow'] ?? "HELLO, I'M") ?></span>
          <h1 class="hero-title"><?= e($site['name'] ?? 'Jordan Lee') ?></h1>
          <h2 class="hero-subtitle"><?= e($site['role'] ?? 'Software & Web Developer') ?></h2>
          <p class="hero-desc">
            <?= nl2br(e($site['tagline'] ?? "I engineer scalable web applications and modern software\nthat are fast, intuitive and built with precision.")) ?>
          </p>

          <div class="hero-actions">
            <a href="/projects" class="btn-dark-pill">
              <span>View My Work</span>
              <svg viewBox="0 0 14 14" width="13" height="13" fill="none" aria-hidden="true">
                <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
            <a href="/resume.php" class="btn-glass-pill">
              <span>Download CV</span>
              <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true">
                <path d="M8 2.6V10.4M8 10.4L5.1 7.6M8 10.4L10.9 7.6M3 11.6V12.6C3 13.15 3.45 13.6 4 13.6H12C12.55 13.6 13 13.15 13 12.6V11.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          </div>

          <div class="engineering-focus">
            <span class="engineering-focus-label">Engineering focus</span>
            <div class="engineering-focus-items">
              <span>Reliable full-stack systems</span>
              <span>Accessible, responsive interfaces</span>
              <span>Secure APIs &amp; thoughtful delivery</span>
            </div>
          </div>
        </div>

        <!-- Right Visual Composition -->
        <div class="hero-right">
          <svg class="hero-glass-frame-svg" viewBox="0 0 640 578" fill="none" aria-hidden="true">
            <defs>
              <linearGradient id="frameFillGrad" x1="120" y1="88" x2="490" y2="540" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#f5f5fc" stop-opacity="0.58"/>
                <stop offset="48%" stop-color="#ebe8fa" stop-opacity="0.46"/>
                <stop offset="100%" stop-color="#dedff0" stop-opacity="0.32"/>
              </linearGradient>
              <linearGradient id="frameRimGrad" x1="80" y1="88" x2="530" y2="578" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
                <stop offset="50%" stop-color="#ffffff" stop-opacity="0.78"/>
                <stop offset="100%" stop-color="#ffffff" stop-opacity="0.92"/>
              </linearGradient>
              <radialGradient id="portraitWarmAura" cx="56%" cy="46%" r="44%">
                <stop offset="0%" stop-color="#f6efff" stop-opacity="0.88"/>
                <stop offset="65%" stop-color="#e8e3f8" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="#dadcf0" stop-opacity="0"/>
              </radialGradient>
              <radialGradient id="portraitBottomGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#9ca2e8" stop-opacity="0.48"/>
                <stop offset="60%" stop-color="#b4b8ee" stop-opacity="0.22"/>
                <stop offset="100%" stop-color="#b4b8ee" stop-opacity="0"/>
              </radialGradient>
              <clipPath id="portraitSCurveClip" clipPathUnits="userSpaceOnUse">
                <path d="M 0,0 L 640,0 L 640,88 L 460,88 C 506,88 520,135 492,218 C 466,295 420,356 378,425 C 333,498 284,548 232,576 L 24,576 C 6,576 -2,560 2,534 L 54,162 C 60,115 84,88 130,88 L 0,88 Z"/>
              </clipPath>
            </defs>
            <ellipse cx="165" cy="574" rx="175" ry="24" fill="url(#portraitBottomGlow)"/>
            <ellipse cx="295" cy="285" rx="220" ry="200" fill="url(#portraitWarmAura)"/>
            <path d="M 128,90 L 446,90 C 494,90 514,134 488,214 C 463,291 418,352 376,422 C 332,495 284,546 230,575 L 28,575 C 8,575 0,559 4,533 L 56,162 C 63,116 88,90 128,90 Z" fill="url(#frameFillGrad)" stroke="url(#frameRimGrad)" stroke-width="2.6"/>
            <path d="M 130,95 L 443,95 C 488,95 507,137 482,214 C 458,289 413,350 372,419 C 328,491 281,541 228,570 L 32,570 C 14,570 7,555 10,531 L 61,164 C 68,120 91,95 130,95 Z" fill="none" stroke="rgba(255,255,255,0.42)" stroke-width="1.2"/>
            <g clip-path="url(#portraitSCurveClip)">
              <image href="<?= e(optimized_image_path((string)($site['portrait_path'] ?? '/assets/profile-cutout.png'))) ?>" x="18" y="-6" width="484" height="590" preserveAspectRatio="xMidYMid meet"/>
            </g>
            <path d="M 488,214 C 463,291 418,352 376,422 C 332,495 284,546 230,575 L 28,575 C 8,575 0,559 4,533" fill="none" stroke="rgba(255,255,255,0.88)" stroke-width="2.4" stroke-linecap="round"/>
          </svg>

          <div class="hero-sparkle-orb" aria-hidden="true">
            <svg viewBox="0 0 92 92" width="90" height="90" fill="none">
              <defs>
                <radialGradient id="orbGlassBg" cx="38%" cy="32%" r="65%">
                  <stop offset="0%" stop-color="#ffffff" stop-opacity="0.92"/>
                  <stop offset="52%" stop-color="#e9e6f8" stop-opacity="0.68"/>
                  <stop offset="85%" stop-color="#cfc9ee" stop-opacity="0.58"/>
                  <stop offset="100%" stop-color="#ffffff" stop-opacity="0.9"/>
                </radialGradient>
                <linearGradient id="sparkleGrad" x1="26" y1="28" x2="62" y2="64" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#67e8f9"/>
                  <stop offset="55%" stop-color="#8b7ef6"/>
                  <stop offset="100%" stop-color="#38bdf8"/>
                </linearGradient>
              </defs>
              <circle cx="46" cy="46" r="42" fill="url(#orbGlassBg)" stroke="rgba(255,255,255,0.92)" stroke-width="1.8"/>
              <path d="M22 25C30 15 48 12 63 18" stroke="rgba(255,255,255,0.95)" stroke-width="3" stroke-linecap="round"/>
              <path d="M72 30C77 40 77 54 70 65" stroke="rgba(255,255,255,0.85)" stroke-width="2.4" stroke-linecap="round"/>
              <path d="M43 29C44.1 38.2 46.8 40.9 56 42C46.8 43.1 44.1 45.8 43 55C41.9 45.8 39.2 43.1 30 42C39.2 40.9 41.9 38.2 43 29Z" fill="url(#sparkleGrad)"/>
              <path d="M56.5 30C57.1 34.1 58.4 35.4 62.5 36C58.4 36.6 57.1 37.9 56.5 42C55.9 37.9 54.6 36.6 50.5 36C54.6 35.4 55.9 34.1 56.5 30Z" fill="#38bdf8" opacity="0.85"/>
            </svg>
          </div>

          <div class="hero-exp-card">
            <span class="exp-big"><?= e($site['hero_badge_years'] ?? '5+') ?></span>
            <span class="exp-sub">Years of</span>
            <span class="exp-bold">Experience</span>
          </div>

          <div class="hero-impact-card">
            <span class="impact-label"><?= e($site['hero_impact_label'] ?? 'System Impact') ?></span>
            <span class="impact-value"><?= e($site['hero_impact_value'] ?? '+120%') ?></span>
            <svg class="impact-chart" viewBox="0 0 180 54" fill="none" aria-hidden="true">
              <defs>
                <linearGradient id="impactArea" x1="90" y1="6" x2="90" y2="52" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#0ea5e9" stop-opacity="0.22"/>
                  <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <path d="M6 46 C 22 41, 32 35, 48 37 C 64 39, 76 28, 92 31 C 106 34, 118 22, 134 26 C 148 29, 160 15, 173 7 L 173 52 L 6 52 Z" fill="url(#impactArea)"/>
              <path d="M6 46 C 22 41, 32 35, 48 37 C 64 39, 76 28, 92 31 C 106 34, 118 22, 134 26 C 148 29, 160 15, 173 7" stroke="#0ea5e9" stroke-width="2.3" stroke-linecap="round"/>
              <circle cx="173" cy="7" r="2.3" fill="#0ea5e9"/>
            </svg>
          </div>
        </div>
      </section>

      <!-- ==================== 3. ABOUT ME SECTION ==================== -->
      <section class="glass-panel about-panel" id="about">
        <div class="about-diagonal-sheen" aria-hidden="true"></div>

        <div class="about-left">
          <span class="eyebrow">ABOUT ME</span>
          <h2 class="section-title about-heading">
            <?= e($site['about_heading_line1'] ?? 'Engineering with Precision') ?><br />
            <?= e($site['about_heading_line2'] ?? 'Building for Scale') ?>
          </h2>

          <div class="about-stats-card">
            <div class="stat-item">
              <div class="stat-icon-badge">
                <svg viewBox="0 0 20 20" width="17" height="17" fill="none" aria-hidden="true">
                  <rect x="2.5" y="2.5" width="15" height="15" rx="4" stroke="#575482" stroke-width="1.4"/>
                  <path d="M7.4 7.5L5.2 10L7.4 12.5M12.6 7.5L14.8 10L12.6 12.5M10.8 6.8L9.2 13.2" stroke="#575482" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="stat-num"><?= e($site['stat1_val'] ?? '5+') ?></div>
              <div class="stat-label"><?= e($site['stat1_label'] ?? 'Years Experience') ?></div>
            </div>

            <div class="stat-item">
              <div class="stat-icon-badge">
                <svg viewBox="0 0 20 20" width="17" height="17" fill="none" aria-hidden="true">
                  <rect x="2.8" y="5.8" width="14.4" height="10.8" rx="2.4" stroke="#575482" stroke-width="1.4"/>
                  <path d="M7.2 5.8V4.4C7.2 3.6 7.8 3 8.6 3H11.4C12.2 3 12.8 3.6 12.8 4.4V5.8" stroke="#575482" stroke-width="1.4"/>
                  <path d="M3 10.4H17" stroke="#575482" stroke-width="1.3"/>
                </svg>
              </div>
              <div class="stat-num"><?= e($site['stat2_val'] ?? '30+') ?></div>
              <div class="stat-label"><?= e($site['stat2_label'] ?? 'Systems Shipped') ?></div>
            </div>

            <div class="stat-item">
              <div class="stat-icon-badge">
                <svg viewBox="0 0 20 20" width="17" height="17" fill="none" aria-hidden="true">
                  <circle cx="10" cy="6.8" r="3" stroke="#575482" stroke-width="1.4"/>
                  <path d="M4.6 16.2C4.9 13.4 7.1 11.8 10 11.8C12.9 11.8 15.1 13.4 15.4 16.2" stroke="#575482" stroke-width="1.4" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="stat-num"><?= e($site['stat3_val'] ?? '15+') ?></div>
              <div class="stat-label"><?= e($site['stat3_label'] ?? 'Global Clients') ?></div>
            </div>
          </div>
        </div>

        <div class="about-right">
          <p class="about-bio"><?= e($site['short_bio'] ?? '') ?></p>
          <a href="/about" class="btn-glass-pill btn-sm-pill">
            <span>More About Me</span>
            <svg viewBox="0 0 14 14" width="12" height="12" fill="none" aria-hidden="true">
              <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>
      </section>

      <!-- ==================== 4. SERVICES SECTION ==================== -->
      <section class="glass-panel services-panel" id="services">
        <div class="services-header-row">
          <div class="section-header" style="margin-bottom:0;">
            <span class="eyebrow">WHAT I DO</span>
            <h2 class="section-title">Services I Offer</h2>
            <p class="section-subtext">End-to-end engineering services designed to ship reliable, scalable, and polished digital products.</p>
          </div>
          <a href="/services" class="btn-glass-pill btn-sm-pill services-all-btn">
            <span>Explore All Services</span>
            <svg viewBox="0 0 14 14" width="12" height="12" fill="none" aria-hidden="true">
              <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>

        <div class="services-grid">
          <?php foreach ($services as $idx => $srv): ?>
            <article class="service-card clickable-card" data-href="/services#<?= e($srv['slug']) ?>">
              <div class="service-card-top">
                <div class="service-icon-tile <?= e($srv['tile_class']) ?>">
                  <?= render_service_svg((string)$srv['icon_key'], (string)$srv['tile_class']) ?>
                </div>
                <span class="service-card-index">0<?= $idx + 1 ?></span>
              </div>
              <h3 class="service-title"><?= e($srv['title']) ?></h3>
              <p class="service-desc"><?= e($srv['short_desc']) ?></p>
              <div class="service-card-foot">
                <a href="/services#<?= e($srv['slug']) ?>" class="service-link-inline" aria-label="View <?= e($srv['title']) ?> details">
                  <span>Learn more</span>
                  <svg viewBox="0 0 14 14" width="12" height="12" fill="none" aria-hidden="true">
                    <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ==================== 5. TECHNOLOGIES I USE ==================== -->
      <section class="glass-panel tech-panel" id="skills">
        <div class="section-header">
          <span class="eyebrow">TOOLS &amp; SKILLS</span>
          <h2 class="section-title">Technologies I Use</h2>
        </div>

        <div class="tech-grid">
          <?php foreach ($techItems as $tech):
              $iconSpec = render_tech_tile_icon((string)$tech['icon_key'], (string)$tech['name']);
          ?>
            <div class="tech-card">
              <div class="tech-icon-wrap <?= e($iconSpec['wrapClass']) ?>"<?= $iconSpec['style'] ? ' style="' . e($iconSpec['style']) . '"' : '' ?>>
                <?= $iconSpec['svg'] ?>
              </div>
              <span class="tech-name"><?= e($tech['name']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ==================== 6. SELECTED WORK ==================== -->
      <section class="glass-panel work-panel" id="work">
        <div class="section-header-row">
          <div>
            <span class="eyebrow">FEATURED PROJECTS</span>
            <h2 class="section-title">Selected Work</h2>
          </div>
          <a href="/projects" class="btn-glass-pill btn-sm-pill">
            <span>View All Projects</span>
            <svg viewBox="0 0 14 14" width="12" height="12" fill="none" aria-hidden="true">
              <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>

        <div class="work-grid">
          <?php foreach ($featuredProjects as $proj): ?>
            <article class="work-card" data-href="/projects/<?= e($proj['slug']) ?>">
              <div class="work-thumb">
                <img src="<?= e(optimized_image_path((string)$proj['image_path'])) ?>" alt="<?= e($proj['title']) ?>" loading="lazy" decoding="async" />
              </div>
              <div class="work-footer">
                <div>
                  <h3 class="work-title"><?= e($proj['title']) ?></h3>
                  <span class="work-cat"><?= e($proj['short_category']) ?></span>
                </div>
                <a href="/projects/<?= e($proj['slug']) ?>" class="work-arrow-btn" aria-label="Open <?= e($proj['title']) ?>">
                  <svg viewBox="0 0 14 14" width="13" height="13" fill="none">
                    <path d="M3.5 10.5L10.5 3.5M10.5 3.5H4.8M10.5 3.5V9.2" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ==================== 7. ENGINEERING PROCESS ==================== -->
      <section class="glass-panel process-panel" id="process">
        <div class="process-header-row">
          <div class="section-header" style="margin-bottom:0;">
            <span class="eyebrow">MY PROCESS</span>
            <h2 class="section-title">A Clear Process, From Idea to Launch</h2>
            <p class="section-subtext">A structured workflow that keeps delivery transparent, efficient, and focused on quality outcomes.</p>
          </div>
        </div>

        <div class="process-grid">
          <?php foreach ($processSteps as $idx => $step):
              $isHighlight = ((int)$step['is_highlighted'] === 1);
          ?>
            <article class="process-card<?= $isHighlight ? ' process-card-highlight' : '' ?>">
              <div class="process-card-head">
                <span class="process-num<?= $isHighlight ? ' process-num-active' : '' ?>"><?= e($step['step_number']) ?></span>
                <div class="process-orb-icon<?= $isHighlight ? ' orb-glow' : '' ?>">
                  <?= render_process_svg((string)$step['icon_key']) ?>
                </div>
              </div>
              <div class="process-body">
                <h3 class="process-title"><?= e($step['title']) ?></h3>
                <p class="process-desc"><?= e($step['description']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ==================== 8. TESTIMONIALS ==================== -->
      <section class="glass-panel testimonials-panel" id="testimonials">
        <div class="section-header">
          <span class="eyebrow">TESTIMONIALS</span>
          <h2 class="section-title">What Clients Say</h2>
        </div>

        <div class="testimonials-row-wrap">
          <div class="testimonials-grid" id="testimonialsGrid">
            <?php foreach (array_slice($testimonials, 0, 3) as $tm): ?>
              <article class="testimonial-card">
                <span class="quote-glyph" aria-hidden="true">
                  <svg viewBox="0 0 24 18" width="19" height="15" fill="none">
                    <path d="M5.6 17C2.6 17 1 14.8 1 11.7C1 7.6 3.7 3.6 8.2 1.2L9.6 3.2C6.5 5 5.1 7.2 4.9 9.5C5.4 9.2 6.1 9.1 6.8 9.1C8.9 9.1 10.4 10.6 10.4 12.9C10.4 15.3 8.5 17 5.6 17ZM17.8 17C14.8 17 13.2 14.8 13.2 11.7C13.2 7.6 15.9 3.6 20.4 1.2L21.8 3.2C18.7 5 17.3 7.2 17.1 9.5C17.6 9.2 18.3 9.1 19 9.1C21.1 9.1 22.6 10.6 22.6 12.9C22.6 15.3 20.7 17 17.8 17Z" fill="<?= e($tm['quote_color'] ?: '#67e8f9') ?>"/>
                  </svg>
                </span>
                <p class="testimonial-text"><?= e($tm['quote_text']) ?></p>
                <div class="client-meta">
                  <img src="<?= e(optimized_image_path((string)$tm['avatar_path'])) ?>" alt="<?= e($tm['client_name']) ?>" class="client-avatar" width="40" height="40" loading="lazy" decoding="async" />
                  <div>
                    <div class="client-name"><?= e($tm['client_name']) ?></div>
                    <div class="client-role"><?= e($tm['client_role']) ?></div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <div class="testimonial-nav-btns">
            <button type="button" class="carousel-circle-btn" id="prevTestBtn" aria-label="Previous testimonials">
              <svg viewBox="0 0 14 14" width="12" height="12" fill="none">
                <path d="M8.8 3.2L5 7L8.8 10.8" stroke="#3c3f50" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <button type="button" class="carousel-circle-btn" id="nextTestBtn" aria-label="Next testimonials">
              <svg viewBox="0 0 14 14" width="12" height="12" fill="none">
                <path d="M5.2 3.2L9 7L5.2 10.8" stroke="#3c3f50" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>
        <script type="application/json" id="testimonialsDataJson"><?= json_encode($testimonials, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
      </section>

      <!-- ==================== 9. LET'S CONNECT ==================== -->
      <section class="glass-panel contact-panel" id="contact">
        <div class="contact-left">
          <span class="eyebrow">LET'S CONNECT</span>
          <h2 class="contact-title">
            Have a project in mind?<br />
            Let's build something scalable<br />
            together.
          </h2>

          <ul class="contact-info-list">
            <li>
              <span class="contact-icon-circle">
                <svg viewBox="0 0 18 18" width="15" height="15" fill="none" aria-hidden="true">
                  <rect x="2.2" y="3.8" width="13.6" height="10.4" rx="2.2" stroke="#3c3f52" stroke-width="1.35"/>
                  <path d="M2.8 4.8L9 9.4L15.2 4.8" stroke="#3c3f52" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <a href="mailto:<?= e($site['email'] ?? 'hello@example.com') ?>"><?= e($site['email'] ?? 'hello@example.com') ?></a>
            </li>
            <li>
              <span class="contact-icon-circle">
                <svg viewBox="0 0 18 18" width="15" height="15" fill="none" aria-hidden="true">
                  <path d="M4.2 3.4H6.6L7.8 6.3L6.2 7.4C7.1 9.2 8.6 10.7 10.4 11.6L11.5 10L14.4 11.2V13.6C14.4 14.3 13.8 14.9 13.1 14.9C7.8 14.6 3.2 10 2.9 4.7C2.9 4 3.5 3.4 4.2 3.4Z" stroke="#3c3f52" stroke-width="1.35" stroke-linejoin="round"/>
                </svg>
              </span>
              <a href="tel:<?= e(preg_replace('/\s+/', '', $site['phone'] ?? '+919876543210')) ?>"><?= e($site['phone'] ?? '+91 98765 43210') ?></a>
            </li>
            <li>
              <span class="contact-icon-circle">
                <svg viewBox="0 0 18 18" width="15" height="15" fill="none" aria-hidden="true">
                  <path d="M9 15.4C9 15.4 14 11.2 14 7.3C14 4.54 11.76 2.3 9 2.3C6.24 2.3 4 4.54 4 7.3C4 11.2 9 15.4 9 15.4Z" stroke="#3c3f52" stroke-width="1.35"/>
                  <circle cx="9" cy="7.3" r="1.9" stroke="#3c3f52" stroke-width="1.35"/>
                </svg>
              </span>
              <span><?= e($site['location'] ?? 'New Delhi, India') ?></span>
            </li>
          </ul>

        </div>

        <!-- Center Glass Form Card -->
        <form class="contact-form-card" id="contactForm" action="/api/contact.php" method="POST" data-ajax-contact="1">
          <div class="form-row-two">
            <input type="text" name="name" class="glass-input" placeholder="Your Name" required />
            <input type="email" name="email" class="glass-input" placeholder="Your Email" required />
          </div>

          <div class="select-wrap">
            <select name="project" class="glass-input glass-select" aria-label="Your Project">
              <option value="General Inquiry" selected>Your Project</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= e($srv['title']) ?>"><?= e($srv['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <svg class="select-chevron" viewBox="0 0 12 12" width="12" height="12" fill="none" aria-hidden="true">
              <path d="M3 4.5L6 7.5L9 4.5" stroke="#5b5e70" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>

          <textarea name="message" class="glass-input glass-textarea" placeholder="Your Message" rows="4" required></textarea>

          <button type="submit" class="btn-send-message">
            <span>Send Message</span>
            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true">
              <path d="M13.8 2.2L7.1 8.9M13.8 2.2L9.5 13.8L7.1 8.9M13.8 2.2L2.2 6.5L7.1 8.9" stroke="currentColor" stroke-width="1.45" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </form>

        <!-- Right 3D Iridescent Lavender Liquid-Glass Orb -->
        <div class="contact-orb-wrap" aria-hidden="true">
          <div class="orb-faint-meta">
            <span>Open to Work</span>
            <span class="orb-faint-lines"></span>
          </div>
          <img src="<?= e(optimized_image_path('/assets/contact-orb-cutout.png')) ?>" alt="" class="contact-orb-img" loading="lazy" decoding="async" />
        </div>
      </section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
