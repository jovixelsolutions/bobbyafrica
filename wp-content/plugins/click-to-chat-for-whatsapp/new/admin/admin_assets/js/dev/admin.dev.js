/* global M, intlTelInput */
// Click to Chat
document.addEventListener( 'DOMContentLoaded', function initializeMaterializeComponents () {
	// md
	try {
		if ( typeof M !== 'undefined' ) {
			const selectElements = document.querySelectorAll( 'select' );
			M.FormSelect.init( selectElements, {} );
			const collapsibleElements = document.querySelectorAll( '.collapsible' );
			M.Collapsible.init( collapsibleElements, {} );
			const modalElements = document.querySelectorAll( '.modal' );
			M.Modal.init( modalElements, {} );
			const tooltippedElements = document.querySelectorAll( '.tooltipped' );
			M.Tooltip.init( tooltippedElements, {} );
		}
	} catch ( error ) {
		console.log( error );
	}
} );

( function htCtcAdminModule ( $ ) {
	console.log( 'ht_ctc_admin.js loaded' );

	// ready
	$( function handleAdminReady () {
		// var all_intl_instances = [];

		function isSafeKey ( key ) {
			return (
				typeof key === 'string' &&
				key.length > 0 &&
				'__proto__' !== key &&
				'prototype' !== key &&
				'constructor' !== key
			);
		}

		var admin_ctc = {};
		try {
			document.dispatchEvent( new CustomEvent( 'ht_ctc_fn_all', {
				detail: { admin_ctc, ctc_getItem, ctc_setItem, intl_init, intl_onchange },
			} ) );
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: ht_ctc_fn_all custom event' );
		}

		// local storage - admin
		var ht_ctc_admin = new Map();

		var ht_ctc_admin_var = window.ht_ctc_admin_var ? window.ht_ctc_admin_var : {};
		console.log( ht_ctc_admin_var );

		if ( localStorage.getItem( 'ht_ctc_admin' ) ) {
			try {
				var ht_ctc_admin_data = JSON.parse( localStorage.getItem( 'ht_ctc_admin' ) );
				ht_ctc_admin = new Map( Object.entries( ht_ctc_admin_data || {} ) );
			} catch ( error ) {
				console.log( error );
				ht_ctc_admin = new Map();
			}
		}

		// get items from ht_ctc_admin
		function ctc_getItem ( item ) {
			if ( isSafeKey( item ) && ht_ctc_admin.has( item ) ) {
				return ht_ctc_admin.get( item );
			}
			return false;
		}

		// set items to ht_ctc_admin storage
		function ctc_setItem ( name, value ) {
			if ( ! isSafeKey( name ) ) {
				return;
			}
			ht_ctc_admin.set( name, value );
			var newValues = JSON.stringify( Object.fromEntries( ht_ctc_admin ) );
			localStorage.setItem( 'ht_ctc_admin', newValues );
		}

		/**
		 * ht_ctc_storage - public
		 * to update public side - localStorage for admins to see the changes.
		 */
		var ht_ctc_storage = new Map();

		if ( localStorage.getItem( 'ht_ctc_storage' ) ) {
			try {
				var ht_ctc_storage_data = JSON.parse( localStorage.getItem( 'ht_ctc_storage' ) );
				ht_ctc_storage = new Map( Object.entries( ht_ctc_storage_data || {} ) );
			} catch ( error ) {
				console.log( error );
				ht_ctc_storage = new Map();
			}
		}

		// // get items from ht_ctc_storage
		// function ctc_front_getItem ( item ) {
		// 	if ( isSafeKey( item ) && ht_ctc_storage.has( item ) ) {
		// 		return ht_ctc_storage.get( item );
		// 	}
		// 	return false;
		// }

		// set items to ht_ctc_storage storage
		function ctc_front_setItem ( name, value ) {
			if ( ! isSafeKey( name ) ) {
				return;
			}
			ht_ctc_storage.set( name, value );
			var newValues = JSON.stringify( Object.fromEntries( ht_ctc_storage ) );
			localStorage.setItem( 'ht_ctc_storage', newValues );
		}

		// md
		try {
			$( 'select' )
				.formSelect();
			$( '.collapsible' )
				.collapsible();
			$( '.modal' )
				.modal();
			$( '.tooltipped' )
				.tooltip();
		} catch ( error ) {
			console.log( error );
		}

		// md tabs
		try {
			var $tabs = $( '.tabs' );

			$( document )
				.on( 'click', '.open_tab', function handleOpenTabClick () {
					var tab = $( this )
						.attr( 'data-tab' );
					$tabs.tabs( 'select', tab );
					ctc_setItem( 'woo_tab', '#' + tab );
				} );

			$( document )
				.on( 'click', '.md_tab_li', function handleMaterialTabClick () {
					var link = $( this )
						.children( 'a' );
					var href = link.attr( 'href' ) || '';
					if ( ! href.startsWith( '#' ) ) {
						return;
					}

					window.location.hash = href;
					ctc_setItem( 'woo_tab', href );
				} );

			$tabs.tabs();

			// only on woo page..
			var wooPageElement = document.querySelector( '.ctc-admin-woo-page' );
			var storedWooTab = ctc_getItem( 'woo_tab' );
			if ( wooPageElement && storedWooTab ) {
				var wooTab = storedWooTab;

				// setTimeout(() => {
				//     $(".tabs").tabs('select', wooTab);
				// }, 2500);

				wooTab = wooTab.replace( '#', '' );
				setTimeout( function triggerStoredTabClick () {
					$( '[data-tab=' + wooTab + ']' )
						.trigger( 'click' );
				}, 1200 );
			}
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: md tabs' );
		}

		// intl
		try {
			// @parm: class name
			intl_input( 'intl_number' );
			$( '.intl_error' )
				.remove();
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: intl_input' );
			$( '.greetings_links' )
				.hide();
			$( '.intl_error' )
				.show();
		}

		// wpColorPicker
		// http://automattic.github.io/Iris/#change
		var colorPicker = {
			palettes: [
				'#000000',
				'#FFFFFF',
				'#075e54',
				'#128C7E',
				'#25d366',
				'#DCF8C6',
				'#34B7F1',
				'#ECE5DD',
				'#00a884',
			],
			change: function handleColorPickerChange ( event, ui ) {
				try {
					var element = event.target;
					console.log( element );

					var color = ui.color.toString();
					console.log( color );

					// check if element have data-update attribute
					var updateType = $( element )
						.attr( 'data-update-type' ); // color, background-color, border-color, ..
					console.log( updateType );

					var updateClass = $( element )
						.attr( 'data-update-selector' ); // the other filed to update
					console.log( updateClass );

					if ( updateType && updateClass ) {
						console.log( 'update' );
						$( updateClass )
							.css( updateType, color );

						// If updating message box, also change ::before element via CSS variable
						if ( updateClass === '.template-greetings-1 .ctc_g_message_box' ) {
							document.documentElement.style.setProperty(
								'--ctc_g_message_box_bg_color',
								color,
							);
						}

						// if data-update-2-type and data-update-2-selector exists
						if (
							$( element )
								.attr( 'data-update-2-type' ) &&
							$( element )
								.attr( 'data-update-2-selector' )
						) {
							console.log( 'update-2-type' );
							$( $( element )
								.attr( 'data-update-2-selector' ) )
								.css(
									$( element )
										.attr( 'data-update-2-type' ),
									color,
								);
						}
					}
				} catch ( error ) {
					console.log( error );
					console.log( 'cache: wpColorPicker on change' );
				}
			},
		};
		try {
			$( '.ht-ctc-color' )
				.wpColorPicker( colorPicker );
			console.log( 'wpColorPicker passed args' );
		} catch ( error ) {
			console.log( error );
			$( '.ht-ctc-color' )
				.wpColorPicker();
			console.log( 'wpColorPicker default' );
		}

		// functions
		showHideOptions();
		styles();
		callToAction();
		htCtcAdminAnimations();
		desktopMobile();
		notificationBadge();
		wn();
		hook();
		ss();
		other();

		try {
			wooPage();
			collapsible();
			updateFrontendStorage();
			analytics();
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: wooPage(), collapsible(), updateFrontendStorage()' );
		}

		// jquery ui
		try {
			$( '.ctc_sortable' )
				.sortable( {
					cursor: 'move',
					handle: '.handle',
				} );
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: jquery ui - sortable' );
		}

		// show/hide settings
		function showHideOptions () {
			// default display
			var val = $( '.global_display:checked' )
				.val();

			if ( val === 'show' ) {
				$( '.global_show_or_hide_icon' )
					.addClass( 'dashicons dashicons-visibility' );
				$( '.hide_settings' )
					.show();
				$( '.show_hide_types .show_btn' )
					.attr( 'disabled', 'disabled' );
				$( '.show_hide_types .show_box' )
					.hide();
			} else if ( val === 'hide' ) {
				$( '.global_show_or_hide_icon' )
					.addClass( 'dashicons dashicons-hidden' );
				$( '.show_settings' )
					.show();
				$( '.show_hide_types .hide_btn' )
					.attr( 'disabled', 'disabled' );
				$( '.show_hide_types .hide_box' )
					.hide();
			}
			$( '.global_show_or_hide_label' )
				.text( '(' + val + ')' );

			// on change
			$( '.global_display' )
				.on( 'change', function handleGlobalDisplayChange ( event ) {
					var changeVal = event.target.value;
					var addClassName = '';
					var removeClassName = '';

					$( '.hide_settings' )
						.hide();
					$( '.show_settings' )
						.hide();
					$( '.show_hide_types .show_btn' )
						.removeAttr( 'disabled' );
					$( '.show_hide_types .hide_btn' )
						.removeAttr( 'disabled' );
					$( '.show_hide_types .show_box' )
						.hide();
					$( '.show_hide_types .hide_box' )
						.hide();

					if ( changeVal === 'show' ) {
						addClassName = 'dashicons dashicons-visibility';
						removeClassName = 'dashicons-hidden';
						$( '.hide_settings' )
							.show( 500 );
						$( '.show_hide_types .show_btn' )
							.attr( 'disabled', 'disabled' );
						$( '.show_hide_types .hide_box' )
							.show();
					} else if ( changeVal === 'hide' ) {
						addClassName = 'dashicons dashicons-hidden';
						removeClassName = 'dashicons-visibility';
						$( '.show_settings' )
							.show( 500 );
						$( '.show_hide_types .hide_btn' )
							.attr( 'disabled', 'disabled' );
						$( '.show_hide_types .show_box' )
							.show();
					}
					$( '.global_show_or_hide_label' )
						.text( '(' + changeVal + ')' );
					$( '.global_show_or_hide_icon' )
						.removeClass( removeClassName );
					$( '.global_show_or_hide_icon' )
						.addClass( addClassName );
				} );
		}

		// styles
		function styles () {
			// get data-style attribute from select_style_container
			// and add class to select_style_item as selected
			var desktopStyle = $( '.select_style_container' )
				.attr( 'data-style' );
			console.log( desktopStyle );
			if ( desktopStyle ) {
				$( '.select_style_item[data-style="' + desktopStyle + '"]' )
					.addClass( 'select_style_selected' );
			}

			// on click select style item
			$( '.select_style_item' )
				.on( 'click', function handleDesktopStyleSelection ( event ) {
					// select effects
					$( '.select_style_item' )
						.removeClass( 'select_style_selected' );
					$( this )
						.addClass( 'select_style_selected' );

					// update chat_select_style value
					var selectedDesktopStyle = $( this )
						.attr( 'data-style' );
					console.log( selectedDesktopStyle );
					$( '.select_style_desktop' )
						.val( selectedDesktopStyle );

					$( '.customize_styles_link' )
						.fadeOut( 100 )
						.fadeIn( 100 );
				} );

			// get data-style attribute from select_style_container
			// and add class to select_style_item as selected
			var mobileStyle = $( '.m_select_style_container' )
				.attr( 'data-style' );
			console.log( mobileStyle );
			if ( mobileStyle ) {
				$( '.m_select_style_item[data-style="' + mobileStyle + '"]' )
					.addClass( 'select_style_selected' );
			}

			// on click select style item
			$( '.m_select_style_item' )
				.on( 'click', function handleMobileStyleSelection ( event ) {
					// select effects
					$( '.m_select_style_item' )
						.removeClass( 'select_style_selected' );
					$( this )
						.addClass( 'select_style_selected' );

					// update chat_select_style value
					var selectedMobileStyle = $( this )
						.attr( 'data-style' );
					console.log( selectedMobileStyle );
					$( '.select_style_mobile' )
						.val( selectedMobileStyle );
				} );

			// If Styles for desktop, mobile not selected as expected
			if ( $( '#select_styles_issue' )
				.is( ':checked' ) && ! $( '.same_settings' )
				.is( ':checked' ) ) {
				$( '.select_styles_issue_checkbox' )
					.show();
			}
			$( '.select_styles_issue_description' )
				.on( 'click', function toggleStyleIssueDescription ( event ) {
					$( '.select_styles_issue_checkbox' )
						.toggle( 500 );
				} );

			// customize styles page:

			// dispaly all style - ask to save changes on change
			$( '#display_allstyles' )
				.on( 'change', function handleDisplayAllStylesToggle ( event ) {
					$( '.display_allstyles_description' )
						.show( 200 );
				} );

			// style-1 - add icon
			if ( $( '.s1_add_icon' )
				.is( ':checked' ) ) {
				$( '.s1_icon_settings' )
					.show();
			} else {
				$( '.s1_icon_settings' )
					.hide();
			}

			$( '.s1_add_icon' )
				.on( 'change', function handleStyleIconToggle ( event ) {
					if ( $( '.s1_add_icon' )
						.is( ':checked' ) ) {
						$( '.s1_icon_settings' )
							.show( 200 );
					} else {
						$( '.s1_icon_settings' )
							.hide( 200 );
					}
				} );

			// if m fullwidth is checked then show m_fullwidth_description else hide
			$( '.cs_m_fullwidth input' )
				.on( 'change', function handleFullWidthToggle ( event ) {
					event.preventDefault();
					var descripton = $( this )
						.closest( '.cs_m_fullwidth' )
						.find( '.m_fullwidth_description' );
					if ( $( this )
						.is( ':checked' ) ) {
						$( descripton )
							.show( 200 );
					} else {
						$( descripton )
							.hide( 200 );
					}
				} );
		}

		// url structure - custom url..
		function urlStructure () {
			console.log( 'urlStructure()' );

			function handleUrlStructureToggle ( selector, wrapSelector ) {
				const $select = $( selector );
				const $wrap = $( wrapSelector );

				function toggleWrap () {
					const selectedVal = $select.find( ':selected' )
						.val();
					if ( selectedVal === 'custom_url' ) {
						$wrap.show( 500 );
					} else {
						$wrap.hide( 500 );
					}
				}

				// Initial check
				toggleWrap();

				// On change
				$select.on( 'change', toggleWrap );
			}

			handleUrlStructureToggle( '.url_structure_d', '.custom_url_desktop' );
			handleUrlStructureToggle( '.url_structure_m', '.custom_url_mobile' );
		}
		urlStructure();

		// call to actions
		function callToAction () {
			var ctaStyles = [ '.ht_ctc_s2', '.ht_ctc_s3', '.ht_ctc_s3_1', '.ht_ctc_s7' ];
			ctaStyles.forEach( htCtcAdminCta );

			function htCtcAdminCta ( style ) {
				// default display
				var val = $( style + ' .select_cta_type' )
					.find( ':selected' )
					.val();
				if ( val === 'hide' ) {
					$( style + ' .cta_stick' )
						.hide();
				}

				// on change
				$( style + ' .select_cta_type' )
					.on( 'change', function handleCtaTypeChange ( event ) {
						var changeVal = event.target.value;
						if ( changeVal === 'hide' ) {
							$( style + ' .cta_stick' )
								.hide( 100 );
						} else {
							$( style + ' .cta_stick' )
								.show( 200 );
						}
					} );
			}
		}

		function htCtcAdminAnimations () {
			// default display
			var val = $( '.select_an_type' )
				.find( ':selected' )
				.val();
			if ( val === 'no-animation' ) {
				$( '.an_delay' )
					.hide();
				$( '.an_itr' )
					.hide();
			}

			// on change
			$( '.select_an_type' )
				.on( 'change', function handleAnimationTypeChange ( event ) {
					var changeVal = event.target.value;

					if ( changeVal === 'no-animation' ) {
						$( '.an_delay' )
							.hide();
						$( '.an_itr' )
							.hide();
					} else {
						$( '.an_delay' )
							.show( 500 );
						$( '.an_itr' )
							.show( 500 );
					}
				} );
		}

		// Deskop, Mobile - same settings
		function desktopMobile () {
			// same setting
			if ( $( '.same_settings' )
				.is( ':checked' ) ) {
				$( '.not_samesettings' )
					.hide();
			} else {
				$( '.not_samesettings' )
					.show();
			}

			$( '.same_settings' )
				.on( 'change', function handleSameSettingsChange ( event ) {
					if ( $( '.same_settings' )
						.is( ':checked' ) ) {
						$( '.not_samesettings' )
							.hide( 900 );
						$( '.select_styles_issue_checkbox' )
							.hide();
					} else {
						$( '.not_samesettings' )
							.show( 900 );
					}
				} );
		}

		function notificationBadge () {
			var $notificationBadge = $( '#notification_badge' );
			var $notificationSettings = $( '.notification_settings ' );

			// same setting
			if ( $notificationBadge.is( ':checked' ) ) {
				$notificationSettings.show();
			} else {
				$notificationSettings.hide();
			}

			$notificationBadge.on( 'change', function handleNotificationBadgeChange ( event ) {
				if ( $notificationBadge.is( ':checked' ) ) {
					$notificationSettings.show( 400 );
				} else {
					$notificationSettings.hide( 400 );
				}
			} );
		}

		// WhatsApp number
		function wn () {
			var cc = $( '#whatsapp_cc' )
				.val();
			var num = $( '#whatsapp_number' )
				.val();

			$( '#whatsapp_cc' )
				.on( 'change paste keyup', function handleWhatsappCcInput ( event ) {
					cc = $( '#whatsapp_cc' )
						.val();
					call();
				} );

			$( '#whatsapp_number' )
				.on( 'change paste keyup', function handleWhatsappNumberInput ( event ) {
					num = $( '#whatsapp_number' )
						.val();
					call();

					if ( num && num.charAt( 0 ) === '0' ) {
						$( '.ctc_wn_initial_zero' )
							.show( 500 );
					} else {
						$( '.ctc_wn_initial_zero' )
							.hide( 500 );
					}
				} );

			function call () {
				$( '.ht_ctc_wn' )
					.text( cc + '' + num );
				$( '#ctc_whatsapp_number' )
					.val( cc + '' + num );
			}
		}

		// woo page..
		function wooPage () {
			//  Woo single product page - woo position
			var positionValue = $( '.woo_single_position_select' )
				.find( ':selected' )
				.val();

			// woo add to cart layout
			var styleValue = $( '.woo_single_style_select' )
				.find( ':selected' )
				.val();

			if ( positionValue && '' !== positionValue && 'select' !== positionValue ) {
				$( '.woo_single_position_settings' )
					.show();
			}
			if ( positionValue && 'select' === positionValue ) {
				hideCartLayout();
			} else if ( ( styleValue && styleValue === '1' ) || styleValue === '8' ) {
				// if positionValue is not 'select'
				showCartLayout();
			}

			// on change - select position
			$( '.woo_single_position_select' )
				.on( 'change', function handleWooSinglePositionChange ( event ) {
					var positionChangeVal = event.target.value;
					var styleValue = $( '.woo_single_style_select' )
						.find( ':selected' )
						.val();

					if ( positionChangeVal === 'select' ) {
						$( '.woo_single_position_settings' )
							.hide( 200 );
						hideCartLayout();
					} else {
						$( '.woo_single_position_settings' )
							.show( 200 );
						if ( styleValue === '1' || styleValue === '8' ) {
							showCartLayout();
						}
					}
				} );

			// on change - style - for cart layout
			$( '.woo_single_style_select' )
				.on( 'change', function handleWooSingleStyleChange ( event ) {
					var styleChangeVal = event.target.value;

					if ( styleChangeVal === '1' || styleChangeVal === '8' ) {
						showCartLayout();
					} else {
						hideCartLayout();
					}
				} );

			// position center is checked
			if ( $( '#woo_single_position_center' )
				.is( ':checked' ) ) {
				$( '.woo_single_position_center_checked_content' )
					.show();
			}

			$( '#woo_single_position_center' )
				.on( 'change', function handleWooPositionCenterChange ( event ) {
					if ( $( '#woo_single_position_center' )
						.is( ':checked' ) ) {
						$( '.woo_single_position_center_checked_content' )
							.show( 200 );
					} else {
						$( '.woo_single_position_center_checked_content' )
							.hide( 100 );
					}
				} );

			// woo shop page ..
			if ( $( '#woo_shop_add_whatsapp' )
				.is( ':checked' ) ) {
				$( '.woo_shop_add_whatsapp_settings' )
					.show();

				var shopStyleValue = $( '.woo_shop_style' )
					.find( ':selected' )
					.val();

				// cart layout button is visible, when style is 1 or 8
				if ( shopStyleValue === '1' || shopStyleValue === '8' ) {
					shopShowCartLayout();
				}
			}

			$( '#woo_shop_add_whatsapp' )
				.on( 'change', function handleWooShopToggle ( event ) {
					if ( $( '#woo_shop_add_whatsapp' )
						.is( ':checked' ) ) {
						$( '.woo_shop_add_whatsapp_settings' )
							.show( 200 );

						var shopStyleValue = $( '.woo_shop_style' )
							.find( ':selected' )
							.val();

						if ( shopStyleValue === '1' || shopStyleValue === '8' ) {
							shopShowCartLayout();
						}
					} else {
						$( '.woo_shop_add_whatsapp_settings' )
							.hide( 100 );
						shopHideCartLayout( 100 );
					}
				} );

			// on change - style - for cart layout
			$( '.woo_shop_style' )
				.on( 'change', function handleWooShopStyleChange ( event ) {
					var shopStyleChangeVal = event.target.value;

					if ( shopStyleChangeVal === '1' || shopStyleChangeVal === '8' ) {
						shopShowCartLayout();
					} else {
						shopHideCartLayout();
					}
				} );

			function showCartLayout () {
				$( '.woo_single_position_settings_cart_layout' )
					.show( 200 );
			}
			function hideCartLayout () {
				$( '.woo_single_position_settings_cart_layout' )
					.hide( 200 );
			}

			function shopShowCartLayout () {
				$( '.woo_shop_cart_layout' )
					.show( 200 );
			}
			function shopHideCartLayout () {
				$( '.woo_shop_cart_layout' )
					.hide( 200 );
			}
		}

		// webhook
		function hook () {
			// webhook value - html
			var hookValueHtml = $( '.add_hook_value' )
				.attr( 'data-html' );

			// add value
			$( document )
				.on( 'click', '.add_hook_value', function handleAddHookValueClick () {
					$( '.ctc_hook_value' )
						.append( hookValueHtml );
				} );

			// Remove value
			$( '.ctc_hook_value' )
				.on( 'click', '.hook_remove_value', function handleHookValueRemove ( event ) {
					event.preventDefault();
					$( this )
						.closest( '.additional-value' )
						.remove();
				} );
		}

		// things based on screen size
		function ss () {
			var is_mobile =
				typeof screen.width !== 'undefined' && screen.width > 1024 ? 'no' : 'yes';

			if ( 'yes' === is_mobile ) {
				// WhatsApp number tooltip position for mobile
				// $("#whatsapp_cc").data('position', 'bottom');
				$( '#whatsapp_cc' )
					.attr( 'data-position', 'bottom' );
				$( '#whatsapp_number' )
					.attr( 'data-position', 'bottom' );
			}
		}

		function other () {
			// google ads - checkbox
			$( '.ga_ads_display' )
				.on( 'click', function toggleGaAdsCheckbox ( event ) {
					$( '.ga_ads_checkbox' )
						.toggle( 500 );
				} );

			// // display - call gtag_report_conversion by default if checked.
			// if ($('#ga_ads').is(':checked')) {
			//     $(".ga_ads_checkbox").show();
			// }

			// hover text on save_changes button
			var text = $( '#ctc_save_changes_hover_text' )
				.text();
			$( '#submit' )
				.attr( 'title', text );

			// s3e - shadow on hover
			var $s3BoxShadow = $( '#s3_box_shadow' );
			var $s3BoxShadowHover = $( '.s3_box_shadow_hover' );

			if ( ! $s3BoxShadow.is( ':checked' ) ) {
				$s3BoxShadowHover.show();
			}

			$s3BoxShadow.on( 'change', function handleS3BoxShadowChange ( event ) {
				if ( $s3BoxShadow.is( ':checked' ) ) {
					$s3BoxShadowHover.hide( 400 );
				} else {
					$s3BoxShadowHover.show( 500 );
				}
			} );
		}

		// collapsible..
		function collapsible () {
			/**
			 * ht_ctc_sidebar_contat, .. - not added, as it may cause view distraction..
			 */
			var collapsible_list = [
				'ht_ctc_s1',
				'ht_ctc_s2',
				'ht_ctc_s3',
				'ht_ctc_s3_1',
				'ht_ctc_s4',
				'ht_ctc_s5',
				'ht_ctc_s6',
				'ht_ctc_s7',
				'ht_ctc_s7_1',
				'ht_ctc_s8',
				'ht_ctc_s99',
				'ht_ctc_webhooks',

				// 'ht_ctc_analytics',
				'ht_ctc_animations',
				'ht_ctc_notification',
				'ht_ctc_other_settings',
				'ht_ctc_enable_share_group',
				'ht_ctc_debug',
				'ht_ctc_device_settings',
				'ht_ctc_show_hide_settings',
				'ht_ctc_woo_1',
				'ht_ctc_woo_shop',
				'ctc_g_opt_in',
				'g_content_collapsible',
				'url_structure',
				'ht_ctc_custom_css',
			];

			var $collActive = $( '.coll_active' );
			if ( $collActive.length ) {
				$collActive
					.each( function recordActiveCollapsible () {
						collapsible_list.push( $( this )
							.attr( 'data-coll_active' ) );
					} );
			}

			var default_active = [
				'ht_ctc_device_settings',
				'ht_ctc_show_hide_settings',
				'ht_ctc_woo_1',
				'ht_ctc_webhooks',

				// 'ht_ctc_analytics',
				'ht_ctc_animations',
				'ht_ctc_notification',
				'g_content_collapsible',
				'url_structure',
			];

			collapsible_list.forEach( ( collapsibleId ) => {
				// one known issue.. is already active its not working as expected.
				var storedCollapseState = ctc_getItem( 'col_' + collapsibleId );
				var is_col = storedCollapseState ? storedCollapseState : '';
				if ( 'open' === is_col ) {
					$( '.' + collapsibleId + ' li' )
						.addClass( 'active' );
				} else if ( 'close' === is_col ) {
					$( '.' + collapsibleId + ' li' )
						.removeClass( 'active' );
				} else if ( default_active.includes( collapsibleId ) ) {
					// if not changed then for default_active list add active..
					$( '.' + collapsibleId + ' li' )
						.addClass( 'active' );
				}

				$( '.' + collapsibleId )
					.collapsible( {
						onOpenEnd () {
							console.log( collapsibleId + ' open' );
							ctc_setItem( 'col_' + collapsibleId, 'open' );
						},
						onCloseEnd () {
							console.log( collapsibleId + ' close' );
							ctc_setItem( 'col_' + collapsibleId, 'close' );
						},
					} );
			} );
		}

		/**
		 * intl tel input
		 * intlTelInput - from intl js..
		 *
		 * class name - intl_number, multi agent class names
		 */
		function intl_input ( className ) {
			console.log( 'intl_input() className: ' + className );

			var $inputs = $( '.' + className );
			if ( $inputs.length ) {
				console.log( className + ' class name exists' );

				if ( typeof intlTelInput !== 'undefined' ) {

					$inputs.each( function initializeIntlInputInstance () {
						console.log( 'each: calling intl_init()..' + this );
						intl_init( this );
					} );

					console.log( 'calling intl_onchange() from intl_input()' );
					intl_onchange();
				} else {
					// throw error..
					console.log( 'intlTelInput not loaded..' );
					throw new Error( 'intlTelInput not loaded..' );
				}

				// // all intl inputs
				// console.log('all_intl_instances');
				// console.log(all_intl_instances);
			}
		}

		// intl: - init
		function intl_init ( phoneInputElement ) {
			console.log( 'intl_init()' );
			console.log( phoneInputElement );

			var attr_value = $( phoneInputElement )
				.attr( 'value' );
			console.log( 'attr_value: ' + attr_value );

			var hidden_input = $( phoneInputElement )
				.attr( 'data-name' ) ?
				$( phoneInputElement )
					.attr( 'data-name' ) :
				'ht_ctc_chat_options[number]';
			console.log( hidden_input );

			$( phoneInputElement )
				.removeAttr( 'name' );
			var pre_countries = [];
			var country_code_date = new Date()
				.toDateString();
			var country_code =
				ctc_getItem( 'country_code_date' ) === country_code_date ?
					ctc_getItem( 'country_code' ) :
					'';
			console.log( 'country_code: ' + country_code );

			if ( '' === country_code ) {
				console.log( 'getting country code..' );

				// fall back..
				country_code = 'us';

				$.ajax( {
					url: 'https://ipinfo.io',
					dataType: 'jsonp',
				} )
					.always( function handleGeoLookupResponse ( resp ) {
						country_code = resp && resp.country ? resp.country : 'us';
						ctc_setItem( 'country_code', country_code );
						ctc_setItem( 'country_code_date', country_code_date );
						add_prefer_countrys( country_code );
						call_intl();
					} );
			} else {
				call_intl();
			}

			var intl = '';
			function call_intl () {
				var storedPreCountries = ctc_getItem( 'pre_countries' );
				pre_countries = storedPreCountries ? storedPreCountries : [];
				console.log( pre_countries );

				var values = {
					autoHideDialCode: false,
					initialCountry: 'auto',
					geoIpLookup: function geoIpLookupCallback ( success, failure ) {
						success( country_code );
					},
					dropdownContainer: document.body,
					hiddenInput: function buildHiddenInputFields () {
						return {
							phone: hidden_input,
							country: 'ht_ctc_chat_options[intl_country]',
						};
					},
					nationalMode: false,

					// autoPlaceholder: "polite",
					countryOrder: pre_countries,
					separateDialCode: true,
					containerClass: 'intl_tel_input_container',

					// countrySearch: false,

					utilsScript: ht_ctc_admin_var.utils,
				};

				intl = intlTelInput( phoneInputElement, values );

				// all_intl_instances.push(intl);

				// Fix: Input display issue – auto-parsing fails for certain numbers
				// (value is saved and retrieved correctly from DB)
				if ( attr_value && attr_value.length > 8 ) {
					console.log( 'set number: ' + attr_value );
					intl.setNumber( attr_value );
				}
			}

			return intl;
		}

		// intl: on change
		function intl_onchange () {
			console.log( 'intl_onchange()' );

			$( '.intl_number' )
				.on( 'input countrychange', function handleIntlInputChange ( event ) {
					// if blank also it may triggers.. as if countrycode changes.
					console.log( 'on change - intl_number - input, countrychange' );
					console.log( this );
					console.log( intlTelInput );

					// var changed = intlTelInputGlobals.getInstance(this);
					// var changed = window.intlTelInput.getInstance(this);
					// var changed = intlTelInput(this);
					var changed = intlTelInput.getInstance( this );

					console.log( changed );
					console.log( changed.getNumber() );

					// add value to next sibbling hidden input field.
					$( this )
						.next( 'input[type="hidden"]' )
						.val( changed.getNumber() );

					if ( window.ht_ctc_admin_demo_var ) {
						console.log( 'for demo: update number' );
						window.ht_ctc_admin_demo_var.number = changed.getNumber();
						console.log( window.ht_ctc_admin_demo_var );
					}

					if ( changed.isValidNumber() ) {
						// to display in format
						console.log( 'valid number: ' + changed.getNumber() );

						// issue here.. setNumber ~ uses for for formating..
						// console.log(changed.getNumber());

						var numberDetails = {
							number: changed.getNumber(),
						};

						// @used at admin demo
						document.dispatchEvent( new CustomEvent(
							'ht_ctc_admin_event_valid_number',
							{ detail: { data: numberDetails } },
						) );
					} else {
						console.log( 'invalid number: ' + changed.getNumber() );
					}
				} );

			// intl: only countrycode changes.
			$( '.intl_number' )
				.on( 'countrychange', function handleIntlCountryChange ( event ) {
					console.log( 'on change - intl_number - countrychange' );

					// var changed = intlTelInputGlobals.getInstance(this);
					// var changed = window.intlTelInput.getInstance(this);
					// var changed = window.intlTelInput(this);
					var changed = intlTelInput.getInstance( this );

					console.log( changed );

					console.log( changed.getSelectedCountryData().iso2 );
					console.log( 'calling add_prefer_countrys()' );
					add_prefer_countrys( changed.getSelectedCountryData().iso2 );
				} );
		}

		function add_prefer_countrys ( country_code ) {
			console.log( 'add_prefer_countrys(): ' + country_code );

			country_code = country_code && '' !== country_code ?
				country_code.toUpperCase() :
				'US';

			var storedPreCountries = ctc_getItem( 'pre_countries' );
			var pre_countries = storedPreCountries ? storedPreCountries : [];
			console.log( pre_countries );

			if ( ! pre_countries.includes( country_code ) ) {
				console.log( country_code +
					' not included. so pushing country code to pre countries' );

				// push to index 0..
				pre_countries.unshift( country_code );

				// pre_countries.push(country_code);

				ctc_setItem( 'pre_countries', pre_countries );
			}
			console.log( '#END add_prefer_countrys()' );
		}

		/**
		 * on save changes clear stuff - local storage: front.
		 *  for better user interface - while testing, admin side..
		 *      for notification badge
		 * as now for colors not added on change..
		 */
		function updateFrontendStorage () {
			$( '.notification_field' )
				.on( 'change', function handleNotificationFieldChange ( event ) {
					console.log( 'notifications updated..' );
					ctc_front_setItem( 'n_badge', 'admin_start' );
				} );
		}

		/**
		 * Analytics..
		 */
		function analytics () {
			console.log( 'analytics()' );

			// google analytics

			// if #google_analytics is checked then display .ctc_ga_values
			if ( $( '#google_analytics' )
				.is( ':checked' ) ) {
				$( '.ctc_ga_values' )
					.show();
			}

			// event name, params - display only if ga is enabled.
			$( '#google_analytics' )
				.on( 'change', function handleGoogleAnalyticsToggle ( event ) {
					if ( $( '#google_analytics' )
						.is( ':checked' ) ) {
						$( '.ctc_ga_values' )
							.show( 400 );
					} else {
						$( '.ctc_ga_values' )
							.hide( 200 );
					}
				} );

			var gAnParamSnippet = $( '.ctc_g_an_param_snippets .ht_ctc_g_an_add_param' );
			console.log( gAnParamSnippet );

			// add value
			$( document )
				.on( 'click', '.ctc_add_g_an_param_button', function handleAddGaParamClick () {
					console.log( 'on click: add g an param button' );
					console.log( gAnParamSnippet );

					var gAnParamOrder = $( '.g_an_param_order' )
						.val();
					gAnParamOrder = parseInt( gAnParamOrder, 10 );

					var gAnParamClone = gAnParamSnippet.clone();
					console.log( gAnParamClone );

					// filed number for reference
					$( gAnParamClone )
						.find( '.g_an_param_order_ref_number' )
						.attr( 'name', 'ht_ctc_othersettings[g_an_params][]' );
					$( gAnParamClone )
						.find( '.g_an_param_order_ref_number' )
						.val( 'g_an_param_' + gAnParamOrder );

					var analyticsParamKey =
						'ht_ctc_othersettings[g_an_param_' +
						gAnParamOrder +
						'][key]';
					var analyticsParamValue =
						'ht_ctc_othersettings[g_an_param_' +
						gAnParamOrder +
						'][value]';
					$( gAnParamClone )
						.find( '.ht_ctc_g_an_add_param_key' )
						.attr( 'name', analyticsParamKey );
					$( gAnParamClone )
						.find( '.ht_ctc_g_an_add_param_value' )
						.attr( 'name', analyticsParamValue );

					console.log( $( '.ctc_new_g_an_param' ) );

					$( '.ctc_new_g_an_param' )
						.append( gAnParamClone );

					gAnParamOrder++;
					$( '.g_an_param_order' )
						.val( gAnParamOrder );
				} );

			// Google Tag Manager
			// if #google_tag_manager is checked then display .ctc_gtm_values
			if ( $( '#google_tag_manager' )
				.is( ':checked' ) ) {
				$( '.ctc_gtm_values' )
					.show();
			}

			// event name, params - display only if gtm is enabled.
			$( '#google_tag_manager' )
				.on( 'change', function handleGoogleTagManagerToggle ( event ) {
					if ( $( '#google_tag_manager' )
						.is( ':checked' ) ) {
						$( '.ctc_gtm_values' )
							.show( 400 );
					} else {
						$( '.ctc_gtm_values' )
							.hide( 200 );
					}
				} );

			var gtmParamSnippet = $( '.ctc_gtm_param_snippets .ht_ctc_gtm_add_param' );
			console.log( gtmParamSnippet );

			// add value
			$( document )
				.on( 'click', '.ctc_add_gtm_param_button', function handleAddGtmParamClick () {
					console.log( 'on click: add gtm param button' );
					console.log( gtmParamSnippet );

					var gtmParamOrder = $( '.gtm_param_order' )
						.val();
					gtmParamOrder = parseInt( gtmParamOrder, 10 );

					var gtmParamClone = gtmParamSnippet.clone();
					console.log( gtmParamClone );
					console.log( 'gtmParamOrder', gtmParamOrder );

					// filed number for reference
					$( gtmParamClone )
						.find( '.gtm_param_order_ref_number' )
						.attr( 'name', 'ht_ctc_othersettings[gtm_params][]' );
					$( gtmParamClone )
						.find( '.gtm_param_order_ref_number' )
						.val( 'gtm_param_' + gtmParamOrder );

					var gtmParamKey =
						'ht_ctc_othersettings[gtm_param_' +
						gtmParamOrder +
						'][key]';
					var gtmParamValue =
						'ht_ctc_othersettings[gtm_param_' +
						gtmParamOrder +
						'][value]';
					$( gtmParamClone )
						.find( '.ht_ctc_gtm_add_param_key' )
						.attr( 'name', gtmParamKey );
					$( gtmParamClone )
						.find( '.ht_ctc_gtm_add_param_value' )
						.attr( 'name', gtmParamValue );

					console.log( $( '.ctc_new_gtm_param' ) );

					$( '.ctc_new_gtm_param' )
						.append( gtmParamClone );

					gtmParamOrder++;
					$( '.gtm_param_order' )
						.val( gtmParamOrder );
				} );

			// fb pixel

			// if #fb_pixel is checked then display .ctc_pixel_values
			if ( $( '#fb_pixel' )
				.is( ':checked' ) ) {
				$( '.ctc_pixel_values' )
					.show();
			}

			// event name, params - display only if fb pixel is enabled.
			$( '#fb_pixel' )
				.on( 'change', function handleFacebookPixelToggle ( event ) {
					if ( $( '#fb_pixel' )
						.is( ':checked' ) ) {
						$( '.ctc_pixel_values' )
							.show( 400 );
					} else {
						$( '.ctc_pixel_values' )
							.hide( 200 );
					}
				} );

			// if pixel_event_type is 'custom' then display .ctc_pixel_custom_event_name
			var pixelEventType = $( '.pixel_event_type' )
				.find( ':selected' )
				.val();
			if ( pixelEventType === 'trackCustom' ) {
				$( '.pixel_custom_event' )
					.show( 100 );
			} else if ( pixelEventType === 'track' ) {
				$( '.pixel_standard_event' )
					.show( 100 );
			}

			// on change - pixel_event_type
			$( '.pixel_event_type' )
				.on( 'change', function handlePixelEventTypeChange ( event ) {
					var pixelEventTypeChangeVal = event.target.value;
					console.log( pixelEventTypeChangeVal );
					if ( pixelEventTypeChangeVal === 'trackCustom' ) {
						$( '.pixel_custom_event' )
							.show( 200 );
						$( '.pixel_standard_event' )
							.hide( 100 );
					} else if ( pixelEventTypeChangeVal === 'track' ) {
						$( '.pixel_standard_event' )
							.show( 200 );
						$( '.pixel_custom_event' )
							.hide( 100 );
					}
				} );

			var pixelParamSnippet = $( '.ctc_pixel_param_snippets .ht_ctc_pixel_add_param' );
			console.log( pixelParamSnippet );

			// add value
			$( document )
				.on( 'click', '.ctc_add_pixel_param_button', function handleAddPixelParamClick () {
					console.log( 'on click: add g an param button' );
					console.log( pixelParamSnippet );

					var pixelParamOrder = $( '.pixel_param_order' )
						.val();
					pixelParamOrder = parseInt( pixelParamOrder, 10 );

					var pixelParamClone = pixelParamSnippet.clone();
					console.log( pixelParamClone );

					// filed number for reference
					$( pixelParamClone )
						.find( '.pixel_param_order_ref_number' )
						.attr( 'name', 'ht_ctc_othersettings[pixel_params][]' );
					$( pixelParamClone )
						.find( '.pixel_param_order_ref_number' )
						.val( 'pixel_param_' + pixelParamOrder );

					var pixelParamKey =
						'ht_ctc_othersettings[pixel_param_' +
						pixelParamOrder +
						'][key]';
					var pixelParamValue =
						'ht_ctc_othersettings[pixel_param_' +
						pixelParamOrder +
						'][value]';
					$( pixelParamClone )
						.find( '.ht_ctc_pixel_add_param_key' )
						.attr( 'name', pixelParamKey );
					$( pixelParamClone )
						.find( '.ht_ctc_pixel_add_param_value' )
						.attr( 'name', pixelParamValue );

					console.log( $( '.ctc_new_pixel_param' ) );

					$( '.ctc_new_pixel_param' )
						.append( pixelParamClone );

					pixelParamOrder++;
					$( '.pixel_param_order' )
						.val( pixelParamOrder );
				} );

			// Remove params
			$( '.ctc_an_params' )
				.on( 'click', '.an_param_remove', function handleAnalyticsParamRemove ( event ) {
					event.preventDefault();
					console.log( 'on click: an_param_remove' );
					$( this )
						.closest( '.ctc_an_param' )
						.remove();
				} );

			// analytics count
			$( '.analytics_count_message' )
				.on( 'click', function toggleAnalyticsCountMessage ( event ) {
					// $(".analytics_count_message span").hide();
					$( '.analytics_count_select' )
						.toggle( 200 );
				} );

			// on change - analytics count value
			$( '.select_analytics' )
				.on( 'change', function handleAnalyticsCountChange ( event ) {
					var changeVal = event.target.value;

					// $(".analytics_count_message span").show();
					// $('.analytics_count_select').hide(200);
					$( '.analytics_count_message span' )
						.text( changeVal );
				} );
		}
	} );
} )( jQuery );
