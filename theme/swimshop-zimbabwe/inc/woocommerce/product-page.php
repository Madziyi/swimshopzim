<?php
/**
 * Single-product presentation and progressive WooCommerce enhancements.
 *
 * The commerce form, native gallery markup and variation engine remain
 * WooCommerce-owned. This file changes presentation through hooks only.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable only WooCommerce's FlexSlider presentation on the PDP.
 *
 * Native image links, zoom and lightbox support stay enabled. Removing the
 * slider support before template rendering leaves the native image nodes in
 * place for the CSS grid/scroll-snap presentation and variation updates.
 *
 * @return void
 */
function ssz_product_page_configure_gallery() {
	if ( is_product() ) {
		remove_theme_support( 'wc-product-gallery-slider' );
	}
}
add_action( 'wp', 'ssz_product_page_configure_gallery', 5 );

/**
 * Add a page-specific body class.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ssz_product_page_body_class( $classes ) {
	if ( is_product() ) {
		$classes[] = 'ssz-product-page';
	}

	return $classes;
}
add_filter( 'body_class', 'ssz_product_page_body_class' );

/**
 * Render the first native brand above the product title.
 *
 * @return void
 */
function ssz_product_page_brand() {
	global $product;

	if ( ! $product instanceof WC_Product || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$brands = get_the_terms( $product->get_id(), 'product_brand' );

	if ( empty( $brands ) || is_wp_error( $brands ) ) {
		return;
	}

	$link = get_term_link( $brands[0] );

	if ( is_wp_error( $link ) ) {
		echo '<div class="ssz-single-brand">' . esc_html( $brands[0]->name ) . '</div>';
		return;
	}

	echo '<div class="ssz-single-brand"><a href="' . esc_url( $link ) . '">' . esc_html( $brands[0]->name ) . '</a></div>';
}
add_action( 'woocommerce_single_product_summary', 'ssz_product_page_brand', 4 );

/**
 * Replace the default price output with a stable dynamic-price region.
 *
 * @return void
 */
function ssz_product_page_price() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	echo '<div class="ssz-product-page__price" data-ssz-product-price>' . wp_kses_post( $product->get_price_html() ) . '</div>';
}

/**
 * Render a product's assigned terms as button-ready variation controls.
 *
 * Native selects are always printed by WooCommerce first. These controls are
 * hidden until product-page.js initializes, so the native commerce source of
 * truth remains usable without JavaScript.
 *
 * @return void
 */
