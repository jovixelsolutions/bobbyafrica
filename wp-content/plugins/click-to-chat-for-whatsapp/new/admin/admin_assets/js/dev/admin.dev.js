/* global M */
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

			// Clear the 2026 new admin interface cache when switching back/loading the 2019 interface.
			ctc_admin_2026_utils();
		} catch ( error ) {
			console.log( error );
			console.log( 'cache: wooPage(), collapsible(), updateFrontendStorage(), ctc_admin_2026_utils()' );
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

			if ( ! $inputs.length ) {
				return;
			}

			console.log( className + ' class name exists' );

			intlLibrary()
				.then( function handleIntlLibrary ( lib ) {
					if ( ! lib ) {
						throw new Error( 'intlTelInput not loaded..' );
					}

					$inputs.each( function initializeIntlInputInstance () {
						intl_init( this );
					} );

					intl_onchange();
				} );
		}

		/**
		 * Load the vendored intl-tel-input, once per page.
		 *
		 * v29 ships as an ES module — there is no `intlTelInput` global to wait
		 * for any more, so it is import()ed from the URL PHP localizes. Callers
		 * (including PRO, via the ht_ctc_fn_all event) get a promise; intl_init()
		 * awaits it internally so their call sites do not have to change.
		 */
		var intlLib = null;
		var intlLibPromise = null;

		function intlLibrary () {
			if ( intlLibPromise ) {
				return intlLibPromise;
			}

			var url = ht_ctc_admin_var.intl;

			if ( ! url ) {
				intlLibPromise = Promise.resolve( null );
				return intlLibPromise;
			}

			intlLibPromise =
				// eslint-disable-next-line no-unsanitized/method -- URL comes from HT_CTC_Phone_Field::assets(), localized by PHP.
				import( /* webpackIgnore: true */ url )
					.then( function handleIntlModule ( module ) {
						intlLib = module.default || null;

						/*
						 * Expose on window for admin-side compatibility.
						 * Kept for backward compatibility with scripts expecting window.intlTelInput.
						 * Can be removed later.
						 */
						if ( intlLib && 'undefined' === typeof window.intlTelInput ) {
							window.intlTelInput = intlLib;
						}

						return intlLib;
					} )
					.catch( function handleIntlModuleError ( error ) {
						console.log( 'failed to load intl-tel-input', error );
						intlLibPromise = null;
						return null;
					} );

			return intlLibPromise;
		}

		/**
		 * Rebuild the library's `uiTranslations` from the strings PHP inlined.

			 * PHP sends the chosen language's strings — no request, no import.
			 *
			 * `searchSummaryAria` arrives as a plural TEMPLATE map rather than a string.
			 * Upstream ships a separate hardcoded function for each of the 48 languages;
			 * this is the one shared implementation of that logic, selecting the template
			 * with Intl.PluralRules (which the browser already has) and substituting %d.
			 * `exact` wins for the counts upstream words specially — Lithuanian says
			 * "Rastas 1" but "Rasti 21", and both are CLDR `one`.
			 *
			 * Empty means English, where the library's own defaults already apply.
		 *
		 * @param {Object} source Strings from PHP.
		 * @param {string} locale Language tag driving plural selection.
		 * @returns {Object} uiTranslations for the library.
		 */
		function buildUiTranslations ( source, locale ) {
			if ( ! source || 'object' !== typeof source ) {
				return {};
			}

			var ui = {};

			Object.keys( source )
				.forEach( function copyKey ( key ) {
					// eslint-disable-next-line security/detect-object-injection -- keys come from our own generated data.
					ui[ key ] = source[ key ];
				} );
			var aria = ui.searchSummaryAria;

			if ( ! aria || 'object' !== typeof aria ) {
				return ui;
			}

			var exact = aria.exact || {};
			var plural = aria.plural || {};
			var rules = null;

			try {
				rules = new Intl.PluralRules( locale );
			} catch {
				rules = null;
			}

			ui.searchSummaryAria = function searchSummaryAria ( count ) {
				/* eslint-disable security/detect-object-injection -- `count` is a number from the library; keys are our own generated data. */
				var template = ( count <= 1 && undefined !== exact[ count ] ) ?
					exact[ count ] :
					plural[ rules ? rules.select( count ) : 'other' ] || plural.other;
				/* eslint-enable security/detect-object-injection */

				return undefined === template ?
					String( count ) :
					template.split( '%d' )
						.join( String( count ) );
			};

			return ui;
		}

		/**
		 * intl: - init
		 *
		 * Awaits the library, so PRO can keep calling this synchronously for
		 * number fields it adds after page load.
		 */
		function intl_init ( phoneInputElement ) {
			console.log( 'intl_init()' );

			return intlLibrary()
				.then( function handleIntlReady ( intlTelInputFn ) {
					var uiTranslations = buildUiTranslations(
						ht_ctc_admin_var.intl_ui,

						// Already a valid BCP-47 tag from HT_CTC_Phone_Field::locale().
						// Do NOT reshape it here.
						ht_ctc_admin_var.intl_lang || 'en',
					);

					if ( ! intlTelInputFn || phoneInputElement.dataset.ctcIntlBound === 'true' ) {
						return null;
					}

					phoneInputElement.dataset.ctcIntlBound = 'true';

					return intlConstruct( phoneInputElement, intlTelInputFn, uiTranslations );
				} );
		}

		/**
		 * Build one instance and the hidden input that actually saves.
		 *
		 * The visible field's `name` is removed and a hidden input carrying it is
		 * created here. v24 had the library do that via `hiddenInput`; v29's
		 * `hiddenInputs` only writes on a native form submit, and the value is
		 * also read before that (the demo preview, the valid-number event), so we
		 * own it and keep it in step on every change.
		 *
		 * `ht_ctc_chat_options[intl_country]` is deliberately not recreated — v24
		 * emitted it, but no PHP has ever read it.
		 */
		function intlConstruct ( phoneInputElement, intlTelInputFn, uiTranslations ) {
			var $el = $( phoneInputElement );
			var attr_value = $el.attr( 'value' ) ?
				$el.attr( 'value' ) :
				'';

			/*
			 * Normalise to a '+' prefix, then take the value OUT of the field:
			 * it is constructed empty and seeded via setNumber() below. That is
			 * load-bearing — see the note at the setNumber() call.
			 */
			if ( attr_value ) {
				attr_value = '+' !== attr_value.charAt( 0 ) ? '+' + attr_value : attr_value;
				phoneInputElement.value = '';
				phoneInputElement.removeAttribute( 'value' );
			}

			var hidden_input_name = $el.attr( 'data-name' ) ?
				$el.attr( 'data-name' ) :
				'ht_ctc_chat_options[number]';

			/*
			 * The visible input KEEPS its `name` until the hidden input that
			 * replaces it exists (below, after the constructor). It used to be
			 * dropped here, which left a window — the constructor — where NOTHING
			 * carried this setting into the POST.
			 *
			 * That window matters more in this tree than in admin2: options_sanitize()
			 * rebuilds the option from `$input` alone, so a key missing from the POST
			 * is not "unchanged", it is DELETED. A library throw during init wiped
			 * the saved number outright.
			 */

			var stored_pre_countries = ctc_getItem( 'pre_countries' );

			var values = {
				// v29: replaces initialCountry:'auto' + geoIpLookup.
				initialCountry: '',
				initialCountryLookup: intlCountryLookup,

				// v29: was dropdownContainer.
				dropdownParent: document.body,

				// v29: was nationalMode:false.
				numberDisplayFormat: 'INTERNATIONAL',
				separateDialCode: true,

				// Don't block keystrokes or cap length — accept what is typed.
				strictMode: false,

				countryOrder: stored_pre_countries ? stored_pre_countries : [],

				// ctc_intl_container is REQUIRED: the vendored stylesheet is
				// scoped to it, so without it the field renders unstyled.
				containerClass: 'ctc_intl_container intl_tel_input_container',

				// We are adding this so to avoid the conflict from material css..
				searchInputClass: 'browser-default',

				// We manage the hidden input below.
				hiddenInputs: null,

				// v29: was utilsScript (an eager URL).
				loadUtils: ht_ctc_admin_var.utils ?
					function loadIntlUtils () {
						// eslint-disable-next-line no-unsanitized/method -- URL comes from HT_CTC_Phone_Field::assets(), localized by PHP.
						return import( /* webpackIgnore: true */ ht_ctc_admin_var.utils );
					} :
					null,

				// Country names from the browser, in the admin's language.
				// Resolved server-side; passed through untouched.
				countryNameLocale: ht_ctc_admin_var.intl_lang || 'en',
			};

			if ( uiTranslations ) {
				values.uiTranslations = uiTranslations;
			}

			var intl = intlTelInputFn( phoneInputElement, values );

			// v29 removed intlTelInput.getInstance(); the change handlers read
			// this instead.
			phoneInputElement._ctcIti = intl;

			intlKeepDropdownInSync( phoneInputElement );

			var hidden = document.createElement( 'input' );

			hidden.type = 'hidden';
			hidden.className = 'ctc_intl_number_hidden';
			hidden.setAttribute( 'name', hidden_input_name );

			/*
			 * Seeded with the value already stored, NOT with getNumber().
			 *
			 * This input is what the form posts, so it must never be empty while a
			 * number is saved. getNumber() throws until the lazy utils module
			 * resolves, so calling it here would leave '' behind and wipe the
			 * setting on the next save. The real value is written below once the
			 * instance reports ready, and on every change after that.
			 */
			hidden.value = attr_value ? attr_value : '';
			phoneInputElement.parentNode.insertBefore( hidden, phoneInputElement.nextSibling );

			// Only now does the visible input stop being the one that posts.
			$el.removeAttr( 'name' );

			/*
			 * Seed the saved number — issue #343. Why the field was constructed
			 * empty and the value is applied HERE:
			 *
			 * "Some numbers" is REGIONLESS NANP — toll-free +1 800 / 833 / 844 /
			 * 855 / 866 / 877 / 888. Their country cannot be derived from the
			 * dial code, so with `initialCountry: ''` + a lookup the library
			 * selects NO country until the geo-IP call returns — and that call
			 * is blocked by most ad blockers.
			 *
			 * The library handles that state inconsistently:
			 *
			 *   - #setInitialState() takes a regionless branch that does NOT
			 *     call #updateCountryFromNumber(), then formats anyway —
			 *     reaching stripSeparateDialCode() with a null country, which
			 *     THROWS out of the intlTelInput() constructor.
			 *   - setNumber() always calls #updateCountryFromNumber() first,
			 *     which resolves these numbers to US, so it is safe.
			 *
			 * So we keep the value away from the constructor and hand it to
			 * setNumber() instead — consumer-side, so there is no patched vendor
			 * file to lose on the next library upgrade. The attribute is
			 * restored first so the library's own recovery pass still sees the
			 * full number.
			 */
			if ( attr_value ) {
				phoneInputElement.setAttribute( 'value', attr_value );
				intl.setNumber( attr_value );
			}

			if ( intl.promise && 'function' === typeof intl.promise.then ) {
				/*
				 * On SETTLE, not on resolve: intl.promise is
				 * Promise.all([autoCountry, utils]) and the auto-country
				 * deferred REJECTS when the geo-IP lookup fails — which it
				 * routinely does, since ipinfo.io is blocked by most ad
				 * blockers. .then() alone would skip the re-apply in exactly
				 * the case that needs it.
				 */
				intl.promise.catch( function handleIntlPromiseError () {
					// Lookup and/or utils failed; still re-seed below.
				} )
					.then( function handleIntlSettledValue () {
						// Re-seed the visible field, unless the user is already editing it.
						if ( attr_value && document.activeElement !== phoneInputElement ) {
							intl.setNumber( attr_value );
						}

						// utils may be ready now: replace the seed / best-effort
						// value with the canonical formatted number.
						var readyNumber = intlNumber( intl, phoneInputElement );

						if ( readyNumber ) {
							hidden.value = readyNumber;
						}
					} )
					.catch( function handleIntlReapplyError () {
						// keep the stored value
					} );
			}

			return intl;
		}

		/**
		 * A saveable number that never throws and never loses what was typed.
		 *
		 * getNumber() needs the lazily-loaded utils module and throws until it
		 * arrives — on a slow network that can be several seconds, during which a
		 * naive sync would leave the hidden field empty and SAVE AN EMPTY NUMBER.
		 *
		 * So: use getNumber() when utils is ready (canonical, formatted), and
		 * otherwise reconstruct a best-effort E.164 from the raw input plus the
		 * selected dial code — getSelectedCountry() needs no utils. Once utils
		 * loads, intl.promise / the change handler overwrite this with the
		 * canonical value, so the fallback only ever stands in for the loading
		 * window and is corrected the moment it can be.
		 *
		 * @param {Object}  intl    The intlTelInput instance.
		 * @param {Element} element The visible input.
		 * @returns {string}
		 */
		function intlNumber ( intl, element ) {
			try {
				var formatted = intl.getNumber();

				if ( formatted ) {
					return formatted;
				}
			} catch {
				// utils not ready — fall through to the raw reconstruction.
			}

			var raw = element && element.value ?
				String( element.value )
					.trim() :
				'';

			if ( '' === raw ) {
				return '';
			}

			// Already a full international number (separateDialCode off, or pasted).
			if ( '+' === raw.charAt( 0 ) ) {
				return raw;
			}

			try {
				var country = intl.getSelectedCountry();

				if ( country && country.dialCode ) {
					return '+' + country.dialCode + raw.replace( /\D/g, '' );
				}
			} catch {
				// no country data — return the raw digits rather than nothing.
			}

			return raw;
		}

		/**
		 * Country for `initialCountryLookup` (v29), cached for the day.
		 */
		function intlCountryLookup () {
			var country_code_date = new Date()
				.toDateString();
			var cached = ctc_getItem( 'country_code_date' ) === country_code_date ?
				ctc_getItem( 'country_code' ) :
				'';

			if ( cached ) {
				return Promise.resolve( String( cached )
					.toLowerCase() );
			}

			return fetch( 'https://ipinfo.io/json', { mode: 'cors', credentials: 'omit' } )
				.then( function handleGeoResponse ( response ) {
					return response.json();
				} )
				.then( function handleGeoJson ( resp ) {
					var code = resp && resp.country && /^[A-Za-z]{2}$/.test( resp.country ) ?
						resp.country :
						'US';

					ctc_setItem( 'country_code', code );
					ctc_setItem( 'country_code_date', country_code_date );
					add_prefer_countrys( code );

					return code.toLowerCase();
				} )
				.catch( function handleGeoError () {
					return 'us';
				} );
		}

		/**
		 * Size the detached dropdown, on every open.
		 *
		 * Both fixes are required by the vendored stylesheet's scoping — the
		 * height one is not cosmetic: the library measures the panel inside a
		 * wrapper that does not carry .ctc_intl_container, so it measures
		 * unstyled and pins roughly 4700px.
		 */
		function intlKeepDropdownInSync ( element ) {
			var wrapper = element.closest( '.iti' );

			if ( ! wrapper ) {
				return;
			}

			element.addEventListener( 'open:countryselector', function handleSelectorOpen () {
				try {
					var button = wrapper.querySelector( '.iti__selected-country' );
					var panelId = button && button.getAttribute( 'aria-controls' );
					var panel = panelId ? document.getElementById( panelId ) : null;

					if ( ! panel || panel.closest( '.iti--fullscreen-popup' ) ) {
						return;
					}

					var width = wrapper.offsetWidth;

					if ( width > 0 ) {
						panel.style.width = width + 'px';
					}

					panel.style.height = '';

					var height = panel.offsetHeight;

					if ( height > 0 ) {
						panel.style.height = height + 'px';
					}
				} catch {
					// a mis-sized dropdown is not worth breaking the page over
				}
			} );
		}

		// intl: on change
		function intl_onchange () {
			console.log( 'intl_onchange()' );

			$( '.intl_number' )
				.on( 'input countrychange', function handleIntlInputChange () {
					// if blank also it may triggers.. as if countrycode changes.
					// v29 removed intlTelInput.getInstance(); intlConstruct()
					// stashes the instance on the element instead.
					var changed = this._ctcIti;

					if ( ! changed ) {
						return;
					}

					// intlNumber() is best-effort: it mirrors the raw input when utils
					// has not loaded, so the hidden field always tracks what the
					// user typed and a save during the loading window keeps it.
					var number = intlNumber( changed, this );

					// the hidden input created in intlConstruct() is what saves.
					$( this )
						.next( 'input[type="hidden"]' )
						.val( number );

					if ( window.ht_ctc_admin_demo_var ) {
						window.ht_ctc_admin_demo_var.number = number;
					}

					// isValidNumber() also needs utils and throws without it.
					var isValid = false;

					try {
						isValid = changed.isValidNumber();
					} catch {
						isValid = false;
					}

					if ( isValid ) {
						var numberDetails = { number: number };

						// @used at admin demo
						document.dispatchEvent( new CustomEvent(
							'ht_ctc_admin_event_valid_number',
							{ detail: { data: numberDetails } },
						) );
					}
				} );

			// intl: only countrycode changes.
			$( '.intl_number' )
				.on( 'countrychange', function handleIntlCountryChange () {
					var changed = this._ctcIti;

					if ( ! changed ) {
						return;
					}

					// v29: was getSelectedCountryData().
					var country = changed.getSelectedCountry();

					if ( country && country.iso2 ) {
						add_prefer_countrys( country.iso2 );
					}
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
		 * FLOW:
		 *   Admin changes badge setting → n_badge = 'admin_start' → reload frontend → badge shows ✓
		 *   User clicks chat            → n_badge = 'stop'        → badge hidden forever ✓
		 *   Admin changes setting again → n_badge = 'admin_start' → overrides stop → badge shows ✓
		 *
		 * on change of notification settings - update local storage: front.
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

			// returns a strictly-increasing, time-based index used as the db key for newly added params.
			var ctcLastParamIndex = 0;
			function ctcUniqueParamIndex () {
				var idx = Date.now();
				if ( idx <= ctcLastParamIndex ) {
					idx = ctcLastParamIndex + 1;
				}
				ctcLastParamIndex = idx;
				return idx;
			}

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
					// time-based index keeps each new row a stable, unique key in the db.
					var gAnParamIndex = ctcUniqueParamIndex();

					var gAnParamClone = gAnParamSnippet.clone();

					$( gAnParamClone )
						.find( '.ht_ctc_g_an_add_param_key' )
						.attr( 'name', 'ht_ctc_othersettings[g_an_params][' + gAnParamIndex + '][key]' );
					$( gAnParamClone )
						.find( '.ht_ctc_g_an_add_param_value' )
						.attr( 'name', 'ht_ctc_othersettings[g_an_params][' + gAnParamIndex + '][value]' );

					$( '.ctc_new_g_an_param' )
						.append( gAnParamClone );
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
					// time-based index keeps each new row a stable, unique key in the db.
					var gtmParamIndex = ctcUniqueParamIndex();

					var gtmParamClone = gtmParamSnippet.clone();

					$( gtmParamClone )
						.find( '.ht_ctc_gtm_add_param_key' )
						.attr( 'name', 'ht_ctc_othersettings[gtm_params][' + gtmParamIndex + '][key]' );
					$( gtmParamClone )
						.find( '.ht_ctc_gtm_add_param_value' )
						.attr( 'name', 'ht_ctc_othersettings[gtm_params][' + gtmParamIndex + '][value]' );

					$( '.ctc_new_gtm_param' )
						.append( gtmParamClone );
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
					// time-based index keeps each new row a stable, unique key in the db.
					var pixelParamIndex = ctcUniqueParamIndex();

					var pixelParamClone = pixelParamSnippet.clone();

					$( pixelParamClone )
						.find( '.ht_ctc_pixel_add_param_key' )
						.attr( 'name', 'ht_ctc_othersettings[pixel_params][' + pixelParamIndex + '][key]' );
					$( pixelParamClone )
						.find( '.ht_ctc_pixel_add_param_value' )
						.attr( 'name', 'ht_ctc_othersettings[pixel_params][' + pixelParamIndex + '][value]' );

					$( '.ctc_new_pixel_param' )
						.append( pixelParamClone );
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

		/**
		 * Utilities and maintenance tasks related to the 2026 React-based admin interface.
		 *
		 * Clears any cached localStorage keys belonging to the 2026 interface when the user
		 * switches back to the classic 2019 PHP interface.
		 * This ensures that when the user switches back to 2026 again, the application
		 * retrieves fresh field configurations and settings from the REST API/server.
		 */
		function ctc_admin_2026_utils () {
			// Clear all localStorage keys starting with 'ht_ctc_fields_' (which store settings fields)
			Object.keys( localStorage )
				.filter( function filterCtcFields ( key ) {
					return key.startsWith( 'ht_ctc_fields_' );
				} )
				.forEach( function removeCtcFields ( key ) {
					localStorage.removeItem( key );
				} );
		}

	} );
} )( jQuery );
