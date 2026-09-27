<?php
$brands = ssz_get_brand_terms( false );
?>
<section class="ssz-section ssz-home-section ssz-home-section--brands" id="shop-the-brands" data-homepage-brands>
	<div class="ssz-container">
		<div class="ssz-section-heading ssz-section-heading--inline">
			<div>
				<p class="ssz-eyebrow"><?php esc_html_e( 'Multi-brand swimming retail', 'swimshop-zimbabwe' ); ?></p>
				<h2><?php esc_html_e( 'Shop the brands', 'swimshop-zimbabwe' ); ?></h2>
			</div>
			<a class="ssz-text-link" href="<?php echo esc_url( ssz_get_brands_url() ); ?>"><?php esc_html_e( 'View all brands', 'swimshop-zimbabwe' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $brands ) : ?>
			<div class="ssz-brand-grid" role="list">
				<?php foreach ( array_slice( $brands, 0, 8 ) as $brand ) : ?>
					<?php
					$link      = get_term_link( $brand );
					$image_url = ssz_get_brand_thumbnail_url( $brand->term_id );
					?>
					<a class="ssz-brand-card" role="listitem" href="<?php echo esc_url( is_wp_error( $link ) ? ssz_get_shop_url() : $link ); ?>">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>" loading="lazy">
						<?php else : ?>
							<span><?php echo esc_html( $brand->name ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="ssz-empty-state"><?php esc_html_e( 'Explore selected swimming brands in the store.', 'swimshop-zimbabwe' ); ?></p>
		<?php endif; ?>
	</div>
</section>
