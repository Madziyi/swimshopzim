<?php
/**
 * Site header.
 *
 * @package SwimShopZimbabwe
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ssz-skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'swimshop-zimbabwe' ); ?></a>

<div class="ssz-site-shell">
	<?php get_template_part( 'template-parts/header/announcement' ); ?>
	<?php get_template_part( 'template-parts/header/site-header' ); ?>
