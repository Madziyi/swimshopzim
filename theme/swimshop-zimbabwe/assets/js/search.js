(() => {
  const openButton = document.querySelector('[data-search-toggle]');
  const closeButton = document.querySelector('[data-search-close]');
  const panel = document.querySelector('[data-search-panel]');

  if (!openButton || !panel) return;

  const prepareInput = () => {
    const input = panel.querySelector('input[type="search"]');
    if (input) {
      input.setAttribute('aria-label', 'Search products');
      input.setAttribute('placeholder', 'Search products…');
    }
    return input;
  };

  prepareInput();

  const close = (restoreFocus = true) => {
    panel.hidden = true;
    openButton.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('ssz-search-active');
    if (restoreFocus) openButton.focus();
  };

	const open = () => {
		document.dispatchEvent(new CustomEvent('ssz:close-menu'));
		document.dispatchEvent(new CustomEvent('ssz:close-desktop-menus'));
		document.dispatchEvent(new CustomEvent('ssz:close-filters'));
		panel.hidden = false;
		openButton.setAttribute('aria-expanded', 'true');
		document.body.classList.add('ssz-search-active');
		prepareInput()?.focus();
	};

  openButton.addEventListener('click', () => {
    if (panel.hidden) {
      open();
    } else {
      close();
    }
  });

  closeButton?.addEventListener('click', () => close());

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) {
      event.preventDefault();
      close();
    }
  });

  document.addEventListener('ssz:close-search', () => {
    if (!panel.hidden) close(false);
  });
})();
