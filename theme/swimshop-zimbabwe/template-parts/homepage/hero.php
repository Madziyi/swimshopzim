<?php
/** Homepage hero carousel. */

$slides = ssz_get_homepage_hero_slides();
$count  = count( $slides );
?>
<section class="ssz-hero ssz-hero--carousel" data-homepage-hero data-homepage-carousel data-carousel-interval="6000" data-slide-count="<?php echo esc_attr( $count ); ?>" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Homepage campaigns', 'swimshop-zimbabwe' ); ?>">
	<?php foreach ( $slides as $index => $slide ) : ?>
		<?php
		$is_initial  = 0 === $index;
		$slide_class = array( 'ssz-hero__slide', 'ssz-hero--align-' . $slide['alignment'] );
		$slide_class[] = $slide['image_id'] ? 'ssz-hero--has-image' : 'ssz-hero--fallback';
		$mobile_srcset = $slide['mobile_image_id'] ? wp_get_attachment_image_srcset( $slide['mobile_image_id'], 'full' ) : '';
		$heading_tag   = $is_initial ? 'h1' : 'h2';
		?>
		<article class="<?php echo esc_attr( implode( ' ', $slide_class ) ); ?>" data-carousel-slide data-slide-index="<?php echo esc_attr( $index ); ?>" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d of %d', 'swimshop-zimbabwe' ), $index + 1, $count ) ); ?>" aria-hidden="<?php echo $is_initial ? 'false' : 'true'; ?>"<?php echo $is_initial ? '' : ' hidden'; ?>>
			<?php if ( $slide['image_id'] ) : ?>
				<picture class="ssz-hero__media">
					<?php if ( $mobile_srcset ) : ?><source media="(max-width: 767px)" srcset="<?php echo esc_attr( $mobile_srcset ); ?>" sizes="100vw">
					<?php endif; ?>
					<?php echo wp_get_attachment_image( $slide['image_id'], 'full', false, array( 'class' => 'ssz-hero__image', 'fetchpriority' => $is_initial ? 'high' : 'auto', 'loading' => $is_initial ? 'eager' : 'lazy', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</picture>
			<?php else : ?><div class="ssz-hero__placeholder" aria-hidden="true"></div><?php endif; ?>

			<div class="ssz-container ssz-hero__content">
				<div class="ssz-hero__content-inner">
					<?php if ( $slide['eyebrow'] ) : ?><p class="ssz-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p><?php endif; ?>
					<<?php echo esc_html( $heading_tag ); ?>><?php echo esc_html( $slide['title'] ); ?></<?php echo esc_html( $heading_tag ); ?>>
					<?php if ( $slide['text'] ) : ?><p class="ssz-hero__copy"><?php echo esc_html( $slide['text'] ); ?></p><?php endif; ?>
					<?php if ( ( $slide['primary_label'] && $slide['primary_url'] ) || ( $slide['secondary_label'] && $slide['secondary_url'] ) ) : ?>
						<div class="ssz-hero__actions">
							<?php if ( $slide['primary_label'] && $slide['primary_url'] ) : ?><a class="ssz-button ssz-button--primary" href="<?php echo esc_url( $slide['primary_url'] ); ?>"><?php echo esc_html( $slide['primary_label'] ); ?></a><?php endif; ?>
							<?php if ( $slide['secondary_label'] && $slide['secondary_url'] ) : ?><a class="ssz-button ssz-button--light" href="<?php echo esc_url( $slide['secondary_url'] ); ?>"><?php echo esc_html( $slide['secondary_label'] ); ?></a><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</article>
	<?php endforeach; ?>
	<?php if ( $count > 1 ) : ?>
		<div class="ssz-container ssz-hero__dots" data-carousel-dots role="tablist" aria-label="<?php esc_attr_e( 'Choose homepage campaign', 'swimshop-zimbabwe' ); ?>">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<button type="button" class="ssz-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-carousel-dot data-slide-to="<?php echo esc_attr( $index ); ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show slide %d', 'swimshop-zimbabwe' ), $index + 1 ) ); ?>"><span aria-hidden="true"></span></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
