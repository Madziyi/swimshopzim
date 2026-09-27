<?php
/**
 * Homepage helpers and defaults.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_homepage_defaults() {
	return array(
		'hero_eyebrow'              => __( 'Performance swim', 'swimshop-zimbabwe' ),
		'hero_title'                => __( 'SWIM FASTER. TRAIN STRONGER.', 'swimshop-zimbabwe' ),
		'hero_text'                 => __( 'Performance swimwear and equipment for training, racing and open water.', 'swimshop-zimbabwe' ),
		'hero_primary_label'        => __( 'Shop men', 'swimshop-zimbabwe' ),
		'hero_primary_url'          => '',
		'hero_secondary_label'      => __( 'Shop women', 'swimshop-zimbabwe' ),
		'hero_secondary_url'        => '',
		'hero_alignment'            => 'left',
		'campaign_eyebrow'          => __( 'Performance', 'swimshop-zimbabwe' ),
		'campaign_title'            => __( 'BUILT FOR TRAINING', 'swimshop-zimbabwe' ),
		'campaign_text'             => __( 'Technical swimwear, goggles and training equipment for consistent work in the water.', 'swimshop-zimbabwe' ),
		'campaign_cta_label'        => __( 'Shop training', 'swimshop-zimbabwe' ),
		'campaign_cta_url'          => '',
		'new_arrivals_heading'      => __( 'New arrivals', 'swimshop-zimbabwe' ),
		'new_arrivals_count'        => 4,
		'best_sellers_heading'      => __( 'Our best sellers', 'swimshop-zimbabwe' ),
		'best_sellers_count'        => 4,
		'activity_racing_title'     => __( 'Racing', 'swimshop-zimbabwe' ),
		'activity_racing_text'      => __( 'Competition-focused products built for speed.', 'swimshop-zimbabwe' ),
		'activity_training_title'   => __( 'Training', 'swimshop-zimbabwe' ),
		'activity_training_text'    => __( 'Durable swimwear and equipment for daily sessions.', 'swimshop-zimbabwe' ),
		'activity_open_water_title' => __( 'Open water', 'swimshop-zimbabwe' ),
		'activity_open_water_text'  => __( 'Gear for swimming beyond the pool.', 'swimshop-zimbabwe' ),
		'race_title'                => __( 'Race day essentials', 'swimshop-zimbabwe' ),
		'race_text'                 => __( 'Focused gear for the moments that matter.', 'swimshop-zimbabwe' ),
		'race_cta_label'            => __( 'Shop race day', 'swimshop-zimbabwe' ),
		'training_title'            => __( 'Training equipment', 'swimshop-zimbabwe' ),
		'training_text'             => __( 'Build a stronger session from the first length to the last.', 'swimshop-zimbabwe' ),
		'training_cta_label'        => __( 'Shop equipment', 'swimshop-zimbabwe' ),
		'proposition_eyebrow'       => __( 'SwimShop Zimbabwe', 'swimshop-zimbabwe' ),
		'proposition_title'         => __( 'Performance swim essentials, selected for Zimbabwe.', 'swimshop-zimbabwe' ),
		'proposition_trusted_title' => __( 'Trusted brands', 'swimshop-zimbabwe' ),
		'proposition_trusted_text'  => __( 'Shop recognized swimming and aquatic-sport manufacturers.', 'swimshop-zimbabwe' ),
		'proposition_swimmers_title' => __( 'Built for swimmers', 'swimshop-zimbabwe' ),
		'proposition_swimmers_text' => __( 'Products for training, racing, recreation and open water.', 'swimshop-zimbabwe' ),
		'proposition_simple_title'  => __( 'Simple shopping', 'swimshop-zimbabwe' ),
		'proposition_simple_text'   => __( 'A focused storefront designed for fast mobile purchasing.', 'swimshop-zimbabwe' ),
		'newsletter_title'          => __( 'Stay in the lane', 'swimshop-zimbabwe' ),
		'newsletter_text'           => __( 'New products, race gear and store updates.', 'swimshop-zimbabwe' ),
		'newsletter_note'           => __( 'Email sign-up is coming soon.', 'swimshop-zimbabwe' ),
	);
}

function ssz_homepage_default( $key ) {
	$defaults = ssz_homepage_defaults();

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

function ssz_get_homepage_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$categories = array();
	$seen       = array();

	for ( $index = 1; $index <= 5; $index++ ) {
		$term_id = absint( get_theme_mod( 'ssz_home_category_' . $index, 0 ) );
		if ( ! $term_id ) {
			continue;
		}

		$term = get_term( $term_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && ! isset( $seen[ $term->term_id ] ) ) {
			$categories[]                = $term;
			$seen[ $term->term_id ] = true;
		}
	}

	$preferred_slugs = array( 'men', 'women', 'kids', 'goggles', 'equipment' );
	foreach ( $preferred_slugs as $slug ) {
		if ( count( $categories ) >= 5 ) {
			break;
		}

		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && ! isset( $seen[ $term->term_id ] ) ) {
			$categories[]                = $term;
			$seen[ $term->term_id ] = true;
		}
	}

	if ( count( $categories ) < 5 ) {
		$fallback = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 10,
				'parent'     => 0,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( ! is_wp_error( $fallback ) ) {
			foreach ( $fallback as $term ) {
				if ( count( $categories ) >= 5 ) {
					break;
				}
				if ( ! isset( $seen[ $term->term_id ] ) ) {
					$categories[]                = $term;
					$seen[ $term->term_id ] = true;
				}
			}
		}
	}

	return $categories;
}

function ssz_get_homepage_category_choices() {
	$choices = array( 0 => __( 'Automatic category selection', 'swimshop-zimbabwe' ) );

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $choices;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $choices;
	}

	foreach ( $terms as $term ) {
		$choices[ $term->term_id ] = $term->name;
	}

	return $choices;
}

function ssz_homepage_setting( $key, $default = '' ) {
	$value = get_theme_mod( 'ssz_' . $key, null );

	return null === $value || '' === $value ? ( '' !== $default ? $default : ssz_homepage_default( $key ) ) : $value;
}

function ssz_homepage_url_setting( $key, $fallback = '' ) {
	$value = ssz_homepage_setting( $key, $fallback );

	return $value ? $value : $fallback;
}

function ssz_homepage_image_url( $setting, $size = 'full' ) {
	$attachment_id = absint( get_theme_mod( $setting, 0 ) );

	return $attachment_id ? wp_get_attachment_image_url( $attachment_id, $size ) : '';
}
