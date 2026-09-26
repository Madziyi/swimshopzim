# Design System Contract

STORE-002 establishes reusable presentation primitives for the storefront. Later milestones may extend these primitives, but should not introduce a competing token vocabulary without an explicit decision.

## Brand and visual direction

**APPROVED:** SwimShop Zimbabwe; premium athletic retail; clean, minimal, image-led composition; large whitespace; restrained colour; trustworthy international character.

**APPROVED:** White, black and navy blue are the core palette. Neutral greys support surfaces, borders, muted text, disabled controls and placeholders. Avoid unnecessary accent colours.

Arena remains a hierarchy and restraint reference only. Arena assets, logos, trademarks, copy, campaign imagery and proprietary graphics are not used.

## Colour tokens

The CSS source of truth is `theme/swimshop-zimbabwe/assets/css/main.css`:

- `--ssz-color-white`, `--ssz-color-black`, `--ssz-color-navy`
- `--ssz-color-neutral-050` through `--ssz-color-neutral-700` (small neutral range)
- Semantic aliases: `--ssz-color-text`, `--ssz-color-text-muted`, `--ssz-color-background`, `--ssz-color-surface`, `--ssz-color-surface-muted`, `--ssz-color-border`, `--ssz-color-border-strong`, `--ssz-color-brand`, and `--ssz-color-focus`

Semantic usage is text on the black token, surfaces on white, muted surfaces on neutral-050, borders on neutral-200/300, and navy reserved for brand, focus, announcements and justified emphasis.

## Typography

**APPROVED:** A performant system fallback stack led by Inter: `Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`. No remote font or commercial font dependency is loaded.

**APPROVED:** Body, helper, label, navigation, product, page-title, section-title and hero-display sizes are tokenized. Headings use a tight line height and restrained negative tracking; labels and navigation use uppercase tracking for an athletic retail tone. Responsive display sizes use `clamp()`.

Font weights are limited to regular, medium, semibold, bold and heavy. The product name, price, section heading and hero display tokens are available to custom components and WooCommerce-compatible styling.

## Spacing and layout

**APPROVED:** The shared spacing scale is 2XS, XS, SM, MD, LG, XL, 2XL, 3XL and 4XL. Component gaps, form padding, section rhythm and page gutters use this scale or a responsive value derived from it.

**APPROVED:** Wide content is capped at approximately 1440px; reading content is capped at 760px. Mobile page gutters are 16px, increasing progressively with viewport width through `--ssz-page-gutter`. The shared grid gap is responsive and section rhythm uses `--ssz-section-space`.

## Buttons and forms

**APPROVED:** Buttons have a roughly 48px minimum touch height, restrained square geometry and primary, secondary, brand and light variants. Primary buttons are black with white text; secondary buttons are transparent with a black border; navy is reserved for the brand variant and justified emphasis. Hover, focus-visible, disabled and loading-ready states are defined without page-specific behavior.

**APPROVED:** Text, email, password, search, number, textarea, select, checkbox, radio, labels, helper text, error and disabled states have shared foundations. Native controls remain native and WooCommerce form rows inherit compatible dimensions and focus behavior.

## Product media

**APPROVED:** Product-card media uses a 4:5 ratio through `--ssz-product-media-ratio`. The default fit is cover for apparel-led imagery; the reusable `ssz-media-frame--contain` modifier and `--ssz-product-media-fit` contract allow equipment imagery to avoid harmful cropping. Final product-card markup and states remain STORE-005 scope.

## Shape, motion and stacking

**APPROVED:** Radius tokens are none, 2px, 4px and pill. Most storefront surfaces use no radius; this is not a rounded-card SaaS system.

**APPROVED:** Motion tokens are fast 150ms, normal 200ms and slow 250ms with a shared easing curve. `prefers-reduced-motion: reduce` disables nonessential transitions and smooth scrolling.

**APPROVED:** Stacking uses a small layer scale for sticky header, dropdown, overlay, modal and skip-link layers. Later drawers or modals should use these tokens rather than arbitrary large z-index values.

## Responsive strategy

**APPROVED:** The system is designed for approximately 360, 390, 430, 768, 1024, 1280, 1440 and 1920px viewports. Existing breakpoints remain limited to 1120px, 782px, 767px and 390px. Mobile is a deliberate layout with its own gutters, section rhythm, hero sizing, grids and footer structure. Header/mega-menu redesign remains STORE-003 scope.

## Editor alignment

`theme.json` exposes the same palette, spacing sizes, typography family and practical font sizes to WordPress editor controls. CSS variables remain the source for custom storefront components; the two systems use matching values rather than contradictory palettes.

## Accessibility contract

Visible `:focus-visible` treatment, readable base sizing, usable control heights, semantic HTML compatibility and reduced motion are preserved. The existing skip link is unchanged. Full accessibility auditing remains STORE-013 scope.

