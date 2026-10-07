<?php
/**
 * WooCommerce archive framework.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is a product search.
 *
 * WooCommerce routes `?s=...&post_type=product` through its product archive
 * template, but it remains a WordPress search request. Keep that distinction
 * explicit so archive links preserve the query and the header can describe it.
 *
 * @return bool
 */
function ssz_is_product_search() {
	if ( ! function_exists( 'is_search' ) || ! is_search() ) {
		return false;
	}

	$post_type = get_query_var( 'post_type' );

	return 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) );
}

/**
 * Whether the current request is a classic WooCommerce product archive.
 *
 * @return bool
 */
function ssz_is_product_archive() {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ssz_is_product_search() );
}

/**
 * Add a stable body class for archive-only presentation refinements.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function ssz_archive_body_class( $classes ) {
	if ( ssz_is_product_archive() ) {
		$classes[] = 'ssz-product-archive';
	}

	if ( ssz_is_product_search() ) {
		$classes[] = 'ssz-product-search';
	}

	return $classes;
}
add_filter( 'body_class', 'ssz_archive_body_class' );

/**
 * Return the current archive type.
 *
 * @return string
 */
function ssz_archive_type() {
	if ( ssz_is_product_search() ) {
		return 'search';
	}

	if ( is_shop() ) {
		return 'shop';
	}

	$term = get_queried_object();

	if ( $term instanceof WP_Term && 'product_brand' === $term->taxonomy ) {
		return 'brand';
	}

	return 'category';
}

/**
 * Return the canonical URL for the current archive context.
 *
 * @return string
 */
function ssz_archive_base_url() {
	if ( ssz_is_product_search() ) {
		return home_url( '/' );
	}

	if ( is_shop() ) {
		return ssz_get_shop_url();
	}

	$term = get_queried_object();

	if ( $term instanceof WP_Term ) {
		$link = get_term_link( $term );

		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	return ssz_get_shop_url();
}

/**
 * Return a safe list of comma-separated or array query values.
 *
 * @param string $key Query key.
 * @param string $type Value type.
 * @return array
 */
function ssz_archive_query_values( $key, $type = 'slug' ) {
	if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return array();
	}

	$raw_values = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$raw_values = is_array( $raw_values ) ? $raw_values : explode( ',', (string) $raw_values );
	$values     = array();

	foreach ( $raw_values as $raw_value ) {
		if ( is_array( $raw_value ) ) {
			continue;
		}

		$value = 'id' === $type ? absint( $raw_value ) : sanitize_title( $raw_value );

		if ( '' !== (string) $value && ( 'id' !== $type || $value > 0 ) ) {
			$values[] = $value;
		}
	}

	return array_values( array_unique( $values ) );
}

/**
 * Return the approved archive filter state.
 *
 * @return array
 */
