# Project Log

## 2026-09-26 — Bootstrap

DECISION
Use the supplied STORE-001 ZIP as the source of truth for the initial theme import. Do not recreate the foundation from memory.

OBSERVATION
The repository directory was empty and had no Git metadata. The supplied `swimshop-zimbabwe-STORE-001.zip` was present at the repository root.

IMPLEMENTATION
Initialized Git on `main`, added the GitHub origin, extracted the ZIP theme into `theme/swimshop-zimbabwe/`, and created the project coordination documents and Local sync/packaging tools.

VERIFICATION
The imported theme contains 39 files, a valid WordPress stylesheet header, `functions.php` includes setup, enqueue, helper, navigation, homepage, accessibility and WooCommerce modules, and the Local theme directory matched the imported source by SHA-256 for every file.

VERIFICATION
Local WordPress 7.1.2 and PHP 8.2.29 were identified. The Local site loaded in the browser, but the active theme was Twenty Twenty-Five and WooCommerce was not installed, so WooCommerce behavior was not claimed as verified.

FAILURE
PHP was not on the system PATH. Linting must use Local's bundled PHP executable under `AppData\\Roaming\\Local\\lightning-services\\php-8.2.29+0\\bin\\win64\\php.exe`.

HANDOFF
Bootstrap is ready for Sol review after the initial commit is created and pushed. STORE-002 remains planned and was not implemented.

## 2026-09-26 — STORE-002 implementation

DECISION
Formalize a compact white/black/navy palette with semantic aliases, a responsive spacing scale, a system-font typography stack, restrained radii, 150–250ms motion, a 4:5 product-media contract and a small z-index scale. Preserve STORE-001 class aliases so later milestones can adopt the new vocabulary incrementally.

IMPLEMENTATION
Aligned `theme.json`, `assets/css/main.css` and `assets/css/woocommerce.css`. Added reusable global heading, link, table, form, button, badge, helper, divider, media-frame, price and loading-ready primitives without copying WooCommerce templates or changing commerce logic.

OBSERVATION
The Local theme source is synced, but the site state remains the bootstrap state: Twenty Twenty-Five is active and WooCommerce is not installed. The precondition for custom-theme visual UAT is therefore not met.

VERIFICATION
PHP lint, browser UAT, console checks and representative interaction checks must not be claimed until SwimShop Zimbabwe is active with WooCommerce active. This milestone is ready for those checks once the Local runtime is prepared.

OBSERVATION
After the first STORE-002 sync, WordPress 7.1.2 raised a fatal in `WP_Theme_JSON` because this runtime does not accept `settings.spacing.spacingScale: false`. Removing that field while retaining the explicit spacing sizes restored the homepage.

VERIFICATION
The corrected theme was synced successfully. All PHP theme files pass Local PHP 8.2.29 lint. The browser rendered the active custom theme with WooCommerce 11.1.2 present, including the homepage, sticky header, hero, brand section and footer. Search opened and closed, accepted `swim`, and reached `/?s=swim&post_type=product`.

VERIFICATION
Visual review was performed in the available in-app browser viewport (approximately desktop width) at the homepage top, mid-page brand/product sections and footer. No visible horizontal overflow or contrast regression remained after the hero-heading fix.

OBSERVATION
The available browser binding does not expose viewport overrides for the required 360, 390, 430, 768, 1024, 1280, 1440 and 1920px widths, nor a console-log API. Those checks remain outstanding and are not claimed as complete. A direct PHP CLI WordPress bootstrap was also unavailable because the bundled CLI binary lacks the MySQL extension; the live browser runtime remained authoritative.

## 2026-09-26 — STORE-002 completion verification

IMPLEMENTATION
Fixed the hero stacking context with a local isolated layer contract: media at layer 0, the image wash at layer 1 and hero content at layer 2. The global sticky header remains above the hero. Added white focus outlines for dark hero, campaign, announcement and footer surfaces.

