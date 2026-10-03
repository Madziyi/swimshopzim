# Browser and Visual UAT Contract

Visual UAT means opening the running Local WordPress site and interacting with the rendered page. Code inspection alone is not visual UAT.

Required viewport checks: exact 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920 pixels wide.

## Global

- Page loads without PHP fatal errors or broken layout.
- Typography, palette, spacing and content width follow the design contract.
- Links and focus states are visible and usable.
- No unexpected horizontal overflow at required viewports.

## Header

- Announcement bar is readable and configurable.
- Branding renders correctly with and without a custom logo.
- Search and cart controls are visible and usable.
- The announcement bar is non-sticky while the main header remains sticky.
- The default lockup uses the supplied transparent color logo plus the SWIMSHOP ZIMBABWE wordmark; custom-logo output remains supported.
- Desktop is active above 1120px and exposes the WordPress Primary Navigation with MEN, WOMEN, KIDS, EQUIPMENT, BRANDS, NEW ARRIVALS and SALE top-level entries.
- Mobile is active through 1120px and exposes menu, search and bag controls; the drawer supports account access, drilldown, accordion groups, Escape/backdrop/button close, focus restoration and body scroll lock.

## Navigation

- WordPress Primary Navigation controls desktop links.
- Desktop child items render as a full-width mega menu with depth-1 group headings and depth-2 links.
- Desktop top-level mega-menu state is exclusive: entering or activating another configured item clears the previous `.is-open` state and synchronizes `aria-expanded`.
- Search and desktop mega-menu state are mutually exclusive in both directions.
- Mobile menu opens, closes and exposes keyboard-accessible links.
- Mobile child groups expose drilldown and accordion state through `aria-expanded` and `aria-controls`.
- The rendered document contains no duplicate non-empty DOM IDs when desktop and mobile copies of the WordPress menu are both present.
- Current fallback navigation is only a new-install safety net.

For fresh-install fallback verification, use a disposable Local database or a temporary transaction that unassigns the Primary Navigation, render the exact viewport matrix, confirm the desktop fallback uses `ssz-primary-menu` and the mobile fallback uses `ssz-mobile-menu`, then restore the configured menu before ending the session. The automated harness also performs a safe source-level fallback contract check without altering the configured Local menu.

## Homepage

- Hero, category grid, brands, product sections, campaign and proposition blocks render safely with empty/default content.
- Customizer content and desktop/mobile hero images render when configured.
- Homepage order is Hero, Shop by Category, New Arrivals, Shop the Brands, Shop by Activity, Performance Campaign, Best Sellers, Race Day / Training Equipment, Why SwimShop Zimbabwe, Newsletter, then Footer.
- Hero exposes one H1, desktop/mobile media controls, two CTA links and left/center content alignment; the primary hero image is the only homepage image with high fetch priority.
- Categories use configurable top-level WooCommerce category selectors, with preferred-slug/catalog fallback and branded media fallback when thumbnails are absent.
- Brands use the native `product_brand` taxonomy, term logo URLs when present, accessible brand-name text fallback otherwise, native term links and the canonical View All Brands link.
- Shop by Activity contains Racing, Training and Open Water editorial links with one prominent title per card; campaign and Race Day / Training Equipment panels remain link-based and use intentional fallback treatments without placeholder labels.
- Performance Campaign establishes an isolated stacking context with media at layer 0, one image wash at layer 1 and content at layer 2. Real-media verification must exercise desktop/mobile campaign sources and preserve CTA contrast/clickability; fallback media must remain intentional.
- Homepage Customizer settings live under the `SwimShop Homepage` panel with separate Hero, Categories, Shop by Activity, Performance Campaign, Product Sections, Race Day / Training, Store Proposition and Newsletter sections. Existing `ssz_*` theme-mod IDs remain unchanged.
- New Arrivals uses newest/date ordering and Best Sellers uses WooCommerce popularity ordering. Product-card polish remains STORE-005 scope; mobile rails use native CSS scroll snap only.
- Newsletter presentation is intentionally disabled and uses customer-facing “Email sign-up is coming soon.” copy by default. It does not submit data or claim subscription success.
- Homepage UAT asserts section presence/order, one H1, two hero CTAs, category/brand/activity/feature links, unique activity titles, campaign layer contract, brand image loading, no developer-facing newsletter wording, no forbidden implementation labels, no broken images, no overflow and no runtime errors.

## Product cards

