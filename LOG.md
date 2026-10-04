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

## 2026-09-27 — STORE-006 visual refinement

IMPLEMENTATION
Applied Sol’s focused PLP presentation correction on the existing `luna/STORE-006-shop-category-pages` branch. Added an archive-only `ssz-product-archive` body scope and refined only the listing presentation: light SwimShop-blue brand line, semibold title weight, image-to-copy spacing, title-to-price spacing and responsive grid row rhythm. The existing 4:5 media frame, contain/cover behavior, hover secondary image, SALE/SOLD OUT badges, placeholder handling, filters, sorting, pagination and query contract remain unchanged. No card swatches, logos, quick actions or architecture rewrite was introduced.

VERIFICATION
Re-ran the complete ten-width visual UAT at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. Computed archive assertions passed for brand color `rgb(35, 136, 173)`, title weight `600`, 16px image-to-brand gap, 10.4px title-to-price gap and responsive row gap. Fine-pointer hover passed at desktop widths; mobile widths correctly preserved touch/non-hover behavior. Multi-filter submit, active chips, sorting/popularity, category archive, brand archive, empty state, drawer behavior, no overflow, no broken images and no console/page errors remained green. Homepage New Arrivals/Best Sellers and STORE-003 header/search/navigation regression checks passed.

HANDOFF
Visual revision is complete and ready for Sol review. The filter layout, archive query structure and all non-presentation STORE-006 architecture remain unchanged. STORE-007 has not started and no merge has been performed.

## 2026-09-27 — STORE-006 spacing revision

IMPLEMENTATION
Diagnosed the live archive geometry before changing spacing. WooCommerce's responsive `li.product` rule was sizing cards to 48% of each CSS grid track, leaving the apparent horizontal dead space. Added an archive-only, higher-specificity correction so product cards and media fill their grid tracks, then set 12px mobile gutters, 24px mobile row gaps, moderate tablet/desktop gaps, 12px media-to-brand spacing, 5.6px brand-to-title spacing and 6.4px title-to-price spacing. Archive titles now use weight `700`; base and sale-current prices use weight `600`; sale-old prices remain muted and struck through. Homepage rails and shared STORE-005 card behavior remain unchanged.

VERIFICATION
Measured the rendered Shop archive at 360, 390 and 430px: grid widths are 328/358/398px, card and media widths are 158/173/193px, media/card ratio is `1.000`, column gap is 12px, row info-to-next-media gap is 24px and row media alignment is exact. The full ten-width Playwright UAT passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px, including archive structure, filters, sorting, category/brand contexts, drawer behavior, media contracts, badges, hover, no overflow, no broken images and no runtime errors. Manual exact-width screenshots confirmed the compact two-column mobile rhythm and desktop 4-column presentation.

HANDOFF
STORE-006 spacing revision is complete and ready for Sol review. Filter layout, category rail, sorting, query logic, pagination and archive architecture remain unchanged. STORE-007 has not started and no merge has been performed.

## 2026-09-27 — STORE-006 related retail cards and colour previews

IMPLEMENTATION
Extended the archive retail presentation to WooCommerce Related Products through the reusable `ssz-product-card--retail` class and an opt-in `ssz_product_card_retail_presentation` filter. Homepage New Arrivals and Best Sellers remain outside the retail scope. Added global `pa_colour` swatches between title and price for archive/related cards only, with maximum five visible terms plus accessible `+N` overflow. Known terms map through `ssz_product_colour_value`; unknown terms receive a neutral outlined swatch. The hook boundary now closes retail product links before sibling swatch/price content so buttons are never nested inside WooCommerce product anchors.

IMPLEMENTATION
Added the no-framework `product-card-swatches.js` enhancement. Variable cards serialize responsive variation image data for `attribute_pa_colour` mappings and update only the primary card image, `srcset`, `sizes`, alt text and selected state; explicit colour preview suppresses unrelated secondary hover. Simple multi-colour cards remain indicator-only without guessed image mapping. The Local-only variable fixture now has seven colours, Black/Navy/Blue image mappings, and existing simple multi-colour/single-colour fixtures cover the other display states.

