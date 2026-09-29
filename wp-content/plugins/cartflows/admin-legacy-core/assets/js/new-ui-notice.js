( function () {
	'use strict';

	if ( typeof window.wcfNewUiNotice === 'undefined' ) {
		return;
	}

	const config = window.wcfNewUiNotice;

	function postForm( action, nonce ) {
		const body = new URLSearchParams();
		body.append( 'action', action );
		body.append( 'security', nonce );

		return fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} ).then( ( response ) => response.json() );
	}

	function onClick( event ) {
		const trigger = event.target.closest( '[data-wcf-action]' );

		if ( ! trigger ) {
			return;
		}

		const notice = trigger.closest( '.cartflows-switch-ui-notice' );

		if ( ! notice ) {
			return;
		}

		event.preventDefault();

		const action = trigger.getAttribute( 'data-wcf-action' );

		// Disable both buttons while in flight to prevent double-submit.
		notice.querySelectorAll( 'button' ).forEach( ( button ) => {
			button.disabled = true;
		} );

		if ( 'switch' === action ) {
			postForm( config.switchAction, config.switchNonce )
				.then( ( payload ) => {
					if (
						payload &&
						payload.success &&
						payload.data &&
						payload.data.redirect_to
					) {
						window.location.href = payload.data.redirect_to;
					} else {
						notice
							.querySelectorAll( 'button' )
							.forEach( ( button ) => {
								button.disabled = false;
							} );
					}
				} )
				.catch( () => {
					notice.querySelectorAll( 'button' ).forEach( ( button ) => {
						button.disabled = false;
					} );
				} );
		}
	}

	document.addEventListener( 'click', onClick );
} )();
