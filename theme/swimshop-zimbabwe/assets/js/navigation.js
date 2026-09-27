(() => {
  const menuToggle = document.querySelector('[data-menu-toggle]');
  const mobileNav = document.querySelector('[data-mobile-nav]');
  const mobileDrawer = document.querySelector('[data-mobile-drawer]');
  const mobileClose = document.querySelector('[data-mobile-close]');
  const mobileBackdrop = document.querySelector('[data-mobile-backdrop]');
  const rootMenu = document.querySelector('[data-mobile-nav] .ssz-mobile-menu');

  const desktopItems = [...document.querySelectorAll('[data-primary-nav] .ssz-nav-item--has-children')];

  const focusable = (container) => [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
    .filter((element) => !element.hidden && element.offsetParent !== null);

	const closeDesktopMenus = (except = null) => {
		desktopItems.forEach((item) => {
			if (item !== except) {
				item.classList.remove('is-open');
				item.classList.remove('ssz-menu-dismissed');
				item.querySelector('[data-nav-toggle]')?.setAttribute('aria-expanded', 'false');
			}
		});
  };

  const resetMobilePanels = () => {
    if (!rootMenu) return;

    rootMenu.querySelectorAll('[data-mobile-panel]').forEach((panel) => {
      panel.hidden = true;
    });
    rootMenu.querySelectorAll('[data-mobile-accordion-panel]').forEach((panel) => {
      panel.hidden = true;
    });
    rootMenu.querySelectorAll('[data-mobile-drilldown], [data-mobile-accordion]').forEach((button) => {
      button.setAttribute('aria-expanded', 'false');
    });
  };

  const closeMenu = (restoreFocus = true) => {
    if (!menuToggle || !mobileNav) return;

    mobileNav.hidden = true;
    mobileNav.setAttribute('aria-hidden', 'true');
    menuToggle.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('ssz-menu-open');
    resetMobilePanels();

    if (restoreFocus) menuToggle.focus();
  };

  const openMenu = () => {
    if (!menuToggle || !mobileNav) return;

    document.dispatchEvent(new CustomEvent('ssz:close-search'));
    closeDesktopMenus();
    resetMobilePanels();
    mobileNav.hidden = false;
    mobileNav.setAttribute('aria-hidden', 'false');
    menuToggle.setAttribute('aria-expanded', 'true');
    document.documentElement.classList.add('ssz-menu-open');
    requestAnimationFrame(() => mobileClose?.focus());
  };

  if (menuToggle && mobileNav) {
    menuToggle.addEventListener('click', () => {
      if (mobileNav.hidden) {
        openMenu();
      } else {
        closeMenu();
      }
    });

    mobileClose?.addEventListener('click', () => closeMenu());
    mobileBackdrop?.addEventListener('click', () => closeMenu());

    mobileNav.addEventListener('click', (event) => {
      const drilldown = event.target.closest('[data-mobile-drilldown]');
      const accordion = event.target.closest('[data-mobile-accordion]');
      const back = event.target.closest('[data-mobile-back]');

      if (drilldown) {
        const panel = document.getElementById(drilldown.getAttribute('aria-controls'));
        if (!panel) return;
        panel.hidden = false;
        drilldown.setAttribute('aria-expanded', 'true');
        requestAnimationFrame(() => panel.querySelector('[data-mobile-back]')?.focus());
      }

      if (accordion) {
        const panel = document.getElementById(accordion.getAttribute('aria-controls'));
        if (!panel) return;
        const isOpen = accordion.getAttribute('aria-expanded') === 'true';
        panel.hidden = isOpen;
        accordion.setAttribute('aria-expanded', String(!isOpen));
      }

      if (back) {
        const panel = back.closest('[data-mobile-panel]');
        if (!panel) return;
        panel.hidden = true;
        const trigger = rootMenu?.querySelector(`[data-mobile-drilldown][aria-controls="${panel.id}"]`);
        trigger?.setAttribute('aria-expanded', 'false');
        requestAnimationFrame(() => trigger?.focus());
      }
    });

    document.addEventListener('keydown', (event) => {
      if (mobileNav.hidden) return;

      if (event.key === 'Escape') {
        event.preventDefault();
        closeMenu();
        return;
      }

      if (event.key !== 'Tab' || !mobileDrawer) return;
      const elements = focusable(mobileDrawer);
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

    document.addEventListener('ssz:close-menu', () => {
      if (!mobileNav.hidden) closeMenu(false);
    });
  }

	desktopItems.forEach((item) => {
		const toggle = item.querySelector(':scope > [data-nav-toggle]');
		if (!toggle) return;

		item.addEventListener('mouseenter', () => item.classList.remove('ssz-menu-dismissed'));
		item.addEventListener('focusin', (event) => {
			if (event.target === toggle || item.classList.contains('ssz-menu-dismissed')) return;
			closeDesktopMenus(item);
			item.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
		});

		toggle.addEventListener('click', () => {
			const isOpen = item.classList.contains('is-open');
			closeDesktopMenus(item);
			item.classList.remove('ssz-menu-dismissed');
			item.classList.toggle('is-open', !isOpen);
			toggle.setAttribute('aria-expanded', String(!isOpen));
		});
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const openItem = desktopItems.find((item) => item.classList.contains('is-open'));
		if (!openItem) return;
		openItem.classList.remove('is-open');
		openItem.classList.add('ssz-menu-dismissed');
		openItem.querySelector('[data-nav-toggle]')?.setAttribute('aria-expanded', 'false');
		openItem.querySelector(':scope > [data-nav-toggle]')?.focus();
	});
})();
