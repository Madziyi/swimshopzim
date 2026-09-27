# Design System Contract

STORE-002 establishes reusable presentation primitives for the storefront. Later milestones may extend these primitives, but should not introduce a competing token vocabulary without an explicit decision.

## Brand and visual direction

**APPROVED:** SwimShop Zimbabwe; premium athletic retail; clean, minimal, image-led composition; large whitespace; restrained colour; trustworthy international character.

**APPROVED:** White, black and navy blue are the core palette. Neutral greys support surfaces, borders, muted text, disabled controls and placeholders. Avoid unnecessary accent colours.

Arena remains a hierarchy and restraint reference only. Arena assets, logos, trademarks, copy, campaign imagery and proprietary graphics are not used.

## Colour tokens

The CSS source of truth is `theme/swimshop-zimbabwe/assets/css/main.css`:

- `--ssz-color-white`, `--ssz-color-black`, `--ssz-color-navy`
- `--ssz-color-neutral-050` through `--ssz-color-neutral-700` (small neutral range)
- Semantic aliases: `--ssz-color-text`, `--ssz-color-text-muted`, `--ssz-color-background`, `--ssz-color-surface`, `--ssz-color-surface-muted`, `--ssz-color-border`, `--ssz-color-border-strong`, `--ssz-color-brand`, and `--ssz-color-focus`

Semantic usage is text on the black token, surfaces on white, muted surfaces on neutral-050, borders on neutral-200/300, and navy reserved for brand, focus, announcements and justified emphasis.

## Typography

**APPROVED:** A performant system fallback stack led by Inter: `Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`. No remote font or commercial font dependency is loaded.

**APPROVED:** Body, helper, label, navigation, product, page-title, section-title and hero-display sizes are tokenized. Headings use a tight line height and restrained negative tracking; labels and navigation use uppercase tracking for an athletic retail tone. Responsive display sizes use `clamp()`.

Font weights are limited to regular, medium, semibold, bold and heavy. The product name, price, section heading and hero display tokens are available to custom components and WooCommerce-compatible styling.

## Spacing and layout

**APPROVED:** The shared spacing scale is 2XS, XS, SM, MD, LG, XL, 2XL, 3XL and 4XL. Component gaps, form padding, section rhythm and page gutters use this scale or a responsive value derived from it.

**APPROVED:** Wide content is capped at approximately 1440px; reading content is capped at 760px. Mobile page gutters are 16px, increasing progressively with viewport width through `--ssz-page-gutter`. The shared grid gap is responsive and section rhythm uses `--ssz-section-space`.

## Buttons and forms

**APPROVED:** Buttons have a roughly 48px minimum touch height, restrained square geometry and primary, secondary, brand and light variants. Primary buttons are black with white text; secondary buttons are transparent with a black border; navy is reserved for the brand variant and justified emphasis. Hover, focus-visible, disabled and loading-ready states are defined without page-specific behavior.

**APPROVED:** Text, email, password, search, number, textarea, select, checkbox, radio, labels, helper text, error and disabled states have shared foundations. Native controls remain native and WooCommerce form rows inherit compatible dimensions and focus behavior.

## Product media

**APPROVED:** Product-card media uses a 4:5 ratio through `--ssz-product-media-ratio`. The default fit is cover for apparel-led imagery; the reusable `ssz-media-frame--contain` modifier and `--ssz-product-media-fit` contract allow equipment imagery to avoid harmful cropping. Final product-card markup and states remain STORE-005 scope.

## Shape, motion and stacking

**APPROVED:** Radius tokens are none, 2px, 4px and pill. Most storefront surfaces use no radius; this is not a rounded-card SaaS system.

**APPROVED:** Motion tokens are fast 150ms, normal 200ms and slow 250ms with a shared easing curve. `prefers-reduced-motion: reduce` disables nonessential transitions and smooth scrolling.

**APPROVED:** Stacking uses a small layer scale for sticky header, dropdown, overlay, modal and skip-link layers. Later drawers or modals should use these tokens rather than arbitrary large z-index values.

## Responsive strategy

**APPROVED:** The system is designed for exact UAT widths 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. Existing breakpoints remain limited to 1120px, 782px, 767px and 390px. Mobile is a deliberate layout with its own gutters, section rhythm, hero sizing, grids and footer structure.

## STORE-003 header and navigation

**APPROVED:** `theme/swimshop-zimbabwe/assets/css/header.css` is the source of truth for announcement, sticky-header, navigation, search-panel and mobile-drawer presentation. The announcement bar is non-sticky; the main header uses the shared sticky layer.

