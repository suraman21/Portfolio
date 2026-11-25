<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';
require_once dirname(__DIR__, 2) . '/includes/admin_parsers.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $kind = (string)($_POST['kind'] ?? '');

    if ($kind === 'save_tech') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim((string)($_POST['name'] ?? ''));
        $iconKey = trim((string)($_POST['icon_key'] ?? 'code'));
        if (!isset(tech_icon_options()[$iconKey])) {
            set_flash('error', 'Choose a valid technology icon preset.');
            redirect_to('/admin/skills.php');
        }
        if ($name !== '') {
            save_tech_item([
                'name' => $name,
                'icon_key' => $iconKey,
                'category' => trim((string)($_POST['category'] ?? 'Core')),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ], $id);
            set_flash('success', $id ? 'Technology updated.' : 'Technology added.');
        }
        redirect_to('/admin/skills.php');
    }

    if ($kind === 'delete_tech') {
        delete_tech_item((int)($_POST['id'] ?? 0));
        set_flash('success', 'Technology deleted.');
        redirect_to('/admin/skills.php');
    }

    if ($kind === 'save_bar') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $groupTitle = trim((string)($_POST['group_title'] ?? ''));
        if ($groupTitle !== '') {
            save_skill_bar([
                'group_title' => $groupTitle,
                'items' => parse_skill_bar_lines((string)($_POST['items_lines'] ?? '')),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ], $id);
            set_flash('success', 'Skill proficiency group saved.');
        }
        redirect_to('/admin/skills.php#bars');
    }

    if ($kind === 'delete_bar') {
        delete_skill_bar((int)($_POST['id'] ?? 0));
        set_flash('success', 'Skill group removed.');
        redirect_to('/admin/skills.php#bars');
    }

    if ($kind === 'save_step') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title !== '') {
            save_process_step([
                'step_number' => trim((string)($_POST['step_number'] ?? '01')),
                'title' => $title,
                'description' => trim((string)($_POST['description'] ?? '')),
                'icon_key' => trim((string)($_POST['icon_key'] ?? 'code')),
                'is_highlighted' => isset($_POST['is_highlighted']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ], $id);
            set_flash('success', 'Engineering process step saved.');
        }
        redirect_to('/admin/skills.php#process');
    }
}

$techItems = get_tech_items(false);
$techIconOptions = tech_icon_options();
$skillBars = get_skill_bars();
$steps = get_process_steps();

$editTech = null;
if (isset($_GET['edit_tech'])) {
    foreach ($techItems as $t) {
        if ((int)$t['id'] === (int)$_GET['edit_tech']) $editTech = $t;
    }
}
$editBar = null;
if (isset($_GET['edit_bar'])) {
    foreach ($skillBars as $b) {
        if ((int)$b['id'] === (int)$_GET['edit_bar']) $editBar = $b;
    }
}
$editStep = null;
if (isset($_GET['edit_step'])) {
    foreach ($steps as $st) {
        if ((int)$st['id'] === (int)$_GET['edit_step']) $editStep = $st;
    }
}

$adminPage = 'skills';
$adminEyebrow = 'TECHNOLOGIES & WORKFLOW';
$adminTitle = 'Skills Matrix & Engineering Process';

