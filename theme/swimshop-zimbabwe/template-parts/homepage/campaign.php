<?php
$desktop_id = absint( get_theme_mod( 'ssz_campaign_image', 0 ) );
$mobile_id  = absint( get_theme_mod( 'ssz_campaign_image_mobile', 0 ) );
$mobile_srcset = $mobile_id ? wp_get_attachment_image_srcset( $mobile_id, 'full' ) : '';
$mobile_source = $mobile_srcset ? $mobile_srcset : ( $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'full' ) : '' );
$campaign_url = ssz_homepage_url_setting( 'campaign_cta_url', ssz_get_shop_url() );
?>
<section class="ssz-campaign ssz-home-section<?php echo $desktop_id ? ' ssz-campaign--has-image' : ' ssz-campaign--fallback'; ?>" data-homepage-campaign>
	<?php if ( $desktop_id ) : ?>
		<picture class="ssz-campaign__media">
			<?php if ( $mobile_source ) : ?>
				<source media="(max-width: 767px)" srcset="<?php echo esc_attr( $mobile_source ); ?>" sizes="100vw">
			<?php endif; ?>
			<?php echo wp_get_attachment_image( $desktop_id, 'full', false, array( 'class' => 'ssz-campaign__image', 'loading' => 'lazy', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</picture>
	<?php else : ?>
		<div class="ssz-campaign__media" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="ssz-container ssz-campaign__content">
		<p class="ssz-eyebrow"><?php echo esc_html( ssz_homepage_setting( 'campaign_eyebrow' ) ); ?></p>
		<h2><?php echo esc_html( ssz_homepage_setting( 'campaign_title' ) ); ?></h2>
		<p><?php echo esc_html( ssz_homepage_setting( 'campaign_text' ) ); ?></p>
		<a class="ssz-button ssz-button--light" href="<?php echo esc_url( $campaign_url ); ?>"><?php echo esc_html( ssz_homepage_setting( 'campaign_cta_label' ) ); ?></a>
	</div>
</section>
