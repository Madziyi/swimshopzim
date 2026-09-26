<?php
$title   = isset( $args['title'] ) ? $args['title'] : __( 'Featured Products', 'swimshop-zimbabwe' );
$orderby = isset( $args['orderby'] ) ? $args['orderby'] : 'date';
?>
<section class="ssz-section ssz-container">
	<div class="ssz-section-heading ssz-section-heading--inline">
		<h2><?php echo esc_html( $title ); ?></h2>
		<a class="ssz-text-link" href="<?php echo esc_url( ssz_get_shop_url() ); ?>"><?php esc_html_e( 'Shop all', 'swimshop-zimbabwe' ); ?></a>
	</div>
	<div class="ssz-home-products">
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<?php echo do_shortcode( '[products limit="4" columns="4" orderby="' . esc_attr( $orderby ) . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<p class="ssz-empty-state"><?php esc_html_e( 'Install and activate WooCommerce to populate this product section.', 'swimshop-zimbabwe' ); ?></p>
		<?php endif; ?>
	</div>
</section>
