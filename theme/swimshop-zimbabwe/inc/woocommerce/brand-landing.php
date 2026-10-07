<?php
/**
 * Native product_brand storefront presentation and term promotion fields.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_is_brand_archive() {
	$term = function_exists( 'get_queried_object' ) ? get_queried_object() : null;

	return function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() && $term instanceof WP_Term && 'product_brand' === $term->taxonomy;
}

function ssz_brand_promo_meta_key( $index, $field ) {
	return 'ssz_brand_promo_' . absint( $index ) . '_' . sanitize_key( $field );
}

function ssz_get_brand_promo_cards( $term_id, $enabled_only = true ) {
	$cards = array();

	for ( $index = 1; $index <= 4; $index++ ) {
		$card = array(
			'index'       => $index,
			'enabled'     => (bool) get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'enabled' ), true ),
			'image_id'    => absint( get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'image_id' ), true ) ),
			'title'       => (string) get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'title' ), true ),
			'text'        => (string) get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'text' ), true ),
			'cta_label'   => (string) get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'cta_label' ), true ),
			'cta_url'     => (string) get_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'cta_url' ), true ),
		);

		if ( $enabled_only && ! $card['enabled'] ) {
			continue;
		}

		$cards[] = $card;
	}

	return $cards;
}

function ssz_brand_category_terms() {
	$categories = array();

	foreach ( array( 'men', 'women', 'kids', 'equipment' ) as $slug ) {
		$term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $slug, 'product_cat' ) : false;
		$categories[] = array(
			'slug' => $slug,
			'name' => $term && ! is_wp_error( $term ) ? $term->name : ucwords( str_replace( '-', ' ', $slug ) ),
			'term' => $term && ! is_wp_error( $term ) ? $term : null,
		);
	}

	return $categories;
}

function ssz_brand_category_url( $brand_id, $category_slug ) {
	return add_query_arg(
		array(
			'filter_product_brand' => absint( $brand_id ),
			'filter_product_cat'   => sanitize_title( $category_slug ),
		),
		ssz_get_shop_url()
	);
}

function ssz_render_brand_storefront_header() {
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || 'product_brand' !== $term->taxonomy ) {
		return;
	}

	$logo_url   = ssz_get_brand_thumbnail_url( $term->term_id );
	$promo_cards = ssz_get_brand_promo_cards( $term->term_id );
	$categories = ssz_brand_category_terms();

	echo '<div class="ssz-brand-storefront" data-brand-storefront>';
	echo '<header class="ssz-brand-storefront__identity">';
	if ( $logo_url ) {
		echo '<img class="ssz-brand-storefront__logo" src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $term->name ) . '" loading="eager" decoding="async">';
		echo '<h1 class="screen-reader-text">' . esc_html( $term->name ) . '</h1>';
	} else {
		echo '<h1>' . esc_html( $term->name ) . '</h1>';
	}
	echo '</header>';

	if ( $promo_cards ) {
		echo '<section class="ssz-brand-promos" aria-label="' . esc_attr( sprintf( __( '%s promotions', 'swimshop-zimbabwe' ), $term->name ) ) . '">';
		echo '<div class="ssz-brand-promos__grid">';
		foreach ( $promo_cards as $card ) {
			echo '<article class="ssz-brand-promo-card">';
			if ( $card['image_id'] ) {
				echo wp_get_attachment_image( $card['image_id'], 'large', false, array( 'class' => 'ssz-brand-promo-card__image', 'alt' => $card['title'], 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<div class="ssz-brand-promo-card__fallback" aria-hidden="true"></div>';
			}
			echo '<div class="ssz-brand-promo-card__content">';
			if ( $card['title'] ) {
				echo '<h2>' . esc_html( $card['title'] ) . '</h2>';
			}
			if ( $card['text'] ) {
				echo '<p>' . esc_html( $card['text'] ) . '</p>';
			}
			if ( $card['cta_label'] && $card['cta_url'] ) {
				echo '<a class="ssz-brand-promo-card__cta" href="' . esc_url( $card['cta_url'] ) . '">' . esc_html( $card['cta_label'] ) . ' <span aria-hidden="true">→</span></a>';
			}
			echo '</div></article>';
		}
	echo '</div></section>';
	}

	echo '<div class="ssz-brand-storefront__shop-all"><a class="ssz-button ssz-button--brand" href="#brand-products">' . esc_html( sprintf( __( 'Shop all %s', 'swimshop-zimbabwe' ), $term->name ) ) . '</a></div>';
	echo '<section class="ssz-brand-categories" aria-labelledby="ssz-brand-category-heading"><h2 id="ssz-brand-category-heading">' . esc_html( sprintf( __( 'Shop %s by category', 'swimshop-zimbabwe' ), $term->name ) ) . '</h2><div class="ssz-brand-category-grid">';
	foreach ( $categories as $category ) {
		$image_id = $category['term'] ? absint( get_term_meta( $category['term']->term_id, 'thumbnail_id', true ) ) : 0;
		echo '<a class="ssz-brand-category-card" href="' . esc_url( ssz_brand_category_url( $term->term_id, $category['slug'] ) ) . '"><span class="ssz-brand-category-card__media">';
		if ( $image_id ) {
			echo wp_get_attachment_image( $image_id, 'ssz-category-card', false, array( 'alt' => $category['name'], 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<span class="ssz-media-placeholder" aria-hidden="true"></span>';
		}
		echo '</span><span class="ssz-brand-category-card__name">' . esc_html( $category['name'] ) . '</span></a>';
	}
	echo '</div></section>';

	echo '<section id="brand-products" class="ssz-brand-products" aria-labelledby="ssz-brand-products-heading"><div class="ssz-brand-products__heading"><h2 id="ssz-brand-products-heading">' . esc_html( sprintf( __( 'Shop %s', 'swimshop-zimbabwe' ), $term->name ) ) . '</h2></div>';
}

function ssz_close_brand_storefront_products() {
	if ( ssz_is_brand_archive() ) {
		echo '</section></div>';
	}
}

function ssz_render_brand_archive_header( $type = '' ) {
	if ( 'brand' === $type && ssz_is_brand_archive() ) {
		ssz_render_brand_storefront_header();
		return true;
	}

	return false;
}

function ssz_brand_promo_fields( $term = null ) {
	$term_id = $term instanceof WP_Term ? $term->term_id : 0;
	if ( ! $term_id ) {
		echo '<input type="hidden" name="ssz_brand_promo_nonce" value="' . esc_attr( wp_create_nonce( 'ssz_brand_promo_save' ) ) . '">';
	} else {
		wp_nonce_field( 'ssz_brand_promo_save', 'ssz_brand_promo_nonce' );
	}
	echo '<div class="form-field"><h2>' . esc_html__( 'Promotional storefront cards', 'swimshop-zimbabwe' ) . '</h2><p>' . esc_html__( 'Use up to four static editorial cards. Images are selected from the Media Library; empty CTA fields leave a card informational only.', 'swimshop-zimbabwe' ) . '</p></div>';

	$cards = $term_id ? ssz_get_brand_promo_cards( $term_id, false ) : array();
	for ( $index = 1; $index <= 4; $index++ ) {
		$card = $cards[ $index - 1 ] ?? array( 'enabled' => false, 'image_id' => 0, 'title' => '', 'text' => '', 'cta_label' => '', 'cta_url' => '' );
		$image = $card['image_id'] ? wp_get_attachment_image( $card['image_id'], 'thumbnail', false, array( 'class' => 'ssz-brand-admin__preview', 'alt' => '' ) ) : '';
		echo '<fieldset class="ssz-brand-admin-card"><legend>' . esc_html( sprintf( __( 'Promotional Card %d', 'swimshop-zimbabwe' ), $index ) ) . '</legend>';
		echo '<label><input type="checkbox" name="ssz_brand_promo_' . esc_attr( $index ) . '_enabled" value="1"' . checked( $card['enabled'], true, false ) . '> ' . esc_html__( 'Enable card', 'swimshop-zimbabwe' ) . '</label>';
		echo '<p><label>' . esc_html__( 'Image', 'swimshop-zimbabwe' ) . '<input type="hidden" class="ssz-brand-admin__image-id" name="ssz_brand_promo_' . esc_attr( $index ) . '_image_id" value="' . esc_attr( $card['image_id'] ) . '"><span class="ssz-brand-admin__preview-wrap">' . wp_kses_post( $image ) . '</span><button type="button" class="button ssz-brand-admin__select-image">' . esc_html__( 'Choose image', 'swimshop-zimbabwe' ) . '</button> <button type="button" class="button-link-delete ssz-brand-admin__remove-image">' . esc_html__( 'Remove', 'swimshop-zimbabwe' ) . '</button></label></p>';
	echo '<p><label>' . esc_html__( 'Title', 'swimshop-zimbabwe' ) . '<input class="widefat" type="text" name="ssz_brand_promo_' . esc_attr( $index ) . '_title" value="' . esc_attr( $card['title'] ) . '"></label></p>';
		echo '<p><label>' . esc_html__( 'Supporting text', 'swimshop-zimbabwe' ) . '<textarea class="widefat" rows="3" name="ssz_brand_promo_' . esc_attr( $index ) . '_text">' . esc_textarea( $card['text'] ) . '</textarea></label></p>';
		echo '<p><label>' . esc_html__( 'CTA label (optional)', 'swimshop-zimbabwe' ) . '<input class="widefat" type="text" name="ssz_brand_promo_' . esc_attr( $index ) . '_cta_label" value="' . esc_attr( $card['cta_label'] ) . '"></label></p>';
		echo '<p><label>' . esc_html__( 'CTA URL (optional)', 'swimshop-zimbabwe' ) . '<input class="widefat" type="url" name="ssz_brand_promo_' . esc_attr( $index ) . '_cta_url" value="' . esc_attr( $card['cta_url'] ) . '"></label></p>';
		echo '</fieldset>';
	}
}
add_action( 'product_brand_add_form_fields', 'ssz_brand_promo_fields' );
add_action( 'product_brand_edit_form_fields', 'ssz_brand_promo_fields' );

function ssz_save_brand_promo_meta( $term_id ) {
	if ( ! isset( $_POST['ssz_brand_promo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ssz_brand_promo_nonce'] ) ), 'ssz_brand_promo_save' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	if ( ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}

	for ( $index = 1; $index <= 4; $index++ ) {
		$prefix = 'ssz_brand_promo_' . $index . '_';
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'enabled' ), isset( $_POST[ $prefix . 'enabled' ] ) ? 1 : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'image_id' ), isset( $_POST[ $prefix . 'image_id' ] ) ? absint( $_POST[ $prefix . 'image_id' ] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'title' ), isset( $_POST[ $prefix . 'title' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'title' ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'text' ), isset( $_POST[ $prefix . 'text' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $prefix . 'text' ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'cta_label' ), isset( $_POST[ $prefix . 'cta_label' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'cta_label' ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, ssz_brand_promo_meta_key( $index, 'cta_url' ), isset( $_POST[ $prefix . 'cta_url' ] ) ? esc_url_raw( wp_unslash( $_POST[ $prefix . 'cta_url' ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
add_action( 'created_product_brand', 'ssz_save_brand_promo_meta' );
add_action( 'edited_product_brand', 'ssz_save_brand_promo_meta' );

function ssz_enqueue_brand_admin_assets( $hook_suffix ) {
	$screen = get_current_screen();
	if ( ! $screen || 'product_brand' !== $screen->taxonomy || ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'ssz-brand-admin', SSZ_THEME_URI . '/assets/js/brand-admin.js', array( 'jquery' ), SSZ_THEME_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'ssz_enqueue_brand_admin_assets' );

add_action( 'woocommerce_after_shop_loop', 'ssz_close_brand_storefront_products', 99 );
add_action( 'woocommerce_no_products_found', 'ssz_close_brand_storefront_products', 99 );
