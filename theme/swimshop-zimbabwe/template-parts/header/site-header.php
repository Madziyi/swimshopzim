<header class="ssz-header" data-site-header>
	<div class="ssz-container ssz-header__inner">
		<button class="ssz-icon-button ssz-menu-toggle" type="button" aria-expanded="false" aria-controls="ssz-mobile-nav" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'swimshop-zimbabwe' ); ?></span>
			<span aria-hidden="true">☰</span>
		</button>

		<div class="ssz-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="ssz-wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">SWIMSHOP <span>ZIMBABWE</span></a>
			<?php endif; ?>
		</div>

		<nav class="ssz-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'swimshop-zimbabwe' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ssz-primary-menu',
					'fallback_cb'    => 'ssz_primary_menu_fallback',
					'depth'          => 3,
				)
			);
			?>
		</nav>

		<div class="ssz-header-actions">
			<button class="ssz-icon-button" type="button" aria-expanded="false" aria-controls="ssz-search-panel" data-search-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'swimshop-zimbabwe' ); ?></span>
				<span aria-hidden="true">⌕</span>
			</button>
			<a class="ssz-icon-button" href="<?php echo esc_url( ssz_get_account_url() ); ?>" aria-label="<?php esc_attr_e( 'My account', 'swimshop-zimbabwe' ); ?>">◯</a>
			<a class="ssz-cart-link" href="<?php echo esc_url( ssz_get_cart_url() ); ?>">
				<span><?php esc_html_e( 'Bag', 'swimshop-zimbabwe' ); ?></span>
				<span class="ssz-cart-count" aria-label="<?php esc_attr_e( 'Items in cart', 'swimshop-zimbabwe' ); ?>"><?php echo esc_html( ssz_cart_count() ); ?></span>
			</a>
		</div>
	</div>

	<div class="ssz-mobile-nav" id="ssz-mobile-nav" hidden data-mobile-nav>
		<div class="ssz-container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ssz-mobile-menu',
					'fallback_cb'    => 'ssz_primary_menu_fallback',
					'depth'          => 3,
				)
			);
			?>
		</div>
	</div>

	<div class="ssz-search-panel" id="ssz-search-panel" hidden data-search-panel>
		<div class="ssz-container ssz-search-panel__inner">
			<?php get_search_form(); ?>
			<button class="ssz-search-close" type="button" data-search-close><?php esc_html_e( 'Close', 'swimshop-zimbabwe' ); ?></button>
		</div>
	</div>
</header>
