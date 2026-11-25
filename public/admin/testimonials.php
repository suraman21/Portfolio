<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['form_action'] ?? 'save');

    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $clientName = trim((string)($_POST['client_name'] ?? ''));
        $quoteText = trim((string)($_POST['quote_text'] ?? ''));
        if ($clientName === '' || $quoteText === '') {
            set_flash('error', 'Client name and testimonial quote are required.');
            redirect_to('/admin/testimonials.php');
        }

        $avatarPath = trim((string)($_POST['existing_avatar'] ?? '/assets/avatar-rohit.png'));
        try {
            $uploaded = handle_image_upload('avatar_image', 'avatar');
            if ($uploaded !== null) {
                $avatarPath = $uploaded;
            }
        } catch (RuntimeException $ex) {
            set_flash('error', $ex->getMessage());
            redirect_to('/admin/testimonials.php');
        }

        save_testimonial([
            'client_name' => $clientName,
            'client_role' => trim((string)($_POST['client_role'] ?? '')),
            'quote_text' => $quoteText,
            'avatar_path' => $avatarPath,
            'quote_color' => trim((string)($_POST['quote_color'] ?? '#67e8f9')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], $id);

        set_flash('success', $id ? 'Testimonial updated!' : 'New testimonial added!');
        redirect_to('/admin/testimonials.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_testimonial($id);
            set_flash('success', 'Testimonial deleted.');
        }
        redirect_to('/admin/testimonials.php');
    }
}

$adminPage = 'testimonials';
$adminEyebrow = 'SOCIAL PROOF';
$adminTitle = 'Manage Client Testimonials';

$testimonials = get_testimonials(false);
$editing = isset($_GET['edit']) ? get_testimonial_by_id((int)$_GET['edit']) : null;
$showNew = isset($_GET['action']) && $_GET['action'] === 'new';

require __DIR__ . '/_layout_top.php';
?>
      <?php if ($editing || $showNew):
          $tm = $editing ?? [
              'id' => '',
              'client_name' => '',
              'client_role' => '',
              'quote_text' => '',
              'avatar_path' => '/assets/avatar-rohit.png',
              'quote_color' => '#67e8f9',
              'sort_order' => count($testimonials) + 1,
              'is_active' => 1,
          ];
      ?>
        <section class="adm-card">
          <div class="adm-card-head">
            <div>
              <h2 class="adm-card-title"><?= $editing ? 'Edit Testimonial: ' . e($tm['client_name']) : 'Add Client Testimonial' ?></h2>
              <p class="adm-card-sub">Keep quotes concise (~115–145 characters) so they fit cleanly inside the 3-card Home carousel.</p>
            </div>
            <a href="/admin/testimonials.php" class="adm-btn adm-btn-glass adm-btn-sm">Cancel</a>
          </div>

          <form method="POST" action="/admin/testimonials.php" enctype="multipart/form-data" class="adm-form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="save" />
            <input type="hidden" name="id" value="<?= e($tm['id']) ?>" />

            <div class="adm-field">
              <label class="adm-label">Client Full Name *</label>
              <input type="text" name="client_name" class="adm-input" value="<?= e($tm['client_name']) ?>" required />
            </div>

            <div class="adm-field">
              <label class="adm-label">Role &amp; Company *</label>
              <input type="text" name="client_role" class="adm-input" value="<?= e($tm['client_role']) ?>" placeholder="VP Engineering, TechCorp" required />
            </div>

            <div class="adm-field span-full">
              <label class="adm-label">Testimonial Quote *</label>
              <textarea name="quote_text" class="adm-textarea" rows="3" maxlength="220" required><?= e($tm['quote_text']) ?></textarea>
            </div>

            <div class="adm-field">
              <label class="adm-label">Upload Client Avatar (optional)</label>
              <input type="file" name="avatar_image" accept="image/png,image/jpeg,image/webp,image/gif" class="adm-input" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Avatar Path</label>
              <input type="text" name="existing_avatar" class="adm-input" value="<?= e($tm['avatar_path']) ?>" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Quote Mark Accent Hex</label>
              <input type="text" name="quote_color" class="adm-input" value="<?= e($tm['quote_color']) ?>" placeholder="#67e8f9" />
            </div>

            <div class="adm-field">
              <label class="adm-label">Sort Order</label>
              <input type="number" name="sort_order" class="adm-input" value="<?= (int)$tm['sort_order'] ?>" />
            </div>

            <div class="adm-field span-full">
              <label class="adm-check-row">
                <input type="checkbox" name="is_active" value="1"<?= ((int)$tm['is_active'] === 1) ? ' checked' : '' ?> />
                <span>Active in Home Testimonials Carousel</span>
              </label>
            </div>

            <div class="adm-field span-full" style="flex-direction:row;gap:10px;">
              <button type="submit" class="adm-btn adm-btn-primary"><?= $editing ? 'Update Testimonial' : 'Add Testimonial' ?></button>
              <a href="/admin/testimonials.php" class="adm-btn adm-btn-glass">Cancel</a>
            </div>
          </form>
        </section>
      <?php endif; ?>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">All Client Testimonials (<?= count($testimonials) ?>)</h2>
            <p class="adm-card-sub">Displayed on the Home page with interactive Prev / Next carousel navigation.</p>
          </div>
          <a href="/admin/testimonials.php?action=new" class="adm-btn adm-btn-primary adm-btn-sm">+ Add Testimonial</a>
        </div>

        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Client</th>
                <th>Quote</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($testimonials as $t): ?>
                <tr>
                  <td>#<?= (int)$t['sort_order'] ?></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <img src="<?= e($t['avatar_path']) ?>" alt="" class="adm-avatar-mini" />
                      <div>
                        <div style="font-weight:650;"><?= e($t['client_name']) ?></div>
                        <div style="font-size:11.5px;color:#6d7288;"><?= e($t['client_role']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="max-width:400px;"><?= e($t['quote_text']) ?></td>
                  <td>
                    <span class="adm-badge <?= (int)$t['is_active'] === 1 ? 'badge-green' : 'badge-amber' ?>">
                      <?= (int)$t['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                    </span>
                  </td>
                  <td class="adm-actions-inline">
                    <a href="/admin/testimonials.php?edit=<?= (int)$t['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Edit</a>
                    <form method="POST" action="/admin/testimonials.php" onsubmit="return confirm('Delete this testimonial?');" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="form_action" value="delete" />
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
<?php require __DIR__ . '/_layout_bottom.php'; ?>
