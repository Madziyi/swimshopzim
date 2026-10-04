<?php
/**
 * WooCommerce customer account presentation hooks.
 *
 * WooCommerce remains authoritative for account authentication, customer data,
 * endpoint routing, validation, orders and payment methods. This module only
 * adjusts the customer-facing menu labels and heading presentation.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove customer-facing Downloads and use SwimShop wording for logout.
 *
 * The underlying endpoints remain registered by WooCommerce. Only the menu
 * presentation is filtered here.
 *
 * @param array $items Native WooCommerce account menu items.
 * @return array
 */
function ssz_account_menu_items( $items ) {
	unset( $items['downloads'] );

	if ( isset( $items['customer-logout'] ) ) {
		$items['customer-logout'] = __( 'Sign out', 'swimshop-zimbabwe' );
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'ssz_account_menu_items', 10 );

/**
 * Keep one stable storefront H1 on every account endpoint.
 *
 * WooCommerce filters the page title to the current endpoint title. The
 * account content receives its own H2 below, so the page-level title stays
 * consistent and does not duplicate endpoint headings as H1 elements.
 *
 * @param string $title Page title.
 * @param int    $post_id Post ID.
 * @return string
 */
function ssz_account_page_title( $title, $post_id ) {
	if ( is_admin() || ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return $title;
	}

	$account_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'myaccount' ) : 0;

	if ( $account_page_id && (int) $post_id === (int) $account_page_id ) {
		return __( 'MY ACCOUNT', 'swimshop-zimbabwe' );
	}

	return $title;
}
add_filter( 'the_title', 'ssz_account_page_title', 999, 2 );

/**
 * Render the current native endpoint as a content heading.
 *
 * The heading is inserted into WooCommerce's account-content action before
 * WooCommerce renders its endpoint template. No endpoint data or routing is
 * changed.
 *
 * @return void
 */
function ssz_account_content_heading() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! function_exists( 'wc_get_account_menu_items' ) ) {
		return;
	}

	$endpoint = function_exists( 'WC' ) && WC()->query ? WC()->query->get_current_endpoint() : '';

	if ( 'view-order' === $endpoint ) {
		return;
	}
	if ( function_exists( 'is_payment_methods_page' ) && is_payment_methods_page() ) {
		return;
	}

	$items    = wc_get_account_menu_items();
	$heading  = $endpoint && isset( $items[ $endpoint ] ) ? wp_strip_all_tags( $items[ $endpoint ] ) : __( 'Dashboard', 'swimshop-zimbabwe' );

	printf(
		'<h2 class="ssz-account-content__heading">%s</h2>',
		esc_html( $heading )
	);
}
add_action( 'woocommerce_account_content', 'ssz_account_content_heading', 9 );

/**
 * Add a heading to the configured native payment-methods endpoint even when
 * WooCommerce omits it from the account menu because no gateway is available.
 *
 * @return void
 */
function ssz_account_payment_methods_heading() {
	printf(
		'<h2 class="ssz-account-content__heading">%s</h2>',
		esc_html__( 'Payment methods', 'swimshop-zimbabwe' )
	);
}
add_action( 'woocommerce_account_payment-methods_endpoint', 'ssz_account_payment_methods_heading', 9 );
