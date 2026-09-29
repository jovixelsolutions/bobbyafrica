jQuery( document ).ready( function( $ ) {
	$.post(
		cartflowsPointerData.ajaxurl,
		{
			action: 'cartflows_pointer_should_show',
			nonce: cartflowsPointerData.nonce,
		},
		function( response ) {
			if ( ! response || ! response.show ) {
				return;
			}

			let $target = $( cartflowsPointerData.target_selector );
			if ( ! $target.length ) {
				$target = $( cartflowsPointerData.fallback_target );
			}

			// Build pointer content safely.
			// Note: content uses .html() as it contains intentional HTML from server (sanitized via wp_kses_post).
			const pointerContentHtml = $( '<div>' )
				.append( $( '<h3>' ).text( response.title ) )
				.append( $( '<p>' ).html( response.content ) );
			const pointerContent = pointerContentHtml.html();

			let pointerClosedBy = null;

			$target
				.pointer( {
					content: pointerContent,
					position: cartflowsPointerData.position,
					buttons: function( event, t ) {
						const dismissBtn = $( '<a class="close" href="#"></a>' )
							.text( response.dismiss )
							.on( 'click.pointer', function( e ) {
								e.preventDefault();
								pointerClosedBy = 'dismiss';
								t.element.pointer( 'close' );
								$.post( cartflowsPointerData.ajaxurl, {
									action: 'cartflows_pointer_dismiss',
									nonce: cartflowsPointerData.nonce,
								} );
							} );

						// CTA button.
						let safeUrl = '#';
						if ( response.button_url && /^https?:\/\//i.test( response.button_url ) ) {
							safeUrl = response.button_url;
						}

						const ctaBtn = $( '<a class="button button-primary"></a>' )
							.attr( 'href', safeUrl )
							.text( response.button_text )
							.on( 'click.pointer', function() {
								pointerClosedBy = 'cta';
								t.element.pointer( 'close' );
								$.post( cartflowsPointerData.ajaxurl, {
									action: 'cartflows_pointer_accept',
									nonce: cartflowsPointerData.nonce,
								} );
							} );

						return $( '<div style="display:flex;justify-content:space-between;align-items:center;width:100%"></div>' )
							.append( ctaBtn )
							.append( dismissBtn );
					},
					close: function() {
						if ( ! pointerClosedBy ) {
							$.post( cartflowsPointerData.ajaxurl, {
								action: 'cartflows_pointer_dismiss',
								nonce: cartflowsPointerData.nonce,
							} );
						}
					},
				} )
				.pointer( 'open' );
		}
	);
} );
