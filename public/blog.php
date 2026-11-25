<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$posts = get_blog_posts(true);
$cats = ['All'];
foreach ($posts as $p) {
    if (!in_array($p['category'], $cats, true)) {
        $cats[] = $p['category'];
    }
}

$activeNav = 'blog';
$isSubpage = true;
$pageTitle = 'Engineering Blog — ' . ($site['name'] ?? 'Aarav Singh');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <div class="breadcrumb-row">
            <a href="/">Home</a>
            <span>›</span>
            <span class="crumb-current">Engineering Notes</span>
          </div>
          <span class="eyebrow">TECHNICAL ARTICLES &amp; ARCHITECTURE</span>
          <h1 class="subpage-title">Developer Notes on Systems, Web &amp; UI Performance</h1>
          <p class="subpage-lead">
            Deep dives into distributed API design, multi-tenant SQL schemas, modern PHP &amp; TypeScript architecture, and GPU-smooth browser rendering.
          </p>
        </section>

        <section class="glass-panel subpage-section-panel">
          <div class="filter-toolbar">
            <div class="filter-pills" id="blogCatFilters">
              <?php foreach ($cats as $idx => $c): ?>
                <button type="button" class="filter-pill<?= $idx === 0 ? ' active' : '' ?>" data-blog-cat="<?= e($c) ?>"><?= e($c) ?></button>
              <?php endforeach; ?>
            </div>
            <input
              type="search"
              id="blogSearchInput"
              class="glass-input filter-search-input"
              placeholder="Search articles..."
              aria-label="Search blog articles"
            />
          </div>

          <div class="blog-grid" id="blogCardsGrid">
            <?php foreach ($posts as $post):
                $blob = implode(' ', [$post['title'], $post['excerpt'], implode(' ', $post['tags'])]);
            ?>
              <article
                class="blog-card"
                data-href="/blog/<?= e($post['slug']) ?>"
                data-bcat="<?= e($post['category']) ?>"
                data-bsearch="<?= e($blob) ?>"
                style="cursor:pointer;"
              >
                <div class="blog-meta-top">
                  <span><?= e($post['category']) ?></span>
                  <span>•</span>
                  <span><?= e($post['published_date']) ?></span>
                  <span>•</span>
                  <span><?= e($post['read_time']) ?></span>
                </div>
                <h3><?= e($post['title']) ?></h3>
                <p><?= e($post['excerpt']) ?></p>
                <div class="dev-pill-row" style="margin-top:auto;">
                  <?php foreach ($post['tags'] as $tg): ?>
                    <span class="dev-pill"><?= e($tg) ?></span>
                  <?php endforeach; ?>
                </div>
                <div style="padding-top:6px;">
                  <a href="/blog/<?= e($post['slug']) ?>" class="btn-glass-pill btn-sm-pill">
                    <span>Read Article →</span>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
