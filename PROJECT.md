# SwimShop Zimbabwe Project Contract

## Purpose

Production-ready ecommerce storefront for a Zimbabwe swimming and aquatic-sports retailer.

## Architecture and stack

- WordPress classic/hybrid theme in PHP, HTML, CSS and small amounts of vanilla JavaScript.
- WooCommerce remains the commerce engine.
- Local WP is the authoritative local development runtime.
- GitHub is the source and coordination system; Hostinger will provide staging and production.
- The installable theme is only `theme/swimshop-zimbabwe/`.

## Responsibility boundaries

WooCommerce owns products, prices, sale prices, inventory, stock status, variations, native SKUs, carts, checkout, accounts, orders, refunds, coupons, shipping, taxes and order statuses.

The custom theme owns storefront presentation, layout, navigation presentation, content presentation, responsive behavior and accessible interaction patterns. It must not create parallel commerce records or checkout systems.

## Taxonomy and brand strategy

Product categories represent shopping intent such as Men, Women, Kids, Equipment/Accessories, Racing, Training, Open Water, New Arrivals, Best Sellers and Sale. The exact catalog is configured in WordPress rather than hardcoded into the theme.

Brands use WooCommerce's native `product_brand` taxonomy. Arena, Spurt, Speedo and future brands are not duplicate product categories and no second custom brand taxonomy should be introduced without an explicit architecture change.

## SKU strategy

The business currently does not use SKUs. Native WooCommerce SKU fields may remain blank and remain authoritative when SKUs are introduced later. The theme must not create a custom SKU system.

## Design reference

Arena (`https://www.arenasport.com/en_row/`) is a UX and visual reference only. Do not copy Arena logos, trademarks, proprietary copy, photography or campaign graphics.

The approved direction is premium, athletic, modern, clean, international, minimal, image-led and trustworthy, using SwimShop Zimbabwe's white, black and navy palette.

## Security rules

- Never commit passwords, payment secrets, salts, API keys, credentials or private customer data.
- Do not commit WordPress core, WooCommerce core, Local configuration, databases, caches or uploads.
- Document secret names only; keep values in environment/runtime configuration.
- Production data must never be replaced by Local or staging database exports once real orders exist.

## Hosting model

Development flows from Local WP to GitHub to Hostinger staging and then production. Code, database, media, environment configuration and secrets are separate concerns. Production WooCommerce data becomes authoritative after launch.

## Permanent constraints

- Do not convert the storefront to React/Next.js.
- Prefer WordPress menus, WooCommerce hooks and native taxonomies over unnecessary hardcoding.
- Preserve WooCommerce compatibility by using hooks and filters before copying templates.
- Do not merge implementation branches into `main` without acceptance.