require __DIR__ . '/_layout_top.php';
?>
      <!-- 1. HOME TECHNOLOGIES -->
      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Home "Technologies I Use" Grid (<?= count($techItems) ?>)</h2>
            <p class="adm-card-sub">All active technologies appear in the responsive Home page grid. Use the icon presets to keep the visuals consistent.</p>
          </div>
        </div>

        <form method="POST" action="/admin/skills.php" class="adm-form-grid-3" style="margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid rgba(190,194,222,0.5);">
          <?= csrf_field() ?>
          <input type="hidden" name="kind" value="save_tech" />
          <input type="hidden" name="id" value="<?= e($editTech['id'] ?? '') ?>" />

          <div class="adm-field">
            <label class="adm-label">Technology Name</label>
            <input type="text" name="name" class="adm-input" value="<?= e($editTech['name'] ?? '') ?>" placeholder="TypeScript" required />
          </div>

          <div class="adm-field">
            <label class="adm-label">Icon Preset</label>
            <select name="icon_key" class="adm-select">
              <?php foreach ($techIconOptions as $ik => $label): ?>
                <option value="<?= e($ik) ?>"<?= (($editTech['icon_key'] ?? 'react') === $ik) ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="adm-field">
            <label class="adm-label">Sort Order</label>
            <input type="number" name="sort_order" class="adm-input" value="<?= (int)($editTech['sort_order'] ?? (count($techItems) + 1)) ?>" />
          </div>

          <div class="adm-field span-full" style="flex-direction:row;align-items:center;gap:14px;">
            <label class="adm-check-row">
              <input type="checkbox" name="is_active" value="1"<?= ((int)($editTech['is_active'] ?? 1) === 1) ? ' checked' : '' ?> />
              <span>Active</span>
            </label>
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><?= $editTech ? 'Update Tech' : '+ Add Tech' ?></button>
            <?php if ($editTech): ?>
              <a href="/admin/skills.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
            <?php endif; ?>
          </div>
        </form>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Icon Key</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($techItems as $t): ?>
                <tr>
                  <td>#<?= (int)$t['sort_order'] ?></td>
                  <td style="font-weight:650;"><?= e($t['name']) ?></td>
                  <td><code><?= e($t['icon_key']) ?></code></td>
                  <td><?= (int)$t['is_active'] === 1 ? '<span class="adm-badge badge-green">Active</span>' : '<span class="adm-badge badge-amber">Hidden</span>' ?></td>
                  <td class="adm-actions-inline">
                    <a href="/admin/skills.php?edit_tech=<?= (int)$t['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/skills.php" onsubmit="return confirm('Delete this item?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="kind" value="delete_tech" />
                      <input type="hidden" name="id" value="<?= (int)$t['id'] ?>" />
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 2. SKILL PROFICIENCY BARS -->
      <section class="adm-card" id="bars">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">About Page Skill Proficiency Bars</h2>
            <p class="adm-card-sub">Format each line as <code>Skill Name | Percentage</code> (e.g. <code>PHP 8 / SQL | 96</code>).</p>
          </div>
        </div>

        <form method="POST" action="/admin/skills.php" class="adm-form-grid" style="margin-bottom:18px;">
          <?= csrf_field() ?>
          <input type="hidden" name="kind" value="save_bar" />
          <input type="hidden" name="id" value="<?= e($editBar['id'] ?? '') ?>" />
          <div class="adm-field">
            <label class="adm-label">Group Heading</label>
            <input type="text" name="group_title" class="adm-input" value="<?= e($editBar['group_title'] ?? '') ?>" placeholder="Backend & Databases" required />
          </div>
          <div class="adm-field">
            <label class="adm-label">Sort Order</label>
            <input type="number" name="sort_order" class="adm-input" value="<?= (int)($editBar['sort_order'] ?? (count($skillBars) + 1)) ?>" />
          </div>
          <div class="adm-field span-full">
            <label class="adm-label">Skills &amp; Percentages (one per line)</label>
            <textarea name="items_lines" class="adm-textarea" rows="4" required><?= e(format_skill_bar_lines($editBar['items'] ?? [])) ?></textarea>
          </div>
          <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><?= $editBar ? 'Update Group' : '+ Add Group' ?></button>
            <?php if ($editBar): ?>
              <a href="/admin/skills.php#bars" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
            <?php endif; ?>
          </div>
        </form>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Group</th>
                <th>Skills</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($skillBars as $sb): ?>
                <tr>
                  <td style="font-weight:650;"><?= e($sb['group_title']) ?></td>
                  <td><?= e(implode(', ', array_map(fn($i) => $i['name'] . ' (' . $i['pct'] . '%)', $sb['items']))) ?></td>
                  <td class="adm-actions-inline">
                    <a href="/admin/skills.php?edit_bar=<?= (int)$sb['id'] ?>#bars" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/skills.php" onsubmit="return confirm('Delete group?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="kind" value="delete_bar" />
                      <input type="hidden" name="id" value="<?= (int)$sb['id'] ?>" />
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 3. PROCESS STEPS -->
      <section class="adm-card" id="process">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Home 5-Step Engineering Process</h2>
            <p class="adm-card-sub">Click Edit on any step to customize its title, description, or glow highlight.</p>
          </div>
        </div>

        <?php if ($editStep): ?>
          <form method="POST" action="/admin/skills.php" class="adm-form-grid" style="margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid rgba(190,194,222,0.5);">
            <?= csrf_field() ?>
            <input type="hidden" name="kind" value="save_step" />
            <input type="hidden" name="id" value="<?= (int)$editStep['id'] ?>" />
            <div class="adm-field">
              <label class="adm-label">Step Number</label>
              <input type="text" name="step_number" class="adm-input" value="<?= e($editStep['step_number']) ?>" />
            </div>
            <div class="adm-field">
              <label class="adm-label">Step Title</label>
              <input type="text" name="title" class="adm-input" value="<?= e($editStep['title']) ?>" required />
            </div>
            <div class="adm-field">
              <label class="adm-label">Description</label>
              <input type="text" name="description" class="adm-input" value="<?= e($editStep['description']) ?>" required />
            </div>
            <div class="adm-field">
              <label class="adm-label">Icon Key</label>
              <select name="icon_key" class="adm-select">
                <?php foreach (['arch', 'proto', 'frontend', 'backend', 'qa'] as $ik): ?>
                  <option value="<?= e($ik) ?>"<?= ($editStep['icon_key'] === $ik) ? ' selected' : '' ?>><?= e($ik) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="adm-field">
              <label class="adm-label">Sort Order</label>
              <input type="number" name="sort_order" class="adm-input" value="<?= (int)$editStep['sort_order'] ?>" />
            </div>
            <div class="adm-field" style="justify-content:center;">
              <label class="adm-check-row" style="margin-top:18px;">
                <input type="checkbox" name="is_highlighted" value="1"<?= ((int)$editStep['is_highlighted'] === 1) ? ' checked' : '' ?> />
                <span>Active Glow Highlight</span>
              </label>
            </div>
            <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
              <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm">Save Step</button>
              <a href="/admin/skills.php#process" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
            </div>
          </form>
        <?php endif; ?>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Step</th>
                <th>Title</th>
                <th>Description</th>
                <th>Highlight</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($steps as $st): ?>
                <tr>
                  <td><strong><?= e($st['step_number']) ?></strong></td>
                  <td style="font-weight:650;"><?= e($st['title']) ?></td>
                  <td><?= e($st['description']) ?></td>
                  <td><?= (int)$st['is_highlighted'] === 1 ? '<span class="adm-badge badge-purple">Highlighted</span>' : '—' ?></td>
                  <td><a href="/admin/skills.php?edit_step=<?= (int)$st['id'] ?>#process" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