VERIFICATION
The full ten-width Playwright UAT passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. It verified related retail styling, global `pa_colour` data, single-colour omission, five-swatch maximum, `+2` overflow, accessible labels/focus, no nested buttons or anchors, Navy image-source change, selected state, unchanged dimensions, no navigation/cart action and hover suppression after explicit preview. Archive filtering/sorting/drawer/category/brand/empty-state checks and STORE-003/004/005 regressions remained green. Exact-width Shop and PDP screenshots were manually reviewed at the requested mobile and desktop widths; SALE/SOLD OUT, 4:5 media and the PDP main variation form remain intact.

HANDOFF
STORE-006 related-card and colour-preview work is complete and ready for Sol review. Filter layout, query logic, category navigation, sorting, pagination, archive geometry and PDP variation controls remain unchanged. STORE-007 has not started and no merge has been performed.

## 2026-09-27 — STORE-006 final swatch correctness correction

IMPLEMENTATION

Corrected the swatch contract so only terms with trustworthy variation-image data render as `Preview …` buttons. Unmapped variable colours and simple multi-colour terms now render as non-focusable indicators with accessible `Available in …` text; they do not expose `aria-pressed`, mutate images or activate colour-preview hover suppression. Added `role="group"` and an accessible `Available colours` name to the swatch row, hardened JavaScript against stale/malformed unmapped controls, and retained the five-item plus `+N` display limit.

VERIFICATION

Added Local-only all-mapped and mixed-variable fixtures, then passed the full visual UAT at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. UAT verified mapped buttons and image changes, mixed Navy/Red indicator safety, simple indicator-only cards with normal hover, group semantics, focus, no nested interactive controls, no navigation/cart/image mismatch, related-product parity, homepage unchanged, archive filter/sort/pagination regressions and no console/page errors. PHP/JavaScript syntax and diff checks passed.

HANDOFF

STORE-006 final swatch correction is complete and ready for Sol review. Previous approved commit: `69c77779b251565bede301ec46693e60cb3f98d5`. STORE-007 has not started and no merge has been performed.

## 2026-09-28 — STORE-006 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-006 branch through commit `33b968d8afa1e0d7ac46b0241f5531349a94670e`. Review covered the hook-first Shop/category/brand archive framework, contextual headers and category rails, server-rendered GET filters, active chips, native sorting and pagination, accessible filter drawer, archive empty state, refined PLP card geometry, Related Products parity and native `pa_colour` swatches.

VERIFICATION
The final correction cleanly separates previewable colour swatches from available-only colour indicators: only trustworthy variation-image mappings render interactive Preview buttons, while unmapped colours expose non-interactive accessible “Available in …” indicators. The swatch row has group semantics, JavaScript ignores stale/unmapped preview controls, homepage cards remain unchanged, PDP variation controls remain native, and the complete ten-width automated UAT plus STORE-003/004/005 regressions passed.

DECISION
STORE-006 is accepted. Pull request #5 was merged to `main` at merge commit `45e0a2767f0cc8cde48b3f2ef90127f4434ad34f`. STORE-007 — Product Page becomes the next planned initiative.

## 2026-09-28 — STORE-007 implementation and verification

IMPLEMENTATION

Created branch `luna/STORE-007-product-page` from accepted main SHA `61174cc63d353acac9d386afe7dbccac5838febd`. Advanced the theme version to `0.7.0` in both `functions.php` and the `style.css` header. Added the hook-first PDP layer in `inc/woocommerce/product-page.php`, `inc/woocommerce/product-page-settings.php`, `assets/css/product-page.css` and `assets/js/product-page.js`; no WooCommerce template override, AJAX, third-party gallery library or competing variation engine was introduced.

IMPLEMENTATION

