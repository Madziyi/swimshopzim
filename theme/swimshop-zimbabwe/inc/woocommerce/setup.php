<?php
/**
 * WooCommerce integration.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 720,
			'single_image_width'    => 1200,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'max_rows'        => 8,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'ssz_woocommerce_setup' );

function ssz_woocommerce_wrapper_start() {
	echo '<main id="primary" class="ssz-site-main ssz-container ssz-commerce-main">';
}

function ssz_woocommerce_wrapper_end() {
	echo '</main>';
}

function ssz_woocommerce_wrappers() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'ssz_woocommerce_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'ssz_woocommerce_wrapper_end', 10 );
}
add_action( 'wp', 'ssz_woocommerce_wrappers' );

function ssz_cart_count_fragment( $fragments ) {
	ob_start();
	?>
	<span class="ssz-cart-count" aria-label="<?php esc_attr_e( 'Items in cart', 'swimshop-zimbabwe' ); ?>"><?php echo esc_html( ssz_cart_count() ); ?></span>
	<?php
	$fragments['.ssz-cart-count'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'ssz_cart_count_fragment' );

function ssz_brand_feature_admin_notice() {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>' . esc_html__( 'SwimShop Zimbabwe is ready to use WooCommerce Product Brands, but the product_brand taxonomy is not currently available. Update/enable the current WooCommerce Brands feature before adding Arena, Spurt, Speedo, or other brands.', 'swimshop-zimbabwe' ) . '</p></div>';
}
add_action( 'admin_notices', 'ssz_brand_feature_admin_notice' );
