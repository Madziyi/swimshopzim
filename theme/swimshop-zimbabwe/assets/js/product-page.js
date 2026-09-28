/* global jQuery */

( function ( $ ) {
	'use strict';

	const product = document.querySelector( '.ssz-product-page div.product' );

	if ( ! product ) {
		return;
	}

	const form = product.querySelector( 'form.variations_form' );
	const priceRegion = product.querySelector( '[data-ssz-product-price]' );
	const initialPrice = priceRegion ? priceRegion.innerHTML : '';

	product.classList.add( 'ssz-product-page--enhanced' );

	const getSelect = ( attributeName ) => form ? form.querySelector( `select[name="${ attributeName }"]` ) : null;

	const syncControl = ( control ) => {
		const attributeName = control.getAttribute( 'data-ssz-attribute' );
		const select = getSelect( attributeName );
		const selectedLabel = control.querySelector( '[data-ssz-selected-label]' );

		if ( ! select ) {
			return;
		}

		const selectedOption = select.options[ select.selectedIndex ];
		if ( selectedLabel ) {
			selectedLabel.textContent = selectedOption && selectedOption.value ? `— ${ selectedOption.textContent.trim() }` : '';
		}

		control.querySelectorAll( '[data-ssz-variation-option]' ).forEach( ( button ) => {
			const value = button.getAttribute( 'data-value' );
			const option = Array.from( select.options ).find( ( item ) => item.value === value );
			const selected = select.value === value;
			const disabled = ! option || option.disabled;

			button.disabled = disabled;
			button.setAttribute( 'aria-disabled', disabled ? 'true' : 'false' );
			button.setAttribute( 'aria-pressed', selected ? 'true' : 'false' );
		} );
	};

	const syncControls = () => {
		product.querySelectorAll( '[data-ssz-variation-control]' ).forEach( syncControl );
	};

	const syncPrice = ( variation ) => {
		if ( ! priceRegion ) {
			return;
		}

		if ( variation && variation.price_html ) {
			priceRegion.innerHTML = variation.price_html;
		} else {
			priceRegion.innerHTML = initialPrice;
		}
	};

	if ( form ) {
		product.querySelectorAll( '[data-ssz-variation-control]' ).forEach( ( control ) => {
			const attributeName = control.getAttribute( 'data-ssz-attribute' );
			const select = getSelect( attributeName );

			if ( ! select ) {
				return;
			}

			select.setAttribute( 'tabindex', '-1' );
			select.setAttribute( 'aria-hidden', 'true' );

			control.querySelectorAll( '[data-ssz-variation-option]' ).forEach( ( button ) => {
				button.addEventListener( 'click', () => {
					if ( button.disabled ) {
						return;
					}

					select.value = button.getAttribute( 'data-value' ) || '';
					select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
					syncControls();
				} );
			} );
		} );

		form.addEventListener( 'change', ( event ) => {
			if ( event.target.matches( 'select' ) ) {
				window.requestAnimationFrame( syncControls );
			}
		} );

		$( form )
			.on( 'found_variation.sszProductPage', ( event, variation ) => {
				syncPrice( variation );
				syncControls();
			} )
			.on( 'reset_data.sszProductPage hide_variation.sszProductPage', () => {
				syncPrice( null );
				syncControls();
			} )
			.on( 'woocommerce_update_variation_values.sszProductPage', syncControls );

		syncControls();
	}
} )( window.jQuery );