The PDP preserves native WooCommerce gallery images, links, variation image updates, zoom and lightbox behavior while removing only FlexSlider support on single-product pages. Presentation is a wide desktop two-column image grid, a tablet one-column gallery beside a sticky summary, and a mobile horizontal CSS scroll-snap gallery with normal-flow purchase content. The summary uses native brand archive links, native price HTML with `variation.price_html` synchronization, real `pa_colour`/`pa_size` buttons backed by native selects, WooCommerce availability/quantity, `Add to bag`, and native `<details>/<summary>` accordions.

IMPLEMENTATION

Added Customizer page selectors under `SwimShop Product Page`: `Size Guide Page` and `Shipping & Returns Page`, both sanitized with `absint`. Product Details uses long-description-first/short-description-fallback content. Shipping & Returns renders only configured page content. Material & Care was intentionally not implemented. Related Products retain the accepted STORE-006 retail card system unchanged.

VERIFICATION

Upgraded the Local-only PDP fixture separately from the STORE-005 archive overflow fixture. Hidden `STORE-007 TEST Variable Product Page` has five gallery images and asymmetric combinations: Black S/M/L, Navy M/L/XL and Blue S/M, with mapped local variation images. The existing STORE-005 variable fixture retains seven colours and `+2` archive overflow. Temporary local pages were assigned through theme mods for configured/unconfigured UAT and restored after the check.

VERIFICATION

The expanded `tools/visual-uat.mjs` passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px with zero PDP console/page errors. It verified HTTP success, no overflow, breadcrumb, five-image gallery nodes, desktop grid/mobile scroll-snap presentation, sticky/normal-flow summary behavior, brand, one H1, native price, dynamic variation price, variation image, keyboard colour/size controls, disabled combinations, reset, both accordions, no default tabs/meta/excerpt and related retail-card parity. STORE-003/004/005/006 regressions remained green.

VERIFICATION

Manual Local checks passed simple, sale, out-of-stock, no-image, single-image, multi-gallery and variable PDP fixtures. Simple and valid variable products added through native WooCommerce forms and updated the cart fragment count. The configured Size Guide link and Shipping & Returns accordion resolved correctly; when both settings were cleared, both UI elements were absent. Native zoom/lightbox opened successfully, and the FlexSlider wrapper/transform was absent.

HANDOFF

STORE-007 implementation and verification are complete on `luna/STORE-007-product-page`; branch remains unmerged and is ready for Sol review. STORE-008 has not started.

## 2026-09-28 — STORE-007 variation-row progressive-enhancement correction

IMPLEMENTATION

Corrected the enhanced PDP variation form so only the native `pa_colour` and `pa_size` rows receive the `ssz-variation-native-row--enhanced` class. Rows without WooCommerce's `.reset_variations` collapse completely after JavaScript enhancement; the reset-containing row keeps the native reset link while its visible label and unnecessary geometry collapse. The native selects remain in the DOM, remain WooCommerce's commerce source of truth, and remain visually hidden with their Woo minimum dimensions reset. Unsupported variation attributes remain native and visible. JS-off behavior remains the original native WooCommerce form.

VERIFICATION

Extended `tools/visual-uat.mjs` to assert one visible Colour label and one visible Size label, custom group visibility, native-select presence and visual hiding, enhanced row classes, collapsed native-row geometry, reset synchronization, unsupported-attribute fallback where present, and a dedicated JavaScript-disabled native fallback pass. The full UAT passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. Local browser inspection confirmed the size select's WooCommerce `min-width:75%` and `min-height:48px` constraints were neutralized to 1px enhanced geometry.

HANDOFF

STORE-007 variation-row correction is complete on `luna/STORE-007-product-page`, remains unmerged, and is ready for Sol review. STORE-008 has not started.

## 2026-09-30 — STORE-007 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-007 branch through commit `c46d7ed9ffd79a87cccc2e54145267504ff9e50e`. Review covered the hook-first premium PDP, native WooCommerce gallery/variation compatibility, responsive desktop/tablet/mobile gallery presentation, sticky summary, native brand/price/stock/quantity, progressive `pa_colour` and `pa_size` controls, dynamic variation price/image updates, Size Guide and Shipping & Returns settings, Product Details accordion, explicit absence of Material & Care, and Related Products regression preservation.

