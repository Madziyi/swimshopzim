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