- Product cards use the native WooCommerce product link/title/price structure with a theme-owned 4:5 media wrapper.
- The hierarchy is media, brand text, product title and native WooCommerce price; cards do not require a surrounding white box or large shadow.
- Featured media uses responsive WordPress image markup and a safe WooCommerce placeholder when no featured image exists.
- Cover cards may use the hard-cropped `ssz-product-card` derivative; contain cards must use an uncropped responsive WordPress source such as `large` for both primary and first-gallery images, preserving the complete object inside the 4:5 frame.
- The first gallery image is the only alternate image. Fine-pointer hover swaps it with a 200ms transition; touch devices do not depend on hover, and reduced motion removes the transition.
- Apparel defaults to `ssz-product-card--cover`; equipment/accessory category families use `ssz-product-card--contain`. The `ssz_product_card_media_fit` filter can override the fit.
- The only visible card badges are `SALE` and `SOLD OUT`; sold-out products do not also show `SALE`.
- Product Brands remain native `product_brand` terms and display as first-brand text, not logos or nested links.
- Titles remain semantic and are visually bounded to approximately two lines; native WooCommerce price HTML supports normal, sale, variable and currency-configured output.
- Loop cards contain no ratings, add-to-cart/quick-add controls, wishlist or quick-view controls, and sold-out cards remain clickable. Archive and related retail cards may expose accessible `pa_colour` preview buttons outside the product link.
- The main single-product wrapper never receives `ssz-product-card`, `ssz-product-card--cover` or `ssz-product-card--contain`; related and other normal WooCommerce product loops continue to receive the card classes.
- Empty SKU fields do not alter the UI.
- STORE-005 automated card UAT covers homepage New Arrivals/Best Sellers, shop/archive grids and a fixture PDP at all ten required widths, including card contract, hover, uncropped contain sources, badges, media fit, placeholder, long title, PDP root scope, related-loop classes, no nested anchors, no overflow and no runtime errors. Local browser verification also covers the Arena, Speedo and Spurt brand archives.

## Shop/category pages — STORE-006

- WooCommerce archives use the theme shell and native loop through hooks for Shop, `product_cat` and native `product_brand` contexts; no archive template override, AJAX or Product Collection/FSE implementation is used.
- Archive headers expose title, optional description, brand identity fallback and a context-aware category rail. Shop and brand pages show approved Men, Women, Kids, Goggles and Equipment links; category pages show direct children or siblings.
- The toolbar exposes result count, native sorting relabelled as Featured, Popularity, Newest, Price: Low to High and Price: High to Low, plus the filter trigger. Native pagination remains active.
- The GET filter contract covers category, native `filter_product_brand`, native `filter_size`/`query_type_size`, native `filter_colour`/`query_type_colour`, `min_price`, `max_price` and in-stock availability. Multi-select values serialize as comma-separated query values; active chips, remove links and Clear all preserve archive context and sorting.
- The accessible drawer supports accordions, Escape, backdrop/button close, focus restoration, focus trapping and body scroll lock. Search and navigation close events prevent stacked overlays.
- Local-only fixtures provide native Arena, Speedo and Spurt brands, approved category hierarchy, `pa_size` XS/S/M/L/XL, `pa_colour` Black/Navy/Blue/Red, distinct prices, stock states and distinct `total_sales` values. STORE-006 also uses dedicated all-mapped and mixed-variable products to verify mapped previews versus unmapped indicators. No fixture rows, uploads or database exports are committed.
- Browser UAT passed at exact widths 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px for no overflow, no broken images, archive structure, 2/3/4 grid columns, drawer focus/scroll/Escape/accordion behavior, sorting, category/brand contexts, multi-filter submit, active chips, empty state and no console/page errors. A focused Local pagination pass with temporary `woocommerce_catalog_rows=1` rendered four products on pages 1 and 2, then restored the normal three-row setting.
- Visual refinement keeps the existing media/card architecture while applying archive-only presentation rules: a light SwimShop blue brand line, bold title weight, 600-level price weight, 4:5 image-to-copy breathing room, compact brand/title/price spacing and responsive retail row gaps. At 360/390/430px, automated geometry checks require two columns, media/card ratio at least 0.97, aligned row media, a 7–16px column gutter, no overflow and a price-to-next-media gap under 50px. Fine-pointer hover remains required at desktop widths; touch widths preserve the non-hover contract.
- Related-product UAT verifies the shared retail presentation, `pa_colour` swatch rendering, previewable-versus-available-only semantics, group naming, no nested interactive elements and safe interaction without navigation. Shop UAT covers all-mapped buttons, mixed-variable Navy preview with Red indicator safety, simple indicator-only cards, selected state, unchanged card dimensions, hover suppression after selection, single-colour omission, five-swatch maximum and `+N` overflow. Homepage cards remain on the existing presentation and do not receive retail swatches.

## Shop/category

- WooCommerce archives use the theme shell and product grid at all required widths.
- Sorting, pagination and taxonomy links remain usable.

## Product page

