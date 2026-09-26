<?php
/**
 * Product-loop enhancements.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_loop_brand() {
	global $product;

	if ( ! $product || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$brands = get_the_terms( $product->get_id(), 'product_brand' );
	if ( empty( $brands ) || is_wp_error( $brands ) ) {
		return;
	}

	echo '<div class="ssz-product-brand">' . esc_html( $brands[0]->name ) . '</div>';
}
add_action( 'woocommerce_shop_loop_item_title', 'ssz_loop_brand', 5 );

function ssz_single_brand() {
	global $product;

	if ( ! $product || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$brands = get_the_terms( $product->get_id(), 'product_brand' );
	if ( empty( $brands ) || is_wp_error( $brands ) ) {
		return;
	}

	$links = array();
	foreach ( $brands as $brand ) {
		$link = get_term_link( $brand );
		if ( ! is_wp_error( $link ) ) {
			$links[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html( $brand->name ) );
		}
	}

	if ( $links ) {
		echo '<div class="ssz-single-brand">' . wp_kses_post( implode( ', ', $links ) ) . '</div>';
	}
}
add_action( 'woocommerce_single_product_summary', 'ssz_single_brand', 4 );
