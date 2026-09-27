<?php
/**
 * Mobile drawer navigation.
 *
 * @package SwimShopZimbabwe
 */
?>
<div class="ssz-mobile-nav" id="ssz-mobile-nav" hidden data-mobile-nav aria-hidden="true">
	<button class="ssz-mobile-nav__backdrop" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'swimshop-zimbabwe' ); ?>" data-mobile-backdrop></button>
	<aside class="ssz-mobile-drawer" role="dialog" aria-modal="true" aria-labelledby="ssz-mobile-nav-title" data-mobile-drawer>
		<div class="ssz-mobile-drawer__header">
			<strong id="ssz-mobile-nav-title"><?php esc_html_e( 'Menu', 'swimshop-zimbabwe' ); ?></strong>
			<button class="ssz-icon-button" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'swimshop-zimbabwe' ); ?>" data-mobile-close>
				<?php echo ssz_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<div class="ssz-mobile-drawer__body">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ssz-mobile-menu',
					'menu_id'        => 'ssz-mobile-menu',
					'fallback_cb'    => 'ssz_primary_menu_fallback_mobile',
					'depth'          => 3,
					'walker'         => new SSZ_Primary_Nav_Walker( 'mobile' ),
					'item_spacing'   => 'discard',
				)
			);
			?>
		</div>

		<div class="ssz-mobile-drawer__footer">
			<a class="ssz-mobile-account" href="<?php echo esc_url( ssz_get_account_url() ); ?>">
				<?php echo ssz_icon( 'account' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'My account', 'swimshop-zimbabwe' ); ?></span>
			</a>
		</div>
	</aside>
</div>
