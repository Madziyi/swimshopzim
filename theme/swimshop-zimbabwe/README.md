# SwimShop Zimbabwe Theme

Version: 0.11.0 (STORE-011 customer account styling)

A custom WooCommerce theme for SwimShop Zimbabwe. The storefront is designed to support a premium aquatic-sports retail experience while leaving commerce operations to WooCommerce.

## Requirements

- WordPress 6.5+
- PHP 8.0+
- WooCommerce (current stable release recommended)
- HTTPS for production checkout

## Install

1. In WordPress Admin, go to **Appearance → Themes → Add New → Upload Theme**.
2. Upload `swimshop-zimbabwe.zip`.
3. Activate **SwimShop Zimbabwe**.
4. Install and activate WooCommerce.
5. Run the WooCommerce setup wizard.

The ZIP is intentionally packaged with `swimshop-zimbabwe/` as the theme root. Do not place the folder inside another wrapper folder before zipping it.

## Initial configuration

### 1. Homepage

- Create a page called **Home**.
- Go to **Settings → Reading** and select a static homepage.
- Choose **Home** as the homepage.
- Go to **Appearance → Customize → SwimShop Homepage**.
- Add the announcement text, hero desktop image, optional mobile hero image, heading, supporting copy and CTA.

### 2. Product categories

Create the catalog under **Products → Categories**. Recommended top-level structure:

- Men
- Women
- Kids
- Equipment

Useful merchandising categories may include:

- New Arrivals
- Best Sellers
- Racing
- Training
- Open Water
- Sale

The homepage preferentially looks for slugs `men`, `women`, `kids`, `goggles`, and `equipment`, then falls back to existing top-level product categories.

### 3. Brands

SwimShop Zimbabwe uses WooCommerce's native **Product Brands** taxonomy. Do not create duplicate product categories for Arena, Spurt, Speedo, etc.

Go to **Products → Brands** and add brands such as:

- Arena
- Spurt
- Speedo

Each brand can have its own description and image. Assign the appropriate brand when editing a product. The theme displays a Shop by Brand section on the homepage and brand names on product cards/product pages.

If **Products → Brands** is unavailable, update WooCommerce and verify its Product Brands feature is enabled. The theme intentionally does not register a competing custom taxonomy.

### 4. Attributes and variations

Under **Products → Attributes**, create reusable attributes such as:

- Size
- Color
- Gender
- Usage / Sport (only if this is better represented as an attribute than a category in the final catalog)

Use native WooCommerce variable products for product-specific size/color inventory.

### 5. SKUs

SKUs are optional. The store can launch with blank SKU fields. When the business later adopts SKUs, use the native WooCommerce SKU field on each product or variation. No theme migration is required.

### 6. Navigation

Go to **Appearance → Menus** and create a Primary Navigation menu. Suggested top level:

- Men
- Women
- Kids
- Equipment / Accessories
- Brands
- New Arrivals
- Sale

Add Product Category and Product Brand archive links as menu items. The theme supports up to three menu levels. A richer full-width mega-menu treatment is planned for STORE-003.

## Placeholder content

This build intentionally includes neutral gradient placeholders rather than Arena imagery or campaign assets. Replace them with SwimShop Zimbabwe-owned/licensed photography.

## STORE-001 scope

Included:

- Valid WordPress classic/hybrid theme structure
- WooCommerce support declaration
- Responsive theme shell
- Announcement bar
- Desktop/mobile navigation foundation
- Product-first search form
- Responsive homepage foundation
- Native WooCommerce Product Brands integration
- WooCommerce product-grid/PDP base styling
- Cart count fragment integration
- Accessible focus states and skip link
- `theme.json` palette/typography foundation

Not yet complete:

- Full Arena-style mega menu (STORE-003)
- Configurable secondary homepage campaign blocks (STORE-004)
- Secondary hover product image and detailed card states (STORE-005)
- Filter drawer and advanced archive controls (STORE-006)
- Final product variation UI / size guide / recently viewed (STORE-007)
- AJAX predictive search (STORE-008)
- Cart drawer (STORE-009)
- Checkout styling (STORE-010, deferred)
- Full accessibility/performance audits (STORE-013/014)

Completed account styling (STORE-011) keeps the native WooCommerce My Account shortcode and endpoints, removes Downloads semantically for this physical-product storefront, labels the native logout action `Sign out`, and provides responsive account navigation, orders, addresses, login and account-detail presentation. Local fixture users and runtime data are not part of the theme package.

## Validation checklist for this build

1. Install ZIP through WordPress Admin.
2. Activate with WooCommerce disabled: no fatal errors should occur.
3. Activate WooCommerce: Shop/Product pages should render with theme styling.
4. Confirm **Products → Brands** exists.
5. Create Arena, Spurt and Speedo brands.
6. Create one simple product with no SKU and one variable product with sizes/colors.
7. Assign brands to both products.
8. Confirm brand names display on product cards and the product page.
9. Create product categories and assign the Primary Navigation menu.
10. Test header/mobile navigation at 360, 390, 430, 768, 1024, 1280 and 1440 widths.
11. Add a product to the cart and confirm the Bag count updates.
12. Configure the homepage hero in the Customizer and verify desktop/mobile image behavior.

## Development approach

WooCommerce templates are not copied into this theme unless an override becomes necessary. The theme uses hooks and filters first to reduce compatibility risk during WooCommerce updates.