VERIFICATION
The final correction cleanly collapses only the enhanced native `pa_colour`/`pa_size` rows after JavaScript initialization, removes duplicate visible labels, preserves native selects as WooCommerce's source of truth, retains `.reset_variations`, leaves unsupported attributes native and visible, and preserves full JS-off fallback behavior. The ten-width UAT, PHP/JS/JSON validation, packaging checks and STORE-003/004/005/006 regressions passed.

DECISION
STORE-007 is accepted. Pull request #6 was merged to `main` at merge commit `88f800a3ac09fa0e938d7fa22d7509c0a216af1f`. STORE-008 — Search becomes the next planned initiative.

## 2026-10-01 — STORE-008 implementation and verification

SCOPE

Created `luna/STORE-008-search` from accepted `main` SHA `75a2ac8a1697d2f94482d9eb752899648e533c16`. Ivory Search FREE v5.5.18 (`add-search-to-menu`) is installed and configured in the Local WordPress runtime only; no plugin files, database export, uploads or generated runtime data are part of the repository.

CONFIGURATION

Configured the existing Ivory Default Search Form (ID 117) as a product-only search. Title, content and excerpt matching are enabled, product taxonomy-title matching is enabled for the Local product brand/category/tag/colour/size taxonomies, AJAX is enabled for both predictive results and the search results page, the free Partial matching mode is selected, and the result limit is six. The Default WordPress Search Engine remains active: the free inverted-index option was inspected but could not be built in this Local UI and was reverted rather than leaving a broken index dependency. SKU matching and typo correction remain unverified/unavailable in the free backend and are documented as expected limitations.

IMPLEMENTATION

Preserved the existing SwimShop header search shell and added a small integration layer for the real Ivory form: product-search labels/placeholders, focus/close state and the shared overlay lifecycle. Added scoped Ivory result styling using the actual AJAX selectors for product image, title, price and excerpt presentation. Extended the STORE-006 WooCommerce archive context to product searches so the results page reuses the approved archive cards, filters, sorting, pagination and query-preserving GET contract. Search filters now correctly apply the serialized brand/size/colour values emitted by the drawer. Added product-only search empty states with a visible search-again form and Shop all fallback, plus a generic `search.php` fallback when WooCommerce is unavailable.

VERIFICATION

Phase A passed the required existing-form, exact-title, partial-title, category, AJAX, Enter/submit, product-only and no-console/page-error gates. The Local matrix returned one exact `Variable Training Suit`, six product suggestions for `train`, one Goggles result, six Arena results and six Navy results. `medium` returned no result in the free taxonomy-title path; short `m` was broad, `trainng` returned no result, and representative `SKU-TEST-001` returned no result as expected for the fixture/free-tier limitation. `Sample Page` returned no products. Direct result-page checks passed search heading, filters, Arena filter preservation, sorting, pagination and no-results recovery.

The full `npm run visual-uat` passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px with the existing STORE-003/004/005/006/007 regressions, Ivory selector contract, overlay interactions and zero console/page errors. PHP lint, JavaScript syntax checks and `git diff --check` passed.

HANDOFF

STORE-008 implementation and Local verification are complete on `luna/STORE-008-search`; the branch remains unmerged and is ready for Sol review. STORE-009 has not started.

## 2026-10-02 — STORE-008 review corrections and final handoff

SCOPE

Applied only the requested review corrections on the existing `luna/STORE-008-search` branch after reviewed commit `baebf4a8fb25ed60131bf504c7435ce8dc295550`. The theme version is now `0.8.0` in both the PHP constant and stylesheet header. Replaced all invalid `--ssz-color-muted` references in `search.css` with approved `--ssz-color-text-muted`. No backend, header, AJAX, archive, card or PDP redesign was introduced.

LOCAL-ONLY SKU CHECK

