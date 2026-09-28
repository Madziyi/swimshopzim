import fs from 'node:fs/promises';
import fsSync from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { chromium } from 'playwright';

const baseUrl = process.env.SSZ_LOCAL_URL ?? 'http://swimshop-zimbabwe.local/';
const widths = [360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920];
const outputDir = path.resolve('artifacts/uat');
const requireLocalMenu = process.env.SSZ_REQUIRE_LOCAL_MENU === '1';

const fallbackSources = await Promise.all([
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/inc/navigation.php'), 'utf8'),
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/template-parts/header/desktop-navigation.php'), 'utf8'),
  fs.readFile(path.resolve('theme/swimshop-zimbabwe/template-parts/header/mobile-navigation.php'), 'utf8'),
]);
const fallbackContract = {
  sharedRenderer: fallbackSources[0].includes('function ssz_primary_menu_fallback('),
  desktopClass: fallbackSources[0].includes("'ssz-primary-menu'") && fallbackSources[1].includes('ssz_primary_menu_fallback_desktop'),
  mobileClass: fallbackSources[0].includes("'ssz-mobile-menu'") && fallbackSources[2].includes('ssz_primary_menu_fallback_mobile'),
};

const browserCandidates = [
  process.env.SSZ_BROWSER_PATH,
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Google', 'Chrome', 'Application', 'chrome.exe'),
  process.env.ProgramFiles && path.join(process.env.ProgramFiles, 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
  process.env['ProgramFiles(x86)'] && path.join(process.env['ProgramFiles(x86)'], 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
].filter(Boolean);

const executablePath = browserCandidates.find((candidate) => fsSync.existsSync(candidate));

if (!executablePath) {
  throw new Error('No Chrome or Edge executable found. Set SSZ_BROWSER_PATH to a local browser executable.');
}

await fs.mkdir(outputDir, { recursive: true });

const visible = async (locator) => (await locator.count()) > 0 && await locator.first().isVisible();

const findKeyboardFocus = async (page, predicate) => {
  await page.mouse.click(1, 1);
  for (let attempt = 0; attempt < 120; attempt += 1) {
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const element = document.activeElement;
      if (!element) return null;
      const style = window.getComputedStyle(element);
      return {
        tag: element.tagName,
        id: element.id,
        className: element.className,
        inFooter: Boolean(element.closest('.ssz-footer')),
        isSearch: Boolean(element.matches('[data-search-toggle]')),
        focusVisible: element.matches(':focus-visible'),
        outlineWidth: style.outlineWidth,
        outlineStyle: style.outlineStyle,
        outlineColor: style.outlineColor,
      };
    });

    if (focus && predicate(focus)) return focus;
  }
  return null;
};

