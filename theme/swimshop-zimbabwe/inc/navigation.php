<?php
/**
 * Navigation helpers and the shared WordPress menu walker.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a small theme-owned SVG icon.
 *
 * @param string $name Icon name.
 * @param string $class Optional additional class names.
 * @return string
 */
function ssz_icon( $name, $class = '' ) {
	$paths = array(
		'menu'    => '<path d="M4 7h16M4 12h16M4 17h16" />',
		'close'   => '<path d="m6 6 12 12M18 6 6 18" />',
		'search'  => '<circle cx="10.8" cy="10.8" r="6.3" /><path d="m16 16 4.3 4.3" />',
		'account' => '<circle cx="12" cy="8.2" r="3.1" /><path d="M5.5 20c.8-3.2 3.1-5 6.5-5s5.7 1.8 6.5 5" />',
		'bag'     => '<path d="M5.5 8.5h13l-1 11h-11z" /><path d="M9 8.5V7a3 3 0 0 1 6 0v1.5" />',
		'chevron' => '<path d="m7 9 5 5 5-5" />',
		'back'    => '<path d="M19 12H5M11 6l-6 6 6 6" />',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$classes = trim( 'ssz-icon ssz-icon--' . sanitize_html_class( $name ) . ' ' . $class );

	return '<svg class="' . esc_attr( $classes ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Escape an array of navigation attributes.
 *
 * @param array<string, string> $attributes Attributes.
 * @return string
 */
function ssz_nav_attributes( $attributes ) {
	$output = '';

	foreach ( $attributes as $name => $value ) {
		if ( '' === $value || null === $value ) {
			continue;
		}

		$output .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}

	return $output;
}

/**
 * Shared walker for desktop mega menus and mobile drill-down navigation.
 */
class SSZ_Primary_Nav_Walker extends Walker_Nav_Menu {
	/** @var string */
	private $context = 'desktop';

	/** @var array<int, int> */
	private $parent_ids = array();

	/** @var array<int, WP_Post> */
	private $parent_items = array();

	/** @var array<int, bool> */
	private $items_with_children = array();

	/**
	 * @param string $context Navigation rendering context.
	 */
	public function __construct( $context = 'desktop' ) {
		$this->context = in_array( $context, array( 'desktop', 'mobile' ), true ) ? $context : 'desktop';
	}

	/**
	 * Track child items explicitly so custom menu arguments cannot omit them.
	 *
	 * @param object   $element           Current menu item.
	 * @param array    $children_elements Child items keyed by parent ID.
	 * @param int      $max_depth         Maximum depth.
	 * @param int      $depth              Current depth.
	 * @param array    $args              Walker arguments.
	 * @param string   $output             Rendered output.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( $element ) {
			$element_id = (int) $element->{$this->db_fields['id']};
			$this->items_with_children[ $element_id ] = ! empty( $children_elements[ $element_id ] );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Start a submenu.
	 *
	 * @param string $output Used to append markup.
	 * @param int    $depth Menu depth.
	 * @param object $args Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$parent_id = isset( $this->parent_ids[ $depth ] ) ? (int) $this->parent_ids[ $depth ] : 0;
		$submenu_id = 'ssz-submenu-' . $parent_id . '-' . $this->context;
		$classes    = array( 'sub-menu' );

		if ( 'desktop' === $this->context && 0 === $depth ) {
			$classes[] = 'ssz-mega-menu';
		}

		if ( 'mobile' === $this->context && 0 === $depth ) {
			$classes[] = 'ssz-mobile-submenu';
		}

		if ( 'mobile' === $this->context && $depth > 0 ) {
			$classes[] = 'ssz-mobile-accordion-panel';
		}

		$attributes = array(
			'id'    => $submenu_id,
			'class' => implode( ' ', $classes ),
		);

		if ( 'mobile' === $this->context && 0 === $depth ) {
			$attributes['hidden']             = 'hidden';
			$attributes['data-mobile-panel']  = 'true';
			$attributes['data-mobile-parent'] = (string) $parent_id;
		}

		if ( 'mobile' === $this->context && $depth > 0 ) {
			$attributes['hidden']                    = 'hidden';
			$attributes['data-mobile-accordion-panel'] = 'true';
		}

		$output .= '<ul' . ssz_nav_attributes( $attributes ) . '>';

		if ( 'mobile' === $this->context && 0 === $depth ) {
			$parent = isset( $this->parent_items[ $depth ] ) ? $this->parent_items[ $depth ] : null;
			$title  = $parent ? wp_strip_all_tags( $parent->title ) : __( 'Menu', 'swimshop-zimbabwe' );

			$output .= '<li class="ssz-mobile-submenu__header">';
			$output .= '<button class="ssz-mobile-back" type="button" data-mobile-back>';
			$output .= ssz_icon( 'back' );
			$output .= '<span>' . esc_html__( 'Back', 'swimshop-zimbabwe' ) . '</span>';
			$output .= '</button>';
			$output .= '<a class="ssz-mobile-submenu__parent" href="' . esc_url( $parent ? $parent->url : home_url( '/' ) ) . '">' . esc_html( sprintf( __( 'Shop all %s', 'swimshop-zimbabwe' ), $title ) ) . '</a>';
			$output .= '</li>';
		}

		$output .= '<!-- ssz-submenu-items -->';
	}

	/**
	 * End a submenu.
	 *
	 * @param string $output Used to append markup.
	 * @param int    $depth Menu depth.
	 * @param object $args Menu arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	/**
	 * Start a menu item.
	 *
	 * @param string  $output Used to append markup.
	 * @param WP_Post $item Menu item.
	 * @param int     $depth Menu depth.
	 * @param object  $args Menu arguments.
	 * @param int     $id Menu item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$item_id      = (int) $item->ID;
		$has_children = ! empty( $this->items_with_children[ $item_id ] ) || ! empty( $args->has_children );
		$this->parent_ids[ $depth ]   = $item_id;
		$this->parent_items[ $depth ] = $item;

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'ssz-nav-item';
		$classes[] = 'ssz-nav-item--depth-' . (int) $depth;

		if ( $has_children ) {
			$classes[] = 'ssz-nav-item--has-children';
		}

		$classes = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth );
		$id_attr = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item_id, $item, $args, $depth );
		if ( empty( $id_attr ) ) {
			$id_attr = 'menu-item-' . $item_id;
		}
		$id_attr = '' !== $id_attr ? $id_attr . '-' . sanitize_html_class( $this->context ) : '';
		$output .= '<li' . ssz_nav_attributes( array( 'id' => $id_attr, 'class' => implode( ' ', $classes ) ) ) . '>';

		$submenu_id = 'ssz-submenu-' . $item_id . '-' . $this->context;
		$attributes = array(
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'href'   => ! empty( $item->url ) ? $item->url : '',
			'class'  => 'ssz-nav-link',
		);

		if ( ! empty( $item->current ) ) {
			$attributes['aria-current'] = 'page';
		}

		$attributes = apply_filters( 'nav_menu_link_attributes', $attributes, $item, $args, $depth );
		$title      = apply_filters( 'the_title', $item->title, $item->ID );
		$title      = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$output .= $args->before;
		$output .= '<a' . ssz_nav_attributes( $attributes ) . '>' . $args->link_before . esc_html( $title ) . $args->link_after . '</a>';

		if ( $has_children ) {
			$toggle_attributes = array(
				'type'            => 'button',
				'class'           => 'ssz-nav-toggle ssz-nav-toggle--' . $this->context,
				'aria-expanded'   => 'false',
				'aria-controls'   => $submenu_id,
				'aria-haspopup'   => 'true',
				'aria-label'      => sprintf( __( 'Open %s menu', 'swimshop-zimbabwe' ), wp_strip_all_tags( $title ) ),
				'data-nav-toggle'  => 'true',
			);

			if ( 'mobile' === $this->context && 0 === $depth ) {
				$toggle_attributes['data-mobile-drilldown'] = 'true';
			}

			if ( 'mobile' === $this->context && $depth > 0 ) {
				$toggle_attributes['data-mobile-accordion'] = 'true';
			}

			$output .= '<button' . ssz_nav_attributes( $toggle_attributes ) . '>';
			$output .= ssz_icon( 'chevron' );
			$output .= '<span class="screen-reader-text">' . esc_html( sprintf( __( 'Open %s menu', 'swimshop-zimbabwe' ), wp_strip_all_tags( $title ) ) ) . '</span>';
			$output .= '</button>';
		}

		$output .= $args->after;
	}
}

/**
 * Clean fallback navigation for a brand-new install.
 * Once a Primary Navigation menu is assigned, WordPress controls the structure.
 */
function ssz_primary_menu_fallback( $args = null, $context = 'desktop' ) {
	$items = array(
		__( 'Men', 'swimshop-zimbabwe' )          => ssz_get_shop_url(),
		__( 'Women', 'swimshop-zimbabwe' )        => ssz_get_shop_url(),
		__( 'Kids', 'swimshop-zimbabwe' )         => ssz_get_shop_url(),
		__( 'Equipment', 'swimshop-zimbabwe' )    => ssz_get_shop_url(),
		__( 'Brands', 'swimshop-zimbabwe' )       => ssz_get_brands_url(),
		__( 'New Arrivals', 'swimshop-zimbabwe' ) => ssz_get_shop_url(),
		__( 'Sale', 'swimshop-zimbabwe' )         => ssz_get_shop_url(),
	);

	$menu_class = 'mobile' === $context ? 'ssz-mobile-menu' : 'ssz-primary-menu';
	if ( is_object( $args ) && ! empty( $args->menu_class ) ) {
		$menu_class = sanitize_html_class( $args->menu_class );
	}

	echo '<ul class="' . esc_attr( $menu_class . ' ssz-primary-menu--fallback' ) . '">';
	foreach ( $items as $label => $url ) {
		printf( '<li class="ssz-nav-item"><a class="ssz-nav-link" href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Desktop fallback callback for wp_nav_menu().
 *
 * @param object|null $args Menu arguments when supplied by WordPress.
 */
function ssz_primary_menu_fallback_desktop( $args = null ) {
	ssz_primary_menu_fallback( $args, 'desktop' );
}

/**
 * Mobile fallback callback for wp_nav_menu().
 *
 * @param object|null $args Menu arguments when supplied by WordPress.
 */
function ssz_primary_menu_fallback_mobile( $args = null ) {
	ssz_primary_menu_fallback( $args, 'mobile' );
}
