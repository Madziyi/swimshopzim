<?php
/**
 * Product-loop enhancements.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine the media fit for a product card.
 *
 * Apparel defaults to cover. Equipment and accessory category families use
 * contain so objects with meaningful whitespace are not cropped aggressively.
 *
 * @param WC_Product $product Product object.
 * @return string
 */
function ssz_product_card_media_fit( $product ) {
	$fit = 'cover';

	if ( $product instanceof WC_Product && taxonomy_exists( 'product_cat' ) ) {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		$slugs = array();

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$slugs[]   = $term->slug;
				$ancestors = get_ancestors( $term->term_id, 'product_cat' );

				foreach ( $ancestors as $ancestor_id ) {
					$ancestor = get_term( $ancestor_id, 'product_cat' );

					if ( $ancestor && ! is_wp_error( $ancestor ) ) {
						$slugs[] = $ancestor->slug;
					}
				}
			}
		}

		$equipment_tokens = array(
			'accessor',
			'bag',
			'bottle',
			'cap',
			'equipment',
			'fins',
			'goggle',
			'kickboard',
			'pull-buoy',
			'snorkel',
			'towel',
			'training-gear',
		);

		foreach ( $slugs as $slug ) {
			foreach ( $equipment_tokens as $token ) {
				if ( $slug === $token || false !== strpos( $slug, $token ) ) {
					$fit = 'contain';
					break 2;
				}
			}
		}
	}

	$fit = apply_filters( 'ssz_product_card_media_fit', $fit, $product );

	return in_array( $fit, array( 'cover', 'contain' ), true ) ? $fit : 'cover';
}

/**
 * Select an image source that matches the card media-fit strategy.
 *
 * Cover cards can use the theme's hard-cropped 4:5 derivative. Contain cards
 * must use a non-cropped WordPress size so CSS can preserve the complete
 * source image inside the same 4:5 frame.
 *
 * @param string $fit Media-fit strategy.
 * @return string
 */
function ssz_product_card_image_size_for_fit( $fit ) {
	return 'contain' === $fit ? 'large' : 'ssz-product-card';
}

/**
 * Decide whether a normal loop should use the retail card presentation.
 *
 * Archive and related loops opt in by default; future loop contexts can opt
 * in through the filter without changing the card markup.
 *
 * @param WC_Product $product Product object.
 * @return bool
 */
function ssz_product_card_retail_presentation_enabled( $product ) {
	$enabled = false;

	if ( function_exists( 'ssz_is_product_archive' ) && ssz_is_product_archive() ) {
		$enabled = true;
	} elseif ( is_product() && get_queried_object_id() !== $product->get_id() ) {
		$enabled = true;
	}

	return (bool) apply_filters( 'ssz_product_card_retail_presentation', $enabled, $product );
}

/**
 * Add reusable card classes to WooCommerce product loop items.
 *
 * WooCommerce uses this filter for both loop items and the main single
 * product wrapper, so the queried PDP product is explicitly excluded.
 *
 * @param array      $classes Existing product classes.
 * @param WC_Product $product Product object.
 * @return array
 */
function ssz_product_card_post_class( $classes, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $classes;
	}

	if ( is_product() && get_queried_object_id() === $product->get_id() ) {
		return $classes;
	}

	$classes[] = 'ssz-product-card';
	$classes[] = 'ssz-product-card--' . ssz_product_card_media_fit( $product );

	if ( ssz_product_card_retail_presentation_enabled( $product ) ) {
		$classes[] = 'ssz-product-card--retail';
	}

	return array_unique( $classes );
}
add_filter( 'woocommerce_post_class', 'ssz_product_card_post_class', 20, 2 );

/**
 * Return the assigned global product colours in taxonomy order.
 *
 * @param WC_Product $product Product object.
 * @return WP_Term[]
 */
function ssz_product_colour_terms( $product ) {
	if ( ! $product instanceof WC_Product || ! taxonomy_exists( 'pa_colour' ) ) {
		return array();
	}

	$terms = get_the_terms( $product->get_id(), 'pa_colour' );

	return empty( $terms ) || is_wp_error( $terms ) ? array() : array_values( $terms );
}

