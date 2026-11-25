<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';
require_once dirname(__DIR__, 2) . '/includes/admin_parsers.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $formType = (string)($_POST['form_type'] ?? 'service');

    if ($formType === 'save_service') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            set_flash('error', 'Service title is required.');
            redirect_to('/admin/services.php');
        }
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }
        $iconKey = trim((string)($_POST['icon_key'] ?? 'browser_code'));
        if (!isset(service_icon_options()[$iconKey])) {
            set_flash('error', 'Choose a valid service icon preset.');
            redirect_to('/admin/services.php');
        }
        save_service([
            'slug' => $slug,
            'title' => $title,
            'short_desc' => trim((string)($_POST['short_desc'] ?? '')),
            'tagline' => trim((string)($_POST['tagline'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'deliverables' => parse_lines_list((string)($_POST['deliverables_text'] ?? '')),
            'stack' => parse_csv_tags((string)($_POST['stack_csv'] ?? '')),
            'metrics_badge' => trim((string)($_POST['metrics_badge'] ?? '')),
            'icon_key' => $iconKey,
            'tile_class' => trim((string)($_POST['tile_class'] ?? 'tile-purple')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], $id);
        set_flash('success', $id ? 'Service updated successfully.' : 'New service created.');
        redirect_to('/admin/services.php');
    }

    if ($formType === 'delete_service') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_service($id);
            set_flash('success', 'Service removed.');
        }
        redirect_to('/admin/services.php');
    }

    if ($formType === 'save_faq') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $q = trim((string)($_POST['question'] ?? ''));
        $a = trim((string)($_POST['answer'] ?? ''));
        if ($q !== '' && $a !== '') {
            save_faq([
                'question' => $q,
                'answer' => $a,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ], $id);
            set_flash('success', $id ? 'FAQ updated.' : 'FAQ added.');
        }
        redirect_to('/admin/services.php#faqs');
    }

    if ($formType === 'delete_faq') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_faq($id);
            set_flash('success', 'FAQ deleted.');
        }
        redirect_to('/admin/services.php#faqs');
    }
}

$adminPage = 'services';
$adminEyebrow = 'SERVICES & ENGAGEMENT FAQ';
$adminTitle = 'Manage Services';

$services = get_services(false);
$faqs = get_faqs();
$editingService = isset($_GET['edit']) ? get_service_by_id((int)$_GET['edit']) : null;
$showNewForm = isset($_GET['action']) && $_GET['action'] === 'new';
$editingFaqId = isset($_GET['edit_faq']) ? (int)$_GET['edit_faq'] : 0;
$editingFaq = null;
foreach ($faqs as $fq) {
    if ((int)$fq['id'] === $editingFaqId) {
        $editingFaq = $fq;
        break;
    }
}

