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

- Product media, title, price, sale state and native Product Brands display correctly.
- Empty SKU fields do not alter the UI.

## Shop/category

- WooCommerce archives use the theme shell and product grid at all required widths.
- Sorting, pagination and taxonomy links remain usable.

## Product page

- Native WooCommerce simple and variable products render.
- Variation selectors, gallery, price, add-to-cart and brand links remain usable.

## Search

- Header search opens and closes, accepts a query and reaches WordPress/WooCommerce search results.

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
