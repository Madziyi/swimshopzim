<?php
/**
 * SwimShop Zimbabwe theme bootstrap.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SSZ_THEME_VERSION', '0.1.0' );
define( 'SSZ_THEME_DIR', get_template_directory() );
define( 'SSZ_THEME_URI', get_template_directory_uri() );

$ssz_includes = array(
	'/inc/setup.php',
	'/inc/enqueue.php',
	'/inc/helpers.php',
	'/inc/navigation.php',
	'/inc/homepage-settings.php',
	'/inc/accessibility.php',
	'/inc/woocommerce/setup.php',
	'/inc/woocommerce/product-loop.php',
);

foreach ( $ssz_includes as $ssz_file ) {
	require_once SSZ_THEME_DIR . $ssz_file;
}
