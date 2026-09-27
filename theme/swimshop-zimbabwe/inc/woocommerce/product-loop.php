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

	return array_unique( $classes );
}
add_filter( 'woocommerce_post_class', 'ssz_product_card_post_class', 20, 2 );

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
