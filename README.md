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

## Visual UAT

With the Local site running and the custom theme active, install the development dependency and run the exact-width browser harness:

```powershell
npm install
npm run visual-uat
```

The harness uses an installed Chrome or Edge executable, checks exact widths `360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440, 1920`, and writes ignored screenshots to `artifacts/uat/`. Set `SSZ_BROWSER_PATH` when the browser is not in a standard installation path. Set `SSZ_REQUIRE_LOCAL_MENU=1` to fail when the local WordPress Primary Navigation has no configured child menu for mega-menu/drilldown checks.

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
