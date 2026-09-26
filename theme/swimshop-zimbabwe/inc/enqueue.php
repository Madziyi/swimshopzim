<?php
/**
 * Assets.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_enqueue_assets() {
	wp_enqueue_style( 'ssz-main', SSZ_THEME_URI . '/assets/css/main.css', array(), SSZ_THEME_VERSION );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'ssz-woocommerce', SSZ_THEME_URI . '/assets/css/woocommerce.css', array( 'ssz-main' ), SSZ_THEME_VERSION );
	}

	wp_enqueue_script( 'ssz-navigation', SSZ_THEME_URI . '/assets/js/navigation.js', array(), SSZ_THEME_VERSION, true );
	wp_enqueue_script( 'ssz-search', SSZ_THEME_URI . '/assets/js/search.js', array(), SSZ_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ssz_enqueue_assets' );
