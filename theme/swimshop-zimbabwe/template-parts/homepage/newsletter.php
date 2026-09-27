<section class="ssz-newsletter ssz-home-section" data-homepage-newsletter>
	<div class="ssz-container ssz-newsletter__inner">
		<div>
			<p class="ssz-eyebrow"><?php esc_html_e( 'Stay connected', 'swimshop-zimbabwe' ); ?></p>
			<h2><?php echo esc_html( ssz_homepage_setting( 'newsletter_title' ) ); ?></h2>
			<p><?php echo esc_html( ssz_homepage_setting( 'newsletter_text' ) ); ?></p>
		</div>
		<form class="ssz-newsletter__form" action="" method="post" data-newsletter-form aria-describedby="ssz-newsletter-note">
			<label class="screen-reader-text" for="ssz-newsletter-email"><?php esc_html_e( 'Email address', 'swimshop-zimbabwe' ); ?></label>
			<input id="ssz-newsletter-email" type="email" name="email" placeholder="<?php esc_attr_e( 'Email address', 'swimshop-zimbabwe' ); ?>" autocomplete="email" disabled>
			<button class="ssz-button ssz-button--primary" type="button" disabled><?php esc_html_e( 'Sign up', 'swimshop-zimbabwe' ); ?></button>
			<p class="ssz-helper-text" id="ssz-newsletter-note"><?php echo esc_html( ssz_homepage_setting( 'newsletter_note' ) ); ?></p>
		</form>
	</div>
</section>
