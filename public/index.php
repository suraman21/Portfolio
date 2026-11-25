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
      <!-- ==================== HERO ==================== -->
      <section class="neon-hero" id="hero">
        <div class="hero-glow hero-glow-orange" aria-hidden="true"></div>
        <div class="hero-glow hero-glow-cyan" aria-hidden="true"></div>
        <div class="hero-left">
          <p class="neon-eyebrow"><span class="eyebrow-dot"></span><?= e($site['eyebrow'] ?? "Hello, I'm") ?></p>
          <h1 class="hero-name"><?= e($site['name'] ?? 'Your Name') ?></h1>
          <h2 class="hero-role"><span class="role-accent"><?= e($site['role'] ?? 'Software & Web Developer') ?></span></h2>
          <p class="hero-lead"><?= nl2br(e($site['tagline'] ?? 'I engineer scalable web applications and modern software that are fast, intuitive and built with precision.')) ?></p>
          <div class="hero-actions">
            <a href="/projects" class="btn-neon btn-neon-primary">
              <span>View My Work</span>
              <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="/resume.php" class="btn-neon btn-neon-outline">
              <span>Download CV</span>
              <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true"><path d="M8 2.5v8M5.5 8l2.5 2.5L10.5 8M3 12.5h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
          <div class="hero-socials">
            <?php $gh = trim((string)($site['github_url'] ?? '')); $li = trim((string)($site['linkedin_url'] ?? '')); $tw = trim((string)($site['twitter_url'] ?? '#')); $em = trim((string)($site['email'] ?? '')); ?>
            <a href="<?= e($gh ?: '#') ?>" class="social-orb" aria-label="GitHub" <?= $gh ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
            </a>
            <a href="<?= e($li ?: '#') ?>" class="social-orb" aria-label="LinkedIn" <?= $li ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="<?= e($em ? 'mailto:'.$em : '#') ?>" class="social-orb" aria-label="Email">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
            </a>
            <a href="/contact" class="social-orb" aria-label="Contact">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </a>
          </div>
        </div>

        <div class="hero-right">
          <div class="hero-portrait-wrap">
            <div class="portrait-ring" aria-hidden="true"></div>
            <div class="portrait-ring portrait-ring-inner" aria-hidden="true"></div>
            <div class="portrait-glow" aria-hidden="true"></div>
            <div class="portrait-frame">
              <img src="<?= e(optimized_image_path((string)($site['portrait_path'] ?? '/assets/profile-cutout.png'))) ?>" alt="<?= e($site['name'] ?? 'Portrait') ?>" class="portrait-img" loading="eager" decoding="async">
            </div>
            <div class="hero-float-badge hero-float-code" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="26" height="26" fill="none"><path d="M8 16l-4-4 4-4M16 8l4 4-4 4M14 4l-4 16" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="hero-float-badge hero-float-motto">
              <span class="motto-build">Build.</span>
              <span class="motto-innovate">Innovate.</span>
              <span class="motto-grow">Grow.</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== ABOUT ==================== -->
      <section class="neon-section" id="about">
        <div class="neon-section-inner about-grid">
          <div class="about-copy">
            <p class="neon-eyebrow"><span class="eyebrow-line"></span>About Me</p>
            <h2 class="neon-title"><?= e($site['about_heading_line1'] ?? 'Engineering with Precision') ?><br><span class="title-accent"><?= e($site['about_heading_line2'] ?? 'Building for Better Tomorrow') ?></span></h2>
            <p class="neon-body"><?= e($site['short_bio'] ?? '') ?></p>
            <div class="about-stats">
              <div class="about-stat"><span class="about-stat-num"><?= e($site['stat1_val'] ?? '5+') ?></span><span class="about-stat-label"><?= e($site['stat1_label'] ?? 'Years Experience') ?></span></div>
              <div class="about-stat"><span class="about-stat-num"><?= e($site['stat2_val'] ?? '30+') ?></span><span class="about-stat-label"><?= e($site['stat2_label'] ?? 'Systems Shipped') ?></span></div>
              <div class="about-stat"><span class="about-stat-num"><?= e($site['stat3_val'] ?? '15+') ?></span><span class="about-stat-label"><?= e($site['stat3_label'] ?? 'Global Clients') ?></span></div>
            </div>
            <a href="/about" class="btn-neon btn-neon-primary btn-neon-sm"><span>More About Me</span><svg viewBox="0 0 16 16" width="13" height="13" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          </div>
          <div class="about-visual">
            <div class="about-image-card neon-card">
              <img src="<?= e(optimized_image_path((string)($site['about_image_path'] ?? $site['portrait_path'] ?? '/assets/profile-cutout.png'))) ?>" alt="About <?= e($site['name'] ?? '') ?>" loading="lazy" decoding="async">
              <div class="about-image-overlay" aria-hidden="true"></div>
              <div class="about-image-badge"><span class="brand-initials"><?= e(strtoupper(substr(trim((string)($site['name'] ?? 'P')),0,1) . substr(explode(' ',trim((string)($site['name'] ?? 'P P')))[1] ?? 'P',0,1))) ?></span></div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== SERVICES ==================== -->
      <section class="neon-section" id="services">
        <div class="neon-section-inner">
          <div class="section-head-row">
            <div>
              <p class="neon-eyebrow"><span class="eyebrow-line"></span>My Services</p>
              <h2 class="neon-title">What I <span class="title-accent">Offer</span></h2>
              <p class="neon-body section-lead">End-to-end engineering services designed to ship reliable, scalable, and polished digital products.</p>
            </div>
            <a href="/services" class="btn-neon btn-neon-outline btn-neon-sm"><span>Explore Services</span><svg viewBox="0 0 16 16" width="13" height="13" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          </div>
          <div class="services-neon-grid">
            <?php foreach (array_slice($services, 0, 4) as $idx => $srv): ?>
            <article class="service-neon-card neon-card clickable-card" data-href="/services#<?= e($srv['slug']) ?>">
              <div class="service-neon-icon">
                <?= render_service_svg((string)$srv['icon_key'], (string)$srv['tile_class']) ?>
              </div>
              <h3 class="service-neon-title"><?= e($srv['title']) ?></h3>
              <p class="service-neon-desc"><?= e($srv['short_desc']) ?></p>
              <a href="/services#<?= e($srv['slug']) ?>" class="card-link">Learn more <svg viewBox="0 0 16 16" width="12" height="12" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- ==================== SKILLS ==================== -->
      <section class="neon-section" id="skills">
        <div class="neon-section-inner">
          <p class="neon-eyebrow"><span class="eyebrow-line"></span>My Skills</p>
          <h2 class="neon-title">Technologies <span class="title-accent">I Use</span></h2>
          <p class="neon-body section-lead">A modern stack to build powerful, scalable products.</p>
          <div class="skills-neon-row">
            <?php foreach ($techItems as $tech):
              $iconSpec = render_tech_tile_icon((string)$tech['icon_key'], (string)$tech['name']);
            ?>
            <div class="skill-neon-item" title="<?= e($tech['name']) ?>">
              <div class="skill-neon-icon <?= e($iconSpec['wrapClass']) ?>"<?= $iconSpec['style'] ? ' style="' . e($iconSpec['style']) . '"' : '' ?>>
                <?= $iconSpec['svg'] ?>
              </div>
              <span class="skill-neon-name"><?= e($tech['name']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="skills-cta"><a href="/services#skills" class="btn-neon btn-neon-outline btn-neon-sm"><span>View All Skills</span></a></div>
        </div>
      </section>

      <!-- ==================== PROJECTS ==================== -->
      <section class="neon-section" id="work">
        <div class="neon-section-inner">
          <div class="section-head-row">
            <div>
              <p class="neon-eyebrow"><span class="eyebrow-line"></span>Featured Projects</p>
              <h2 class="neon-title">Selected <span class="title-accent">Work</span></h2>
            </div>
            <a href="/projects" class="btn-neon btn-neon-outline btn-neon-sm"><span>View All Projects</span><svg viewBox="0 0 16 16" width="13" height="13" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          </div>
          <div class="projects-neon-grid">
            <?php foreach ($featuredProjects as $proj): ?>
            <article class="project-neon-card neon-card">
              <div class="project-neon-thumb">
                <img src="<?= e(optimized_image_path((string)$proj['image_path'])) ?>" alt="<?= e($proj['title']) ?>" loading="lazy" decoding="async">
                <div class="project-thumb-glow" aria-hidden="true"></div>
              </div>
              <div class="project-neon-body">
                <div>
                  <h3 class="project-neon-title"><?= e($proj['title']) ?></h3>
                  <p class="project-neon-cat"><?= e($proj['short_category']) ?></p>
                </div>
                <a href="/projects/<?= e($proj['slug']) ?>" class="project-neon-arrow" aria-label="Open <?= e($proj['title']) ?>">
                  <svg viewBox="0 0 16 16" width="14" height="14" fill="none"><path d="M4 12L12 4M12 4H6M12 4v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- ==================== PROCESS ==================== -->
      <section class="neon-section" id="process">
        <div class="neon-section-inner">
          <div class="section-center-head">
            <p class="neon-eyebrow neon-eyebrow-center"><span class="eyebrow-line"></span>My Process<span class="eyebrow-line"></span></p>
            <h2 class="neon-title">A Clear Process, From <span class="title-accent">Idea</span> to <span class="title-accent-cyan">Launch</span></h2>
            <p class="neon-body section-lead">A structured workflow that keeps delivery transparent, efficient, and focused on quality.</p>
          </div>
          <div class="process-neon-track">
            <div class="process-neon-line" aria-hidden="true"></div>
            <?php foreach (array_slice($processSteps, 0, 4) as $idx => $step): ?>
            <article class="process-neon-step">
              <div class="process-neon-node">
                <span class="process-neon-num"><?= e($step['step_number']) ?></span>
                <div class="process-neon-icon"><?= render_process_svg((string)$step['icon_key']) ?></div>
              </div>
              <h3 class="process-neon-title"><?= e($step['title']) ?></h3>
              <p class="process-neon-desc"><?= e($step['description']) ?></p>
            </article>
            <?php endforeach; ?>
            <div class="process-neon-rocket" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none"><defs><linearGradient id="rocketG" x1="32" y1="8" x2="32" y2="56"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#f97316"/></linearGradient></defs><path d="M32 6c8 6 14 16 14 28 0 4-1 8-3 11H21c-2-3-3-7-3-11 0-12 6-22 14-28z" fill="url(#rocketG)" opacity="0.95"/><circle cx="32" cy="26" r="5" fill="#061224"/><path d="M21 39l-7 8 9-2M43 39l7 8-9-2" stroke="#f97316" stroke-width="2.5" stroke-linecap="round"/><path d="M28 45c1 5 2 8 4 11 2-3 3-6 4-11" fill="#f97316"/></svg>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== STATS / ACHIEVEMENTS ==================== -->
      <section class="neon-section" id="achievements">
        <div class="neon-section-inner">
          <div class="section-center-head">
            <p class="neon-eyebrow neon-eyebrow-center"><span class="eyebrow-line"></span>My Achievements<span class="eyebrow-line"></span></p>
            <h2 class="neon-title">Trusted by <span class="title-accent">Clients</span>, Proven by <span class="title-accent-cyan">Results</span></h2>
          </div>
          <div class="stats-neon-grid">
            <div class="stat-neon-card neon-card"><div class="stat-neon-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4zM7 6H4v2a3 3 0 003 3M17 6h3v2a3 3 0 01-3 3" stroke="#f97316" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="stat-neon-value"><?= e($site['stat1_val'] ?? '5+') ?></div><div class="stat-neon-label"><?= e($site['stat1_label'] ?? 'Years of Experience') ?></div></div>
            <div class="stat-neon-card neon-card"><div class="stat-neon-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M4 17l6-6 4 4 6-8M14 7h6v6" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="stat-neon-value"><?= e($site['stat2_val'] ?? '50+') ?></div><div class="stat-neon-label"><?= e($site['stat2_label'] ?? 'Projects Completed') ?></div></div>
            <div class="stat-neon-card neon-card"><div class="stat-neon-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M12 21s-7-4.4-9-9a5 5 0 019-5 5 5 0 019 5c-2 4.6-9 9-9 9z" stroke="#f97316" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="stat-neon-value"><?= e($site['stat3_val'] ?? '100%') ?></div><div class="stat-neon-label"><?= e($site['stat3_label'] ?? 'Client Satisfaction') ?></div></div>
            <div class="stat-neon-card neon-card"><div class="stat-neon-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="stat-neon-value">24/7</div><div class="stat-neon-label">Always Learning</div></div>
          </div>
        </div>
      </section>

      <!-- ==================== TESTIMONIALS ==================== -->
      <section class="neon-section" id="testimonials">
        <div class="neon-section-inner">
          <div class="section-center-head">
            <p class="neon-eyebrow neon-eyebrow-center"><span class="eyebrow-line"></span>Testimonials<span class="eyebrow-line"></span></p>
            <h2 class="neon-title">What <span class="title-accent">Clients</span> Say</h2>
          </div>
          <div class="testimonials-neon-grid" id="testimonialsGrid">
            <?php foreach (array_slice($testimonials, 0, 3) as $tm): ?>
            <article class="testimonial-neon-card neon-card">
              <div class="testimonial-quote-mark">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="#f97316"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
              </div>
              <p class="testimonial-neon-text"><?= e($tm['quote_text']) ?></p>
              <div class="testimonial-neon-author">
                <img src="<?= e(optimized_image_path((string)$tm['avatar_path'])) ?>" alt="<?= e($tm['client_name']) ?>" class="testimonial-neon-avatar" width="44" height="44" loading="lazy" decoding="async">
                <div><div class="testimonial-neon-name"><?= e($tm['client_name']) ?></div><div class="testimonial-neon-role"><?= e($tm['client_role']) ?></div></div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
          <div class="testimonial-neon-nav">
            <button type="button" class="carousel-circle-btn neon-circle-btn" id="prevTestBtn" aria-label="Previous testimonials"><svg viewBox="0 0 14 14" width="14" height="14" fill="none"><path d="M9 2L4 7l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            <button type="button" class="carousel-circle-btn neon-circle-btn" id="nextTestBtn" aria-label="Next testimonials"><svg viewBox="0 0 14 14" width="14" height="14" fill="none"><path d="M5 2l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
          </div>
        </div>
        <script type="application/json" id="testimonialsDataJson"><?= json_encode($testimonials, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
      </section>

      <!-- ==================== CONTACT ==================== -->
      <section class="neon-section" id="contact">
        <div class="neon-section-inner contact-neon-grid">
          <div class="contact-neon-left">
            <p class="neon-eyebrow"><span class="eyebrow-line"></span>Let's Work Together</p>
            <h2 class="neon-title">Have a project in mind?<br><span class="title-accent">Let's build something amazing together.</span></h2>
            <div class="contact-neon-info">
              <a href="mailto:<?= e($site['email'] ?? 'hello@example.com') ?>" class="contact-info-row"><span class="contact-info-icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 7l9 6 9-6" stroke="currentColor" stroke-width="1.8"/></svg></span><span><?= e($site['email'] ?? 'hello@example.com') ?></span></a>
              <a href="tel:<?= e(preg_replace('/\s+/', '', $site['phone'] ?? '+251912345678')) ?>" class="contact-info-row"><span class="contact-info-icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.69 12a19.79 19.79 0 01-3.07-8.67A2 2 0 012.6 1.27h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 8.91a16 16 0 006.18 6.18l1-1a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="1.8"/></svg></span><span><?= e($site['phone'] ?? '+251 912 345 678') ?></span></a>
              <div class="contact-info-row"><span class="contact-info-icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.8"/></svg></span><span><?= e($site['location'] ?? 'Addis Ababa, Ethiopia') ?></span></div>
            </div>
            <div class="contact-globe" aria-hidden="true">
              <div class="globe-ring"></div>
              <div class="globe-ring globe-ring-2"></div>
              <div class="globe-core"><svg viewBox="0 0 120 120" width="140" height="140" fill="none"><circle cx="60" cy="60" r="46" stroke="rgba(56,189,248,0.4)" stroke-width="1.5"/><ellipse cx="60" cy="60" rx="22" ry="46" stroke="rgba(56,189,248,0.25)" stroke-width="1"/><ellipse cx="60" cy="60" rx="46" ry="18" stroke="rgba(56,189,248,0.2)" stroke-width="1"/><path d="M14 60h92M60 14v92" stroke="rgba(56,189,248,0.15)" stroke-width="1"/><circle cx="60" cy="60" r="46" fill="url(#globeFill)" opacity="0.15"/><defs><radialGradient id="globeFill" cx="40%" cy="35%" r="60%"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#061224" stop-opacity="0"/></radialGradient></defs></svg></div>
            </div>
          </div>
          <div class="contact-neon-right">
            <form class="contact-neon-form neon-card" id="contactForm" action="/api/contact.php" method="POST" data-ajax-contact="1">
              <div class="form-row-two">
                <label class="field"><span>Your Name</span><input type="text" name="name" class="neon-input" placeholder="John Doe" required></label>
                <label class="field"><span>Your Email</span><input type="email" name="email" class="neon-input" placeholder="john@example.com" required></label>
              </div>
              <label class="field"><span>Your Project</span>
                <select name="project" class="neon-input" aria-label="Your Project">
                  <option value="General Inquiry" selected>General Inquiry</option>
                  <?php foreach ($services as $srv): ?><option value="<?= e($srv['title']) ?>"><?= e($srv['title']) ?></option><?php endforeach; ?>
                </select>
              </label>
              <label class="field"><span>Your Message</span><textarea name="message" class="neon-input neon-textarea" placeholder="Tell me about your project..." rows="5" required></textarea></label>
              <button type="submit" class="btn-neon btn-neon-primary btn-full"><span>Send Message</span><svg viewBox="0 0 16 16" width="14" height="14" fill="none"><path d="M14 2L7 9M14 2l-5 12-2-5-5-2 12-5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            </form>
          </div>
        </div>
      </section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