Assigned `SSZ-STORE008-SKU-001` to existing Local product ID 72, `STORE-005 TEST Variable Training Suit`, through wp-admin only. The real title returned one product; the exact SKU returned `Nothing found`. The product/database change is runtime-only and is not tracked, exported or packaged.

SEARCH AND QUERY VERIFICATION

Predictive UAT now requires an actual Ivory product result image element, `complete === true`, `naturalWidth > 0` and `naturalHeight > 0`; all four checks passed. Verified matching includes title, partial word, category, native `product_brand`, native `pa_colour`, AJAX and product-only behavior. The matrix also recorded `XL` as three results, while `medium` remains a limitation because the Local fixture terms are XS/S/M/L/XL and no `Medium` term exists; short `m` is broad, typo `trainng` returns no results, and exact SKU matching is unsupported in this free configuration.

Automated search-archive checks passed filter submission, active-chip removal, native sorting, Clear all and pagination with `s=train`, `post_type=product` and selected parameters preserved. Empty price inputs are omitted at filter submit so the active chip is rendered consistently. The full Playwright regression passed at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px, including STORE-003/004/005/006/007 coverage, with zero console/page errors. PHP lint, JavaScript syntax, JSON validation, diff checks and packaging passed; the package contains no Ivory/plugin/runtime artifacts.

HANDOFF

STORE-008 review corrections are complete and ready for Sol review on `luna/STORE-008-search`. The branch remains unmerged, and STORE-009 has not started.

## 2026-10-03 — STORE-008 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-008 branch through commit `c76828ef16d03bace15ccda966b12ecd56d13134`. Review covered the Ivory Search FREE integration boundary, existing SwimShop header search shell, predictive AJAX result styling, title/partial/category/native `product_brand`/native `pa_colour` matching, product-only submitted results, STORE-006 archive/card/filter/sort/pagination reuse, search query preservation, no-results recovery and plugin-absent/theme fallback behavior.

VERIFICATION
The final correction advanced the theme to `0.8.0`, replaced the invalid search colour token with the approved muted semantic token, added loaded predictive-image assertions, used a real Local-only SKU fixture to verify exact SKU matching remains unsupported in the free configuration, clarified actual size-term behavior, and automated preservation of `s=train` plus `post_type=product` through filters, active-chip removal, sorting, Clear all and pagination. The complete ten-width UAT and STORE-003/004/005/006/007 regressions passed, along with PHP/JavaScript/JSON/diff/package checks and confirmation that no Ivory/plugin/runtime artifacts are packaged.

DECISION
STORE-008 is accepted. Pull request #7 was merged to `main` at merge commit `7356d95ee40b439533755d2f18af01d95560d033`. STORE-009 — Cart and mini-cart becomes the next planned initiative.

## 2026-10-02 — STORE-009 implementation and verification

SCOPE

Created `luna/STORE-009-cart-mini-cart` from accepted `main` SHA `4ef4bcb`. The theme advances to version `0.9.0`. STORE-010 was not started, the Cart page was not converted from its live WooCommerce Cart Block, and no Local database, cart session, upload or plugin/runtime artifact was added to the repository.

IMPLEMENTATION

Added a global WooCommerce-aware mini-cart drawer and Cart Block presentation layer. The header bag remains a real `/cart/` link; JavaScript progressively enhances it into a right-side dialog with Woo fragment rendering, Store API quantity/remove mutations, live count/subtotal refresh, readable variable-product labels, exact bag/checkout/cart/continue-shopping actions, empty state, loading/error status, focus trapping/restoration, Escape/backdrop close, body scroll lock and shared one-overlay coordination. Normal form submissions use a server session flag for delayed auto-open; WooCommerce's native `added_to_cart` event covers AJAX PDP adds. The Cart page retains native Cart Block rows, coupon disclosure and checkout behavior while receiving theme styling, a synchronized subtotal bridge and a sibling empty state. The existing New in store rail is hidden on Cart pages to keep the cart hierarchy focused.

VERIFICATION

