(() => {
  const shell = document.querySelector('[data-archive-filter-shell]');
  const toggle = document.querySelector('[data-archive-filters-toggle]');
  const panel = shell?.querySelector('[data-archive-filter-panel]') || shell?.querySelector('.ssz-filter-drawer__panel');
  const closeButton = shell?.querySelector('[data-archive-filter-close]');
  const backdrop = shell?.querySelector('[data-archive-filter-backdrop]');
  const form = shell?.querySelector('[data-archive-filter-form]');

  if (!shell || !toggle || !panel) return;

  const focusable = (container) => [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
    .filter((element) => !element.hidden && element.offsetParent !== null);

  const close = (restoreFocus = true) => {
    panel.hidden = true;
    shell.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('ssz-filter-open');
    document.body.classList.remove('ssz-filter-open');
    if (restoreFocus) toggle.focus();
  };

  const open = () => {
    document.dispatchEvent(new CustomEvent('ssz:close-search'));
    document.dispatchEvent(new CustomEvent('ssz:close-menu'));
    document.dispatchEvent(new CustomEvent('ssz:close-desktop-menus'));
    panel.hidden = false;
    shell.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    document.documentElement.classList.add('ssz-filter-open');
    document.body.classList.add('ssz-filter-open');
    requestAnimationFrame(() => closeButton?.focus());
  };

  toggle.addEventListener('click', () => (panel.hidden ? open() : close()));
  closeButton?.addEventListener('click', () => close());
  backdrop?.addEventListener('click', () => close());

  document.addEventListener('keydown', (event) => {
    if (panel.hidden) return;

    if (event.key === 'Escape') {
      event.preventDefault();
      close();
      return;
    }

    if (event.key !== 'Tab') return;
    const elements = focusable(panel);
    if (!elements.length) return;
    const first = elements[0];
    const last = elements[elements.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  document.addEventListener('ssz:close-filters', () => {
    if (!panel.hidden) close(false);
  });

  shell.addEventListener('click', (event) => {
    const accordion = event.target.closest('[data-archive-accordion]');
    if (!accordion) return;
    const target = document.getElementById(accordion.getAttribute('aria-controls'));
    if (!target) return;
    const isOpen = accordion.getAttribute('aria-expanded') === 'true';
    target.hidden = isOpen;
    accordion.setAttribute('aria-expanded', String(!isOpen));
    const icon = accordion.querySelector('span[aria-hidden="true"]');
    if (icon) icon.textContent = isOpen ? '+' : '−';
  });

  form?.addEventListener('submit', () => {
    const grouped = new Map();
    form.querySelectorAll('[data-archive-filter-checkbox]').forEach((input) => {
      const key = input.getAttribute('data-archive-filter-checkbox');
      if (!grouped.has(key)) grouped.set(key, []);
      if (input.checked) grouped.get(key).push(input.value);
      input.disabled = true;
    });

    grouped.forEach((values, key) => {
      if (!values.length) return;
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = key;
      hidden.value = values.join(',');
      form.appendChild(hidden);
      if (key === 'filter_size' || key === 'filter_colour') {
        const queryType = document.createElement('input');
        queryType.type = 'hidden';
        queryType.name = `query_type_${key.replace('filter_', '')}`;
        queryType.value = 'or';
        form.appendChild(queryType);
      }
    });
  });
})();
