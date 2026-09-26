<?php
/**
 * Accessibility helpers.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssz_skip_link_focus_fix() {
	// Reserved for browser-specific fixes if testing exposes a need.
}
add_action( 'wp_footer', 'ssz_skip_link_focus_fix', 99 );
