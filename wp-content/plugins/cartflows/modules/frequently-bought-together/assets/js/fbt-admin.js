/* global wcf_fbt */
/**
 * Frequently Bought Together — Admin panel behaviour.
 *
 * Handles: sub-tab mode switch, card grid sync with WC's Select2, counter,
 * card × remove, drag reorder, and (Phase 3) the Auto Suggest workflow.
 *
 * Depends on jQuery, jquery-ui-sortable, and wc-enhanced-select (already
 * enqueued by WooCommerce on the product edit screen).
 */
( function ( $ ) {
	'use strict';

	let $panel;
	let $chooser;
	let $values;
	let $aiValues;
	let $grid;
	let $empty;
	let $counter;
	let $headerRow;
	let $tabs;
	let $panes;
	let $sourceInputs;
	let $aiPane;
	let $enable;
	let $drawerRoot;
	let $drawerBody;
	let $drawerTitle;
	let $drawerMeta;
	let lastFocused = null;
	let drawerContext = null;

	function init() {
		maybeCaptureAccessKey();

		$panel = $( '#cartflows_fbt_data' );

		if ( $panel.length ) {
			$chooser = $panel.find( '#_cartflows_fbt_product_search' );
			$values = $panel.find( '.wcf-fbt-picker-values' );
			$aiValues = $panel.find( '.wcf-fbt-picker-ai-values' );
			$grid = $panel.find( '.wcf-fbt-picker-rows' );
			$empty = $panel.find( '.wcf-fbt-picker-empty' );
			$counter = $panel.find( '[data-role="picker-count"]' );
			$headerRow = $panel.find( '.wcf-fbt-picker-header-row' );
			$tabs = $panel.find( '.wcf-fbt-sub-tab' );
			$panes = $panel.find( '.wcf-fbt-pane' );
			$sourceInputs = $panel.find( '.wcf-fbt-source-input' );
			$aiPane = $panel.find( '.wcf-fbt-pane-ai' );
			$enable = $panel.find( '#_cartflows_fbt_enabled' );

			bindEnable();
			bindCustomQty();
			bindTabs();
			bindChooser();
			bindCards();
			bindSortable();
			bindAi();
			syncEmpty();
			syncCounter();
		}

		initDrawer();

		$( document ).on( 'click', '.wcf-fbt-cta-connect', function ( event ) {
			event.preventDefault();
			const redirectBack = encodeURIComponent( window.location.href );
			wp.apiFetch( {
				path: '/cartflows/v1/ai/auth?redirect_back=' + redirectBack,
			} ).then( function ( res ) {
				if ( res && res.success && res.data && res.data.auth_url ) {
					window.location = res.data.auth_url;
				}
			} );
		} );
	}

	function maybeCaptureAccessKey() {
		const params = new URLSearchParams( window.location.search );
		const accessKey = params.get( 'access_key' );
		if ( ! accessKey ) {
			return;
		}

		params.delete( 'access_key' );
		const cleanQuery = params.toString();
		const cleanUrl =
			window.location.pathname +
			( cleanQuery ? '?' + cleanQuery : '' ) +
			window.location.hash;
		window.history.replaceState( {}, document.title, cleanUrl );

		wp.apiFetch( {
			path: '/cartflows/v1/ai/auth',
			method: 'POST',
			data: { accessKey },
		} )
			.then( function ( res ) {
				if ( res && res.success ) {
					window.location.reload();
				}
			} )
			.catch( function () {} );
	}

	function bindCustomQty() {
		const $toggle = $panel.find( '#_cartflows_fbt_custom_qty' );
		const $shell = $panel.find( '.wcf-fbt-picker-shell' );
		if ( ! $toggle.length || ! $shell.length ) {
			return;
		}
		$toggle.on( 'change', function () {
			$shell.toggleClass( 'has-qty', $toggle.is( ':checked' ) );
		} );
	}

	function bindEnable() {
		$enable.on( 'change', function () {
			$panel.toggleClass( 'is-widget-enabled', $enable.is( ':checked' ) );
		} );
	}

	function bindTabs() {
		$tabs.on( 'click', function () {
			const mode = $( this ).data( 'mode' );
			switchMode( mode );
		} );
	}

	function switchMode( mode ) {
		$tabs.removeClass( 'is-active' );
		$tabs.filter( '[data-mode="' + mode + '"]' ).addClass( 'is-active' );
		$panes.removeClass( 'is-active' );
		$panes.filter( '[data-pane="' + mode + '"]' ).addClass( 'is-active' );
		$sourceInputs.prop( 'checked', false );
		$sourceInputs
			.filter( '[value="' + mode + '"]' )
			.prop( 'checked', true );
	}

	function bindChooser() {
		$chooser.on( 'select2:select', function ( event ) {
			const item =
				event.params && event.params.data ? event.params.data : null;
			if ( ! item || ! item.id ) {
				return;
			}
			addProduct( item.id, String( item.text || '' ).trim() );
			// Pop the just-added option off so the Select2 stays chip-free
			// and keeps rendering as a plain search input — the row list is
			// the source of truth for what's selected.
			$chooser.find( 'option[value="' + item.id + '"]' ).remove();
			$chooser.trigger( 'change' );
		} );
	}

	function bindCards() {
		$grid.on( 'click', '.wcf-fbt-picker-remove', function () {
			const id = $( this )
				.closest( '.wcf-fbt-picker-row' )
				.data( 'product-id' );
			if ( ! id ) {
				return;
			}
			removeCard( id );
		} );
	}

	function bindSortable() {
		if ( ! $.fn.sortable ) {
			return;
		}
		$grid.sortable( {
			handle: '.wcf-fbt-picker-drag',
			placeholder: 'wcf-fbt-picker-row is-sortable-placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			update: syncValueOrder,
		} );
	}

	function syncValueOrder() {
		const newOrder = [];
		$grid.find( '.wcf-fbt-picker-row' ).each( function () {
			newOrder.push( String( $( this ).data( 'product-id' ) ) );
		} );
		const $inputs = $values.find( 'input' ).detach();
		$.each( newOrder, function ( _idx, id ) {
			const $match = $inputs.filter( '[value="' + id + '"]' );
			if ( $match.length ) {
				$values.append( $match );
			}
		} );
	}

	function addProduct( id, name ) {
		if ( ! id ) {
			return;
		}
		if (
			$grid.find( '.wcf-fbt-picker-row[data-product-id="' + id + '"]' )
				.length
		) {
			return;
		}
		if (
			$grid.find( '.wcf-fbt-picker-row' ).length >= wcf_fbt.max_products
		) {
			return;
		}
		$values.append(
			'<input type="hidden" name="_cartflows_fbt_product_ids[]" value="' +
				escapeHtml( id ) +
				'" />'
		);
		addCard( id, name );
		updateExclude();
		fetchProductMeta( id );
	}

	function markAiSourced( id ) {
		if (
			! $aiValues.length ||
			$aiValues.find( 'input[value="' + id + '"]' ).length
		) {
			return;
		}
		$aiValues.append(
			'<input type="hidden" name="_cartflows_fbt_ai_product_ids[]" value="' +
				escapeHtml( id ) +
				'" />'
		);
	}

	function updateExclude() {
		const ids = [ parseInt( wcf_fbt.product_id, 10 ) ];
		$values.find( 'input' ).each( function () {
			ids.push( parseInt( $( this ).val(), 10 ) );
		} );
		// JSON array so jQuery.data() (which Select2 reads) returns an array — not a string.
		$chooser.attr( 'data-exclude', JSON.stringify( ids ) );
		// Bust jQuery's data cache so the next read picks up the new value.
		$chooser.removeData( 'exclude' );
	}

	function fetchProductMeta( id ) {
		$.ajax( {
			url: wcf_fbt.ajax_url,
			method: 'POST',
			data: {
				action: 'wcf_fbt_get_product_meta',
				security: $( '#wcf_fbt_ai_nonce' ).val(),
				product_id: id,
			},
			timeout: 8000,
		} ).done( function ( response ) {
			if ( ! response || ! response.success || ! response.data ) {
				return;
			}
			const $row = $grid.find(
				'.wcf-fbt-picker-row[data-product-id="' + id + '"]'
			);
			if ( ! $row.length ) {
				return;
			}
			if ( response.data.price_html ) {
				$row.find( '.wcf-fbt-picker-price' ).text(
					response.data.price_html
				);
			}
			if ( response.data.stock_label ) {
				$row.find( '.wcf-fbt-picker-stock' ).html(
					'<span class="wcf-fbt-badge ' +
						escapeHtml( response.data.stock_class || '' ) +
						'">' +
						escapeHtml( response.data.stock_label ) +
						'</span>'
				);
			}
			if ( response.data.thumb_url ) {
				$row.find( '.wcf-fbt-picker-thumb' ).attr(
					'src',
					response.data.thumb_url
				);
			}
		} );
	}

	function addCard( id, name ) {
		if ( ! id ) {
			return;
		}
		if (
			$grid.find( '.wcf-fbt-picker-row[data-product-id="' + id + '"]' )
				.length
		) {
			return;
		}
		const html = [
			'<div class="wcf-fbt-picker-row" data-product-id="' +
				escapeHtml( id ) +
				'">',
			'<span class="wcf-fbt-picker-drag" aria-hidden="true">',
			'<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>',
			'</span>',
			'<img class="wcf-fbt-picker-thumb" src="' +
				escapeHtml( wcf_fbt.placeholder_thumb || '' ) +
				'" alt="" />',
			'<div class="wcf-fbt-picker-info">',
			'<p class="wcf-fbt-picker-name">' + escapeHtml( name ) + '</p>',
			'</div>',
			'<span class="wcf-fbt-picker-price"></span>',
			'<span class="wcf-fbt-picker-stock"></span>',
			'<span class="wcf-fbt-picker-qty">',
			'<input type="number" min="1" step="1" class="wcf-fbt-picker-qty-input" name="_cartflows_fbt_product_qty[' +
				escapeHtml( id ) +
				']" value="1" />',
			'</span>',
			'<button type="button" class="wcf-fbt-picker-remove" aria-label="' +
				escapeHtml( wcf_fbt.i18n.remove ) +
				'">',
			'<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>',
			'</button>',
			'</div>',
		].join( '' );
		$grid.append( html );
		syncEmpty();
		syncCounter();
	}

	function removeCard( id ) {
		$grid
			.find( '.wcf-fbt-picker-row[data-product-id="' + id + '"]' )
			.remove();
		$values.find( 'input[value="' + id + '"]' ).remove();
		$aiValues.find( 'input[value="' + id + '"]' ).remove();
		updateExclude();
		syncEmpty();
		syncCounter();
	}

	function syncEmpty() {
		const hasRows = $grid.find( '.wcf-fbt-picker-row' ).length > 0;
		$empty.prop( 'hidden', hasRows );
		$grid.toggle( hasRows );
		$headerRow.toggle( hasRows );
	}

	function syncCounter() {
		const count = $grid.find( '.wcf-fbt-picker-row' ).length;
		$counter.text( count );
		$panel
			.find( '[data-role="products-label"]' )
			.text( wcf_fbt.i18n.products_label.replace( '%d', count ) );
	}

	function bindAi() {
		if ( ! $aiPane.length ) {
			return;
		}

		$aiPane.on( 'click', '.wcf-fbt-generate', function () {
			runSuggest( false );
		} );

		$aiPane.on( 'click', '.wcf-fbt-regen-all', function () {
			runSuggest( true );
		} );

		$aiPane.on( 'click', '.wcf-fbt-ai-accept', function () {
			const $item = $( this ).closest( '.wcf-fbt-ai-item' );
			const id = $item.data( 'product-id' );
			if ( ! id ) {
				return;
			}
			addProduct( id, $item.data( 'name' ) );
			markAiSourced( id );
			$item.remove();
			maybeShowAiHero();
		} );

		$aiPane.on( 'click', '.wcf-fbt-ai-reject', function ( event ) {
			event.preventDefault();
			const $item = $( this ).closest( '.wcf-fbt-ai-item' );
			$item.remove();
			maybeShowAiHero();
		} );
	}

	function maybeShowAiHero() {
		if ( $aiPane.find( '.wcf-fbt-ai-item' ).length ) {
			return;
		}
		$aiPane.html( renderAiHero() );
	}

	function selectedProductIds() {
		return $values
			.find( 'input' )
			.map( function () {
				return parseInt( $( this ).val(), 10 );
			} )
			.get();
	}

	function runSuggest( force ) {
		const exclude = selectedProductIds().concat(
			force ? collectShownIds( $aiPane ) : []
		);
		showAiLoading();
		fetchSuggestions( force, 'fbt', exclude )
			.then( function ( result ) {
				renderAiResults( result.products, result.basis );
			} )
			.catch( function ( error ) {
				showAiError( errorMessageFor( error ) );
			} );
	}

	function fetchSuggestions( force, context, exclude ) {
		let path =
			'/cartflows-pro/v1/frequently-bought-together/?product_ids=' +
			encodeURIComponent( wcf_fbt.product_id ) +
			'&limit=4&context=' +
			encodeURIComponent( context || 'fbt' );
		if ( exclude && exclude.length ) {
			path += '&exclude=' + encodeURIComponent( exclude.join( ',' ) );
		}
		if ( force ) {
			path += '&force=1&_ts=' + Date.now();
		}
		return wp.apiFetch( { path } ).then( function ( response ) {
			if ( ! response || ! response.success ) {
				return Promise.reject( new Error( 'no-success' ) );
			}
			return {
				products: response.products || [],
				basis: response.basis || '',
			};
		} );
	}

	function collectShownIds( $scope ) {
		return $scope
			.find( '.wcf-fbt-ai-item' )
			.map( function () {
				return $( this ).data( 'product-id' );
			} )
			.get();
	}

	function errorMessageFor( error ) {
		if ( error && 'rest_no_route' === error.code ) {
			return wcf_fbt.i18n.ai_endpoint_missing;
		}
		return wcf_fbt.i18n.ai_error;
	}

	function showAiLoading() {
		let rows = '';
		let i;
		for ( i = 0; i < 3; i++ ) {
			rows += '<div class="wcf-fbt-skeleton-row"></div>';
		}
		$aiPane.html(
			'<div class="wcf-fbt-status">' +
				escapeHtml( wcf_fbt.i18n.ai_loading ) +
				'</div>' +
				'<div class="wcf-fbt-skeleton-list">' +
				rows +
				'</div>'
		);
	}

	function showAiError( message ) {
		$aiPane.html(
			'<div class="wcf-fbt-status is-error">' +
				escapeHtml( message ) +
				'</div>' +
				renderAiHero()
		);
	}

	function renderAiHero() {
		return [
			'<div class="wcf-fbt-ai-hero">',
			'<div class="wcf-fbt-ai-hero-icon" aria-hidden="true">' +
				escapeHtml( '✨' ) +
				'</div>',
			'<h4>' + escapeHtml( wcf_fbt.i18n.ai_hero_title ) + '</h4>',
			'<p>' + escapeHtml( wcf_fbt.i18n.ai_hero_desc ) + '</p>',
			'<button type="button" class="button button-primary wcf-fbt-generate">' +
				escapeHtml( wcf_fbt.i18n.ai_generate ) +
				'</button>',
			'</div>',
		].join( '' );
	}

	function renderAiResults( products ) {
		if ( ! products || ! products.length ) {
			showAiError( wcf_fbt.i18n.ai_none );
			return;
		}
		const toolbar = [
			'<div class="wcf-fbt-ai-toolbar">',
			'<span>' + escapeHtml( wcf_fbt.i18n.ai_cached ) + '</span>',
			'<button type="button" class="button wcf-fbt-btn wcf-fbt-btn-sm wcf-fbt-regen-all">' +
				escapeHtml( wcf_fbt.i18n.ai_regen_all ) +
				'</button>',
			'</div>',
		].join( '' );
		let list = '<div class="wcf-fbt-ai-list">';
		let i;
		for ( i = 0; i < products.length; i++ ) {
			list += renderAiRow( products[ i ] );
		}
		list += '</div>';
		$aiPane.html( toolbar + list );
	}

	function renderAiRow( item ) {
		const percent =
			'number' === typeof item.match_percent ? item.match_percent : 0;
		const matchLabel = wcf_fbt.i18n.match_label.replace(
			'%s',
			Math.round( percent ) + '%'
		);
		const fallbackLabels = {
			category: wcf_fbt.i18n.badge_popular,
			popular: wcf_fbt.i18n.badge_popular,
			recent: wcf_fbt.i18n.badge_new,
		};
		// A 0% match is a normalization artifact — show no badge rather than a misleading number.
		let badge = '';
		if ( fallbackLabels[ item.source ] ) {
			badge =
				'<span class="wcf-fbt-badge wcf-fbt-badge-gray">' +
				escapeHtml( fallbackLabels[ item.source ] ) +
				'</span>';
		} else if ( Math.round( percent ) > 0 ) {
			badge =
				'<span class="wcf-fbt-badge wcf-fbt-badge-green">' +
				escapeHtml( matchLabel ) +
				'</span>';
		}
		return [
			'<div class="wcf-fbt-ai-item" data-product-id="' +
				escapeHtml( item.id ) +
				'" data-name="' +
				escapeHtml( item.name ) +
				'">',
			'<img class="wcf-fbt-card-thumb" src="' +
				escapeHtml( item.image || wcf_fbt.placeholder_thumb || '' ) +
				'" alt="" />',
			'<div class="wcf-fbt-ai-content">',
			'<div class="wcf-fbt-ai-row1">',
			'<p class="wcf-fbt-ai-name">' + escapeHtml( item.name ) + '</p>',
			badge,
			item.price_html
				? '<span class="wcf-fbt-badge wcf-fbt-badge-gray">' +
				  item.price_html +
				  '</span>'
				: '',
			'</div>',
			'</div>',
			'<div class="wcf-fbt-ai-actions">',
			'<button type="button" class="button button-primary wcf-fbt-btn wcf-fbt-btn-sm wcf-fbt-ai-accept">' +
				escapeHtml( wcf_fbt.i18n.ai_accept ) +
				'</button>',
			'<a href="#" class="button-link wcf-fbt-ai-reject" role="button">' +
				escapeHtml( wcf_fbt.i18n.ai_reject ) +
				'</a>',
			'</div>',
			'</div>',
		].join( '' );
	}

	function initDrawer() {
		$drawerRoot = $( '.wcf-fbt-drawer-root' );
		if ( ! $drawerRoot.length ) {
			return;
		}
		$drawerBody = $drawerRoot.find( '.wcf-fbt-drawer-body' );
		$drawerTitle = $drawerRoot.find( '.wcf-fbt-drawer-title' );
		$drawerMeta = $drawerRoot.find( '.wcf-fbt-drawer-meta' );

		relocateSuggestButtons();

		$( document ).on( 'click', '.wcf-fbt-suggest-ai', function () {
			const context = $( this ).data( 'context' );
			openDrawer( context );
		} );

		$drawerRoot.on(
			'click',
			'.wcf-fbt-drawer-close, .wcf-fbt-drawer-done, .wcf-fbt-drawer-backdrop',
			closeDrawer
		);

		$( document ).on( 'keyup.wcffbtdrawer', function ( event ) {
			if ( 'Escape' === event.key && ! $drawerRoot.prop( 'hidden' ) ) {
				closeDrawer();
			}
		} );

		$drawerBody.on( 'click', '.wcf-fbt-ai-accept', drawerAccept );
		$drawerBody.on( 'click', '.wcf-fbt-ai-reject', drawerReject );

		$drawerRoot.on( 'click', '.wcf-fbt-drawer-regen', function () {
			$( this ).addClass( 'wcf-fbt-is-regenerating' );
			runDrawerSuggest( true );
		} );
	}

	/**
	 * Move each "Auto Suggest" button next to the WC field it targets.
	 *
	 * WC's `woocommerce_product_options_related` hook fires below both fields, so
	 * PHP renders both buttons together. This lifts each button into its matching
	 * .form-field row so it sits inline with the Select2, matching the mockup.
	 */
	function relocateSuggestButtons() {
		const $wrapper = $( '.wcf-fbt-linked-buttons' );
		if ( ! $wrapper.length ) {
			return;
		}
		$wrapper.find( '.wcf-fbt-suggest-ai' ).each( function () {
			const $btn = $( this );
			const targetId = $btn.data( 'target-select' );
			if ( ! targetId ) {
				return;
			}
			const $target = $( '#' + targetId );
			if ( ! $target.length ) {
				return;
			}
			const $field = $target.closest( '.form-field' );
			if ( $field.length ) {
				$field.append( $btn );
			}
		} );
		if ( ! $wrapper.children().length ) {
			$wrapper.remove();
		}
	}

	function openDrawer( context ) {
		if ( 'upsell' !== context && 'crosssell' !== context ) {
			return;
		}
		drawerContext = context;
		const title =
			'upsell' === context
				? wcf_fbt.i18n.drawer_title_upsell
				: wcf_fbt.i18n.drawer_title_crosssell;
		$drawerTitle.text( title );
		$drawerMeta.text( '' );
		lastFocused = $drawerRoot[ 0 ].ownerDocument.activeElement;
		$drawerRoot.prop( 'hidden', false ).addClass( 'is-open' );
		runDrawerSuggest( false );
	}

	function closeDrawer() {
		if ( ! $drawerRoot || ! $drawerRoot.length ) {
			return;
		}
		$drawerRoot
			.prop( 'hidden', true )
			.removeClass( 'is-open is-empty is-disconnected' );
		drawerContext = null;
		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}
	}

	function runDrawerSuggest( force ) {
		if ( ! drawerContext ) {
			return;
		}
		if ( ! wcf_fbt.auth_connected ) {
			showDrawerConnect();
			return;
		}
		const chosen = (
			$( '#' + wcf_fbt.select_ids[ drawerContext ] ).val() || []
		).map( Number );
		const exclude = chosen.concat(
			force ? collectShownIds( $drawerBody ) : []
		);
		showDrawerLoading();
		fetchSuggestions( force, drawerContext, exclude )
			.then( function ( result ) {
				renderDrawerResults( result.products, result.basis );
			} )
			.catch( function ( error ) {
				showDrawerError( errorMessageFor( error ) );
			} );
	}

	function showDrawerConnect() {
		$drawerRoot.removeClass( 'is-empty' ).addClass( 'is-disconnected' );
		$drawerMeta.text( '' );
		$drawerBody.html(
			[
				'<div class="wcf-fbt-ai-hero">',
				'<div class="wcf-fbt-ai-hero-icon" aria-hidden="true">' +
					escapeHtml( '✨' ) +
					'</div>',
				'<h4>' + escapeHtml( wcf_fbt.i18n.connect_title ) + '</h4>',
				'<p>' + escapeHtml( wcf_fbt.i18n.connect_desc ) + '</p>',
				'<a href="#" class="button button-primary wcf-fbt-upgrade-cta wcf-fbt-cta-connect">' +
					escapeHtml( wcf_fbt.i18n.connect_btn ) +
					'</a>',
				'</div>',
			].join( '' )
		);
	}

	function showDrawerLoading() {
		let rows = '';
		let i;
		for ( i = 0; i < 3; i++ ) {
			rows += '<div class="wcf-fbt-skeleton-row"></div>';
		}
		$drawerRoot.removeClass( 'is-empty' );
		$drawerBody.html(
			'<div class="wcf-fbt-status">' +
				escapeHtml( wcf_fbt.i18n.ai_loading ) +
				'</div>' +
				'<div class="wcf-fbt-skeleton-list">' +
				rows +
				'</div>'
		);
	}

	function showDrawerError( message ) {
		$drawerRoot
			.find( '.wcf-fbt-drawer-regen' )
			.removeClass( 'wcf-fbt-is-regenerating' );
		$drawerBody.html(
			'<div class="wcf-fbt-status is-error">' +
				escapeHtml( message ) +
				'</div>'
		);
	}

	function renderDrawerResults( products ) {
		$drawerRoot
			.removeClass( 'is-disconnected' )
			.find( '.wcf-fbt-drawer-regen' )
			.removeClass( 'wcf-fbt-is-regenerating' );
		if ( ! products || ! products.length ) {
			showDrawerError( wcf_fbt.i18n.ai_none );
			return;
		}
		let list = '<div class="wcf-fbt-ai-list">';
		let i;
		for ( i = 0; i < products.length; i++ ) {
			list += renderAiRow( products[ i ] );
		}
		list += '</div>';
		$drawerBody.html( list );
		$drawerMeta.text( wcf_fbt.i18n.ai_cached );
	}

	function drawerAccept() {
		const $item = $( this ).closest( '.wcf-fbt-ai-item' );
		const id = $item.data( 'product-id' );
		if ( ! id || ! drawerContext ) {
			return;
		}
		const targetSelectId = wcf_fbt.select_ids[ drawerContext ];
		if ( ! targetSelectId ) {
			return;
		}
		const $target = $( '#' + targetSelectId );
		if ( ! $target.length ) {
			return;
		}
		const name = $item.data( 'name' );
		let $option = $target.find( 'option[value="' + id + '"]' );
		if ( ! $option.length ) {
			$option = $( '<option>', {
				value: id,
				text: name,
				selected: true,
			} );
			$target.append( $option );
		} else {
			$option.prop( 'selected', true );
		}
		$target.trigger( 'change' );
		$item.remove();
		maybeShowDrawerEmpty();
	}

	function drawerReject( event ) {
		event.preventDefault();
		const $item = $( this ).closest( '.wcf-fbt-ai-item' );
		$item.remove();
		maybeShowDrawerEmpty();
	}

	function maybeShowDrawerEmpty() {
		if ( $drawerBody.find( '.wcf-fbt-ai-item' ).length ) {
			return;
		}
		$drawerBody.empty();
		$drawerMeta.text( '' );
		$drawerRoot.addClass( 'is-empty' );
	}

	function escapeHtml( str ) {
		return String( null === str || undefined === str ? '' : str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#39;' );
	}

	$( document ).ready( init );
} )( jQuery );
