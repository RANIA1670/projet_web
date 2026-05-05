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

  const initVoiceToText = () => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (typeof SpeechRecognition !== 'function') {
      showToast('Dictee vocale non supportee sur ce navigateur. Utilisez Chrome ou Edge.');
      return;
    }

    const langStorageKey = 'cityzen_vtt_lang';
    const supportedInputTypes = new Set(['text', 'search', 'email', 'tel', 'url']);
    const languageChoices = [
      { code: 'fr-FR', label: 'Francais (FR)' },
      { code: 'fr-CA', label: 'Francais (CA)' },
      { code: 'en-US', label: 'English (US)' },
      { code: 'ar-MA', label: 'Arabic (MA)' },
    ];

    let activeSession = null;
    let languageCode = window.localStorage.getItem(langStorageKey) || 'fr-FR';
    if (!languageChoices.some((item) => item.code === languageCode)) {
      languageCode = 'fr-FR';
    }

    const punctuationRules = [
      { pattern: /\bretour a la ligne\b/gi, replacement: '\n' },
      { pattern: /\bnouvelle ligne\b/gi, replacement: '\n' },
      { pattern: /\bpoint d interrogation\b/gi, replacement: '?' },
      { pattern: /\bpoint d exclamation\b/gi, replacement: '!' },
      { pattern: /\bdeux points\b/gi, replacement: ':' },
      { pattern: /\bpoint virgule\b/gi, replacement: ';' },
      { pattern: /\bvirgule\b/gi, replacement: ',' },
      { pattern: /\bpoint\b/gi, replacement: '.' },
      { pattern: /\barobase\b/gi, replacement: '@' },
      { pattern: /\btiret\b/gi, replacement: '-' },
    ];

    const normalizeTranscript = (rawText) => {
      let normalized = rawText.trim();
      punctuationRules.forEach((rule) => {
        normalized = normalized.replace(rule.pattern, rule.replacement);
      });
      normalized = normalized.replace(/\s+([,.;:!?])/g, '$1');
      normalized = normalized.replace(/([,.;:!?])([^\s])/g, '$1 $2');
      normalized = normalized.replace(/\s*\n\s*/g, '\n');
      normalized = normalized.replace(/[ \t]{2,}/g, ' ');
      return normalized.trim();
    };

    const buildChunkWithSpacing = (left, right, text) => {
      if (text === '') {
        return '';
      }

      const needsSpaceBefore = left !== '' && !/[ \n\t]$/.test(left) && text[0] !== '\n';
      const needsSpaceAfter = right !== '' && !/^[ \n\t]/.test(right) && text[text.length - 1] !== '\n';
      return `${needsSpaceBefore ? ' ' : ''}${text}${needsSpaceAfter ? ' ' : ''}`;
    };

    const renderSessionText = (session, committedText, interimText) => {
      const parts = [];
      if (committedText !== '') {
        parts.push(committedText);
      }
      if (interimText !== '') {
        parts.push(interimText);
      }

      const spokenText = parts.join(' ').trim();
      const chunk = buildChunkWithSpacing(session.leadingValue, session.trailingValue, spokenText);
      const nextValue = `${session.leadingValue}${chunk}${session.trailingValue}`;
      const nextCaret = (session.leadingValue + chunk).length;

      session.field.value = nextValue;
      try {
        if (typeof session.field.setSelectionRange === 'function') {
          session.field.setSelectionRange(nextCaret, nextCaret);
        }
      } catch (error) {
        // Some input types do not allow caret selection updates.
      }
      session.field.dispatchEvent(new Event('input', { bubbles: true }));
      session.field.dispatchEvent(new Event('change', { bubbles: true }));
      session.committedText = committedText;
      session.interimText = interimText;
    };

    const isEligibleField = (field) => {
      if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement)) {
        return false;
      }
      if (field.disabled || field.readOnly) {
        return false;
      }
      if (field.dataset.vtt === 'off') {
        return false;
      }

      if (field instanceof HTMLTextAreaElement) {
        return true;
      }

      const type = (field.type || 'text').toLowerCase();
      return supportedInputTypes.has(type);
    };

    const setSessionState = (button, listening) => {
      button.classList.toggle('is-listening', listening);
      button.setAttribute('aria-pressed', String(listening));
      button.setAttribute('aria-label', listening ? 'Arreter la dictee vocale' : 'Demarrer la dictee vocale');
      button.title = listening ? 'Arreter la dictee vocale (Alt+M)' : 'Demarrer la dictee vocale (Alt+M)';
    };

    const stopActiveSession = (notifyMessage = '') => {
      if (!activeSession) {
        return;
      }

      const { recognition, button, field, silenceTimer } = activeSession;
      window.clearTimeout(silenceTimer);
      activeSession.running = false;

      try {
        recognition.stop();
      } catch (error) {
        // ignore stop errors triggered by race conditions
      }

      setSessionState(button, false);
      field.classList.remove('is-vtt-active');
      activeSession = null;

      if (notifyMessage !== '') {
        showToast(notifyMessage);
      }
    };

    const resetSilenceTimeout = (field, button, recognition) => {
      if (!activeSession || activeSession.field !== field) {
        return;
      }

      window.clearTimeout(activeSession.silenceTimer);
      activeSession.silenceTimer = window.setTimeout(() => {
        if (!activeSession || activeSession.field !== field) {
          return;
        }
        activeSession.running = false;
        try {
          recognition.stop();
        } catch (error) {
          // ignore stop errors
        }
        setSessionState(button, false);
        field.classList.remove('is-vtt-active');
        activeSession = null;
        showToast('Dictee arretee (silence detecte).');
      }, 12000);
    };

    const startSession = (field, button) => {
      if (activeSession && activeSession.field !== field) {
        stopActiveSession();
      }

      if (activeSession && activeSession.field === field) {
        stopActiveSession('Dictee arretee.');
        return;
      }

      const recognition = new SpeechRecognition();
      recognition.lang = languageCode;
      recognition.interimResults = true;
      recognition.continuous = true;
      recognition.maxAlternatives = 1;
      let insertStart = field.value.length;
      let insertEnd = field.value.length;
      try {
        insertStart = field.selectionStart ?? field.value.length;
        insertEnd = field.selectionEnd ?? field.value.length;
      } catch (error) {
        insertStart = field.value.length;
        insertEnd = field.value.length;
      }

      const session = {
        field,
        button,
        recognition,
        running: true,
        silenceTimer: 0,
        leadingValue: field.value.slice(0, insertStart),
        trailingValue: field.value.slice(insertEnd),
        committedText: '',
        interimText: '',
      };
      activeSession = session;

      recognition.onstart = () => {
        if (!activeSession || activeSession.field !== field) {
          return;
        }
        setSessionState(button, true);
        field.classList.add('is-vtt-active');
        showToast(`Dictee active (${languageCode}).`);
        resetSilenceTimeout(field, button, recognition);
      };

      recognition.onresult = (event) => {
        if (!activeSession || activeSession.field !== field) {
          return;
        }

        let finalText = '';
        let interimText = '';

        for (let i = 0; i < event.results.length; i += 1) {
          const spoken = event.results[i][0]?.transcript || '';
          if (event.results[i].isFinal) {
            finalText += `${spoken} `;
          } else {
            interimText += `${spoken} `;
          }
        }

        const normalizedFinal = normalizeTranscript(finalText);
        const normalizedInterim = normalizeTranscript(interimText);

        if (
          normalizedFinal !== activeSession.committedText ||
          normalizedInterim !== activeSession.interimText
        ) {
          renderSessionText(activeSession, normalizedFinal, normalizedInterim);
        }

        resetSilenceTimeout(field, button, recognition);
      };

      recognition.onerror = (event) => {
        if (!activeSession || activeSession.field !== field) {
          return;
        }

        const blocked = event.error === 'not-allowed' || event.error === 'service-not-allowed';
        const message = blocked
          ? 'Micro non autorise. Activez la permission du navigateur.'
          : `Dictee indisponible (${event.error}).`;
        showToast(message);
        stopActiveSession();
      };

      recognition.onend = () => {
        if (!activeSession || activeSession.field !== field) {
          return;
        }

        if (activeSession.running) {
          try {
            recognition.start();
            return;
          } catch (error) {
            // If restart fails, fall through and stop cleanly.
          }
        }

        setSessionState(button, false);
        field.classList.remove('is-vtt-active');
        window.clearTimeout(activeSession.silenceTimer);
        activeSession = null;
      };

      try {
        recognition.start();
      } catch (error) {
        showToast('Impossible de demarrer la dictee.');
        stopActiveSession();
      }
    };

    const createLanguagePanel = () => {
      const panel = document.createElement('div');
      panel.className = 'cityzen-vtt-panel';

      const label = document.createElement('label');
      label.className = 'cityzen-vtt-label';
      label.textContent = 'Dictee vocale';
      label.htmlFor = 'cityzen-vtt-lang';

      const select = document.createElement('select');
      select.id = 'cityzen-vtt-lang';
      select.className = 'cityzen-vtt-select';
      select.setAttribute('aria-label', 'Langue de dictee vocale');

      languageChoices.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.code;
        option.textContent = item.label;
        option.selected = item.code === languageCode;
        select.appendChild(option);
      });

      select.addEventListener('change', () => {
        languageCode = select.value;
        window.localStorage.setItem(langStorageKey, languageCode);
        showToast(`Langue vocale: ${languageCode}`);
      });

      const hint = document.createElement('p');
      hint.className = 'cityzen-vtt-hint';
      hint.textContent = 'Raccourci: Alt+M';

      panel.append(label, select, hint);
      document.body.appendChild(panel);
    };

    const bindVoiceButton = (field) => {
      if (!isEligibleField(field) || field.dataset.vttReady === '1') {
        return;
      }

      const wrapper = document.createElement('span');
      wrapper.className = 'cityzen-vtt-field';
      field.parentNode?.insertBefore(wrapper, field);
      wrapper.appendChild(field);

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'cityzen-vtt-btn';
      button.setAttribute('aria-pressed', 'false');
      button.setAttribute('aria-label', 'Demarrer la dictee vocale');
      button.title = 'Demarrer la dictee vocale (Alt+M)';
      button.textContent = 'Mic';

      button.addEventListener('click', () => {
        startSession(field, button);
      });

      wrapper.appendChild(button);
      field.dataset.vttReady = '1';
    };

    const voiceFields = [...document.querySelectorAll('input, textarea')].filter((field) => isEligibleField(field));
    if (!voiceFields.length) {
      return;
    }

    voiceFields.forEach((field) => {
      bindVoiceButton(field);
    });
    createLanguagePanel();

    document.addEventListener('keydown', (event) => {
      if (!event.altKey || event.key.toLowerCase() !== 'm') {
        return;
      }

      const focused = document.activeElement;
      if (!isEligibleField(focused)) {
        return;
      }

      const button = focused.parentElement?.querySelector('.cityzen-vtt-btn');
      if (!button) {
        return;
      }

      event.preventDefault();
      button.click();
    });
  };

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

  initVoiceToText();
  filterReports();
});
