/* global tinyMCE */
( function htCtcGreetingsModule ( $ ) {
	// ready
	$( function handleGreetingsReady () {
		var greetingsTemplateElement = document.querySelector( '.pr_greetings_template' );
		if ( greetingsTemplateElement ) {
			try {
				greetings_template();
			} catch ( error ) {
				console.error( 'Failed to initialize greetings template', error );
			}
		}

		var hasGreetingsPage = document.querySelector( '.ctc-admin-greetings-page' );
		var hasWooPage = document.querySelector( '.ctc-admin-woo-page' );
		if ( hasGreetingsPage || hasWooPage ) {
			try {
				editor();
			} catch ( error ) {
				console.error( 'Failed to initialize greetings editor', error );
			}
		}

		/**
		 * display settings based on Greetings template selection
		 */
		function greetings_template () {
			var $greetingsTemplateSelect = $( '.pr_greetings_template select' );
			var greetings_template = $greetingsTemplateSelect.find( ':selected' )
				.val();

			if ( greetings_template === 'no' || '' === greetings_template ) {
				$( '.g_content_collapsible' )
					.hide();
			} else {
				$( '.g_content_collapsible' )
					.show();
			}

			// greetings-1
			if ( greetings_template === 'greetings-1' ) {
				$( '.ctc_greetings_settings.ctc_g_1' )
					.show();
				$( '.pr_ht_ctc_greetings_1' )
					.show();
				$( '.pr_ht_ctc_greetings_settings' )
					.show();
				$( '.ctc_greetings_notes' )
					.show();
				optin();
			}

			// greetings-2
			if ( greetings_template === 'greetings-2' ) {
				$( '.ctc_greetings_settings.ctc_g_2' )
					.show();
				$( '.pr_ht_ctc_greetings_2' )
					.show();
				$( '.pr_ht_ctc_greetings_settings' )
					.show();
				$( '.ctc_greetings_notes' )
					.show();
				optin();
			}

			// on change
			$( '.pr_greetings_template select' )
				.on( 'change', function handleGreetingsTemplateOptionChange ( event ) {
					var greetings_template = event.target.value;

					// ctc_greetings_settings
					if ( greetings_template === 'no' ) {
						$( '.g_content_collapsible' )
							.hide( 100 );
						$( ' .ctc_greetings_settings' )
							.hide();
					} else {
						// $(" ." + greetings_template).show(100);

						$( '.g_content_collapsible' )
							.show();

						// if not no - then first hide all and again display required fields..
						if (
							greetings_template === 'greetings-2' ||
							greetings_template === 'greetings-1'
						) {
							$( ' .ctc_greetings_settings' )
								.hide();
						}
						$( '.ctc_greetings_notes' )
							.show();

						// greetings-1
						if ( greetings_template === 'greetings-1' ) {
							$( '.ctc_greetings_settings.ctc_g_1' )
								.show( 100 );
							$( '.pr_ht_ctc_greetings_1' )
								.show( 100 );
							optin();
						}

						// greetings-2
						if ( greetings_template === 'greetings-2' ) {
							$( '.ctc_greetings_settings.ctc_g_2' )
								.show( 100 );
							$( '.pr_ht_ctc_greetings_2' )
								.show( 100 );
							optin();
						}

						$( '.pr_ht_ctc_greetings_settings' )
							.show();
					}
				} );

			// optin - show/hide
			function optin () {
				if ( $( '.is_opt_in' )
					.is( ':checked' ) ) {
					$( '.pr_opt_in ' )
						.show( 200 );
				} else {
					$( '.pr_opt_in ' )
						.hide( 200 );
				}
			}

			// optin change
			$( '.is_opt_in' )
				.on( 'change', function handleOptInToggle ( event ) {
					optin();
				} );
		}

		/**
		 * greetings header image
		 *
		 * @since 3.34
		 */
		function greetings_header_image () {
			var mediaUploader;
			var attachment;
			var image_url;

			// Event listener for adding an image
			$( '.ctc_add_image_wp' )
				.on( 'click', function handleAddGreetingImageClick ( event ) {
					event.preventDefault();

					// If mediaUploader already exists, open it
					if ( mediaUploader ) {
						mediaUploader.open();
						return;
					}

					// Create a new media uploader instance
					mediaUploader = wp.media.frames.file_frame = wp.media( {
						title: 'Select Header Image',
						button: {
							text: 'Select',
						},
						multiple: false,
					} );

					// When an image is selected
					mediaUploader.on( 'select', function processGreetingImageSelection () {
						attachment = mediaUploader.state()
							.get( 'selection' )
							.first()
							.toJSON();
						console.log( attachment );

						// if closed with out selecting image
						if ( typeof attachment === 'undefined' ) { return true; }

						// Set the image URL and update the preview
						image_url = attachment.url;
						$( '.g_header_image' )
							.val( image_url );
						$( '.g_header_image_preview' )
							.attr( 'src', image_url );
						$( '.g_header_image_preview' )
							.show();
						$( '.ctc_remove_image_wp' )
							.show();
						header_image_badge();

						console.log( 'image_url: ' + image_url );

						// Custom event: ht_ctc_event_greetings_header_image
						document.dispatchEvent( new CustomEvent(
							'ht_ctc_event_greetings_header_image',
							{
								detail: image_url,
							},
						) );
					} );

					// Open the media uploader
					mediaUploader.open();
				} );

			// Event listener for removing an image
			$( '.ctc_remove_image_wp' )
				.on( 'click', function handleRemoveGreetingImageClick ( event ) {
					event.preventDefault();
					$( '.g_header_image' )
						.val( '' );
					$( '.g_header_image_preview' )
						.hide();
					$( '.ctc_remove_image_wp' )
						.hide();
					header_image_badge();
					return;
				} );

			// Function to show/hide elements based on the presence of a header image
			function header_image_badge () {
				// pr_g_header_online_badge display only if header image is set
				console.log( $( '.g_header_image' )
					.val() );

				// If no header image is set, hide related elements
				// g_header_image type is string
				if ( $( '.g_header_image' )
					.val() === '' ) {
					$( '.row_g_header_online_status' )
						.hide();
					$( '.row_g_header_online_status_color' )
						.hide();
					console.log( 'hide' );
				} else {
					// If a header image is set, show related elements
					$( '.row_g_header_online_status' )
						.show();

					// Show/hide online status color based on checkbox state
					if ( $( '.g_header_online_status' )
						.is( ':checked' ) ) {
						$( '.row_g_header_online_status_color' )
							.show();
					} else {
						$( '.row_g_header_online_status_color' )
							.hide();
					}
					console.log( 'show' );
				}
			}

			// Initial call to set the correct visibility of elements
			header_image_badge();

			// Event listener for changes to the online status checkbox
			$( '.g_header_online_status' )
				.on( 'change', function handleHeaderOnlineStatusChange () {
					console.log( 'on change g_header_online_status' );

					// Show/hide online status color based on checkbox state
					if ( $( '.g_header_online_status' )
						.is( ':checked' ) ) {
						console.log( 'g_header_online_status checked' );
						$( '.row_g_header_online_status_color' )
							.show();
					} else {
						console.log( 'g_header_online_status unchecked' );
						$( '.row_g_header_online_status_color' )
							.hide();
					}
				} );
		}
		greetings_header_image();

		/**
		 * tinymce editor
		 * only on greetings, woo pages
		 *  bg color
		 */
		function editor () {
			var check = 1;
			var check_interval = 1000;
			var check_times = 28; // ( check_times * check_interval = total milliseconds )

			function tiny_bg () {
				if ( document.getElementById( 'header_content_ifr' ) ) {
					try {
						tiny_bg_color();
					} catch ( error ) {
						console.error( 'tiny_bg_color() failed', error );
					}
				} else {
					check++;
					if ( check < check_times ) {
						setTimeout( tiny_bg, check_interval );
					}
				}
			}

			// also calls from setTimeout....
			tiny_bg();

			function tiny_bg_color () {
				console.log( 'tiny_bg_color' );

				try {
					// this works for single editor.
					// tinyMCE.activeEditor.dom.setStyle(
					// 	tinyMCE.activeEditor.getBody(), 'backgroundColor', '#26a69a'
					// );

					// for multiple editors
					Array.prototype.forEach.call(
						tinyMCE.editors,
						function applyTinyMceBackground ( editor ) {
							if ( ! editor || ! editor.dom || ! editor.getBody ) {
								return;
							}
							editor.dom.setStyle(
								editor.getBody(),
								'backgroundColor',
								'#26a69a',
							);
						},
					);
				} catch ( error ) {
					console.error( 'Unable to set TinyMCE background color', error );
				}

				// var i = document.querySelectorAll(".ctc_wp_editor iframe");
				// i.forEach(e => {
				//     var elmnt = e.contentWindow.document.getElementsByTagName("body")[0];
				//     elmnt.style.backgroundColor = "#26a69a";
				// });
			}
		}
	} );
} )( jQuery );
