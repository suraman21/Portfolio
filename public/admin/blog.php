<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';
require_once dirname(__DIR__, 2) . '/includes/admin_parsers.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['form_action'] ?? 'save');

    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            set_flash('error', 'Article title is required.');
            redirect_to('/admin/blog.php');
        }
        $slug = trim((string)($_POST['slug'] ?? ''));
        $slug = $slug !== '' ? slugify($slug) : slugify($title);

        save_blog_post([
            'slug' => $slug,
            'title' => $title,
            'category' => trim((string)($_POST['category'] ?? 'Engineering')),
            'published_date' => trim((string)($_POST['published_date'] ?? date('M j, Y'))),
            'read_time' => trim((string)($_POST['read_time'] ?? '6 min read')),
            'excerpt' => trim((string)($_POST['excerpt'] ?? '')),
            'tags' => parse_csv_tags((string)($_POST['tags_csv'] ?? '')),
            'sections' => parse_blog_sections_text((string)($_POST['sections_text'] ?? '')),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ], $id);

        set_flash('success', $id ? 'Article updated!' : 'New developer article published!');
        redirect_to('/admin/blog.php');
    }

    if ($action === 'delete') {
        delete_blog_post((int)($_POST['id'] ?? 0));
        set_flash('success', 'Article deleted.');
        redirect_to('/admin/blog.php');
    }
}

$posts = get_blog_posts(false);
$editing = isset($_GET['edit']) ? get_blog_post_by_id((int)$_GET['edit']) : null;
$showNew = isset($_GET['action']) && $_GET['action'] === 'new';

$adminPage = 'blog';
$adminEyebrow = 'DEVELOPER BLOG';
$adminTitle = 'Manage Technical Articles';

require __DIR__ . '/_layout_top.php';
?>
      <?php if ($editing || $showNew):
          $po = $editing ?? [
              'id' => '',
              'slug' => '',
              'title' => '',
              'category' => 'Backend & APIs',
              'published_date' => date('M j, Y'),
              'read_time' => '6 min read',
              'excerpt' => '',
              'tags' => ['PHP 8', 'Architecture'],
              'sections' => [
                  [
                      'heading' => '1. Architectural Context',
                      'body' => 'Explain the core engineering tradeoff and system goal.',
                      'codeTitle' => 'src/service.php',
                      'code' => "<?php\n// Sample code block\n",
                  ],
              ],
              'is_published' => 1,
              'sort_order' => count($posts) + 1,
          ];
      ?>
        <section class="adm-card">
          <div class="adm-card-head">
            <div>
              <h2 class="adm-card-title"><?= $editing ? 'Edit Article: ' . e($po['title']) : 'Write New Developer Article' ?></h2>
              <p class="adm-card-sub">Separate article sections with <code>---</code> on its own line.</p>
            </div>
            <a href="/admin/blog.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
          </div>

          <form method="POST" action="/admin/blog.php" class="adm-form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="save" />
            <input type="hidden" name="id" value="<?= e($po['id']) ?>" />

            <div class="adm-field">
              <label class="adm-label">Article Title *</label>
              <input type="text" name="title" class="adm-input" value="<?= e($po['title']) ?>" required />
            </div>

            <div class="adm-field">
              <label class="adm-label">URL Slug (/blog/{slug})</label>
              <input type="text" name="slug" class="adm-input" value="<?= e($po['slug']) ?>" placeholder="auto-generated" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Category</label>
              <input type="text" name="category" class="adm-input" value="<?= e($po['category']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Published Date &amp; Read Time</label>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <input type="text" name="published_date" class="adm-input" value="<?= e($po['published_date']) ?>" />
                <input type="text" name="read_time" class="adm-input" value="<?= e($po['read_time']) ?>" />
              </div>
            </div>

            <div class="adm-field">
              <label class="adm-label">Tags (comma-separated)</label>
              <input type="text" name="tags_csv" class="adm-input" value="<?= e(implode(', ', $po['tags'] ?? [])) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Sort Order</label>
              <input type="number" name="sort_order" class="adm-input" value="<?= (int)$po['sort_order'] ?>" />
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Excerpt</label>
              <textarea name="excerpt" class="adm-textarea" rows="2" required><?= e($po['excerpt']) ?></textarea>
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Article Sections (Format: <code>Heading:</code>, <code>Body:</code>, optional <code>CodeTitle:</code> &amp; <code>Code:</code>, separated by <code>---</code>)</label>
              <textarea name="sections_text" class="adm-textarea code-font" rows="12" required><?= e(format_blog_sections_text($po['sections'] ?? [])) ?></textarea>
            </div>

            <div class="adm-field span-full">
              <label class="adm-check-row">
                <input type="checkbox" name="is_published" value="1"<?= ((int)$po['is_published'] === 1) ? ' checked' : '' ?> />
                <span>Published on /blog</span>
              </label>
            </div>

            <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
              <button type="submit" class="adm-btn adm-btn-primary"><?= $editing ? 'Update Article' : 'Publish Article' ?></button>
              <a href="/admin/blog.php" class="adm-btn adm-btn-glass">Cancel</a>
            </div>
          </form>
        </section>
      <?php endif; ?>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">All Developer Articles (<?= count($posts) ?>)</h2>
          </div>
          <a href="/admin/blog.php?action=new" class="adm-btn adm-btn-primary adm-btn-sm">+ Write Article</a>
        </div>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($posts as $p): ?>
                <tr>
                  <td>#<?= (int)$p['sort_order'] ?></td>
                  <td>
                    <div style="font-weight:650;"><?= e($p['title']) ?></div>
                    <div style="font-size:11.5px;color:#6d7288;">/blog/<?= e($p['slug']) ?></div>
                  </td>
                  <td><?= e($p['category']) ?></td>
                  <td><?= e($p['published_date']) ?></td>
                  <td><?= (int)$p['is_published'] === 1 ? '<span class="adm-badge badge-green">Published</span>' : '<span class="adm-badge badge-amber">Draft</span>' ?></td>
                  <td class="adm-actions-inline">
                    <a href="/admin/blog.php?edit=<?= (int)$p['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <a href="/blog/<?= e($p['slug']) ?>" target="_blank" class="adm-btn adm-btn-glass adm-btn-sm">Read ↗</a>
                    <form method="POST" action="/admin/blog.php" onsubmit="return confirm('Delete this article?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="form_action" value="delete" />
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>" />
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
