<?php
/**
 * Theme setup.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_setup() {
	load_theme_textdomain( 'swimshop-zimbabwe', SSZ_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 90,
			'width'       => 420,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'swimshop-zimbabwe' ),
			'footer'  => __( 'Footer Navigation', 'swimshop-zimbabwe' ),
			'legal'   => __( 'Legal Navigation', 'swimshop-zimbabwe' ),
		)
	);

	add_image_size( 'ssz-product-card', 720, 900, true );
	add_image_size( 'ssz-category-card', 960, 1200, true );
	add_image_size( 'ssz-campaign', 1920, 1080, true );
}
add_action( 'after_setup_theme', 'ssz_setup' );

function ssz_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ssz_content_width', 1440 );
}
add_action( 'after_setup_theme', 'ssz_content_width', 0 );
