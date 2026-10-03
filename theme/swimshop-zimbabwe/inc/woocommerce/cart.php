<?php
/**
 * Cart and mini-cart presentation.
 *
 * WooCommerce remains authoritative for cart state, pricing, quantity rules,
 * totals and the Cart Block. The theme only owns the drawer presentation and
 * the branded Cart Block shell.
 *
 * @package SwimShopZimbabwe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current runtime can safely render WooCommerce cart UI.
 *
 * @return bool
 */
function ssz_cart_is_available() {
	return function_exists( 'WC' ) && WC()->cart && class_exists( 'WooCommerce' );
}

/**
 * Get the first native product brand for a product.
 *
 * @param WC_Product $product Product object.
 * @return string
 */
function ssz_cart_product_brand( $product ) {
	if ( ! $product || ! taxonomy_exists( 'product_brand' ) ) {
		return '';
	}

	$terms = get_the_terms( $product->get_id(), 'product_brand' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	return (string) $terms[0]->name;
}

/**
 * Render one theme-owned mini-cart line using WooCommerce cart data.
 *
 * @param string   $cart_item_key Cart item key.
 * @param array    $cart_item     Cart item data.
 * @param WC_Cart  $cart          Cart object.
 * @return void
 */
function ssz_render_mini_cart_item( $cart_item_key, $cart_item, $cart ) {
	$product = isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : false;
	if ( ! $product || ! $product->exists() || empty( $cart_item['quantity'] ) ) {
		return;
	}

	$display_product = $product;
	$parent_id = ! empty( $cart_item['variation_id'] ) ? wp_get_post_parent_id( absint( $cart_item['variation_id'] ) ) : 0;
	if ( ! $parent_id && $product->get_parent_id() ) {
		$parent_id = $product->get_parent_id();
	}
	if ( ! $parent_id && ! empty( $cart_item['product_id'] ) && $product->is_type( 'variation' ) ) {
		$parent_id = wp_get_post_parent_id( absint( $cart_item['product_id'] ) );
	}
	if ( $parent_id && function_exists( 'wc_get_product' ) ) {
		$parent_product = wc_get_product( $parent_id );
		if ( $parent_product ) {
			$display_product = $parent_product;
		}
	}

	$quantity_args = function_exists( 'wc_get_quantity_input_args' ) ? wc_get_quantity_input_args( array( 'input_value' => $cart_item['quantity'] ), $product ) : array();
	$min_value     = isset( $quantity_args['min_value'] ) ? $quantity_args['min_value'] : 1;
	$max_value     = isset( $quantity_args['max_value'] ) ? $quantity_args['max_value'] : '';
	$step          = isset( $quantity_args['step'] ) ? $quantity_args['step'] : 1;
	$product_name  = $display_product->get_name();
	if ( method_exists( $product, 'get_parent_data' ) ) {
		$parent_data = $product->get_parent_data();
		if ( ! empty( $parent_data['title'] ) ) {
			$product_name = $parent_data['title'];
		}
	}
	$product_url   = $display_product->is_visible() ? $display_product->get_permalink() : '';
	$remove_url    = wc_get_cart_remove_url( $cart_item_key );
	$brand         = ssz_cart_product_brand( $display_product );
	$variation     = array();
	$variation_attributes = ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ? $cart_item['variation'] : ( $product->is_type( 'variation' ) ? $product->get_variation_attributes() : array() );
	if ( ! empty( $variation_attributes ) ) {
		foreach ( $variation_attributes as $attribute_name => $attribute_value ) {
			if ( '' === (string) $attribute_value ) {
				continue;
			}

			$taxonomy = str_replace( 'attribute_', '', $attribute_name );
			$term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', sanitize_title( $attribute_value ), $taxonomy ) : false;
			$variation[] = $term && ! is_wp_error( $term ) ? $term->name : wc_clean( $attribute_value );
		}
	}
	$variation = implode( ' / ', $variation );
	if ( ! $variation && ( $product->is_type( 'variation' ) || ! empty( $cart_item['variation_id'] ) ) ) {
		$separator = strrpos( $product_name, ' - ' );
		if ( false !== $separator ) {
			$variation    = trim( substr( $product_name, $separator + 3 ) );
			$product_name = trim( substr( $product_name, 0, $separator ) );
		}
	}
	?>
	<li class="ssz-mini-cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
		<div class="ssz-mini-cart-item__media">
			<?php if ( $product_url ) : ?><a href="<?php echo esc_url( $product_url ); ?>" tabindex="-1"><?php endif; ?>
			<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'ssz-mini-cart-item__image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $product_url ) : ?></a><?php endif; ?>
		</div>
		<div class="ssz-mini-cart-item__details">
			<?php if ( $brand ) : ?><span class="ssz-mini-cart-item__brand"><?php echo esc_html( $brand ); ?></span><?php endif; ?>
			<?php if ( $product_url ) : ?>
				<a class="ssz-mini-cart-item__title" href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a>
			<?php else : ?>
				<span class="ssz-mini-cart-item__title"><?php echo esc_html( $product_name ); ?></span>
			<?php endif; ?>
			<?php if ( $variation ) : ?><div class="ssz-mini-cart-item__variation"><?php echo wp_kses_post( $variation ); ?></div><?php endif; ?>
			<div class="ssz-mini-cart-item__price"><?php echo wp_kses_post( $cart->get_product_price( $product ) ); ?></div>
			<div class="ssz-mini-cart-item__controls">
				<div class="ssz-quantity-control" data-cart-quantity-control>
					<button class="ssz-quantity-control__button" type="button" data-cart-action="decrease" aria-label="<?php echo esc_attr( sprintf( __( 'Decrease quantity of %s', 'swimshop-zimbabwe' ), $product_name ) ); ?>" <?php disabled( (float) $cart_item['quantity'] <= (float) $min_value ); ?>>−</button>
					<input class="ssz-quantity-control__input" type="number" inputmode="numeric" name="ssz_cart_quantity[<?php echo esc_attr( $cart_item_key ); ?>]" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="<?php echo esc_attr( $min_value ); ?>" <?php echo '' !== (string) $max_value ? 'max="' . esc_attr( $max_value ) . '"' : ''; ?> step="<?php echo esc_attr( $step ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Quantity of %s in your bag', 'swimshop-zimbabwe' ), $product_name ) ); ?>" data-cart-quantity-input>
					<button class="ssz-quantity-control__button" type="button" data-cart-action="increase" aria-label="<?php echo esc_attr( sprintf( __( 'Increase quantity of %s', 'swimshop-zimbabwe' ), $product_name ) ); ?>" <?php disabled( '' !== (string) $max_value && (float) $cart_item['quantity'] >= (float) $max_value ); ?>>+</button>
				</div>
				<a class="ssz-mini-cart-item__remove" href="<?php echo esc_url( $remove_url ); ?>" data-cart-action="remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"><?php esc_html_e( 'Remove', 'swimshop-zimbabwe' ); ?></a>
			</div>
		</div>
	</li>
	<?php
}

/**
 * Render the stable fragment wrapper used by the global mini-cart drawer.
 *
 * @return void
 */
function ssz_render_mini_cart_content() {
	if ( ! ssz_cart_is_available() ) {
		return;
	}

	$cart = WC()->cart;
	?>
	<div data-mini-cart-content>
		<?php if ( $cart->is_empty() ) : ?>
			<div class="ssz-mini-cart-empty">
				<p><?php esc_html_e( 'Your bag is empty.', 'swimshop-zimbabwe' ); ?></p>
				<a class="ssz-button ssz-button--secondary" href="<?php echo esc_url( ssz_get_shop_url() ); ?>"><?php esc_html_e( 'Start shopping', 'swimshop-zimbabwe' ); ?></a>
			</div>
		<?php else : ?>
			<ul class="ssz-mini-cart-items" aria-label="<?php esc_attr_e( 'Products in your bag', 'swimshop-zimbabwe' ); ?>">
				<?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) : ?>
					<?php ssz_render_mini_cart_item( $cart_item_key, $cart_item, $cart ); ?>
				<?php endforeach; ?>
			</ul>

			<div class="ssz-mini-cart-summary">
				<div class="ssz-mini-cart-summary__row">
					<span><?php esc_html_e( 'Subtotal', 'swimshop-zimbabwe' ); ?></span>
					<strong data-mini-cart-subtotal><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></strong>
				</div>
				<?php if ( $cart->needs_shipping() || ( function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() ) ) : ?>
					<p class="ssz-mini-cart-summary__note"><?php esc_html_e( 'Shipping and taxes calculated at checkout.', 'swimshop-zimbabwe' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="ssz-mini-cart-actions">
				<a class="ssz-button ssz-button--primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'swimshop-zimbabwe' ); ?></a>
				<a class="ssz-button ssz-button--secondary" href="<?php echo esc_url( ssz_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'swimshop-zimbabwe' ); ?></a>
				<button class="ssz-mini-cart-continue" type="button" data-cart-close><?php esc_html_e( 'Continue shopping', 'swimshop-zimbabwe' ); ?></button>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the global mini-cart drawer shell.
 *
 * @return void
 */
function ssz_render_mini_cart_drawer() {
	if ( ! ssz_cart_is_available() ) {
		return;
	}

	$auto_open = false;
	if ( function_exists( 'WC' ) && WC()->session ) {
		$auto_open = (bool) WC()->session->get( 'ssz_cart_auto_open', false );
		WC()->session->set( 'ssz_cart_auto_open', false );
	}
	?>
	<div id="ssz-mini-cart" class="ssz-mini-cart" data-cart-drawer hidden aria-hidden="true" data-auto-open="<?php echo $auto_open ? 'true' : 'false'; ?>">
		<button class="ssz-mini-cart__backdrop" type="button" data-cart-backdrop aria-label="<?php esc_attr_e( 'Close your bag', 'swimshop-zimbabwe' ); ?>"></button>
		<aside class="ssz-mini-cart__panel" role="dialog" aria-modal="true" aria-labelledby="ssz-mini-cart-title" data-cart-panel tabindex="-1">
			<div class="ssz-mini-cart__header">
				<h2 id="ssz-mini-cart-title"><?php esc_html_e( 'Your bag', 'swimshop-zimbabwe' ); ?></h2>
				<button class="ssz-icon-button ssz-mini-cart__close" type="button" data-cart-close aria-label="<?php esc_attr_e( 'Close your bag', 'swimshop-zimbabwe' ); ?>">×</button>
			</div>
			<p class="ssz-mini-cart__status" data-cart-status role="status" aria-live="polite" hidden></p>
			<?php ssz_render_mini_cart_content(); ?>
		</aside>
	</div>
	<?php
}

/**
 * Add the drawer content to WooCommerce's existing fragment refresh response.
 *
 * @param array $fragments Existing fragments.
 * @return array
 */
function ssz_cart_fragments( $fragments ) {
	if ( ! ssz_cart_is_available() ) {
		return $fragments;
	}

	ob_start();
	ssz_render_mini_cart_content();
	$fragments['[data-mini-cart-content]'] = ob_get_clean();

	ob_start();
	$count = ssz_cart_count();
	?>
	<span class="ssz-cart-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $count, 'swimshop-zimbabwe' ), $count ) ); ?>"><?php echo esc_html( $count ); ?></span>
	<?php
	$fragments['.ssz-cart-count'] = ob_get_clean();

	if ( ssz_cart_is_available() ) {
		ob_start();
		echo '<div class="ssz-cart-block-subtotal" data-cart-page-subtotal' . ( WC()->cart->is_empty() ? ' hidden' : '' ) . '><div class="ssz-cart-block-subtotal__label">' . esc_html__( 'Subtotal', 'swimshop-zimbabwe' ) . '</div><div class="ssz-cart-block-subtotal__value">' . ( WC()->cart->is_empty() ? '' : wp_kses_post( WC()->cart->get_cart_subtotal() ) ) . '</div></div>';
		$fragments['[data-cart-page-subtotal]'] = ob_get_clean();
	}

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'ssz_cart_fragments' );

/**
 * Flag successful normal single-product submissions for progressive enhancement.
 *
 * @param string $cart_item_key Cart item key.
 * @param int    $product_id    Product ID.
 * @param int    $quantity      Quantity.
 * @param int    $variation_id  Variation ID.
 * @param array  $variation     Variation data.
 * @param array  $cart_item_data Cart item data.
 * @return void
 */
function ssz_cart_flag_auto_open( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( wp_doing_ajax() || isset( $_REQUEST['wc-ajax'] ) || empty( $_POST['add-to-cart'] ) || 'product' !== get_post_type( $product_id ) ) {
		return;
	}

	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( 'ssz_cart_auto_open', true );
	}
}
add_action( 'woocommerce_add_to_cart', 'ssz_cart_flag_auto_open', 20, 6 );

/**
 * Add a branded empty state alongside the real Cart Block.
 *
 * The Cart Block stays in place and remains the authority. This small sibling
 * state is toggled by cart.js when the Block changes from filled to empty.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block data.
 * @return string
 */
function ssz_cart_block_empty_state( $block_content, $block ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! ssz_cart_is_available() ) {
		return $block_content;
	}

	$hidden = WC()->cart->is_empty() ? '' : ' hidden';
	$state  = '<section class="ssz-cart-empty-state" data-ssz-cart-empty-state' . $hidden . ' aria-live="polite"><h2>' . esc_html__( 'Your cart is empty', 'swimshop-zimbabwe' ) . '</h2><p>' . esc_html__( "Looks like you haven't added anything yet.", 'swimshop-zimbabwe' ) . '</p><a class="ssz-button ssz-button--primary" href="' . esc_url( ssz_get_shop_url() ) . '">' . esc_html__( 'Shop all', 'swimshop-zimbabwe' ) . '</a></section>';

	return $block_content . $state;
}
add_filter( 'render_block_woocommerce/cart', 'ssz_cart_block_empty_state', 10, 2 );

/**
 * Add a server-rendered body state so the native Cart Block empty message can
 * be suppressed alongside the theme-owned branded empty state.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function ssz_cart_body_class( $classes ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! ssz_cart_is_available() || ! WC()->cart->is_empty() ) {
		return $classes;
	}

	$classes[] = 'ssz-cart-has-branded-empty';
	return $classes;
}
add_filter( 'body_class', 'ssz_cart_body_class' );