const inspectProductCards = async (page, { homepage = false, expectedColumns = null } = {}) => {
  const cardsLocator = page.locator('ul.products li.ssz-product-card');
  const cardCount = await cardsLocator.count();

  for (let index = 0; index < cardCount; index += 1) {
    await cardsLocator.nth(index).scrollIntoViewIfNeeded();
  }
  await page.waitForTimeout(500);

  let hover = { checked: false, changed: null, secondaryLoaded: null };
  const hoverCard = cardsLocator.filter({ hasText: 'STORE-005 TEST Simple Performance Suit' }).first();
  if (await hoverCard.count()) {
    const hoverMedia = hoverCard.locator('.ssz-product-card__media');
    await hoverMedia.scrollIntoViewIfNeeded();
    const hoverBox = await hoverMedia.boundingBox();
    if (hoverBox) {
      const hoverY = hoverBox.y < 140 ? hoverBox.y + hoverBox.height - 10 : hoverBox.y + hoverBox.height / 2;
      await page.mouse.move(hoverBox.x + hoverBox.width / 2, hoverY);
    }
    await page.waitForTimeout(250);
    hover = await page.evaluate(() => {
      const card = [...document.querySelectorAll('ul.products li.ssz-product-card')]
        .find((node) => node.textContent?.includes('STORE-005 TEST Simple Performance Suit'));
      const secondary = card?.querySelector('.ssz-product-card__image--secondary');
      const primary = card?.querySelector('.ssz-product-card__image--primary');
      return {
        checked: Boolean(card && secondary && primary),
        changed: Boolean(card && secondary && primary && getComputedStyle(secondary).opacity === '1' && getComputedStyle(primary).opacity === '0'),
        secondaryLoaded: Boolean(secondary && secondary.complete && secondary.naturalWidth > 0),
      };
    });
    await page.mouse.move(1, 1);
  }

  let swatchInteraction = {
    configured: false,
    allMappedConfigured: false,
    allMappedButtons: false,
    allMappedLabels: false,
    mixedConfigured: false,
    mixedPreviewable: false,
    mixedAvailableOnly: false,
    mixedNoMismatch: false,
    simpleIndicators: false,
    simpleNoFakeButtons: false,
    simpleNoPreviewClass: false,
    simpleHoverPreserved: false,
    singleColourOmitted: false,
    maxFiveVisible: false,
    overflowLabel: false,
    groupSemantics: false,
    focusVisible: false,
    selected: false,
    sourceChanged: false,
    dimensionsStable: false,
    noNavigation: false,
    noAddToCart: false,
    noNestedInteractive: false,
    hoverPreserved: false,
  };

  if (!homepage) {
    const allMappedCard = cardsLocator.filter({ hasText: 'STORE-006 TEST All Mapped Colour Suit' }).first();
    const mixedCard = cardsLocator.filter({ hasText: 'STORE-006 TEST Mixed Colour Suit' }).first();
    const overflowCard = cardsLocator.filter({ hasText: 'STORE-005 TEST Variable Training Suit' }).first();
    const simpleMultiCard = cardsLocator.filter({ hasText: 'STORE-005 TEST Simple Performance Suit' }).first();
    const singleColourCard = cardsLocator.filter({ hasText: 'STORE-005 TEST Single Image Towel' }).first();
    const allMappedButtons = allMappedCard.locator('[data-ssz-colour-swatch]');
    const mixedSwatches = mixedCard.locator('.ssz-product-card__swatch');
    const mixedAvailable = mixedCard.locator('[data-ssz-colour-available][data-colour-slug="red"]');
    const simpleIndicators = simpleMultiCard.locator('[data-ssz-colour-available]');
    const visibleOverflow = overflowCard.locator('.ssz-product-card__swatches-more');
    swatchInteraction.configured = await allMappedCard.count() > 0 && await mixedCard.count() > 0 && await simpleMultiCard.count() > 0;
    swatchInteraction.allMappedConfigured = await allMappedCard.count() > 0;
    swatchInteraction.allMappedButtons = await allMappedButtons.count() === 3 && await allMappedCard.locator('[data-ssz-colour-available]').count() === 0;
    swatchInteraction.allMappedLabels = await allMappedButtons.evaluateAll((items) => items.every((item) => item.getAttribute('aria-label')?.startsWith('Preview ')));
    swatchInteraction.mixedConfigured = await mixedCard.count() > 0;
    swatchInteraction.mixedPreviewable = await mixedCard.locator('[data-ssz-colour-swatch][data-colour-slug="navy"]').count() === 1;
    swatchInteraction.mixedAvailableOnly = await mixedAvailable.count() === 1 && await mixedAvailable.evaluate((item) => item.textContent?.includes('Available in Red') && !item.hasAttribute('aria-pressed') && item.tabIndex < 0);
    swatchInteraction.simpleIndicators = await simpleIndicators.count() >= 2;
    swatchInteraction.simpleNoFakeButtons = await simpleMultiCard.locator('[data-ssz-colour-swatch]').count() === 0 && await simpleMultiCard.locator('[aria-label^="Preview "]').count() === 0;
    swatchInteraction.singleColourOmitted = await singleColourCard.locator('[data-ssz-colour-swatches]').count() === 0;
    swatchInteraction.maxFiveVisible = await overflowCard.locator('.ssz-product-card__swatch').count() <= 5;
    swatchInteraction.overflowLabel = await visibleOverflow.count() === 1 && /^\+\d+$/.test((await visibleOverflow.innerText()).trim());
    swatchInteraction.groupSemantics = await Promise.all([allMappedCard, mixedCard, simpleMultiCard].map(async (card) => {
      const group = card.locator('[data-ssz-colour-swatches][role="group"][aria-label="Available colours"]');
      return await group.count() === 1;
    })).then((values) => values.every(Boolean));
    swatchInteraction.noNestedInteractive = await Promise.all([allMappedCard, mixedCard, simpleMultiCard].map(async (card) => (
      await card.locator('.woocommerce-loop-product__link button, .woocommerce-loop-product__link a').count() === 0
    ))).then((values) => values.every(Boolean));

    const allMappedMedia = allMappedCard.locator('.ssz-product-card__media');
    const beforeState = await page.evaluate(() => {
      const card = [...document.querySelectorAll('li.ssz-product-card')].find((node) => node.textContent?.includes('STORE-006 TEST All Mapped Colour Suit'));
      const primaryImage = card?.querySelector('.ssz-product-card__image--primary');
      const mediaFrame = card?.querySelector('.ssz-product-card__media');
      const mediaRect = mediaFrame?.getBoundingClientRect();
      return { source: primaryImage?.getAttribute('src') ?? null, width: mediaRect?.width ?? null, height: mediaRect?.height ?? null };
    });
    const beforeUrl = page.url();
    const navy = allMappedCard.locator('[data-ssz-colour-swatch][data-colour-slug="navy"]');
    if (await navy.count()) {
      await navy.click();
      await page.waitForTimeout(120);
      await page.mouse.click(1, 1);
      for (let attempt = 0; attempt < 80; attempt += 1) {
        await page.keyboard.press('Tab');
        const focusState = await navy.evaluate((element) => ({
          active: document.activeElement === element,
          visible: element.matches(':focus-visible') && getComputedStyle(element).outlineWidth === '3px',
        }));
        if (focusState.active) {
          swatchInteraction.focusVisible = focusState.visible;
          break;
        }
      }
      const afterState = await page.evaluate(() => {
        const card = [...document.querySelectorAll('li.ssz-product-card')].find((node) => node.textContent?.includes('STORE-006 TEST All Mapped Colour Suit'));
        const primaryImage = card?.querySelector('.ssz-product-card__image--primary');
        const mediaFrame = card?.querySelector('.ssz-product-card__media');
        const mediaRect = mediaFrame?.getBoundingClientRect();
        const selected = card?.querySelector('[data-ssz-colour-swatch][data-colour-slug="navy"]');
        return {
          source: primaryImage?.getAttribute('src') ?? null,
          width: mediaRect?.width ?? null,
          height: mediaRect?.height ?? null,
          selected: selected?.getAttribute('aria-pressed') === 'true',
          exists: Boolean(card),
          hasPreviewClass: Boolean(card?.classList.contains('ssz-product-card--colour-preview')),
          noAddToCart: Boolean(card && !card.querySelector('.add_to_cart_button, .ajax_add_to_cart, .button')),
          noNestedInteractive: Boolean(card && !card.querySelector('.woocommerce-loop-product__link button, .woocommerce-loop-product__link a')),
        };
      });
      swatchInteraction.selected = afterState.selected;
      swatchInteraction.sourceChanged = Boolean(beforeState.source && afterState.source && beforeState.source !== afterState.source);
      swatchInteraction.dimensionsStable = Boolean(beforeState.width && afterState.width && beforeState.width === afterState.width && beforeState.height === afterState.height);
      swatchInteraction.noNavigation = beforeUrl === page.url();
      swatchInteraction.noAddToCart = afterState.noAddToCart;
      swatchInteraction.noNestedInteractive = swatchInteraction.noNestedInteractive && afterState.noNestedInteractive;
      if (afterState.exists && swatchInteraction.noNavigation) {
        await allMappedMedia.hover();
        await page.waitForTimeout(100);
        swatchInteraction.hoverPreserved = await page.evaluate(() => {
          const card = [...document.querySelectorAll('li.ssz-product-card')].find((node) => node.textContent?.includes('STORE-006 TEST All Mapped Colour Suit'));
          const primaryImage = card?.querySelector('.ssz-product-card__image--primary');
          const secondaryImage = card?.querySelector('.ssz-product-card__image--secondary');
          return Boolean(card && primaryImage && getComputedStyle(primaryImage).opacity === '1' && (!secondaryImage || getComputedStyle(secondaryImage).opacity === '0'));
        });
      }
      await page.mouse.move(1, 1);
    }

    if (await mixedCard.count() && await mixedAvailable.count()) {
      const mixedNavy = mixedCard.locator('[data-ssz-colour-swatch][data-colour-slug="navy"]');
      if (await mixedNavy.count()) {
        await mixedNavy.click();
        await page.waitForTimeout(120);
        const mixedBefore = await mixedCard.locator('.ssz-product-card__image--primary').getAttribute('src');
        await mixedAvailable.evaluate((element) => element.click());
        await page.waitForTimeout(80);
        const mixedAfter = await mixedCard.locator('.ssz-product-card__image--primary').getAttribute('src');
        swatchInteraction.mixedNoMismatch = mixedBefore === mixedAfter && await mixedCard.locator('[data-ssz-colour-swatch][data-colour-slug="red"]').count() === 0 && await mixedCard.locator('[data-ssz-colour-available][data-colour-slug="red"][aria-pressed]').count() === 0;
      }
    }

    if (await simpleMultiCard.count()) {
      const simpleBefore = await simpleMultiCard.evaluate((card) => card.classList.contains('ssz-product-card--colour-preview'));
      const simpleIndicator = simpleMultiCard.locator('[data-ssz-colour-available]').first();
      if (await simpleIndicator.count()) {
        await simpleIndicator.evaluate((element) => element.click());
        await page.waitForTimeout(80);
      }
      swatchInteraction.simpleNoPreviewClass = simpleBefore === false && await simpleMultiCard.evaluate((card) => !card.classList.contains('ssz-product-card--colour-preview'));
      swatchInteraction.simpleHoverPreserved = hover.checked && hover.changed;
    }
  }

  const cardResult = await page.evaluate(({ homepage, expectedColumns }) => {
    const cards = [...document.querySelectorAll('ul.products li.ssz-product-card')];
    const style = (element) => element ? getComputedStyle(element) : null;
    const cardDetails = cards.map((card) => {
      const media = card.querySelector('.ssz-product-card__media');
      const title = card.querySelector('.woocommerce-loop-product__title');
      const primary = card.querySelector('.ssz-product-card__image--primary');
      const secondary = card.querySelector('.ssz-product-card__image--secondary');
      const link = card.querySelector('.woocommerce-loop-product__link');
      const rect = media?.getBoundingClientRect();
      const titleStyle = style(title);
      return {
        name: title?.textContent?.trim() ?? '',
        href: link?.getAttribute('href') ?? null,
        media: Boolean(media),
        aspectRatio: rect && rect.height ? Number((rect.width / rect.height).toFixed(3)) : null,
        primaryImage: Boolean(primary && primary.complete && primary.naturalWidth > 0),
        primaryAlt: primary?.getAttribute('alt') ?? null,
        primarySource: primary?.currentSrc || primary?.getAttribute('src') || null,
        primaryNaturalWidth: primary?.naturalWidth ?? 0,
        primaryNaturalHeight: primary?.naturalHeight ?? 0,
        primarySourceRatio: primary?.naturalHeight ? Number((primary.naturalWidth / primary.naturalHeight).toFixed(3)) : null,
        secondaryImage: Boolean(secondary),
        secondaryLoaded: Boolean(secondary && secondary.complete && secondary.naturalWidth > 0),
        secondarySource: secondary?.currentSrc || secondary?.getAttribute('src') || null,
        secondaryNaturalWidth: secondary?.naturalWidth ?? 0,
        secondaryNaturalHeight: secondary?.naturalHeight ?? 0,
        secondarySourceRatio: secondary?.naturalHeight ? Number((secondary.naturalWidth / secondary.naturalHeight).toFixed(3)) : null,
        imageCount: media?.querySelectorAll('img').length ?? 0,
        brand: card.querySelector('.ssz-product-card__brand')?.textContent?.trim() ?? null,
        title: Boolean(title),
        titleLineClamp: titleStyle?.webkitLineClamp ?? null,
        titleHeight: title ? Number(title.getBoundingClientRect().height.toFixed(2)) : null,
        titleLineHeight: titleStyle ? Number.parseFloat(titleStyle.lineHeight) : null,
        price: Boolean(card.querySelector('.price')),
        saleBadge: card.querySelector('.ssz-product-card__badge')?.textContent?.trim() === 'Sale',
        soldOutBadge: card.querySelector('.ssz-product-card__badge')?.textContent?.trim() === 'Sold out',
        fit: card.classList.contains('ssz-product-card--contain') ? 'contain' : card.classList.contains('ssz-product-card--cover') ? 'cover' : null,
        anchorCount: card.querySelectorAll('a').length,
        nestedAnchors: card.querySelectorAll('a a').length,
        hasLoopButton: Boolean(card.querySelector('.add_to_cart_button, .ajax_add_to_cart, .button')),
        hasRating: Boolean(card.querySelector('.star-rating, .woocommerce-loop-rating')),
        retailPresentation: card.classList.contains('ssz-product-card--retail'),
        swatchCount: card.querySelectorAll('.ssz-product-card__swatch').length,
        previewSwatchCount: card.querySelectorAll('[data-ssz-colour-swatch]').length,
        availableSwatchCount: card.querySelectorAll('[data-ssz-colour-available]').length,
        swatchOverflow: card.querySelector('.ssz-product-card__swatches-more')?.textContent?.trim() ?? null,
        swatchGroup: card.querySelector('[data-ssz-colour-swatches][role="group"][aria-label="Available colours"]') !== null,
        swatchButtonsInLink: card.querySelectorAll('.woocommerce-loop-product__link button, .woocommerce-loop-product__link a').length,
      };
    });
    const findCard = (needle) => cardDetails.find((card) => card.name.includes(needle));
    const grid = document.querySelector('ul.products');
    const gridStyle = style(grid);
    const columnCount = gridStyle?.gridTemplateColumns && gridStyle.gridTemplateColumns !== 'none'
      ? gridStyle.gridTemplateColumns.split(' ').filter(Boolean).length
      : null;
    const productSections = [...document.querySelectorAll('[data-homepage-product-section] ul.products')];
    const railState = productSections.map((section) => {
      const sectionStyle = style(section);
      return {
        display: sectionStyle?.display ?? null,
        overflowX: sectionStyle?.overflowX ?? null,
        scrollWidth: section.scrollWidth,
        clientWidth: section.clientWidth,
      };
    });
    const simple = findCard('Simple Performance Suit');
    const sale = findCard('Sale Racing Goggles');
    const outOfStock = findCard('Sold Out Race Cap');
    const equipment = findCard('Equipment Pull Buoy');
    const singleImage = findCard('Single Image Towel');
    const noImage = findCard('Placeholder Training Bottle');
    const longTitle = findCard('Long Product Title');
    const cardContract = cardDetails.length > 0 && cardDetails.every((card) => (
      card.href && card.media && card.aspectRatio !== null && Math.abs(card.aspectRatio - .8) < .03 &&
      card.primaryImage && card.primaryAlt && card.imageCount >= 1 && card.imageCount <= 2 &&
      card.title && card.price && card.anchorCount === 1 && card.nestedAnchors === 0 &&
      !card.hasLoopButton && !card.hasRating
    ));

    return {
      cardCount: cards.length,
      cardContract,
      cardDetails,
      primaryImagesLoaded: cardDetails.every((card) => card.primaryImage),
      secondaryImages: cardDetails.filter((card) => card.secondaryImage).length,
      secondaryImagesLoaded: cardDetails.filter((card) => card.secondaryImage).every((card) => card.secondaryLoaded),
      onlyTwoImages: cardDetails.every((card) => card.imageCount <= 2),
      coverApparel: Boolean(simple?.fit === 'cover'),
      containEquipment: homepage ? (!equipment || equipment.fit === 'contain') : Boolean(equipment?.fit === 'contain'),
      containEquipmentSourceUncropped: homepage ? (!equipment || (equipment.fit === 'contain' && equipment.primarySourceRatio > 1.5 && !/720x900/.test(equipment.primarySource ?? ''))) : Boolean(equipment?.fit === 'contain' && equipment.primarySourceRatio > 1.5 && !/720x900/.test(equipment.primarySource ?? '')),
      containSecondarySourceUncropped: homepage ? (!equipment || (equipment.fit === 'contain' && equipment.secondaryImage && equipment.secondarySourceRatio > 1.5 && !/720x900/.test(equipment.secondarySource ?? ''))) : Boolean(equipment?.fit === 'contain' && equipment.secondaryImage && equipment.secondarySourceRatio > 1.5 && !/720x900/.test(equipment.secondarySource ?? '')),
      saleState: Boolean(sale?.saleBadge && !sale?.soldOutBadge),
      soldOutState: Boolean(outOfStock?.soldOutBadge && !outOfStock?.saleBadge),
      variablePrice: Boolean(findCard('Variable Training Suit')?.price),
      singleImageStable: homepage ? (!singleImage || (!singleImage.secondaryImage && singleImage.primaryImage)) : Boolean(singleImage && !singleImage.secondaryImage && singleImage.primaryImage),
      noImageState: Boolean(noImage?.primaryImage),
      longTitleBounded: Boolean(longTitle && longTitle.titleLineClamp === '2' && longTitle.titleLineHeight && longTitle.titleHeight <= longTitle.titleLineHeight * 2.15),
      archiveColumns: expectedColumns === null || columnCount === expectedColumns,
      columnCount,
      homepageRail: !homepage || (railState.length === 2 && railState.every((rail) => rail.display === 'flex' && rail.overflowX === 'auto' && rail.scrollWidth >= rail.clientWidth)),
      retailPresentation: homepage ? cardDetails.every((card) => !card.retailPresentation) : cardDetails.every((card) => card.retailPresentation),
      swatchContract: homepage ? cardDetails.every((card) => card.swatchCount === 0) : cardDetails.every((card) => card.swatchButtonsInLink === 0),
      railState,
    };
  }, { homepage, expectedColumns });

  return { ...cardResult, hover, swatchInteraction };
};

