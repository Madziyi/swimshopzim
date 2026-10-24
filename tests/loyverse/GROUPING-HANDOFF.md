# Grouping preview 0.1.5

User approved building a read-only grouping preview. Eventual product publication
is authorized; importing, saving grouping decisions and publishing are not part
of this release. Existing WooCommerce products/settings were not changed.

Added SSZ_Loyverse_Grouping, a pure suggestion layer after the existing source
checks. It parses size/colour suffixes, handles both suffix orders, and groups
only exact remaining names with normalized whitespace/case. Native source
variations remain within their original item. Each source SKU, item ID and
variant ID remains present. No fuzzy matching, source-ID replacement or product
writes occur. Suggestions are never treated as approved import mappings.

The admin preview adds parent summaries and per-row parent/options/review notes.
Full copied reports include all grouping fields. Blocked-only reports include
all members of groups blocked by duplicate options, missing dimensions, generic
family names or source data issues. Price differences are warnings requiring
confirmation; variation prices are not unified. Source-check readiness is
explicitly separated from grouping approval.

Verification:

- Grouping fixtures pass: suffix order, paired/age/junior sizes, preserved model
  and gender boundaries, native variants, no fuzzy typo correction, duplicate
  options, missing dimensions, generic family review, prices and source blockers.
- Existing catalogue and cursor regressions pass; PHP and JS syntax checks pass.
- User-export replay: all 989 rows retained; 127 proposed variable parents cover
  776 rows. 213 rows remain separate, including 153 unresolved names/singletons.
  17 groups have blockers; 13 groups have differing prices to confirm.
- Duplicate Sharon School Girls 3XS rows (SKUs 10080 and 10076) block that parent.
- Runtime admin/clipboard UAT remains unverified (no logged-in browser session).
  File-based browser inspection of the offline HTML was blocked by browser URL
  policy; no alternative browser route was attempted.

The replay helper accepts a copied report and writes local-only HTML, text and
JSON reports under ignored artifacts/uat/. Actual catalogue fixtures and source
IDs are not committed. Generated ZIP includes only the plugin directory.

Package: dist/swimshop-loyverse-sync-0.1.5.zip.
Branch: luna/LOYVERSE-001-inventory-sync-fixes. No main merge.
