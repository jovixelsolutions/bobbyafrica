( function ( $ ) {
	EmailTemplatesAdmin = {
		init() {
			$( document ).on(
				'click',
				'#wcf_preview_email',
				EmailTemplatesAdmin.send_test_email
			);
			$( document ).on(
				'click',
				'.wcf-ca-switch.wcf-toggle-template-status',
				EmailTemplatesAdmin.toggle_activate_template
			);
			$( document ).on(
				'click',
				'#wcf_ca_delete_coupons',
				EmailTemplatesAdmin.delete_coupons
			);
			$( document ).on(
				'click',
				'#wcf_ca_export_orders',
				EmailTemplatesAdmin.export_orders
			);
			// Trigger to export all email templates on click of export button.
			$( document ).on(
				'click',
				'#wcf-export-templates',
				EmailTemplatesAdmin.export_templates
			);
			// Trigger to export email templates on click of export besides clone template button.
			$( document ).on(
				'click',
				'.wcf-export-template',
				EmailTemplatesAdmin.export_templates
			);
			$( document ).on(
				'click',
				'#doaction, #doaction2',
				EmailTemplatesAdmin.handle_bulk_action
			);
			$( document ).on(
				'click',
				'#wcf-import-templates',
				EmailTemplatesAdmin.open_import_modal
			);
			$( document ).on(
				'click',
				'.wcf-ca-modal-close, .wcf-ca-action--cancel',
				EmailTemplatesAdmin.close_import_modal
			);
			$( document ).on(
				'submit',
				'#wcf-ca-import-form',
				EmailTemplatesAdmin.import_templates
			);
			$( document ).on(
				'change',
				'#wcf-ca-import-file',
				EmailTemplatesAdmin.handle_file_change
			);
			$( document ).on(
				'dragover',
				'#wcf-import-modal',
				EmailTemplatesAdmin.prevent_default
			);
			$( document ).on(
				'drop',
				'#wcf-import-modal',
				EmailTemplatesAdmin.handle_file_drop
			);
			$( document ).on(
				'click',
				'.wcf-ca-remove-file',
				EmailTemplatesAdmin.reset_import_file
			);
			$( document ).on(
				'click',
				'.wcar-switch-grid',
				EmailTemplatesAdmin.toggle_activate_template_on_grid
			);
			const coupon_child_fields =
				'#wcf_email_discount_type, #wcf_email_discount_amount, #wcf_email_coupon_expiry_date, #wcf_free_shipping_coupon, #wcf_auto_coupon_apply, #wcf_individual_use_only';
			$( coupon_child_fields )
				.closest( 'tr' )
				.toggle( $( '#wcf_override_global_coupon' ).is( ':checked' ) );
			$( document ).on(
				'click',
				'#wcf_override_global_coupon',
				function () {
					$( coupon_child_fields )
						.closest( 'tr' )
						.fadeToggle(
							$( '#wcf_override_global_coupon' ).is( ':checked' )
						);
				}
			);
		},

		send_test_email() {
			let email_body = '';
			if (
				jQuery( '#wp-wcf_email_body-wrap' ).hasClass( 'tmce-active' )
			) {
				email_body = tinyMCE.get( 'wcf_email_body' ).getContent();
			} else {
				email_body = jQuery( '#wcf_email_body' ).val();
			}

			const email_subject = $( '#wcf_email_subject' ).val();
			const email_send_to = $( '#wcf_send_test_email' ).val();
			const email_template_id =
				document.getElementsByName( 'id' )[ 0 ].value;
			const wp_nonce = $( '#_wpnonce' ).val();

			$( this ).next( 'div.error' ).remove();

			if ( ! $.trim( email_body ) ) {
				$( this ).after(
					'<div class="error-message wcf-ca-error-msg"> Email body is required! </div>'
				);
			} else if ( ! $.trim( email_subject ) ) {
				$( this ).after(
					'<div class="error-message wcf-ca-error-msg"> Email subject is required! </div>'
				);
			} else if ( ! $.trim( email_send_to ) ) {
				$( this ).after(
					'<div class="error-message wcf-ca-error-msg"> You must add your email id! </div>'
				);
			} else {
				const data = {
					email_subject,
					email_body,
					email_send_to,
					email_template_id,
					action: 'wcf_ca_preview_email_send',
					security: wp_nonce,
				};
				$( '#wcf_preview_email' )
					.css( 'cursor', 'wait' )
					.attr( 'disabled', true );

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post( ajaxurl, data, function ( response ) {
					$( '#mail_response_msg' ).empty().fadeIn();

					if ( response.success ) {
						const success_string =
							'<strong> Email has been sent successfully! </strong>';
						$( '#mail_response_msg' )
							.css( 'color', 'green' )
							.html( success_string )
							.delay( 3000 )
							.fadeOut();
					} else {
						const error_string =
							'<strong> Email sending failed! Please check your SMTP settings!  </a></strong>';
						$( '#mail_response_msg' )
							.css( 'color', 'red' )
							.html( error_string )
							.delay( 3000 )
							.fadeOut();
					}
					$( '#wcf_preview_email' )
						.css( 'cursor', '' )
						.attr( 'disabled', false );
				} );
			}

			$( '.wcf-ca-error-msg' ).delay( 2000 ).fadeOut();
		},

		delete_coupons() {
			if ( confirm( wcf_ca_localized_vars._confirm_msg ) ) {
				const data = {
					action: 'wcf_ca_delete_garbage_coupons',
					security: wcf_ca_localized_vars._delete_coupon_nonce,
				};
				$( '.wcf-ca-spinner' ).show();

				$( '.wcf-ca-spinner' ).addClass( 'is-active' );
				$( '#wcf_ca_delete_coupons' )
					.css( 'cursor', 'wait' )
					.attr( 'disabled', true );
				$.post( ajaxurl, data, function ( response ) {
					$( '.wcf-ca-response-msg' ).empty().fadeIn();
					if ( response.success ) {
						$( '.wcf-ca-spinner' ).hide();
						$( '.wcf-ca-response-msg' )
							.css( 'color', 'green' )
							.html( response.data )
							.delay( 5000 )
							.fadeOut();
					}

					$( '#wcf_ca_delete_coupons' )
						.css( 'cursor', '' )
						.attr( 'disabled', false );
				} );
			}
		},
		export_orders() {
			if ( confirm( wcf_ca_localized_vars._confirm_msg_export ) ) {
				window.location.href =
					window.location.search +
					'&export_data=true&security=' +
					wcf_ca_localized_vars._export_orders_nonce;
			}
		},
		export_templates( e, ids ) {
			if ( e ) {
				e.preventDefault();
			}
			const $el = e ? $( e.currentTarget ) : $( '#wcf-export-templates' );
			const nonce = $el.data( 'nonce' );
			const id = $el.data( 'id' );
			const data = {
				action: 'wcf_ca_export_email_templates',
				_wpnonce: nonce,
			};
			if ( Array.isArray( ids ) && ids.length ) {
				data.ids = ids;
			} else if ( id !== undefined ) {
				data.ids = [ id ];
			}
			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				dataType: 'json',
				data,
				beforeSend() {
					if ( $el.attr( 'id' ) === 'wcf-export-templates' ) {
						$el.prop( 'disabled', true ).text( 'Exporting...' );
						$( '#wcf-export-spinner' )
							.addClass( 'is-active' )
							.show();
					}
				},
			} )
				.done( function ( resData ) {
					const blob = new Blob( [ JSON.stringify( resData ) ], {
						type: 'application/json',
					} );
					const url = window.URL.createObjectURL( blob );
					const a = document.createElement( 'a' );
					a.href = url;
					a.download = 'cart_abandonment_email_templates.json';
					document.body.appendChild( a );
					a.click();
					window.URL.revokeObjectURL( url );
					a.remove();
				} )
				.always( function () {
					if ( $el.attr( 'id' ) === 'wcf-export-templates' ) {
						$el.prop( 'disabled', false ).text( 'Export' );
						$( '#wcf-export-spinner' )
							.removeClass( 'is-active' )
							.hide();
					}
				} );
		},
		handle_bulk_action( e ) {
			const $btn = $( e.currentTarget );
			const selector =
				$btn.attr( 'id' ) === 'doaction'
					? '#bulk-action-selector-top'
					: '#bulk-action-selector-bottom';
			if ( $( selector ).val() === 'export_email_templates' ) {
				e.preventDefault();
				const ids = $( 'input[name="id[]"]:checked' )
					.map( function () {
						return $( this ).val();
					} )
					.get();
				if ( ids.length ) {
					EmailTemplatesAdmin.export_templates( null, ids );
				}
			}
		},
		toggle_activate_template_on_grid() {
			const $switch = $( this ),
				state = $switch.attr( 'wcf-ca-template-switch' ),
				new_state = state === 'on' ? 'off' : 'on',
				css = state === 'on' ? 'green' : 'red';

			$.post(
				ajaxurl,
				{
					action: 'activate_email_templates',
					id: $( this ).attr( 'id' ),
					state,
					security: wcf_ca_details.email_toggle_button_nonce,
				},
				function ( response ) {
					$( '#wcf_activate_email_template' ).val(
						new_state === 'on' ? 1 : 0
					);

					$( '.wcar_tmpl_response_msg' ).remove();

					$(
						"<span class='wcar_tmpl_response_msg'> " +
							response.data +
							' </span>'
					)
						.insertAfter( $switch )
						.delay( 2000 )
						.fadeOut()
						.css( 'color', css );
				}
			);
		},

		toggle_activate_template() {
			const $switch = $( this ),
				state = $switch.attr( 'wcf-ca-template-switch' );
			const new_state = state === 'on' ? 'off' : 'on';
			$( '#wcf_activate_email_template' ).val(
				new_state === 'on' ? 1 : 0
			);
			$switch.attr( 'wcf-ca-template-switch', new_state );
		},

		open_import_modal() {
			$( '#wcf-import-modal' ).show();
		},

		close_import_modal() {
			$( '#wcf-import-modal' ).hide();
		},

		import_templates( e ) {
			e.preventDefault();
			const fileInput = document.getElementById( 'wcf-ca-import-file' );
			if ( ! fileInput.files.length ) {
				return;
			}
			const reader = new FileReader();
			reader.onload = function ( evt ) {
				let templates = evt.target.result;
				if ( typeof templates === 'string' ) {
					try {
						templates = JSON.parse( templates );
					} catch ( error ) {
						console.error( 'Error parsing JSON:', error );
						return;
					}
				}
				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wcf_ca_import_email_templates',
						_wpnonce: $(
							'#wcf-ca-import-form [name="_wpnonce"]'
						).val(),
						templates: JSON.stringify( templates ),
					},
					beforeSend: EmailTemplatesAdmin.show_import_loader,
				} ).done( function () {
					window.location.reload();
				} );
			};
			reader.readAsText( fileInput.files[ 0 ] );
		},

		handle_file_change() {
			const file = this.files[ 0 ];
			if ( file ) {
				$( '.wcf-ca-file-name' ).text( file.name );
				$( '.wcf-ca-file-preview' ).show();
			} else {
				EmailTemplatesAdmin.reset_import_file();
			}
		},

		handle_file_drop( e ) {
			EmailTemplatesAdmin.prevent_default( e );
			const files = e.originalEvent.dataTransfer.files;
			if ( files && files.length ) {
				const input = document.getElementById( 'wcf-ca-import-file' );
				input.files = files;
				EmailTemplatesAdmin.handle_file_change.call( input );
			}
		},

		prevent_default( e ) {
			e.preventDefault();
			e.stopPropagation();
		},

		reset_import_file() {
			$( '#wcf-ca-import-file' ).val( '' );
			$( '.wcf-ca-file-preview' ).hide();
		},

		show_import_loader() {
			$( '#wcf-ca-import-form .spinner' ).addClass( 'is-active' ).show();
			$( '#wcf-ca-import-form button[type="submit"]' ).prop(
				'disabled',
				true
			);
		},
	};

	$( function () {
		EmailTemplatesAdmin.init();
	} );
} )( jQuery );
