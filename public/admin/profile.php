<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/repository.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $formType = (string)($_POST['form_type'] ?? 'settings');

    if ($formType === 'settings') {
        $contactScopeOptions = lines_to_array((string)($_POST['contact_scope_options'] ?? ''));
        $contactTimelineOptions = lines_to_array((string)($_POST['contact_timeline_options'] ?? ''));
        foreach ([
            'Project scope' => $contactScopeOptions,
            'Estimated timeline' => $contactTimelineOptions,
        ] as $label => $options) {
            if ($options === [] || count($options) > 12) {
                set_flash('error', $label . ' needs between 1 and 12 options.');
                redirect_to('/admin/profile.php');
            }
            foreach ($options as $option) {
                if (mb_strlen($option, 'UTF-8') > 100) {
                    set_flash('error', $label . ' options must be 100 characters or fewer.');
                    redirect_to('/admin/profile.php');
                }
            }
        }

        $portraitPath = trim((string)($_POST['existing_portrait'] ?? '/assets/profile-cutout.png'));
        try {
            $resumePath = handle_cv_upload('resume_file');
            $uploaded = handle_image_upload('portrait_file', 'portrait');
            if ($uploaded !== null) {
                $portraitPath = $uploaded;
            }
        } catch (RuntimeException $ex) {
            set_flash('error', $ex->getMessage());
            redirect_to('/admin/profile.php');
        }

        $fields = [
            'name', 'role', 'eyebrow', 'tagline', 'short_bio', 'extended_bio',
            'about_heading_line1', 'about_heading_line2',
            'stat1_val', 'stat1_label', 'stat2_val', 'stat2_label', 'stat3_val', 'stat3_label',
            'hero_badge_years', 'hero_impact_label', 'hero_impact_value',
            'availability', 'email', 'phone', 'location', 'location_full',
            'github_url', 'linkedin_url', 'copyright',
        ];
        $pairs = [
            'portrait_path' => $portraitPath,
            'contact_scope_options' => implode("\n", $contactScopeOptions),
            'contact_timeline_options' => implode("\n", $contactTimelineOptions),
        ];
        if ($resumePath !== null) {
            $pairs['resume_path'] = $resumePath;
        }
        foreach ($fields as $key) {
            if (isset($_POST[$key])) {
                $pairs[$key] = trim((string)$_POST[$key]);
            }
        }
        update_site_settings($pairs);
        set_flash('success', 'Hero, About, Contact, and Site Settings updated across all pages!');
        redirect_to('/admin/profile.php');
    }

    if ($formType === 'password') {
        $current = (string)($_POST['current_password'] ?? '');
        $newPass = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if (strlen($newPass) < 8) {
            set_flash('error', 'New password must be at least 8 characters.');
            redirect_to('/admin/profile.php#security');
        }
        if ($newPass !== $confirm) {
            set_flash('error', 'New password and confirmation do not match.');
            redirect_to('/admin/profile.php#security');
        }

        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)($_SESSION['admin_id'] ?? 0)]);
        $adm = $stmt->fetch();

        if (!$adm || !password_verify($current, (string)$adm['password_hash'])) {
            set_flash('error', 'Current password is incorrect.');
            redirect_to('/admin/profile.php#security');
        }

        $upd = $pdo->prepare('UPDATE admins SET password_hash = :h, updated_at = :u WHERE id = :id');
        $upd->execute([
            ':h' => password_hash($newPass, PASSWORD_DEFAULT),
            ':u' => date('Y-m-d H:i:s'),
            ':id' => (int)$adm['id'],
        ]);
        set_flash('success', 'Admin password updated securely!');
        redirect_to('/admin/profile.php#security');
    }
}

$adminPage = 'profile';
$adminEyebrow = 'GLOBAL PROFILE & SITE SETTINGS';
$adminTitle = 'Hero, Bio, Contact & Security';

