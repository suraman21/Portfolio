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
        $org = trim((string)($_POST['organization'] ?? ''));
        if ($title !== '' && $org !== '') {
            save_experience([
                'entry_type' => ($_POST['entry_type'] ?? 'work') === 'education' ? 'education' : 'work',
                'title' => $title,
                'organization' => $org,
                'period' => trim((string)($_POST['period'] ?? '')),
                'summary' => trim((string)($_POST['summary'] ?? '')),
                'highlights' => parse_lines_list((string)($_POST['highlights_lines'] ?? '')),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ], $id);
            set_flash('success', $id ? 'Timeline entry updated.' : 'Timeline entry created.');
        }
        redirect_to('/admin/experience.php');
    }

    if ($action === 'delete') {
        delete_experience((int)($_POST['id'] ?? 0));
        set_flash('success', 'Timeline entry deleted.');
        redirect_to('/admin/experience.php');
    }
}

$allEntries = array_merge(get_experiences_by_type('work'), get_experiences_by_type('education'));
$editing = null;
if (isset($_GET['edit'])) {
    foreach ($allEntries as $eItem) {
        if ((int)$eItem['id'] === (int)$_GET['edit']) $editing = $eItem;
    }
}

$adminPage = 'experience';
$adminEyebrow = 'RESUME & CREDENTIALS';
$adminTitle = 'Career Experience & Education';

require __DIR__ . '/_layout_top.php';
?>
      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title"><?= $editing ? 'Edit Entry: ' . e($editing['title']) : 'Add Experience or Education Entry' ?></h2>
            <p class="adm-card-sub">Appears on the /about Career Timeline and downloadable /resume.php file.</p>
          </div>
          <?php if ($editing): ?>
            <a href="/admin/experience.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
          <?php endif; ?>
        </div>

        <form method="POST" action="/admin/experience.php" class="adm-form-grid">
          <?= csrf_field() ?>
          <input type="hidden" name="form_action" value="save" />
          <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>" />

          <div class="adm-field">
            <label class="adm-label">Entry Type</label>
            <select name="entry_type" class="adm-select">
              <option value="work"<?= (($editing['entry_type'] ?? 'work') === 'work') ? ' selected' : '' ?>>Work Experience</option>
              <option value="education"<?= (($editing['entry_type'] ?? '') === 'education') ? ' selected' : '' ?>>Education / Certification</option>
            </select>
          </div>

          <div class="adm-field">
            <label class="adm-label">Period</label>
            <input type="text" name="period" class="adm-input" value="<?= e($editing['period'] ?? '') ?>" placeholder="2023 — Present" required />
          </div>

          <div class="adm-field">
            <label class="adm-label">Role / Degree Title *</label>
            <input type="text" name="title" class="adm-input" value="<?= e($editing['title'] ?? '') ?>" required />
          </div>

          <div class="adm-field">
            <label class="adm-label">Company / Institution *</label>
            <input type="text" name="organization" class="adm-input" value="<?= e($editing['organization'] ?? '') ?>" required />
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">Summary</label>
            <textarea name="summary" class="adm-textarea" rows="2" required><?= e($editing['summary'] ?? '') ?></textarea>
          </div>

          <div class="adm-field">
            <label class="adm-label">Key Bullet Highlights (one per line, optional for education)</label>
            <textarea name="highlights_lines" class="adm-textarea" rows="3"><?= e(implode("\n", $editing['highlights'] ?? [])) ?></textarea>
          </div>

          <div class="adm-field">
            <label class="adm-label">Sort Order</label>
            <input type="number" name="sort_order" class="adm-input" value="<?= (int)($editing['sort_order'] ?? 1) ?>" />
          </div>

          <div class="adm-field span-full">
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm" style="align-self:flex-start;">
              <?= $editing ? 'Update Entry' : '+ Add Entry' ?>
            </button>
          </div>
        </form>
      </section>

      <section class="adm-card">
        <div class="adm-card-head">
          <h2 class="adm-card-title">All Career &amp; Education Entries (<?= count($allEntries) ?>)</h2>
        </div>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Title &amp; Organization</th>
                <th>Period</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allEntries as $item): ?>
                <tr>
                  <td>
                    <span class="adm-badge <?= $item['entry_type'] === 'work' ? 'badge-purple' : 'badge-green' ?>">
                      <?= e(ucfirst($item['entry_type'])) ?>
                    </span>
                  </td>
                  <td>
                    <div style="font-weight:650;"><?= e($item['title']) ?></div>
                    <div style="font-size:12px;color:#6d7288;"><?= e($item['organization']) ?></div>
                  </td>
                  <td><?= e($item['period']) ?></td>
                  <td class="adm-actions-inline">
                    <a href="/admin/experience.php?edit=<?= (int)$item['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/experience.php" onsubmit="return confirm('Delete this entry?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="form_action" value="delete" />
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>" />
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
