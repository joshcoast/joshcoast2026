(function () {
	const DEFAULT_STYLE_SCHEME = 'stripes';

	const normalizeStyleScheme = (scheme) => {
		if (scheme === 'neon') {
			return 'stripes';
		}

		if (scheme === 'arcade' || scheme === 'stripes') {
			return scheme;
		}

		return null;
	};

	const getStoredStyleScheme = () => {
		const cookieValue = document.cookie
			.split('; ')
			.find((entry) => entry.startsWith('jc_style_scheme='));

		if (!cookieValue) {
			return null;
		}

		const rawValue = cookieValue.split('=').slice(1).join('=');
		return normalizeStyleScheme(rawValue);
	};

	const applyStyleScheme = (scheme, persist = true) => {
		const normalizedScheme = normalizeStyleScheme(scheme);

		if (normalizedScheme) {
			document.body.dataset.styleScheme = normalizedScheme;
		} else {
			delete document.body.dataset.styleScheme;
		}

		document.body.classList.remove('style-scheme-arcade', 'style-scheme-stripes');
		if (normalizedScheme) {
			document.body.classList.add('style-scheme-' + normalizedScheme);
		}

		document.querySelectorAll('.jc-theme-switcher__button').forEach((button) => {
			const isActive = !!normalizedScheme && button.dataset.styleScheme === normalizedScheme;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});

		if (persist) {
			if (normalizedScheme) {
				document.cookie = 'jc_style_scheme=' + normalizedScheme + '; path=/; max-age=31536000; SameSite=Lax';
			} else {
				document.cookie = 'jc_style_scheme=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
			}
		}
	};

	const styleSwitcherButtons = Array.from(document.querySelectorAll('.jc-theme-switcher__button'));
	const storedScheme = getStoredStyleScheme();
	const initialScheme = normalizeStyleScheme(document.body.dataset.styleScheme) || storedScheme || DEFAULT_STYLE_SCHEME;

	applyStyleScheme(initialScheme, !storedScheme);

	styleSwitcherButtons.forEach((button) => {
		button.addEventListener('click', () => {
			applyStyleScheme(button.dataset.styleScheme || null);
		});
	});

	const starsContainer = document.querySelector('.jc-stars');
	const themeBase = (document.body.dataset.themeUri || '').replace(/\/?$/, '/');
	const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
	const isHomepage = document.body.classList.contains('home') || document.body.classList.contains('front-page');

	const setupProjectsPageLayoutFallback = () => {
		if (!document.body.classList.contains('page-id-11')) {
			return;
		}

		const entryContent = document.querySelector('.jc-content .entry-content');
		if (!entryContent || entryContent.classList.contains('jc-projects-ready')) {
			return;
		}

		// Skip fallback if the new native project block is already in use.
		if (entryContent.querySelector('.wp-block-jc-project-client-card')) {
			return;
		}

		const children = Array.from(entryContent.children);
		const headings = children.filter((child) => child.tagName === 'H2' && child.classList.contains('gb-headline-text'));

		if (!headings.length) {
			return;
		}

		const projectFigures = children.filter((child) => child.tagName === 'FIGURE' && child.classList.contains('wp-block-image'));
		const firstHeadingIndex = children.indexOf(headings[0]);
		const introNodes = children
			.slice(0, firstHeadingIndex)
			.filter((child) => !(child.tagName === 'FIGURE' && child.classList.contains('wp-block-image')));

		const projectsWrapper = document.createElement('div');
		projectsWrapper.className = 'jc-projects';

		let figureIndex = 0;

		headings.forEach((heading, projectIndex) => {
			const row = document.createElement('section');
			row.className = 'jc-project-row';

			if (projectIndex % 2 === 1) {
				row.classList.add('jc-project-row--reverse');
			}

			const media = document.createElement('figure');
			media.className = 'jc-project-row__media';

			const sourceFigure = projectFigures[figureIndex] || null;
			if (sourceFigure) {
				figureIndex += 1;
				media.innerHTML = sourceFigure.innerHTML;
			}

			const copy = document.createElement('div');
			copy.className = 'jc-project-row__copy';

			let node = heading;
			while (node) {
				const next = node.nextElementSibling;
				copy.appendChild(node);

				if (!next || (next.tagName === 'H2' && next.classList.contains('gb-headline-text'))) {
					break;
				}

				node = next;
			}

			const tagNodes = Array.from(copy.querySelectorAll(':scope > .gb-headline:not(.gb-headline-text)'));
			if (tagNodes.length) {
				const tags = document.createElement('div');
				tags.className = 'jc-project-row__tags';

				tagNodes.forEach((tagNode) => {
					tags.appendChild(tagNode);
				});

				const description = copy.querySelector(':scope > p.gb-headline-text');
				if (description && description.nextSibling) {
					copy.insertBefore(tags, description.nextSibling);
				} else {
					copy.appendChild(tags);
				}
			}

			const viewProjectLink = copy.querySelector(':scope > a.gb-button');
			if (viewProjectLink) {
				viewProjectLink.classList.add('jc-btn', 'jc-btn--sm', 'jc-project-row__link');
			}

			if (media.children.length === 0) {
				row.classList.add('jc-project-row--no-media');
			}

			if (row.classList.contains('jc-project-row--reverse')) {
				row.append(copy, media);
			} else {
				row.append(media, copy);
			}

			projectsWrapper.appendChild(row);
		});

		entryContent.innerHTML = '';
		introNodes.forEach((node) => entryContent.appendChild(node));
		entryContent.appendChild(projectsWrapper);
		entryContent.classList.add('jc-projects-ready');
	};

	setupProjectsPageLayoutFallback();

	const assetUrl = (fileName) => themeBase + 'assets/img/' + fileName;

	if (starsContainer) {
		const count = window.innerWidth < 800 ? 40 : 80;

		for (let i = 0; i < count; i += 1) {
			const star = document.createElement('span');
			star.className = 'jc-star';
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
	const isNearCenterZone = (x, y) => x >= 32 && x <= 68 && y >= 28 && y <= 76;
	const pickOuterSafePoint = () => {
		for (let attempt = 0; attempt < 30; attempt += 1) {
			const x = rand(6, 92);
			const y = rand(8, 88);

			if (!isNearCenterZone(x, y)) {
				return { x, y };
			}
		}

		// Fallback to edge-biased points if random picks keep landing near center.
		const edgeBand = Math.random() > 0.5 ? rand(6, 24) : rand(76, 92);
		return {
			x: edgeBand,
			y: rand(8, 88),
		};
	};
	const pickOffscreenStart = (anchorX, anchorY) => {
		const side = Math.floor(rand(0, 4));
		const offset = rand(10, 20);

		if (side === 0) {
			return {
				x: -offset,
				y: clamp(anchorY + rand(-12, 12), -8, 108),
			};
		}

		if (side === 1) {
			return {
				x: 100 + offset,
				y: clamp(anchorY + rand(-12, 12), -8, 108),
			};
		}

		if (side === 2) {
			return {
				x: clamp(anchorX + rand(-12, 12), -8, 108),
				y: -offset,
			};
		}

		return {
			x: clamp(anchorX + rand(-12, 12), -8, 108),
			y: 100 + offset,
		};
	};
	const getAlienPeekSide = (alien) => {
		if (alien.dataset.peekSide) {
			return alien.dataset.peekSide;
		}

		if (alien.classList.contains('is-peek-left')) {
			return 'left';
		}

		if (alien.classList.contains('is-peek-right')) {
			return 'right';
		}

		return '';
	};
	const canUsePeekTop = (activePeekAliens, side, topValue, minGap) => {
		return activePeekAliens.every((activeAlien) => {
			if (getAlienPeekSide(activeAlien) !== side) {
				return true;
			}

			const activeTop = Number.parseFloat(activeAlien.style.top || '0');
			return Math.abs(activeTop - topValue) >= minGap;
		});
	};
	const pickPeekPosition = (activePeekAliens = [], minGap = 14) => {
		const sideOrder = Math.random() > 0.5 ? ['left', 'right'] : ['right', 'left'];

		for (let sideIndex = 0; sideIndex < sideOrder.length; sideIndex += 1) {
			const side = sideOrder[sideIndex];

			for (let attempt = 0; attempt < 30; attempt += 1) {
				const topValue = rand(8, 86);

				if (!canUsePeekTop(activePeekAliens, side, topValue, minGap)) {
					continue;
				}

				return {
					left: side === 'left' ? '-2.2%' : '97.2%',
					top: topValue.toFixed(2) + '%',
					side,
				};
			}
		}

		return null;
	};
	const alienNodes = [];
	const alienRespawnDelay = 240;
	const alienPeekIntervalDelay = 5000;
	const alienPeekVisibleDuration = 90000;
	const alienPeekMaxVisible = 5;
	const alienPeekMinVerticalGap = 14;
	const aliensContainer = document.createElement('div');
	aliensContainer.className = 'jc-aliens';
	aliensContainer.setAttribute('aria-hidden', 'true');
	document.body.appendChild(aliensContainer);
	const alienPeekHideTimers = new Map();
	let alienPeekIntervalId = null;
	let alienMode = isHomepage ? 'roam' : 'peek';

	const createAliens = (count = alienSprites.length) => {
		aliensContainer.replaceChildren();
		alienNodes.length = 0;

		for (let index = 0; index < count; index += 1) {
			const spriteFile = alienSprites[index % alienSprites.length];
			const alien = document.createElement('button');
			alien.type = 'button';
			alien.className = 'jc-alien';
			alien.setAttribute('aria-label', 'Destroy alien ' + (index + 1));

			const parallax = rand(0.06, 0.16);
			const scale = rand(0.82, 1.1);
			const entryPoint = pickOuterSafePoint();
			const midPoint = pickOuterSafePoint();
			const endPoint = pickOuterSafePoint();
			const startPoint = pickOffscreenStart(entryPoint.x, entryPoint.y);
			const bobDelay = rand(0, 5).toFixed(2);
			const bobDuration = rand(2.8, 4.8).toFixed(2);
			const wanderDuration = rand(14, 26).toFixed(2);
			const wanderDelay = rand(0, 1.2).toFixed(2);

			alien.style.left = startPoint.x + '%';
			alien.style.top = startPoint.y + '%';
			alien.style.setProperty('--alien-scale', scale.toFixed(2));
			alien.style.setProperty('--alien-parallax', '0px');
			alien.style.setProperty('--alien-bob-duration', bobDuration + 's');
			alien.style.setProperty('--alien-wander-duration', wanderDuration + 's');
			alien.style.setProperty('--alien-wander-delay', wanderDelay + 's');
			alien.style.setProperty('--alien-start-x', startPoint.x.toFixed(2) + '%');
			alien.style.setProperty('--alien-start-y', startPoint.y.toFixed(2) + '%');
			alien.style.setProperty('--alien-entry-x', entryPoint.x.toFixed(2) + '%');
			alien.style.setProperty('--alien-entry-y', entryPoint.y.toFixed(2) + '%');
			alien.style.setProperty('--alien-mid-x', midPoint.x.toFixed(2) + '%');
			alien.style.setProperty('--alien-mid-y', midPoint.y.toFixed(2) + '%');
			alien.style.setProperty('--alien-end-x', endPoint.x.toFixed(2) + '%');
			alien.style.setProperty('--alien-end-y', endPoint.y.toFixed(2) + '%');
			alien.dataset.parallax = String(parallax);
			alien.dataset.state = 'idle';

			alien.innerHTML = [
				'<span class="jc-alien__art">',
				'<img class="jc-alien__sprite" src="' + assetUrl(spriteFile) + '" alt="" loading="lazy" decoding="async" />',
				'<img class="jc-alien__bomb jc-alien__bomb--1" src="' + assetUrl('bomb-1.svg') + '" alt="" loading="lazy" decoding="async" />',
				'<img class="jc-alien__bomb jc-alien__bomb--2" src="' + assetUrl('bomb-2.svg') + '" alt="" loading="lazy" decoding="async" />',
				'<img class="jc-alien__bomb jc-alien__bomb--3" src="' + assetUrl('bomb-3.svg') + '" alt="" loading="lazy" decoding="async" />',
				'</span>',
			].join('');

			alien.style.setProperty('--alien-bob-delay', bobDelay + 's');

			aliensContainer.appendChild(alien);
			alienNodes.push(alien);

			alien.addEventListener('click', (event) => {
				event.preventDefault();
				explodeAlien(alien);
			});
		}
	};

	const animateHomepageStats = () => {
		const stats = Array.from(document.querySelectorAll('.jc-hero .jc-stat'));

		if (!stats.length) {
			return;
		}

		const parsePercent = (value) => {
			const parsed = Number.parseInt(String(value).replace(/[^\d]/g, ''), 10);
			return Number.isFinite(parsed) ? clamp(parsed, 0, 100) : 0;
		};

		stats.forEach((stat, index) => {
			const valueNode = stat.querySelector('.jc-stat__value');
			const fillNode = stat.querySelector('.jc-stat__fill');

			if (!valueNode || !fillNode) {
				return;
			}

			const labelTarget = parsePercent(valueNode.textContent || '0');
			const fillTarget = parsePercent(fillNode.style.width || '0');
			const target = Math.max(labelTarget, fillTarget);

			if (reduceMotionQuery.matches) {
				valueNode.textContent = target + '%';
				fillNode.style.width = target + '%';
				return;
			}

			const duration = 1450;
			const delay = index * 220;
			const startTime = performance.now() + delay;

			valueNode.textContent = '0%';
			fillNode.style.width = '0%';

			const tick = (now) => {
				if (now < startTime) {
					requestAnimationFrame(tick);
					return;
				}

				const elapsed = now - startTime;
				const progress = Math.min(1, elapsed / duration);
				const eased = 1 - Math.pow(1 - progress, 3);
				const current = Math.round(target * eased);

				valueNode.textContent = current + '%';
				fillNode.style.width = current + '%';

				if (progress < 1) {
					requestAnimationFrame(tick);
				}
			};

			requestAnimationFrame(tick);
		});
	};

	if (isHomepage) {
		createAliens();
		animateHomepageStats();
	}

	const updateAlienParallax = () => {
		if (reduceMotionQuery.matches) {
			return;
		}

		const scrollY = window.scrollY || window.pageYOffset || 0;
		alienNodes.forEach((alien) => {
			if (alien.dataset.state === 'hidden' || alien.classList.contains('is-peeking')) {
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

	const clearPeekHideTimer = (alien) => {
		const timerId = alienPeekHideTimers.get(alien);
		if (!timerId) {
			return;
		}

		window.clearTimeout(timerId);
		alienPeekHideTimers.delete(alien);
	};

	const hidePeekAlien = (alien) => {
		if (!alien) {
			return;
		}

		clearPeekHideTimer(alien);
		alien.dataset.state = 'hidden';
		alien.hidden = true;
		alien.disabled = false;
		alien.classList.remove('is-peeking');
		alien.classList.remove('is-peek-left');
		alien.classList.remove('is-peek-right');
		alien.classList.remove('is-peek-entering');
		delete alien.dataset.peekSide;
		alien.style.removeProperty('--alien-peek-rotation');
		alien.style.removeProperty('--alien-peek-slide-x');
		alien.classList.remove('is-exploding');
	};

	const showPeekAlien = (alien, activePeekAliens = []) => {
		if (!alien) {
			return;
		}

		const position = pickPeekPosition(activePeekAliens, alienPeekMinVerticalGap);

		if (!position) {
			return;
		}

		clearPeekHideTimer(alien);
		alien.hidden = false;
		alien.disabled = false;
		alien.dataset.state = 'idle';
		alien.classList.remove('is-exploding');
		alien.classList.add('is-peeking');
		alien.classList.remove('is-peek-left');
		alien.classList.remove('is-peek-right');
		alien.classList.remove('is-peek-entering');
		alien.style.left = position.left;
		alien.style.top = position.top;
		alien.dataset.peekSide = position.side;

		if (position.side === 'left') {
			alien.classList.add('is-peek-left');
			alien.style.setProperty('--alien-peek-rotation', '12deg');
			alien.style.setProperty('--alien-peek-slide-x', '-22px');
		} else {
			alien.classList.add('is-peek-right');
			alien.style.setProperty('--alien-peek-rotation', '-12deg');
			alien.style.setProperty('--alien-peek-slide-x', '22px');
		}

		alien.classList.add('is-peek-entering');
		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				alien.style.setProperty('--alien-peek-slide-x', '0px');
				alien.classList.remove('is-peek-entering');
			});
		});

		const hideTimer = window.setTimeout(() => {
			hidePeekAlien(alien);
		}, alienPeekVisibleDuration);

		alienPeekHideTimers.set(alien, hideTimer);
	};

	const spawnPeekAlien = () => {
		if (alienMode !== 'peek') {
			return;
		}

		const activePeekAliens = alienNodes.filter((alien) => alien.classList.contains('is-peeking') && !alien.hidden);

		if (activePeekAliens.length >= alienPeekMaxVisible) {
			return;
		}

		const hiddenAliens = alienNodes.filter((alien) => alien.hidden || alien.dataset.state === 'hidden');

		if (!hiddenAliens.length) {
			return;
		}

		const alien = hiddenAliens[Math.floor(Math.random() * hiddenAliens.length)];
		showPeekAlien(alien, activePeekAliens);
	};

	const clearPeekCycle = () => {
		if (alienPeekIntervalId) {
			window.clearInterval(alienPeekIntervalId);
			alienPeekIntervalId = null;
		}

		alienNodes.forEach((alien) => {
			clearPeekHideTimer(alien);
		});
	};

	const startPeekCycle = () => {
		alienMode = 'peek';
		createAliens(22);
		alienNodes.forEach((alien) => {
			hidePeekAlien(alien);
		});

		clearPeekCycle();
		alienPeekIntervalId = window.setInterval(spawnPeekAlien, alienPeekIntervalDelay);
		spawnPeekAlien();
	};

	const switchToRoamMode = () => {
		clearPeekCycle();
		alienMode = 'roam';
		regenerateAliens();
	};

	const explodeAlien = (alien) => {
		if (!alien || alien.dataset.state !== 'idle') {
			return;
		}

		alien.dataset.state = 'exploding';
		alien.disabled = true;
		alien.classList.add('is-exploding');
		alien.classList.remove('is-peeking');
		clearPeekHideTimer(alien);

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
			'.jc-section-title, .jc-hero__title, .jc-stat__label, .jc-stat__value, .jc-card h3 a, .jc-card h2 a, .jc-topbar__nav a, .jc-topbar__branding a, .jc-btn'
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

	const hudQuarter = document.querySelector('.jc-coin');
	const tickerCoinTrigger = document.querySelector('.jc-ticker__coin');
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

		if (!isHomepage && alienMode === 'peek') {
			switchToRoamMode();
			return;
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

	if (!isHomepage) {
		startPeekCycle();
	}

	const trigger = document.getElementById('player-one-trigger');

	if (trigger) {
		const canvas = document.createElement('canvas');
		canvas.className = 'jc-fireworks';
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
	const faceCards = document.querySelectorAll('.jc-reference');
	const referenceCountLabel = document.querySelector('.jc-references__count');
	if (referenceCountLabel) {
		referenceCountLabel.textContent = String(faceCards.length);
	}
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
		const bubble = card.querySelector('.jc-reference__bubble');
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
		const bubble = card.querySelector('.jc-reference__bubble');
		if (!bubble) return;

		// Make visible first so offsetHeight is measurable, then position
		bubble.classList.add('bubble-visible');
		bubble.setAttribute('aria-hidden', 'false');
		positionBubble(card, bubble);

		// Trigger wiggle
		const inner = bubble.querySelector('.jc-reference__bubble-inner');
		if (inner) {
			inner.classList.remove('bubble-wiggle-anim');
			void inner.offsetWidth;
			inner.classList.add('bubble-wiggle-anim');
		}
	};

	faceCards.forEach((card) => {
		card.addEventListener('click', (e) => {
			if (e.target.closest('.jc-reference__bubble')) return;
			if (card.getAttribute('aria-expanded') === 'true') {
				closeBubble(card);
			} else {
				openBubble(card);
			}
		});

		const closeBtn = card.querySelector('.jc-reference__close');
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
				const bubble = card.querySelector('.jc-reference__bubble');
				if (bubble) positionBubble(card, bubble);
			}
		});
	};

	window.addEventListener('resize', reposition);
	window.addEventListener('scroll', reposition, { passive: true });

	document.addEventListener('click', (e) => {
		if (!e.target.closest('.jc-reference')) {
			faceCards.forEach(closeBubble);
		}
	});
})();