require __DIR__ . '/_layout_top.php';
?>
      <section class="adm-card">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Hero, About &amp; Contact Settings</h2>
            <p class="adm-card-sub">Updates propagate immediately to the Navbar, Hero, About, Contact, Resume, and Footer.</p>
          </div>
        </div>

        <form method="POST" action="/admin/profile.php" enctype="multipart/form-data" class="adm-form-grid">
          <?= csrf_field() ?>
          <input type="hidden" name="form_type" value="settings" />

          <div class="adm-field">
            <label class="adm-label">Developer Full Name</label>
            <input type="text" name="name" class="adm-input" value="<?= e($site['name'] ?? '') ?>" required />
          </div>

          <div class="adm-field">
            <label class="adm-label">Primary Title / Role</label>
            <input type="text" name="role" class="adm-input" value="<?= e($site['role'] ?? '') ?>" required />
          </div>

          <div class="adm-field">
            <label class="adm-label">Hero Eyebrow</label>
            <input type="text" name="eyebrow" class="adm-input" value="<?= e($site['eyebrow'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Availability Badge</label>
            <input type="text" name="availability" class="adm-input" value="<?= e($site['availability'] ?? '') ?>" />
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">Hero Tagline (2 lines recommended)</label>
            <textarea name="tagline" class="adm-textarea" rows="2"><?= e($site['tagline'] ?? '') ?></textarea>
          </div>

          <div class="adm-field">
            <label class="adm-label">Upload Hero / About Portrait Cutout (PNG/WebP)</label>
            <input type="file" name="portrait_file" accept="image/png,image/jpeg,image/webp" class="adm-input" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Current Portrait Path</label>
            <input type="text" name="existing_portrait" class="adm-input" value="<?= e($site['portrait_path'] ?? '/assets/profile-cutout.png') ?>" />
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">Upload CV (PDF, DOC, or DOCX — max 10 MB)</label>
            <input type="file" name="resume_file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="adm-input" />
            <small class="adm-card-sub">A new upload replaces the public download. Leave this empty to keep the current CV.</small>
            <?php if (!empty($site['resume_path'])): ?>
              <a href="/resume.php" class="adm-btn adm-btn-glass adm-btn-sm" style="align-self:flex-start;margin-top:8px;">Download Current CV</a>
            <?php else: ?>
              <span class="adm-card-sub">No CV uploaded; the generated text resume is currently used.</span>
            <?php endif; ?>
          </div>

          <div class="adm-field">
            <label class="adm-label">Hero Floating Badge Years</label>
            <input type="text" name="hero_badge_years" class="adm-input" value="<?= e($site['hero_badge_years'] ?? '5+') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Hero Impact Card (Label &amp; Value)</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <input type="text" name="hero_impact_label" class="adm-input" value="<?= e($site['hero_impact_label'] ?? 'System Impact') ?>" />
              <input type="text" name="hero_impact_value" class="adm-input" value="<?= e($site['hero_impact_value'] ?? '+120%') ?>" />
            </div>
          </div>

          <div class="adm-field">
            <label class="adm-label">About Heading Line 1</label>
            <input type="text" name="about_heading_line1" class="adm-input" value="<?= e($site['about_heading_line1'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">About Heading Line 2</label>
            <input type="text" name="about_heading_line2" class="adm-input" value="<?= e($site['about_heading_line2'] ?? '') ?>" />
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">About Stat Cards (3 Metrics)</label>
            <div class="adm-form-grid-3">
              <div style="display:flex;gap:8px;">
                <input type="text" name="stat1_val" class="adm-input" style="max-width:90px;" value="<?= e($site['stat1_val'] ?? '') ?>" />
                <input type="text" name="stat1_label" class="adm-input" value="<?= e($site['stat1_label'] ?? '') ?>" />
              </div>
              <div style="display:flex;gap:8px;">
                <input type="text" name="stat2_val" class="adm-input" style="max-width:90px;" value="<?= e($site['stat2_val'] ?? '') ?>" />
                <input type="text" name="stat2_label" class="adm-input" value="<?= e($site['stat2_label'] ?? '') ?>" />
              </div>
              <div style="display:flex;gap:8px;">
                <input type="text" name="stat3_val" class="adm-input" style="max-width:90px;" value="<?= e($site['stat3_val'] ?? '') ?>" />
                <input type="text" name="stat3_label" class="adm-input" value="<?= e($site['stat3_label'] ?? '') ?>" />
              </div>
            </div>
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">Home Short Bio</label>
            <textarea name="short_bio" class="adm-textarea" rows="3"><?= e($site['short_bio'] ?? '') ?></textarea>
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">About Page Extended Bio (separate paragraphs with a blank line)</label>
            <textarea name="extended_bio" class="adm-textarea" rows="5"><?= e($site['extended_bio'] ?? '') ?></textarea>
          </div>

          <div class="adm-field">
            <label class="adm-label">Contact Email</label>
            <input type="email" name="email" class="adm-input" value="<?= e($site['email'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Phone / WhatsApp</label>
            <input type="text" name="phone" class="adm-input" value="<?= e($site['phone'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Location (Short)</label>
            <input type="text" name="location" class="adm-input" value="<?= e($site['location'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">Location &amp; Timezone (Full)</label>
            <input type="text" name="location_full" class="adm-input" value="<?= e($site['location_full'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label" for="contactScopeOptions">Contact Form Project Scope Options</label>
            <textarea id="contactScopeOptions" name="contact_scope_options" class="adm-textarea" rows="5" required><?= e(implode("\n", configured_contact_options($site, 'contact_scope_options', DEFAULT_CONTACT_SCOPE_OPTIONS))) ?></textarea>
            <small class="adm-hint">One option per line, up to 12. These appear in the contact form dropdown.</small>
          </div>

          <div class="adm-field">
            <label class="adm-label" for="contactTimelineOptions">Contact Form Timeline Options</label>
            <textarea id="contactTimelineOptions" name="contact_timeline_options" class="adm-textarea" rows="5" required><?= e(implode("\n", configured_contact_options($site, 'contact_timeline_options', DEFAULT_CONTACT_TIMELINE_OPTIONS))) ?></textarea>
            <small class="adm-hint">One option per line, up to 12. Visitors can also choose “I'm flexible.”</small>
          </div>

          <div class="adm-field">
            <label class="adm-label">GitHub Profile URL</label>
            <input type="text" name="github_url" class="adm-input" value="<?= e($site['github_url'] ?? '') ?>" />
          </div>

          <div class="adm-field">
            <label class="adm-label">LinkedIn Profile URL</label>
            <input type="text" name="linkedin_url" class="adm-input" value="<?= e($site['linkedin_url'] ?? '') ?>" />
          </div>

          <div class="adm-field span-full">
            <label class="adm-label">Footer Copyright Text</label>
            <input type="text" name="copyright" class="adm-input" value="<?= e($site['copyright'] ?? '') ?>" />
          </div>

          <div class="adm-field span-full">
            <button type="submit" class="adm-btn adm-btn-primary" style="align-self:flex-start;">Save All Site Settings</button>
          </div>
        </form>
      </section>

      <section class="adm-card" id="security">
        <div class="adm-card-head">
          <div>
            <h2 class="adm-card-title">Change Admin Password</h2>
            <p class="adm-card-sub">Passwords are stored hashed with PHP bcrypt (`password_hash`).</p>
          </div>
        </div>

        <form method="POST" action="/admin/profile.php" class="adm-form-grid-3">
          <?= csrf_field() ?>
          <input type="hidden" name="form_type" value="password" />
          <div class="adm-field">
            <label class="adm-label">Current Password</label>
            <input type="password" name="current_password" class="adm-input" required />
          </div>
          <div class="adm-field">
            <label class="adm-label">New Password (min 8 chars)</label>
            <input type="password" name="new_password" class="adm-input" minlength="8" required />
          </div>
          <div class="adm-field">
            <label class="adm-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="adm-input" minlength="8" required />
          </div>
          <div class="adm-field span-full">
            <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm" style="align-self:flex-start;">Update Password</button>
          </div>
        </form>
      </section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