const inspectProductPage = async (page) => {
  const relatedCardsForLoad = page.locator('.related.products ul.products li.product');
  for (let index = 0; index < await relatedCardsForLoad.count(); index += 1) {
    await relatedCardsForLoad.nth(index).scrollIntoViewIfNeeded();
  }
  await page.waitForTimeout(180);
  return page.evaluate(() => {
  const mainProduct = document.querySelector('main#primary > .product[id^="product-"]');
  const gallery = document.querySelector('.woocommerce-product-gallery');
  const galleryWrapper = document.querySelector('.woocommerce-product-gallery__wrapper');
  const summary = document.querySelector('main#primary > .product .summary');
  const price = document.querySelector('[data-ssz-product-price]');
  const relatedCards = [...document.querySelectorAll('.related.products ul.products li.product')];
  const relatedDetails = relatedCards.map((card) => {
    const media = card.querySelector('.ssz-product-card__media');
    const primary = card.querySelector('.ssz-product-card__image--primary');
    const title = card.querySelector('.woocommerce-loop-product__title');
    const cardPrice = card.querySelector('.price');
    const rect = media?.getBoundingClientRect();
    return {
      cardClass: card.classList.contains('ssz-product-card'),
      fitClass: card.classList.contains('ssz-product-card--cover') || card.classList.contains('ssz-product-card--contain'),
      media: Boolean(media),
      image: Boolean(primary && primary.complete && primary.naturalWidth > 0),
      title: Boolean(title),
      price: Boolean(cardPrice),
      aspectRatio: rect && rect.height ? Number((rect.width / rect.height).toFixed(3)) : null,
      anchorCount: card.querySelectorAll('a').length,
      retailPresentation: card.classList.contains('ssz-product-card--retail'),
      brandColor: card.querySelector('.ssz-product-card__brand') ? getComputedStyle(card.querySelector('.ssz-product-card__brand')).color : null,
      titleWeight: title ? getComputedStyle(title).fontWeight : null,
      priceWeight: cardPrice ? getComputedStyle(cardPrice).fontWeight : null,
      swatchCount: card.querySelectorAll('.ssz-product-card__swatch').length,
      previewSwatchCount: card.querySelectorAll('[data-ssz-colour-swatch]').length,
      availableSwatchCount: card.querySelectorAll('[data-ssz-colour-available]').length,
      swatchOverflow: card.querySelector('.ssz-product-card__swatches-more')?.textContent?.trim() ?? null,
      swatchGroup: card.querySelector('[data-ssz-colour-swatches][role="group"][aria-label="Available colours"]') !== null,
      swatchButtonsInLink: card.querySelectorAll('.woocommerce-loop-product__link button, .woocommerce-loop-product__link a').length,
      buttons: card.querySelectorAll('.add_to_cart_button, .ajax_add_to_cart, .button').length,
      ratings: card.querySelectorAll('.star-rating, .woocommerce-loop-rating').length,
    };
  });
  const imageNodes = gallery ? [...gallery.querySelectorAll('.woocommerce-product-gallery__image img:not(.zoomImg)')] : [];
  const details = [...document.querySelectorAll('.ssz-product-accordion')];

  return {
    mainFound: Boolean(mainProduct),
    mainHasCardClass: Boolean(mainProduct?.classList.contains('ssz-product-card')),
    mainHasFitClass: Boolean(mainProduct && [...mainProduct.classList].some((className) => className === 'ssz-product-card--cover' || className === 'ssz-product-card--contain')),
    breadcrumb: Boolean(document.querySelector('.woocommerce-breadcrumb')),
    galleryFound: Boolean(gallery),
    galleryImageCount: imageNodes.length,
    galleryPrimaryImageLoaded: Boolean(imageNodes[0] && imageNodes[0].complete && imageNodes[0].naturalWidth > 0),
    galleryImagesHaveAlt: imageNodes.length > 0 && imageNodes.every((image) => Boolean(image.getAttribute('alt')?.trim())),
    galleryLinks: gallery ? gallery.querySelectorAll('.woocommerce-product-gallery__image > a[href]').length : 0,
    galleryUsesGrid: Boolean(galleryWrapper && getComputedStyle(galleryWrapper).display === 'grid'),
    galleryUsesScrollSnap: Boolean(galleryWrapper && getComputedStyle(galleryWrapper).scrollSnapType.includes('x')),
    galleryHasFlexViewport: Boolean(gallery?.querySelector('.flex-viewport')),
    galleryTransform: galleryWrapper ? getComputedStyle(galleryWrapper).transform : null,
    summaryFound: Boolean(summary),
    brand: Boolean(summary?.querySelector('.ssz-single-brand a, .ssz-single-brand')),
    brandColor: summary?.querySelector('.ssz-single-brand') ? getComputedStyle(summary.querySelector('.ssz-single-brand')).color : null,
    titleCount: document.querySelectorAll('main#primary h1.product_title').length,
    price: Boolean(price && price.querySelector('.woocommerce-Price-amount')),
    priceText: price?.textContent?.trim() ?? '',
    addToBag: document.querySelector('.single_add_to_cart_button')?.textContent?.trim() === 'Add to bag',
    nativeColour: Boolean(document.querySelector('form.variations_form select[name="attribute_pa_colour"]')),
    nativeSize: Boolean(document.querySelector('form.variations_form select[name="attribute_pa_size"]')),
    colourControl: Boolean(document.querySelector('[data-ssz-attribute="attribute_pa_colour"]')),
    sizeControl: Boolean(document.querySelector('[data-ssz-attribute="attribute_pa_size"]')),
    details: details.map((detail) => detail.querySelector('summary')?.textContent?.trim() ?? ''),
    hasDefaultTabs: Boolean(document.querySelector('.woocommerce-tabs, .wc-tabs-wrapper')),
    hasDefaultExcerpt: Boolean(document.querySelector('.woocommerce-product-details__short-description')),
    hasDefaultMeta: Boolean(document.querySelector('.product_meta')),
    hasMaterialCare: [...document.querySelectorAll('summary,h2,h3')].some((element) => /material|care/i.test(element.textContent ?? '')),
    relatedFound: Boolean(document.querySelector('.related.products')),
    relatedCount: relatedCards.length,
    relatedDetails,
    relatedCardContract: relatedCards.length > 0 && relatedDetails.every((card) => (
      card.cardClass && card.fitClass && card.media && card.image && card.title && card.price &&
      card.aspectRatio !== null && Math.abs(card.aspectRatio - .8) < .03 && card.anchorCount === 1 &&
      card.buttons === 0 && card.ratings === 0
    )),
    relatedRetailPresentation: relatedCards.length > 0 && relatedDetails.every((card) => (
      card.retailPresentation && card.brandColor === 'rgb(35, 136, 173)' && card.titleWeight === '700' && card.priceWeight === '600'
    )),
    relatedSwatchContract: relatedDetails.every((card) => card.swatchButtonsInLink === 0 && (card.swatchCount === 0 || (card.swatchCount <= 5 && card.swatchGroup))),
  };
  });
};

const inspectProductPageInteractions = async (page) => {
  const result = {
    initialRange: false,
    initialDisabled: false,
    colourSync: false,
    colourName: false,
    sizeSync: false,
    asymmetricDisabled: false,
    disabledCannotActivate: false,
    variationFound: false,
    variationPrice: false,
    variationImage: false,
    resetSync: false,
    sizeGuide: false,
    shippingReturns: false,
    accordionsKeyboard: false,
  };

  const state = page.locator('form.variations_form');
  if (!(await state.count())) return result;

  result.initialRange = (await page.locator('[data-ssz-product-price]').innerText()).includes('$89.00') && (await page.locator('[data-ssz-product-price]').innerText()).includes('$109.00');
  result.initialDisabled = await page.locator('.woocommerce-variation-add-to-cart-disabled').count() === 1;
  result.sizeGuide = await page.getByRole('link', { name: 'Size guide', exact: true }).count() === 1 && (await page.getByRole('link', { name: 'Size guide', exact: true }).getAttribute('href'))?.includes('store-007-test-size-guide');
  result.shippingReturns = await page.getByText('Shipping & returns', { exact: true }).count() === 1;

  const colour = page.getByRole('button', { name: 'Colour: Black', exact: true });
  const size = page.getByRole('button', { name: 'Size: M', exact: true });
  await colour.press('Enter');
  await page.waitForTimeout(180);
  result.colourSync = await page.locator('select[name="attribute_pa_colour"]').getAttribute('value') === 'black' || await page.locator('select[name="attribute_pa_colour"]').evaluate((select) => select.value === 'black');
  result.colourName = (await page.locator('[data-ssz-attribute="attribute_pa_colour"] [data-ssz-selected-label]').textContent()).includes('Black');
  result.asymmetricDisabled = await page.getByRole('button', { name: 'Size: XL', exact: true }).isEnabled() === false;
  await size.press('Enter');
  await page.waitForTimeout(650);
  result.sizeSync = await page.locator('select[name="attribute_pa_size"]').evaluate((select) => select.value === 'm');
  result.variationFound = await page.locator('.variation_id').inputValue().catch(() => '') !== '';
  result.variationPrice = (await page.locator('[data-ssz-product-price]').innerText()).includes('$89.00') && !(await page.locator('[data-ssz-product-price]').innerText()).includes('$109.00');
  const disabledSize = page.getByRole('button', { name: 'Size: XL', exact: true });
  result.disabledCannotActivate = !(await disabledSize.isEnabled());
  await page.getByRole('button', { name: 'Colour: Navy', exact: true }).press('Enter');
  await page.waitForTimeout(650);
  result.variationImage = await page.locator('.woocommerce-product-gallery__image img').first().getAttribute('src') && (await page.locator('.woocommerce-product-gallery__image img').first().getAttribute('src')).includes('simple-primary');

  await page.locator('.reset_variations').click();
  await page.waitForTimeout(300);
  result.resetSync = await page.locator('select[name="attribute_pa_colour"]').evaluate((select) => select.value === '') && await page.locator('select[name="attribute_pa_size"]').evaluate((select) => select.value === '') && await page.locator('[data-ssz-product-price]').innerText().then((text) => text.includes('$89.00') && text.includes('$109.00')) && await page.locator('[data-ssz-variation-control] button[aria-pressed="true"]').count() === 0;

  const productDetails = page.getByText('Product details', { exact: true });
  await productDetails.press('Enter');
  result.accordionsKeyboard = await page.locator('details').filter({ hasText: 'Product details' }).getAttribute('open') !== null;
  return result;
};

