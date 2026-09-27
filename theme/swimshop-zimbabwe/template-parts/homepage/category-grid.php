<?php
$categories = ssz_get_homepage_categories();
?>
<section class="ssz-section ssz-container ssz-home-section ssz-home-section--categories" id="shop-by-category" data-homepage-categories>
	<div class="ssz-section-heading">
		<p class="ssz-eyebrow"><?php esc_html_e( 'What are you shopping for?', 'swimshop-zimbabwe' ); ?></p>
		<h2><?php esc_html_e( 'Shop by category', 'swimshop-zimbabwe' ); ?></h2>
	</div>

	<?php if ( $categories ) : ?>
		<div class="ssz-category-grid">
			<?php foreach ( $categories as $index => $category ) : ?>
				<?php
				$link        = get_term_link( $category );
				$thumbnail_id = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );
				$card_class  = 0 === $index ? ' ssz-category-card--lead' : '';
				?>
				<a class="ssz-category-card<?php echo esc_attr( $card_class ); ?>" href="<?php echo esc_url( is_wp_error( $link ) ? ssz_get_shop_url() : $link ); ?>">
					<div class="ssz-category-card__media">
						<?php if ( $thumbnail_id ) : ?>
							<?php echo wp_get_attachment_image( $thumbnail_id, 'ssz-category-card', false, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<div class="ssz-media-placeholder" aria-hidden="true"></div>
						<?php endif; ?>
					</div>
					<span><?php echo esc_html( $category->name ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="ssz-empty-state"><?php esc_html_e( 'Explore the shop for swimwear, goggles and equipment.', 'swimshop-zimbabwe' ); ?></p>
	<?php endif; ?>
</section>
