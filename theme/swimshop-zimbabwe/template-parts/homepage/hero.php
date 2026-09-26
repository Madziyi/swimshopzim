<?php
$desktop_id = absint( get_theme_mod( 'ssz_hero_image', 0 ) );
$mobile_id  = absint( get_theme_mod( 'ssz_hero_image_mobile', 0 ) );
$cta_url    = get_theme_mod( 'ssz_hero_cta_url', '' );
$cta_url    = $cta_url ? $cta_url : ssz_get_shop_url();
?>
<section class="ssz-hero<?php echo $desktop_id ? ' ssz-hero--has-image' : ''; ?>">
	<?php if ( $desktop_id ) : ?>
		<picture class="ssz-hero__media">
			<?php if ( $mobile_id ) : ?>
				<source media="(max-width: 767px)" srcset="<?php echo esc_url( wp_get_attachment_image_url( $mobile_id, 'full' ) ); ?>">
			<?php endif; ?>
			<?php echo wp_get_attachment_image( $desktop_id, 'full', false, array( 'class' => 'ssz-hero__image', 'fetchpriority' => 'high' ) ); ?>
		</picture>
	<?php else : ?>
		<div class="ssz-hero__placeholder" aria-hidden="true"><span><?php esc_html_e( 'Replace with campaign photography', 'swimshop-zimbabwe' ); ?></span></div>
	<?php endif; ?>

	<div class="ssz-container ssz-hero__content">
		<p class="ssz-eyebrow"><?php echo esc_html( get_theme_mod( 'ssz_hero_eyebrow', __( 'SwimShop Zimbabwe', 'swimshop-zimbabwe' ) ) ); ?></p>
		<h1><?php echo esc_html( get_theme_mod( 'ssz_hero_title', __( 'SWIM FASTER. TRAIN STRONGER.', 'swimshop-zimbabwe' ) ) ); ?></h1>
		<p class="ssz-hero__copy"><?php echo esc_html( get_theme_mod( 'ssz_hero_text', __( 'Performance swimwear and equipment for training, racing and open water.', 'swimshop-zimbabwe' ) ) ); ?></p>
		<a class="ssz-button ssz-button--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( get_theme_mod( 'ssz_hero_cta_label', __( 'Shop Now', 'swimshop-zimbabwe' ) ) ); ?></a>
	</div>
</section>
