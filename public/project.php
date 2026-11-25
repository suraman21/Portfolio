<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$slug = trim((string)($_GET['slug'] ?? ''));
$allProjects = get_projects(true, false);

$projIndex = -1;
foreach ($allProjects as $idx => $item) {
    if ($item['slug'] === $slug) {
        $projIndex = $idx;
        break;
    }
}

$proj = $projIndex >= 0 ? $allProjects[$projIndex] : null;
if (!$proj) {
    http_response_code(404);
}

$prevProj = ($proj && count($allProjects) > 1)
    ? $allProjects[($projIndex - 1 + count($allProjects)) % count($allProjects)]
    : null;
$nextProj = ($proj && count($allProjects) > 1)
    ? $allProjects[($projIndex + 1) % count($allProjects)]
    : null;

$activeNav = 'projects';
$isSubpage = true;
$pageTitle = ($proj ? $proj['title'] : 'Project Not Found') . ' — ' . ($site['name'] ?? 'Aarav Singh');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <?php if (!$proj): ?>
          <section class="glass-panel subpage-hero-panel">
            <span class="eyebrow">404 NOT FOUND</span>
            <h1 class="subpage-title">Project Case Study Not Found</h1>
            <p class="subpage-lead">We could not find a published project matching slug "<?= e($slug) ?>".</p>
            <div class="subpage-hero-meta">
              <a href="/projects" class="btn-dark-pill btn-sm-pill"><span>← Back to All Projects</span></a>
            </div>
          </section>
        <?php else: ?>
          <section class="glass-panel subpage-hero-panel">
            <div class="breadcrumb-row">
              <a href="/">Home</a>
              <span>›</span>
              <a href="/projects">Projects</a>
              <span>›</span>
              <span class="crumb-current"><?= e($proj['title']) ?></span>
            </div>
            <span class="eyebrow"><?= e(strtoupper($proj['category'] . ' • ' . $proj['year'])) ?></span>
            <h1 class="subpage-title"><?= e($proj['title']) ?></h1>
            <p class="subpage-lead"><?= e($proj['summary']) ?></p>

            <div class="dev-pill-row" style="margin-top:14px;">
              <span class="dev-pill dev-pill-accent">Role: <?= e($proj['role_name']) ?></span>
              <span class="dev-pill">Client: <?= e($proj['client_name']) ?></span>
              <?php foreach ($proj['stack'] as $s): ?>
                <span class="dev-pill"><?= e($s) ?></span>
              <?php endforeach; ?>
            </div>

            <div class="subpage-hero-meta">
              <button type="button" class="btn-dark-pill btn-sm-pill" data-live-preview="<?= e($proj['slug']) ?>">
                <span>Launch Interactive Preview ↗</span>
              </button>
              <?php if (!empty($proj['repo_url'])): ?>
                <a href="<?= e($proj['repo_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-glass-pill btn-sm-pill">
                  <span>View Repository</span>
                </a>
              <?php endif; ?>
              <a href="/projects" class="btn-glass-pill btn-sm-pill">
                <span>← All Projects</span>
              </a>
            </div>
          </section>

          <section class="glass-panel subpage-section-panel">
            <?php if (!empty($proj['metrics'])): ?>
              <div class="case-metrics-grid">
                <?php foreach ($proj['metrics'] as $m): ?>
                  <div class="case-metric-card">
                    <div class="case-metric-num"><?= e($m['value'] ?? '') ?></div>
                    <div class="case-metric-label"><?= e($m['label'] ?? '') ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="case-showcase-frame" style="margin-bottom:24px;">
              <img src="<?= e(optimized_image_path((string)$proj['image_path'])) ?>" alt="<?= e($proj['title']) ?>" loading="lazy" decoding="async" />
            </div>

            <div class="dev-tabs-bar" role="tablist" aria-label="Technical Case Study Sections">
              <button type="button" class="dev-tab-btn active" data-dev-tab="overview">Overview &amp; Solution</button>
              <button type="button" class="dev-tab-btn" data-dev-tab="architecture">System Architecture</button>
              <button type="button" class="dev-tab-btn" data-dev-tab="code">Code Highlights</button>
              <button type="button" class="dev-tab-btn" data-dev-tab="benchmarks">Benchmarks &amp; QA</button>
            </div>

            <!-- Panel 1: Overview -->
            <div data-panel="overview">
              <div class="case-two-col">
                <div class="case-prose-card">
                  <h4>Engineering Challenge</h4>
                  <p><?= e($proj['challenge']) ?></p>
                </div>
                <div class="case-prose-card">
                  <h4>Technical Solution</h4>
                  <p><?= e($proj['solution']) ?></p>
                </div>
              </div>
            </div>

            <!-- Panel 2: Architecture -->
            <div data-panel="architecture" hidden>
              <div class="arch-flow-grid">
                <?php foreach ($proj['architecture'] as $idx => $a): ?>
                  <div class="arch-node-card">
                    <div class="arch-node-layer">0<?= $idx + 1 ?> • <?= e($a['layer'] ?? '') ?></div>
                    <div class="arch-node-name"><?= e($a['name'] ?? '') ?></div>
                    <div class="arch-node-detail"><?= e($a['detail'] ?? '') ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Panel 3: Code Snippet -->
            <div data-panel="code" hidden>
              <div class="code-window">
                <div class="code-window-bar">
                  <div class="code-window-title">
                    <span class="b-dot dot-red"></span>
                    <span class="b-dot dot-amber"></span>
                    <span class="b-dot dot-green"></span>
                    <span style="margin-left:6px;"><?= e($proj['code_filename'] ?: 'snippet.ts') ?></span>
                  </div>
                  <button
                    type="button"
                    class="code-copy-btn"
                    data-copy-code="<?= e(rawurlencode((string)$proj['code_snippet'])) ?>"
                  >Copy Code</button>
                </div>
                <pre><code><?= e($proj['code_snippet']) ?></code></pre>
              </div>
            </div>

            <!-- Panel 4: Benchmarks -->
            <div data-panel="benchmarks" hidden>
              <div class="case-prose-card">
                <h4>Production Performance &amp; Quality Audit</h4>
                <?php foreach ($proj['benchmarks'] as $b): ?>
                  <div class="skill-bar-item" style="margin-top:12px;">
                    <div class="skill-bar-top">
                      <span><?= e($b['label'] ?? '') ?></span>
                      <span><?= (int)($b['score'] ?? 95) ?> / 100</span>
                    </div>
                    <div class="skill-bar-track">
                      <div class="skill-bar-fill" style="width:<?= max(0, min(100, (int)($b['score'] ?? 95))) ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <?php if ($prevProj && $nextProj): ?>
              <div style="margin-top:26px;padding-top:18px;border-top:1px solid rgba(190,192,216,0.45);display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <a href="/projects/<?= e($prevProj['slug']) ?>" class="btn-glass-pill btn-sm-pill">
                  <span>← Prev: <?= e($prevProj['title']) ?></span>
                </a>
                <a href="/projects/<?= e($nextProj['slug']) ?>" class="btn-dark-pill btn-sm-pill">
                  <span>Next: <?= e($nextProj['title']) ?> →</span>
                </a>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
