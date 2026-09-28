(function () {
  'use strict';

  const cards = document.querySelectorAll('[data-ssz-colour-swatches]');

  cards.forEach((swatchRow) => {
    const card = swatchRow.closest('.ssz-product-card');
    const primary = card?.querySelector('.ssz-product-card__image--primary');
    let variationData = {};

    try {
      variationData = JSON.parse(swatchRow.dataset.sszColourVariations || '{}');
    } catch (error) {
      variationData = {};
    }

    if (!card || !primary) return;

    const original = {
      src: primary.getAttribute('src') || '',
      srcset: primary.getAttribute('srcset') || '',
      sizes: primary.getAttribute('sizes') || '',
      alt: primary.getAttribute('alt') || '',
    };

    [...swatchRow.querySelectorAll('[data-ssz-colour-swatch]')]
      .filter((button) => typeof variationData[button.dataset.colourSlug || '']?.src === 'string' && variationData[button.dataset.colourSlug || ''].src.trim() !== '')
      .forEach((button) => {
      button.addEventListener('click', () => {
        const slug = button.dataset.colourSlug || '';
        const image = variationData[slug];

        if (!image || typeof image.src !== 'string' || image.src.trim() === '') return;

        swatchRow.querySelectorAll('[data-ssz-colour-swatch]').forEach((item) => item.setAttribute('aria-pressed', item === button ? 'true' : 'false'));
        card.classList.add('ssz-product-card--colour-preview');
        card.dataset.sszOriginalPrimary = JSON.stringify(original);

        if (image) {
          primary.setAttribute('src', image.src || original.src);
          if (image.srcset) primary.setAttribute('srcset', image.srcset); else primary.removeAttribute('srcset');
          if (image.sizes) primary.setAttribute('sizes', image.sizes); else primary.removeAttribute('sizes');
          primary.setAttribute('alt', image.alt || original.alt);
        }
      });
    });
  });
}());
