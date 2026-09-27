(() => {
  const openButton = document.querySelector('[data-search-toggle]');
  const closeButton = document.querySelector('[data-search-close]');
  const panel = document.querySelector('[data-search-panel]');

  if (!openButton || !panel) return;

  const input = panel.querySelector('input[type="search"]');

  const close = (restoreFocus = true) => {
    panel.hidden = true;
    openButton.setAttribute('aria-expanded', 'false');
    if (restoreFocus) openButton.focus();
  };

  const open = () => {
		 document.dispatchEvent(new CustomEvent('ssz:close-menu'));
		 panel.hidden = false;
		 openButton.setAttribute('aria-expanded', 'true');
		 input?.focus();
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
