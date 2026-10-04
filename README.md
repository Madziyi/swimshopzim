# SwimShop Zimbabwe

Custom WordPress/WooCommerce storefront source for SwimShop Zimbabwe.

## Repository layout

- `theme/swimshop-zimbabwe/` — the only directory packaged into the installable theme ZIP.
- `tools/` — safe local development and release helpers.
- `docs/` — reserved for supporting technical documentation.
- Root Markdown files — project contract, coordination state, milestones, UAT, design and deployment rules.

## STORE-001 foundation

The supplied `swimshop-zimbabwe-STORE-001.zip` was imported without recreating its implementation. The theme provides WordPress metadata and setup, WooCommerce support declarations, responsive shell, announcement bar, header/mobile navigation foundation, search overlay, homepage templates and Customizer settings, native `product_brand` display support, WooCommerce loop/PDP hooks, cart-count fragments, and accessibility foundations.

The imported source intentionally uses neutral placeholders and does not include proprietary Arena assets. STORE-002 is not implemented by this bootstrap.

## Local development

The authoritative Local site is `C:\Users\stanm\Local Sites\swimshop-zimbabwe\app\public`. Use `tools/sync-theme.ps1` after inspecting the destination theme directory. The script copies source files without deleting destination-only files; it requires an explicit `-AllowExisting` when a destination already exists.

Example from the repository root:

```powershell
.\tools\sync-theme.ps1 -AllowExisting
```

The current Local activation and WooCommerce status are recorded in `LIVE-STATE.md`; keep that file authoritative when runtime state changes.

STORE-003 uses the WordPress Primary Navigation as the source of truth for desktop and mobile header navigation. The local `DEVELOPMENT` menu used for UAT is runtime database configuration and is intentionally not exported into the repository. Configure a Primary Navigation menu in WordPress when reproducing the mega-menu UAT; the theme retains a seven-link fallback for a new install.

STORE-004 uses the native Customizer panel `SwimShop Homepage` with separate Hero, Categories, Shop by Activity, Performance Campaign, Product Sections, Race Day / Training, Store Proposition and Newsletter sections. Existing `ssz_*` theme-mod IDs are preserved while controls move between sections. Homepage order is defined in `front-page.php`; homepage-only presentation is in `assets/css/homepage.css`. The newsletter remains disabled with customer-facing “Email sign-up is coming soon.” copy. New Arrivals and Best Sellers keep native WooCommerce shortcode/product-card rendering; final product-card behavior belongs to STORE-005.

STORE-005 uses a reusable hook-first WooCommerce product-card system shared by homepage rails, shop/category/brand archives and related loops. Local-only `STORE-005 TEST` fixtures exercise primary/secondary media, SALE, SOLD OUT, variable pricing, equipment contain mode, single-image stability, placeholder media and long titles. The fixture seeder and generated uploads remain local runtime state and must not be committed or exported.

STORE-006 adds the shared Shop/category/brand archive framework through WooCommerce hooks. The archive header and approved category rail are theme-owned, while the native product grid, sorting and pagination remain WooCommerce-owned. Filters are GET-based and server-rendered, covering native brand/size/colour contracts plus category, price and in-stock availability. The filter drawer is progressive enhancement only: without JavaScript the GET form remains usable; JavaScript adds focus management, accordions, overlay coordination and comma-separated multi-select serialization. Local-only attributes and category assignments are seeded in the runtime for UAT and must not be exported.

The STORE-006 visual refinement uses a reusable retail-card presentation for archives and related products: brand text uses a light SwimShop blue, cards fill their WooCommerce grid tracks, titles use bold emphasis, prices use a 600-level weight, and image/text/row spacing is tuned for a compact PLP rhythm. Global `pa_colour` terms render up to five swatch items plus `+N`; variable cards expose `Preview …` buttons only for mapped variation images, while unmapped variable colours and simple multi-colour cards show non-interactive `Available in …` indicators. Homepage rails remain on their existing shared-card rules.

STORE-007 adds a hook-first product-page layer without copying WooCommerce templates. Native gallery markup remains compatible with variation images, zoom and lightbox; FlexSlider is disabled for the PDP so `product-page.css` can provide a desktop image grid, tablet stack and mobile scroll-snap gallery. The purchase summary keeps native brand, price, availability, quantity and variation behavior, with progressive `pa_colour`/`pa_size` button controls and `Add to bag` copy. The PDP uses native Product Details and configured Shipping & Returns accordions, an optional Customizer-managed Size Guide link, and no Material & Care section. Local-only fixture data and policy pages are documented in `LIVE-STATE.md` and are never packaged.

