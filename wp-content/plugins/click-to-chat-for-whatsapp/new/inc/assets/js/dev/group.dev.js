/* global gtag, ga, __gaTracker, dataLayer, gtag_report_conversion, fbq */
// Click to Chat - Group
( function htCtcGroupModule ( $ ) {
	// ready
	$( function handleGroupReady () {

		var url = window.location.href;
		var is_mobile = typeof screen.width !== 'undefined' && screen.width > 1024 ? 'no' : 'yes';
		var post_title = typeof document.title !== 'undefined' ? document.title : '';

		function group_ht_ctc () {
			var ht_ctc_group = document.querySelector( '.ht-ctc-group' );
			if ( ht_ctc_group ) {
				group_display( ht_ctc_group );

				// click
				ht_ctc_group.addEventListener( 'click', function handleGroupButtonClick () {
					// link
					var base_link = 'https://chat.whatsapp.com/';
					var group_id = ht_ctc_group.getAttribute( 'data-group_id' );
					window.open( base_link + group_id, '_blank', 'noopener' );

					// analytics
					group_analytics( ht_ctc_group );
				} );
			}

			// shortcode - click
			$( document )
				.on( 'click', '.ht-ctc-sc-group', function handleGroupShortcodeClick () {
					var data_link = this.getAttribute( 'data-ctc-link' );
					data_link = encodeURI( data_link );
					window.open( data_link, '_blank', 'noopener' );

					group_analytics( this );
				} );
		}
		group_ht_ctc();

		// Hide based on device
		function group_display ( element ) {
			const cssStyles = element.getAttribute( 'data-css' );
			if ( is_mobile === 'yes' ) {
				var display_mobile = element.getAttribute( 'data-display_mobile' );
				if ( 'show' === display_mobile ) {
					// remove desktop style
					var removeDesktopGroup = document.querySelector( '.ht_ctc_desktop_group' );
					if ( removeDesktopGroup ) {
						removeDesktopGroup.remove();
					}

					var position_mobile = element.getAttribute( 'data-position_mobile' );
					element.style.cssText = position_mobile + cssStyles;
					display( element );
				}
			} else {
				var display_desktop = element.getAttribute( 'data-display_desktop' );
				if ( 'show' === display_desktop ) {
					// remove mobile style
					var removeMobileGroup = document.querySelector( '.ht_ctc_mobile_group' );
					if ( removeMobileGroup ) {
						removeMobileGroup.remove();
					}

					var position = element.getAttribute( 'data-position' );
					element.style.cssText = position + cssStyles;
					display( element );
				}
			}
		}

		/**
		 * display
		 * animations
		 * cta hover effects
		 */
		function display ( element ) {
			// p.style.display = "block";
			try {
				// var dt = parseInt( element.getAttribute( 'data-show_effect' ), 10 );
				// NaN this can works perfect with jQuery show function to display css animations
				var dt = parseInt( element.getAttribute( 'data-show_effect' ) );

				$( element )
					.show( dt );
			} catch ( error ) {
				console.warn( 'Group display fallback triggered', error );
				element.style.display = 'block';
			}

			// animations
			var animateclass = element.getAttribute( 'data-an_type' );
			var an_time = $( element )
				.hasClass( 'ht_ctc_entry_animation' ) ?
				1200 :
				120;

			setTimeout( function runGroupAnimation () {
				element.classList.add( 'ht_ctc_animation', animateclass );
			}, an_time );

			// cta hover effects
			$( '.ht-ctc-group' )
				.hover(
					function showGroupHoverCta () {
						$( '.ht-ctc-group .ht-ctc-cta-hover' )
							.show( 220 );
					},
					function hideGroupHoverCta () {
						$( '.ht-ctc-group .ht-ctc-cta-hover' )
							.hide( 100 );
					},
				);
		}

		// analytics
		function group_analytics ( values ) {
			console.log( 'group analytics' );

			var id = values.getAttribute( 'data-group_id' );

			// Google Analytics
			var ga_category = 'Click to Chat for WhatsApp';
			var ga_action = 'group: ' + id;
			var ga_label = post_title + ', ' + url;

			// if ga_enabled
			if ( 'yes' === values.getAttribute( 'data-is_ga_enable' ) ) {
				console.log( 'google analytics' );
				if ( typeof gtag !== 'undefined' ) {
					console.log( 'gtag' );
					gtag( 'event', ga_action, {
						event_category: ga_category,
						event_label: ga_label,
					} );
				} else if ( typeof ga !== 'undefined' && typeof ga.getAll !== 'undefined' ) {
					console.log( 'ga' );
					var tracker = ga.getAll();
					tracker[ 0 ].send( 'event', ga_category, ga_action, ga_label );

					// ga('send', 'event', ga_category, ga_action, ga_label);
				} else if ( typeof __gaTracker !== 'undefined' ) {
					console.log( '__gaTracker' );
					__gaTracker( 'send', 'event', ga_category, ga_action, ga_label );
				}
			}

			// dataLayer
			if ( typeof dataLayer !== 'undefined' ) {
				console.log( 'dataLayer' );
				dataLayer.push( {
					event: 'Click to Chat',
					event_category: ga_category,
					event_label: ga_label,
					event_action: ga_action,
				} );
			}

			// google ads - call conversation code
			if ( 'yes' === values.getAttribute( 'data-ga_ads' ) ) {
				console.log( 'google ads enabled' );
				if ( typeof gtag_report_conversion !== 'undefined' ) {
					console.log( 'calling gtag_report_conversion' );
					gtag_report_conversion();
				}
			}

			// FB Pixel
			if ( 'yes' === values.getAttribute( 'data-is_fb_pixel' ) ) {
				console.log( 'fb pixel' );
				if ( typeof fbq !== 'undefined' ) {
					fbq( 'trackCustom', 'Click to Chat by HoliThemes', {
						Category: 'Click to Chat for WhatsApp',
						return_type: 'group',
						ID: id,
						Title: post_title,
						URL: url,
					} );
				}
			}
		}
	} );
} )( jQuery );
