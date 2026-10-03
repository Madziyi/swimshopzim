(() => {
  const drawer = document.querySelector('[data-cart-drawer]');
  const headerLink = document.querySelector('.ssz-cart-link');
  const panel = drawer?.querySelector('[data-cart-panel]');
  const backdrop = drawer?.querySelector('[data-cart-backdrop]');
  const status = drawer?.querySelector('[data-cart-status]');

  if (!drawer || !panel || !headerLink) return;

  let lastFocused = headerLink;
  let mutationInFlight = false;
  let fragmentRefreshPending = false;
  let lastFragmentRefresh = 0;

  const focusable = (container) => [...container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
    .filter((element) => !element.hidden && element.offsetParent !== null);

  const applyFragment = (selector, html) => {
    if (!html || typeof html !== 'string') return;
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    const replacement = template.content.firstElementChild;
    if (!replacement) return;
    document.querySelectorAll(selector).forEach((element) => element.replaceWith(replacement.cloneNode(true)));
  };

  const applyFragments = (fragments) => {
    if (!fragments) return;
    Object.entries(fragments).forEach(([selector, html]) => applyFragment(selector, html));
    updateEmptyCartState();
    normalizeCartBlockCopy();
    ensureCartPageSubtotal();
  };

  const setStatus = (message = '') => {
    if (!status) return;
    status.textContent = message;
    status.hidden = !message;
  };

  const setLoading = (item = null, isLoading = true) => {
    drawer.classList.toggle('is-loading', isLoading);
    panel.setAttribute('aria-busy', String(isLoading));
    const controls = item ? item.querySelectorAll('button, input, a[data-cart-action="remove"]') : drawer.querySelectorAll('[data-cart-action], [data-cart-quantity-input]');
    controls.forEach((control) => {
      if (isLoading) {
        control.dataset.sszWasDisabled = control.disabled ? 'true' : 'false';
        control.disabled = true;
        control.setAttribute('aria-disabled', 'true');
      } else {
        control.disabled = control.dataset.sszWasDisabled === 'true';
        control.removeAttribute('aria-disabled');
        delete control.dataset.sszWasDisabled;
      }
    });
  };

  const close = (restoreFocus = true) => {
    drawer.hidden = true;
    drawer.setAttribute('aria-hidden', 'true');
    headerLink.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('ssz-cart-open');
    document.body.classList.remove('ssz-cart-open');
    setStatus('');
    setLoading(null, false);
    if (restoreFocus && lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  };

  const open = (origin = headerLink) => {
    document.dispatchEvent(new CustomEvent('ssz:close-search'));
    document.dispatchEvent(new CustomEvent('ssz:close-filters'));
    document.dispatchEvent(new CustomEvent('ssz:close-menu'));
    document.dispatchEvent(new CustomEvent('ssz:close-desktop-menus'));
    lastFocused = origin && typeof origin.focus === 'function' ? origin : headerLink;
    drawer.hidden = false;
    drawer.setAttribute('aria-hidden', 'false');
    headerLink.setAttribute('aria-expanded', 'true');
    document.documentElement.classList.add('ssz-cart-open');
    document.body.classList.add('ssz-cart-open');
    requestAnimationFrame(() => panel.querySelector('[data-cart-close]')?.focus());
  };

  const parseQuantity = (input, fallback = 1) => {
    const min = Number.parseFloat(input.min || '1');
    const max = input.max ? Number.parseFloat(input.max) : Number.POSITIVE_INFINITY;
    const step = Number.parseFloat(input.step || '1');
    const requested = Number.parseFloat(input.value);
    const safe = Number.isFinite(requested) ? requested : fallback;
    const stepped = Number.isFinite(step) && step > 0 ? Math.round(safe / step) * step : safe;
    return Math.max(min, Math.min(max, Number(stepped.toFixed(4))));
  };

  const refreshFragments = async () => {
    if (!window.SSZCart?.fragmentsUrl) return;
    const response = await fetch(window.SSZCart.fragmentsUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) throw new Error('Fragment refresh failed');
    const data = await response.json();
    applyFragments(data.fragments);
  };

  const mutateCart = async (item, action, quantity) => {
    const key = item?.dataset.cartItemKey;
    if (mutationInFlight || !key || !window.SSZCart?.storeApiUrl || !window.SSZCart?.storeApiNonce) return;

    const endpoint = action === 'remove' ? 'cart/remove-item' : 'cart/update-item';
    const body = action === 'remove' ? { key } : { key, quantity };
    mutationInFlight = true;
    setStatus('');
    setLoading(item, true);

    try {
      const response = await fetch(`${window.SSZCart.storeApiUrl}${endpoint}`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Nonce: window.SSZCart.storeApiNonce,
        },
        body: JSON.stringify(body),
      });
      if (!response.ok) throw new Error('Cart update failed');
      await response.json();
      await refreshFragments();
    } catch (error) {
      setStatus('We could not update your bag. Please try again.');
    } finally {
      setLoading(item, false);
      mutationInFlight = false;
    }
  };

  const updateEmptyCartState = () => {
    const emptyState = document.querySelector('[data-ssz-cart-empty-state]');
    const blockEmpty = Boolean(document.querySelector('.wc-block-cart__empty-cart__title'));
    const brandedEmpty = Boolean(emptyState && blockEmpty);
    if (emptyState) emptyState.hidden = !brandedEmpty;
    document.body.classList.toggle('ssz-cart-has-branded-empty', brandedEmpty);
    document.querySelectorAll('.wc-block-cart__empty-cart__title').forEach((title) => {
      title.classList.toggle('ssz-cart-native-empty-suppressed', brandedEmpty);
    });
  };

  const normalizeCartBlockCopy = () => {
    const checkoutText = document.querySelector('.wc-block-cart__submit-button .wc-block-components-button__text');
    if (checkoutText && checkoutText.textContent.trim() !== 'CHECKOUT') checkoutText.textContent = 'CHECKOUT';
    const summaryHeading = document.querySelector('.wc-block-cart__totals-title');
    if (summaryHeading && summaryHeading.textContent.trim() !== 'ORDER SUMMARY') summaryHeading.textContent = 'ORDER SUMMARY';
  };

  const ensureCartPageSubtotal = () => {
    const totals = document.querySelector('.wp-block-woocommerce-cart-order-summary-totals-block');
    if (!totals || !window.SSZCart?.initialSubtotalHtml) return;
    if (totals.querySelector('[data-cart-page-subtotal]')) return;
    const bridge = document.createElement('div');
    bridge.className = 'ssz-cart-block-subtotal';
    bridge.dataset.cartPageSubtotal = '';
    bridge.innerHTML = `<div class="ssz-cart-block-subtotal__label">Subtotal</div><div class="ssz-cart-block-subtotal__value">${window.SSZCart.initialSubtotalHtml}</div>`;
    totals.prepend(bridge);
  };

  headerLink.addEventListener('click', (event) => {
    event.preventDefault();
    if (drawer.hidden) open(headerLink);
    else close();
  });

  backdrop?.addEventListener('click', () => close());

  drawer.addEventListener('click', (event) => {
    const closeControl = event.target.closest('[data-cart-close]');
    if (closeControl) {
      event.preventDefault();
      close();
      return;
    }

    const action = event.target.closest('[data-cart-action]');
    if (!action) return;
    const item = action.closest('[data-cart-item-key]');
    if (!item) return;
    if (mutationInFlight) return;

    if (action.dataset.cartAction === 'remove') {
      event.preventDefault();
      mutateCart(item, 'remove');
      return;
    }

    if (!['increase', 'decrease'].includes(action.dataset.cartAction)) return;
    const input = item.querySelector('[data-cart-quantity-input]');
    if (!input) return;
    const step = Number.parseFloat(input.step || '1');
    const current = parseQuantity(input);
    input.value = String(action.dataset.cartAction === 'increase' ? current + step : current - step);
    mutateCart(item, 'update', parseQuantity(input));
  });

  drawer.addEventListener('change', (event) => {
    const input = event.target.closest('[data-cart-quantity-input]');
    if (!input) return;
    if (mutationInFlight) return;
    const item = input.closest('[data-cart-item-key]');
    if (!item) return;
    const quantity = parseQuantity(input);
    input.value = String(quantity);
    mutateCart(item, 'update', quantity);
  });

  document.addEventListener('keydown', (event) => {
    if (drawer.hidden) return;
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

  document.addEventListener('ssz:close-cart', () => {
    if (!drawer.hidden) close(false);
  });

  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart.sszCart', () => {
      window.setTimeout(() => open(headerLink), 40);
    });
  }

  const cartRoot = document.querySelector('[data-block-name="woocommerce/cart"]');
  if (cartRoot) {
    const observer = new MutationObserver(() => {
      updateEmptyCartState();
      normalizeCartBlockCopy();
      ensureCartPageSubtotal();
      if (Date.now() - lastFragmentRefresh < 1000 || fragmentRefreshPending) return;
      fragmentRefreshPending = true;
      window.setTimeout(async () => {
        try {
          await refreshFragments();
          lastFragmentRefresh = Date.now();
        } catch (error) {
          // The native Cart Block still owns the full-page result if fragments fail.
        } finally {
          fragmentRefreshPending = false;
        }
      }, 250);
    });
    observer.observe(cartRoot, { childList: true, subtree: true });
  }

  updateEmptyCartState();
  normalizeCartBlockCopy();
  ensureCartPageSubtotal();

  if (drawer.dataset.autoOpen === 'true') {
    drawer.dataset.autoOpen = 'false';
    window.setTimeout(() => {
      open(headerLink);
      window.setTimeout(() => panel.querySelector('[data-cart-close]')?.focus(), 220);
    }, 40);
  }
})();
