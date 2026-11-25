<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$projects = get_projects(true, false);

$categories = ['All'];
$stacks = ['All Stack'];
foreach ($projects as $p) {
    if (!in_array($p['category'], $categories, true)) {
        $categories[] = $p['category'];
    }
    foreach ($p['stack'] as $st) {
        if (!in_array($st, $stacks, true) && count($stacks) < 10) {
            $stacks[] = $st;
        }
    }
}

$activeNav = 'projects';
$isSubpage = true;
$pageTitle = 'Projects — ' . ($site['name'] ?? 'Jordan Lee');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <div class="breadcrumb-row">
            <a href="/">Home</a>
            <span>›</span>
            <span class="crumb-current">Projects &amp; Case Studies</span>
          </div>
          <span class="eyebrow">PRODUCTION SOFTWARE ARCHIVE</span>
          <h1 class="subpage-title">Selected Engineering Works &amp; Case Studies</h1>
          <p class="subpage-lead">
            Explore full-stack SaaS platforms, distributed developer infrastructure, mobile fintech apps, and AI developer tools—complete with system architecture, real code snippets, and verified benchmarks.
          </p>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="filter-toolbar">
            <div class="filter-pills" id="projCatFilters" role="tablist" aria-label="Filter projects by category">
              <?php foreach ($categories as $idx => $cat): ?>
                <button type="button" class="filter-pill<?= $idx === 0 ? ' active' : '' ?>" data-cat="<?= e($cat) ?>"><?= e($cat) ?></button>
              <?php endforeach; ?>
            </div>
            <input
              type="search"
              id="projSearchInput"
              class="glass-input filter-search-input"
              placeholder="Search stack (Next.js, PHP, Go, PostgreSQL...)"
              aria-label="Search projects"
            />
          </div>

          <div class="dev-pill-row" id="projStackFilters" style="margin-bottom:20px;">
            <?php foreach ($stacks as $idx => $st): ?>
              <button
                type="button"
                class="dev-pill"
                data-stack="<?= e($st) ?>"
                style="cursor:pointer;<?= $idx === 0 ? 'background:#161a2c;color:#fff;border-color:#161a2c;' : '' ?>"
              ><?= e($st) ?></button>
            <?php endforeach; ?>
          </div>

          <div class="projects-archive-grid" id="projectsArchiveGrid">
            <?php foreach ($projects as $p):
                $searchBlob = implode(' ', [$p['title'], $p['summary'], $p['category'], implode(' ', $p['stack'])]);
            ?>
              <article
                class="project-archive-card"
                data-href="/projects/<?= e($p['slug']) ?>"
                data-card-cat="<?= e($p['category']) ?>"
                data-card-stacks="<?= e(implode('|', $p['stack'])) ?>"
                data-card-search="<?= e($searchBlob) ?>"
                style="cursor:pointer;"
              >
                <div class="project-archive-thumb">
                  <img src="<?= e(optimized_image_path((string)$p['image_path'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy" decoding="async" />
                </div>
                <div class="project-archive-body">
                  <div class="project-meta-top">
                    <span><?= e($p['category']) ?></span>
                    <span>•</span>
                    <span><?= e($p['year']) ?></span>
                  </div>
                  <h3><?= e($p['title']) ?></h3>
                  <p><?= e($p['summary']) ?></p>

                  <div class="dev-pill-row">
                    <?php foreach (array_slice($p['stack'], 0, 5) as $s): ?>
                      <span class="dev-pill"><?= e($s) ?></span>
                    <?php endforeach; ?>
                  </div>

                  <div class="project-card-actions">
                    <a href="/projects/<?= e($p['slug']) ?>" class="btn-dark-pill btn-sm-pill">
                      <span>Case Study &amp; Code</span>
                    </a>
                    <button type="button" class="btn-glass-pill btn-sm-pill" data-live-preview="<?= e($p['slug']) ?>">
                      <span>Live Preview</span>
                    </button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <div id="projectsEmptyNotice" hidden style="padding:36px;text-align:center;color:#55596b;">
            No projects matched that filter combination.
          </div>
        </section>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
