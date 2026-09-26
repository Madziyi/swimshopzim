<?php
/**
 * 404 template.
 *
 * @package SwimShopZimbabwe
 */
get_header();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout ssz-empty-page">
	<p class="ssz-eyebrow">404</p>
	<h1><?php esc_html_e( 'That page is out of the lane.', 'swimshop-zimbabwe' ); ?></h1>
	<p><?php esc_html_e( 'The page may have moved or no longer exists.', 'swimshop-zimbabwe' ); ?></p>
	<a class="ssz-button ssz-button--primary" href="<?php echo esc_url( ssz_get_shop_url() ); ?>"><?php esc_html_e( 'Shop products', 'swimshop-zimbabwe' ); ?></a>
</main>
<?php get_footer(); ?>
