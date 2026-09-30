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
	wp_enqueue_style( 'ssz-header', SSZ_THEME_URI . '/assets/css/header.css', array( 'ssz-main' ), SSZ_THEME_VERSION );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'ssz-woocommerce', SSZ_THEME_URI . '/assets/css/woocommerce.css', array( 'ssz-main' ), SSZ_THEME_VERSION );
		wp_enqueue_style( 'ssz-product-card', SSZ_THEME_URI . '/assets/css/product-card.css', array( 'ssz-woocommerce' ), SSZ_THEME_VERSION );
		wp_enqueue_script( 'ssz-product-card-swatches', SSZ_THEME_URI . '/assets/js/product-card-swatches.js', array(), SSZ_THEME_VERSION, true );

		if ( is_product() ) {
			wp_enqueue_style( 'ssz-product-page', SSZ_THEME_URI . '/assets/css/product-page.css', array( 'ssz-woocommerce', 'ssz-product-card' ), SSZ_THEME_VERSION );
			wp_enqueue_script( 'ssz-product-page', SSZ_THEME_URI . '/assets/js/product-page.js', array( 'jquery', 'wc-add-to-cart-variation' ), SSZ_THEME_VERSION, true );
		}

		if ( function_exists( 'ssz_is_product_archive' ) && ssz_is_product_archive() ) {
			wp_enqueue_style( 'ssz-archive', SSZ_THEME_URI . '/assets/css/archive.css', array( 'ssz-product-card' ), SSZ_THEME_VERSION );
			wp_enqueue_script( 'ssz-archive-filters', SSZ_THEME_URI . '/assets/js/archive-filters.js', array(), SSZ_THEME_VERSION, true );
		}
	}

	if ( is_front_page() ) {
		wp_enqueue_style( 'ssz-homepage', SSZ_THEME_URI . '/assets/css/homepage.css', array( 'ssz-main', 'ssz-header' ), SSZ_THEME_VERSION );
	}

	wp_enqueue_script( 'ssz-navigation', SSZ_THEME_URI . '/assets/js/navigation.js', array(), SSZ_THEME_VERSION, true );
	wp_enqueue_script( 'ssz-search', SSZ_THEME_URI . '/assets/js/search.js', array(), SSZ_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ssz_enqueue_assets' );
