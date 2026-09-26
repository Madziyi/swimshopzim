<?php
/**
 * Page template.
 *
 * @package SwimShopZimbabwe
 */
get_header();
?>
<main id="primary" class="ssz-site-main ssz-container ssz-content-layout">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'ssz-entry' ); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="ssz-entry__content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
