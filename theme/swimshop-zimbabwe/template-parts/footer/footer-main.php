<footer class="ssz-footer">
	<div class="ssz-container ssz-footer__grid">
		<div>
			<a class="ssz-wordmark ssz-wordmark--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">SWIMSHOP <span>ZIMBABWE</span></a>
			<p><?php esc_html_e( 'Swimming and aquatic sports retail for Zimbabwe.', 'swimshop-zimbabwe' ); ?></p>
		</div>
		<div>
			<h2 class="ssz-footer__heading"><?php esc_html_e( 'Shop', 'swimshop-zimbabwe' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</div>
		<div>
			<h2 class="ssz-footer__heading"><?php esc_html_e( 'Support', 'swimshop-zimbabwe' ); ?></h2>
			<ul>
				<li><a href="#"><?php esc_html_e( 'Shipping & Delivery', 'swimshop-zimbabwe' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Returns', 'swimshop-zimbabwe' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Size Guide', 'swimshop-zimbabwe' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Contact', 'swimshop-zimbabwe' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="ssz-container ssz-footer__bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'SwimShop Zimbabwe', 'swimshop-zimbabwe' ); ?></span>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'legal',
				'container'      => false,
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
		?>
	</div>
</footer>