IMPLEMENTATION
Added repeatable Playwright visual UAT tooling in `tools/visual-uat.mjs`, with `npm run visual-uat` as the entry point. The harness uses the installed Chrome executable, runs exact viewports at 360, 390, 430, 768, 1024, 1280, 1440 and 1920px, checks HTTP/theme/runtime state, overflow, sticky header coverage, search, mobile navigation where applicable, keyboard focus and full-page screenshots under ignored `artifacts/uat/`.

VERIFICATION
All eight exact-width runs passed with HTTP 200, active custom theme visible, no horizontal overflow, no console errors, no page errors, working search, working mobile menu at widths through 1024px, sticky header above content, and visible 3px solid keyboard focus outlines. Light-surface focus was navy; dark-surface focus was white.

OBSERVATION
Screenshot inspection at all eight required widths showed stable responsive layout, readable content, contained navigation, and no clipping or horizontal expansion. Placeholder media remains intentionally neutral pending later content/assets work.

VERIFICATION
All PHP files pass Local PHP 8.2.29 lint, `theme.json` parses, `git diff --check` is clean, and the packaged theme ZIP contains only the `swimshop-zimbabwe/` theme root. The repository remains on the STORE-002 branch and is ready for Sol review; it is not marked accepted or merged here.

## 2026-09-26 — STORE-002 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-002 branch through commit `e081549d244b7a10a1e259b726e43a5b44b59a72`, including the token system, `theme.json`, WooCommerce-compatible styling, hero stacking correction, focus treatment, development tooling and documentation.

VERIFICATION
The milestone handoff records successful Local browser UAT at 360, 390, 430, 768, 1024, 1280, 1440 and 1920px with HTTP 200, the custom theme visible, no horizontal overflow, no console or page errors, working search/mobile navigation, correct sticky-header layering and visible keyboard focus. PHP lint, `theme.json` validation and release packaging also passed.

DECISION
STORE-002 is accepted. Pull request #1 was merged to `main` at merge commit `3e31887976a205427a0d418e2983fdd1d076a220`. STORE-003 — Header and Navigation becomes the next planned initiative.

## 2026-09-26 — STORE-003 implementation and verification

IMPLEMENTATION
Created branch `luna/STORE-003-header-navigation` from accepted `main` SHA `65aee1a66cf770a556158805857dd994a583616a`. Extracted header presentation to `assets/css/header.css`, added the supplied unmodified color/white logo assets, and implemented the WordPress-driven desktop mega-menu plus mobile drawer, drilldown and accordion behavior. Search, account, cart count, sticky-header and announcement behavior remain native to the existing storefront shell.

IMPLEMENTATION
The local WordPress Primary Navigation was configured only in the Local database for UAT. It contains the required seven top-level entries, Men depth-1 groups with depth-2 links, and Brands links targeting the native Arena, Speedo and Spurt `product_brand` terms. This runtime menu configuration is not committed to source.

VERIFICATION
`SSZ_REQUIRE_LOCAL_MENU=1 npm run visual-uat` passed at exact widths 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. The pass covered HTTP/theme state, no overflow, logo load, native account/cart URLs, search focus and restoration, menu mutual exclusion, mobile body scroll lock/focus trap/drilldown/accordion/backdrop/Escape close, desktop mega-menu geometry/Escape close, sticky header layering, keyboard focus visibility, and no console/page errors.

OBSERVATION
Visual inspection of the generated screenshots showed the supplied lockup, mobile/desktop breakpoint transition, full-width mega panel, responsive content containment and footer remain stable. Placeholder campaign/media content remains intentionally neutral and is outside STORE-003 scope.

## 2026-09-26 — STORE-003 targeted Sol corrections

IMPLEMENTATION
Updated the shared WordPress walker to suffix menu-item IDs by rendering context (`-desktop` and `-mobile`), preserving current-item/classes filtering while preventing duplicate DOM IDs. Disclosure ARIA now belongs to the actual toggle button; category anchors retain navigation semantics without `aria-haspopup`.

IMPLEMENTATION
Restricted desktop interaction state to top-level mega-menu items, so mouse entry, activation and search events clear other `.is-open` states and synchronize `aria-expanded`. Search opening now dismisses desktop mega menus through the existing custom-event pattern; activating a mega-menu item closes Search. Fresh-install fallback callbacks now share one renderer while emitting desktop-compatible `ssz-primary-menu` or mobile-compatible `ssz-mobile-menu` classes.