**APPROVED:** The default lockup uses the supplied unmodified transparent PNGs in `assets/images/brand/` and renders the SWIMSHOP ZIMBABWE wordmark beside the symbol. A WordPress custom logo remains supported. Header and menu controls use theme-owned inline SVG icons only.

**APPROVED:** Desktop begins at 1121px. The WordPress Primary Navigation owns the top-level structure; depth 1 renders as mega-menu group headings and depth 2 as links. Mobile through 1120px uses a fixed drawer with drilldown/accordion groups, native account/cart URLs, body scroll lock, focus trapping/restoration and Escape/backdrop close behavior.

## Editor alignment

`theme.json` exposes the same palette, spacing sizes, typography family and practical font sizes to WordPress editor controls. CSS variables remain the source for custom storefront components; the two systems use matching values rather than contradictory palettes.

## STORE-004 homepage

Homepage-only composition lives in `assets/css/homepage.css`; global primitives remain in `main.css`, header behavior remains in `header.css`, and WooCommerce behavior remains in `woocommerce.css`.

The approved homepage rhythm is editorial and image-led: Hero, Shop by Category, New Arrivals, Shop the Brands, Shop by Activity, Performance Campaign, Best Sellers, Race Day / Training Equipment, Why SwimShop Zimbabwe, Newsletter, then Footer. Missing media uses navy/black geometric fallback treatments rather than customer-facing implementation labels.

Brand presentation is logo-led on a quiet neutral surface. Native `product_brand` term media is authoritative, with optical max-width/max-height containment and accessible text fallback when a term has no logo. Product sections keep native WooCommerce card rendering and leave final card states to STORE-005.

## STORE-005 product cards

Product cards are reusable across homepage product rails, WooCommerce shop/category/brand archives and related loops. The approved hierarchy is 4:5 media, first-brand text, semantic product title and native WooCommerce price. Cards use a clean page background without a required surrounding card box or heavy shadow.

`assets/css/product-card.css` owns card media, first-gallery-image hover, badges, brand/title/price treatment and responsive card behavior. `assets/css/woocommerce.css` remains responsible for archive grid structure and general WooCommerce/PDP/form styling.

The first gallery image is the only alternate image and is revealed only for fine-pointer hover, with reduced-motion support. Apparel uses cover by default; known equipment/accessory category families use contain through the `ssz_product_card_media_fit` helper/filter while retaining the same 4:5 frame. Cover may use the hard-cropped 4:5 card derivative, but contain uses uncropped responsive media for both primary and first-gallery images. Card classes are scoped to normal WooCommerce loops; the queried main PDP product is excluded. Card badges are limited to SALE and SOLD OUT. Loop ratings and add-to-cart controls are removed through WooCommerce hooks; quick-add, wishlist, quick-view and swatches are intentionally out of scope.

## STORE-006 shop/category archives

Archive composition remains owned by classic WooCommerce hooks. `archive.php` is intentionally not overridden: the theme replaces only the native archive header, result/order toolbar, sidebar and filtered empty state while retaining WooCommerce loop markup, query semantics and pagination.

`assets/css/archive.css` owns archive header, category rail, toolbar, active chips, filter drawer and pagination presentation. Product media and card internals remain in `product-card.css`; shared layout, tokens and form foundations remain in `main.css`/`woocommerce.css`. The drawer is a right-side modal on wider screens and a full-width mobile surface, with the established overlay/modal layers, visible focus treatment, sticky footer actions and reduced-motion-compatible behavior.

The filter contract is server-rendered and URL-addressable: category, native product brand, size, colour, price and availability are represented as GET values. `archive-filters.js` only coordinates drawer state, accordions and form serialization; it does not fetch or render products. Search and navigation close events are shared so the header and archive overlays preserve one-open-surface behavior.

The archive PLP rhythm is scoped with `ssz-product-archive` so shared STORE-005 cards remain unchanged on homepage rails and PDP related loops. Archive cards keep the 4:5 media/brand/title/native-price hierarchy, fill their WooCommerce grid tracks, use a restrained light-blue brand label, bold two-line title and 600-level price. Mobile archives use a narrow 12px column gutter and 24px row gap; image-to-brand, brand-to-title and title-to-price spacing stay compact. Sale-old pricing remains muted/struck through while sale-current pricing follows the 600-level price weight. No card swatches, logos, quick actions or new product-card architecture were added.

## Accessibility contract

Visible `:focus-visible` treatment, readable base sizing, usable control heights, semantic HTML compatibility and reduced motion are preserved. The existing skip link is unchanged. Full accessibility auditing remains STORE-013 scope.

