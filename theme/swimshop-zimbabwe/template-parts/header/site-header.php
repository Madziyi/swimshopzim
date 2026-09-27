<?php
/**
 * Site header.
 *
 * @package SwimShopZimbabwe
 */
?>
<header class="ssz-header" data-site-header>
	<div class="ssz-container ssz-header__inner">
		<button class="ssz-icon-button ssz-menu-toggle" type="button" aria-expanded="false" aria-controls="ssz-mobile-nav" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'swimshop-zimbabwe' ); ?></span>
			<?php echo ssz_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<div class="ssz-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="ssz-brand-lockup" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img class="ssz-brand-lockup__symbol" src="<?php echo esc_url( SSZ_THEME_URI . '/assets/images/brand/swimshop-logo-color.png' ); ?>" alt="">
					<span class="ssz-brand-lockup__text">
						<span>SWIMSHOP</span>
						<span>ZIMBABWE</span>
					</span>
				</a>
			<?php endif; ?>
		</div>

		<?php get_template_part( 'template-parts/header/desktop-navigation' ); ?>

		<div class="ssz-header-actions">
			<button class="ssz-icon-button" type="button" aria-expanded="false" aria-controls="ssz-search-panel" data-search-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'swimshop-zimbabwe' ); ?></span>
				<?php echo ssz_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<a class="ssz-account-link" href="<?php echo esc_url( ssz_get_account_url() ); ?>">
				<?php echo ssz_icon( 'account' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Account', 'swimshop-zimbabwe' ); ?></span>
			</a>

			<a class="ssz-cart-link" href="<?php echo esc_url( ssz_get_cart_url() ); ?>">
				<?php echo ssz_icon( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="ssz-cart-label"><?php esc_html_e( 'Bag', 'swimshop-zimbabwe' ); ?></span>
				<span class="ssz-cart-count" aria-label="<?php echo esc_attr( sprintf( __( '%d items in cart', 'swimshop-zimbabwe' ), ssz_cart_count() ) ); ?>"><?php echo esc_html( ssz_cart_count() ); ?></span>
			</a>
		</div>
	</div>

	<?php get_template_part( 'template-parts/header/mobile-navigation' ); ?>
	<?php get_template_part( 'template-parts/header/search-panel' ); ?>
</header>
