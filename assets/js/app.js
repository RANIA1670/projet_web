document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-search-form]');
  const input = document.querySelector('[data-search-input]');
  const items = [...document.querySelectorAll('[data-report-item]')];
  const emptyState = document.querySelector('[data-empty-state]');

  if (!form || !input || items.length === 0 || !emptyState) {
    return;
  }

  const filterReports = () => {
    const query = input.value.trim().toLowerCase();
    let visibleCount = 0;

    items.forEach((item) => {
      const haystack = item.dataset.searchText || '';
      const visible = query === '' || haystack.includes(query);
      item.hidden = !visible;

      if (visible) {
        visibleCount += 1;
      }
    });

    emptyState.hidden = visibleCount !== 0;
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    filterReports();
  });

  input.addEventListener('input', filterReports);
});
