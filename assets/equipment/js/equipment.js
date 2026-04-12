/**
 * Onglets — uniquement si #equipment-tabs est présent (ancienne vue).
 */
document.addEventListener('DOMContentLoaded', () => {
  const tabContainer = document.getElementById('equipment-tabs');
  if (!tabContainer) return;

  const tabBtns = tabContainer.querySelectorAll('.tab-btn');
  const tabPanes = document.querySelectorAll('.tab-pane');
  const initial = tabContainer.dataset.initialTab || 'tab-types';

  function activateTab(targetId) {
    tabBtns.forEach((b) => b.classList.remove('active'));
    tabPanes.forEach((p) => p.classList.remove('active'));
    const btn = Array.from(tabBtns).find((b) => b.dataset.tab === targetId);
    const pane = document.getElementById(targetId);
    if (btn) btn.classList.add('active');
    if (pane) pane.classList.add('active');
  }

  const hashId = window.location.hash ? window.location.hash.slice(1) : '';
  if (hashId && document.getElementById(hashId)) {
    activateTab(hashId);
  } else {
    activateTab(initial);
  }

  tabBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      if (target) activateTab(target);
    });
  });
});
