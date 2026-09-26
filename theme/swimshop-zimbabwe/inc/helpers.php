<?php
/**
 * Theme helpers.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_get_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

function ssz_get_brands_url() {
	$brands_page = get_page_by_path( 'brands' );
	if ( $brands_page ) {
		return get_permalink( $brands_page );
	}

	return ssz_get_shop_url();
}

function ssz_get_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return wp_login_url();
}

function ssz_get_cart_url() {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

function ssz_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}

	return 0;
}

function ssz_get_brand_terms( $hide_empty = true ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => $hide_empty,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

function ssz_get_brand_thumbnail_url( $term_id ) {
	if ( function_exists( 'wc_get_brand_thumbnail_url' ) ) {
		return wc_get_brand_thumbnail_url( $term_id, 'medium' );
	}

	return '';
}
