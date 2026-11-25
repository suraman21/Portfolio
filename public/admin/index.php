<?php
declare(strict_types=1);

$adminPage = 'dashboard';
$adminEyebrow = 'CMS OVERVIEW';
$adminTitle = 'Portfolio Admin Dashboard';

require __DIR__ . '/_layout_top.php';

$recentMessages = array_slice(get_contact_messages('all'), 0, 5);
$featuredProjects = get_projects(false, false);
?>
      <section class="kpi-grid">
        <a href="/admin/services.php" class="kpi-card">
          <span class="kpi-label">Engineering Services</span>
          <span class="kpi-value"><?= (int)$counts['services'] ?></span>
          <span class="kpi-foot">Manage services &amp; FAQs →</span>
        </a>
        <a href="/admin/projects.php" class="kpi-card">
          <span class="kpi-label">Selected Works</span>
          <span class="kpi-value"><?= (int)$counts['projects'] ?></span>
          <span class="kpi-foot">Manage case studies →</span>
        </a>
        <a href="/admin/messages.php" class="kpi-card">
          <span class="kpi-label">Contact Submissions</span>
          <span class="kpi-value"><?= (int)$counts['messages_total'] ?></span>
          <span class="kpi-foot"><?= (int)$counts['messages_unread'] ?> unread in inbox →</span>
        </a>
        <a href="/admin/testimonials.php" class="kpi-card">
          <span class="kpi-label">Client Testimonials</span>
          <span class="kpi-value"><?= (int)$counts['testimonials'] ?></span>
          <span class="kpi-foot">Manage client quotes →</span>
        </a>
      </section>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Quick CMS Actions</h2>
            <p class="adm-card-sub">Jump directly to creating or updating content across your multi-page PHP portfolio.</p>
          </div>
        </div>
        <div class="adm-actions-inline" style="gap:10px;">
          <a href="/admin/projects.php?action=new" class="adm-btn adm-btn-primary">+ Add Portfolio Project</a>
          <a href="/admin/services.php?action=new" class="adm-btn adm-btn-glass">+ Add Service</a>
          <a href="/admin/testimonials.php?action=new" class="adm-btn adm-btn-glass">+ Add Testimonial</a>
          <a href="/admin/blog.php?action=new" class="adm-btn adm-btn-glass">+ Write Blog Article</a>
          <a href="/admin/profile.php" class="adm-btn adm-btn-glass">Edit Hero &amp; Site Settings</a>
        </div>
      </section>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Recent Contact Form Submissions</h2>
            <p class="adm-card-sub">Messages sent from the Home and Contact pages land directly here.</p>
          </div>
          <a href="/admin/messages.php" class="adm-btn adm-btn-glass adm-btn-sm">Open Full Inbox →</a>
        </div>

        <?php if (empty($recentMessages)): ?>
          <p style="font-size:13.4px;color:#696e85;padding:12px 0;">No contact form messages received yet. Submit the form on the Home or Contact page to test it live!</p>
        <?php else: ?>
          <div class="adm-table-wrap">
            <table class="adm-table">
              <thead>
                <tr>
                  <th>Sender</th>
                  <th>Scope / Project</th>
                  <th>Message Preview</th>
                  <th>Status</th>
                  <th>Received</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentMessages as $m): ?>
                  <tr>
                    <td>
                      <div style="font-weight:650;"><?= e($m['name']) ?></div>
                      <div style="font-size:11.8px;color:#6d7288;"><?= e($m['email']) ?></div>
                    </td>
                    <td><?= e($m['project']) ?></td>
                    <td style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($m['message']) ?></td>
                    <td>
                      <?php if ($m['status'] === 'unread'): ?>
                        <span class="adm-badge badge-purple">Unread</span>
                      <?php elseif ($m['status'] === 'replied'): ?>
                        <span class="adm-badge badge-green">Replied</span>
                      <?php else: ?>
                        <span class="adm-badge badge-slate"><?= e(ucfirst($m['status'])) ?></span>
                      <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6d7288;"><?= e($m['created_at']) ?></td>
                    <td><a href="/admin/messages.php?view=<?= (int)$m['id'] ?>" class="adm-btn adm-btn-glass adm-btn-sm">Inspect</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Portfolio Projects Overview</h2>
            <p class="adm-card-sub">Featured projects appear in the 3-card Selected Work showcase on the Home page.</p>
          </div>
          <a href="/admin/projects.php" class="adm-btn adm-btn-glass adm-btn-sm">Manage All Projects →</a>
        </div>
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Preview</th>
                <th>Project Title</th>
                <th>Category</th>
                <th>Home Featured</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($featuredProjects, 0, 5) as $p): ?>
                <tr>
                  <td><img src="<?= e($p['image_path']) ?>" alt="" class="adm-thumb-mini" /></td>
                  <td>
                    <div style="font-weight:650;"><?= e($p['title']) ?></div>
                    <div style="font-size:11.5px;color:#6d7288;">/projects/<?= e($p['slug']) ?></div>
                  </td>
                  <td><?= e($p['category']) ?></td>
                  <td>
                    <?php if ((int)$p['is_featured'] === 1): ?>
                      <span class="adm-badge badge-purple">★ Home Featured</span>
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
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