Local runtime checks confirmed page ID 9 `/cart/` is the native `wp-block-woocommerce-cart` Cart Block and that WooCommerce 11.1.2 Store API/cart fragments remain authoritative. Manual browser checks covered drawer open/close, focus restoration, scroll locking, Store API plus/minus, native Cart Block quantity synchronization, Cart Block remove, search-to-cart exclusivity and simple/variable PDP additions. The Playwright harness covers all required widths and functional cart flows; the supported checks pass simple/variable add, variation labels, quantity/count/subtotal synchronization, Cart Block rendering, coupon, remove, empty states and zero console/page errors. The strengthened JavaScript-disabled pass intentionally fails the full fallback criterion: a filled Cart Block exposes no quantity/remove controls without hydration, although the real cart link/header and checkout path remain available. PHP lint, JavaScript syntax, diff checks and package verification pass.

LIMITATION

WooCommerce's Cart Block is JavaScript-hydrated. The branch preserves the accepted block architecture as requested and therefore cannot provide filled-cart quantity/remove controls with JavaScript disabled; no-JavaScript visitors retain real cart navigation and the server-rendered empty state. A shortcode replacement would be a separate architecture decision for Sol.

HANDOFF

Supported implementation and verification were complete on `luna/STORE-009-cart-mini-cart`; the initial handoff recorded STORE-009 as Partial pending Sol's architecture decision on the filled-cart no-JavaScript limitation. The branch remained unmerged and STORE-010 had not started.

## 2026-10-03 — STORE-009 Sol review corrections

SCOPE

Continued on the same `luna/STORE-009-cart-mini-cart` branch from reviewed commit `f1591c57ba0d5e4d8fbb4227cc41bbb350b1b609`. No merge was performed and STORE-010 was not started.

CORRECTIONS

Added the real `ssz-mini-cart` drawer target for the header `aria-controls` contract; made Continue shopping use stable drawer delegation after Woo fragment replacement; added server-backed branded-empty-state coordination that suppresses the native Cart Block empty title and New in Store rail without JavaScript-only replacement; added an in-flight Store API mutation guard; reduced the Woo AJAX add listener to one namespaced `added_to_cart.sszCart` registration; and strengthened overlay UAT in both directions for search, filters, mobile menu and configured desktop mega menus with hidden/ARIA/scroll-lock assertions. The Cart Block architecture was preserved.

VERIFICATION

The final Local Playwright run passed STORE-003–009 regression coverage at `360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920px`, including zero horizontal overflow, zero console/page errors, cart fragment replacement, duplicate-mutation protection, single branded empty state, all configured overlay directions and the real `aria-controls` target. Functional cart UAT passed simple/variable add, variation labels, quantity/count/subtotal synchronization, coupon, remove, checkout/view-cart/Continue shopping and AJAX single-open behavior. No-JavaScript facts are explicit: header cart link PASS, Cart page PASS, checkout path PASS, filled quantity control UNSUPPORTED, filled remove control UNSUPPORTED. PHP lint, JavaScript syntax, JSON, diff and package checks passed; the package has one top-level `swimshop-zimbabwe/` directory and contains the corrected cart assets.

DECISION

Sol accepted the no-JavaScript filled Cart Block quantity/remove limitation as an architectural consequence of retaining the live WooCommerce Cart Block. STORE-009 review corrections are Complete; the limitation is documented and is not a failure. The branch remains unmerged and STORE-010 has not started.

HANDOFF

`STORE-009 REVIEW CORRECTION: Complete`. New correction commit and push will be recorded by Git history on `luna/STORE-009-cart-mini-cart`; previous reviewed commit: `f1591c57ba0d5e4d8fbb4227cc41bbb350b1b609`.

## 2026-10-03 — STORE-009 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-009 branch through commit `96ed43ecfebf339ef77f917ddf51d575b1211a9c`. Review covered the retained WooCommerce Cart Block architecture, theme-owned mini-cart drawer, Store API quantity/remove mutations, Woo fragment synchronization, auto-open after successful Add to Bag, readable variation labels, header count synchronization, branded Cart presentation, coupon behavior, empty states, responsive layout and one-overlay interaction contract.

