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

/**
 * Return the four fixed homepage carousel slots.
 *
 * @return array<int,array<string,mixed>>
 */
function ssz_get_homepage_hero_slides() {
	$defaults = array(
		1 => array( 'enabled' => true, 'eyebrow' => ssz_homepage_default( 'hero_eyebrow' ), 'title' => ssz_homepage_default( 'hero_title' ), 'text' => ssz_homepage_default( 'hero_text' ), 'primary_label' => ssz_homepage_default( 'hero_primary_label' ), 'primary_url' => '', 'secondary_label' => ssz_homepage_default( 'hero_secondary_label' ), 'secondary_url' => '', 'alignment' => 'left' ),
		2 => array( 'enabled' => false, 'eyebrow' => '', 'title' => '', 'text' => '', 'primary_label' => '', 'primary_url' => '', 'secondary_label' => '', 'secondary_url' => '', 'alignment' => 'left' ),
		3 => array( 'enabled' => false, 'eyebrow' => '', 'title' => '', 'text' => '', 'primary_label' => '', 'primary_url' => '', 'secondary_label' => '', 'secondary_url' => '', 'alignment' => 'left' ),
		4 => array( 'enabled' => false, 'eyebrow' => '', 'title' => '', 'text' => '', 'primary_label' => '', 'primary_url' => '', 'secondary_label' => '', 'secondary_url' => '', 'alignment' => 'left' ),
	);

	$slides = array();
	foreach ( $defaults as $index => $default ) {
		$prefix = 'ssz_hero_slide_' . $index . '_';
		$slide  = $default;
		$slide['enabled']         = (bool) get_theme_mod( $prefix . 'enabled', $default['enabled'] );
		$slide['image_id']        = absint( get_theme_mod( $prefix . 'image', 0 ) );
		$slide['mobile_image_id'] = absint( get_theme_mod( $prefix . 'image_mobile', 0 ) );
		$slide['eyebrow']         = (string) get_theme_mod( $prefix . 'eyebrow', $default['eyebrow'] );
		$slide['title']           = (string) get_theme_mod( $prefix . 'title', $default['title'] );
		$slide['text']            = (string) get_theme_mod( $prefix . 'text', $default['text'] );
		$slide['primary_label']   = (string) get_theme_mod( $prefix . 'primary_label', $default['primary_label'] );
		$slide['primary_url']     = (string) get_theme_mod( $prefix . 'primary_url', $default['primary_url'] );
		$slide['secondary_label'] = (string) get_theme_mod( $prefix . 'secondary_label', $default['secondary_label'] );
		$slide['secondary_url']   = (string) get_theme_mod( $prefix . 'secondary_url', $default['secondary_url'] );
		$slide['alignment']       = sanitize_key( get_theme_mod( $prefix . 'alignment', $default['alignment'] ) );
		$slide['alignment']       = in_array( $slide['alignment'], array( 'left', 'center' ), true ) ? $slide['alignment'] : 'left';

		if ( $slide['enabled'] ) {
			$slides[] = $slide;
		}
	}

	if ( ! $slides ) {
		$fallback = $defaults[1];
		$fallback['image_id']        = absint( get_theme_mod( 'ssz_hero_slide_1_image', 0 ) );
		$fallback['mobile_image_id'] = absint( get_theme_mod( 'ssz_hero_slide_1_image_mobile', 0 ) );
		$slides[]                   = $fallback;
	}

	return $slides;
}

function ssz_get_homepage_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$categories = array();
	$seen       = array();
	$preferred_slugs = array( 'men', 'women', 'kids', 'equipment' );

	for ( $index = 1; $index <= 4; $index++ ) {
		$term_id = absint( get_theme_mod( 'ssz_home_category_' . $index, 0 ) );
		if ( ! $term_id ) {
			continue;
		}

		$term = get_term( $term_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && in_array( $term->slug, $preferred_slugs, true ) && ! isset( $seen[ $term->term_id ] ) ) {
			$categories[]                = $term;
			$seen[ $term->term_id ] = true;
		}
	}

	foreach ( $preferred_slugs as $slug ) {
		if ( count( $categories ) >= 4 ) {
			break;
		}

		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && ! isset( $seen[ $term->term_id ] ) ) {
			$categories[]                = $term;
			$seen[ $term->term_id ] = true;
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

	$preferred = array( 'men', 'women', 'kids', 'equipment' );
	foreach ( $preferred as $slug ) {
		foreach ( $terms as $term ) {
			if ( $term->slug === $slug ) {
				$choices[ $term->term_id ] = $term->name;
				break;
			}
		}
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
