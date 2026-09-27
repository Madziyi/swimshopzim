<?php
/**
 * Desktop primary navigation.
 *
 * @package SwimShopZimbabwe
 */
?>
<nav class="ssz-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'swimshop-zimbabwe' ); ?>" data-primary-nav>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'ssz-primary-menu',
			'fallback_cb'    => 'ssz_primary_menu_fallback',
			'depth'          => 3,
			'walker'         => new SSZ_Primary_Nav_Walker( 'desktop' ),
			'item_spacing'   => 'discard',
		)
	);
	?>
</nav>
