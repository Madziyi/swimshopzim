<?php
/**
 * Product-page Customizer settings.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register global product-page content settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function ssz_product_page_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'ssz_product_page',
		array(
			'title'       => __( 'SwimShop Product Page', 'swimshop-zimbabwe' ),
			'description' => __( 'Choose the real store pages shown on product details. Leave either field empty to omit that product-page link or accordion.', 'swimshop-zimbabwe' ),
			'priority'    => 40,
		)
	);

	$settings = array(
		'ssz_product_page_size_guide'       => __( 'Size Guide Page', 'swimshop-zimbabwe' ),
		'ssz_product_page_shipping_returns' => __( 'Shipping & Returns Page', 'swimshop-zimbabwe' ),
	);

	foreach ( $settings as $setting_id => $label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $label,
				'section' => 'ssz_product_page',
				'type'    => 'dropdown-pages',
			)
		);
	}
}
add_action( 'customize_register', 'ssz_product_page_customize_register' );
