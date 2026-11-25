const pagePreloader = document.getElementById('pagePreloader');
if (pagePreloader) {
  const root = document.documentElement;
  let pageReady = false;
  let preloaderVisible = false;
  let hideTimer = 0;

  const finishLoading = () => {
    if (pageReady) return;
    pageReady = true;
    window.clearTimeout(showTimer);
    window.clearTimeout(fallbackTimer);

    const hidePreloader = () => {
      root.classList.remove('preloader-visible');
      pagePreloader.setAttribute('aria-hidden', 'true');
      hideTimer = window.setTimeout(() => {
        pagePreloader.hidden = true;
      }, 260);
    };

    if (preloaderVisible) {
      window.setTimeout(hidePreloader, Math.max(0, 360 - (performance.now() - shownAt)));
    } else {
      pagePreloader.hidden = true;
    }
  };

  let shownAt = 0;
  const showTimer = window.setTimeout(() => {
    if (pageReady) return;
    preloaderVisible = true;
    shownAt = performance.now();
    pagePreloader.hidden = false;
    pagePreloader.setAttribute('aria-hidden', 'false');
    root.classList.add('preloader-visible');
  }, 140);
  const fallbackTimer = window.setTimeout(finishLoading, 2400);

  window.addEventListener('load', finishLoading, { once: true });
  window.addEventListener('pageshow', event => {
    if (event.persisted) finishLoading();
  }, { once: true });

  if (document.readyState === 'complete') finishLoading();
}

