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
	<?php get_template_part( 'template-parts/homepage/product-section', null, array( 'id' => 'new-arrivals', 'title' => ssz_homepage_setting( 'new_arrivals_heading' ), 'orderby' => 'date', 'count' => absint( ssz_homepage_setting( 'new_arrivals_count', 4 ) ) ) ); ?>
	<?php get_template_part( 'template-parts/homepage/brands' ); ?>
	<?php get_template_part( 'template-parts/homepage/category-grid' ); ?>
	<?php get_template_part( 'template-parts/homepage/product-section', null, array( 'id' => 'best-sellers', 'title' => ssz_homepage_setting( 'best_sellers_heading' ), 'orderby' => 'popularity', 'count' => absint( ssz_homepage_setting( 'best_sellers_count', 4 ) ) ) ); ?>
	<?php get_template_part( 'template-parts/homepage/activity' ); ?>
	<?php get_template_part( 'template-parts/homepage/campaign' ); ?>
	<?php get_template_part( 'template-parts/homepage/features' ); ?>
	<?php get_template_part( 'template-parts/homepage/proposition' ); ?>
	<?php get_template_part( 'template-parts/homepage/newsletter' ); ?>
</main>
<?php
get_footer();
