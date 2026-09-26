<?php
/**
 * Navigation helpers.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean fallback navigation for a brand-new install.
 * Once a Primary Navigation menu is assigned, WordPress fully controls the structure.
 */
function ssz_primary_menu_fallback() {
	$items = array(
		__( 'Men', 'swimshop-zimbabwe' )         => ssz_get_shop_url(),
		__( 'Women', 'swimshop-zimbabwe' )       => ssz_get_shop_url(),
		__( 'Kids', 'swimshop-zimbabwe' )        => ssz_get_shop_url(),
		__( 'Equipment / Accessories', 'swimshop-zimbabwe' ) => ssz_get_shop_url(),
		__( 'Brands', 'swimshop-zimbabwe' )      => ssz_get_brands_url(),
		__( 'New Arrivals', 'swimshop-zimbabwe' ) => ssz_get_shop_url(),
		__( 'Sale', 'swimshop-zimbabwe' )        => ssz_get_shop_url(),
	);

	echo '<ul class="ssz-primary-menu">';
	foreach ( $items as $label => $url ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
