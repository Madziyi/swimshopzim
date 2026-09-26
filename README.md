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

## Visual UAT

With the Local site running and the custom theme active, install the development dependency and run the exact-width browser harness:

```powershell
npm install
npm run visual-uat
```

The harness uses an installed Chrome or Edge executable, checks widths from 360px through 1920px, and writes ignored screenshots to `artifacts/uat/`. Set `SSZ_BROWSER_PATH` when the browser is not in a standard installation path.

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
