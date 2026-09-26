<?php
$brands = ssz_get_brand_terms( false );
?>
<section class="ssz-section ssz-section--muted">
	<div class="ssz-container">
		<div class="ssz-section-heading ssz-section-heading--inline">
			<div>
				<p class="ssz-eyebrow"><?php esc_html_e( 'Trusted names in swimming', 'swimshop-zimbabwe' ); ?></p>
				<h2><?php esc_html_e( 'Shop by Brand', 'swimshop-zimbabwe' ); ?></h2>
			</div>
		</div>

		<div class="ssz-brand-grid">
			<?php if ( $brands ) : ?>
				<?php foreach ( array_slice( $brands, 0, 8 ) as $brand ) : ?>
					<?php
					$link      = get_term_link( $brand );
					$image_url = ssz_get_brand_thumbnail_url( $brand->term_id );
					?>
					<a class="ssz-brand-card" href="<?php echo esc_url( is_wp_error( $link ) ? ssz_get_shop_url() : $link ); ?>">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>" loading="lazy">
						<?php else : ?>
							<span><?php echo esc_html( $brand->name ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<?php foreach ( array( 'Arena', 'Spurt', 'Speedo' ) as $brand_name ) : ?>
					<div class="ssz-brand-card ssz-brand-card--placeholder"><span><?php echo esc_html( $brand_name ); ?></span></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
