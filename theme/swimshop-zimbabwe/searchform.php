<form role="search" method="get" class="ssz-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="ssz-search-field" class="screen-reader-text"><?php esc_html_e( 'Search products', 'swimshop-zimbabwe' ); ?></label>
	<input id="ssz-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products…', 'swimshop-zimbabwe' ); ?>">
	<?php if ( post_type_exists( 'product' ) ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
	<button type="submit"><?php esc_html_e( 'Search', 'swimshop-zimbabwe' ); ?></button>
</form>