const inspectRelatedSwatchInteraction = async (page) => {
  const cards = page.locator('.related.products ul.products li.ssz-product-card');
  const card = cards.filter({ has: page.locator('[data-ssz-colour-swatches]') }).first();
  const fallbackCard = cards.first();
  const targetCard = await card.count() ? card : fallbackCard;
  const button = targetCard.locator('[data-ssz-colour-swatch]').first();
  const available = targetCard.locator('[data-ssz-colour-available]').first();
  if (!(await targetCard.count()) || (!(await button.count()) && !(await available.count()))) {
    return { configured: false, semanticContract: false, selected: false, availableOnlySafe: false, noNavigation: false, noNestedInteractive: false };
  }
  const url = page.url();
  if (await button.count()) await button.click();
  else await available.evaluate((element) => element.click());
  await page.waitForTimeout(100);
  const state = await page.evaluate(() => {
    const card = [...document.querySelectorAll('.related.products ul.products li.ssz-product-card')]
      .find((node) => node.querySelector('[data-ssz-colour-swatches]'));
    const selected = card?.querySelector('[data-ssz-colour-swatch][aria-pressed="true"]');
    const available = card?.querySelector('[data-ssz-colour-available]');
    return {
      exists: Boolean(card),
      selected: Boolean(selected),
      availableOnlySafe: Boolean(available && !available.hasAttribute('aria-pressed') && !card?.classList.contains('ssz-product-card--colour-preview')),
      semanticContract: Boolean(card?.querySelector('[data-ssz-colour-swatches][role="group"][aria-label="Available colours"]')),
      noNestedInteractive: Boolean(card && !card.querySelector('.woocommerce-loop-product__link button, .woocommerce-loop-product__link a')),
    };
  });
  return {
    configured: true,
    semanticContract: state.semanticContract,
    selected: state.selected,
    availableOnlySafe: state.availableOnlySafe,
    noNavigation: url === page.url(),
    noNestedInteractive: state.exists && state.noNestedInteractive,
  };
};

const inspectArchivePage = async (page, expectedColumns) => {
  const archive = await page.evaluate((expected) => {
    const root = document.documentElement;
    const body = document.body;
    const grid = document.querySelector('ul.products');
    const gridStyle = grid ? getComputedStyle(grid) : null;
    const columnCount = gridStyle?.gridTemplateColumns && gridStyle.gridTemplateColumns !== 'none'
      ? gridStyle.gridTemplateColumns.split(' ').filter(Boolean).length
      : null;
    const images = [...document.images].filter((image) => image.complete && image.naturalWidth === 0 && image.currentSrc);
    return {
      header: Boolean(document.querySelector('.ssz-archive-header h1')),
      toolbar: Boolean(document.querySelector('[data-archive-toolbar]')),
      count: Boolean(document.querySelector('.ssz-archive-toolbar__count')),
      filterToggle: Boolean(document.querySelector('[data-archive-filters-toggle]')),
      ordering: Boolean(document.querySelector('.woocommerce-ordering select')),
      grid: Boolean(grid),
      columnCount,
      expectedColumns: expected,
      categoryNav: Boolean(document.querySelector('.ssz-archive-category-nav')),
      presentation: (() => {
        const card = document.querySelector('ul.products li.ssz-product-card');
        const cards = [...document.querySelectorAll('ul.products li.ssz-product-card')];
        const media = card?.querySelector('.ssz-product-card__media');
        const brand = card?.querySelector('.ssz-product-card__brand');
        const title = card?.querySelector('.woocommerce-loop-product__title');
        const swatches = card?.querySelector('.ssz-product-card__swatches');
        const price = card?.querySelector('.price');
        const saleCard = cards.find((item) => item.querySelector('.price del'));
        const salePrice = saleCard?.querySelector('.price');
        const saleCurrent = saleCard?.querySelector('.price ins');
        const saleOld = saleCard?.querySelector('.price del');
        const rect = (element) => element?.getBoundingClientRect();
        const cardRect = rect(card);
        const mediaRect = rect(media);
        const firstRowTop = cardRect?.top ?? null;
        const firstRow = firstRowTop === null ? [] : cards.filter((item) => Math.abs((rect(item)?.top ?? 0) - firstRowTop) < 2);
        const secondRow = firstRowTop === null ? [] : cards.filter((item) => (rect(item)?.top ?? 0) > firstRowTop + 2);
        const firstRowSorted = [...firstRow].sort((left, right) => (rect(left)?.left ?? 0) - (rect(right)?.left ?? 0));
        const firstRowMedia = firstRowSorted.map((item) => rect(item.querySelector('.ssz-product-card__media'))).filter(Boolean);
        const row1InfoBottom = firstRow.length ? Math.max(...firstRow.map((item) => rect(item.querySelector('.price'))?.bottom ?? rect(item)?.bottom ?? 0)) : null;
        const row2MediaTop = secondRow.length ? Math.min(...secondRow.map((item) => rect(item.querySelector('.ssz-product-card__media'))?.top ?? Infinity)) : null;
        const cardWidths = cards.map((item) => rect(item)?.width ?? 0).filter(Boolean);
        const mediaWidths = cards.map((item) => rect(item.querySelector('.ssz-product-card__media'))?.width ?? 0).filter(Boolean);
        const rowColumnGaps = firstRowSorted.slice(1).map((item, index) => (rect(item)?.left ?? 0) - (rect(firstRowSorted[index])?.right ?? 0));
        return {
          brandColor: brand ? getComputedStyle(brand).color : null,
          titleWeight: title ? getComputedStyle(title).fontWeight : null,
          priceWeight: price ? getComputedStyle(price).fontWeight : null,
          saleCurrentWeight: saleCurrent ? getComputedStyle(saleCurrent).fontWeight : null,
          saleOldWeight: saleOld ? getComputedStyle(saleOld).fontWeight : null,
          saleOldColor: saleOld ? getComputedStyle(saleOld).color : null,
          saleOldDecoration: saleOld ? getComputedStyle(saleOld).textDecorationLine : null,
          brandMediaGap: media && brand ? Number((brand.getBoundingClientRect().top - media.getBoundingClientRect().bottom).toFixed(1)) : null,
          brandTitleGap: brand && title ? Number((title.getBoundingClientRect().top - brand.getBoundingClientRect().bottom).toFixed(1)) : null,
          swatchTitleGap: title && swatches ? Number((swatches.getBoundingClientRect().top - title.getBoundingClientRect().bottom).toFixed(1)) : null,
          swatchPriceGap: swatches && price ? Number((price.getBoundingClientRect().top - swatches.getBoundingClientRect().bottom).toFixed(1)) : null,
          priceTitleGap: title && price ? Number((price.getBoundingClientRect().top - title.getBoundingClientRect().bottom).toFixed(1)) : null,
          rowGap: gridStyle ? gridStyle.rowGap : null,
          cardCount: cards.length,
          cardWidth: cardWidths[0] ?? null,
          mediaWidth: mediaWidths[0] ?? null,
          mediaCardRatio: cardWidths[0] && mediaWidths[0] ? Number((mediaWidths[0] / cardWidths[0]).toFixed(3)) : null,
          minMediaCardRatio: cardWidths.length && mediaWidths.length ? Number(Math.min(...cards.map((item) => {
            const itemCard = rect(item);
            const itemMedia = rect(item.querySelector('.ssz-product-card__media'));
            return itemCard?.width && itemMedia?.width ? itemMedia.width / itemCard.width : 0;
          })).toFixed(3)) : null,
          columnGap: rowColumnGaps.length ? Number(Math.min(...rowColumnGaps).toFixed(1)) : null,
          rowInfoGap: row2MediaTop !== null && row1InfoBottom !== null ? Number((row2MediaTop - row1InfoBottom).toFixed(1)) : null,
          rowMediaAlignment: firstRowMedia.length > 1 ? Number((Math.max(...firstRowMedia.map((item) => item.top)) - Math.min(...firstRowMedia.map((item) => item.top))).toFixed(1)) : null,
        };
      })(),
      brokenImages: images.length,
      horizontalOverflow: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0) > (root?.clientWidth ?? 0) + 2,
    };
  }, expectedColumns);

  const drawer = page.locator('[data-archive-filter-shell]');
  const panel = page.locator('[data-archive-filter-shell] .ssz-filter-drawer__panel');
  const filterToggle = page.locator('[data-archive-filters-toggle]');
  const drawerState = {
    configured: await filterToggle.count() > 0,
    opened: null,
    bodyScrollLock: null,
    focusInside: null,
    closedByEscape: null,
    focusRestored: null,
    accordion: { configured: false, expanded: null, collapsed: null },
  };

  if (drawerState.configured) {
    await filterToggle.click();
    await page.waitForTimeout(60);
    drawerState.opened = await panel.isVisible() && await filterToggle.getAttribute('aria-expanded') === 'true';
    drawerState.bodyScrollLock = await page.evaluate(() => getComputedStyle(document.documentElement).overflow === 'hidden' && getComputedStyle(document.body).overflow === 'hidden');
    drawerState.focusInside = await page.evaluate(() => Boolean(document.activeElement?.closest('[data-archive-filter-shell]')));
    const accordion = page.locator('[data-archive-accordion]').first();
    drawerState.accordion.configured = await accordion.count() > 0;
    if (drawerState.accordion.configured) {
      const wasExpanded = await accordion.getAttribute('aria-expanded') === 'true';
      await accordion.click();
      drawerState.accordion[wasExpanded ? 'collapsed' : 'expanded'] = await accordion.getAttribute('aria-expanded') === (wasExpanded ? 'false' : 'true');
      await accordion.click();
      drawerState.accordion[wasExpanded ? 'expanded' : 'collapsed'] = await accordion.getAttribute('aria-expanded') === (wasExpanded ? 'true' : 'false');
    }
    await page.keyboard.press('Escape');
    drawerState.closedByEscape = !(await panel.isVisible()) && await filterToggle.getAttribute('aria-expanded') === 'false';
    drawerState.focusRestored = await page.evaluate(() => document.activeElement?.matches('[data-archive-filters-toggle]') ?? false);
  }

  return { ...archive, drawer: drawerState };
};

