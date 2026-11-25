<?php
declare(strict_types=1);
$site = $site ?? get_site_settings();
?>
      <!-- ==================== BOTTOM FOOTER STRIP ==================== -->
      <footer class="glass-footer-strip" id="siteFooter">
        <a href="/" class="footer-brand" style="text-decoration:none;">
          <span class="footer-logo-box">
            <svg viewBox="0 0 32 32" width="22" height="22" fill="none" aria-hidden="true">
              <path d="M16 5.2L6.5 25.8H11.5L16 15.4L20.5 25.8H25.5L16 5.2Z" fill="#a294f8" stroke="#a294f8" stroke-width="0.8" stroke-linejoin="round"/>
              <polygon points="16,19.6 13.7,24.6 18.3,24.6" fill="#a294f8"/>
            </svg>
          </span>
          <span class="footer-brand-name"><?= e($site['name'] ?? 'Aarav Singh') ?></span>
        </a>

        <span class="footer-copyright"><?= e($site['copyright'] ?? ('© ' . date('Y') . ' ' . ($site['name'] ?? 'Aarav Singh') . '. All rights reserved.')) ?></span>

        <div class="footer-subpage-links" aria-label="Footer Quick Links">
          <a href="/">Home</a>
          <a href="/about">About</a>
          <a href="/services">Services</a>
          <a href="/projects">Projects</a>
          <a href="/blog">Blog</a>
          <a href="/contact">Contact</a>
        </div>
      </footer>
    </div>
  </div>

  <!-- Interactive Live Project Preview Modal (Frosted Glass Browser Frame) -->
  <div class="glass-modal-backdrop" id="previewModal" hidden>
    <div class="glass-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="previewModalTitle">
      <div class="modal-browser-bar">
        <div class="browser-dots" aria-hidden="true">
          <span class="b-dot dot-red"></span>
          <span class="b-dot dot-amber"></span>
          <span class="b-dot dot-green"></span>
        </div>
        <div class="browser-url-pill" id="previewModalUrl">https://demo.local</div>
        <button type="button" class="modal-close-btn" id="closePreviewModal" aria-label="Close live preview">✕</button>
      </div>
      <div class="modal-body-content" id="previewModalBody"></div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div class="glass-toast" id="glassToast" role="status" aria-live="polite"></div>

  <script src="/ui.js?v=17"></script>
</body>
</html>