require __DIR__ . '/_layout_top.php';
?>
      <?php if ($editingService || $showNewForm):
          $sv = $editingService ?? [
              'id' => '',
              'slug' => '',
              'title' => '',
              'short_desc' => '',
              'tagline' => '',
              'description' => '',
              'deliverables' => [],
              'stack' => [],
              'metrics_badge' => '',
              'icon_key' => 'code',
              'tile_class' => 'tile-purple',
              'sort_order' => count($services) + 1,
              'is_active' => 1,
          ];
      ?>
        <section class="adm-card">
          <div class="adm-card-head">
            <div>
              <h2 class="adm-card-title"><?= $editingService ? 'Edit Service: ' . e($sv['title']) : 'Add New Engineering Service' ?></h2>
              <p class="adm-card-sub">Changes reflect immediately on the responsive Home services grid and the dedicated /services page.</p>
            </div>
            <a href="/admin/services.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
          </div>

          <form method="POST" action="/admin/services.php" class="adm-form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="form_type" value="save_service" />
            <input type="hidden" name="id" value="<?= e($sv['id']) ?>" />

            <div class="adm-field">
              <label class="adm-label">Service Title *</label>
              <input type="text" name="title" class="adm-input" value="<?= e($sv['title']) ?>" required />
            </div>

            <div class="adm-field">
              <label class="adm-label">URL Anchor Slug</label>
              <input type="text" name="slug" class="adm-input" value="<?= e($sv['slug']) ?>" placeholder="auto-generated-from-title" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Home Card Short Description (1–2 lines)</label>
              <input type="text" name="short_desc" class="adm-input" value="<?= e($sv['short_desc']) ?>" required />
            </div>

            <div class="adm-field">
              <label class="adm-label">Subpage Tagline</label>
              <input type="text" name="tagline" class="adm-input" value="<?= e($sv['tagline']) ?>" placeholder="Production SaaS & Modern Web Platforms" />
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Detailed Subpage Description</label>
              <textarea name="description" class="adm-textarea" rows="3"><?= e($sv['description']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Deliverables (one bullet per line)</label>
              <textarea name="deliverables_text" class="adm-textarea" rows="4"><?= e(implode("\n", $sv['deliverables'] ?? [])) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Tech Stack Pills (comma-separated)</label>
              <input type="text" name="stack_csv" class="adm-input" value="<?= e(implode(', ', $sv['stack'] ?? [])) ?>" placeholder="Next.js, TypeScript, PostgreSQL, Docker" />
              <label class="adm-label" style="margin-top:8px;">Performance / Metric Badge</label>
              <input type="text" name="metrics_badge" class="adm-input" value="<?= e($sv['metrics_badge']) ?>" placeholder="99+ Lighthouse • < 120ms TTFB" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Icon Artwork</label>
              <select name="icon_key" class="adm-select">
                <?php foreach (service_icon_options() as $k => $lbl): ?>
                  <option value="<?= e($k) ?>"<?= ($sv['icon_key'] === $k) ? ' selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="adm-field">
              <label class="adm-label">Glass Tile Accent</label>
              <select name="tile_class" class="adm-select">
                <?php foreach (['tile-amber' => 'Warm Amber Tile', 'tile-violet' => 'Iridescent Violet Tile', 'tile-blue' => 'Sapphire Blue Tile', 'tile-cyan' => 'Frost Cyan Tile'] as $k => $lbl): ?>
                  <option value="<?= e($k) ?>"<?= ($sv['tile_class'] === $k) ? ' selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="adm-field">
              <label class="adm-label">Sort Order</label>
              <input type="number" name="sort_order" class="adm-input" value="<?= (int)$sv['sort_order'] ?>" />
            </div>

            <div class="adm-field" style="justify-content:center;">
              <label class="adm-check-row" style="margin-top:18px;">
                <input type="checkbox" name="is_active" value="1"<?= ((int)$sv['is_active'] === 1) ? ' checked' : '' ?> />
                <span>Visible on Public Website</span>
              </label>
            </div>

            <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
              <button type="submit" class="adm-btn adm-btn-primary"><?= $editingService ? 'Save Service Changes' : 'Create Service' ?></button>
              <a href="/admin/services.php" class="adm-btn adm-btn-glass">Cancel</a>
            </div>
          </form>
        </section>
      <?php endif; ?>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">All Services (<?= count($services) ?>)</h2>
            <p class="adm-card-sub">All active services ordered by Sort Order appear on the Home page.</p>
          </div>
          <a href="/admin/services.php?action=new" class="adm-btn adm-btn-primary adm-btn-sm">+ Add Service</a>
        </div>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Service</th>
                <th>Home Summary</th>
                <th>Stack</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($services as $s): ?>
                <tr>
                  <td>#<?= (int)$s['sort_order'] ?></td>
                  <td>
                    <div style="font-weight:650;"><?= e($s['title']) ?></div>
                    <div style="font-size:11.5px;color:#6d7288;">#<?= e($s['slug']) ?></div>
                  </td>
                  <td style="max-width:280px;"><?= e($s['short_desc']) ?></td>
                  <td><?= e(implode(', ', array_slice($s['stack'], 0, 3))) ?></td>
                  <td>
                    <span class="adm-badge <?= (int)$s['is_active'] === 1 ? 'badge-green' : 'badge-amber' ?>">
                      <?= (int)$s['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                    </span>
                  </td>
                  <td class="adm-actions-inline">
                    <a href="/admin/services.php?edit=<?= (int)$s['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/services.php" onsubmit="return confirm('Delete this service?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="form_type" value="delete_service" />
                      <input type="hidden" name="id" value="<?= (int)$s['id'] ?>" />
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="adm-card" id="faqs">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Services Page FAQs (<?= count($faqs) ?>)</h2>
            <p class="adm-card-sub">Manage frequently asked questions displayed at the bottom of /services.</p>
          </div>
        </div>

        <form method="POST" action="/admin/services.php" class="adm-form-grid" style="margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid rgba(190,194,222,0.5);">
          <?= csrf_field() ?>
          <input type="hidden" name="form_type" value="save_faq" />
          <input type="hidden" name="id" value="<?= e($editingFaq['id'] ?? '') ?>" />
          <div class="adm-field">
            <label class="adm-label"><?= $editingFaq ? 'Edit Question' : 'New Question' ?></label>
            <input type="text" name="question" class="adm-input" value="<?= e($editingFaq['question'] ?? '') ?>" required />
          </div>
          <div class="adm-field">
            <label class="adm-label">Sort Order</label>
            <input type="number" name="sort_order" class="adm-input" value="<?= (int)($editingFaq['sort_order'] ?? (count($faqs) + 1)) ?>" />
          </div>
          <div class="adm-field span-full">
            <label class="adm-label">Answer</label>
            <textarea name="answer" class="adm-textarea" rows="2" required><?= e($editingFaq['answer'] ?? '') ?></textarea>
          </div>
          <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><?= $editingFaq ? 'Update FAQ' : '+ Add FAQ' ?></button>
            <?php if ($editingFaq): ?>
              <a href="/admin/services.php#faqs" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
            <?php endif; ?>
          </div>
        </form>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($faqs as $f): ?>
                <tr>
                  <td>#<?= (int)$f['sort_order'] ?></td>
                  <td style="font-weight:600;max-width:260px;"><?= e($f['question']) ?></td>
                  <td style="max-width:420px;"><?= e($f['answer']) ?></td>
                  <td class="adm-actions-inline">
                    <a href="/admin/services.php?edit_faq=<?= (int)$f['id'] ?>#faqs" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/services.php" onsubmit="return confirm('Delete this FAQ?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="form_type" value="delete_faq" />
                      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>" />
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
