# Catalogue preview 0.1.3

User changed scope: Loyverse is the catalogue source, existing website catalogue
is unrelated, creation and publication are authorized. Current request is only
a read-only preview. No importer is implemented yet.

The previous user test confirms /variants and /inventory PASS and full mapping
of 989 Loyverse SKUs completes without INVALID_CURSOR. Zero SKU matches are
expected for the unrelated WooCommerce catalogue.

Added paginated /items fetch, a separate read-only catalogue planner, protected
admin preview action, 30-minute per-user cache and a 50-row paginated report.
Selected-store price/availability and inventory are evaluated. Simple/variable
grouping, future source-ID identity, existing SKU conflicts, missing data,
duplicate options and unsupported quantity/item policies are surfaced.
One blocked variant blocks the whole parent. No product/media writes occur.

PASS: planner regression fixtures (including zero stock/price, leading-zero SKUs,
grouped variants, missing/invalid/fractional/negative inventory, duplicate source
SKUs/options, existing IDs/SKUs and selected-store availability).
PASS: prior cursor regression using Local WordPress URL helpers.
PASS: PHP syntax checks.

Live preview and visual admin UAT remain unverified: the in-app browser reaches
the WordPress login page and has no authenticated session. Installed plugin was
not overwritten and saved settings were not changed. User can upload 0.1.3 and
run Preview Loyverse Product Import (read-only) with their saved connection.

Currency and tax treatment require review before a real import. Images are
flagged only. Categories/brands/descriptions are outside this first preview.
Existing inventory sync safety limitations remain documented in README.
Do not enable automatic sync or claim full original LOYVERSE-001 acceptance.

Package: dist/swimshop-loyverse-sync-0.1.3.zip; plugin folder only, no fixtures.
Branch: luna/LOYVERSE-001-inventory-sync-fixes. No main merge.
