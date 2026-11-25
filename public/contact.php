<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();
$scopeOptions = configured_contact_options($site, 'contact_scope_options', DEFAULT_CONTACT_SCOPE_OPTIONS);
$timelineOptions = configured_contact_options($site, 'contact_timeline_options', DEFAULT_CONTACT_TIMELINE_OPTIONS);
$requestedScope = trim((string)($_GET['service'] ?? ''));
$preselectedScope = in_array($requestedScope, $scopeOptions, true)
    ? $requestedScope
    : $scopeOptions[0];
$statusParam = (string)($_GET['status'] ?? '');

$activeNav = 'contact';
$isSubpage = true;
$pageTitle = 'Contact — ' . ($site['name'] ?? 'Jordan Lee');

require dirname(__DIR__) . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <div class="breadcrumb-row">
            <a href="/">Home</a>
            <span>›</span>
            <span class="crumb-current">Start a Project</span>
          </div>
          <span class="eyebrow">DIRECT ENGINEERING CONSULTATION</span>
          <h1 class="subpage-title">Let's Architect &amp; Ship Your Next Software Product</h1>
          <p class="subpage-lead">
            Share your product roadmap, technical bottleneck, or hiring needs. You'll receive a concrete response with architecture thoughts within 24 hours.
          </p>
        </section>

        <?php if ($statusParam === 'sent'): ?>
          <div class="glass-panel" style="padding:18px 24px;color:#166534;font-weight:600;border-color:#86efac;">
            ✓ Thank you! Your message has been saved. We will respond within 24 hours.
          </div>
        <?php elseif ($statusParam === 'invalid'): ?>
          <div class="glass-panel" style="padding:18px 24px;color:#991b1b;font-weight:600;border-color:#fca5a5;">
            Please fill in a valid name, email address, and project message.
          </div>
        <?php endif; ?>

        <section class="glass-panel subpage-section-panel">
          <div class="contact-page-grid">
            <div class="contact-channels-card">
              <div>
                <span class="availability-pill"><span class="avail-dot"></span> <?= e($site['availability'] ?? 'Open to Work') ?></span>
              </div>

              <div class="channel-row">
                <span class="contact-icon-circle">✉</span>
                <div>
                  <div class="channel-label">Direct Email</div>
                  <div class="channel-val"><a href="mailto:<?= e($site['email'] ?? 'hello@example.com') ?>"><?= e($site['email'] ?? 'hello@example.com') ?></a></div>
                </div>
              </div>

              <div class="channel-row">
                <span class="contact-icon-circle">☎</span>
                <div>
                  <div class="channel-label">Phone / WhatsApp</div>
                  <div class="channel-val"><a href="tel:<?= e(preg_replace('/\s+/', '', $site['phone'] ?? '+919876543210')) ?>"><?= e($site['phone'] ?? '+91 98765 43210') ?></a></div>
                </div>
              </div>

              <div class="channel-row">
                <span class="contact-icon-circle">⌘</span>
                <div>
                  <div class="channel-label">GitHub &amp; LinkedIn</div>
                  <div class="channel-val">
                    <a href="<?= e($site['github_url'] ?? 'https://github.com/jordanlee-dev') ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                    &nbsp;·&nbsp;
                    <a href="<?= e($site['linkedin_url'] ?? 'https://linkedin.com/in/jordanlee-dev') ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                  </div>
                </div>
              </div>

              <div class="channel-row">
                <span class="contact-icon-circle">📍</span>
                <div>
                  <div class="channel-label">Base &amp; Timezone</div>
                  <div class="channel-val"><?= e($site['location_full'] ?? 'New Delhi, India (IST / UTC+5:30)') ?></div>
                </div>
              </div>
            </div>

            <form class="contact-form-card" id="dedicatedContactForm" action="/api/contact.php" method="POST" data-ajax-contact="1">
              <div class="contact-choice-grid">
                <div class="contact-choice-field">
                  <label class="contact-choice-label" for="selectedScopeInput">Project scope</label>
                  <div class="select-wrap">
                    <select class="glass-input glass-select contact-choice-select" name="project" id="selectedScopeInput" required>
                      <?php foreach ($scopeOptions as $scope): ?>
                        <option value="<?= e($scope) ?>"<?= $scope === $preselectedScope ? ' selected' : '' ?>><?= e($scope) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <svg class="select-chevron" viewBox="0 0 12 12" width="12" height="12" fill="none" aria-hidden="true">
                      <path d="M3 4.5L6 7.5L9 4.5" stroke="#5b5e70" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                </div>

                <div class="contact-choice-field">
                  <label class="contact-choice-label" for="selectedTimelineInput">Estimated timeline <span>Optional</span></label>
                  <div class="select-wrap">
                    <select class="glass-input glass-select contact-choice-select" name="timeline" id="selectedTimelineInput">
                      <option value="">I'm flexible</option>
                      <?php foreach ($timelineOptions as $timeline): ?>
                        <option value="<?= e($timeline) ?>"><?= e($timeline) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <svg class="select-chevron" viewBox="0 0 12 12" width="12" height="12" fill="none" aria-hidden="true">
                      <path d="M3 4.5L6 7.5L9 4.5" stroke="#5b5e70" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="form-row-two">
                <label class="contact-choice-field">
                  <span class="contact-choice-label">Your name</span>
                  <input type="text" name="name" class="glass-input" placeholder="Jane Smith" autocomplete="name" required />
                </label>
                <label class="contact-choice-field">
                  <span class="contact-choice-label">Email address</span>
                  <input type="email" name="email" class="glass-input" placeholder="jane@company.com" autocomplete="email" required />
                </label>
              </div>

              <label class="contact-choice-field">
                <span class="contact-choice-label">A little about your project</span>
                <textarea
                  name="message"
                  class="glass-input glass-textarea"
                  placeholder="What are you building, and how can I help?"
                  rows="4"
                  required
                ></textarea>
              </label>

              <button type="submit" class="btn-send-message" style="align-self:flex-start;">
                <span>Send Project Brief</span>
                <svg viewBox="0 0 16 16" width="14" height="14" fill="none" aria-hidden="true">
                  <path d="M13.8 2.2L7.1 8.9M13.8 2.2L9.5 13.8L7.1 8.9M13.8 2.2L2.2 6.5L7.1 8.9" stroke="currentColor" stroke-width="1.45" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </form>
          </div>
        </section>
      </div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