const browser = await chromium.launch({ headless: true, executablePath });
const results = [];
let archiveFunctional = {
  filterSubmit: false,
  filterQuery: null,
  activeChips: false,
  sorting: false,
  popularityFirst: null,
  categoryArchive: false,
  brandArchive: false,
  emptyState: false,
  pagination: false,
  consoleErrors: [],
  pageErrors: [],
};

try {
  for (const width of widths) {
    const height = width < 768 ? 800 : 900;
    const context = await browser.newContext({ viewport: { width, height } });
    const page = await context.newPage();
    const consoleErrors = [];
    const pageErrors = [];

    page.on('console', (message) => {
      if (message.type() === 'error') consoleErrors.push(message.text());
    });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    let response;
    let navigationError = null;
    try {
      response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
    } catch (error) {
      navigationError = error.message;
    }

    const pageState = await page.evaluate(() => {
      const root = document.documentElement;
      const body = document.body;
      return {
        innerWidth: window.innerWidth,
        clientWidth: root?.clientWidth ?? 0,
        scrollWidth: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0),
        themeVisible: Boolean(document.querySelector('.ssz-site-shell, .ssz-header[data-site-header], .ssz-hero')),
        headerPresent: Boolean(document.querySelector('[data-site-header]')),
      };
    });

    const duplicateIds = await page.evaluate(() => {
      const counts = new Map();
      document.querySelectorAll('[id]').forEach((element) => {
        if (element.id) counts.set(element.id, (counts.get(element.id) ?? 0) + 1);
      });
      return [...counts.entries()].filter(([, count]) => count > 1).map(([id]) => id);
    });

    const headerMode = await page.evaluate(() => {
      const desktopNav = document.querySelector('[data-primary-nav]');
      const menuToggle = document.querySelector('[data-menu-toggle]');
      const announcement = document.querySelector('.ssz-announcement');
      const colorLogo = document.querySelector('.ssz-brand-lockup__symbol');
      const customLogo = document.querySelector('.custom-logo');
      const cart = document.querySelector('.ssz-cart-link');
      const style = (element) => element ? window.getComputedStyle(element) : null;
      return {
        desktopNavVisible: Boolean(desktopNav && style(desktopNav).display !== 'none'),
        mobileToggleVisible: Boolean(menuToggle && style(menuToggle).display !== 'none'),
        announcementPresent: Boolean(announcement),
        colorLogoVisible: Boolean(colorLogo && style(colorLogo).display !== 'none'),
        colorLogoLoaded: Boolean(colorLogo && colorLogo.complete && colorLogo.naturalWidth > 0),
        customLogoVisible: Boolean(customLogo && style(customLogo).display !== 'none'),
        cartVisible: Boolean(cart && style(cart).display !== 'none'),
        accountHref: document.querySelector('.ssz-account-link')?.getAttribute('href') ?? null,
        mobileAccountHref: document.querySelector('.ssz-mobile-account')?.getAttribute('href') ?? null,
      };
    });

    const overflow = pageState.scrollWidth > pageState.clientWidth + 2;
    await page.evaluate(() => document.querySelector('[data-homepage-campaign]')?.scrollIntoView({ block: 'center' }));
    await page.waitForTimeout(1000);
    const homepage = await page.evaluate(() => {
      const main = document.querySelector('main');
      const orderSelectors = [
        '[data-homepage-hero]',
        '[data-homepage-categories]',
        '[data-homepage-product-section="new-arrivals"]',
        '[data-homepage-brands]',
        '[data-homepage-activities]',
        '[data-homepage-campaign]',
        '[data-homepage-product-section="best-sellers"]',
        '[data-homepage-features]',
        '[data-homepage-proposition]',
        '[data-homepage-newsletter]',
      ];
      const orderedNodes = orderSelectors.map((selector) => main?.querySelector(selector) ?? null);
      const positions = orderedNodes.map((node) => node ? [...(main?.children ?? [])].indexOf(node) : -1);
      const heroCtas = main?.querySelectorAll('[data-homepage-hero] .ssz-hero__actions a') ?? [];
      const categoryCards = [...(main?.querySelectorAll('[data-homepage-categories] .ssz-category-card') ?? [])];
      const brandCards = [...(main?.querySelectorAll('[data-homepage-brands] .ssz-brand-card') ?? [])];
      const brandImages = brandCards.flatMap((card) => [...card.querySelectorAll('img')]);
      const activityCards = [...(main?.querySelectorAll('[data-homepage-activities] .ssz-activity-card') ?? [])];
      const featurePanels = [...(main?.querySelectorAll('[data-homepage-features] .ssz-feature-panel') ?? [])];
      const brokenImages = [...(document.images ?? [])].filter((image) => image.complete && image.naturalWidth === 0 && image.currentSrc);
      const pageText = document.body?.textContent?.toLowerCase() ?? '';
      const forbiddenLabels = [
        'campaign image placeholder',
        'replace with campaign photography',
        'developer placeholder',
        'missing image',
      ];
      const newsletterForm = main?.querySelector('[data-homepage-newsletter] form');
      const newsletterButton = newsletterForm?.querySelector('button');
      const newsletterInput = newsletterForm?.querySelector('input[type="email"]');
      const newsletterCopy = [...(main?.querySelectorAll('[data-homepage-newsletter]') ?? [])]
        .map((node) => node.textContent?.toLowerCase() ?? '')
        .join(' ');
      const newsletterForbiddenLabels = [
        'integration',
        'provider',
        'backend',
        'api',
        'will be connected here',
      ];
      const campaign = main?.querySelector('[data-homepage-campaign]');
      const campaignMedia = campaign?.querySelector('.ssz-campaign__media');
      const campaignImage = campaign?.querySelector('.ssz-campaign__image');
      const campaignSource = campaign?.querySelector('source');
      const campaignContent = campaign?.querySelector('.ssz-campaign__content');
      const campaignLayering = Boolean(campaign && campaignMedia && campaignContent && (() => {
        const campaignStyle = getComputedStyle(campaign);
        const mediaStyle = getComputedStyle(campaignMedia);
        const overlayStyle = getComputedStyle(campaign, '::after');
        const contentStyle = getComputedStyle(campaignContent);
        return campaignStyle.position === 'relative' && campaignStyle.isolation === 'isolate' &&
          mediaStyle.zIndex === '0' && overlayStyle.zIndex === '1' && contentStyle.zIndex === '2';
      })());

      return {
        hero: Boolean(orderedNodes[0]),
        h1Count: main?.querySelectorAll('h1').length ?? 0,
        heroCtas: heroCtas.length,
        categorySection: Boolean(orderedNodes[1]),
        categoryLinks: categoryCards.length === 0 || categoryCards.every((card) => Boolean(card.getAttribute('href'))),
        brandSection: Boolean(orderedNodes[3]),
        brandLinks: brandCards.length === 0 || brandCards.every((card) => Boolean(card.getAttribute('href'))),
        brandLinksToArchives: brandCards.length === 0 || brandCards.every((card) => {
          const href = card.getAttribute('href');
          if (!href) return false;
          const path = new URL(href, window.location.href).pathname.replace(/\/$/, '');
          return path !== '' && path !== '/shop';
        }),
        brandImagesConfigured: brandImages.length,
        brandImagesLoaded: brandImages.every((image) => image.complete && image.naturalWidth > 0),
        activitySection: Boolean(orderedNodes[4]),
        activities: activityCards.length,
        activityLinks: activityCards.length === 0 || activityCards.every((card) => Boolean(card.getAttribute('href'))),
        activityTitlesUnique: activityCards.length === 0 || activityCards.every((card) => card.querySelectorAll('strong').length === 1 && card.querySelectorAll('.ssz-eyebrow').length === 0),
        campaignSection: Boolean(orderedNodes[5]),
        campaignCta: Boolean(main?.querySelector('[data-homepage-campaign] a[href]')),
        campaignMediaLoaded: !campaignImage || (campaignImage.complete && campaignImage.naturalWidth > 0),
        campaignMobileSource: !campaignSource || Boolean(campaignSource.getAttribute('srcset')),
        campaignLayering,
        newArrivals: Boolean(orderedNodes[2]),
        bestSellers: Boolean(orderedNodes[6]),
        featurePanels: featurePanels.length,
        featureLinks: featurePanels.length === 0 || featurePanels.every((panel) => Boolean(panel.getAttribute('href'))),
        proposition: Boolean(orderedNodes[8]),
        newsletter: Boolean(orderedNodes[9]),
        newsletterHonest: Boolean(newsletterForm && newsletterButton?.disabled && newsletterInput?.disabled && newsletterForm.hasAttribute('aria-describedby')),
        newsletterForbiddenLabels: newsletterForbiddenLabels.filter((label) => newsletterCopy.includes(label)),
        sectionOrder: positions.every((position, index) => position >= 0 && (index === 0 || position > positions[index - 1])),
        forbiddenLabels: forbiddenLabels.filter((label) => pageText.includes(label)),
        brokenImages: brokenImages.length,
      };
    });
    const homepageProductCards = await inspectProductCards(page, { homepage: true });
    await page.evaluate(() => window.scrollTo(0, 0));

    const shopPage = await context.newPage();
    const shopConsoleErrors = [];
    const shopPageErrors = [];
    shopPage.on('console', (message) => {
      if (message.type() === 'error') shopConsoleErrors.push(message.text());
    });
    shopPage.on('pageerror', (error) => shopPageErrors.push(error.message));
    let shopResponse;
    let shopNavigationError = null;
    try {
      shopResponse = await shopPage.goto(new URL('shop/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
    } catch (error) {
      shopNavigationError = error.message;
    }
    const shopState = await shopPage.evaluate(() => {
      const root = document.documentElement;
      const body = document.body;
      return {
        clientWidth: root?.clientWidth ?? 0,
        scrollWidth: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0),
        themeVisible: Boolean(document.querySelector('.ssz-site-shell, .ssz-header[data-site-header]')),
        duplicateIds: [...document.querySelectorAll('[id]')].reduce((counts, element) => {
          if (element.id) counts[element.id] = (counts[element.id] ?? 0) + 1;
          return counts;
        }, {}),
      };
    });
    const shopColumns = width < 768 ? 2 : width <= 1024 ? 3 : 4;
    const shopProductCards = await inspectProductCards(shopPage, { expectedColumns: shopColumns });
    const shopArchive = await inspectArchivePage(shopPage, shopColumns);
    await shopPage.close();

    const pdpPage = await context.newPage();
    const pdpConsoleErrors = [];
    const pdpPageErrors = [];
    pdpPage.on('console', (message) => {
      if (message.type() === 'error') pdpConsoleErrors.push(message.text());
    });
    pdpPage.on('pageerror', (error) => pdpPageErrors.push(error.message));
    let pdpResponse;
    let pdpNavigationError = null;
    try {
      pdpResponse = await pdpPage.goto(new URL('product/store-007-test-variable-product-page/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
    } catch (error) {
      pdpNavigationError = error.message;
    }
    const pdpState = await pdpPage.evaluate(() => {
      const root = document.documentElement;
      const body = document.body;
      return {
        clientWidth: root?.clientWidth ?? 0,
        scrollWidth: Math.max(root?.scrollWidth ?? 0, body?.scrollWidth ?? 0),
        themeVisible: Boolean(document.querySelector('.ssz-site-shell, .ssz-header[data-site-header]')),
      };
    });
    const pdpProduct = await inspectProductPage(pdpPage);
    const pdpInteractions = await inspectProductPageInteractions(pdpPage);
    const relatedSwatchInteraction = await inspectRelatedSwatchInteraction(pdpPage);
    await pdpPage.close();
    const search = { opened: false, inputFocused: false, closed: false, focusRestored: false, menuCloses: true };
    const searchToggle = page.locator('[data-search-toggle]');
    const searchPanel = page.locator('[data-search-panel]');
    const searchInput = page.locator('[data-search-panel] input[type="search"]');
    const menuToggle = page.locator('[data-menu-toggle]');
    const mobileNav = page.locator('[data-mobile-nav]');

    if (await searchToggle.count()) {
      await searchToggle.click();
      search.opened = await searchPanel.isVisible();
      search.inputFocused = await page.evaluate(() => document.activeElement?.matches('[data-search-panel] input[type="search"]') ?? false);
      await page.locator('[data-search-close]').click();
      search.closed = !(await searchPanel.isVisible());
      search.focusRestored = await page.evaluate(() => document.activeElement?.matches('[data-search-toggle]') ?? false);
    }

    const menuState = {
      checked: width <= 1120,
      opened: null,
      closedByButton: null,
      closedByEscape: null,
      bodyScrollLock: null,
      focusRestored: null,
      drilldown: { configured: false, opened: null, backedOut: null },
      accordion: { configured: false, expanded: null, collapsed: null },
    };

    if (menuState.checked && await menuToggle.count() && await menuToggle.isVisible()) {
      await menuToggle.click();
      menuState.opened = await mobileNav.isVisible();
      menuState.bodyScrollLock = await page.evaluate(() => getComputedStyle(document.documentElement).overflow === 'hidden' && getComputedStyle(document.body).overflow === 'hidden');
      await page.locator('[data-mobile-close]').click();
      menuState.closedByButton = !(await mobileNav.isVisible());
      await menuToggle.click();
      await page.keyboard.press('Escape');
      menuState.closedByEscape = !(await mobileNav.isVisible());
      menuState.focusRestored = await page.evaluate(() => document.activeElement?.matches('[data-menu-toggle]') ?? false);
    }

    if (menuState.checked && await menuToggle.count() && await menuToggle.isVisible()) {
      await menuToggle.click();
      const drilldown = page.locator('[data-mobile-drilldown]').first();
      menuState.drilldown.configured = (await drilldown.count()) > 0;
      if (menuState.drilldown.configured) {
        const panelId = await drilldown.getAttribute('aria-controls');
        await drilldown.click();
        const panel = page.locator(`#${panelId}`);
        menuState.drilldown.opened = await panel.isVisible();
        const accordion = panel.locator('[data-mobile-accordion]').first();
        menuState.accordion.configured = (await accordion.count()) > 0;
        if (menuState.accordion.configured) {
          const accordionId = await accordion.getAttribute('aria-controls');
          await accordion.click();
          const accordionPanel = page.locator(`#${accordionId}`);
          menuState.accordion.expanded = await accordionPanel.isVisible() && await accordion.getAttribute('aria-expanded') === 'true';
          await accordion.click();
          menuState.accordion.collapsed = !(await accordionPanel.isVisible()) && await accordion.getAttribute('aria-expanded') === 'false';
        }
        await panel.locator('[data-mobile-back]').click();
        menuState.drilldown.backedOut = !(await panel.isVisible());
      }
      await page.locator('[data-mobile-close]').click();
    }

    if (menuState.checked && await searchToggle.count()) {
      await searchToggle.click();
      await menuToggle.click();
      search.menuCloses = !(await searchPanel.isVisible()) && await mobileNav.isVisible();
      await page.keyboard.press('Escape');
    }

    const desktop = {
      configured: false,
      twoConfigured: false,
      opened: null,
      panelWithinViewport: null,
      closedByEscape: null,
      ariaReset: null,
      exclusive: null,
      searchClosesMega: null,
      megaClosesSearch: null,
      searchAriaReset: null,
    };
    if (!menuState.checked) {
      const desktopToggles = page.locator('[data-primary-nav] > .ssz-primary-menu > .ssz-nav-item--has-children > [data-nav-toggle]');
      const desktopToggle = desktopToggles.first();
      const desktopToggleCount = await desktopToggles.count();
      desktop.configured = desktopToggleCount > 0;
      desktop.twoConfigured = desktopToggleCount >= 2;
      if (desktop.configured) {
        const panelId = await desktopToggle.getAttribute('aria-controls');
        const panel = page.locator(`#${panelId}`);
        await desktopToggle.click();
        await page.waitForTimeout(300);
        const box = await panel.boundingBox();
        desktop.opened = await panel.isVisible();
        desktop.panelWithinViewport = Boolean(box && box.x >= 0 && box.x + box.width <= width && box.y >= 0 && box.y + box.height <= height);

        if (desktop.twoConfigured) {
          const secondToggle = desktopToggles.nth(1);
          const secondPanelId = await secondToggle.getAttribute('aria-controls');
          const secondPanel = page.locator(`#${secondPanelId}`);
          await secondToggle.hover();
          await page.waitForTimeout(300);
          desktop.exclusive = !(await panel.isVisible()) && await desktopToggle.getAttribute('aria-expanded') === 'false' && await secondPanel.isVisible();
        }

        await searchToggle.click();
        await page.waitForTimeout(50);
        desktop.searchClosesMega = !(await panel.isVisible()) && await searchPanel.isVisible();
        desktop.searchAriaReset = await desktopToggle.getAttribute('aria-expanded') === 'false';
        await page.locator('[data-search-close]').click();

        await desktopToggle.click();
        await page.waitForTimeout(300);
        desktop.megaClosesSearch = await panel.isVisible() && !(await searchPanel.isVisible()) && await desktopToggle.getAttribute('aria-expanded') === 'true';
        await page.keyboard.press('Escape');
        await page.waitForTimeout(300);
        desktop.closedByEscape = !(await panel.isVisible());
        desktop.ariaReset = await desktopToggle.getAttribute('aria-expanded') === 'false';
      }
    }

    await page.evaluate(() => window.scrollTo(0, 500));
    const stickyHeader = await page.evaluate(() => {
      const header = document.querySelector('[data-site-header]');
      const announcement = document.querySelector('.ssz-announcement');
      if (!header) return { present: false, aboveContent: false, announcementScrolledAway: false };
      const rect = header.getBoundingClientRect();
      const elements = document.elementsFromPoint(rect.left + Math.max(4, rect.width / 2), Math.max(4, rect.top + 4));
      const announcementRect = announcement?.getBoundingClientRect();
      return {
        present: true,
        top: rect.top,
        aboveContent: rect.top <= 1 && rect.bottom > rect.top && elements.some((element) => element.closest?.('[data-site-header]')),
        announcementScrolledAway: !announcementRect || announcementRect.bottom <= 0,
      };
    });

    await page.evaluate(() => window.scrollTo(0, 0));
    const lightFocus = await findKeyboardFocus(page, (focus) => focus.isSearch);
    const darkFocus = await findKeyboardFocus(page, (focus) => focus.inFooter);

    const screenshotPath = path.join(outputDir, `${width}-home.png`);
    await page.screenshot({ path: screenshotPath, fullPage: true });

    results.push({
      width,
      height,
      responseStatus: response?.status() ?? null,
      navigationError,
      themeVisible: pageState.themeVisible,
      horizontalOverflow: overflow,
      scrollWidth: pageState.scrollWidth,
      clientWidth: pageState.clientWidth,
      headerMode,
      consoleErrors,
      pageErrors,
      duplicateIds,
      homepage,
      homepageProductCards,
      shop: {
        responseStatus: shopResponse?.status() ?? null,
        navigationError: shopNavigationError,
        themeVisible: shopState.themeVisible,
        horizontalOverflow: shopState.scrollWidth > shopState.clientWidth + 2,
        duplicateIds: Object.entries(shopState.duplicateIds).filter(([, count]) => count > 1).map(([id]) => id),
        consoleErrors: shopConsoleErrors,
        pageErrors: shopPageErrors,
        productCards: shopProductCards,
        archive: shopArchive,
      },
      pdp: {
        responseStatus: pdpResponse?.status() ?? null,
        navigationError: pdpNavigationError,
        themeVisible: pdpState.themeVisible,
        horizontalOverflow: pdpState.scrollWidth > pdpState.clientWidth + 2,
        consoleErrors: pdpConsoleErrors,
        pageErrors: pdpPageErrors,
        product: pdpProduct,
        interactions: pdpInteractions,
        relatedSwatchInteraction,
      },
      search,
      menuState,
      desktop,
      stickyHeader,
      focus: { light: lightFocus, dark: darkFocus },
      screenshot: path.relative(process.cwd(), screenshotPath),
    });

    await context.close();
  }
try {
  const functionalContext = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const functionalPage = await functionalContext.newPage();
  functionalPage.on('console', (message) => {
    if (message.type() === 'error') archiveFunctional.consoleErrors.push(message.text());
  });
  functionalPage.on('pageerror', (error) => archiveFunctional.pageErrors.push(error.message));

  await functionalPage.goto(new URL('shop/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  const paginationPage = await functionalContext.newPage();
  await paginationPage.goto(new URL('shop/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  archiveFunctional.pagination = await paginationPage.locator('.woocommerce-pagination a.page-numbers').count() > 0;
  await paginationPage.close();
  await functionalPage.locator('[data-archive-filters-toggle]').click();
  await functionalPage.locator('input[data-archive-filter-checkbox="filter_product_brand"]').first().check();
  await functionalPage.locator('input[data-archive-filter-checkbox="filter_size"][value="m"]').check();
  await functionalPage.getByRole('button', { name: 'Colour', exact: true }).click();
  await functionalPage.locator('input[data-archive-filter-checkbox="filter_colour"][value="black"]').check();
  await functionalPage.getByRole('button', { name: 'Price', exact: true }).click();
  await functionalPage.locator('input[name="min_price"]').fill('20');
  await functionalPage.locator('input[name="max_price"]').fill('130');
  await functionalPage.getByRole('button', { name: 'Availability', exact: true }).click();
  await functionalPage.locator('input[name="filter_stock_status"]').check();
  await functionalPage.locator('[data-archive-filter-form] button[type="submit"]').click({ force: true });
  await functionalPage.waitForLoadState('networkidle');
  const filteredUrl = new URL(functionalPage.url());
  archiveFunctional.filterQuery = filteredUrl.search;
  archiveFunctional.filterSubmit = filteredUrl.searchParams.has('filter_product_brand') && filteredUrl.searchParams.get('filter_size') === 'm' && filteredUrl.searchParams.get('filter_colour') === 'black' && filteredUrl.searchParams.get('filter_stock_status') === 'instock';
  archiveFunctional.activeChips = await functionalPage.locator('[data-archive-active-filters] .ssz-active-filter').count() >= 4;

  const sortPage = await functionalContext.newPage();
  await sortPage.goto(new URL('shop/?orderby=popularity', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  archiveFunctional.sorting = await sortPage.locator('.woocommerce-ordering select').inputValue() === 'popularity';
  archiveFunctional.popularityFirst = await sortPage.locator('ul.products .woocommerce-loop-product__title').first().innerText();
  await sortPage.close();

  const categoryPage = await functionalContext.newPage();
  const categoryResponse = await categoryPage.goto(new URL('product-category/men/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  archiveFunctional.categoryArchive = (categoryResponse?.status() ?? 500) < 400 && await categoryPage.locator('.ssz-archive-header h1').innerText() === 'Men';
  await categoryPage.close();

  const brandPage = await functionalContext.newPage();
  const brandResponse = await brandPage.goto(new URL('brand/arena/', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  archiveFunctional.brandArchive = (brandResponse?.status() ?? 500) < 400 && await brandPage.locator('.ssz-archive-header h1').innerText() === 'Arena';
  await brandPage.close();

  const emptyPage = await functionalContext.newPage();
  await emptyPage.goto(new URL('shop/?min_price=9999', baseUrl).href, { waitUntil: 'networkidle', timeout: 30000 });
  archiveFunctional.emptyState = await emptyPage.locator('.ssz-archive-empty').isVisible() && await emptyPage.getByText('No products found', { exact: true }).isVisible();
  await emptyPage.close();

  await functionalPage.close();
  await functionalContext.close();
} catch (error) {
  archiveFunctional.pageErrors.push(error.message);
}

} finally {
  await browser.close();
}

if (process.env.SSZ_UAT_SUMMARY_ONLY === '1') {
  console.log(JSON.stringify(results.map((result) => ({
    width: result.width,
    headerMode: result.headerMode,
    homepage: {
      cardContract: result.homepageProductCards.cardContract,
      primaryImagesLoaded: result.homepageProductCards.primaryImagesLoaded,
      secondaryImagesLoaded: result.homepageProductCards.secondaryImagesLoaded,
      onlyTwoImages: result.homepageProductCards.onlyTwoImages,
      coverApparel: result.homepageProductCards.coverApparel,
      containEquipment: result.homepageProductCards.containEquipment,
      containEquipmentSourceUncropped: result.homepageProductCards.containEquipmentSourceUncropped,
      containSecondarySourceUncropped: result.homepageProductCards.containSecondarySourceUncropped,
      saleState: result.homepageProductCards.saleState,
      soldOutState: result.homepageProductCards.soldOutState,
      variablePrice: result.homepageProductCards.variablePrice,
      singleImageStable: result.homepageProductCards.singleImageStable,
      noImageState: result.homepageProductCards.noImageState,
      longTitleBounded: result.homepageProductCards.longTitleBounded,
      retailPresentation: result.homepageProductCards.retailPresentation,
      swatchContract: result.homepageProductCards.swatchContract,
      hover: result.homepageProductCards.hover,
      homepageRail: result.homepageProductCards.homepageRail,
    },
    shop: {
      productCards: {
        cardContract: result.shop.productCards.cardContract,
        primaryImagesLoaded: result.shop.productCards.primaryImagesLoaded,
        secondaryImagesLoaded: result.shop.productCards.secondaryImagesLoaded,
        onlyTwoImages: result.shop.productCards.onlyTwoImages,
        coverApparel: result.shop.productCards.coverApparel,
        containEquipment: result.shop.productCards.containEquipment,
        containEquipmentSourceUncropped: result.shop.productCards.containEquipmentSourceUncropped,
        containSecondarySourceUncropped: result.shop.productCards.containSecondarySourceUncropped,
        saleState: result.shop.productCards.saleState,
        soldOutState: result.shop.productCards.soldOutState,
        variablePrice: result.shop.productCards.variablePrice,
        singleImageStable: result.shop.productCards.singleImageStable,
        noImageState: result.shop.productCards.noImageState,
        longTitleBounded: result.shop.productCards.longTitleBounded,
        archiveColumns: result.shop.productCards.archiveColumns,
        retailPresentation: result.shop.productCards.retailPresentation,
        swatchContract: result.shop.productCards.swatchContract,
        swatchInteraction: result.shop.productCards.swatchInteraction,
        hover: result.shop.productCards.hover,
      },
      archive: {
        header: result.shop.archive.header,
        toolbar: result.shop.archive.toolbar,
        count: result.shop.archive.count,
        filterToggle: result.shop.archive.filterToggle,
        ordering: result.shop.archive.ordering,
        grid: result.shop.archive.grid,
        columnCount: result.shop.archive.columnCount,
        expectedColumns: result.shop.archive.expectedColumns,
        horizontalOverflow: result.shop.archive.horizontalOverflow,
        brokenImages: result.shop.archive.brokenImages,
        presentation: result.shop.archive.presentation,
        drawer: result.shop.archive.drawer,
      },
      menuState: result.menuState,
      desktop: result.desktop,
      focus: result.focus,
    },
    pdp: {
      product: result.pdp.product,
      interactions: result.pdp.interactions,
      relatedSwatchInteraction: result.pdp.relatedSwatchInteraction,
    },
  })), null, 2));
} else {
  console.log(JSON.stringify({ baseUrl, executablePath, requireLocalMenu, fallbackContract, results, archiveFunctional }, null, 2));
}

const failures = results.filter((result) => {
  const mobileFailure = result.menuState.checked && (
    result.menuState.opened !== true ||
    result.menuState.closedByButton !== true ||
    result.menuState.closedByEscape !== true ||
    result.menuState.bodyScrollLock !== true ||
    result.menuState.focusRestored !== true ||
    result.search.menuCloses !== true ||
    (result.menuState.drilldown.configured && (result.menuState.drilldown.opened !== true || result.menuState.drilldown.backedOut !== true)) ||
    (result.menuState.accordion.configured && (result.menuState.accordion.expanded !== true || result.menuState.accordion.collapsed !== true))
  );
  const desktopFailure = !result.menuState.checked && (
    result.headerMode.desktopNavVisible !== true ||
    result.headerMode.mobileToggleVisible !== false ||
    (result.desktop.configured && (
      result.desktop.opened !== true ||
      result.desktop.panelWithinViewport !== true ||
      result.desktop.closedByEscape !== true ||
      result.desktop.ariaReset !== true ||
      (result.desktop.twoConfigured && result.desktop.exclusive !== true) ||
      result.desktop.searchClosesMega !== true ||
      result.desktop.megaClosesSearch !== true ||
      result.desktop.searchAriaReset !== true
    ))
  );
  const missingConfiguredMenu = requireLocalMenu && !result.menuState.checked && !result.desktop.configured;

  return (
    result.responseStatus === null || result.responseStatus >= 400 || result.navigationError ||
    !result.themeVisible || result.horizontalOverflow || result.consoleErrors.length || result.pageErrors.length ||
    result.duplicateIds.length ||
    !result.homepage.hero || result.homepage.h1Count !== 1 || result.homepage.heroCtas < 2 ||
    !result.homepage.categorySection || !result.homepage.categoryLinks ||
    !result.homepage.brandSection || !result.homepage.brandLinks || !result.homepage.brandLinksToArchives ||
    !result.homepage.brandImagesLoaded || !result.homepage.activitySection || result.homepage.activities !== 3 || !result.homepage.activityLinks ||
    !result.homepage.activityTitlesUnique || !result.homepage.campaignSection || !result.homepage.campaignCta || !result.homepage.campaignMediaLoaded || !result.homepage.campaignMobileSource || !result.homepage.campaignLayering || !result.homepage.newArrivals || !result.homepage.bestSellers ||
    result.homepage.featurePanels !== 2 || !result.homepage.featureLinks || !result.homepage.proposition || !result.homepage.newsletter ||
    !result.homepage.newsletterHonest || result.homepage.newsletterForbiddenLabels.length || !result.homepage.sectionOrder || result.homepage.forbiddenLabels.length || result.homepage.brokenImages ||
    !result.homepageProductCards.cardContract || !result.homepageProductCards.primaryImagesLoaded || !result.homepageProductCards.secondaryImagesLoaded || !result.homepageProductCards.onlyTwoImages ||
    !result.homepageProductCards.coverApparel || !result.homepageProductCards.containEquipment || !result.homepageProductCards.containEquipmentSourceUncropped || !result.homepageProductCards.containSecondarySourceUncropped || !result.homepageProductCards.saleState || !result.homepageProductCards.soldOutState ||
    !result.homepageProductCards.variablePrice || !result.homepageProductCards.singleImageStable || !result.homepageProductCards.noImageState || !result.homepageProductCards.longTitleBounded ||
    !result.homepageProductCards.retailPresentation || !result.homepageProductCards.swatchContract ||
    !result.homepageProductCards.hover.checked || !result.homepageProductCards.hover.changed || !result.homepageProductCards.hover.secondaryLoaded ||
    (result.width <= 767 && !result.homepageProductCards.homepageRail) ||
    !result.shop.themeVisible || result.shop.responseStatus === null || result.shop.responseStatus >= 400 || result.shop.navigationError || result.shop.horizontalOverflow || result.shop.duplicateIds.length ||
    result.shop.consoleErrors.length || result.shop.pageErrors.length || !result.shop.productCards.cardContract || !result.shop.productCards.primaryImagesLoaded || !result.shop.productCards.secondaryImagesLoaded ||
    !result.shop.productCards.onlyTwoImages || !result.shop.productCards.coverApparel || !result.shop.productCards.containEquipment || !result.shop.productCards.containEquipmentSourceUncropped || !result.shop.productCards.containSecondarySourceUncropped || !result.shop.productCards.saleState || !result.shop.productCards.soldOutState ||
    !result.shop.productCards.variablePrice || !result.shop.productCards.singleImageStable || !result.shop.productCards.noImageState || !result.shop.productCards.longTitleBounded || !result.shop.productCards.archiveColumns ||
    !result.shop.productCards.retailPresentation || !result.shop.productCards.swatchContract || !result.shop.productCards.swatchInteraction.configured || !result.shop.productCards.swatchInteraction.allMappedConfigured || !result.shop.productCards.swatchInteraction.allMappedButtons || !result.shop.productCards.swatchInteraction.allMappedLabels || !result.shop.productCards.swatchInteraction.mixedConfigured || !result.shop.productCards.swatchInteraction.mixedPreviewable || !result.shop.productCards.swatchInteraction.mixedAvailableOnly || !result.shop.productCards.swatchInteraction.mixedNoMismatch || !result.shop.productCards.swatchInteraction.simpleIndicators || !result.shop.productCards.swatchInteraction.simpleNoFakeButtons || !result.shop.productCards.swatchInteraction.simpleNoPreviewClass || !result.shop.productCards.swatchInteraction.singleColourOmitted || !result.shop.productCards.swatchInteraction.maxFiveVisible || !result.shop.productCards.swatchInteraction.overflowLabel || !result.shop.productCards.swatchInteraction.groupSemantics || !result.shop.productCards.swatchInteraction.focusVisible || !result.shop.productCards.swatchInteraction.selected || !result.shop.productCards.swatchInteraction.sourceChanged || !result.shop.productCards.swatchInteraction.dimensionsStable || !result.shop.productCards.swatchInteraction.noNavigation || !result.shop.productCards.swatchInteraction.noAddToCart || !result.shop.productCards.swatchInteraction.noNestedInteractive || !result.shop.productCards.swatchInteraction.hoverPreserved ||
    !result.shop.productCards.hover.checked || (result.width >= 768 && !result.shop.productCards.hover.changed) || !result.shop.productCards.hover.secondaryLoaded ||
    !result.shop.archive.header || !result.shop.archive.toolbar || !result.shop.archive.count || !result.shop.archive.filterToggle || !result.shop.archive.ordering || !result.shop.archive.grid || result.shop.archive.horizontalOverflow || result.shop.archive.brokenImages || result.shop.archive.columnCount !== result.shop.archive.expectedColumns || result.shop.archive.presentation.brandColor !== 'rgb(35, 136, 173)' || result.shop.archive.presentation.titleWeight !== '700' || result.shop.archive.presentation.priceWeight !== '600' || result.shop.archive.presentation.saleCurrentWeight !== '600' || result.shop.archive.presentation.saleOldWeight !== '500' || result.shop.archive.presentation.saleOldDecoration !== 'line-through' || result.shop.archive.presentation.brandMediaGap < 10 || result.shop.archive.presentation.brandMediaGap > 18 || result.shop.archive.presentation.brandTitleGap < 4 || result.shop.archive.presentation.brandTitleGap > 9 || result.shop.archive.presentation.swatchTitleGap < 4 || result.shop.archive.presentation.swatchTitleGap > 10 || result.shop.archive.presentation.swatchPriceGap < 4 || result.shop.archive.presentation.swatchPriceGap > 10 || (result.width <= 430 && (result.shop.archive.presentation.minMediaCardRatio < .97 || result.shop.archive.presentation.columnGap < 7 || result.shop.archive.presentation.columnGap > 16 || result.shop.archive.presentation.rowMediaAlignment > 2 || result.shop.archive.presentation.rowInfoGap < 0 || result.shop.archive.presentation.rowInfoGap > 50)) ||
    !result.shop.archive.drawer.opened || !result.shop.archive.drawer.bodyScrollLock || !result.shop.archive.drawer.focusInside || !result.shop.archive.drawer.closedByEscape || !result.shop.archive.drawer.focusRestored || (result.shop.archive.drawer.accordion.configured && (!result.shop.archive.drawer.accordion.expanded || !result.shop.archive.drawer.accordion.collapsed)) ||
    !result.pdp.themeVisible || result.pdp.responseStatus === null || result.pdp.responseStatus >= 400 || result.pdp.navigationError || result.pdp.horizontalOverflow || result.pdp.consoleErrors.length || result.pdp.pageErrors.length ||
    !result.pdp.product.mainFound || result.pdp.product.mainHasCardClass || result.pdp.product.mainHasFitClass || !result.pdp.product.breadcrumb || !result.pdp.product.galleryFound || result.pdp.product.galleryImageCount < 1 || !result.pdp.product.galleryPrimaryImageLoaded || !result.pdp.product.galleryImagesHaveAlt || result.pdp.product.galleryLinks < 1 || (result.width >= 1121 && !result.pdp.product.galleryUsesGrid) || (result.width <= 767 && !result.pdp.product.galleryUsesScrollSnap) || result.pdp.product.galleryHasFlexViewport || result.pdp.product.galleryTransform !== 'none' || !result.pdp.product.summaryFound || !result.pdp.product.brand || result.pdp.product.brandColor !== 'rgb(35, 136, 173)' || result.pdp.product.titleCount !== 1 || !result.pdp.product.price || !result.pdp.product.addToBag || !result.pdp.product.nativeColour || !result.pdp.product.nativeSize || !result.pdp.product.colourControl || !result.pdp.product.sizeControl || result.pdp.product.details.length !== 2 || result.pdp.product.details.includes('Material & care') || result.pdp.product.hasDefaultTabs || result.pdp.product.hasDefaultExcerpt || result.pdp.product.hasDefaultMeta || result.pdp.product.hasMaterialCare || !result.pdp.product.relatedFound || !result.pdp.product.relatedCardContract || !result.pdp.product.relatedRetailPresentation || !result.pdp.product.relatedSwatchContract || !result.pdp.interactions.initialRange || !result.pdp.interactions.initialDisabled || !result.pdp.interactions.colourSync || !result.pdp.interactions.colourName || !result.pdp.interactions.sizeSync || !result.pdp.interactions.asymmetricDisabled || !result.pdp.interactions.disabledCannotActivate || !result.pdp.interactions.variationFound || !result.pdp.interactions.variationPrice || !result.pdp.interactions.variationImage || !result.pdp.interactions.resetSync || !result.pdp.interactions.sizeGuide || !result.pdp.interactions.shippingReturns || !result.pdp.interactions.accordionsKeyboard || !result.pdp.relatedSwatchInteraction.configured || !result.pdp.relatedSwatchInteraction.semanticContract || (!result.pdp.relatedSwatchInteraction.selected && !result.pdp.relatedSwatchInteraction.availableOnlySafe) || !result.pdp.relatedSwatchInteraction.noNavigation || !result.pdp.relatedSwatchInteraction.noNestedInteractive ||
    !result.headerMode.colorLogoVisible || !result.headerMode.colorLogoLoaded || !result.headerMode.cartVisible ||
    !result.search.opened || !result.search.inputFocused || !result.search.closed || !result.search.focusRestored ||
    mobileFailure || desktopFailure || missingConfiguredMenu ||
    !result.stickyHeader.aboveContent || !result.stickyHeader.announcementScrolledAway ||
    !result.focus.light?.focusVisible || !result.focus.dark?.focusVisible ||
    !fallbackContract.sharedRenderer || !fallbackContract.desktopClass || !fallbackContract.mobileClass
  );
});

if (failures.length) {
  console.error(`Visual UAT failed at: ${failures.map((failure) => failure.width).join(', ')}`);
  process.exitCode = 1;
}

if (!archiveFunctional.filterSubmit || !archiveFunctional.activeChips || !archiveFunctional.sorting || archiveFunctional.popularityFirst !== 'STORE-005 TEST Simple Performance Suit' || !archiveFunctional.categoryArchive || !archiveFunctional.brandArchive || !archiveFunctional.emptyState || (process.env.SSZ_REQUIRE_PAGINATION === '1' && !archiveFunctional.pagination) || archiveFunctional.consoleErrors.length || archiveFunctional.pageErrors.length) {
  console.error('Archive functional UAT failed');
  process.exitCode = 1;
}