- STORE-007 uses a hook-first PDP layer in `inc/woocommerce/product-page.php`, `assets/css/product-page.css` and `assets/js/product-page.js`; no WooCommerce product template override or third-party gallery library is used.
- WooCommerce's native gallery image nodes and variation-image contract remain intact. FlexSlider support is disabled on product pages so the native nodes can use a desktop grid, tablet one-column presentation and mobile CSS scroll-snap; native zoom/lightbox remain enabled and were verified.
- Desktop uses an image-led gallery with a sticky purchase summary. Mobile uses normal-flow purchase content; the gallery scrolls horizontally with snap and the page itself has no horizontal overflow.
- The summary hierarchy is native brand archive link, restrained H1, native WooCommerce price HTML, real `pa_colour` swatch buttons, real `pa_size` buttons, optional Size guide link, WooCommerce availability, native quantity input, Add to Bag and native details accordions.
- `product-page.js` progressively enhances only colour/size controls, mirroring the native Woo selects. Native selects remain in the DOM and remain usable when theme JavaScript is unavailable; unavailable combinations mirror WooCommerce's disabled option state.
- After enhancement, only the native `pa_colour` and `pa_size` rows receive stable enhanced-row classes: native labels disappear, rows without a WooCommerce reset link collapse, the reset-containing row preserves its native reset link, Woo minimum select dimensions are neutralized, and unsupported variation attributes remain native and visible. UAT asserts one visible Colour label, one visible Size label, no duplicate native labels or meaningful blank row geometry, and reset clearing of custom state.
- A dedicated JavaScript-disabled PDP pass verifies native Colour/Size labels and selects remain visible while custom controls remain hidden, preserving the progressive-enhancement fallback.
- Variable PDP price uses `variation.price_html` after a valid variation and restores the native parent range on reset. Variation image changes remain WooCommerce-owned.
- Product Details uses `<details>/<summary>` and the long description, falling back to the short description only when long description is empty. Shipping & Returns renders only the page selected in the `SwimShop Product Page` Customizer section. No theme-created Material & Care section exists.
- Local-only PDP fixture coverage includes simple/in-stock, sale, out-of-stock, no-image, single-image, multi-gallery and a hidden variable fixture with Black S/M/L, Navy M/L/XL and Blue S/M combinations. Temporary Size Guide and Shipping & Returns pages were assigned during UAT and remain runtime-only.
- Automated PDP UAT passed HTTP success, one H1, breadcrumb, gallery, summary, brand, price, Add to Bag, related retail cards, no overflow, no console errors and no page errors at 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. It also passed keyboard variation selection, disabled-state recalculation, dynamic price, variation image, reset, native accordions and configured/unconfigured page-setting behavior.
- Local browser checks confirmed simple and valid variable products add through existing WooCommerce cart behavior, with cart count fragments updating; out-of-stock products expose no Add to Bag control.

## Search

- Ivory Search FREE v5.5.18 is the Local-only backend candidate for the existing header form. Verified support covers product-only title search, partial-word matching, category matching, native `product_brand` matching, native `pa_colour` matching, predictive AJAX suggestions, submitted product-only results and the real Ivory image/title/price/excerpt markup. The theme owns the shell, focus/overlay behavior, result styling and archive presentation; Ivory owns matching, AJAX and indexing.
- Required matrix passed in the Local fixture catalog:

  | Query | Observed result | Status |
  | --- | --- | --- |
  | `Variable Training Suit` | One matching product | PASS |
  | `train` | Six product suggestions plus More Results | PASS |
  | `goggles` | One Goggles-category product | PASS — category matching |
  | Enter/submit `train` | `?s=train&post_type=product` product archive | PASS |
  | `Sample Page` | No results | PASS — pages excluded |
  | `arena` | Six Arena products | PASS — native `product_brand` matching |
  | `navy` | Six Navy-assigned products | PASS — native `pa_colour` matching |
  | `XL` | Three products | PASS — distinct size term check |
  | `medium` | No result | LIMITATION — no `Medium` term exists in the fixture; actual terms are XS/S/M/L/XL |
  | `m` | Broad six-product result set | LIMITATION — partial matching; not an exact size assertion |
  | `trainng` | No results | LIMITATION — no typo correction |
  | `SSZ-STORE008-SKU-001` | No results after assigning the real Local-only SKU | LIMITATION — exact SKU matching is not supported by this free configuration |

- Search result pages reuse the STORE-006 archive structure. Automated query-preservation checks pass for filter submission, active-chip removal, native sorting, Clear all and pagination while retaining `s=train`, `post_type=product` and selected parameters. Empty price fields are omitted from the filter GET request so active chips render reliably. No-results pages show the query, a visible search-again form and Shop all fallback; the generic `search.php` fallback also remains product-only.
- Predictive UAT requires a real `.is-ajax-search-post.is-product .thumbnail img` element that is complete and has `naturalWidth` and `naturalHeight` greater than zero; the Local run passed all four checks.
- Browser UAT passed at exact widths 360, 390, 430, 768, 1024, 1120, 1121, 1280, 1440 and 1920px. The full regression run verified header search open/close/focus restoration, navigation/filter overlay coordination, AJAX product container/title/image/price/excerpt selectors, no horizontal overflow and zero console/page errors.

## Cart

- Cart page renders and the header count updates after adding an item.

## Checkout

- Checkout fields and order summary remain readable and usable on mobile and desktop.

## Account

- Login, registration, account navigation and order views remain readable and usable.

## Accessibility

- Skip link works.
- Keyboard focus is visible.
- Images have appropriate alternative text.
- Reduced-motion preferences are respected.
- Controls expose meaningful labels and expanded/collapsed state.
- Inline SVG is used for header/menu/search/account/bag/chevron controls; remote icon fonts are not required.