function ssz_product_page_variation_controls() {
	global $product;

	if ( ! $product instanceof WC_Product_Variable ) {
		return;
	}

	$variation_attributes = $product->get_variation_attributes();
	$special_attributes   = array( 'pa_colour', 'pa_size' );

	foreach ( $special_attributes as $attribute_name ) {
		if ( empty( $variation_attributes[ $attribute_name ] ) ) {
			continue;
		}

		$select_name = 'attribute_' . sanitize_title( $attribute_name );
		$label       = wc_attribute_label( $attribute_name );
		$terms       = get_terms(
			array(
				'taxonomy'   => $attribute_name,
				'hide_empty' => false,
				'slug'       => $variation_attributes[ $attribute_name ],
			)
		);
		$term_map    = array();

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_map[ $term->slug ] = $term;
			}
		}

		$control_id = 'ssz-variation-control-' . sanitize_html_class( $attribute_name );
		echo '<fieldset class="ssz-variation-control" data-ssz-variation-control data-ssz-attribute="' . esc_attr( $select_name ) . '" aria-labelledby="' . esc_attr( $control_id ) . '">';
		echo '<legend id="' . esc_attr( $control_id ) . '"><span>' . esc_html( $label ) . '</span><span class="ssz-variation-control__selected" data-ssz-selected-label aria-live="polite"></span></legend>';
		echo '<div class="ssz-variation-control__options" role="group" aria-label="' . esc_attr( $label ) . ' options">';

		foreach ( $variation_attributes[ $attribute_name ] as $option_slug ) {
			$term       = $term_map[ $option_slug ] ?? null;
			$option_name = $term ? $term->name : $option_slug;
			$swatch      = 'pa_colour' === $attribute_name && $term ? ssz_product_colour_value( $term ) : '';
			$style       = $swatch ? ' style="--ssz-swatch-color:' . esc_attr( $swatch ) . '"' : '';
			$class       = 'pa_colour' === $attribute_name ? ' ssz-variation-option--colour' : '';

			echo '<button type="button" class="ssz-variation-option' . esc_attr( $class ) . '" data-ssz-variation-option data-value="' . esc_attr( $option_slug ) . '" aria-label="' . esc_attr( sprintf( __( '%1$s: %2$s', 'swimshop-zimbabwe' ), $label, $option_name ) ) . '" aria-pressed="false"' . $style . '>';
			if ( 'pa_colour' === $attribute_name ) {
				echo '<span class="ssz-variation-option__swatch" aria-hidden="true"></span>';
			}
			echo '<span>' . esc_html( $option_name ) . '</span></button>';
		}

		echo '</div></fieldset>';
	}

	$size_guide_id = absint( get_theme_mod( 'ssz_product_page_size_guide', 0 ) );
	if ( $size_guide_id && ! empty( $variation_attributes['pa_size'] ) ) {
		$size_guide_url = get_permalink( $size_guide_id );

		if ( $size_guide_url ) {
			echo '<a class="ssz-size-guide" href="' . esc_url( $size_guide_url ) . '">' . esc_html__( 'Size guide', 'swimshop-zimbabwe' ) . '</a>';
		}
	}
}
add_action( 'woocommerce_after_variations_table', 'ssz_product_page_variation_controls', 10 );

/**
 * Add a visible quantity label while preserving native quantity input logic.
 *
 * @return void
 */
function ssz_product_page_quantity_label() {
	echo '<span class="ssz-quantity-label">' . esc_html__( 'Quantity', 'swimshop-zimbabwe' ) . '</span>';
}
add_action( 'woocommerce_before_add_to_cart_quantity', 'ssz_product_page_quantity_label', 5 );

/**
 * Render the product description and optional policy accordion.
 *
 * @return void
 */
function ssz_product_page_accordions() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$description = trim( $product->get_description() );
	if ( ! $description ) {
		$description = trim( $product->get_short_description() );
	}

	if ( $description ) {
		echo '<details class="ssz-product-accordion"><summary>' . esc_html__( 'Product details', 'swimshop-zimbabwe' ) . '</summary><div class="ssz-product-accordion__content">' . wp_kses_post( apply_filters( 'the_content', $description ) ) . '</div></details>';
	}

	$shipping_page_id = absint( get_theme_mod( 'ssz_product_page_shipping_returns', 0 ) );
	$shipping_page    = $shipping_page_id ? get_post( $shipping_page_id ) : null;

	if ( $shipping_page && 'page' === $shipping_page->post_type && 'publish' === $shipping_page->post_status ) {
		echo '<details class="ssz-product-accordion"><summary>' . esc_html__( 'Shipping & returns', 'swimshop-zimbabwe' ) . '</summary><div class="ssz-product-accordion__content">' . wp_kses_post( apply_filters( 'the_content', $shipping_page->post_content ) ) . '</div></details>';
	}
}
add_action( 'woocommerce_after_single_product_summary', 'ssz_product_page_accordions', 10 );

/**
 * Apply the approved single-product CTA copy.
 *
 * @param string     $text    Button text.
 * @param WC_Product $product Product object.
 * @return string
 */
function ssz_product_page_add_to_bag_text( $text, $product ) {
	return __( 'Add to bag', 'swimshop-zimbabwe' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'ssz_product_page_add_to_bag_text', 10, 2 );

/**
 * Recompose the default single-product summary around the approved hierarchy.
 *
 * @return void
 */
function ssz_product_page_configure_hooks() {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

	add_action( 'woocommerce_single_product_summary', 'ssz_product_page_price', 10 );
}
add_action( 'init', 'ssz_product_page_configure_hooks', 20 );
