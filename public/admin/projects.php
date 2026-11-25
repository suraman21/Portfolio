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
            set_flash('error', 'Project title is required.');
            redirect_to('/admin/projects.php');
        }
        $slug = trim((string)($_POST['slug'] ?? ''));
        $slug = $slug !== '' ? slugify($slug) : slugify($title);

        $imagePath = trim((string)($_POST['existing_image'] ?? '/assets/work-finova.png'));
        try {
            $uploaded = handle_image_upload('project_image', 'proj');
            if ($uploaded !== null) {
                $imagePath = $uploaded;
            }
        } catch (RuntimeException $ex) {
            set_flash('error', $ex->getMessage());
            redirect_to('/admin/projects.php' . ($id ? '?edit=' . $id : '?action=new'));
        }

        save_project([
            'slug' => $slug,
            'title' => $title,
            'category' => trim((string)($_POST['category'] ?? 'SaaS Platform')),
            'short_category' => trim((string)($_POST['short_category'] ?? 'Full-Stack SaaS')),
            'year' => trim((string)($_POST['year'] ?? date('Y'))),
            'client_name' => trim((string)($_POST['client_name'] ?? '')),
            'role_name' => trim((string)($_POST['role_name'] ?? 'Lead Software Engineer')),
            'image_path' => $imagePath,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'challenge' => trim((string)($_POST['challenge'] ?? '')),
            'solution' => trim((string)($_POST['solution'] ?? '')),
            'stack' => parse_csv_tags((string)($_POST['stack_csv'] ?? '')),
            'metrics' => parse_metrics_lines((string)($_POST['metrics_lines'] ?? '')),
            'architecture' => parse_architecture_lines((string)($_POST['architecture_lines'] ?? '')),
            'code_filename' => trim((string)($_POST['code_filename'] ?? 'snippet.ts')),
            'code_snippet' => (string)($_POST['code_snippet'] ?? ''),
            'benchmarks' => parse_benchmarks_lines((string)($_POST['benchmarks_lines'] ?? '')),
            'live_url' => trim((string)($_POST['live_url'] ?? '')),
            'repo_url' => trim((string)($_POST['repo_url'] ?? '')),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ], $id);

        set_flash('success', $id ? 'Project case study updated!' : 'New portfolio project created!');
        redirect_to('/admin/projects.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_project($id);
            set_flash('success', 'Project deleted.');
        }
        redirect_to('/admin/projects.php');
    }
}

$adminPage = 'projects';
$adminEyebrow = 'PORTFOLIO / SELECTED WORKS';
$adminTitle = 'Manage Portfolio Projects';

$projects = get_projects(false, false);
$editing = isset($_GET['edit']) ? get_project_by_id((int)$_GET['edit']) : null;
$showNew = isset($_GET['action']) && $_GET['action'] === 'new';