The current STORE-007 correction keeps WooCommerce's native `pa_colour`/`pa_size` rows as the progressive-enhancement fallback, collapses only their redundant enhanced labels/geometry, preserves the native reset link, and leaves unsupported variation attributes native.

STORE-008 adds product search through the existing SwimShop header form and advances the theme to version `0.8.0`. Ivory Search FREE is the Local-only matching/AJAX backend candidate; the theme retains ownership of the shell, overlay behavior, result styling and WooCommerce product-search archive. Verified support includes product-only title and partial-word search, category, native `product_brand`, native `pa_colour`, predictive AJAX and the actual result image/title/price/excerpt contract. Product results reuse the STORE-006 archive cards, filters, sorting, pagination and query preservation; filter/chip/sort/Clear all/pagination checks preserve `s`, `post_type=product` and selected parameters. The real Local-only SKU fixture `SSZ-STORE008-SKU-001` was used for a negative exact-SKU test and is not repository data. Do not commit the plugin, its index, Local database, uploads or fixture runtime state. Limitations observed in UAT are no typo correction, no exact SKU matching, broad short `m` matching and no exact medium-size result because the fixture has XS/S/M/L/XL terms but no `Medium` term.

STORE-009 advances the theme to version `0.9.0` and adds a WooCommerce-authoritative cart experience. The header bag remains a real `/cart/` link for no-JavaScript navigation while JavaScript opens a right-side drawer with live Woo fragments, Store API quantity/remove mutations, subtotal/count synchronization, exact Checkout/View cart/Continue shopping actions, focus trapping, Escape/backdrop close, scroll lock and one-open-overlay coordination. The full Cart page keeps the live Cart Block and native coupon/checkout controls, adding only branded shell styling, subtotal bridging and an empty-state sibling; the existing Product New rail is hidden on Cart pages. Normal and AJAX PDP adds open the drawer after success, and variable lines retain readable variation labels when WooCommerce supplies them. Sol accepted the documented Cart Block limitation: without JavaScript, the real header link, Cart page, checkout path and server-rendered empty state work, while filled quantity/remove controls are unsupported because the block requires hydration. Do not commit Local runtime cart sessions, database state, uploads or generated UAT artifacts.

STORE-011 advances the theme to version `0.11.0` and styles the native WooCommerce My Account surface without copying account templates or adding account JavaScript. The account menu removes Downloads semantically, labels the native customer-logout endpoint `Sign out`, and presents an exact `MY ACCOUNT` H1. `assets/css/account.css` provides the wide nav/content split, stacked tablet/mobile navigation, native login/lost-password/forms, responsive orders, addresses and account-detail presentation. Local-only populated/empty customer fixtures exercise order, address, account and Payment Methods states; credentials and runtime data are not packaged. STORE-010 checkout styling remains deferred and STORE-012 responsive polish has not started.

## Visual UAT

With the Local site running and the custom theme active, install the development dependency and run the exact-width browser harness:

```powershell
npm install
npm run visual-uat
```

The harness uses an installed Chrome or Edge executable, checks exact widths `360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920`, and writes ignored screenshots to `artifacts/uat/`. Set `SSZ_BROWSER_PATH` when the browser is not in a standard installation path. Set `SSZ_REQUIRE_LOCAL_MENU=1` to fail when the local WordPress Primary Navigation has no configured child menu for mega-menu/drilldown checks.

For archive UAT, the Local runtime must have WooCommerce active and Coming Soon disabled locally. The STORE-006 fixture catalog includes the native Arena, Speedo and Spurt brands, approved category hierarchy, `pa_size` and `pa_colour` terms, stock variation and popularity values. Pagination checks may temporarily set `woocommerce_catalog_rows=1`; restore the normal three-row setting before ending the session.

## Linting

Use Local's bundled PHP runtime when it is not on PATH:

```powershell
$php = 'C:\Users\stanm\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe'
Get-ChildItem theme\swimshop-zimbabwe -Filter *.php -Recurse | ForEach-Object { & $php -l $_.FullName }
```

## Packaging

Run `tools/package-theme.ps1` to create a ZIP whose only top-level directory is `swimshop-zimbabwe/`. Do not package the repository root, coordination documents, Local runtime, databases, uploads, caches, secrets or the source ZIP.

## Workflow

Read `PROJECT.md`, `AGENT-WORKFLOW.md`, `NOW.md`, `TASKS.md` and `UAT.md` before implementation work. The bootstrap may land on `main`; subsequent implementation work uses `luna/STORE-XXX-short-description` branches and is reviewed by Sol before merge.
