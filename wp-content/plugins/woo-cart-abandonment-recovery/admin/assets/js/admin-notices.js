( function ( $ ) {
	AdminNotices = {
		init() {
			$( document ).on(
				'click',
				'.weekly-report-email-notice.wcar-dismissible-notice .notice-dismiss',
				AdminNotices.disable_weekly_report_email_admin_notice
			);

			$( document ).on(
				'click',
				'button.wcar-switch-ui-btn',
				AdminNotices.switch_to_new_ui
			);

			$( document ).on(
				'click',
				'button.wcar-dismiss-notice-btn',
				AdminNotices.switch_to_legacy_ui
			);
		},

		disable_weekly_report_email_admin_notice( event ) {
			event.preventDefault();
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wcar_disable_weekly_report_email_notice',
					security:
						wcf_ca_notices_vars.weekly_report_email_notice_nonce,
				},
			} )
				.done( function () {} )
				.fail( function () {} )
				.always( function () {} );
		},

		switch_to_new_ui( event ) {
			event.preventDefault();

			const button = $( this ),
				originalText = button.text(),
				buttonAction = button.data( 'action' ),
				actionNonce = button.data( 'nonce' );

			// Show loading state.
			button.text( 'Switching...' ).prop( 'disabled', true );

			// Make AJAX request.
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wcar_switch_to_new_ui',
					action_type: buttonAction,
					nonce: actionNonce,
				},
				success( response ) {
					if ( response.success ) {
						if ( response.data.redirect_to ) {
							// Redirect to new UI.
							window.location.href = response.data.redirect_to;
						} else if ( response.data.reload ) {
							// Just reload the page.
							window.location.reload();
						}
					} else {
						console.error( 'Error: ' + response.data.message );
						button.text( originalText ).prop( 'disabled', false );
					}
				},
				error() {
					console.error( 'Something went wrong. Please try again.' );
					button.text( originalText ).prop( 'disabled', false );
				},
			} );
		},

		switch_to_legacy_ui( event ) {
			event.preventDefault();

			const button = $( this ),
				notice = button.closest( '.wcar-ui-switch-notice' ),
				buttonAction = button.data( 'action' ),
				actionNonce = button.data( 'nonce' );

			// Make AJAX request to dismiss notice.
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wcar_switch_to_new_ui',
					action_type: buttonAction,
					nonce: actionNonce,
				},
				success( response ) {
					if ( response.success ) {
						// Hide the notice.
						notice.fadeOut();
					}
				},
			} );
		},
	};

	$( function () {
		AdminNotices.init();
	} );
} )( jQuery );
