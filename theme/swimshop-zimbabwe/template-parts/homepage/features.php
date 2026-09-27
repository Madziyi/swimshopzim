<?php
$features = array(
	'race'     => array( 'title_key' => 'feature_race_title', 'text_key' => 'feature_race_text', 'label_key' => 'feature_race_cta_label', 'url_key' => 'feature_race_cta_url', 'image' => 'ssz_feature_race_image' ),
	'training' => array( 'title_key' => 'feature_training_title', 'text_key' => 'feature_training_text', 'label_key' => 'feature_training_cta_label', 'url_key' => 'feature_training_cta_url', 'image' => 'ssz_feature_training_image' ),
);
?>
<section class="ssz-section ssz-container ssz-home-section ssz-home-section--features" id="race-day-training" data-homepage-features>
	<div class="ssz-feature-grid">
		<?php foreach ( $features as $slug => $feature ) : ?>
			<?php
			$image_id = absint( get_theme_mod( $feature['image'], 0 ) );
			$url      = ssz_homepage_url_setting( $feature['url_key'], ssz_get_shop_url() );
			?>
			<a class="ssz-feature-panel<?php echo $image_id ? ' ssz-feature-panel--has-image' : ' ssz-feature-panel--fallback'; ?>" href="<?php echo esc_url( $url ); ?>">
				<?php if ( $image_id ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'ssz-campaign', false, array( 'class' => 'ssz-feature-panel__image', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span class="ssz-feature-panel__fallback" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="ssz-feature-panel__content">
					<span class="ssz-eyebrow"><?php echo esc_html( 'race' === $slug ? __( 'Race day', 'swimshop-zimbabwe' ) : __( 'Daily work', 'swimshop-zimbabwe' ) ); ?></span>
					<strong><?php echo esc_html( ssz_homepage_setting( $feature['title_key'] ) ); ?></strong>
					<span><?php echo esc_html( ssz_homepage_setting( $feature['text_key'] ) ); ?></span>
					<span class="ssz-feature-panel__cta"><?php echo esc_html( ssz_homepage_setting( $feature['label_key'] ) ); ?> <span aria-hidden="true">→</span></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