VERIFICATION
The final correction added the real `aria-controls` drawer target, stable delegated Continue shopping handling after fragment replacement, a single branded empty-cart state, duplicate mutation protection, a single namespaced Woo `added_to_cart` listener and bidirectional overlay UAT for search, filters, mobile navigation and configured desktop mega menus. The complete ten-width STORE-003–009 regression suite, PHP/JavaScript/JSON/diff/package checks and one-top-level-directory package verification passed.

ARCHITECTURE DECISION
The current WooCommerce Cart Block remains authoritative. Its filled-cart quantity/remove controls are JavaScript-hydrated and are unavailable with JavaScript disabled. Sol accepted this limitation rather than replacing the current Cart Block with the classic `[woocommerce_cart]` shortcode solely for that edge case. Real Cart navigation and the checkout path remain available in the supported fallback.

DECISION
STORE-009 is accepted. Pull request #8 was merged to `main` at merge commit `524e007bc95bd526e6764904912339472a526577`. STORE-010 — Checkout styling becomes the next planned initiative.

## 2026-10-03 — STORE-010 deferred

DECISION
Defer STORE-010 — Checkout styling. No STORE-010 implementation branch was started, no checkout architecture was changed, and the accepted STORE-009 state remains the current storefront baseline.

SCOPE
Checkout styling will be revisited later when the project is ready to address payment/shipping configuration and checkout presentation together. The milestone remains incomplete rather than accepted.

NEXT
Advance active planning to STORE-011 — Customer account styling. STORE-012 — Responsive polish follows STORE-011.

## 2026-10-04 — STORE-011 implementation and verification

SCOPE

Created `luna/STORE-011-account-styling` from accepted `main` SHA `275bea2`. STORE-010 remains deferred, checkout was not changed, and STORE-012 was not started. The theme advances to version `0.11.0`.

IMPLEMENTATION

Kept page ID 11 as the native `[woocommerce_my_account]` shortcode and preserved WooCommerce 11.1.2 server-rendered account endpoints. Added `inc/woocommerce/account.php` for the exact `MY ACCOUNT` title, semantic Downloads removal, native customer-logout relabeling to `Sign out`, and endpoint content headings. Added account-only `assets/css/account.css` for the 1121px desktop nav/content grid, stacked tablet/mobile navigation, active/secondary menu states, native login/lost-password/forms, notices, responsive orders, addresses, order details, account details and the native Payment Methods empty state. No WooCommerce account template override or account JavaScript was introduced.

LOCAL FIXTURES

Created only Local runtime fixtures: one populated customer with order #120, simple and variable line items, billing/shipping addresses, and one empty customer with no orders or addresses. Fixture users, credentials, order rows, uploads and sessions are not tracked, exported or packaged.

VERIFICATION

The final Playwright visual UAT passed STORE-011 logged-out/login/lost-password, exact single H1, menu contract, Downloads omission, Sign out endpoint/function, dashboard, populated/empty orders, native order details and variation text, addresses/edit address, account details/password fields, Payment Methods empty state, JavaScript-disabled navigation and no-overflow checks at `360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920px`. The same run passed the existing STORE-003–009 regression suite with zero console/page errors. PHP lint, JavaScript syntax, JSON parsing, `git diff --check` and one-top-level-directory theme packaging all passed; the package contains no Local runtime fixtures or credentials.

HANDOFF

STORE-011 implementation and Local verification are complete on `luna/STORE-011-account-styling`; the branch remains unmerged and awaits Sol review. Registration remains disabled in Local by configuration, and Payment Methods is directly reachable as WooCommerce's native empty state but is not in the account menu because no saved-method gateway is configured.

## 2026-10-04 — STORE-011 Sol acceptance

VERIFICATION
Sol reviewed the complete STORE-011 branch through commit `3adcedceb4c25d46e77943a1d69c9e61c8c5cb87`. Review covered the retained native `[woocommerce_my_account]` architecture, account-only asset loading, semantic Downloads removal, native customer-logout relabeling to `Sign out`, stable `MY ACCOUNT` title treatment, endpoint content headings, wide desktop account shell, stacked tablet/mobile navigation, logged-out login/lost-password styling, responsive orders/order detail, addresses/edit-address forms, account details/password fields and native Payment Methods handling.

