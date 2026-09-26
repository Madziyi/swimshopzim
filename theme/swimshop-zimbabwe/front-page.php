<?php
/**
 * Front page.
 *
 * @package SwimShopZimbabwe
 */

get_header();
?>
<main id="primary" class="ssz-site-main">
	<?php get_template_part( 'template-parts/homepage/hero' ); ?>
	<?php get_template_part( 'template-parts/homepage/category-grid' ); ?>
	<?php get_template_part( 'template-parts/homepage/brands' ); ?>
	<?php get_template_part( 'template-parts/homepage/product-section', null, array( 'title' => __( 'New Arrivals', 'swimshop-zimbabwe' ), 'orderby' => 'date' ) ); ?>
	<?php get_template_part( 'template-parts/homepage/campaign' ); ?>
	<?php get_template_part( 'template-parts/homepage/product-section', null, array( 'title' => __( 'Best Sellers', 'swimshop-zimbabwe' ), 'orderby' => 'popularity' ) ); ?>
	<?php get_template_part( 'template-parts/homepage/proposition' ); ?>
</main>
<?php
get_footer();