VERIFICATION
`SSZ_REQUIRE_LOCAL_MENU=1 npm run visual-uat` passed at all ten required widths. Every result reported zero duplicate IDs; desktop widths with two configured mega-menu items passed hover exclusivity, Search→mega close, mega→Search close, ARIA reset and Escape checks. Mobile drawer, drilldown, accordion, focus restoration, body scroll lock, overflow, sticky-header, logo, search and runtime-error checks remained green. The fallback contract source check passed without changing the configured Local database menu.

## 2026-09-26 — STORE-003 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-003 branch through commit `a2739fa168dc561e5b4d46e3e4e71f0db733c77e`, including the approved brand assets, WordPress-driven desktop mega-menu, mobile drawer/drilldown/accordion behavior, search/account/cart integration, context-specific DOM IDs, disclosure ARIA, fallback navigation, and extended Playwright checks.

VERIFICATION
The STORE-003 handoff records successful UAT at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px with no horizontal overflow, duplicate IDs, console errors or page errors. Desktop mega-menu exclusivity, search/menu mutual exclusion, mobile focus/scroll behavior, sticky layering, logo rendering, PHP lint, JSON/JavaScript validation and theme packaging all passed.

DECISION
STORE-003 is accepted. Pull request #2 was merged to `main` at merge commit `cb0bc621571707e42391b3384e66788071237d4e`. STORE-004 — Homepage becomes the next planned initiative.

## 2026-09-27 — STORE-004 implementation and initial verification

IMPLEMENTATION
Created branch `luna/STORE-004-homepage` from accepted `main` SHA `857bf97348f646812f0d9c42bec44878616b3cc9`. Rebuilt the homepage into the approved ten-section order, added two hero CTAs and desktop/mobile media controls, introduced native category selectors and homepage-specific CSS, added activity/feature/newsletter sections, and preserved native WooCommerce product rendering for STORE-005.

IMPLEMENTATION
Homepage brands use the native `product_brand` taxonomy and `ssz_get_brand_thumbnail_url()` when a term logo exists. Brand links remain native term archives, with accessible text fallback for terms without media. Missing campaign, activity, feature and category media use intentional branded treatments without customer-facing placeholder labels. Newsletter controls are disabled and explicitly integration-ready; no provider or fake success behavior was added.

VERIFICATION
After syncing the theme to Local, the complete Playwright visual UAT passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. Homepage assertions passed for section order/presence, one H1, two hero CTAs, category/brand/activity/feature links, brand archive URLs, no forbidden labels, no broken images, no overflow, no console/page errors and the honest disabled newsletter state. STORE-003 header/search/mobile/desktop checks remained green.

OBSERVATION
The current Local catalog has no uploaded product-brand term logos, so Arena, Speedo and Spurt render through the accessible text fallback. No competitor imagery or logos were downloaded or committed. Authenticated Customizer verification exposed the homepage panel and its hero, category, activity, campaign, product-count, feature, proposition and newsletter controls; no settings were changed or saved.

## 2026-09-27 — STORE-004 targeted review corrections

IMPLEMENTATION
Corrected Performance Campaign layering with an isolated stacking context: campaign media is layer 0, one combined image wash is layer 1, and campaign content is layer 2. Removed the duplicate global campaign media wash so uploaded photography is not double-darkened or hidden behind an ancestor.

IMPLEMENTATION
Replaced the long homepage section with a native `SwimShop Homepage` Customizer panel containing separate Hero, Categories, Shop by Activity, Performance Campaign, Product Sections, Race Day / Training, Store Proposition and Newsletter sections. Existing `ssz_*` setting IDs and values remain unchanged; the pseudo-heading control was removed.

IMPLEMENTATION
Removed repeated activity-card eyebrows so Racing, Training and Open Water each render one prominent title. Changed the default newsletter note to customer-facing “Email sign-up is coming soon.” while retaining the disabled, non-submitting form.

