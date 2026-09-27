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
	$defaults = ssz_homepage_defaults();

	$wp_customize->add_panel(
		'ssz_homepage',
		array(
			'title'       => __( 'SwimShop Homepage', 'swimshop-zimbabwe' ),
			'description' => __( 'Shape the homepage sections without editing theme files. Empty media fields use an intentional branded treatment.', 'swimshop-zimbabwe' ),
			'priority'    => 30,
		)
	);

	$sections = array(
		'announcement' => array(
			'id'    => 'ssz_homepage_announcement',
			'title' => __( 'Announcement', 'swimshop-zimbabwe' ),
		),
		'hero' => array(
			'id'          => 'ssz_homepage_hero',
			'title'       => __( 'Hero', 'swimshop-zimbabwe' ),
			'description' => __( 'One campaign image, two clear shopping paths. Use a mobile crop where the desktop image does not compose well.', 'swimshop-zimbabwe' ),
		),
		'categories' => array(
			'id'          => 'ssz_homepage_categories',
			'title'       => __( 'Categories', 'swimshop-zimbabwe' ),
			'description' => __( 'Choose up to five top-level WooCommerce categories. Automatic selection uses sensible swimming slugs, then catalog order.', 'swimshop-zimbabwe' ),
		),
		'activities' => array(
			'id'          => 'ssz_homepage_activities',
			'title'       => __( 'Shop by Activity', 'swimshop-zimbabwe' ),
			'description' => __( 'Give swimmers three clear reasons to enter the store.', 'swimshop-zimbabwe' ),
		),
		'campaign' => array(
			'id'    => 'ssz_homepage_campaign',
			'title' => __( 'Performance Campaign', 'swimshop-zimbabwe' ),
		),
		'products' => array(
			'id'          => 'ssz_homepage_products',
			'title'       => __( 'Product Sections', 'swimshop-zimbabwe' ),
			'description' => __( 'Product cards remain WooCommerce-owned and will receive their final STORE-005 treatment later.', 'swimshop-zimbabwe' ),
		),
		'features' => array(
			'id'    => 'ssz_homepage_features',
			'title' => __( 'Race Day / Training', 'swimshop-zimbabwe' ),
		),
		'proposition' => array(
			'id'    => 'ssz_homepage_proposition',
			'title' => __( 'Store Proposition', 'swimshop-zimbabwe' ),
		),
		'newsletter' => array(
			'id'    => 'ssz_homepage_newsletter',
			'title' => __( 'Newsletter', 'swimshop-zimbabwe' ),
		),
	);

	foreach ( $sections as $section ) {
		$wp_customize->add_section(
			$section['id'],
			array(
				'title'       => $section['title'],
				'description' => isset( $section['description'] ) ? $section['description'] : '',
				'panel'       => 'ssz_homepage',
			)
		);
	}

	$section = $sections['announcement']['id'];
	$add_setting = function ( $id, $label, $default, $type = 'text', $sanitize = 'sanitize_text_field' ) use ( $wp_customize, &$section ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitize,
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			)
		);
	};

	$add_media = function ( $id, $label ) use ( $wp_customize, &$section ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$id,
				array(
					'label'     => $label,
					'section'   => $section,
					'mime_type' => 'image',
				)
			)
		);
	};

	$add_setting( 'ssz_announcement', __( 'Announcement text', 'swimshop-zimbabwe' ), __( 'Performance swimwear and equipment for every lane.', 'swimshop-zimbabwe' ) );

	$section = $sections['hero']['id'];
	$add_media( 'ssz_hero_image', __( 'Hero desktop image', 'swimshop-zimbabwe' ) );
	$add_media( 'ssz_hero_image_mobile', __( 'Hero mobile image', 'swimshop-zimbabwe' ) );
	$add_setting( 'ssz_hero_eyebrow', __( 'Hero eyebrow', 'swimshop-zimbabwe' ), $defaults['hero_eyebrow'] );
	$add_setting( 'ssz_hero_title', __( 'Hero heading', 'swimshop-zimbabwe' ), $defaults['hero_title'] );
	$add_setting( 'ssz_hero_text', __( 'Hero supporting text', 'swimshop-zimbabwe' ), $defaults['hero_text'], 'textarea', 'sanitize_textarea_field' );
	$legacy_label = get_theme_mod( 'ssz_hero_cta_label', '' );
	$legacy_url   = get_theme_mod( 'ssz_hero_cta_url', '' );
	$add_setting( 'ssz_hero_primary_label', __( 'Primary CTA label', 'swimshop-zimbabwe' ), $legacy_label ? $legacy_label : $defaults['hero_primary_label'] );
	$add_setting( 'ssz_hero_primary_url', __( 'Primary CTA URL', 'swimshop-zimbabwe' ), $legacy_url, 'url', 'esc_url_raw' );
	$add_setting( 'ssz_hero_secondary_label', __( 'Secondary CTA label', 'swimshop-zimbabwe' ), $defaults['hero_secondary_label'] );
	$add_setting( 'ssz_hero_secondary_url', __( 'Secondary CTA URL', 'swimshop-zimbabwe' ), '', 'url', 'esc_url_raw' );
	$add_setting( 'ssz_hero_alignment', __( 'Hero content alignment', 'swimshop-zimbabwe' ), $defaults['hero_alignment'], 'select', 'sanitize_key' );
	$wp_customize->get_control( 'ssz_hero_alignment' )->choices = array(
		'left'   => __( 'Left', 'swimshop-zimbabwe' ),
		'center' => __( 'Center', 'swimshop-zimbabwe' ),
	);

	$section = $sections['categories']['id'];
	$category_choices = ssz_get_homepage_category_choices();
	for ( $index = 1; $index <= 5; $index++ ) {
		$id = 'ssz_home_category_' . $index;
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => sprintf( __( 'Category %d', 'swimshop-zimbabwe' ), $index ),
				'section' => $section,
				'type'    => 'select',
				'choices' => $category_choices,
			)
		);
	}

	$section = $sections['activities']['id'];
	$activities = array( 'racing' => __( 'Racing', 'swimshop-zimbabwe' ), 'training' => __( 'Training', 'swimshop-zimbabwe' ), 'open_water' => __( 'Open Water', 'swimshop-zimbabwe' ) );
	foreach ( $activities as $slug => $label ) {
		$add_media( 'ssz_activity_' . $slug . '_image', sprintf( __( '%s image', 'swimshop-zimbabwe' ), $label ) );
		$add_setting( 'ssz_activity_' . $slug . '_title', sprintf( __( '%s title', 'swimshop-zimbabwe' ), $label ), $defaults[ 'activity_' . $slug . '_title' ] );
		$add_setting( 'ssz_activity_' . $slug . '_text', sprintf( __( '%s supporting text', 'swimshop-zimbabwe' ), $label ), $defaults[ 'activity_' . $slug . '_text' ], 'textarea', 'sanitize_textarea_field' );
		$add_setting( 'ssz_activity_' . $slug . '_url', sprintf( __( '%s URL', 'swimshop-zimbabwe' ), $label ), '', 'url', 'esc_url_raw' );
	}

	$section = $sections['campaign']['id'];
	$add_media( 'ssz_campaign_image', __( 'Campaign desktop image', 'swimshop-zimbabwe' ) );
	$add_media( 'ssz_campaign_image_mobile', __( 'Campaign mobile image', 'swimshop-zimbabwe' ) );
	$add_setting( 'ssz_campaign_eyebrow', __( 'Campaign eyebrow', 'swimshop-zimbabwe' ), $defaults['campaign_eyebrow'] );
	$add_setting( 'ssz_campaign_title', __( 'Campaign heading', 'swimshop-zimbabwe' ), $defaults['campaign_title'] );
	$add_setting( 'ssz_campaign_text', __( 'Campaign text', 'swimshop-zimbabwe' ), $defaults['campaign_text'], 'textarea', 'sanitize_textarea_field' );
	$add_setting( 'ssz_campaign_cta_label', __( 'Campaign CTA label', 'swimshop-zimbabwe' ), $defaults['campaign_cta_label'] );
	$add_setting( 'ssz_campaign_cta_url', __( 'Campaign CTA URL', 'swimshop-zimbabwe' ), '', 'url', 'esc_url_raw' );

	$section = $sections['products']['id'];
	$add_setting( 'ssz_new_arrivals_heading', __( 'New Arrivals heading', 'swimshop-zimbabwe' ), $defaults['new_arrivals_heading'] );
	$add_setting( 'ssz_new_arrivals_count', __( 'New Arrivals count', 'swimshop-zimbabwe' ), $defaults['new_arrivals_count'], 'number', 'absint' );
	$add_setting( 'ssz_best_sellers_heading', __( 'Best Sellers heading', 'swimshop-zimbabwe' ), $defaults['best_sellers_heading'] );
	$add_setting( 'ssz_best_sellers_count', __( 'Best Sellers count', 'swimshop-zimbabwe' ), $defaults['best_sellers_count'], 'number', 'absint' );

	$section = $sections['features']['id'];
	$add_media( 'ssz_feature_race_image', __( 'Race Day image', 'swimshop-zimbabwe' ) );
	$add_setting( 'ssz_feature_race_title', __( 'Race Day title', 'swimshop-zimbabwe' ), $defaults['race_title'] );
	$add_setting( 'ssz_feature_race_text', __( 'Race Day text', 'swimshop-zimbabwe' ), $defaults['race_text'], 'textarea', 'sanitize_textarea_field' );
	$add_setting( 'ssz_feature_race_cta_label', __( 'Race Day CTA label', 'swimshop-zimbabwe' ), $defaults['race_cta_label'] );
	$add_setting( 'ssz_feature_race_cta_url', __( 'Race Day CTA URL', 'swimshop-zimbabwe' ), '', 'url', 'esc_url_raw' );
	$add_media( 'ssz_feature_training_image', __( 'Training Equipment image', 'swimshop-zimbabwe' ) );
	$add_setting( 'ssz_feature_training_title', __( 'Training Equipment title', 'swimshop-zimbabwe' ), $defaults['training_title'] );
	$add_setting( 'ssz_feature_training_text', __( 'Training Equipment text', 'swimshop-zimbabwe' ), $defaults['training_text'], 'textarea', 'sanitize_textarea_field' );
	$add_setting( 'ssz_feature_training_cta_label', __( 'Training Equipment CTA label', 'swimshop-zimbabwe' ), $defaults['training_cta_label'] );
	$add_setting( 'ssz_feature_training_cta_url', __( 'Training Equipment CTA URL', 'swimshop-zimbabwe' ), '', 'url', 'esc_url_raw' );

	$section = $sections['proposition']['id'];
	$add_setting( 'ssz_proposition_eyebrow', __( 'Proposition eyebrow', 'swimshop-zimbabwe' ), $defaults['proposition_eyebrow'] );
	$add_setting( 'ssz_proposition_title', __( 'Proposition heading', 'swimshop-zimbabwe' ), $defaults['proposition_title'] );
	$proposition_points = array( 'trusted', 'swimmers', 'simple' );
	foreach ( $proposition_points as $point ) {
		$add_setting( 'ssz_proposition_' . $point . '_title', sprintf( __( '%s point title', 'swimshop-zimbabwe' ), ucfirst( $point ) ), $defaults[ 'proposition_' . $point . '_title' ] );
		$add_setting( 'ssz_proposition_' . $point . '_text', sprintf( __( '%s point text', 'swimshop-zimbabwe' ), ucfirst( $point ) ), $defaults[ 'proposition_' . $point . '_text' ], 'textarea', 'sanitize_textarea_field' );
	}

	$section = $sections['newsletter']['id'];
	$add_setting( 'ssz_newsletter_title', __( 'Newsletter heading', 'swimshop-zimbabwe' ), $defaults['newsletter_title'] );
	$add_setting( 'ssz_newsletter_text', __( 'Newsletter text', 'swimshop-zimbabwe' ), $defaults['newsletter_text'], 'textarea', 'sanitize_textarea_field' );
	$add_setting( 'ssz_newsletter_note', __( 'Newsletter note', 'swimshop-zimbabwe' ), $defaults['newsletter_note'], 'textarea', 'sanitize_textarea_field' );
}
add_action( 'customize_register', 'ssz_customize_register' );
