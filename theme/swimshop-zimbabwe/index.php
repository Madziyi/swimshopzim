<?php
/**
 * Main template.
 *
 * @package SwimShopZimbabwe
 */
get_header();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'ssz-entry' ); ?>>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'swimshop-zimbabwe' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
