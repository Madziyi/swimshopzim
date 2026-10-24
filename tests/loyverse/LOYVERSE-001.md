# LOYVERSE-001 cursor correction — partial handoff

Base main: 7248311 (fetched 2026-10-24).
Branch: luna/LOYVERSE-001-inventory-sync-fixes.
Plugin: 0.1.2. Installed 0.1.1 source imported separately from the theme.

User reported mapping and manual sync failing on GET /variants with HTTP 400,
INVALID_CURSOR, on 2026-10-24. The displayed cursor ends in one equals sign.

Root cause demonstrated locally: add_query_arg receives raw query values.
WordPress removes one trailing equals sign from an unencoded padded cursor;
unencoded plus signs also become spaces when decoded by a server. Reproduced
with the installed WordPress functions, using a two-padding synthetic cursor.
The original API response cursor was not captured, so attributing the user's
exact request to the demonstrated corruption still requires a real API retest.

Fix: use http_build_query with PHP_QUERY_RFC3986 and preserve returned cursors
without trimming. Options, credentials, installed runtime and stock untouched.

PASS: PHP lint on all four plugin PHP files.
PASS: regression test using actual WordPress URL helpers demonstrates old padding
loss and verifies complete two-page reads for padded and special-character
cursors, plus loop rejection. Test uses synthetic responses and no credentials.

NOT VERIFIED: live stores/variants/inventory, selected-store filtering, activation,
token preservation in runtime, mapping, controlled stock updates, scheduling,
and storefront regression. Do not mark full LOYVERSE-001 accepted.

Inherited safety issues found during source inspection: no overlapping-run lock,
stock management is enabled automatically, raw quantities are cast to float and
then wc_stock_amount, selected store existence is not revalidated by sync.
Resolve before full sync acceptance. Automatic sync must remain disabled.

Run regression:

    php tests/loyverse/cursor-regression.php <WordPress-public-directory>

Installable ZIP is generated under dist with only swimshop-loyverse-sync/.
Tests and this handoff stay outside the plugin ZIP. No main merge performed.
