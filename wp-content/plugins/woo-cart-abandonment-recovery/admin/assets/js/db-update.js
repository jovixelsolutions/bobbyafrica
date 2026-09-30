jQuery( document ).ready( function ( $ ) {
	$( '#wcar-update-db-btn' ).on( 'click', function () {
		const $btn = $( this );
		const $spinner = $btn.next( '.spinner' );
		const data = window.wcarDbUpdate || {};
		const messages = data.messages || {};

		$btn.prop( 'disabled', true );
		$spinner.addClass( 'is-active' );

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wcar_update_db',
				nonce: data.nonce || '',
			},
			success( response ) {
				if ( response.success ) {
					$( '#wcar-update-notice' )
						.removeClass( 'notice-warning' )
						.addClass( 'notice-success' );
					$( '#wcar-update-notice p:first' ).html(
						'<strong>' +
							( messages.success ||
								'Database updated successfully!' ) +
							'</strong>'
					);
					$( '#wcar-update-notice p:last' ).remove();
					setTimeout( function () {
						$( '#wcar-update-notice' ).fadeOut();
					}, 3000 );
				} else {
					window.alert(
						messages.failed || 'Update failed. Please try again.'
					);
				}
			},
			error() {
				window.alert(
					messages.failed || 'Update failed. Please try again.'
				);
			},
			complete() {
				$btn.prop( 'disabled', false );
				$spinner.removeClass( 'is-active' );
			},
		} );
	} );
} );
