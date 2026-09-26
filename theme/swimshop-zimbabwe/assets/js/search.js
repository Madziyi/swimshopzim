(() => {
  const openButton = document.querySelector('[data-search-toggle]');
  const closeButton = document.querySelector('[data-search-close]');
  const panel = document.querySelector('[data-search-panel]');

  if (!openButton || !panel) return;

  const input = panel.querySelector('input[type="search"]');

  const open = () => {
    panel.hidden = false;
    openButton.setAttribute('aria-expanded', 'true');
    requestAnimationFrame(() => input?.focus());
  };

  const close = () => {
    panel.hidden = true;
    openButton.setAttribute('aria-expanded', 'false');
    openButton.focus();
  };

  openButton.addEventListener('click', open);
  closeButton?.addEventListener('click', close);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) close();
  });
})();
