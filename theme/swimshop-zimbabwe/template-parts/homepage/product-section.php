<?php
$section_dom_id = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'featured-products';
$title          = isset( $args['title'] ) ? $args['title'] : __( 'Featured products', 'swimshop-zimbabwe' );
$orderby        = isset( $args['orderby'] ) ? sanitize_key( $args['orderby'] ) : 'date';
$count          = isset( $args['count'] ) ? absint( $args['count'] ) : 4;
$count    = max( 1, min( 12, $count ) );
$products = '';

if ( class_exists( 'WooCommerce' ) ) {
	$products = do_shortcode( sprintf( '[products limit="%1$d" columns="4" orderby="%2$s" order="DESC"]', $count, esc_attr( $orderby ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
<section id="<?php echo esc_attr( $section_dom_id ); ?>" class="ssz-section ssz-container ssz-home-section ssz-home-section--products" data-homepage-product-section="<?php echo esc_attr( $section_dom_id ); ?>">
	<div class="ssz-section-heading ssz-section-heading--inline">
		<h2><?php echo esc_html( $title ); ?></h2>
		<a class="ssz-text-link" href="<?php echo esc_url( ssz_get_shop_url() ); ?>"><?php esc_html_e( 'Shop all', 'swimshop-zimbabwe' ); ?> <span aria-hidden="true">→</span></a>
	</div>
	<div class="ssz-home-products">
		<?php if ( $products ) : ?>
			<?php echo $products; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<p class="ssz-empty-state"><?php esc_html_e( 'Products will appear here as the catalogue is ready.', 'swimshop-zimbabwe' ); ?></p>
		<?php endif; ?>
	</div>
</section>