VERIFICATION
Targeted real-media campaign verification used two harmless local-only generated rasters. The authenticated Customizer panel and campaign controls were verified manually; because the in-app media picker did not open, the corresponding theme-mod values were assigned temporarily through a local-only WordPress runtime workaround. Desktop and mobile campaign images rendered above the single overlay, copy remained readable, and the CTA remained clickable. The temporary media and local uploads were removed; theme media settings were restored to the empty fallback state after testing.

## 2026-09-27 — STORE-004 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-004 branch through commit `55e48f1e8fcab2e650a94e11bbb5994ec6e21508`, including the approved homepage section order, native WooCommerce category/brand integration, logo-first `product_brand` rendering, responsive hero and campaign media, activity and feature panels, proposition/newsletter treatment, Customizer panel organization, and expanded homepage UAT.

VERIFICATION
The targeted follow-up corrected campaign stacking with an isolated media/overlay/content layer contract, preserved existing `ssz_*` theme-mod IDs while moving controls into dedicated Customizer sections, removed duplicated activity titles, and replaced developer-facing newsletter language with customer-facing disabled-state copy. Local verification included real campaign media, all ten responsive widths, STORE-003 regression checks, PHP lint, JSON/JavaScript validation, diff checks and theme packaging.

DECISION
STORE-004 is accepted. Pull request #3 was merged to `main` at merge commit `8f6f70cb102c2172b7dddd764eec9be4ae62ebec`. STORE-005 — Product Cards becomes the next planned initiative.

## 2026-09-27 — STORE-005 implementation and verification

IMPLEMENTATION
Created branch `luna/STORE-005-product-cards` from accepted `main` SHA `3b912f8cfed5d3a6a27263f6278b1db34ccb3eeb`. Advanced the theme version to `0.5.0` and kept the normal WooCommerce loop anchor/title/price structure while replacing only the default thumbnail, sale flash, rating and add-to-cart presentation through hooks.

IMPLEMENTATION
Added reusable `assets/css/product-card.css` and theme-owned media/badge rendering. Cards use a stable 4:5 frame, responsive primary image markup, first-gallery-image hover, native first-brand text, SALE/SOLD OUT state logic, semantic two-line title treatment and native WooCommerce price HTML. Equipment/accessory category families receive contain media through `ssz_product_card_media_fit` and its filter; apparel remains cover by default. `ssz_product_card_image_size_for_fit()` keeps cover cards on the hard-cropped `ssz-product-card` derivative while contain cards use uncropped `large` media for both primary and first-gallery images.

IMPLEMENTATION
Removed loop ratings and add-to-cart controls through WooCommerce actions rather than CSS-only hiding. No quick-add, wishlist, quick-view, swatches or other card action tray was introduced. Preserved the homepage mobile scroll-snap rails and archive 2/3/4-column grid contract. Fixed archive-only logo sizing and generated unique search-field IDs so the shared shell remains overflow- and duplicate-ID-safe with WooCommerce active.

VERIFICATION
Created eight Local-only `STORE-005 TEST` products using existing native Arena, Speedo and Spurt brands: simple/in-stock with gallery, sale with gallery, variable price range with gallery, out-of-stock, equipment contain, single-image, no-image placeholder and long-title. Generated non-proprietary raster fixtures live only in Local uploads; no database, upload, generated image or runtime file is tracked.

VERIFICATION
Moved card classes to WooCommerce's `woocommerce_post_class` filter and explicitly excluded the queried main PDP product, so related loops retain `ssz-product-card` classes without leaking them to the single-product root. Expanded `tools/visual-uat.mjs` to exercise homepage New Arrivals/Best Sellers, shop/archive cards and a fixture PDP at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. All ten widths passed card structure, links, 4:5 ratio, uncropped 1200x500 contain sources for both equipment images, image loading, first-gallery-only behavior, fine-pointer hover swap, badges, native price states, cover/contain fit, single/no-image behavior, title bounds, no nested anchors, no ratings/add-to-cart controls, archive columns, PDP root exclusion, related-loop classes, homepage rails, overflow and STORE-003/STORE-004 regression checks. Additional Local browser verification passed Arena, Speedo and Spurt brand archives with the same contract. Manual screenshots confirmed the mobile rail, archive grids, SALE/SOLD OUT, placeholder, complete wide equipment object in contain mode and desktop hover state.

