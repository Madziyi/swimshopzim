<?php
/**
 * Lightweight homepage settings using the WordPress Customizer.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'ssz_homepage',
		array(
			'title'       => __( 'SwimShop Homepage', 'swimshop-zimbabwe' ),
			'description' => __( 'Core homepage campaign and announcement settings.', 'swimshop-zimbabwe' ),
			'priority'    => 30,
		)
	);

	$settings = array(
		'ssz_announcement' => array(
			'label'   => __( 'Announcement text', 'swimshop-zimbabwe' ),
			'default' => __( 'Performance swimwear and equipment for every lane.', 'swimshop-zimbabwe' ),
			'type'    => 'text',
		),
		'ssz_hero_eyebrow' => array(
			'label'   => __( 'Hero eyebrow', 'swimshop-zimbabwe' ),
			'default' => __( 'SwimShop Zimbabwe', 'swimshop-zimbabwe' ),
			'type'    => 'text',
		),
		'ssz_hero_title' => array(
			'label'   => __( 'Hero heading', 'swimshop-zimbabwe' ),
			'default' => __( 'SWIM FASTER. TRAIN STRONGER.', 'swimshop-zimbabwe' ),
			'type'    => 'text',
		),
		'ssz_hero_text' => array(
			'label'   => __( 'Hero supporting text', 'swimshop-zimbabwe' ),
			'default' => __( 'Performance swimwear and equipment for training, racing and open water.', 'swimshop-zimbabwe' ),
			'type'    => 'textarea',
		),
		'ssz_hero_cta_label' => array(
			'label'   => __( 'Hero CTA label', 'swimshop-zimbabwe' ),
			'default' => __( 'Shop Now', 'swimshop-zimbabwe' ),
			'type'    => 'text',
		),
		'ssz_hero_cta_url' => array(
			'label'   => __( 'Hero CTA URL', 'swimshop-zimbabwe' ),
			'default' => '',
			'type'    => 'url',
		),
	);

	foreach ( $settings as $id => $args ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $args['type'] ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'url' === $args['type'] ) {
			$sanitize = 'esc_url_raw';
		}

		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $args['default'],
				'sanitize_callback' => $sanitize,
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'label'   => $args['label'],
				'section' => 'ssz_homepage',
				'type'    => $args['type'],
			)
		);
	}

	$wp_customize->add_setting(
		'ssz_hero_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'ssz_hero_image',
			array(
				'label'     => __( 'Hero desktop image', 'swimshop-zimbabwe' ),
				'section'   => 'ssz_homepage',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'ssz_hero_image_mobile',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'ssz_hero_image_mobile',
			array(
				'label'     => __( 'Hero mobile image', 'swimshop-zimbabwe' ),
				'section'   => 'ssz_homepage',
				'mime_type' => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'ssz_customize_register' );