VERIFICATION
The branch uses no WooCommerce account template overrides and no account-specific JavaScript. Local-only populated/empty customer fixtures and a Local-only order exercise real Woo account states without packaging credentials or runtime data. The ten-width account UAT and STORE-003–009 regressions passed with zero console/page errors, along with PHP/JavaScript/JSON/diff/package checks.

RUNTIME DECISIONS
Downloads remains intentionally absent from customer-facing account navigation while the underlying Woo endpoint is left intact. Registration is currently disabled in Local. Payment Methods remains WooCommerce-owned and directly reachable as an empty native state, but is not shown in the account menu because the Local runtime has no compatible saved-method gateway configured.

DECISION
STORE-011 is accepted. Pull request #9 was merged to `main` at merge commit `01bb778e0247ceb15b3f9bb8a6f677e3bbd0342e`. STORE-012 — Responsive polish becomes the next planned initiative. STORE-010 — Checkout styling remains deferred.

## 2026-10-04 — STORE-012 implementation and verification

SCOPE

Created `luna/STORE-012-responsive-polish` from accepted `main` SHA `749224bd7a709f0299d9a9f9dbc5b5b019994aee`. STORE-010 remains deferred, Checkout was not changed, STORE-013 remains next, and the theme advances to version `0.12.0`.

AUDIT

The baseline Local audit at the required widths found no document horizontal overflow, console/page runtime errors or header/PDP mode regressions. The demonstrated visual issue was the four-item homepage New Arrivals/Best Sellers rails falling into three columns at tablet widths and leaving a single orphaned card; the homepage tablet rail now uses four columns from 768–1120px. The archive was reviewed separately and retains its balanced three-column 768–1024px range before switching to four columns at 1025px. The account stylesheet's previous `max-width: 900px` stack did not match the documented 1121px desktop split, so it now stacks through 1120px. No template, WooCommerce architecture or JavaScript behavior was changed for these corrections.

IMPLEMENTATION

Added focused STORE-012 geometry recording to `tools/visual-uat.mjs` for HOME, SHOP, PDP, SEARCH, CART and MY ACCOUNT. It records viewport/document width, primary container edges, header mode, grid modes/columns, key widths, sticky state, overlay state and account geometry; seam assertions cover 767/768, 1120/1121, the 1024/1025 archive exception and short-height purchase/cart reachability. The short-height matrix includes the brief's 390×667, 390×844, 430×932, 768×600, 768×1024, 1024×700, 1024×768, 1280×720 and 1440×900 cases, plus a populated two-line 360×667 mini-cart check. `SSZ_RESPONSIVE_ONLY=1` provides a focused run without duplicating the legacy functional suite.

VERIFICATION

The responsive-only run exited 0 with no horizontal-overflow failures and all seam assertions passing. Manual screenshot review covered the ten required widths, including the 1120→1121 header/PDP transition, 360/390/430 mobile group, 768/1024/1120 tablet group, 1280/1440/1920 desktop group, PDP states, Cart/account states and the populated 360×667 mini-cart. The mini-cart content reported `overflow-y:auto` and its action stack was reachable after internal scroll. The complete legacy ten-width regression also exited 0 with zero console/page errors; Search, Cart and PDP fallback functional checks passed, while authenticated account fixtures remained skipped because the documented account credentials were unavailable. PHP lint, JavaScript syntax, JSON parsing, `git diff --check` and one-top-level-directory packaging passed. The accepted STORE-011 authenticated account UAT remains the account behavior baseline.

HANDOFF

STORE-012 is implemented and ready for Sol review on `luna/STORE-012-responsive-polish`; it is not marked accepted here and no merge was performed. Generated Local screenshots remain ignored under `artifacts/uat/`, and no Local runtime data is part of the branch.