function ssz_archive_filters() {
	$filters = array(
		'filter_product_cat'   => ssz_archive_query_values( 'filter_product_cat' ),
		'filter_product_brand' => ssz_archive_query_values( 'filter_product_brand', 'id' ),
		'filter_size'          => ssz_archive_query_values( 'filter_size' ),
		'filter_colour'        => ssz_archive_query_values( 'filter_colour' ),
		'filter_stock_status'  => array(),
		'min_price'            => '',
		'max_price'            => '',
	);

	if ( isset( $_GET['filter_stock_status'] ) && 'instock' === sanitize_key( wp_unslash( $_GET['filter_stock_status'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters['filter_stock_status'] = array( 'instock' );
	}

	foreach ( array( 'min_price', 'max_price' ) as $price_key ) {
		if ( isset( $_GET[ $price_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$value = wc_format_decimal( wp_unslash( $_GET[ $price_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' !== $value && is_numeric( $value ) && $value >= 0 ) {
				$filters[ $price_key ] = $value;
			}
		}
	}

	return $filters;
}

/**
 * Return the current valid ordering value.
 *
 * @return string
 */
function ssz_archive_orderby() {
	if ( ! function_exists( 'wc_get_catalog_ordering_options' ) ) {
		return '';
	}

	$options = wc_get_catalog_ordering_options();
	$value   = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	return $value && isset( $options[ $value ] ) ? $value : '';
}

/**
 * Build a safe archive URL from the current filter state.
 *
 * @param array $filters Filter state.
 * @param bool  $sort    Preserve current valid sorting.
 * @return string
 */
function ssz_archive_build_url( $filters, $sort = true ) {
	$args = array();

	if ( ssz_is_product_search() ) {
		$args['s']         = get_search_query( false );
		$args['post_type'] = 'product';
	}

	foreach ( array( 'filter_product_cat', 'filter_product_brand', 'filter_size', 'filter_colour' ) as $key ) {
		if ( ! empty( $filters[ $key ] ) ) {
			$args[ $key ] = 'filter_product_brand' === $key
				? implode( ',', array_map( 'absint', $filters[ $key ] ) )
				: implode( ',', array_map( 'sanitize_title', $filters[ $key ] ) );
		}
	}

	if ( ! empty( $filters['filter_stock_status'] ) ) {
		$args['filter_stock_status'] = 'instock';
	}

	foreach ( array( 'min_price', 'max_price' ) as $key ) {
		if ( '' !== (string) $filters[ $key ] ) {
			$args[ $key ] = wc_format_decimal( $filters[ $key ] );
		}
	}

	if ( $sort && ssz_archive_orderby() ) {
		$args['orderby'] = ssz_archive_orderby();
	}

	return add_query_arg( $args, ssz_archive_base_url() );
}

/**
 * Return whether any non-sorting filter is active.
 *
 * @return bool
 */
function ssz_archive_has_active_filters() {
	$filters = ssz_archive_filters();

	foreach ( $filters as $key => $value ) {
		if ( is_array( $value ) && ! empty( $value ) ) {
			return true;
		}
		if ( is_string( $value ) && '' !== $value ) {
			return true;
		}
	}

	return false;
}

/**
 * Add a filter value to a copied state.
 *
 * @param array  $filters Current state.
 * @param string $key     Filter key.
 * @param mixed  $value   Value to remove.
 * @return array
 */
function ssz_archive_remove_filter_value( $filters, $key, $value ) {
	if ( isset( $filters[ $key ] ) && is_array( $filters[ $key ] ) ) {
		$filters[ $key ] = array_values( array_filter( $filters[ $key ], static function ( $item ) use ( $value ) {
			return (string) $item !== (string) $value;
		} ) );
	}

	return $filters;
}

/**
 * Return preferred top-level categories that actually exist.
 *
 * @return WP_Term[]
 */
function ssz_archive_preferred_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = array();
	foreach ( array( 'men', 'women', 'kids', 'goggles', 'equipment' ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && 0 === (int) $term->parent ) {
			$terms[ $term->term_id ] = $term;
		}
	}

	return array_values( $terms );
}

/**
 * Return the category navigation for the current archive.
 *
 * @return WP_Term[]
 */
function ssz_archive_category_navigation() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$term = get_queried_object();

	if ( in_array( ssz_archive_type(), array( 'shop', 'brand', 'search' ), true ) || ( $term instanceof WP_Term && 'product_brand' === $term->taxonomy ) ) {
		return ssz_archive_preferred_categories();
	}

	if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => $term->term_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_wp_error( $terms ) && $terms ) {
		return $terms;
	}

	if ( $term->parent ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => $term->parent,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		return is_wp_error( $terms ) ? array() : $terms;
	}

	return array();
}

/**
 * Return the archive header description.
 *
 * @return string
 */
function ssz_archive_description() {
	$term = get_queried_object();

	if ( $term instanceof WP_Term ) {
		return trim( (string) $term->description );
	}

	if ( is_shop() && ! ssz_is_product_search() ) {
		$shop_page = get_post( wc_get_page_id( 'shop' ) );
		return $shop_page ? trim( (string) $shop_page->post_excerpt ) : '';
	}

	return '';
}

/**
 * Render the shared archive header and category rail.
 *
 * @return void
 */
function ssz_render_archive_header() {
	$type = ssz_archive_type();
	$term = get_queried_object();
	if ( 'brand' === $type && function_exists( 'ssz_render_brand_storefront_header' ) ) {
		ssz_render_brand_storefront_header();
		return;
	}
	if ( 'search' === $type ) {
		$title = sprintf( __( 'Search results for “%s”', 'swimshop-zimbabwe' ), get_search_query() );
	} else {
		$title = is_shop() ? get_the_title( wc_get_page_id( 'shop' ) ) : ( $term instanceof WP_Term ? $term->name : __( 'Shop', 'swimshop-zimbabwe' ) );
	}
	$description = ssz_archive_description();
	$navigation = ssz_archive_category_navigation();

	echo '<header class="ssz-archive-header" data-archive-type="' . esc_attr( $type ) . '">';

	if ( 'brand' === $type && $term instanceof WP_Term ) {
		$brand_image = ssz_get_brand_thumbnail_url( $term->term_id );
		echo '<div class="ssz-archive-brand-identity">';
		if ( $brand_image ) {
			echo '<img class="ssz-archive-brand-identity__image" src="' . esc_url( $brand_image ) . '" alt="" loading="eager" decoding="async">';
		}
		echo '<span class="ssz-archive-brand-identity__name">' . esc_html( $title ) . '</span></div>';
	}

	echo '<h1 class="woocommerce-products-header__title page-title">' . esc_html( $title ) . '</h1>';

	if ( $description ) {
		echo '<div class="ssz-archive-header__description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
	}

	if ( $navigation ) {
		echo '<nav class="ssz-archive-category-nav" aria-label="' . esc_attr__( 'Shop categories', 'swimshop-zimbabwe' ) . '"><ul>';
		foreach ( $navigation as $nav_term ) {
			$is_current = $term instanceof WP_Term && 'product_cat' === $term->taxonomy && (int) $term->term_id === (int) $nav_term->term_id;
			$nav_link = get_term_link( $nav_term );
			if ( is_wp_error( $nav_link ) ) {
				continue;
			}
			echo '<li><a class="' . ( $is_current ? 'is-current' : '' ) . '" href="' . esc_url( $nav_link ) . '"' . ( $is_current ? ' aria-current="page"' : '' ) . '>' . esc_html( $nav_term->name ) . '</a></li>';
		}
		echo '</ul></nav>';
	}

	echo '</header>';
}

/**
 * Return filter term options for a taxonomy.
 *
 * @param string $taxonomy Taxonomy name.
 * @param int[]  $exclude  Term IDs to exclude.
 * @return WP_Term[]
 */
function ssz_archive_filter_terms( $taxonomy, $exclude = array() ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_values( array_filter( $terms, static function ( $term ) use ( $exclude ) {
		return ! in_array( (int) $term->term_id, array_map( 'absint', $exclude ), true );
	} ) );
}

/**
 * Return context-aware filter groups.
 *
 * @return array
 */
function ssz_archive_filter_groups() {
	$type   = ssz_archive_type();
	$term   = get_queried_object();
	$groups = array();

	if ( in_array( $type, array( 'shop', 'brand', 'search' ), true ) ) {
		$category_terms = ssz_archive_filter_terms( 'product_cat' );
		if ( $category_terms ) {
			$groups['filter_product_cat'] = array( 'label' => __( 'Category', 'swimshop-zimbabwe' ), 'type' => 'slug', 'terms' => $category_terms );
		}
	}

	if ( 'category' !== $type || ! $term instanceof WP_Term ) {
		$brand_exclude = 'brand' === $type && $term instanceof WP_Term ? array( $term->term_id ) : array();
		$brand_terms   = ssz_archive_filter_terms( 'product_brand', $brand_exclude );
		if ( $brand_terms ) {
			$groups['filter_product_brand'] = array( 'label' => __( 'Brand', 'swimshop-zimbabwe' ), 'type' => 'id', 'terms' => $brand_terms );
		}
	}

	foreach ( array( 'filter_size' => array( 'label' => __( 'Size', 'swimshop-zimbabwe' ), 'taxonomy' => 'pa_size' ), 'filter_colour' => array( 'label' => __( 'Colour', 'swimshop-zimbabwe' ), 'taxonomy' => 'pa_colour' ) ) as $key => $config ) {
		$terms = ssz_archive_filter_terms( $config['taxonomy'] );
		if ( $terms ) {
			$groups[ $key ] = array( 'label' => $config['label'], 'type' => 'slug', 'terms' => $terms );
		}
	}

	$groups['price'] = array( 'label' => __( 'Price', 'swimshop-zimbabwe' ), 'type' => 'price' );
	$groups['filter_stock_status'] = array( 'label' => __( 'Availability', 'swimshop-zimbabwe' ), 'type' => 'availability' );

	return $groups;
}

/**
 * Render one accessible filter accordion.
 *
 * @param string $key   Filter key.
 * @param array  $group Group configuration.
 * @param array  $state Current state.
 * @param int    $index Group index.
 * @return void
 */
function ssz_render_archive_filter_group( $key, $group, $state, $index ) {
	$panel_id = 'ssz-filter-panel-' . sanitize_html_class( $key ) . '-' . absint( $index );
	$open     = in_array( $key, array( 'filter_product_brand', 'filter_size' ), true );

	echo '<section class="ssz-filter-group" data-filter-group="' . esc_attr( $key ) . '">';
	echo '<h3 class="ssz-filter-group__heading"><button type="button" data-archive-accordion aria-expanded="' . ( $open ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $panel_id ) . '">' . esc_html( $group['label'] ) . '<span aria-hidden="true">' . ( $open ? '−' : '+' ) . '</span></button></h3>';
	echo '<div id="' . esc_attr( $panel_id ) . '" class="ssz-filter-group__panel"' . ( $open ? '' : ' hidden' ) . '>';

	if ( 'price' === $group['type'] ) {
		echo '<div class="ssz-filter-price"><label for="ssz-filter-min-price">' . esc_html__( 'Min', 'swimshop-zimbabwe' ) . '</label><input id="ssz-filter-min-price" type="number" min="0" step="0.01" name="min_price" value="' . esc_attr( $state['min_price'] ) . '"><label for="ssz-filter-max-price">' . esc_html__( 'Max', 'swimshop-zimbabwe' ) . '</label><input id="ssz-filter-max-price" type="number" min="0" step="0.01" name="max_price" value="' . esc_attr( $state['max_price'] ) . '"></div>';
	} elseif ( 'availability' === $group['type'] ) {
		$checked = ! empty( $state['filter_stock_status'] );
		echo '<label class="ssz-filter-option"><input type="checkbox" name="filter_stock_status" value="instock"' . checked( $checked, true, false ) . '><span>' . esc_html__( 'In stock', 'swimshop-zimbabwe' ) . '</span></label>';
	} else {
		$selected = isset( $state[ $key ] ) ? $state[ $key ] : array();
		foreach ( $group['terms'] as $term ) {
			$value   = 'id' === $group['type'] ? (string) $term->term_id : $term->slug;
			$checked = in_array( $value, array_map( 'strval', $selected ), true );
			echo '<label class="ssz-filter-option"><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $value ) . '" data-archive-filter-checkbox="' . esc_attr( $key ) . '"' . checked( $checked, true, false ) . '><span>' . esc_html( $term->name ) . '</span></label>';
		}
	}

	echo '</div></section>';
}

/**
 * Render active filter chips.
 *
 * @return void
 */
function ssz_render_archive_active_filters() {
	if ( ! ssz_archive_has_active_filters() ) {
		return;
	}

	$filters = ssz_archive_filters();
	$labels  = array();

	foreach ( array( 'filter_product_cat' => 'product_cat', 'filter_product_brand' => 'product_brand', 'filter_size' => 'pa_size', 'filter_colour' => 'pa_colour' ) as $key => $taxonomy ) {
		foreach ( $filters[ $key ] as $value ) {
			$term = 'filter_product_brand' === $key ? get_term( absint( $value ), $taxonomy ) : get_term_by( 'slug', $value, $taxonomy );
			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}
			$next = ssz_archive_remove_filter_value( $filters, $key, $value );
			$labels[] = '<a class="ssz-active-filter" href="' . esc_url( ssz_archive_build_url( $next ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Remove %s filter', 'swimshop-zimbabwe' ), $term->name ) ) . '">' . esc_html( $term->name ) . ' <span aria-hidden="true">×</span></a>';
		}
	}

	if ( ! empty( $filters['filter_stock_status'] ) ) {
		$next     = ssz_archive_remove_filter_value( $filters, 'filter_stock_status', 'instock' );
		$labels[] = '<a class="ssz-active-filter" href="' . esc_url( ssz_archive_build_url( $next ) ) . '" aria-label="' . esc_attr__( 'Remove In stock filter', 'swimshop-zimbabwe' ) . '">' . esc_html__( 'In stock', 'swimshop-zimbabwe' ) . ' <span aria-hidden="true">×</span></a>';
	}

	foreach ( array( 'min_price' => __( 'Min %s', 'swimshop-zimbabwe' ), 'max_price' => __( 'Max %s', 'swimshop-zimbabwe' ) ) as $key => $label ) {
		if ( '' === $filters[ $key ] ) {
			continue;
		}
		$next     = $filters;
		$next[ $key ] = '';
		$labels[] = '<a class="ssz-active-filter" href="' . esc_url( ssz_archive_build_url( $next ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Remove %s filter', 'swimshop-zimbabwe' ), sprintf( $label, $filters[ $key ] ) ) ) . '">' . esc_html( sprintf( $label, $filters[ $key ] ) ) . ' <span aria-hidden="true">×</span></a>';
	}

	if ( $labels ) {
		echo '<div class="ssz-active-filters" data-archive-active-filters><span class="ssz-active-filters__label">' . esc_html__( 'Active filters', 'swimshop-zimbabwe' ) . '</span><div class="ssz-active-filters__list">' . wp_kses_post( implode( '', $labels ) ) . '</div><a class="ssz-active-filters__clear" href="' . esc_url( ssz_archive_build_url( array( 'filter_product_cat' => array(), 'filter_product_brand' => array(), 'filter_size' => array(), 'filter_colour' => array(), 'filter_stock_status' => array(), 'min_price' => '', 'max_price' => '' ) ) ) . '">' . esc_html__( 'Clear all', 'swimshop-zimbabwe' ) . '</a></div>';
	}
}

/**
 * Render the result toolbar and filter drawer.
 *
 * @return void
 */
function ssz_render_archive_toolbar() {
	$total  = absint( wc_get_loop_prop( 'total' ) );
	$groups = ssz_archive_filter_groups();
	$drawer_id = 'ssz-archive-filter-drawer';

	echo '<div class="ssz-archive-toolbar" data-archive-toolbar><p class="ssz-archive-toolbar__count">' . esc_html( sprintf( _n( '%s product', '%s products', $total, 'swimshop-zimbabwe' ), number_format_i18n( $total ) ) ) . '</p><div class="ssz-archive-toolbar__actions"><button class="ssz-archive-toolbar__filter-button" type="button" data-archive-filters-toggle aria-expanded="false" aria-controls="' . esc_attr( $drawer_id ) . '">' . esc_html__( 'Filters', 'swimshop-zimbabwe' ) . '</button>';
	ob_start();
	woocommerce_catalog_ordering();
	$ordering = ob_get_clean();
	if ( ssz_is_product_search() ) {
		$ordering = preg_replace( '/(<input\s+type="hidden"\s+name="s")[^>]*>/', '<input type="hidden" name="s" value="' . esc_attr( get_search_query( false ) ) . '">', $ordering );
		$ordering = preg_replace( '/(<input\s+type="hidden"\s+name="post_type")[^>]*>/', '<input type="hidden" name="post_type" value="product">', $ordering );
	}
	echo $ordering . '</div></div>';

	ssz_render_archive_active_filters();

	echo '<div class="ssz-filter-drawer" data-archive-filter-shell><button type="button" class="ssz-filter-drawer__backdrop" data-archive-filter-backdrop aria-label="' . esc_attr__( 'Close filters', 'swimshop-zimbabwe' ) . '"></button><aside id="' . esc_attr( $drawer_id ) . '" class="ssz-filter-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="ssz-archive-filter-title" hidden><div class="ssz-filter-drawer__header"><h2 id="ssz-archive-filter-title">' . esc_html__( 'Filters', 'swimshop-zimbabwe' ) . '</h2><button type="button" class="ssz-filter-drawer__close" data-archive-filter-close aria-label="' . esc_attr__( 'Close filters', 'swimshop-zimbabwe' ) . '">×</button></div><form class="ssz-filter-form" method="get" action="' . esc_url( ssz_archive_base_url() ) . '" data-archive-filter-form>';
	if ( ssz_is_product_search() ) {
		echo '<input type="hidden" name="s" value="' . esc_attr( get_search_query( false ) ) . '"><input type="hidden" name="post_type" value="product">';
	}
	if ( ssz_archive_orderby() ) {
		echo '<input type="hidden" name="orderby" value="' . esc_attr( ssz_archive_orderby() ) . '">';
	}
	$state = ssz_archive_filters();
	$index = 0;
	foreach ( $groups as $key => $group ) {
		$index++;
		ssz_render_archive_filter_group( $key, $group, $state, $index );
	}
	echo '<div class="ssz-filter-drawer__footer"><a class="ssz-filter-drawer__clear" href="' . esc_url( ssz_archive_build_url( array( 'filter_product_cat' => array(), 'filter_product_brand' => array(), 'filter_size' => array(), 'filter_colour' => array(), 'filter_stock_status' => array(), 'min_price' => '', 'max_price' => '' ) ) ) . '">' . esc_html__( 'Clear all', 'swimshop-zimbabwe' ) . '</a><button class="ssz-button ssz-button--primary" type="submit">' . esc_html__( 'View products', 'swimshop-zimbabwe' ) . '</button></div></form></aside></div>';
}

/**
 * Add theme-owned category and availability filters to the main Woo query.
 *
 * @param array $tax_query Current tax query.
 * @return array
 */
function ssz_archive_product_query_tax_query( $tax_query ) {
	if ( ! ssz_is_product_archive() ) {
		return $tax_query;
	}

	$filters = ssz_archive_filters();

	if ( ! empty( $filters['filter_product_cat'] ) ) {
		$tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $filters['filter_product_cat'], 'operator' => 'IN', 'include_children' => false );
	}

	if ( ! empty( $filters['filter_product_brand'] ) ) {
		$tax_query[] = array( 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => $filters['filter_product_brand'], 'operator' => 'IN', 'include_children' => false );
	}

	foreach ( array( 'filter_size' => 'pa_size', 'filter_colour' => 'pa_colour' ) as $key => $taxonomy ) {
		if ( ! empty( $filters[ $key ] ) && taxonomy_exists( $taxonomy ) ) {
			$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $filters[ $key ], 'operator' => 'IN', 'include_children' => false );
		}
	}

	if ( ! empty( $filters['filter_stock_status'] ) && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$visibility = wc_get_product_visibility_term_ids();
		if ( ! empty( $visibility['outofstock'] ) ) {
			$tax_query[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array( $visibility['outofstock'] ), 'operator' => 'NOT IN' );
		}
	}

	return $tax_query;
}
add_filter( 'woocommerce_product_query_tax_query', 'ssz_archive_product_query_tax_query', 20 );

/**
 * Remove rating from visible catalog ordering and use storefront labels.
 *
 * @param array $options Ordering options.
 * @return array
 */
function ssz_archive_catalog_orderby( $options ) {
	unset( $options['rating'], $options['rating-desc'] );

	$labels = array(
		'menu_order' => __( 'Featured', 'swimshop-zimbabwe' ),
		'date'       => __( 'Newest', 'swimshop-zimbabwe' ),
		'popularity' => __( 'Popularity', 'swimshop-zimbabwe' ),
		'price'      => __( 'Price: Low to High', 'swimshop-zimbabwe' ),
		'price-desc' => __( 'Price: High to Low', 'swimshop-zimbabwe' ),
	);

	foreach ( $labels as $key => $label ) {
		if ( isset( $options[ $key ] ) ) {
			$options[ $key ] = $label;
		}
	}

	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'ssz_archive_catalog_orderby' );

/**
 * Render a filtered empty state while preserving archive context.
 *
 * @return void
 */
function ssz_archive_empty_state() {
	if ( ssz_is_product_search() ) {
		$query = get_search_query();
		echo '<div class="ssz-archive-empty ssz-search-empty" data-search-empty><h2>' . esc_html( sprintf( __( 'NO RESULTS FOR “%s”', 'swimshop-zimbabwe' ), $query ) ) . '</h2><p>' . esc_html__( "We couldn't find any products matching your search.", 'swimshop-zimbabwe' ) . '</p><form class="ssz-search-again" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"><label for="ssz-search-again-input" class="screen-reader-text">' . esc_html__( 'Search products again', 'swimshop-zimbabwe' ) . '</label><input id="ssz-search-again-input" type="search" name="s" value="' . esc_attr( $query ) . '" placeholder="' . esc_attr__( 'Search products…', 'swimshop-zimbabwe' ) . '"><input type="hidden" name="post_type" value="product"><button class="ssz-button ssz-button--primary" type="submit">' . esc_html__( 'Search products again', 'swimshop-zimbabwe' ) . '</button></form><a class="ssz-button ssz-button--secondary" href="' . esc_url( ssz_get_shop_url() ) . '">' . esc_html__( 'Shop all', 'swimshop-zimbabwe' ) . '</a></div>';
		return;
	}

	echo '<div class="ssz-archive-empty"><h2>' . esc_html__( 'No products found', 'swimshop-zimbabwe' ) . '</h2><p>' . esc_html__( 'Try removing one or more filters.', 'swimshop-zimbabwe' ) . '</p><a class="ssz-button ssz-button--primary" href="' . esc_url( ssz_archive_build_url( array( 'filter_product_cat' => array(), 'filter_product_brand' => array(), 'filter_size' => array(), 'filter_colour' => array(), 'filter_stock_status' => array(), 'min_price' => '', 'max_price' => '' ) ) ) . '">' . esc_html__( 'Clear filters', 'swimshop-zimbabwe' ) . '</a></div>';
}

/**
 * Configure archive hooks after the main query is available.
 *
 * @return void
 */
function ssz_configure_archive_hooks() {
	if ( ! ssz_is_product_archive() ) {
		return;
	}

	remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
	add_action( 'woocommerce_shop_loop_header', 'ssz_render_archive_header', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	add_action( 'woocommerce_before_shop_loop', 'ssz_render_archive_toolbar', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	if ( ssz_is_product_search() || ssz_archive_has_active_filters() ) {
		remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
		add_action( 'woocommerce_no_products_found', 'ssz_archive_empty_state' );
	}
}
add_action( 'wp', 'ssz_configure_archive_hooks', 20 );
