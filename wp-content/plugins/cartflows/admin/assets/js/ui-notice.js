( function ( $ ) {
	const ignore_gb_notice = function () {
		$( '.wcf_notice_gutenberg_plugin button.notice-dismiss' ).on(
			'click',
			function ( e ) {
				e.preventDefault();

				const data = {
					action: 'cartflows_ignore_gutenberg_notice',
					security: cartflows_notices.ignore_gb_notice,
				};

				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					data,

					success( response ) {
						if ( response.success ) {
							console.log( 'Gutenberg Notice Ignored.' );
						}
					},
				} );
			}
		);
	};

	const dismiss_weekly_report_email_notice = function () {
		$(
			'.weekly-report-email-notice.wcf-dismissible-notice button.notice-dismiss'
		).on( 'click', function ( e ) {
			e.preventDefault();

			const data = {
				action: 'cartflows_disable_weekly_report_email_notice',
				security: cartflows_notices.dismiss_weekly_report_email_notice,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						console.log( 'Weekly Report Email Notice Ignored.' );
					}
				},
			} );
		} );
	};

	const migrate_custom_scripts = function () {
		$( '.wcf-migrate-scripts-btn' ).on( 'click', function ( e ) {
			e.preventDefault();

			const $btn = $( this );
			const $notice = $btn.closest( '.script-migration-notice' );

			$btn.prop( 'disabled', true ).text( $btn.text() + '...' );

			const data = {
				action: 'cartflows_migrate_custom_scripts',
				security: cartflows_notices.migrate_custom_scripts,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						$notice.fadeOut( 'fast', function () {
							$( this ).remove();
						} );
					} else {
						$btn.prop( 'disabled', false ).text(
							$btn.text().replace( '...', '' )
						);
					}
				},

				error() {
					$btn.prop( 'disabled', false ).text(
						$btn.text().replace( '...', '' )
					);
				},
			} );
		} );
	};

	const snooze_script_migration_notice = function () {
		$( '.wcf-snooze-migration-btn' ).on( 'click', function ( e ) {
			e.preventDefault();

			const $btn = $( this );
			const $notice = $btn.closest( '.script-migration-notice' );

			$btn.prop( 'disabled', true );

			const data = {
				action: 'cartflows_snooze_script_migration',
				security: cartflows_notices.snooze_script_migration,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						$notice.fadeOut( 'fast', function () {
							$( this ).remove();
						} );
					} else {
						$btn.prop( 'disabled', false );
					}
				},

				error() {
					$btn.prop( 'disabled', false );
				},
			} );
		} );
	};

	const dismiss_script_migration_complete_notice = function () {
		$(
			'.script-migration-complete-notice.wcf-dismissible-notice button.notice-dismiss'
		).on( 'click', function ( e ) {
			e.preventDefault();

			const data = {
				action: 'cartflows_dismiss_script_migration_complete_notice',
				security: cartflows_notices.dismiss_migration_complete_notice,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,

				success( response ) {
					if ( response.success ) {
						console.log(
							'Script Migration Complete Notice Dismissed.'
						);
					}
				},
			} );
		} );
	};

	$( function () {
		ignore_gb_notice();
		dismiss_weekly_report_email_notice();
		migrate_custom_scripts();
		snooze_script_migration_notice();
		dismiss_script_migration_complete_notice();
	} );
} )( jQuery );