VERIFICATION
Local WooCommerce Coming Soon mode was disabled only in the Local runtime so anonymous automated archive UAT could reach the fixture catalog. The repository contains no secrets, database exports, uploads or generated fixture media. STORE-006 archive shell/filter work and STORE-007 PDP redesign remain out of scope.

## 2026-09-27 — STORE-005 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-005 branch through commit `ccbe27f02e811b91171f19dbf8b479bfd09e6bf0`, including the reusable WooCommerce card structure, 4:5 media frame, first-gallery hover behavior, SALE/SOLD OUT badges, native Product Brand text, native WooCommerce pricing, loop rating/add-to-cart removal, responsive archive/homepage behavior, and Local fixture coverage.

VERIFICATION
The targeted follow-up corrected two implementation details: contain-mode products now use uncropped WordPress `large` media while cover cards retain the hard-cropped `ssz-product-card` derivative, and product-card classes now use `woocommerce_post_class` with explicit exclusion of the queried single-product root while preserving related-product loop classes. Local verification covered wide 1200×500 equipment fixtures, PDP root/related loops, all ten responsive widths, brand archives, hover state, PHP lint, JSON/JavaScript validation, diff checks and theme packaging.

DECISION
STORE-005 is accepted. Pull request #4 was merged to `main` at merge commit `2f54e9135e146087923e4b07513d1b94ab0f0a8b`. STORE-006 — Shop/category pages becomes the next planned initiative.

## 2026-09-27 — STORE-006 implementation and verification

IMPLEMENTATION
Created branch `luna/STORE-006-shop-category-pages` from accepted main SHA `85a55ea032e012d8dca23026c0b91134f11bb961` and advanced the theme version to `0.6.0`. Added a classic WooCommerce hook-first archive layer for Shop, product categories and native product brands: context-aware header/description, brand identity fallback, approved category rail, result toolbar, native sorting, filter drawer, active chips, filtered empty state and preserved WooCommerce pagination. No archive template override, FSE Product Collection, AJAX or plugin was introduced.

IMPLEMENTATION
Added server-rendered GET filtering for category, native `filter_product_brand`, native `filter_size`/`query_type_size`, native `filter_colour`/`query_type_colour`, `min_price`, `max_price` and in-stock availability. Added progressive-enhancement drawer JavaScript for comma-separated multi-select serialization, accordions, focus trap/restoration, Escape/backdrop/button close, body scroll lock and mutual exclusion with search/navigation overlays. Added archive-only CSS without changing STORE-005 card ownership; fixed the closed backdrop state so it cannot intercept the toolbar trigger.

VERIFICATION
Seeded Local-only global attributes, approved category hierarchy, size/colour terms, stock coverage, prices and distinct popularity values across the existing eight `STORE-005 TEST` products. Live browser checks verified Shop, Men, Arena and multi-filter archive URLs. The expanded Playwright visual UAT passed all ten required widths: 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. It passed archive structure, 2/3/4 grid columns, native ordering, drawer open/close/focus/accordion behavior, no overflow, no broken images, no console/page errors and all STORE-003/004/005 regression assertions.

VERIFICATION
Functional archive UAT passed multi-group filter submit with `filter_product_brand=17`, `filter_size=m`, `query_type_size=or`, `filter_colour=black`, `query_type_colour=or`, `filter_stock_status=instock`, `min_price=20` and `max_price=130`; active chips, sorting/popularity order, category archive, brand archive and zero-result empty state all passed. A separate reversible pagination pass set `woocommerce_catalog_rows=1`, verified four cards and page 2 with four cards/current page 2, then restored the normal Local options. PHP lint, JavaScript syntax, diff checks and theme sync passed; the temporary UAT scripts and runtime fixtures remain untracked/local only.

HANDOFF
STORE-006 implementation, verification and documentation are complete. Branch is ready for Sol review; STORE-007 has not started and no merge has been performed.

