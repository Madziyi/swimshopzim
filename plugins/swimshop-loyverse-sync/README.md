# SwimShop Loyverse Sync

A standalone WordPress/WooCommerce plugin for one-way inventory synchronization from Loyverse POS to WooCommerce.

## Version 0.1.5 — size and colour grouping suggestions

The preview now suggests variable parent products by parsing recognizable size
and colour suffixes from separate Loyverse item names. Examples include
`Team T-shirt White M` and `Team T-shirt Navy L` under `Team T-shirt`. Variations
retain every original SKU, item ID and variant ID. Native Loyverse variable
items keep their source grouping and are never merged by title.

**Every name-based group needs review before import.** Only whitespace/case and
common size notation are normalized; no fuzzy name matching is used. Different
gender prefixes, model numbers, spelling and unknown colour descriptions remain
separate. Singletons and unparsed size-like suffixes are marked unresolved.
Duplicate variation options, inconsistent attribute dimensions, generic names
without a product model, and existing source blockers flag groups for review.
Different prices and mixed stock-tracking policies are reported explicitly.
All rows in a group with blockers appear in the blocked-only copied report.

Expand **View proposed parent products** to review groups and use the complete
copy report to inspect every member, its proposed size/colour and grouping
notes. Source-check counts are separate from grouping review; passing source
checks does not approve an import. Preview-only: no import/publish operation
is implemented. Parent proposal references are review aids, not authoritative
saved mappings. Availability policy for untracked/negative stock remains open.

Expand **All products** or **Blocked products only** beneath the preview summary
and click its Copy button. Reports contain all pages, source IDs, proposed
actions and blocking reasons. The read-only text boxes also support manual
selection and copying if clipboard access is unavailable. Reports are plain
tab-separated text suitable for pasting into a message or text editor.

Open WooCommerce → Loyverse Sync and click **Preview Loyverse Product Import
(read-only)**. This fetches all `/items` pages (including embedded variants),
validates the selected store and retrieves its inventory. It does not import,
publish, update, adopt or delete products and does not download media. Only a
30-minute admin-only preview cache is written. Results are paginated at 50 rows.

The source checks evaluate simple/variable Loyverse items before suggesting
cross-item grouping. Future imports must preserve `_ssz_loyverse_item_id` and
`_ssz_loyverse_variant_id` on each corresponding stock-bearing record. An approved
parent grouping map is still required. Existing WooCommerce SKU collisions and
already-linked IDs are blocked for review. Missing or ambiguous prices, options,
inventory and unsupported weight/composite items are reported. If any variant
is blocked, the whole item is blocked. No name-based matching takes place.

Prices use the selected store's settings with default pricing as fallback.
Amounts are shown without currency conversion: confirm the Loyverse account's
currency and tax treatment against WooCommerce before import. Image availability
is shown; categories, descriptions and brands are outside this initial preview.
No importer/publish action is included in this release. Keep automatic stock
sync disabled while reviewing the catalogue.

### Cursor correction

Version 0.1.2 preserves opaque pagination cursors and encodes query parameters
using RFC 3986. Previously unencoded values passed to WordPress `add_query_arg`
could lose trailing `=` padding or decode `+` as a space, producing
`INVALID_CURSOR` on subsequent pages. No saved option names change.

Keep automatic sync disabled. Verify API access and read-only mapping before
stock updates. This focused correction has automated pagination coverage; real
account pagination and controlled stock synchronization remain to be verified.

Known inherited limitations: sync currently enables stock management, converts
inventory values to floats and WooCommerce stock amounts, and has no concurrent
run lock. Full LOYVERSE-001 acceptance requires these safety issues to be resolved
and tested before enabling unattended synchronization.

- Loyverse personal access token is entered from WordPress admin; no code editing required.
- The token field is write-only in the UI. A saved token is never printed back into the page.
- `Save & Test Connection` validates the token and loads stores from Loyverse.
- One Loyverse store is selected as the WooCommerce inventory source.
- Products and variations are matched by exact SKU.
- **Test API Data Access (read-only)** checks `/variants` and `/inventory` independently and reports the Loyverse error code/details/field if one fails.
- Read-only SKU mapping check before changing inventory.
- Manual inventory sync button.
- Optional WP-Cron sync every 15 minutes, 30 minutes or hourly.
- Last-sync mapping/status report and a short sanitized log.
- One-way only: the plugin does **not** write inventory, orders, products or customers back to Loyverse.

## API resources used

Read-only requests to Loyverse API v1.0:

- `GET /stores`
- `GET /variants?limit=50`
- `GET /inventory?store_ids={store_id}&limit=50`

Paginated variant and inventory responses follow the Loyverse `cursor` field.

## Installation

1. Zip this directory so the top-level directory is `swimshop-loyverse-sync/`.
2. In WordPress: Plugins → Add Plugin → Upload Plugin.
3. Activate **SwimShop Loyverse Sync**.
4. Open WooCommerce → Loyverse Sync.
5. Paste a Loyverse personal access token.
6. Select **Save & Test Connection**.
7. Choose the inventory store and save.
8. Click **Test API Data Access (read-only)**. Both endpoints should show PASS.
9. Run **Check SKU Mapping**, then **Sync Inventory Now** after reviewing results.
10. Keep automatic sync disabled until successful manual validation.

## Matching rules

- Exact SKU match only.
- Variable product parents are skipped; their individual variations are matched by SKU.
- Grouped and external products are skipped.
- Duplicate SKUs on either side are skipped rather than guessed.
- WooCommerce products/variations without SKUs are reported and skipped.

## Diagnostics and HTTP 400 errors

Version 0.1.0 ignored Loyverse's documented `errors[]` structure. Version 0.1.1 displays the failing endpoint and a safely truncated `code`, `field`, and `details` from that structure. The read-only diagnostics use only one record per endpoint and do not change stock. An HTTP 400 must be investigated using the displayed error; success cannot be guaranteed without the actual Loyverse response.

Paging now fails closed if Loyverse returns a malformed response, repeated cursor, or the 100-page safety limit is reached, instead of silently syncing incomplete results.

## Important behavior

When a SKU matches and an inventory level exists for the chosen Loyverse store, WooCommerce stock management is enabled for that product/variation and its stock quantity/status is set to the Loyverse value.

The plugin does not alter prices, product titles, images, descriptions, orders, customers or Loyverse data.

## Security

Loyverse personal access tokens are powerful credentials. WordPress must be served over HTTPS in production. The plugin does not place the token in logs, URLs or rendered admin HTML after it has been saved.

As with most WordPress plugins using API credentials, the token is stored in the WordPress options table. Protect database and administrator access accordingly.

## Scheduling

Automatic synchronization uses WP-Cron. WP-Cron is traffic-driven and is not an exact system scheduler. A real server cron can trigger `wp-cron.php` later if stricter timing is required.

## Future scope

Not included in 0.1.0:

- WooCommerce → Loyverse order/inventory writes.
- Loyverse webhooks.
- OAuth application authorization.
- Price synchronization.
- Product creation/update synchronization.
