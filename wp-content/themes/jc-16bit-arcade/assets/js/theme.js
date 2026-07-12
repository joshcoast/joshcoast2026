(function () {
  const starsContainer = document.querySelector('.arcade-stars');
  const themeBase = (document.body.dataset.themeUri || '').replace(/\/?$/, '/');
  const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  const isHomepage = document.body.classList.contains('home') || document.body.classList.contains('front-page');

  const assetUrl = (fileName) => themeBase + 'assets/img/' + fileName;

  if (starsContainer) {
    const count = window.innerWidth < 800 ? 40 : 80;

    for (let i = 0; i < count; i += 1) {
      const star = document.createElement('span');
      star.className = 'star';
      star.style.left = Math.random() * 100 + '%';
      star.style.top = Math.random() * 100 + '%';
      star.style.opacity = String(0.2 + Math.random() * 0.8);
      star.style.setProperty('--dur', 1.5 + Math.random() * 4 + 's');
      starsContainer.appendChild(star);
    }
  }

  const alienSprites = [
    'alian-1.svg',
    'alian-2.svg',
    'alian-3.svg',
    'alian-4.svg',
  ];

  const rand = (min, max) => min + Math.random() * (max - min);
  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
  const alienNodes = [];
  const alienRespawnDelay = 240;
  const aliensContainer = document.createElement('div');
  aliensContainer.className = 'arcade-aliens';
  aliensContainer.setAttribute('aria-hidden', 'true');
  document.body.appendChild(aliensContainer);

  const createAliens = () => {
    aliensContainer.replaceChildren();
    alienNodes.length = 0;

    alienSprites.forEach((spriteFile, index) => {
      const alien = document.createElement('button');
      alien.type = 'button';
      alien.className = 'arcade-alien';
      alien.setAttribute('aria-label', 'Destroy alien ' + (index + 1));

      const parallax = rand(0.06, 0.16);
      const scale = rand(0.82, 1.1);
      const left = rand(5, 88);
      const top = rand(8, 72);
      const wanderX = rand(5, 14) * (Math.random() > 0.5 ? 1 : -1);
      const wanderY = rand(4, 10) * (Math.random() > 0.5 ? 1 : -1);
      const wanderMidX = clamp(left + wanderX, 4, 92);
      const wanderMidY = clamp(top + wanderY, 6, 88);
      const wanderEndX = clamp(left + wanderX * -0.65, 4, 92);
      const wanderEndY = clamp(top + wanderY * -0.65, 6, 88);
      const bobDelay = rand(0, 5).toFixed(2);
      const bobDuration = rand(2.8, 4.8).toFixed(2);
      const wanderDuration = rand(14, 26).toFixed(2);
      const wanderDelay = rand(0, 6).toFixed(2);

      alien.style.left = left + '%';
      alien.style.top = top + '%';
      alien.style.setProperty('--alien-scale', scale.toFixed(2));
      alien.style.setProperty('--alien-parallax', '0px');
      alien.style.setProperty('--alien-bob-duration', bobDuration + 's');
      alien.style.setProperty('--alien-wander-duration', wanderDuration + 's');
      alien.style.setProperty('--alien-wander-delay', wanderDelay + 's');
      alien.style.setProperty('--alien-start-x', left.toFixed(2) + '%');
      alien.style.setProperty('--alien-start-y', top.toFixed(2) + '%');
      alien.style.setProperty('--alien-mid-x', wanderMidX.toFixed(2) + '%');
      alien.style.setProperty('--alien-mid-y', wanderMidY.toFixed(2) + '%');
      alien.style.setProperty('--alien-end-x', wanderEndX.toFixed(2) + '%');
      alien.style.setProperty('--alien-end-y', wanderEndY.toFixed(2) + '%');
      alien.dataset.parallax = String(parallax);
      alien.dataset.state = 'idle';

      alien.innerHTML = [
        '<span class="arcade-alien__art">',
        '<img class="arcade-alien__sprite" src="' + assetUrl(spriteFile) + '" alt="" loading="lazy" decoding="async" />',
        '<img class="arcade-alien__bomb arcade-alien__bomb-1" src="' + assetUrl('bomb-1.svg') + '" alt="" loading="lazy" decoding="async" />',
        '<img class="arcade-alien__bomb arcade-alien__bomb-2" src="' + assetUrl('bomb-2.svg') + '" alt="" loading="lazy" decoding="async" />',
        '<img class="arcade-alien__bomb arcade-alien__bomb-3" src="' + assetUrl('bomb-3.svg') + '" alt="" loading="lazy" decoding="async" />',
        '</span>',
      ].join('');

      alien.style.setProperty('--alien-bob-delay', bobDelay + 's');

      aliensContainer.appendChild(alien);
      alienNodes.push(alien);

      alien.addEventListener('click', (event) => {
        event.preventDefault();
        explodeAlien(alien);
      });
    });
  };

  if (isHomepage) {
    createAliens();
  }

  const updateAlienParallax = () => {
    if (reduceMotionQuery.matches) {
      return;
    }

    const scrollY = window.scrollY || window.pageYOffset || 0;
    alienNodes.forEach((alien) => {
      if (alien.dataset.state === 'hidden') {
        return;
      }

      const parallax = Number.parseFloat(alien.dataset.parallax || '0');
      alien.style.setProperty('--alien-parallax', `${scrollY * parallax}px`);
    });
  };

  updateAlienParallax();

  const regenerateAliens = () => {
    createAliens();
    updateAlienParallax();
  };

  const explodeAlien = (alien) => {
    if (!alien || alien.dataset.state !== 'idle') {
      return;
    }

    alien.dataset.state = 'exploding';
    alien.disabled = true;
    alien.classList.add('is-exploding');

    window.setTimeout(() => {
      alien.dataset.state = 'hidden';
      alien.hidden = true;
    }, 560);
  };

  const setupAmbientFlicker = () => {
    if (reduceMotionQuery.matches) {
      return;
    }

    const candidates = Array.from(document.querySelectorAll(
      '.section-title, .hud-sub, .stat-label, .stat-value, .card-item h3 a, .card-item h2 a, .arcade-nav a, .site-branding a, .btn-arcade'
    ));

    if (!candidates.length) {
      return;
    }

    const triggerFlicker = () => {
      if (document.hidden) {
        return;
      }

      const pool = candidates.filter((element) => !element.classList.contains('ambient-flicker'));
      const targetPool = pool.length ? pool : candidates;
      const target = targetPool[Math.floor(Math.random() * targetPool.length)];

      if (!target) {
        return;
      }

      target.classList.add('ambient-flicker');
      window.setTimeout(() => {
        target.classList.remove('ambient-flicker');
      }, 560);
    };

    const scheduleFlicker = () => {
      const delay = 1300 + Math.random() * 3200;
      window.setTimeout(() => {
        triggerFlicker();
        scheduleFlicker();
      }, delay);
    };

    scheduleFlicker();
  };

  setupAmbientFlicker();

  const hudQuarter = document.querySelector('.hud-quarter');
  const tickerCoinTrigger = document.querySelector('.ticker-coin-trigger');
  let spinTimer = null;
  const spinDuration = 420;

  const triggerCoinAndAliens = () => {
    if (hudQuarter) {
      hudQuarter.classList.remove('is-spinning');
      void hudQuarter.offsetWidth;
      hudQuarter.classList.add('is-spinning');

      if (spinTimer) {
        window.clearTimeout(spinTimer);
      }

      spinTimer = window.setTimeout(() => {
        hudQuarter.classList.remove('is-spinning');
        spinTimer = null;
      }, spinDuration);
    }

    window.setTimeout(() => {
      regenerateAliens();
    }, alienRespawnDelay);
  };

  if (hudQuarter) {
    hudQuarter.addEventListener('click', triggerCoinAndAliens);
  }

  if (tickerCoinTrigger) {
    tickerCoinTrigger.addEventListener('click', triggerCoinAndAliens);
  }

  const trigger = document.getElementById('player-one-trigger');

  if (trigger) {
    const canvas = document.createElement('canvas');
    canvas.className = 'fireworks-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    const particles = [];
    const palette = ['#fff18a', '#ff7fe7', '#8af6ff', '#b2ff8a', '#ffb18f'];
    let rafId = null;

    const resize = () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    };

    const spawnBurst = (x, y) => {
      const count = 42;

      for (let i = 0; i < count; i += 1) {
        const angle = (Math.PI * 2 * i) / count;
        const velocity = 2 + Math.random() * 3.5;
        particles.push({
          x,
          y,
          vx: Math.cos(angle) * velocity,
          vy: Math.sin(angle) * velocity,
          life: 38 + Math.random() * 24,
          size: 3 + Math.random() * 3,
          color: palette[Math.floor(Math.random() * palette.length)],
        });
      }

      if (!rafId) {
        rafId = requestAnimationFrame(tick);
      }
    };

    const tick = () => {
      if (!ctx) {
        rafId = null;
        return;
      }

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      for (let i = particles.length - 1; i >= 0; i -= 1) {
        const p = particles[i];
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.05;
        p.life -= 1;

        if (p.life <= 0) {
          particles.splice(i, 1);
          continue;
        }

        ctx.globalAlpha = Math.max(0, Math.min(1, p.life / 48));
        ctx.fillStyle = p.color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = p.color;
        ctx.fillRect(p.x, p.y, p.size, p.size);
      }

      ctx.globalAlpha = 1;
      ctx.shadowBlur = 0;

      if (particles.length > 0) {
        rafId = requestAnimationFrame(tick);
      } else {
        rafId = null;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
      }
    };

    const triggerFireworks = () => {
      const rect = trigger.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;

      spawnBurst(centerX, centerY);
      spawnBurst(centerX - 70, centerY - 15);
      spawnBurst(centerX + 70, centerY - 15);
    };

    trigger.addEventListener('click', triggerFireworks);
    trigger.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        triggerFireworks();
      }
    });

    window.addEventListener('resize', resize);
    resize();

    window.addEventListener('scroll', updateAlienParallax, { passive: true });
    window.addEventListener('resize', updateAlienParallax);
  }

  // Reference speech bubbles — fixed tooltip with dynamic positioning
  const faceCards = document.querySelectorAll('.face-card');
  const BUBBLE_GAP = 14;

  const positionBubble = (card, bubble) => {
    const cardRect = card.getBoundingClientRect();
    const bw = bubble.offsetWidth || 300;
    const bh = bubble.offsetHeight || 200;
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const margin = 10;

    // Prefer above the card; fall back to below
    let placement = 'top';
    let top = cardRect.top - bh - BUBBLE_GAP;
    if (top < margin) {
      placement = 'bottom';
      top = cardRect.bottom + BUBBLE_GAP;
    }
    top = Math.min(top, vh - bh - margin);

    // Center over the card, clamped to viewport edges
    let left = cardRect.left + cardRect.width / 2 - bw / 2;
    left = Math.max(margin, Math.min(left, vw - bw - margin));

    // Triangle points at the card's horizontal center
    const cardCenterX = cardRect.left + cardRect.width / 2;
    const triOffset = Math.max(16, Math.min(cardCenterX - left, bw - 16));

    bubble.style.top = top + 'px';
    bubble.style.left = left + 'px';
    bubble.dataset.placement = placement;
    bubble.style.setProperty('--tri-offset', triOffset + 'px');
  };

  const closeBubble = (card) => {
    card.setAttribute('aria-expanded', 'false');
    const bubble = card.querySelector('.ref-bubble');
    if (bubble) {
      bubble.classList.remove('bubble-visible');
      bubble.setAttribute('aria-hidden', 'true');
    }
  };

  const openBubble = (card) => {
    faceCards.forEach((c) => {
      if (c !== card) closeBubble(c);
    });
    card.setAttribute('aria-expanded', 'true');
    const bubble = card.querySelector('.ref-bubble');
    if (!bubble) return;

    // Make visible first so offsetHeight is measurable, then position
    bubble.classList.add('bubble-visible');
    bubble.setAttribute('aria-hidden', 'false');
    positionBubble(card, bubble);

    // Trigger wiggle
    const inner = bubble.querySelector('.ref-bubble-inner');
    if (inner) {
      inner.classList.remove('bubble-wiggle-anim');
      void inner.offsetWidth;
      inner.classList.add('bubble-wiggle-anim');
    }
  };

  faceCards.forEach((card) => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('.ref-bubble')) return;
      if (card.getAttribute('aria-expanded') === 'true') {
        closeBubble(card);
      } else {
        openBubble(card);
      }
    });

    const closeBtn = card.querySelector('.ref-bubble-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        closeBubble(card);
        card.focus();
      });
    }

    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if (card.getAttribute('aria-expanded') === 'true') {
          closeBubble(card);
        } else {
          openBubble(card);
        }
      }
      if (e.key === 'Escape') {
        closeBubble(card);
        card.focus();
      }
    });
  });

  // Reposition open bubbles on scroll/resize
  const reposition = () => {
    faceCards.forEach((card) => {
      if (card.getAttribute('aria-expanded') === 'true') {
        const bubble = card.querySelector('.ref-bubble');
        if (bubble) positionBubble(card, bubble);
      }
    });
  };

  window.addEventListener('resize', reposition);
  window.addEventListener('scroll', reposition, { passive: true });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.face-card')) {
      faceCards.forEach(closeBubble);
    }
  });
})();