/**
 * Map a global colour term to a safe visual value.
 *
 * @param WP_Term $term Colour term.
 * @return string
 */
function ssz_product_colour_value( $term ) {
	$slug = $term instanceof WP_Term ? $term->slug : '';
	$known = array(
		'black' => '#000000',
		'navy'  => '#0b1f3a',
		'blue'  => '#2388ad',
		'red'   => '#d6284d',
	);
	$value = apply_filters( 'ssz_product_colour_value', $known[ $slug ] ?? '', $term );

	return is_string( $value ) && preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ? strtolower( $value ) : '';
}

/**
 * Build image data for variable-product colour previews.
 *
 * @param WC_Product $product Product object.
 * @return array<string,array<string,string>>
 */
function ssz_product_colour_variation_data( $product ) {
	$map = array();

	if ( ! $product instanceof WC_Product_Variable ) {
		return $map;
	}

	$fit        = ssz_product_card_media_fit( $product );
	$image_size = ssz_product_card_image_size_for_fit( $fit );

	foreach ( $product->get_children() as $variation_id ) {
		$variation  = wc_get_product( $variation_id );
		$attributes  = $variation ? $variation->get_attributes() : array();
		$colour_slug = $attributes['pa_colour'] ?? $attributes['colour'] ?? '';
		$image_id    = $variation ? $variation->get_image_id() : 0;

		if ( ! $variation || ! $colour_slug || ! $image_id ) {
			continue;
		}

		$term = get_term_by( 'slug', $colour_slug, 'pa_colour' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$src = wp_get_attachment_image_src( $image_id, $image_size );
		if ( empty( $src[0] ) ) {
			continue;
		}

		$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$map[ $term->slug ] = array(
			'src'    => $src[0],
			'srcset' => wp_get_attachment_image_srcset( $image_id, $image_size ) ?: '',
			'sizes'  => wp_get_attachment_image_sizes( $image_id, $image_size ) ?: '',
			'alt'    => $alt ? $alt : sprintf( '%s — %s', $product->get_name(), $term->name ),
		);
	}

	return $map;
}

/**
 * Render the first brand as text above the native loop title.
 *
 * @return void
 */
function ssz_loop_brand() {
	global $product;

	if ( ! $product || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$brands = get_the_terms( $product->get_id(), 'product_brand' );

	if ( empty( $brands ) || is_wp_error( $brands ) ) {
		return;
	}

	echo '<div class="ssz-product-card__brand ssz-product-brand">' . esc_html( $brands[0]->name ) . '</div>';
}
add_action( 'woocommerce_shop_loop_item_title', 'ssz_loop_brand', 5 );

/**
 * Render assigned colour previews between the loop title and price.
 *
 * @return void
 */
function ssz_loop_product_colour_swatches() {
	global $product;

	if ( ! $product instanceof WC_Product || ! ssz_product_card_retail_presentation_enabled( $product ) ) {
		return;
	}

	$terms = ssz_product_colour_terms( $product );
	if ( count( $terms ) < 2 ) {
		return;
	}

	$visible_terms = array_slice( $terms, 0, 5 );
	$remaining    = count( $terms ) - count( $visible_terms );
	$variation_data = ssz_product_colour_variation_data( $product );

	echo '<div class="ssz-product-card__swatches" data-ssz-colour-swatches data-ssz-colour-variations="' . esc_attr( wp_json_encode( $variation_data ) ) . '" aria-label="' . esc_attr__( 'Available colours', 'swimshop-zimbabwe' ) . '">';

	foreach ( $visible_terms as $term ) {
		$value       = ssz_product_colour_value( $term );
		$style       = $value ? ' style="--ssz-swatch-color:' . esc_attr( $value ) . '"' : '';
		$unknown     = $value ? '' : ' ssz-product-card__swatch--unknown';

		echo '<button class="ssz-product-card__swatch' . esc_attr( $unknown ) . '" type="button" data-ssz-colour-swatch data-colour-slug="' . esc_attr( $term->slug ) . '" aria-label="' . esc_attr( sprintf( __( 'Preview %s', 'swimshop-zimbabwe' ), $term->name ) ) . '" aria-pressed="false"' . $style . '><span aria-hidden="true"></span></button>';
	}

	if ( $remaining > 0 ) {
		echo '<span class="ssz-product-card__swatches-more" aria-label="' . esc_attr( sprintf( __( '%d more colours', 'swimshop-zimbabwe' ), $remaining ) ) . '">+' . esc_html( $remaining ) . '</span>';
	}

	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item_title', 'ssz_loop_product_colour_swatches', 8 );

/**
 * Close retail-card links before sibling swatch and price content.
 *
 * @return void
 */
function ssz_close_retail_product_link_after_title() {
	global $product;

	if ( $product instanceof WC_Product && ssz_product_card_retail_presentation_enabled( $product ) ) {
		echo '</a>';
	}
}

/**
 * Close non-retail product links at WooCommerce's normal hook point.
 *
 * @return void
 */
function ssz_close_product_link_after_item() {
	global $product;

	if ( ! $product instanceof WC_Product || ! ssz_product_card_retail_presentation_enabled( $product ) ) {
		echo '</a>';
	}
}

/**
 * Render the theme-owned product media and primary badge.
 *
 * The normal WooCommerce loop link remains the single interactive wrapper.
 * The secondary image is decorative because the product name and price are
 * already announced by the same link.
 *
 * @return void
 */
function ssz_product_card_media() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_name = $product->get_name();
	$image_id    = $product->get_image_id();
	$gallery_ids = $product->get_gallery_image_ids();
	$secondary   = ! empty( $gallery_ids ) ? (int) reset( $gallery_ids ) : 0;
	$fit         = ssz_product_card_media_fit( $product );
	$image_size  = ssz_product_card_image_size_for_fit( $fit );
	$badge       = '';

	if ( ! $product->is_in_stock() ) {
		$badge = __( 'Sold out', 'swimshop-zimbabwe' );
	} elseif ( $product->is_on_sale() ) {
		$badge = __( 'Sale', 'swimshop-zimbabwe' );
	}

	echo '<div class="ssz-product-card__media ssz-media-frame">';

	if ( $image_id ) {
		echo wp_get_attachment_image(
			$image_id,
			$image_size,
			false,
			array(
				'class'    => 'ssz-product-card__image ssz-product-card__image--primary',
				'alt'      => $product_name,
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		$placeholder = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( $image_size ) : '';

		if ( $placeholder ) {
			echo '<img class="ssz-product-card__image ssz-product-card__image--primary" src="' . esc_url( $placeholder ) . '" alt="' . esc_attr( $product_name ) . '" loading="lazy" decoding="async">';
		}
	}

	if ( $secondary ) {
		echo wp_get_attachment_image(
			$secondary,
			$image_size,
			false,
			array(
				'class'       => 'ssz-product-card__image ssz-product-card__image--secondary',
				'alt'         => '',
				'aria-hidden' => 'true',
				'loading'     => 'lazy',
				'decoding'    => 'async',
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( $badge ) {
		echo '<span class="ssz-product-card__badge" aria-label="' . esc_attr( $badge ) . '">' . esc_html( $badge ) . '</span>';
	}

	echo '</div>';
}

/**
 * Keep the normal WooCommerce loop link/title/price, replacing only the
 * presentation controls that do not belong in this card system.
 *
 * @return void
 */
function ssz_configure_product_loop() {
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'ssz_product_card_media', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	add_action( 'woocommerce_shop_loop_item_title', 'ssz_close_retail_product_link_after_title', 20 );
	add_action( 'woocommerce_after_shop_loop_item', 'ssz_close_product_link_after_item', 5 );
}
add_action( 'init', 'ssz_configure_product_loop', 20 );

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
