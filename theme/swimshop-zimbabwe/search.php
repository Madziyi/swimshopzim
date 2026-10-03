<?php
/**
 * Search results.
 *
 * @package SwimShopZimbabwe
 */
get_header();

$ssz_search_query = get_search_query();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout ssz-search-results-page">
	<h1><?php echo esc_html( sprintf( __( 'Search results for “%s”', 'swimshop-zimbabwe' ), $ssz_search_query ) ); ?></h1>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'ssz-entry ssz-search-result' ); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<div class="ssz-archive-empty ssz-search-empty">
			<h2><?php echo esc_html( sprintf( __( 'NO RESULTS FOR “%s”', 'swimshop-zimbabwe' ), $ssz_search_query ) ); ?></h2>
			<p><?php esc_html_e( "We couldn't find any products matching your search.", 'swimshop-zimbabwe' ); ?></p>
			<form class="ssz-search-again" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="ssz-search-again-input" class="screen-reader-text"><?php esc_html_e( 'Search products again', 'swimshop-zimbabwe' ); ?></label>
				<input id="ssz-search-again-input" type="search" name="s" value="<?php echo esc_attr( $ssz_search_query ); ?>" placeholder="<?php esc_attr_e( 'Search products…', 'swimshop-zimbabwe' ); ?>">
				<input type="hidden" name="post_type" value="product">
				<button class="ssz-button ssz-button--primary" type="submit"><?php esc_html_e( 'Search products again', 'swimshop-zimbabwe' ); ?></button>
			</form>
			<a class="ssz-button ssz-button--secondary" href="<?php echo esc_url( function_exists( 'ssz_get_shop_url' ) ? ssz_get_shop_url() : home_url( '/' ) ); ?>"><?php esc_html_e( 'Shop all', 'swimshop-zimbabwe' ); ?></a>
		</div>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
