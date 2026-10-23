/* Five Star Tours admin — small enhancements. */
(() => {
  'use strict';

  // Instant table search: <input data-table-filter="#tableId">
  document.querySelectorAll('[data-table-filter]').forEach((input) => {
    const table = document.querySelector(input.dataset.tableFilter);
    if (!table) return;
    const rows = [...table.querySelectorAll('tbody tr:not(.no-results)')];
    const empty = table.querySelector('.no-results');
    input.addEventListener('input', () => {
      const term = input.value.trim().toLowerCase();
      let shown = 0;
      rows.forEach((row) => {
        const match = row.textContent.toLowerCase().includes(term);
        row.hidden = !match;
        if (match) shown++;
      });
      if (empty) empty.style.display = shown ? 'none' : 'table-row';
    });
  });

  // Live preview for image uploads: <input type="file" data-preview="#imgId">
  document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
    const preview = document.querySelector(input.dataset.preview);
    input.addEventListener('change', () => {
      const [file] = input.files;
      if (file && preview) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
      }
    });
  });
})();
