<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$slug = trim((string)($_GET['slug'] ?? ''));
$post = $slug !== '' ? get_blog_post_by_slug($slug) : null;
$allPosts = get_blog_posts(true);

if (!$post) {
    http_response_code(404);
}

$activeNav = 'blog';
$isSubpage = true;
$pageTitle = ($post ? $post['title'] : 'Article Not Found') . ' — ' . ($site['name'] ?? 'Aarav Singh');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <?php if (!$post): ?>
          <section class="glass-panel subpage-hero-panel">
            <span class="eyebrow">404 NOT FOUND</span>
            <h1 class="subpage-title">Article Not Found</h1>
            <div class="subpage-hero-meta">
              <a href="/blog" class="btn-dark-pill btn-sm-pill"><span>← Back to Blog</span></a>
            </div>
          </section>
        <?php else: ?>
          <section class="glass-panel subpage-hero-panel">
            <div class="breadcrumb-row">
              <a href="/">Home</a>
              <span>›</span>
              <a href="/blog">Blog</a>
              <span>›</span>
              <span class="crumb-current"><?= e($post['category']) ?></span>
            </div>
            <span class="eyebrow"><?= e(strtoupper($post['category'] . ' • ' . $post['published_date'] . ' • ' . $post['read_time'])) ?></span>
            <h1 class="subpage-title"><?= e($post['title']) ?></h1>
            <p class="subpage-lead"><?= e($post['excerpt']) ?></p>
            <div class="dev-pill-row" style="margin-top:14px;">
              <?php foreach ($post['tags'] as $t): ?>
                <span class="dev-pill"><?= e($t) ?></span>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="glass-panel subpage-section-panel">
            <div class="article-layout">
              <div class="article-prose-card">
                <?php foreach ($post['sections'] as $idx => $sec): ?>
                  <div id="sec-<?= $idx ?>">
                    <h2><?= e($sec['heading'] ?? '') ?></h2>
                    <p><?= e($sec['body'] ?? '') ?></p>
                    <?php if (!empty($sec['code'])): ?>
                      <div class="code-window" style="margin-bottom:20px;">
                        <div class="code-window-bar">
                          <div class="code-window-title">
                            <span class="b-dot dot-red"></span>
                            <span class="b-dot dot-amber"></span>
                            <span class="b-dot dot-green"></span>
                            <span style="margin-left:6px;"><?= e($sec['codeTitle'] ?? 'example.ts') ?></span>
                          </div>
                          <button
                            type="button"
                            class="code-copy-btn"
                            data-copy-code="<?= e(rawurlencode((string)$sec['code'])) ?>"
                          >Copy Code</button>
                        </div>
                        <pre><code><?= e($sec['code']) ?></code></pre>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>

              <aside class="article-toc-card">
                <h4>On This Page</h4>
                <ul>
                  <?php foreach ($post['sections'] as $idx => $sec): ?>
                    <li><a href="#sec-<?= $idx ?>"><?= e($sec['heading'] ?? '') ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <div style="margin-top:18px;padding-top:14px;border-top:1px solid rgba(190,192,216,0.4);">
                  <div style="font-size:12px;font-weight:700;color:#141726;margin-bottom:8px;">More Engineering Notes</div>
                  <?php foreach ($allPosts as $other):
                      if ($other['slug'] === $post['slug']) continue;
                  ?>
                    <a href="/blog/<?= e($other['slug']) ?>" style="display:block;font-size:12.5px;color:#55596c;text-decoration:none;margin-bottom:8px;line-height:1.4;">
                      → <?= e($other['title']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </aside>
            </div>
          </section>
        <?php endif; ?>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
