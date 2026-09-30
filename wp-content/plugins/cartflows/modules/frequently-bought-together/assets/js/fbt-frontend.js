/* global wcf_fbt_frontend */
/**
 * Frequently Bought Together — Frontend widget.
 *
 * Vertical checkbox list (Config B). Recalculates total + count on selection or
 * qty change, enforces single-select mode when set, and submits the bundle to
 * admin-ajax (wcf_fbt_add_to_cart).
 */
( function ( $ ) {
	'use strict';

	/**
	 * Boot every FBT widget on the page.
	 */
	function wcf_fbt_init() {
		$( '.wcf-fbt-widget' ).each( function () {
			wcf_fbt_bind( $( this ) );
		} );
	}

	/**
	 * Wire event handlers on a single widget and run an initial recalc.
	 *
	 * @param {jQuery} $widget The widget root.
	 */
	function wcf_fbt_bind( $widget ) {
		const is_single = $widget.hasClass( 'is-single' );

		$widget.on(
			'change',
			'input[type="checkbox"]:not(:disabled)',
			function () {
				if ( is_single && this.checked ) {
					$widget
						.find( 'input[type="checkbox"]:not(:disabled)' )
						.not( this )
						.prop( 'checked', false );
				}
				wcf_fbt_recalc( $widget );
			}
		);

		$widget.on( 'input', '.wcf-fbt-widget-qty', function () {
			wcf_fbt_recalc( $widget );
		} );

		// The main row's qty lives in WC's own form — keep the total in sync with it.
		$( 'form.cart' ).on(
			'change input',
			'input[name="quantity"]',
			function () {
				wcf_fbt_recalc( $widget );
			}
		);

		// Variable main product: mirror the chosen variation into the row's canonical data-price store.
		const $main_box = $widget.find(
			'.wcf-fbt-widget-row.is-main input[type="checkbox"]'
		);
		const main_base_price =
			parseFloat( $main_box.attr( 'data-price' ) ) || 0;
		$( 'form.variations_form' ).on(
			'found_variation reset_data hide_variation',
			function ( event, variation ) {
				$main_box.attr(
					'data-price',
					( variation && parseFloat( variation.display_price ) ) ||
						main_base_price
				);
				wcf_fbt_recalc( $widget );
			}
		);

		// Gift cards and name-your-price products fire no found_variation, so follow WC's
		// price element. Watching the element ignores when each add-on binds its handlers.
		const price_el = wcf_fbt_get_price_element();
		if ( price_el ) {
			new window.MutationObserver( function () {
				wcf_fbt_sync_main_price( $widget );
			} ).observe( price_el, {
				childList: true,
				characterData: true,
				subtree: true,
			} );
			wcf_fbt_sync_main_price( $widget );
		}

		$widget.on( 'change', '.wcf-fbt-widget-variation-select', function () {
			wcf_fbt_recalc( $widget );
		} );

		$widget.on( 'click', '.wcf-fbt-widget-add', function ( e ) {
			e.preventDefault();
			wcf_fbt_submit( $widget );
		} );

		$widget.on( 'click', '.wcf-fbt-widget-toggle', function () {
			const collapsed = $widget
				.toggleClass( 'is-collapsed' )
				.hasClass( 'is-collapsed' );
			const extras = parseInt( this.dataset.extras, 10 ) || 0;
			this.textContent = collapsed
				? wcf_fbt_frontend.i18n.show_more.replace( '%d', extras )
				: wcf_fbt_frontend.i18n.show_fewer;
		} );

		wcf_fbt_recalc( $widget );
	}

	/**
	 * Read one row's full state: checkbox, quantity, and — for variable rows —
	 * the variation select. Single source of truth for recalc and submit.
	 *
	 * @param {jQuery} $row The .wcf-fbt-widget-row element.
	 * @return {Object} { checked, disabled, id, qty, price, unresolved }
	 */
	function wcf_fbt_row_state( $row ) {
		const $box = $row.find( 'input[type="checkbox"]' );
		const $qty = $row.find( '.wcf-fbt-widget-qty' );
		const $select = $row
			.closest( '.wcf-fbt-widget-row-group' )
			.find( '.wcf-fbt-widget-variation-select' );

		const state = {
			checked: $box.prop( 'checked' ),
			disabled: $box.prop( 'disabled' ),
			id: parseInt( $box.val(), 10 ) || 0,
			qty: $qty.length
				? Math.max( 1, parseInt( $qty.val(), 10 ) || 1 )
				: 1,
			price: parseFloat( $box.attr( 'data-price' ) ) || 0,
			unresolved: false,
		};

		// Main row qty comes from WC's quantity input — the same source submit sends as source_qty.
		if ( $row.hasClass( 'is-main' ) ) {
			state.qty = Math.max(
				1,
				parseInt(
					$( 'form.cart' ).find( 'input[name="quantity"]' ).val(),
					10
				) || 1
			);
		}

		if ( $select.length ) {
			state.id = parseInt( $select.val(), 10 ) || 0;
			state.unresolved = ! state.id;
			state.price = state.id
				? parseFloat( $select.find( ':selected' ).data( 'price' ) ) || 0
				: 0;
		}

		return state;
	}

	/**
	 * The source product's own price element, on block and classic themes alike.
	 * Block themes render `.wp-block-woocommerce-product-price` with no `.price` class.
	 *
	 * @return {HTMLElement|null} The price element, or null when none matches.
	 */
	function wcf_fbt_get_price_element() {
		// Mini-cart lines, product grids, and this widget carry price nodes of their own.
		const excluded = [
			'.wp-block-woocommerce-mini-cart-contents',
			'.wc-block-grid',
			'.wc-block-product-template',
			'.related',
			'.up-sells',
			'.cross-sells',
			'.wcf-fbt-widget',
		].join( ', ' );

		const selectors = [
			'.wp-block-woocommerce-product-price',
			'.product .summary .price',
			'.product .entry-summary .price',
			'.product .price',
		];

		for ( let i = 0; i < selectors.length; i++ ) {
			const $found = $( selectors[ i ] ).filter( function () {
				return ! $( this ).closest( excluded ).length;
			} );
			if ( $found.length ) {
				return $found.first()[ 0 ];
			}
		}

		return null;
	}

	/**
	 * Copy the price WooCommerce currently shows into the main row.
	 * Used by products that price themselves from their own form, where get_price() is a placeholder.
	 *
	 * @param {jQuery} $widget The widget root.
	 * @return {void}
	 */
	function wcf_fbt_sync_main_price( $widget ) {
		const $amount = $( wcf_fbt_get_price_element() ).find(
			'.woocommerce-Price-amount'
		);

		// Variable, grouped and on-sale products show two or more amounts — none of them single and resolved.
		if ( 1 !== $amount.length ) {
			return;
		}

		const $box = $widget.find(
			'.wcf-fbt-widget-row.is-main input[type="checkbox"]'
		);
		const text = $amount.text().trim();
		const price = wcf_fbt_parse_price( text );

		// A statically priced product already agrees, so leave its rendered price HTML alone.
		if ( price === ( parseFloat( $box.attr( 'data-price' ) ) || 0 ) ) {
			return;
		}

		$box.attr( 'data-price', price );
		$widget
			.find( '.wcf-fbt-widget-row.is-main .wcf-fbt-widget-price' )
			.text( text );
		wcf_fbt_recalc( $widget );
	}

	/**
	 * Sync every price cell that can change after render: the main row (variable
	 * source) and each variable row-group's select, hint, and price cell.
	 *
	 * @param {jQuery} $widget The widget root.
	 */
	function wcf_fbt_sync_variation_rows( $widget ) {
		const $main_row = $widget.find( '.wcf-fbt-widget-row.is-main' );
		if ( $main_row.length && $( 'form.variations_form' ).length ) {
			$main_row
				.find( '.wcf-fbt-widget-price' )
				.text(
					wcf_fbt_format_price( wcf_fbt_row_state( $main_row ).price )
				);
		}

		$widget.find( '.wcf-fbt-widget-row-group' ).each( function () {
			const $group = $( this );
			const row = wcf_fbt_row_state(
				$group.find( '.wcf-fbt-widget-row' )
			);
			const $price = $group.find( '.wcf-fbt-widget-price' );

			$group.toggleClass( 'is-open', row.checked );
			$group
				.find( '.wcf-fbt-widget-variation' )
				.toggleClass( 'needs-choice', row.checked && row.unresolved );

			if ( row.unresolved ) {
				$price.text( $price.data( 'from' ) ).addClass( 'is-from' );
			} else {
				$price
					.text( wcf_fbt_format_price( row.price ) )
					.removeClass( 'is-from' );
			}
		} );
	}

	/**
	 * Recompute count + total from currently-checked rows and update the footer.
	 *
	 * @param {jQuery} $widget The widget root.
	 */
	function wcf_fbt_recalc( $widget ) {
		let total = 0;
		let count = 0;
		let companions = 0;
		let unresolved = 0;

		// A variable main product without a chosen variation blocks the bundle the same way an unresolved companion does.
		const $variations_form = $( 'form.variations_form' );
		if (
			$variations_form.length &&
			! (
				parseInt(
					$variations_form.find( 'input[name="variation_id"]' ).val(),
					10
				) > 0
			)
		) {
			unresolved += 1;
		}

		wcf_fbt_sync_variation_rows( $widget );

		$widget.find( '.wcf-fbt-widget-row' ).each( function () {
			const row = wcf_fbt_row_state( $( this ) );

			if ( ! row.checked ) {
				return;
			}
			if ( row.unresolved ) {
				unresolved += 1;
				return;
			}

			total += row.price * row.qty;
			count += 1;
			if ( ! row.disabled ) {
				companions += 1;
			}
		} );

		const template =
			1 === count
				? wcf_fbt_frontend.i18n.label_single
				: wcf_fbt_frontend.i18n.label_plural;

		$widget
			.find( '[data-role="wcf-fbt-label"]' )
			.text( template.replace( '%d', count ) );
		$widget
			.find( '[data-role="wcf-fbt-total"]' )
			.text( wcf_fbt_format_price( total ) );

		const $companions = $widget.find(
			'.wcf-fbt-widget-row input[type="checkbox"]:not(:disabled)'
		);
		let buttonKey = 'button_some';
		if ( ! companions ) {
			buttonKey = 'button_single';
		} else if ( companions === $companions.length ) {
			buttonKey = 'button_all';
		}
		const buttonLabel = unresolved
			? wcf_fbt_frontend.i18n.select_options
			: wcf_fbt_frontend.i18n[ buttonKey ].replace( '%d', count );
		$widget
			.find( '.wcf-fbt-widget-add' )
			.text( buttonLabel )
			.prop( 'disabled', ! companions || unresolved > 0 );
	}

	/**
	 * Collect selected companions and submit the bundle to admin-ajax.
	 *
	 * @param {jQuery} $widget The widget root.
	 */
	function wcf_fbt_submit( $widget ) {
		const items = [];

		$widget
			.find( '.wcf-fbt-widget-input:checked:not(:disabled)' )
			.each( function () {
				// For variable rows the state carries the chosen variation ID, not the parent.
				const row = wcf_fbt_row_state(
					$( this ).closest( '.wcf-fbt-widget-row' )
				);
				if ( row.id && ! row.unresolved ) {
					items.push( { id: row.id, qty: row.qty } );
				}
			} );

		if ( ! items.length ) {
			return;
		}

		const $button = $widget.find( '.wcf-fbt-widget-add' );
		const $body = $( document.body );
		const original_label = $button.text();

		// Chosen form attributes resolve "Any …" attributes the variation itself leaves empty.
		const source_attributes = {};
		$( 'form.variations_form' )
			.find( '[name^="attribute_"]' )
			.each( function () {
				source_attributes[ this.name ] = this.value;
			} );

		$button.prop( 'disabled', true ).text( wcf_fbt_frontend.i18n.adding );

		// Post the product form's own fields (gift card amount, product options…) as real fields,
		// so add-to-cart hooks that read $_POST see exactly what a normal form submission sends.
		// 'add-to-cart' is dropped because WC's form handler runs on every request and would add the product twice.
		const source_fields = $( 'form.cart' )
			.first()
			.find( ':input' )
			.filter( function () {
				return this.name && 'add-to-cart' !== this.name;
			} )
			.serialize();

		// FBT's own parameters go last so they win over any same-named product field.
		const own_params = $.param( {
			action: 'wcf_fbt_add_to_cart',
			security: wcf_fbt_frontend.nonce,
			source_id: parseInt( $widget.attr( 'data-source-id' ), 10 ) || 0,
			source_qty: wcf_fbt_row_state(
				$widget.find( '.wcf-fbt-widget-row.is-main' )
			).qty,
			// Variable source products carry the variation chosen in WC's own form.
			source_variation_id:
				parseInt(
					$( 'form.variations_form' )
						.find( 'input[name="variation_id"]' )
						.val(),
					10
				) || 0,
			source_attributes,
			items,
		} );

		$.ajax( {
			url: wcf_fbt_frontend.ajax_url,
			method: 'POST',
			timeout: 15000,
			data: source_fields ? source_fields + '&' + own_params : own_params,
		} )
			.done( function ( response ) {
				if ( response && response.fragments ) {
					$body.trigger( 'wc_fragments_refreshed' );
					// Themes read the button arg (Blocksy does $button[0]) — pass the real jQuery object.
					$body.trigger( 'added_to_cart', [
						response.fragments,
						response.cart_hash,
						$button,
					] );
					// WC core has synchronously injected a "View cart" link after our button — drop it.
					$widget.find( '.added_to_cart' ).remove();
				}
				if ( response && response.notices_html ) {
					wcf_fbt_render_notices( $widget, response.notices_html );
				}
				wcf_fbt_flash_button(
					$button,
					wcf_fbt_frontend.i18n.added,
					original_label
				);
			} )
			.fail( function ( xhr ) {
				const data =
					xhr && xhr.responseJSON ? xhr.responseJSON.data : null;
				if ( data && data.notices_html ) {
					wcf_fbt_render_notices( $widget, data.notices_html );
				}
				wcf_fbt_flash_button(
					$button,
					wcf_fbt_frontend.i18n.error,
					original_label
				);
			} );
	}

	/**
	 * Inject the WooCommerce success notice above the widget.
	 *
	 * Prefers the theme's existing .woocommerce-notices-wrapper (Storefront et
	 * al.) so notices sit exactly where WC's own add-to-cart flow puts them.
	 * Falls back to inserting a wrapper right before the widget.
	 *
	 * @param {jQuery} $widget    The widget root.
	 * @param {string} noticeHtml Server-rendered notice markup.
	 */
	function wcf_fbt_render_notices( $widget, noticeHtml ) {
		if ( ! noticeHtml ) {
			return;
		}
		let $wrapper = $( '.woocommerce-notices-wrapper' ).first();
		if ( ! $wrapper.length ) {
			$wrapper = $( '<div class="woocommerce-notices-wrapper" />' );
			$widget.before( $wrapper );
		}
		$wrapper.html( noticeHtml );
		$( 'html, body' ).animate(
			{ scrollTop: $wrapper.offset().top - 80 },
			300
		);
	}

	/**
	 * Show a transient status label on the button, then revert after 2 seconds.
	 *
	 * @param {jQuery} $button  The submit button.
	 * @param {string} status   Transient status text.
	 * @param {string} original Original button label to restore.
	 */
	function wcf_fbt_flash_button( $button, status, original ) {
		$button.text( status );
		window.setTimeout( function () {
			$button.prop( 'disabled', false ).text( original );
		}, 2000 );
	}

	/**
	 * Format a numeric total with the currency symbol from localized data.
	 *
	 * @param {number} value Numeric total to render.
	 * @return {string} Formatted price string.
	 */
	function wcf_fbt_format_price( value ) {
		const symbol = wcf_fbt_frontend.currency_symbol || '';
		return symbol + value.toFixed( 2 );
	}

	/**
	 * Read a price WooCommerce has already formatted back into a number.
	 *
	 * @param {string} text Rendered price, e.g. "$1,234.50".
	 * @return {number} The amount, or 0 when the text holds no number.
	 */
	function wcf_fbt_parse_price( text ) {
		const plain = text
			.split( wcf_fbt_frontend.thousand_sep )
			.join( '' )
			.replace( wcf_fbt_frontend.decimal_sep, '.' );

		return parseFloat( plain.replace( /[^0-9.-]/g, '' ) ) || 0;
	}

	$( wcf_fbt_init );
} )( jQuery );