require __DIR__ . '/_layout_top.php';
?>
      <?php if ($editing || $showNew):
          $pr = $editing ?? [
              'id' => '',
              'slug' => '',
              'title' => '',
              'category' => 'SaaS Platform',
              'short_category' => 'Full-Stack SaaS',
              'year' => '2026',
              'client_name' => '',
              'role_name' => 'Lead Full-Stack Engineer',
              'image_path' => '/assets/work-finova.png',
              'summary' => '',
              'challenge' => '',
              'solution' => '',
              'stack' => ['Next.js', 'TypeScript', 'PostgreSQL'],
              'metrics' => [['value' => '99.98%', 'label' => 'Uptime SLA'], ['value' => '110ms', 'label' => 'P95 Latency']],
              'architecture' => [['layer' => 'Frontend', 'name' => 'Next.js App Router', 'detail' => 'Server components & typed UI']],
              'code_filename' => 'app/api/handler.ts',
              'code_snippet' => "export async function GET() {\n  return Response.json({ ok: true });\n}",
              'benchmarks' => [['label' => 'Lighthouse Performance', 'score' => 99]],
              'live_url' => '',
              'repo_url' => '',
              'is_featured' => 1,
              'is_published' => 1,
              'sort_order' => count($projects) + 1,
          ];
      ?>
        <section class="adm-card">
          <div class="adm-card-head">
            <div>
              <h2 class="adm-card-title"><?= $editing ? 'Edit Project: ' . e($pr['title']) : 'Add Portfolio Project / Case Study' ?></h2>
              <p class="adm-card-sub">Upload a project screenshot or specify an asset path, plus full case-study details.</p>
            </div>
            <a href="/admin/projects.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
          </div>

          <form method="POST" action="/admin/projects.php" enctype="multipart/form-data" class="adm-form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="save" />
            <input type="hidden" name="id" value="<?= e($pr['id']) ?>" />

            <div class="adm-field">
              <label class="adm-label">Project Title *</label>
              <input type="text" name="title" class="adm-input" value="<?= e($pr['title']) ?>" required />
            </div>

            <div class="adm-field">
              <label class="adm-label">URL Slug (/projects/{slug})</label>
              <input type="text" name="slug" class="adm-input" value="<?= e($pr['slug']) ?>" placeholder="auto-generated-from-title" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Archive Filter Category</label>
              <input type="text" name="category" class="adm-input" value="<?= e($pr['category']) ?>" placeholder="SaaS Platform / Mobile & FinTech / Cloud & APIs" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Home Card Subtitle</label>
              <input type="text" name="short_category" class="adm-input" value="<?= e($pr['short_category']) ?>" placeholder="Full-Stack SaaS" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Year</label>
              <input type="text" name="year" class="adm-input" value="<?= e($pr['year']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Client / Organization</label>
              <input type="text" name="client_name" class="adm-input" value="<?= e($pr['client_name']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Engineering Role</label>
              <input type="text" name="role_name" class="adm-input" value="<?= e($pr['role_name']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Tech Stack (comma-separated)</label>
              <input type="text" name="stack_csv" class="adm-input" value="<?= e(implode(', ', $pr['stack'] ?? [])) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Upload New Thumbnail Image (PNG/JPG/WebP, max 5 MB)</label>
              <input type="file" name="project_image" accept="image/png,image/jpeg,image/webp,image/gif" class="adm-input" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Current / Fallback Image Path</label>
              <input type="text" name="existing_image" class="adm-input" value="<?= e($pr['image_path']) ?>" />
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Executive Summary</label>
              <textarea name="summary" class="adm-textarea" rows="2" required><?= e($pr['summary']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Engineering Challenge</label>
              <textarea name="challenge" class="adm-textarea" rows="4"><?= e($pr['challenge']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Technical Solution</label>
              <textarea name="solution" class="adm-textarea" rows="4"><?= e($pr['solution']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Key Metrics (one per line: <code>Value | Label</code>)</label>
              <textarea name="metrics_lines" class="adm-textarea" rows="4"><?= e(format_metrics_lines($pr['metrics'] ?? [])) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Benchmarks (one per line: <code>Label | Score 0-100</code>)</label>
              <textarea name="benchmarks_lines" class="adm-textarea" rows="4"><?= e(format_benchmarks_lines($pr['benchmarks'] ?? [])) ?></textarea>
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">System Architecture Nodes (one per line: <code>Layer | Component Name | Technical Detail</code>)</label>
              <textarea name="architecture_lines" class="adm-textarea" rows="4"><?= e(format_architecture_lines($pr['architecture'] ?? [])) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Code Snippet Filename</label>
              <input type="text" name="code_filename" class="adm-input" value="<?= e($pr['code_filename']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Sort Order</label>
              <input type="number" name="sort_order" class="adm-input" value="<?= (int)$pr['sort_order'] ?>" />
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Code Highlight Snippet</label>
              <textarea name="code_snippet" class="adm-textarea code-font" rows="6"><?= e($pr['code_snippet']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Live Demo URL</label>
              <input type="text" name="live_url" class="adm-input" value="<?= e($pr['live_url']) ?>" placeholder="https://app.example.com" />
            </div>

            <div class="adm-field">
              <label class="adm-label">GitHub Repository URL</label>
              <input type="text" name="repo_url" class="adm-input" value="<?= e($pr['repo_url']) ?>" placeholder="https://github.com/..." />
            </div>

            <div class="adm-field">
              <label class="adm-check-row">
                <input type="checkbox" name="is_featured" value="1"<?= ((int)$pr['is_featured'] === 1) ? ' checked' : '' ?> />
                <span>★ Feature in Home "Selected Work" (top 3 shown)</span>
              </label>
            </div>

            <div class="adm-field">
              <label class="adm-check-row">
                <input type="checkbox" name="is_published" value="1"<?= ((int)$pr['is_published'] === 1) ? ' checked' : '' ?> />
                <span>Published on Public Site</span>
              </label>
            </div>

            <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
              <button type="submit" class="adm-btn adm-btn-primary"><?= $editing ? 'Save Project Case Study' : 'Create Project' ?></button>
              <a href="/admin/projects.php" class="adm-btn adm-btn-glass">Cancel</a>
            </div>
          </form>
        </section>
      <?php endif; ?>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">All Portfolio Projects (<?= count($projects) ?>)</h2>
            <p class="adm-card-sub">Manage your Selected Works on the Home page and full Case Studies on /projects.</p>
          </div>
          <a href="/admin/projects.php?action=new" class="adm-btn adm-btn-primary adm-btn-sm">+ Add Project</a>
        </div>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Thumbnail</th>
                <th>Title &amp; Slug</th>
                <th>Category</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($projects as $p): ?>
                <tr>
                  <td>#<?= (int)$p['sort_order'] ?></td>
                  <td><img src="<?= e($p['image_path']) ?>" alt="" class="adm-thumb-mini" /></td>
                  <td>
                    <div style="font-weight:650;"><?= e($p['title']) ?></div>
                    <div style="font-size:11.5px;color:#6d7288;">/projects/<?= e($p['slug']) ?></div>
                  </td>
                  <td><?= e($p['category']) ?></td>
                  <td>
                    <?php if ((int)$p['is_featured'] === 1): ?>
                      <span class="adm-badge badge-purple">★ Home</span>
                    <?php else: ?>
                      <span class="adm-badge badge-slate">Archive</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="adm-badge <?= (int)$p['is_published'] === 1 ? 'badge-green' : 'badge-amber' ?>">
                      <?= (int)$p['is_published'] === 1 ? 'Published' : 'Draft' ?>
                    </span>
                  </td>
                  <td class="adm-actions-inline">
                    <a href="/admin/projects.php?edit=<?= (int)$p['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <a href="/projects/<?= e($p['slug']) ?>" target="_blank" class="adm-btn adm-btn-glass adm-btn-sm">View ↗</a>
                    <form method="POST" action="/admin/projects.php" onsubmit="return confirm('Delete this project?');" style="display:inline;">
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
