<?php
/**
 * Search results.
 *
 * @package SwimShopZimbabwe
 */
get_header();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout">
	<h1><?php printf( esc_html__( 'Search results for: %s', 'swimshop-zimbabwe' ), esc_html( get_search_query() ) ); ?></h1>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'ssz-entry' ); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php esc_html_e( 'No matching results.', 'swimshop-zimbabwe' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
