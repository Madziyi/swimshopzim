<?php
$desktop_id = absint( get_theme_mod( 'ssz_hero_image', 0 ) );
$mobile_id  = absint( get_theme_mod( 'ssz_hero_image_mobile', 0 ) );
$alignment  = sanitize_html_class( get_theme_mod( 'ssz_hero_alignment', ssz_homepage_default( 'hero_alignment' ) ) );
$alignment  = in_array( $alignment, array( 'left', 'center' ), true ) ? $alignment : 'left';

$legacy_label       = get_theme_mod( 'ssz_hero_cta_label', '' );
$legacy_url         = get_theme_mod( 'ssz_hero_cta_url', '' );
$primary_label      = get_theme_mod( 'ssz_hero_primary_label', null );
$primary_url        = get_theme_mod( 'ssz_hero_primary_url', null );
$secondary_label    = ssz_homepage_setting( 'hero_secondary_label' );
$secondary_url      = ssz_homepage_url_setting( 'hero_secondary_url', ssz_get_shop_url() );
$primary_label      = $primary_label ? $primary_label : ( $legacy_label ? $legacy_label : ssz_homepage_default( 'hero_primary_label' ) );
$primary_url        = $primary_url ? $primary_url : ( $legacy_url ? $legacy_url : ssz_get_shop_url() );
$desktop_srcset     = $desktop_id ? wp_get_attachment_image_srcset( $desktop_id, 'full' ) : '';
$mobile_srcset      = $mobile_id ? wp_get_attachment_image_srcset( $mobile_id, 'full' ) : '';
$hero_classes       = array( 'ssz-hero', 'ssz-hero--align-' . $alignment );
$hero_classes[]     = $desktop_id ? 'ssz-hero--has-image' : 'ssz-hero--fallback';
?>
<section class="<?php echo esc_attr( implode( ' ', $hero_classes ) ); ?>" data-homepage-hero>
	<?php if ( $desktop_id ) : ?>
		<picture class="ssz-hero__media">
			<?php if ( $mobile_srcset ) : ?>
				<source media="(max-width: 767px)" srcset="<?php echo esc_attr( $mobile_srcset ); ?>" sizes="100vw">
			<?php endif; ?>
			<?php echo wp_get_attachment_image( $desktop_id, 'full', false, array( 'class' => 'ssz-hero__image', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</picture>
	<?php else : ?>
		<div class="ssz-hero__placeholder" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="ssz-container ssz-hero__content">
		<div class="ssz-hero__content-inner">
			<p class="ssz-eyebrow"><?php echo esc_html( ssz_homepage_setting( 'hero_eyebrow' ) ); ?></p>
			<h1><?php echo esc_html( ssz_homepage_setting( 'hero_title' ) ); ?></h1>
			<p class="ssz-hero__copy"><?php echo esc_html( ssz_homepage_setting( 'hero_text' ) ); ?></p>
			<div class="ssz-hero__actions">
				<a class="ssz-button ssz-button--primary" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_label ); ?></a>
				<a class="ssz-button ssz-button--light" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_label ); ?></a>
			</div>
		</div>
	</div>
</section>
