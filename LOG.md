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
