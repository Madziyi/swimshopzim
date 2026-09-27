<?php
/**
 * Header search panel.
 *
 * @package SwimShopZimbabwe
 */
?>
<div class="ssz-search-panel" id="ssz-search-panel" hidden data-search-panel>
	<div class="ssz-container ssz-search-panel__inner" role="search" aria-label="<?php esc_attr_e( 'Product search', 'swimshop-zimbabwe' ); ?>">
		<div class="ssz-search-panel__heading"><?php esc_html_e( 'Search products', 'swimshop-zimbabwe' ); ?></div>
		<?php get_search_form(); ?>
		<button class="ssz-icon-button ssz-search-close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'swimshop-zimbabwe' ); ?>" data-search-close>
			<?php echo ssz_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>
</div>
