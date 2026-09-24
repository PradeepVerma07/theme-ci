/* CI360 final enhancements: interactive About timeline. */
(() => {
  'use strict';

  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));

  const isReduced = () => document.documentElement.classList.contains('reduced-motion') ||
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initTimeline(root) {
    if (!root || root.dataset.ci360TimelineReady === '1') return;

    let items = [];
    try {
      items = JSON.parse(root.dataset.ci360Timeline || '[]');
    } catch (e) {
      items = [];
    }
    if (!Array.isArray(items) || !items.length) return;

    root.dataset.ci360TimelineReady = '1';

    const stepsWrap = root.querySelector('[data-ci360-tl-steps]');
    const fill = root.querySelector('[data-ci360-tl-fill]');
    const card = root.querySelector('[data-ci360-tl-card]');
    const glow = root.querySelector('[data-ci360-tl-glow]');
    let active = 0;
    let timer = null;

    function progressDots() {
      return items.map((_, index) => {
        const cls = index < active ? 'ci360-tl-pdot done' : index === active ? 'ci360-tl-pdot current' : 'ci360-tl-pdot';
        return `<span class="${cls}"></span>`;
      }).join('');
    }

    function renderCard() {
      const item = items[active];
      if (!item || !card) return;

      const tags = Array.isArray(item.tags)
        ? item.tags.map((tag) => `<span class="ci360-tl-tag">${esc(tag)}</span>`).join('')
        : '';

      const image = item.img
        ? `<img class="ci360-tl-image" src="${esc(item.img)}" alt="CI360 ${esc(item.year)}" loading="eager" decoding="async">`
        : '<span class="ci360-tl-image-fallback"></span>';

      card.innerHTML =
        '<div class="ci360-tl-card-grid">' +
          '<div class="ci360-tl-card-left">' +
            '<div>' +
              `<span class="ci360-tl-year-big">${esc(item.year)}</span>` +
              `<h3 class="ci360-tl-card-title">${item.title || ''}</h3>` +
            '</div>' +
            `<p class="ci360-tl-card-desc">${esc(item.desc)}</p>` +
            `<div class="ci360-tl-tags">${tags}</div>` +
            '<div class="ci360-tl-nav">' +
              `<button type="button" class="ci360-tl-nav-btn prev" data-ci360-tl-prev ${active === 0 ? 'disabled' : ''}>← <span>Previous</span></button>` +
              `<button type="button" class="ci360-tl-nav-btn next" data-ci360-tl-next ${active === items.length - 1 ? 'disabled' : ''}><span>Next</span> →</button>` +
            '</div>' +
          '</div>' +
          '<div class="ci360-tl-card-right">' +
            image +
            '<span class="ci360-tl-img-fade" aria-hidden="true"></span>' +
            '<span class="ci360-tl-img-vignette" aria-hidden="true"></span>' +
            `<span class="ci360-tl-progress" aria-hidden="true">${progressDots()}</span>` +
          '</div>' +
        '</div>';

      if (glow) glow.style.background = `radial-gradient(ellipse, ${item.glow || 'rgba(59,130,246,.22), rgba(6,182,212,.08)'})`;

      const prev = card.querySelector('[data-ci360-tl-prev]');
      const next = card.querySelector('[data-ci360-tl-next]');
      if (prev) prev.addEventListener('click', () => { setActive(active - 1, true); restart(); });
      if (next) next.addEventListener('click', () => { setActive(active + 1, true); restart(); });

      card.style.animation = 'none';
      void card.offsetHeight;
      card.style.animation = '';
    }

    function updateSteps(scrollIntoView) {
      if (!stepsWrap) return;
      const buttons = [...stepsWrap.querySelectorAll('[data-ci360-tl-step]')];
      buttons.forEach((button, index) => {
        button.classList.toggle('active', index === active);
        button.classList.toggle('passed', index < active);
        button.setAttribute('aria-pressed', String(index === active));
        button.tabIndex = index === active ? 0 : -1;
      });
      if (fill) {
        const pct = items.length > 1 ? (active / (items.length - 1)) * 100 : 0;
        fill.style.width = active === 0 ? '0' : `calc(${pct}% - 16px)`;
      }
      if (scrollIntoView && window.matchMedia('(max-width: 767px)').matches) {
        const current = buttons[active];
        if (current) {
          const left = current.offsetLeft - (stepsWrap.clientWidth - current.offsetWidth) / 2;
          stepsWrap.scrollTo({ left: Math.max(0, left), behavior: isReduced() ? 'auto' : 'smooth' });
        }
      }
    }

    function setActive(index, scrollIntoView) {
      if (index < 0 || index >= items.length) return;
      active = index;
      updateSteps(scrollIntoView);
      renderCard();
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (isReduced()) return;
      timer = setInterval(() => {
        if (!document.documentElement.contains(root)) {
          stop();
          return;
        }
        setActive((active + 1) % items.length, true);
      }, 5000);
    }

    function restart() {
      start();
    }

    if (stepsWrap) {
      stepsWrap.querySelectorAll('[data-ci360-tl-step]').forEach((button) => {
        button.addEventListener('click', () => {
          const index = Number(button.dataset.ci360TlStep);
          if (Number.isInteger(index)) {
            setActive(index, true);
            restart();
          }
        });
        button.addEventListener('keydown', (event) => {
          let next = active;
          if (event.key === 'ArrowRight') next = Math.min(items.length - 1, active + 1);
          else if (event.key === 'ArrowLeft') next = Math.max(0, active - 1);
          else if (event.key === 'Home') next = 0;
          else if (event.key === 'End') next = items.length - 1;
          else return;
          event.preventDefault();
          setActive(next, true);
          restart();
        });
      });
    }

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', (event) => {
      if (!root.contains(event.relatedTarget)) start();
    });

    updateSteps(false);
    renderCard();
    start();
  }

  function initAll() {
    document.querySelectorAll('[data-ci360-timeline]').forEach(initTimeline);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll, { once: true });
  } else {
    initAll();
  }

  const hookElementor = () => {
    if (!window.elementorFrontend || !elementorFrontend.hooks || hookElementor.done) return;
    hookElementor.done = true;
    elementorFrontend.hooks.addAction('frontend/element_ready/widget', () => setTimeout(initAll, 80));
  };
  hookElementor();
  window.addEventListener('elementor/frontend/init', hookElementor);
})();
