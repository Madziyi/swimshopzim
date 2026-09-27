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

## HOSTINGER STAGING

NOT DEPLOYED / NOT VERIFIED.

## HOSTINGER PRODUCTION

NOT DEPLOYED / NOT VERIFIED.
