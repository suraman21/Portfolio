<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['form_action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'set_status' && $id > 0) {
        $newStatus = (string)($_POST['status'] ?? 'read');
        update_contact_message_status($id, $newStatus);
        set_flash('success', 'Message marked as ' . ucfirst($newStatus) . '.');
        redirect_to('/admin/messages.php?view=' . $id);
    }

    if ($action === 'delete' && $id > 0) {
        delete_contact_message($id);
        set_flash('success', 'Message deleted from inbox.');
        redirect_to('/admin/messages.php');
    }
}

$filterStatus = (string)($_GET['status'] ?? 'all');
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$viewingMsg = $viewId > 0 ? get_contact_message_by_id($viewId) : null;
if ($viewingMsg && $viewingMsg['status'] === 'unread') {
    update_contact_message_status($viewId, 'read');
    $viewingMsg['status'] = 'read';
}

$messages = get_contact_messages($filterStatus);

$adminPage = 'messages';
$adminEyebrow = 'INCOMING INQUIRIES';
$adminTitle = 'Contact Form Submissions';

require __DIR__ . '/_layout_top.php';
?>
      <?php if ($viewingMsg): ?>
        <section class="adm-card">
          <div class="adm-card-head">
            <div>
              <span class="adm-badge badge-purple" style="margin-bottom:6px;"><?= e(ucfirst($viewingMsg['status'])) ?></span>
              <h2 class="adm-card-title">Inquiry from <?= e($viewingMsg['name']) ?> (<?= e($viewingMsg['project']) ?>)</h2>
              <p class="adm-card-sub">
                Email: <a href="mailto:<?= e($viewingMsg['email']) ?>"><?= e($viewingMsg['email']) ?></a>
                <?php if (!empty($viewingMsg['timeline'])): ?>
                  &nbsp;•&nbsp; Timeline: <strong><?= e($viewingMsg['timeline']) ?></strong>
                <?php endif; ?>
                &nbsp;•&nbsp; Received: <?= e($viewingMsg['created_at']) ?>
              </p>
            </div>
            <a href="/admin/messages.php" class="adm-btn adm-btn-glass adm-btn-sm">← Close Detail</a>
          </div>

          <div style="padding:18px 20px;border-radius:16px;background:rgba(255,255,255,0.85);border:1px solid rgba(195,198,224,0.7);font-size:14px;line-height:1.65;color:#1e2235;white-space:pre-wrap;margin-bottom:18px;"><?= e($viewingMsg['message']) ?></div>

          <div class="adm-actions-inline" style="gap:10px;">
            <a
              href="mailto:<?= e($viewingMsg['email']) ?>?subject=<?= rawurlencode('Re: ' . $viewingMsg['project'] . ' Inquiry') ?>"
              class="adm-btn adm-btn-primary adm-btn-sm"
            >Reply via Email ↗</a>

            <?php foreach (['read' => 'Mark Read', 'replied' => 'Mark Replied', 'archived' => 'Archive', 'unread' => 'Mark Unread'] as $stKey => $stLabel):
                if ($viewingMsg['status'] === $stKey) continue;
            ?>
              <form method="POST" action="/admin/messages.php" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="set_status" />
                <input type="hidden" name="id" value="<?= (int)$viewingMsg['id'] ?>" />
                <input type="hidden" name="status" value="<?= e($stKey) ?>" />
                <button type="submit" class="adm-btn adm-btn-glass adm-btn-sm"><?= e($stLabel) ?></button>
              </form>
            <?php endforeach; ?>

            <form method="POST" action="/admin/messages.php" onsubmit="return confirm('Permanently delete this message?');" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="form_action" value="delete" />
              <input type="hidden" name="id" value="<?= (int)$viewingMsg['id'] ?>" />
              <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Submissions Inbox (<?= count($messages) ?>)</h2>
            <p class="adm-card-sub">Filter by status, inspect full project briefs, and track replied leads.</p>
          </div>
          <div class="adm-actions-inline">
            <?php foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'] as $k => $lbl): ?>
              <a
                href="/admin/messages.php?status=<?= e($k) ?>"
                class="adm-btn <?= $filterStatus === $k ? 'adm-btn-primary' : 'adm-btn-glass' ?> adm-btn-sm"
              ><?= e($lbl) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (empty($messages)): ?>
          <p style="font-size:13.5px;color:#686d84;padding:18px 0;">No messages found in this filter view.</p>
        <?php else: ?>
          <div class="adm-table-wrap">
            <table class="adm-table">
              <thead>
                <tr>
                  <th>Sender</th>
                  <th>Scope &amp; Timeline</th>
                  <th>Message</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($messages as $m): ?>
                  <tr style="<?= $m['status'] === 'unread' ? 'background:rgba(124,114,244,0.06);' : '' ?>">
                    <td>
                      <div style="font-weight:<?= $m['status'] === 'unread' ? '750' : '600' ?>;"><?= e($m['name']) ?></div>
                      <div style="font-size:11.8px;color:#6d7288;"><?= e($m['email']) ?></div>
                    </td>
                    <td>
                      <div><?= e($m['project']) ?></div>
                      <?php if (!empty($m['timeline'])): ?>
                        <div style="font-size:11.4px;color:#6d7288;"><?= e($m['timeline']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td style="max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($m['message']) ?></td>
                    <td>
                      <?php if ($m['status'] === 'unread'): ?>
                        <span class="adm-badge badge-purple">Unread</span>
                      <?php elseif ($m['status'] === 'replied'): ?>
                        <span class="adm-badge badge-green">Replied</span>
                      <?php elseif ($m['status'] === 'archived'): ?>
                        <span class="adm-badge badge-amber">Archived</span>
                      <?php else: ?>
                        <span class="adm-badge badge-slate">Read</span>
                      <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6d7288;"><?= e($m['created_at']) ?></td>
                    <td class="adm-actions-inline">
                      <a href="/admin/messages.php?view=<?= (int)$m['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">View</a>
                      <form method="POST" action="/admin/messages.php" onsubmit="return confirm('Delete this message?');" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_action" value="delete" />
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>" />
                        <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
