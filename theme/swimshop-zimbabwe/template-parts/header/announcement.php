<?php
$announcement = get_theme_mod( 'ssz_announcement', __( 'Performance swimwear and equipment for every lane.', 'swimshop-zimbabwe' ) );
if ( ! $announcement ) {
	return;
}
?>
<div class="ssz-announcement" role="region" aria-label="<?php esc_attr_e( 'Store announcement', 'swimshop-zimbabwe' ); ?>">
	<div class="ssz-container"><?php echo esc_html( $announcement ); ?></div>
</div>
