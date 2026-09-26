# Browser and Visual UAT Contract

Visual UAT means opening the running Local WordPress site and interacting with the rendered page. Code inspection alone is not visual UAT.

Required viewport checks: approximately 360, 390, 430, 768, 1024, 1280, 1440 and 1920 pixels wide.

## Global

- Page loads without PHP fatal errors or broken layout.
- Typography, palette, spacing and content width follow the design contract.
- Links and focus states are visible and usable.
- No unexpected horizontal overflow at required viewports.

## Header

- Announcement bar is readable and configurable.
- Branding renders correctly with and without a custom logo.
- Search and cart controls are visible and usable.

## Navigation

- WordPress Primary Navigation controls desktop links.
- Mobile menu opens, closes and exposes keyboard-accessible links.
- Current fallback navigation is only a new-install safety net.

## Homepage

- Hero, category grid, brands, product sections, campaign and proposition blocks render safely with empty/default content.
- Customizer content and desktop/mobile hero images render when configured.

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

