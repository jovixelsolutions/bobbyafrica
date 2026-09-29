/* global gtag, ga, __gaTracker, dataLayer, gtag_report_conversion, fbq */
// Click to Chat - Share

/* global ht_ctc_share_var */
/**
 * will create variable.. ht_ctc_share_var like.. ht_ctc_share_var ..
 */
( function htCtcShareModule ( $ ) {
	// ready
	$( function handleShareReady () {

		var url = window.location.href;

		var isMobile = 'no';
		var is_iphone = 'no';
		try {
			// Where user can install app.
			// instead: /Android|webOS|...|Opera Mini/i.test(navigator.userAgent)
			const mobileUserAgentPattern =
				/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
			if (
				typeof navigator.userAgent !== 'undefined' &&
				navigator.userAgent.match( mobileUserAgentPattern )
			) {
				isMobile = 'yes';
				console.log( 'User agent: isMobile: ' + isMobile );

				// if iphone
				if ( navigator.userAgent.match( /iPhone/i ) ) {
					is_iphone = 'yes';
					console.log( 'User agent: is_iphone: ' + is_iphone );
				}

				// // if iphone chrome
				// if (navigator.userAgent.match(/CriOS/i)) {
				// }
				// // or
				// if (/iPhone/.test(navigator.userAgent) && /CriOS/.test(navigator.userAgent)) {
				// }
			}
		} catch ( error ) {
			console.warn( 'navigator.userAgent unavailable while detecting share mode', error );
		}

		if ( 'no' === isMobile ) {
			// isMobile yes/no,  desktop > 1025
			isMobile =
				typeof screen.width !== 'undefined' && screen.width > 1025 ? 'no' : 'yes';
			console.log( 'screen width: isMobile: ' + isMobile );
		}

		var post_title = typeof document.title !== 'undefined' ? document.title : '';

		var share = '';
		variable_ctc_share();

		/**
		 * get ht_ctc_share_var and assing to ctc variable
		 */
		function variable_ctc_share () {
			if ( typeof ht_ctc_share_var !== 'undefined' ) {
				share = ht_ctc_share_var;
				console.log( 'ht_ctc_share_var - share_var: ', share );
			}
		}

		function shareHtCtc () {
			var ht_ctc_share = document.querySelector( '.ht-ctc-share' );
			if ( ht_ctc_share ) {
				shareDisplay( ht_ctc_share );
				ht_ctc_share.addEventListener( 'click', function handleShareButtonClick () {
					htCtcShareClick( ht_ctc_share );
				} );
			}

			// shortcode
			$( document )
				.on( 'click', '.ht-ctc-sc-share', function handleShareShortcodeClick () {
					var data_link = this.getAttribute( 'data-ctc-link' );
					data_link = encodeURI( data_link );
					window.open( data_link, '_blank', 'noopener' );

					// analytics
					share_analytics( this );
				} );
		}
		shareHtCtc();

		// Hide based on device
		function shareDisplay ( element ) {
			const cssStyles = element.getAttribute( 'data-css' );
			if ( isMobile === 'yes' ) {
				var display_mobile = element.getAttribute( 'data-display_mobile' );
				if ( 'show' === display_mobile ) {
					// remove desktop style
					var removeDesktopShare = document.querySelector( '.ht_ctc_desktop_share' );
					if ( removeDesktopShare ) {
						removeDesktopShare.remove();
					}

					var position_mobile = element.getAttribute( 'data-position_mobile' );
					element.style.cssText = position_mobile + cssStyles;
					display( element );
				}
			} else {
				var display_desktop = element.getAttribute( 'data-display_desktop' );
				if ( 'show' === display_desktop ) {
					// remove mobile style
					var removeMobileShare = document.querySelector( '.ht_ctc_mobile_share' );
					if ( removeMobileShare ) {
						removeMobileShare.remove();
					}

					var position = element.getAttribute( 'data-position' );
					element.style.cssText = position + cssStyles;
					display( element );
				}
			}
		}

		function display ( element ) {
			// p.style.display = "block";
			try {
				var dt = parseInt( element.getAttribute( 'data-show_effect' ) );

				// var dt = parseInt( element.getAttribute( 'data-show_effect' ), 10 );
				$( element )
					.show( dt );
			} catch ( error ) {
				console.warn( 'Share display fallback triggered', error );
				element.style.display = 'block';
			}

			// hover effect
			ht_ctc_share_things( element );
		}

		function ht_ctc_share_things ( element ) {
			// animations
			var animateclass = element.getAttribute( 'data-an_type' );
			var an_time = $( element )
				.hasClass( 'ht_ctc_entry_animation' ) ?
				1200 :
				120;

			setTimeout( function runShareAnimation () {
				element.classList.add( 'ht_ctc_animation', animateclass );
			}, an_time );

			// hover effects
			$( '.ht-ctc-share' )
				.hover(
					function showShareHoverCta () {
						$( '.ht-ctc-share .ht-ctc-cta-hover' )
							.show( 220 );
					},
					function hideShareHoverCta () {
						$( '.ht-ctc-share .ht-ctc-cta-hover' )
							.hide( 100 );
					},
				);
		}

		// floating style - click
		function htCtcShareClick ( values ) {
			// link
			share_link( values );

			// analytics
			share_analytics( values );
		}

		// analytics
		function share_analytics ( values ) {
			console.log( 'share analytics' );

			var id = values.getAttribute( 'data-share_text' );

			// Google Analytics
			var ga_category = 'Click to Chat for WhatsApp';
			var ga_action = 'share: ' + id;
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
						return_type: 'share',
						ID: id,
						Title: post_title,
						URL: url,
					} );
				}
			}
		}

		// link share
		function share_link ( values ) {
			var share_text = values.getAttribute( 'data-share_text' );
			var webandapi = values.getAttribute( 'data-webandapi' );

			// web/api.whatsapp or api.whatsapp
			var share_nav = 'api';
			if ( 'webapi' === webandapi ) {
				share_nav = isMobile === 'yes' ? 'api' : 'web';
			}
			var base_link = 'https://' + share_nav + '.whatsapp.com/send';

			var target = '_blank';

			// if its an iphone, then target is _self
			if ( is_iphone === 'yes' ) {
				target = '_self';
			}

			console.log( 'base_link: ', base_link );
			console.log( 'share_text: ', share_text );
			console.log( 'target: ', target );

			window.open( base_link + '?text=' + share_text, target, 'noopener' );
		}
	} );
} )( jQuery );
