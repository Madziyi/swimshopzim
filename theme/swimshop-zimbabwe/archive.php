<?php
/**
 * Archive template.
 *
 * @package SwimShopZimbabwe
 */
get_header();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout">
	<header class="ssz-archive-header">
		<?php the_archive_title( '<h1>', '</h1>' ); ?>
		<?php the_archive_description( '<div>', '</div>' ); ?>
	</header>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'ssz-entry' ); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'swimshop-zimbabwe' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
