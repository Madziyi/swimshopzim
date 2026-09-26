<?php
$categories = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	$preferred_slugs = array( 'men', 'women', 'kids', 'goggles', 'equipment' );
	foreach ( $preferred_slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$categories[] = $term;
		}
	}

	if ( count( $categories ) < 5 ) {
		$fallback = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 5,
				'parent'     => 0,
			)
		);
		if ( ! is_wp_error( $fallback ) ) {
			$categories = array_slice( array_unique( array_merge( $categories, $fallback ), SORT_REGULAR ), 0, 5 );
		}
	}
}
?>
<section class="ssz-section ssz-container">
	<div class="ssz-section-heading">
		<p class="ssz-eyebrow"><?php esc_html_e( 'Find your lane', 'swimshop-zimbabwe' ); ?></p>
		<h2><?php esc_html_e( 'Shop by Category', 'swimshop-zimbabwe' ); ?></h2>
	</div>

	<div class="ssz-category-grid">
		<?php if ( $categories ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<?php
				$link = get_term_link( $category );
				$thumbnail_id = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );
				?>
				<a class="ssz-category-card" href="<?php echo esc_url( is_wp_error( $link ) ? ssz_get_shop_url() : $link ); ?>">
					<div class="ssz-category-card__media">
						<?php if ( $thumbnail_id ) : ?>
							<?php echo wp_get_attachment_image( $thumbnail_id, 'ssz-category-card', false, array( 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<div class="ssz-media-placeholder" aria-hidden="true"></div>
						<?php endif; ?>
					</div>
					<span><?php echo esc_html( $category->name ); ?></span>
				</a>
			<?php endforeach; ?>
		<?php else : ?>
			<?php foreach ( array( 'Men', 'Women', 'Kids', 'Goggles', 'Equipment' ) as $label ) : ?>
				<a class="ssz-category-card" href="<?php echo esc_url( ssz_get_shop_url() ); ?>">
					<div class="ssz-category-card__media"><div class="ssz-media-placeholder" aria-hidden="true"></div></div>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>
