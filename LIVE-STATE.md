# Live State

## LOCAL

- Site domain: `http://swimshop-zimbabwe.local/`
- Site filesystem: `C:\Users\stanm\Local Sites\swimshop-zimbabwe\app\public`
- Web server: nginx (environment fact supplied for this Local site)
- PHP: 8.2.29 (Local bundled runtime)
- Database: MySQL 8.4.0 (environment fact supplied for this Local site)
- WordPress: 7.1.2 (`wp-includes/version.php` verified)
- Multisite: No (environment fact supplied for this Local site)
- Theme directory: `wp-content/themes/swimshop-zimbabwe/` exists and matches the imported source
- Active theme: Yes — browser rendered the SwimShop Zimbabwe homepage and custom `ssz-*` storefront shell
- WooCommerce: Yes — WooCommerce 11.1.2 is present, and the live theme resolves WooCommerce cart/account/shop URLs
- Browser opened: Yes — homepage, search results and footer loaded at the local domain
- STORE-004 runtime check: the synced homepage renders with all ten sections, no horizontal overflow, no broken images and no console/page errors at the required browser widths. Local brand terms currently have no uploaded term logos, so the homepage exercises the accessible text fallback.
- STORE-005 local fixture catalog: eight clearly named `STORE-005 TEST` products exist in the Local database only, using Arena, Speedo and Spurt native `product_brand` terms. The fixtures cover simple/in-stock with gallery, sale, variable price range, out of stock, equipment contain media, single-image, no-image placeholder and long-title states. Generated PNGs live under `wp-content/uploads/ssz-store-005-fixtures/`; neither database rows nor uploads are repository/export artifacts.
- Local WooCommerce Coming Soon mode is disabled for automated shop/archive UAT so anonymous Playwright can exercise the fixture catalog. This is Local-only runtime state and is not a theme or production setting.
- STORE-006 Local archive fixtures add global attributes `pa_size` (XS, S, M, L, XL) and `pa_colour` (Black, Navy, Blue, Red, Green, Yellow, Purple), approved Men/Women/Kids/Goggles/Equipment category hierarchy with child categories, and distinct prices, stock states and `total_sales` values across the eight existing `STORE-005 TEST` products. The variable training-suit fixture has seven assigned colours with Black/Navy/Blue variation-image mappings and five visible swatches plus `+2`; Local-only `STORE-006 TEST All Mapped Colour Suit` covers three mapped colours, while `STORE-006 TEST Mixed Colour Suit` covers Navy mapped and Red available-only. Simple multi-colour and single-colour fixtures cover indicator-only and omitted-row states. These assignments are Local database state only.
- STORE-006 archive UAT temporarily set `woocommerce_catalog_rows=1` and related per-page test values to exercise two-page pagination, then restored `woocommerce_catalog_rows=3`, `posts_per_page=10` and the absent `woocommerce_loop_shop_per_page` override. No runtime fixture script, database export or upload is part of the repository.

## HOSTINGER STAGING

NOT DEPLOYED / NOT VERIFIED.

## HOSTINGER PRODUCTION

NOT DEPLOYED / NOT VERIFIED.
