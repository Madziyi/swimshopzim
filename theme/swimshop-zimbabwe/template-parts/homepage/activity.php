<?php
$activities = array(
	'racing'     => array( 'title' => ssz_homepage_setting( 'activity_racing_title' ), 'text' => ssz_homepage_setting( 'activity_racing_text' ) ),
	'training'   => array( 'title' => ssz_homepage_setting( 'activity_training_title' ), 'text' => ssz_homepage_setting( 'activity_training_text' ) ),
	'open_water' => array( 'title' => ssz_homepage_setting( 'activity_open_water_title' ), 'text' => ssz_homepage_setting( 'activity_open_water_text' ) ),
);
?>
<section class="ssz-section ssz-container ssz-home-section ssz-home-section--activity" id="shop-by-activity" data-homepage-activities>
	<div class="ssz-section-heading">
		<p class="ssz-eyebrow"><?php esc_html_e( 'Why are you swimming?', 'swimshop-zimbabwe' ); ?></p>
		<h2><?php esc_html_e( 'Shop by activity', 'swimshop-zimbabwe' ); ?></h2>
	</div>
	<div class="ssz-activity-grid">
		<?php foreach ( $activities as $slug => $activity ) : ?>
			<?php
			$image_id = absint( get_theme_mod( 'ssz_activity_' . $slug . '_image', 0 ) );
			$url      = ssz_homepage_url_setting( 'activity_' . $slug . '_url', ssz_get_shop_url() );
			?>
			<a class="ssz-activity-card<?php echo $image_id ? ' ssz-activity-card--has-image' : ' ssz-activity-card--fallback'; ?>" href="<?php echo esc_url( $url ); ?>">
				<?php if ( $image_id ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'ssz-campaign', false, array( 'class' => 'ssz-activity-card__image', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span class="ssz-activity-card__fallback" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="ssz-activity-card__content">
					<strong><?php echo esc_html( $activity['title'] ); ?></strong>
					<span><?php echo esc_html( $activity['text'] ); ?></span>
					<span class="ssz-activity-card__cta"><?php esc_html_e( 'Explore', 'swimshop-zimbabwe' ); ?> <span aria-hidden="true">→</span></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