const ambientDotField = document.getElementById('ambientDotField');
if (ambientDotField) {
  const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  const context = ambientDotField.getContext('2d', { alpha: true });
  if (context) {
    let spacing = 32;
    let width = 0;
    let height = 0;
    let pixelRatio = 1;
    let ambientDots = [];
    let dotRows = [];
    const previousSparkDots = [];
    const previousGlows = [
      { active: false, x: 0, y: 0, radius: 0 },
      { active: false, x: 0, y: 0, radius: 0 }
    ];
    let pointerX = 0;
    let pointerY = 0;
    let currentX = 0;
    let currentY = 0;
    let pointerStrength = 0;
    let targetStrength = 0;
    let ripple = null;
    let touchStart = null;
    let frame = 0;
    let lastFrameTime = 0;
    let motionTime = 0;
    let slowDrawFrames = 0;
    let fastDrawFrames = 0;
    let reducedWaveWork = false;
    const pointerRadius = 260;
    const touchRadius = 220;

    function resizeCanvas() {
      width = window.innerWidth;
      height = window.innerHeight;
      spacing = width <= 600 ? 38 : width <= 900 ? 34 : 32;
      pixelRatio = Math.min(window.devicePixelRatio || 1, finePointer.matches ? 1.5 : 1.15);
      ambientDotField.width = Math.round(width * pixelRatio);
      ambientDotField.height = Math.round(height * pixelRatio);
      context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
      ambientDots = [];
      dotRows = [];
      previousSparkDots.length = 0;
      previousGlows.forEach(glow => {
        glow.active = false;
      });

      const rows = Math.ceil(height / spacing) + 1;
      const columns = Math.ceil(width / spacing) + 1;
      for (let row = 0; row < rows; row += 1) {
        const rowDots = [];
        for (let column = 0; column < columns; column += 1) {
          const variation = (row * 17 + column * 31) % 7;
          const isWarm = row % 3 === 0 && column % 3 === 0;
          const x = 16 + column * spacing;
          const y = 16 + row * spacing;
          const phase = ((row * 37 + column * 19) % 97) / 97 * Math.PI * 2;
          const dot = {
            x,
            y,
            radius: 0.72 + variation * 0.06,
            color: isWarm ? 'rgb(255 174 111)' : 'rgb(126 171 235)',
            waveOnePhase: x * 0.011 + y * 0.006 + phase,
            waveTwoPhase: x * -0.006 + y * 0.009 + phase * 1.7,
            sparkPhase: phase * 2.3,
            spark: (row * 13 + column * 7) % 17 === 0,
            wave: (row + column) % 3 === 0,
            lowerCostWave: (row + column) % 6 === 0
          };
          rowDots.push(dot);
          if (dot.wave || dot.spark) ambientDots.push(dot);
        }
        dotRows.push(rowDots);
      }
    }

    function clearPreviousFrame() {
      for (const dot of previousSparkDots) {
        context.clearRect(dot.x - 5, dot.y - 5, 10, 10);
      }
      previousSparkDots.length = 0;
      for (const glow of previousGlows) {
        if (glow.active) {
          const extent = glow.radius + 32;
          context.clearRect(glow.x - extent, glow.y - extent, extent * 2, extent * 2);
          glow.active = false;
        }
      }
    }

    function drawDot(dot, alpha, radius) {
      context.globalAlpha = Math.min(0.82, alpha);
      context.fillStyle = dot.color;
      context.beginPath();
      context.arc(dot.x, dot.y, radius, 0, Math.PI * 2);
      context.fill();
    }

    function drawLocalGlow(x, y, radius, strength, areaIndex) {
      if (strength < 0.015) return;
      context.save();
      context.globalCompositeOperation = 'lighter';

      const blueX = x + 24;
      const blueY = y - 10;
      const blueGlow = context.createRadialGradient(blueX, blueY, 0, blueX, blueY, radius);
      blueGlow.addColorStop(0, `rgba(66, 142, 255, ${0.19 * strength})`);
      blueGlow.addColorStop(0.34, `rgba(65, 130, 244, ${0.105 * strength})`);
      blueGlow.addColorStop(1, 'rgba(65, 130, 244, 0)');
      context.fillStyle = blueGlow;
      context.fillRect(Math.max(0, blueX - radius), Math.max(0, blueY - radius), Math.min(width, radius * 2), Math.min(height, radius * 2));

      const orangeX = x - 28;
      const orangeY = y + 14;
      const orangeRadius = radius * 0.78;
      const orangeGlow = context.createRadialGradient(orangeX, orangeY, 0, orangeX, orangeY, orangeRadius);
      orangeGlow.addColorStop(0, `rgba(255, 151, 83, ${0.16 * strength})`);
      orangeGlow.addColorStop(0.4, `rgba(255, 139, 73, ${0.075 * strength})`);
      orangeGlow.addColorStop(1, 'rgba(255, 139, 73, 0)');
      context.fillStyle = orangeGlow;
      context.fillRect(Math.max(0, orangeX - orangeRadius), Math.max(0, orangeY - orangeRadius), Math.min(width, orangeRadius * 2), Math.min(height, orangeRadius * 2));
      context.restore();

      const area = previousGlows[areaIndex];
      area.x = x;
      area.y = y;
      area.radius = radius;
      area.active = true;
    }

    function drawAmbientWaves(time) {
      const mobileIntensity = finePointer.matches ? 1 : 0.58;
      for (const dot of ambientDots) {
        if (reducedWaveWork && dot.wave && !dot.spark && !dot.lowerCostWave) continue;
        const waveOne = Math.sin(dot.waveOnePhase - time * 0.00062);
        const waveTwo = Math.sin(dot.waveTwoPhase + time * 0.00043);
        let shimmer = (waveOne * 0.62 + waveTwo * 0.38) * mobileIntensity;
        if (dot.spark) {
          shimmer += (0.5 + 0.5 * Math.sin(time * 0.00072 + dot.sparkPhase)) * 0.2 * mobileIntensity;
        }
        const glow = Math.max(0, shimmer + 0.12) * (dot.spark ? 0.34 : 0.24);
        if (glow < 0.025) continue;

        drawDot(dot, glow, dot.radius * (1 + glow * 0.45));
        if (dot.spark && shimmer > 0.48) {
          context.globalAlpha = Math.min(0.28, (shimmer - 0.4) * 0.16);
          context.beginPath();
          context.arc(dot.x, dot.y, dot.radius + 2.1, 0, Math.PI * 2);
          context.fill();
        }
        previousSparkDots.push(dot);
      }
      context.globalAlpha = 1;
    }

    function drawInteractionDots(x, y, radius, strength, ringRadius = 0, ringFade = 0) {
      const firstRow = Math.max(0, Math.floor((y - radius - 16) / spacing));
      const lastRow = Math.min(dotRows.length - 1, Math.ceil((y + radius - 16) / spacing));
      for (let row = firstRow; row <= lastRow; row += 1) {
        const rowDots = dotRows[row];
        const firstColumn = Math.max(0, Math.floor((x - radius - 16) / spacing));
        const lastColumn = Math.min(rowDots.length - 1, Math.ceil((x + radius - 16) / spacing));
        for (let column = firstColumn; column <= lastColumn; column += 1) {
          const dot = rowDots[column];
          const dx = dot.x - x;
          const dy = dot.y - y;
          const distanceSquared = dx * dx + dy * dy;
          if (distanceSquared > radius * radius) continue;
          const distance = Math.sqrt(distanceSquared);
          const influence = ringRadius
            ? Math.max(0, 1 - Math.abs(distance - ringRadius) / 74) * ringFade
            : Math.pow(1 - distance / radius, 2) * strength;
          if (influence < 0.025) continue;

          drawDot(dot, influence * 0.64, dot.radius * (1 + influence * 0.68));
          if (influence > 0.42) {
            context.globalAlpha = influence * 0.17;
            context.beginPath();
            context.arc(dot.x, dot.y, dot.radius + 2.2 * influence, 0, Math.PI * 2);
            context.fill();
          }
        }
      }
      context.globalAlpha = 1;
    }

    function drawFrame(now) {
      clearPreviousFrame();
      const time = motionTime;
      const rippleProgress = ripple ? Math.min((now - ripple.startedAt) / 1100, 1) : 1;
      const touchActive = Boolean(ripple && rippleProgress < 1);
      if (ripple && !touchActive) ripple = null;
      const pointerActive = pointerStrength > 0.012;

      drawAmbientWaves(time);
      if (pointerActive) {
        drawLocalGlow(currentX, currentY, pointerRadius, pointerStrength, 0);
        drawInteractionDots(currentX, currentY, pointerRadius, pointerStrength);
      }
      if (touchActive) {
        const ringRadius = 18 + rippleProgress * touchRadius;
        drawLocalGlow(ripple.x, ripple.y, touchRadius, 1 - rippleProgress, 1);
        drawInteractionDots(ripple.x, ripple.y, touchRadius, 0, ringRadius, 1 - rippleProgress);
      }
      context.globalAlpha = 1;
    }

    function animate(now) {
      frame = 0;
      if (document.hidden) return;
      if (motionPreference.matches) {
        clearPreviousFrame();
        return;
      }

      const elapsed = lastFrameTime ? Math.min(now - lastFrameTime, 80) : 1000 / 60;
      lastFrameTime = now;
      motionTime += elapsed;
      const pointerEase = 1 - Math.exp(-elapsed / 60);
      const strengthEase = 1 - Math.exp(-elapsed / 158);
      currentX += (pointerX - currentX) * pointerEase;
      currentY += (pointerY - currentY) * pointerEase;
      pointerStrength += (targetStrength - pointerStrength) * strengthEase;
      const drawStartedAt = performance.now();
      drawFrame(now);
      const drawDuration = performance.now() - drawStartedAt;
      const drawBudget = Math.max(1.8, Math.min(5.8, elapsed * 0.35));
      if (drawDuration > drawBudget) {
        slowDrawFrames += 1;
        fastDrawFrames = 0;
        if (slowDrawFrames >= 8) reducedWaveWork = true;
      } else if (drawDuration < drawBudget * 0.45) {
        fastDrawFrames += 1;
        slowDrawFrames = 0;
        if (fastDrawFrames >= 180) reducedWaveWork = false;
      } else {
        slowDrawFrames = 0;
        fastDrawFrames = 0;
      }
      frame = window.requestAnimationFrame(animate);
    }

    function startAnimation() {
      if (frame || document.hidden || motionPreference.matches) return;
      lastFrameTime = 0;
      frame = window.requestAnimationFrame(animate);
    }

    resizeCanvas();
    startAnimation();
    window.addEventListener('resize', resizeCanvas, { passive: true });
    window.addEventListener('pointermove', event => {
      if (!finePointer.matches || event.pointerType === 'touch' || motionPreference.matches) return;
      pointerX = event.clientX;
      pointerY = event.clientY;
      targetStrength = 1;
      startAnimation();
    }, { passive: true });
    window.addEventListener('pointerout', event => {
      if (!event.relatedTarget) targetStrength = 0;
    }, { passive: true });
    window.addEventListener('blur', () => {
      targetStrength = 0;
    });

    document.addEventListener('pointerdown', event => {
      if (event.pointerType === 'mouse' || motionPreference.matches) return;
      touchStart = { x: event.clientX, y: event.clientY, pointerId: event.pointerId };
    }, { passive: true });
    document.addEventListener('pointerup', event => {
      if (!touchStart || touchStart.pointerId !== event.pointerId) return;
      const distance = Math.hypot(event.clientX - touchStart.x, event.clientY - touchStart.y);
      if (distance < 12 && !(event.target instanceof Element && event.target.closest('a, button, input, select, textarea, [role="button"], [data-href], .glass-nav, .glass-modal-dialog'))) {
        ripple = { x: event.clientX, y: event.clientY, startedAt: performance.now() };
        startAnimation();
      }
      touchStart = null;
    }, { passive: true });
    document.addEventListener('pointercancel', () => {
      touchStart = null;
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        window.cancelAnimationFrame(frame);
        frame = 0;
        targetStrength = 0;
        pointerStrength = 0;
        ripple = null;
        clearPreviousFrame();
        context.clearRect(0, 0, width, height);
      } else {
        startAnimation();
      }
    });

    motionPreference.addEventListener('change', event => {
      if (event.matches) {
        targetStrength = 0;
        pointerStrength = 0;
        ripple = null;
        window.cancelAnimationFrame(frame);
        frame = 0;
        context.clearRect(0, 0, width, height);
      } else {
        startAnimation();
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const toastEl = document.getElementById('glassToast');
  const navbar = document.getElementById('navbar');
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const previewModal = document.getElementById('previewModal');
  const previewModalUrl = document.getElementById('previewModalUrl');
  const previewModalBody = document.getElementById('previewModalBody');
  const closePreviewBtn = document.getElementById('closePreviewModal');

  let toastTimer = null;
  let modalReturnFocus = null;

  const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
  const revealTargets = Array.from(document.querySelectorAll('.main-shell .glass-panel'));
  const skillGroups = Array.from(document.querySelectorAll('.skills-matrix-grid > .skill-group-card'));
  if (!motionPreference.matches && 'IntersectionObserver' in window && (revealTargets.length || skillGroups.length)) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.06, rootMargin: '200px 0px' });

    document.documentElement.classList.add('motion-enabled');
    revealTargets.forEach(target => {
      target.classList.add('scroll-reveal');
      observer.observe(target);
    });
    skillGroups.forEach(target => {
      observer.observe(target);
    });
  }

  function esc(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function showToast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add('visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastEl.classList.remove('visible');
    }, 3400);
  }

  // Auto-show server flash toast if present
  const serverToast = document.body.getAttribute('data-flash-toast');
  if (serverToast) {
    showToast(serverToast);
  }

  // Responsive Mobile/Tablet Navigation Drawer
  function closeMobileMenu() {
    if (!navbar || !mobileMenuBtn) return;
    navbar.classList.remove('nav-open');
    mobileMenuBtn.setAttribute('aria-expanded', 'false');
  }

  function closePreviewModal() {
    if (!previewModal || previewModal.hidden) return;
    previewModal.hidden = true;
    if (modalReturnFocus instanceof HTMLElement) {
      modalReturnFocus.focus();
    }
    modalReturnFocus = null;
  }

  if (mobileMenuBtn && navbar) {
    mobileMenuBtn.addEventListener('click', e => {
      e.stopPropagation();
      const isOpen = navbar.classList.toggle('nav-open');
      mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', e => {
      if (navbar.classList.contains('nav-open') && !navbar.contains(e.target)) {
        closeMobileMenu();
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        if (navbar.classList.contains('nav-open')) {
          closeMobileMenu();
          mobileMenuBtn.focus();
        }
        if (previewModal && !previewModal.hidden) {
          closePreviewModal();
        }
      } else if (e.key === 'Tab' && previewModal && !previewModal.hidden) {
        const dialog = previewModal.querySelector('[role="dialog"]');
        const focusable = dialog
          ? Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
            .filter(element => element.getClientRects().length > 0)
          : [];
        if (!focusable.length) {
          e.preventDefault();
          closePreviewBtn?.focus();
          return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && (document.activeElement === first || !dialog.contains(document.activeElement))) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && (document.activeElement === last || !dialog.contains(document.activeElement))) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 1024 && navbar.classList.contains('nav-open')) {
        closeMobileMenu();
      }
    });
  }

  //Clickable whole-card links (data-href)
  document.addEventListener('click', e => {
    const copyBtn = e.target.closest('[data-copy-code]');
    if (copyBtn) {
      const code = decodeURIComponent(copyBtn.getAttribute('data-copy-code') || '');
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).catch(() => {});
      }
      copyBtn.textContent = 'Copied ✓';
      showToast('Code snippet copied to clipboard!');
      setTimeout(() => {
        copyBtn.textContent = 'Copy Code';
      }, 1800);
      return;
    }

    const previewBtn = e.target.closest('[data-live-preview]');
    if (previewBtn) {
      e.preventDefault();
      e.stopPropagation();
      const slug = previewBtn.getAttribute('data-live-preview');
      openLivePreviewModal(slug, previewBtn);
      return;
    }

    const cardHrefEl = e.target.closest('[data-href]');
    if (cardHrefEl && !e.target.closest('a, button')) {
      window.location.href = cardHrefEl.getAttribute('data-href');
    }
  });

  // Live Project Preview Modal via /api/project.php?slug=...
  async function openLivePreviewModal(slug, trigger) {
    if (!previewModal || !slug) return;
    try {
      const res = await fetch(`/api/project.php?slug=${encodeURIComponent(slug)}`);
      const data = await res.json();
      if (!data.ok || !data.project) return;
      const proj = data.project;
      previewModalUrl.textContent = proj.live_url || `https://${proj.slug}.demo`;
      previewModalBody.innerHTML = `
        <div style="display:flex;flex-direction:column;gap:18px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
              <span class="eyebrow">${esc(proj.category)} • ${esc(proj.year)}</span>
              <h3 id="previewModalTitle" style="font-size:22px;font-weight:700;color:#111422;">${esc(proj.title)}</h3>
              <p style="font-size:13.2px;color:#55596b;margin-top:4px;max-width:560px;">${esc(proj.summary)}</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <a href="/projects/${esc(proj.slug)}" class="btn-dark-pill btn-sm-pill">
                <span>Full Architecture &amp; Code ↗</span>
              </a>
            </div>
          </div>
          <div class="case-showcase-frame">
            <img src="${esc(proj.image_path)}" alt="${esc(proj.title)} Live Application View" />
          </div>
          <div class="case-metrics-grid" style="margin-bottom:0;">
            ${(proj.metrics || [])
              .map(
                m => `
              <div class="case-metric-card">
                <div class="case-metric-num">${esc(m.value)}</div>
                <div class="case-metric-label">${esc(m.label)}</div>
              </div>`
              )
              .join('')}
          </div>
        </div>
      `;
      modalReturnFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;
      previewModal.hidden = false;
      closePreviewBtn?.focus();
    } catch {
      window.location.href = `/projects/${encodeURIComponent(slug)}`;
    }
  }

  if (closePreviewBtn && previewModal) {
    closePreviewBtn.addEventListener('click', () => {
      closePreviewModal();
    });
    previewModal.addEventListener('click', e => {
      if (e.target === previewModal) closePreviewModal();
    });
  }

  // Home Testimonials Carousel (reads embedded JSON array if >0 items)
  const testJsonEl = document.getElementById('testimonialsDataJson');
  const testGrid = document.getElementById('testimonialsGrid');
  const prevBtn = document.getElementById('prevTestBtn');
  const nextBtn = document.getElementById('nextTestBtn');
  if (testJsonEl && testGrid && prevBtn && nextBtn) {
    try {
      const items = JSON.parse(testJsonEl.textContent || '[]');
      let offset = 0;
      const render = () => {
        if (!items.length) return;
        const cards = testGrid.querySelectorAll('.testimonial-card, .testimonial-neon-card');
        cards.forEach((card, i) => {
          const item = items[(i + offset + items.length) % items.length];
          const text = card.querySelector('.testimonial-text, .testimonial-neon-text');
          const name = card.querySelector('.client-name, .testimonial-neon-name');
          const role = card.querySelector('.client-role, .testimonial-neon-role');
          const img = card.querySelector('.client-avatar, .testimonial-neon-avatar');
          if (text) text.textContent = item.quote_text;
          if (name) name.textContent = item.client_name;
          if (role) role.textContent = item.client_role;
          if (img) { img.src = item.avatar_path; img.alt = item.client_name; }
          const path = card.querySelector('.quote-glyph path');
          if (path) path.setAttribute('fill', item.quote_color || '#f97316');
        });
      };
      prevBtn.addEventListener('click', () => {
        offset = (offset - 1 + items.length) % items.length;
        render();
      });
      nextBtn.addEventListener('click', () => {
        offset = (offset + 1) % items.length;
        render();
      });
    } catch {
      // Ignore malformed JSON
    }
  }

  // Projects Archive Instant Client Filtering (progressive enhancement over server-rendered cards!)
  const projCatGroup = document.getElementById('projCatFilters');
  const projStackGroup = document.getElementById('projStackFilters');
  const projSearchInput = document.getElementById('projSearchInput');
  const projGrid = document.getElementById('projectsArchiveGrid');
  if (projCatGroup && projStackGroup && projSearchInput && projGrid) {
    let activeCat = 'All';
    let activeStack = 'All Stack';
    const filterCards = () => {
      const q = projSearchInput.value.trim().toLowerCase();
      let visibleCount = 0;
      projGrid.querySelectorAll('.project-archive-card').forEach(card => {
        const cat = card.getAttribute('data-card-cat') || '';
        const stacks = (card.getAttribute('data-card-stacks') || '').split('|');
        const searchBlob = (card.getAttribute('data-card-search') || '').toLowerCase();
        const matchCat = activeCat === 'All' || cat === activeCat;
        const matchStack = activeStack === 'All Stack' || stacks.includes(activeStack);
        const matchQ = !q || searchBlob.includes(q);
        const show = matchCat && matchStack && matchQ;
        card.hidden = !show;
        if (show) visibleCount++;
      });
      const emptyEl = document.getElementById('projectsEmptyNotice');
      if (emptyEl) emptyEl.hidden = visibleCount > 0;
    };

    projCatGroup.addEventListener('click', e => {
      const btn = e.target.closest('[data-cat]');
      if (!btn) return;
      activeCat = btn.getAttribute('data-cat');
      projCatGroup.querySelectorAll('.filter-pill').forEach(b => b.classList.toggle('active', b === btn));
      filterCards();
    });

    projStackGroup.addEventListener('click', e => {
      const btn = e.target.closest('[data-stack]');
      if (!btn) return;
      activeStack = btn.getAttribute('data-stack');
      projStackGroup.querySelectorAll('[data-stack]').forEach(b => {
        const on = b === btn;
        b.style.background = on ? '#161a2c' : '';
        b.style.color = on ? '#fff' : '';
        b.style.borderColor = on ? '#161a2c' : '';
      });
      filterCards();
    });

    projSearchInput.addEventListener('input', filterCards);
  }

  // Case Study Tabs
  const tabBtns = document.querySelectorAll('[data-dev-tab]');
  if (tabBtns.length) {
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-dev-tab');
        tabBtns.forEach(b => b.classList.toggle('active', b === btn));
        document.querySelectorAll('[data-panel]').forEach(panel => {
          panel.hidden = panel.getAttribute('data-panel') !== target;
        });
      });
    });
  }

  // Blog Category Filter & Search
  const blogCats = document.getElementById('blogCatFilters');
  const blogSearch = document.getElementById('blogSearchInput');
  const blogGrid = document.getElementById('blogCardsGrid');
  if (blogCats && blogSearch && blogGrid) {
    let activeBlogCat = 'All';
    const filterBlogs = () => {
      const q = blogSearch.value.trim().toLowerCase();
      blogGrid.querySelectorAll('.blog-card').forEach(card => {
        const cat = card.getAttribute('data-bcat') || '';
        const blob = (card.getAttribute('data-bsearch') || '').toLowerCase();
        const show = (activeBlogCat === 'All' || cat === activeBlogCat) && (!q || blob.includes(q));
        card.hidden = !show;
      });
    };
    blogCats.addEventListener('click', e => {
      const btn = e.target.closest('[data-blog-cat]');
      if (!btn) return;
      activeBlogCat = btn.getAttribute('data-blog-cat');
      blogCats.querySelectorAll('.filter-pill').forEach(b => b.classList.toggle('active', b === btn));
      filterBlogs();
    });
    blogSearch.addEventListener('input', filterBlogs);
  }

  // FAQ Accordions
  document.querySelectorAll('.faq-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
      const answer = btn.nextElementSibling;
      const chev = btn.querySelector('.faq-chevron');
      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!isOpen));
      if (answer) answer.hidden = isOpen;
      if (chev) chev.textContent = isOpen ? '+' : '−';
    });
  });

  // AJAX Contact Form Enhancement (saves to PHP SQLite/MySQL via /api/contact.php!)
  document.querySelectorAll('form[data-ajax-contact]').forEach(formEl => {
    formEl.addEventListener('submit', async e => {
      e.preventDefault();
      const submitBtn = formEl.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
      const formData = new FormData(formEl);
      try {
        const res = await fetch('/api/contact.php', {
          method: 'POST',
          headers: { Accept: 'application/json' },
          body: formData,
        });
        const data = await res.json();
        if (data.ok) {
          showToast(data.message || 'Thank you! Your message has been saved.');
          formEl.reset();
        } else {
          showToast(data.error || 'Please check all required fields.');
        }
      } catch {
        showToast('Network error—please try again.');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  });
});
