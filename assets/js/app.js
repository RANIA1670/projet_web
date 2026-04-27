document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-search-form]');
  const input = document.querySelector('[data-search-input]');
  const items = [...document.querySelectorAll('[data-report-item]')];
  const emptyState = document.querySelector('[data-empty-state]');
  const filterButtons = [...document.querySelectorAll('[data-filter]')];
  const counters = [...document.querySelectorAll('[data-count-to]')];
  const progressBars = [...document.querySelectorAll('[data-width]')];
  const chartBars = [...document.querySelectorAll('[data-height]')];
  const toggles = [...document.querySelectorAll('[data-report-toggle]')];
  const interactiveCards = [...document.querySelectorAll('[data-card]')];
  const interactiveRows = [...document.querySelectorAll('[data-row]')];
  let activeFilter = 'all';
  let activeToastTimer;

  const ensureToast = () => {
    let toast = document.querySelector('[data-ui-toast]');
    if (toast) {
      return toast;
    }

    toast = document.createElement('div');
    toast.className = 'ui-toast';
    toast.setAttribute('data-ui-toast', '');
    toast.setAttribute('aria-live', 'polite');
    toast.setAttribute('aria-atomic', 'true');
    document.body.appendChild(toast);
    return toast;
  };

  const showToast = (message) => {
    const toast = ensureToast();
    toast.textContent = message;
    toast.classList.add('is-visible');

    window.clearTimeout(activeToastTimer);
    activeToastTimer = window.setTimeout(() => {
      toast.classList.remove('is-visible');
    }, 1600);
  };

  const filterReports = () => {
    if (!items.length || !emptyState) {
      return;
    }

    const query = (input?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    items.forEach((item) => {
      const haystack = item.dataset.searchText || '';
      const matchesQuery = query === '' || haystack.includes(query);
      const matchesFilter = activeFilter === 'all' || item.dataset.status === activeFilter;
      const visible = matchesQuery && matchesFilter;

      item.hidden = !visible;

      if (visible) {
        visibleCount += 1;
      }
    });

    emptyState.hidden = visibleCount !== 0;
  };

  if (form && input) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      filterReports();
    });

    input.addEventListener('input', filterReports);
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeFilter = button.dataset.filter || 'all';
      filterButtons.forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      filterReports();
    });
  });

  toggles.forEach((button) => {
    button.addEventListener('click', () => {
      const article = button.closest('[data-report-item]');
      const details = article?.querySelector('[data-report-details]');

      if (!details) {
        return;
      }

      const expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!expanded));
      button.textContent = expanded ? 'Details' : 'Masquer';
      details.hidden = expanded;
    });
  });

  items.forEach((item) => {
    item.tabIndex = 0;
    item.setAttribute('role', 'button');

    item.addEventListener('click', (event) => {
      if (event.target instanceof HTMLElement && event.target.closest('[data-report-toggle]')) {
        return;
      }
      const button = item.querySelector('[data-report-toggle]');
      button?.click();
    });

    item.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        const button = item.querySelector('[data-report-toggle]');
        button?.click();
      }
    });
  });

  interactiveCards.forEach((card) => {
    card.addEventListener('click', () => {
      card.classList.toggle('is-selected');
      const title =
        card.querySelector('h2')?.textContent?.trim() ||
        card.querySelector('span')?.textContent?.trim() ||
        'Element';
      showToast(`${title} selectionne`);
    });

    card.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        card.click();
      }
    });
  });

  interactiveRows.forEach((row) => {
    row.tabIndex = 0;
    row.setAttribute('role', 'button');
    row.setAttribute('aria-pressed', 'false');

    row.addEventListener('click', () => {
      const isActive = row.classList.toggle('is-selected');
      row.setAttribute('aria-pressed', String(isActive));
      const incident = row.querySelector('span')?.textContent?.trim() || 'Incident';
      showToast(isActive ? `Suivi active: ${incident}` : `Suivi retire: ${incident}`);
    });

    row.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        row.click();
      }
    });
  });

  counters.forEach((counter) => {
    const target = Number(counter.dataset.countTo || 0);
    const suffix = counter.dataset.countSuffix || '';

    if (!Number.isFinite(target)) {
      return;
    }

    const start = performance.now();
    const duration = 900;

    const tick = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const current = Math.round(target * progress);
      counter.textContent = `${current}${suffix}`;

      if (progress < 1) {
        requestAnimationFrame(tick);
      }
    };

    requestAnimationFrame(tick);
  });

  progressBars.forEach((bar) => {
    const width = bar.dataset.width || '0';
    requestAnimationFrame(() => {
      bar.style.width = `${width}%`;
    });
  });

  chartBars.forEach((bar) => {
    const height = bar.dataset.height || '10';
    requestAnimationFrame(() => {
      bar.style.height = `${height}px`;
    });
  });

  const backToTop = document.createElement('button');
  backToTop.type = 'button';
  backToTop.className = 'back-to-top';
  backToTop.setAttribute('aria-label', 'Revenir en haut');
  backToTop.textContent = '↑';
  document.body.appendChild(backToTop);

  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  const handleBackToTop = () => {
    const shouldShow = window.scrollY > 240;
    backToTop.classList.toggle('is-visible', shouldShow);
  };

  window.addEventListener('scroll', handleBackToTop, { passive: true });
  handleBackToTop();

  filterReports();
});
