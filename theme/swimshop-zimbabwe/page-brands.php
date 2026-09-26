<?php
/**
 * Brands directory page. Automatically used for a WordPress page with the slug "brands".
 *
 * @package SwimShopZimbabwe
 */
get_header();
$brands = ssz_get_brand_terms( false );
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout">
	<header class="ssz-archive-header ssz-section-heading">
		<p class="ssz-eyebrow"><?php esc_html_e( 'Shop by manufacturer', 'swimshop-zimbabwe' ); ?></p>
		<h1><?php esc_html_e( 'Brands', 'swimshop-zimbabwe' ); ?></h1>
		<p><?php esc_html_e( 'Browse swimming and aquatic-sport products by brand.', 'swimshop-zimbabwe' ); ?></p>
	</header>

	<?php if ( $brands ) : ?>
		<div class="ssz-brand-grid ssz-brand-grid--directory">
			<?php foreach ( $brands as $brand ) : ?>
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
		</div>
	<?php else : ?>
		<div class="ssz-empty-state">
			<p><?php esc_html_e( 'No brands have been added yet. Add Arena, Spurt, Speedo and other manufacturers under Products → Brands.', 'swimshop-zimbabwe' ); ?></p>
		</div>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
