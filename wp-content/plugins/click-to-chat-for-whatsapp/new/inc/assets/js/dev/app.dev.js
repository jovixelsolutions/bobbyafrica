/* global gtag, ga, __gaTracker, dataLayer, gtag_report_conversion, fbq */
// Click to Chat
const htCtcJq = ( typeof window !== 'undefined' && typeof window.jQuery === 'function' ) ?
	window.jQuery :
	null;

( function htCtcAppModule ( window, document, ctcJq ) {

	// (for testing) forces the no-jQuery path. Keep it commented - if you uncomment
	// the line below, add a 'todo(release):' so it cannot ship enabled.
	// ctcJq = false;

	function nojQueryCompatibility () {

		/**
		 * backword compatibility when no jQuery is used.
		 * added display none at inline due to this css animations may not work properly.
		 * If sudden change can cause cache issue so we did like this..
		 */

		// backword compatibility: ht_ctc_chat_greetings_box inline display: none; is added so css animation not works properly.. so add 'ht_ctc_greetings_box' class to hide and remove display none;
		const greetingsBox = document.querySelector( '.ht_ctc_chat_greetings_box' );

		// ht_ctc_greetings
		const greetings = document.querySelector( '.ht_ctc_greetings' );
		if ( greetingsBox && greetings ) {
			greetings.style.setProperty( 'pointer-events', 'none' );
			greetingsBox.classList.add( 'ht_ctc_greetings_box' );
			greetingsBox.style.removeProperty( 'display' );
			greetingsBox.style.setProperty( 'pointer-events', 'auto' );
		}

		// .ht-ctc-chat .ht-ctc-cta-hover
		const ctaHoverEl = document.querySelector( '.ht-ctc-chat .ht-ctc-cta-hover' );
		if ( ctaHoverEl ) {
			ctaHoverEl.classList.add( 'ht-ctc-opacity-hide' );
			ctaHoverEl.style.removeProperty( 'display' );
		}

	}

	// if ctcJq is not function. then backward compatibility mode
	if ( ! ctcJq ) {
		nojQueryCompatibility();
	}

	// ready
	function initClickToChat () {

		// variables

		var url = window.location.href;

		var post_title = typeof document.title !== 'undefined' ? document.title : '';

		const ht_ctc_chat = document.querySelector( '.ht-ctc-chat' );

		let ctc = {}; // For main chat settings - ht_ctc_chat_var
		/* global ht_ctc_chat_var, ht_ctc_variables */
		let ctc_values = {}; // For additional configuration variables - ht_ctc_variables

		/**
		 * Detect if the current device is mobile.
		 * Checks user agent first, then falls back to screen width (<= 1025px).
		 * @returns {'yes'|'no'}
		 */
		function isMobile () {
			let userAgent = '';

			// let screenWidth = Infinity;
			let screenWidth = 9999; // fallback instead of Infinity
			// let maxTouch = 0;

			// try catch for security mostly no issue if used in browser. just in case
			try {
				userAgent = navigator.userAgent || '';
				screenWidth = screen.width || 9999;

				// maxTouch = navigator.maxTouchPoints || 0;
			} catch ( error ) {
				console.error( error );
			}

			const mobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
			const byUserAgent = mobileUA.test( userAgent );

			// Very reliable: real mobile devices always have >1 touch point
			// const byTouch = maxTouch > 1;

			// Handles tablets + iPad desktop mode
			const byScreenWidth = screenWidth <= 1025;

			// If ANY strong signal says mobile → treat as mobile.  (byUserAgent || byTouch || byScreenWidth)
			return ( byUserAgent || byScreenWidth ) ? 'yes' : 'no';
		}

		const is_mobile = isMobile();

		var ht_ctc_storage = new Map();

		const blockedKeys = [ '__proto__', 'prototype', 'constructor', '__defineGetter__', '__defineSetter__', '__lookupGetter__', '__lookupSetter__' ];

		/**
		 * Validates if a key is safe to use for object property access.
		 * Prevents prototype pollution and other common injection attacks.
		 *
		 * @param {string} key The key to validate
		 * @return {boolean} True if the key is safe, false otherwise.
		 */
		const isSafeObjectKey = ( key ) => {
			// Allow only alphanumeric, underscore, hyphen
			if ( typeof key !== 'string' || key.length === 0 || ! /^[a-zA-Z0-9_-]+$/.test( key ) ) {
				return false;
			}

			// Explicitly block prototype pollution keys
			return ! blockedKeys.includes( key );
		};

		/**
		 * Safely retrieves a property from an object using a dynamic key.
		 * suppresses security/detect-object-injection
		 *
		 * @param {Object} obj The object to access
		 * @param {string} key The key to access
		 * @param {*} fallback fallback value if key or object is invalid
		 * @return {*}
		 */
		function getSafeProperty ( obj, key, fallback = false ) {
			if ( ! obj || typeof obj !== 'object' || ! isSafeObjectKey( key ) ) {
				return fallback;
			}
			// eslint-disable-next-line security/detect-object-injection -- Key is validated by isSafeObjectKey above to prevent prototype pollution
			return Object.prototype.hasOwnProperty.call( obj, key ) ? obj[ key ] : fallback;
		}

		/**
		 * Safely sets a property on an object using a dynamic key.
		 * suppresses security/detect-object-injection
		 *
		 * @param {Object} obj The object to modify
		 * @param {string} key The key to set
		 * @param {*} value The value to set
		 * @return {boolean} true if successful, false otherwise
		 */
		function setSafeProperty ( obj, key, value ) {
			if ( obj && typeof obj === 'object' && isSafeObjectKey( key ) ) {
				// eslint-disable-next-line security/detect-object-injection -- Key is validated by isSafeObjectKey above to prevent prototype pollution
				obj[ key ] = value;
				return true;
			}
			return false;
		}

		// Retrieve and parse plugin-related data from localStorage and assign it to ht_ctc_storage.
		function getStorageData () {

			// Check if the 'ht_ctc_storage' key exists in localStorage
			if ( localStorage.getItem( 'ht_ctc_storage' ) ) {
				try {
					const ht_ctc_storage_raw = JSON.parse( localStorage.getItem( 'ht_ctc_storage' ) );
					ht_ctc_storage = new Map( Object.entries( ht_ctc_storage_raw || {} ) );
				} catch {
					ht_ctc_storage = new Map();
				}
			} else {
				ht_ctc_storage = new Map();
			}
		}
		getStorageData(); // Call the function to initialize ht_ctc_storage

		// Retrieve a specific item from the ht_ctc_storage object
		function ctc_getItem ( item ) {

			if ( isSafeObjectKey( item ) && ht_ctc_storage.has( item ) ) {
				return ht_ctc_storage.get( item );
			}
			return false;
		}

		// Store or update a key-value pair in ht_ctc_storage and persist it to localStorage
		function ctc_setItem ( name, value ) {

			// Refresh local copy of storage data from localStorage
			getStorageData();

			// Update or add the item to the ht_ctc_storage object
			if ( ! isSafeObjectKey( name ) ) {
				return;
			}
			ht_ctc_storage.set( name, value );

			// Convert updated storage object to a JSON string
			const newValues = JSON.stringify( Object.fromEntries( ht_ctc_storage ) );

			// Persist the updated data to localStorage
			localStorage.setItem( 'ht_ctc_storage', newValues );
		}

		// document.dispatchEvent(
		//     new CustomEvent(
		//         "ht_ctc_fn_all",
		//         { detail: { ht_ctc_storage, ctc_setItem, ctc_getItem } }
		//     )
		// );

		/* --------------------------------------------------------
		ELEMENT RESOLVER
		Turns selector or element into a list of DOM elements
		--------------------------------------------------------- */
		function resolveEls ( target ) {
			if ( target instanceof Element ) { return [ target ]; }

			if ( typeof target === 'string' ) {
				return Array.from( document.querySelectorAll( target ) );
			}

			return [];
		}

		function playAnimation ( el, classNames ) {

			if ( ! el || ! classNames ) {
				console.warn( 'playAnimation: missing element or classNames' );
				return;
			}

			if ( el.style.display === 'none' ) {
				el.style.display = '';
			}

			const classes = classNames.split( /\s+/ )
				.filter( Boolean );

			classes.forEach( cls => {
				// Remove if exists
				el.classList.remove( cls );

				// Force reflow to restart animation
				void el.offsetWidth;

				// Add again
				el.classList.add( cls );
			} );

			// Auto-remove animation classes after animation finishes
			const handleEnd = () => {
				classes.forEach( cls => el.classList.remove( cls ) );
				el.removeEventListener( 'animationend', handleEnd );
				clearTimeout( safetyTimeout );
			};
			const safetyTimeout = setTimeout( handleEnd, 2000 ); // Fallback cleanup
			el.addEventListener( 'animationend', handleEnd );
		}

		/* --------------------------------------------------------
		MAIN UI API (uses jQuery if available, otherwise using css classes/js)
		--------------------------------------------------------- */
		const ui = {

			/**
			 *
			 * @uses
			 * [ok if no jq] 1.ui.show( '.ht_ctc_chat_greetings_box', 70 ); // Initial Display of Greetings Box (maybe fadein like .. )
			 * [animations load form php] 2.ui.show( chatElement, showEffectTime, 'defaultShow' ); // corner animation
			 * [animations load form php] 3.ui.show( chatElement, '', 'defaultShow' ); //corner animation with no time.
			 * [done if no jq] 4.ui.show( '.for_greetings_header_image_badge' ); // Shows online badge for greetings header image  - natual display works.
			 * [done if no jq] 5.ui.show( '.ht_ctc_notification', 400, 'defaultShow' ); // Shows notification badge
			 * [done if no jq] 6.ui.show( '.ht-ctc-chat .ht-ctc-cta-hover', 120, 'defaultShow' ); // Shows CTA on hover - cta stick
			 *
			 *
			 * 1.uiShow( '.ctc_g_agents' ); // Displays Multi Agents - in multi agent is initial stage is like g1. and then on click shows multi agents.
			 * (solution: if any existing animation works will add that else will display plan)
			 * [done if no jq] 2.uiShow( '.for_greetings_header_image_badge' ); // Shows Offline Badge for greetings header image
			 *
			 */
			show ( target, duration = '', animation = '', classToAdd = '', classToRemove = '' ) {

				const els = resolveEls( target );

				// jQuery fallback
				if ( ctcJq ) {

					// if target is .ctc_opt_in then animate
					if ( '.ctc_opt_in' === target ) {
						ctcJq( target )
							.fadeOut( 200 )
							.fadeIn( 200 )
							.fadeOut( 200 )
							.fadeIn( 200 );
						return;
					}

					ctcJq( target )
						.show( duration || undefined );
					return;
				}

				els.forEach( el => {

					if ( classToAdd ) {
						// el.classList.add(classToAdd);
						classToAdd.split( /\s+/ )
							.forEach( cls => {
								if ( cls.trim() ) { el.classList.add( cls.trim() ); }
							} );
					}
					if ( classToRemove ) {
						// el.classList.remove(classToRemove);
						classToRemove.split( /\s+/ )
							.forEach( cls => {
								if ( cls.trim() ) { el.classList.remove( cls.trim() ); }
							} );
					}

					// el.style.display = '';

					// Apply duration only if passed
					if ( duration ) {

						// inline variables added using js variables wont inherit. this can work.
						el.style.setProperty( '--ht-ctc-el-duration', `${duration}ms` );
					} else {
						el.style.removeProperty( '--ht-ctc-el-duration' );
					}

					// Apply animation only if passed
					if ( animation ) {
						playAnimation( el, animation );
					}

					// if no other parameters are added. just ui.show() the display direclty
					if ( ! classToAdd && ! classToRemove && ! duration && ! animation ) {
						// el.style.display = '';
						el.style.display = 'block';
					}
				} );
			},

			/**
			 *
			 * @uses
			 * 1.ui.hide( '.ctc_opt_in', 100 ); // Hides the optin checkbox
			 * [ok if no jq] 2.ui.hide( '.ht_ctc_chat_greetings_box', 70 ); // Hides the Greetins Box - quick close. when click on custom element like .ctc_greetings to open greetings
			 * 3.ui.hide( '.ht-ctc-chat .ht-ctc-cta-hover', 100, 'defaultHide' ); // Hides CTA on hover
			 *
			 * 1.uiHide( optinWrapper); // Hides the optin in Greetings Form
			 * 2.uiHide( '.ctc_opt_in', 100, '' ); //Hides optin in Greetings Multi-Agent
			 * 3.uiHide( '.ctc_opt_in', 100, '' ); // Hides optin in Mutli-Agent
			 */
			hide ( target, duration = '', animation = '', classToAdd = '', classToRemove = '' ) {

				const els = resolveEls( target );

				if ( ctcJq ) {
					ctcJq( target )
						.hide( duration );
					return;
				}

				els.forEach( el => {

					if ( classToAdd ) {
						el.classList.add( classToAdd );
					}
					if ( classToRemove ) {
						el.classList.remove( classToRemove );
					}

					if ( duration ) {
						el.style.setProperty( '--ht-ctc-el-duration', `${duration}ms` );
					} else {
						el.style.removeProperty( '--ht-ctc-el-duration' );
					}

					if ( animation ) {
						el.classList.add( `ht-ctc-${animation}` );
					}

					// if no other parameters are added. just ui.hide() the display direclty
					if ( ! classToAdd && ! classToRemove && ! duration && ! animation ) {
						el.style.display = 'none';
					}

				} );
			},

		};

		// Initialize plugin configuration containers

		// Step 1: Load config from global variables if already defined (preferred and most common)
		if ( typeof ht_ctc_chat_var !== 'undefined' ) {
			ctc = ht_ctc_chat_var;
		}

		if ( typeof ht_ctc_variables !== 'undefined' ) {
			ctc_values = ht_ctc_variables;
		}

		// Step 2: If not available globally, fallback to fetching via REST API
		// This ensures the plugin works even when globals are not rendered inline
		if ( Object.keys( ctc ).length === 0 || Object.keys( ctc_values ).length === 0 ) {
			// Use modern async/fetch approach to get values from server
			// Once fetched, the start() function will be called internally
			// getValuesUsingRestApi();

			// existing way..
			getValues();
		} else {
			// Config already available, proceed to initialize the plugin
			start();
		}

		/**
		 * Fallback method to load settings
		 */
		function getValues () {

			const chatData = document.querySelector( '.ht_ctc_chat_data' );
			if (
				Object.keys( ctc ).length === 0 &&
				chatData
			) {
				try {
					const settings = chatData
						?.getAttribute( 'data-settings' ) || '';
					ctc = JSON.parse( settings );
					window.ht_ctc_chat_var = ctc;
				} catch ( error ) {
					console.error( 'Failed to parse ht_ctc_chat_data', error );
				}
			}

			// if ctc_values is not set, then set default values
			if ( Object.keys( ctc_values ).length === 0 ) {
				ctc_values = {
					'g_an_event_name': 'click to chat',
					'pixel_event_name': 'Click to Chat by HoliThemes',
					'pixel_event_type': 'trackCustom',
					'g_an_params': [
						{ 'key': 'number', 'value': '{number}' },
						{ 'key': 'title', 'value': '{title}' },
						{ 'key': 'url', 'value': '{url}' },
					],
					'pixel_params': [
						{ 'key': 'Category', 'value': 'Click to Chat for WhatsApp' },
						{ 'key': 'return_type', 'value': 'chat' },
						{ 'key': 'ID', 'value': '{number}' },
						{ 'key': 'Title', 'value': '{title}' },
					],
				};

				window.ht_ctc_variables = ctc_values;
			}

			// start
			start();

		}

		/**
		 * Fallback method if wp_localize_script values are not available.
		 * Load ht_ctc_chat_var, ht_ctc_variables using REST API
		 */
		// Fetch Click to Chat settings from REST API if not already defined globally
		// function getValuesUsingRestApi() {
		// 	console.log('Loading settings from REST API');

		// 	// Extract nonce for REST API request from DOM element
		// 	let nonce = document.querySelector( '.ht_ctc_chat_data' )
		// 		?.getAttribute( 'data-rest' ) || '';

		// 	console.log('Nonce for REST API:', nonce);

		// 	// Abort if nonce is missing, as REST API requires it for authentication
		// 	if (!nonce) {
		// 		console.warn('⛔ No nonce found for REST API. Skipping fetch calls.');
		// 		return;
		// 	}

		// 	const header = {
		// 		'X-WP-Nonce': nonce,
		// 	};

		// 	// Check if the browser supports fetch and Promise (modern environment)
		// 	if (typeof fetch !== 'undefined' && typeof Promise !== 'undefined') {
		// 		console.log('Async/fetch supported. Fetching settings from REST API...');

		// 		try {
		// 			// Asynchronously load data and then call start()
		// 			( async function handleCallback () {
		// 				await load_ctc_settings();
		// 				// Fetch 'ht_ctc_chat_var' from REST API and assign to `ctc`
		// 				// (also saved as window.ht_ctc_chat_var)
		// 				await load_ctc_values();
		// 				// Fetch 'ht_ctc_variables from REST API and assign to `ctc_values`
		// 				// (also saved as window.ht_ctc_variables)
		// 				start(); // Initialize the plugin after all settings are loaded
		// 			} )();
		// 		} catch ( error ) {
		// 			console.warn('Async fallback failed:', error);
		// 			start();
		// 		}

		// 		/**
		// 		 * Load `ht_ctc_chat_var` configuration from the REST API.
		// 		 * This includes chat button settings, position, visibility, etc.
		// 		 * The result is assigned to the local variable `ctc`
		// 		 * and also exposed globally via `window.ht_ctc_chat_var`.
		// 		 * Called only if settings are not already available in the global scope.
		// 		 */
		// 		async function load_ctc_settings() {
		// 			try {
		// 				const controller = new AbortController();
		// 				const timeoutId = setTimeout(() => controller.abort(), 5000);

		// 				const response = await fetch(
		// 					'/wp-json/click-to-chat-for-whatsapp/v1/get_ht_ctc_chat_var',
		// 					{
		// 						method: 'GET',
		// 						signal: controller.signal,
		// 						headers: header,
		// 					}
		// 				);
		// 				clearTimeout(timeoutId);

		// 				if (response.ok) {
		// 					const data = await response.json();
		// 					if (data && typeof data === 'object') {
		// 						ctc = data;
		// 						console.log( 'ht_ctc_chat_var loaded:', ctc );
		// 						// Assign to global variable for easy access
		// 						// in other scripts
		// 						window.ht_ctc_chat_var = ctc;
		// 					}
		// 				} else {
		// 					console.warn('Failed to fetch ht_ctc_chat_var');
		// 				}
		// 			} catch (error) {
		// 				console.error('Error loading ht_ctc_chat_var:', error);
		// 			}
		// 		}

		// 		/**
		// 		 * Load `ht_ctc_variables` from the REST API.
		// 		 * These are additional global variables required for rendering
		// 		 * or logic (e.g., online status, labels).
		// 		 * The result is assigned to the local variable `ctc_values`
		// 		 * and also exposed globally via `window.ht_ctc_variables`.
		// 		 * Called only if values are not already available in the global scope.
		// 		 */
		// 		async function load_ctc_values() {
		// 			try {
		// 				const controller = new AbortController();
		// 				const timeoutId = setTimeout(() => controller.abort(), 5000);

		// 				const response = await fetch(
		// 					'/wp-json/click-to-chat-for-whatsapp/v1/get_ht_ctc_variables',
		// 					{
		// 						signal: controller.signal,
		// 						headers: header,
		// 					}
		// 				);
		// 				clearTimeout(timeoutId);

		// 				if (response.ok) {
		// 					const data = await response.json();
		// 					if (data && typeof data === 'object') {
		// 						ctc_values = data;
		// 						console.log('ht_ctc_variables loaded:', ctc_values);
		// 						// Assign to global variable for easy access in other scripts
		// 						window.ht_ctc_variables = ctc_values;
		// 					}
		// 				} else {
		// 					console.warn('Failed to fetch ht_ctc_variables');
		// 				}
		// 			} catch (error) {
		// 				console.error('Error loading ht_ctc_variables:', error);
		// 			}
		// 		}
		// 	} else {
		// 		// Fallback: Skip execution if the environment doesn't support fetch/Promise
		// 		console.warn('⛔ Async/fetch not supported. Skipping fetch calls.');
		// 	}
		// }

		// Build the payload for other scripts (pro.js, date.js, custom code, etc.)
		function buildHtCtcInitDetail () {

			// ---------------------------------------------
			// CONFIG (raw values)
			// ---------------------------------------------
			const config = {
				// version: '4.34',
				ctc: ctc,                // main config from wp_localize_script
				ctc_values: ctc_values, // secondary config
				is_mobile: is_mobile,
				url: url,
				post_title: post_title,

				// storage: ht_ctc_storage,
			};

			// ---------------------------------------------
			// API (all most of functions)
			// ---------------------------------------------
			const api = {

				// --------------------------
				// Storage API
				// --------------------------
				storage: {
					get: ctc_getItem,
					set: ctc_setItem,
					raw: ht_ctc_storage,
				},

				// --------------------------
				// API (all most of functions)
				// --------------------------
				ui: ui,             // ← unified UI system

				// --------------------------
				// Greetings system
				// --------------------------
				greetings: {
					open: greetings_open,
					close: greetings_close,
					closeAfterClick: greetings_close_500,
					initListeners: greetings,
					display: greetings_display,
				},

				// --------------------------
				// Notifications system
				// --------------------------
				notifications: {
					display: display_notifications,
					stop: stop_notification_badge,
				},

				// --------------------------
				// Chat functions
				// --------------------------
				chat: {
					openLink: ht_ctc_link,
					displaySettings: display_settings,
				},

				// --------------------------
				// Utility functions
				// --------------------------
				utils: {
					isSafeObjectKey,
					getSafeProperty,
					setSafeProperty,

					// timeOnWp: time_on_wordpress,
					// applyVariables: apply_variables,
				},
			};

			// // an event listener so that other scripts can access config + api together and update if needed
			// this.dispatchEvent(new CustomEvent('ht_ctc_event_build_config', {
			// 	detail: {
			// 		config,
			// 		api,
			// 	}
			// }));

			return {
				config,
				api,
			};
		}

		// Initialize the plugin after settings are loaded
		function start () {

			// 🔹 New: Configure event to allow users to override settings
			// Dispatched before any other processing to ensure overrides are applied
			document.dispatchEvent( new CustomEvent( 'ht_ctc_event_configure', { detail: { ctc, ctc_values } } ) );

			// remove ht_ctc_chat_data - Clean up the element after extracting settings
			var el = document.querySelector( '.ht_ctc_chat_data' );
			if ( el ) {
				el.remove();
			}

			// 🔹 New: global init event with config + api + shortcuts
			const initDetail = buildHtCtcInitDetail();
			document.dispatchEvent( new CustomEvent( 'ht_ctc_event_init', { detail: initDetail } ) );

			// 🔹 Keep old event for backward compatibility
			document.dispatchEvent( new CustomEvent(
				'ht_ctc_event_settings',
				{ detail: { ctc } },
			) );

			// Initialize the main fixed-position chat button (bottom left or right of screen)
			ht_ctc();

			// Render any plugin shortcodes placed in the content
			shortcode();

			// Initialize any elements using the [ht-ctc] custom HTML tag or class
			custom_link();
		}

		// E.g. add event listener for ht_ctc_event_configure
		// document.addEventListener('ht_ctc_event_configure', function (event) {
		// 	var g1_form_webhook = 'https://example.com/webhook';
		// 	var ctc = event.detail.ctc;
		// 	ctc.g1_form_webhook = g1_form_webhook;
		// });
		// 	// Modify the configuration directly on the event object
		// document.addEventListener('ht_ctc_event_configure', function (event) {
		// 	event.detail.ctc.g1_form_webhook = 'https://example.com/webhook';
		// });

		// fixed position
		function ht_ctc () {
			if ( ht_ctc_chat ) {
				document.dispatchEvent( new CustomEvent( 'ht_ctc_event_chat' ) );

				// display
				display_settings( ht_ctc_chat );

				// click
				ht_ctc_chat.addEventListener( 'click', function handleCallback () {
					// ht_ctc_chat_greetings_box (ht_ctc_chat_greetings_box_link) is not exists..

					// if greetings dialog is not exists, directly navigates to chat
					if ( ! document.querySelector( '.ht_ctc_chat_greetings_box' ) ) {

						// link
						ht_ctc_link( ht_ctc_chat );
					}
				} );

				// greetings dialog settings..
				greetings();

				// Select the main container of the plugin
				// to scope the click listener only to our plugin
				if ( ht_ctc_chat ) {
					// Add click event listener only within the plugin container
					ht_ctc_chat.addEventListener( 'click', function handleEvent ( event ) {
						// Check if the clicked element (or its ancestor)
						// is the greetings box link
						const target = event.target.closest( '.ht_ctc_chat_greetings_box_link' );

						if ( target ) {
							// Prevent the default link behavior (like navigating away)
							event.preventDefault();

							// Get the opt-in checkbox (if it exists in DOM)
							const optCheckbox = document.querySelector( '#ctc_opt' );

							if ( optCheckbox ) {
								// Proceed only if the checkbox is checked
								// OR user has previously opted in (via localStorage or cookie)
								if ( optCheckbox.checked || ctc_getItem( 'g_optin' ) ) {

									// Open the chat link
									ht_ctc_link( ht_ctc_chat );

									// Close the greetings box after 500ms (custom function)
									greetings_close_500();
								} else {
									// User hasn't opted in — show the opt-in prompt

									// Blink the opt-in checkbox to draw attention
									ui.show( '.ctc_opt_in', '', 'ht-ctc-fade-in', '', '' );

								}
							} else {
								// If checkbox not found, fallback to open chat directly
								ht_ctc_link( ht_ctc_chat );
								greetings_close_500();
							}

							// Dispatch a custom event so other parts of the plugin/theme
							// can hook into this action
							document.dispatchEvent( new CustomEvent( 'ht_ctc_event_greetings' ) );
						}
					} );
				}

				// Javascript
				// Select the opt-in checkbox element
				const optCheckbox = document.querySelector( '#ctc_opt' );

				if ( optCheckbox ) {
					// Add a 'change' event listener
					// to detect when the checkbox is checked/unchecked
					optCheckbox.addEventListener( 'change', function handleCallback () {
						// Proceed only if the checkbox is checked (i.e., user opted in)
						if ( optCheckbox.checked ) {
							// Select the opt-in UI element (e.g., the popup box)
							const optInElement = document.querySelector( '.ctc_opt_in' );

							if ( optInElement ) {
								ui.hide( '.ctc_opt_in', 100 );
							}

							// Store the user's opt-in status using a custom utility
							// (e.g., localStorage)
							ctc_setItem( 'g_optin', 'y' );

							// After a short delay, trigger the chat link
							// and close the greetings box
							setTimeout( () => {
								ht_ctc_link( ht_ctc_chat );
								greetings_close_500();
							}, 500 );
						}
					} );
				}
			}
		}

		/**
		 * greetings dialog
		 */
		function greetings () {
			// Check if the main chat container exists
			if ( ht_ctc_chat ) {
				const greetingsBox = document.querySelector( '.ht_ctc_chat_greetings_box' );

				if ( greetingsBox ) {
					// Listen for clicks inside the chat container
					ht_ctc_chat.addEventListener( 'click', function handleEvent ( event ) {
						// Check if the clicked element (or its parent)
						// has `.ht_ctc_chat_style` class. to undestand that the click is on chat style button or greeting style button
						const chatStyle = event.target.closest( '.ht_ctc_chat_style' );

						if ( chatStyle ) {

							// Toggle the greetings box open/close
							if ( greetingsBox.classList.contains( 'ctc_greetings_opened' ) ) {
								greetings_close( 'user_closed' );
							} else {
								greetings_open( 'user_opened' );
							}
						}
					} );
				}

				// Listen for click on greetings close button
				ht_ctc_chat.addEventListener( 'click', function handleEvent ( event ) {
					if ( event.target.closest( '.ctc_greetings_close_btn' ) ) {
						greetings_close( 'user_closed' );
					}
				} );
			}
		}

		function greetings_display () {

			const greetingsBox = document.querySelector( '.ht_ctc_chat_greetings_box' );

			if ( greetingsBox ) {

				// Device-specific display logic
				if ( ctc.g_device ) {
					if ( 'yes' !== is_mobile && 'mobile' === ctc.g_device ) {
						// If device is desktop but greeting is mobile-only, remove it
						greetingsBox.remove();
						return;
					} else if ( 'yes' === is_mobile && 'desktop' === ctc.g_device ) {
						// If device is mobile but greeting is desktop-only, remove it
						greetingsBox.remove();
						return;
					}
				}

				// Dispatch custom event indicating greetings box is now displayed
				document.dispatchEvent( new CustomEvent(
					'ht_ctc_event_after_chat_displayed',
					{
						detail: { ctc, greetings_open, greetings_close },
					},
				) );

				// Auto open logic based on `g_init` config
				if ( ctc.g_init && ctc_getItem( 'g_user_action' ) !== 'user_closed' ) {
					if ( ctc.g_init === 'default' ) {
						if ( is_mobile !== 'yes' ) {
							greetings_open( 'init' );
						}
					} else if ( ctc.g_init === 'open' ) {
						greetings_open( 'init' );
					}
				}

				// // Greetings Action: click — opens the greetings dialog
				// // when specific elements are clicked

				// // Use event delegation for dynamically added elements
				// // Listen for clicks on any element matching the selectors below
				// document.addEventListener('click', function (e) {
				//     const selector =
				//         '.ctc_greetings, #ctc_greetings, .ctc_greetings_now, ' +
				//         '[href=\"#ctc_greetings\"]';
				//     const el = e.target.closest(selector);

				//     if (el) {
				//         console.log('greetings open triggered');

				//         e.preventDefault(); // Prevent default anchor behavior if it's a link

				//         // Close any existing greetings box first
				//         greetings_close('element');

				//         // Open the greetings box
				//         greetings_open('element');
				//     }
				// });

				// Find all elements that should trigger the greetings dialog
				// These include: .ctc_greetings, #ctc_greetings, .ctc_greetings_now,
				// or [href="#ctc_greetings"]
				// (This is a non-delegated approach —
				// works only for elements present at page load)

				const greetingsTriggers = document.querySelectorAll( '.ctc_greetings, #ctc_greetings, .ctc_greetings_now,' +
					' [href="#ctc_greetings"]' );

				if ( greetingsTriggers.length > 0 ) {

					// Attach individual click listeners to each trigger
					greetingsTriggers.forEach( function handleElement ( el ) {
						el.addEventListener( 'click', function handleEvent ( event ) {
							// Prevent link behavior if it's an anchor
							event.preventDefault();

							// Close existing greetings box (if open)
							greetings_close( 'element' );

							// Open greetings box
							greetings_open( 'element' );
						} );
					} );
				}
			}
		}

		/**
			 * ht_ctc_chat_greetings_box_user_action - this is needed for initial close or open.
			 * if user closed then no auto open initially.
		 *
		 * g_action: open, close, chat_clicked, user_opened, user_closed
		 * g_user_action: user_opened, user_closed
		 *
		 *
		 * init - this is used to open greetings box on page load
		 * user_opened - this is used to track if user manually opened the greetings box
		 * user_closed - this is used to track if user manually closed the greetings box
		 *
		 */
		function greetings_open ( message = 'open' ) {

			// Stop notification badge if it's currently displayed
			stop_notification_badge();

			// Remove CTA sticky button if it exists.
			// Reason: When the greetings box is shown,
			// the CTA button can visually or functionally conflict.
			// This ensures only one interactive element is shown at a time
			// to avoid overlapping actions.
			const el = document.querySelector( '.ht-ctc-chat .ctc_cta_stick' );
			if ( el ) {
				el.remove();
			}

			// Get the greetings box element
			const greetingsBox = document.querySelector( '.ht_ctc_chat_greetings_box' );
			if ( greetingsBox ) {
				// Show the greetings box with animation
				// Use shorter duration if message is 'init'
				if ( 'init' === message ) {
					// initial open - faster - auto open with chat base widget.
					ui.show( '.ht_ctc_chat_greetings_box', 70, '', 'ht_ctc_greetings_box_open', '' );
				} else {
					// mostly user triggered open, ..
					ui.show( '.ht_ctc_chat_greetings_box', 400, '', 'ht_ctc_greetings_box_open', '' );
				}

				// Update the state classes
				greetingsBox.classList.add( 'ctc_greetings_opened' );
				greetingsBox.classList.remove( 'ctc_greetings_closed' );
			}

			// Save user action to localStorage (via wrapper)
			ctc_setItem( 'g_action', message );

			// If user manually opened it, also save separate user intent
			if ( 'user_opened' === message ) {
				ctc_setItem( 'g_user_action', message );
			}

			// Create a modal backdrop behind the greeting box for better UX
			createModalBackdrop();
		}

		// Close the greetings box after a delay of 500 milliseconds
		function greetings_close_500 () {
			// Remove the modal backdrop behind the greetings box
			closeModalBackdrop();

			// Wait for 500 milliseconds before closing the greetings box
			setTimeout( () => {
				// Trigger the greetings close function with the action 'chat_clicked'
				greetings_close( 'chat_clicked' );
			}, 500 );
		}

		/**
		 *
		 * @param {*} message
		 */
		// Close the greetings box with different behaviors based on the message type
		function greetings_close ( message = 'close' ) {

			// Remove the modal backdrop (overlay) from the screen
			closeModalBackdrop();

			if ( 'element' === message ) {
				// element - close quickly when triggered by element click like .ctc_greetings.
				ui.hide( '.ht_ctc_chat_greetings_box', 70, '', 'ht-ctc-display-unset', '' );
			} else {
				ui.hide( '.ht_ctc_chat_greetings_box', 400, '', '', 'ht_ctc_greetings_box_open' );
			}

			// Update the class names to reflect that the box is now closed
			const greetingsBox = document.querySelector( '.ht_ctc_chat_greetings_box' );
			if ( greetingsBox ) {
				// Mark as closed
				greetingsBox.classList.add( 'ctc_greetings_closed' );

				// Remove open status
				greetingsBox.classList.remove( 'ctc_greetings_opened' );
			}

			// Store the action in localStorage
			ctc_setItem( 'g_action', message );

			// If user manually closed the greetings, store additional flag
			if ( 'user_closed' === message ) {
				ctc_setItem( 'g_user_action', message );
			}
		}

		/**
		 * create modal backdrop
		 *
		 * ht_ctc_modal_open - for scroll lock by adding class to body with css overflow: hidden;
		 */
		function createModalBackdrop () {
			// Check if the modal element with .ctc_greetings_modal exists
			const modal = document.querySelector( '.ctc_greetings_modal' );
			if ( ! modal ) {
				return;
			}

			// Only create the backdrop if it doesn't already exist
			if ( ! document.querySelector( '.ht_ctc_modal_backdrop' ) ) {

				const backdrop = document.createElement( 'div' );
				backdrop.className = 'ht_ctc_modal_backdrop';

				// Append the backdrop to the body
				document.body.appendChild( backdrop );

				// Add click listener to close greetings on backdrop click
				backdrop.addEventListener( 'click', function handleCallback () {
					greetings_close( 'user_closed' );
				} );

				// Add Escape key listener with a named handler for IE-compatible removal
				function handleEscapeKey ( event ) {
					if ( event.key === 'Escape' ) {
						greetings_close( 'user_closed' );
						document.removeEventListener( 'keydown', handleEscapeKey );
					}
				}
				document.addEventListener( 'keydown', handleEscapeKey );

				// Optionally add class to body for scroll lock or visual effects
				// document.body.classList.add('ht_ctc_modal_open');
			}
		}

		/**
		 * Close and remove the modal backdrop overlay.
		 * This is used when the greetings dialog (or any modal) is dismissed,
		 * ensuring the background overlay is also cleaned up.
		 */
		function closeModalBackdrop () {
			// Check if the modal backdrop exists in the DOM
			const modalBackdrop = document.querySelector( '.ht_ctc_modal_backdrop' );
			if ( modalBackdrop ) {

				// Remove the backdrop element from the DOM
				modalBackdrop.remove();
			}

			// Optional: remove any modal-open related styles from body
			// document.body.classList.remove('ht_ctc_modal_open');
		}

		// Display settings - handles how the chat button appears (based on schedule or directly)
		// Applies fixed-position styling and triggers content display logic
		function display_settings ( ht_ctc_chat ) {
			// If scheduling is enabled via plugin settings
			if ( ctc.schedule && 'yes' === ctc.schedule ) {

				// Dispatch an event so external scripts or handlers can control when/how to display
				document.dispatchEvent( new CustomEvent( 'ht_ctc_event_display', {
					detail: {
						ctc, // Chat config data
						display_chat, // Function to call when ready to display
						ht_ctc_chat, // The main chat DOM element
						online_content, // Function to update online indicators
					},
				} ) );
			} else {
				// If no schedule is applied, display the button immediately
				display_chat( ht_ctc_chat ); // Show the button
				online_content(); // Mark badge/agent as online if needed
			}
		}

		// Determine which version of the chat button to display based on the user's device.
		// Applies positioning and styling, and ensures only the correct variant is visible.
		function display_chat ( chatElement ) {
			if ( is_mobile === 'yes' ) {
				// If user is on mobile and mobile display is enabled
				if ( 'show' === ctc.dis_m ) {
					// Remove desktop version to avoid layout or interaction conflicts
					const desktopChat = document.querySelector( '.ht_ctc_desktop_chat' );
					if ( desktopChat ) { desktopChat.remove(); }

					// Apply mobile-specific styles
					chatElement.style.cssText = ctc.pos_m + ctc.css;

					if ( ctc.side_m ) {
						chatElement.style.setProperty( '--side', ctc.side_m );
					}

					// Show the chat element
					display( chatElement );
				}
			} else {
				// If user is on desktop and desktop display is enabled
				if ( 'show' === ctc.dis_d ) {
					// Remove mobile version to avoid layout or interaction conflicts
					const mobileChat = document.querySelector( '.ht_ctc_mobile_chat' );
					if ( mobileChat ) { mobileChat.remove(); }

					// Apply desktop-specific position and custom CSS styles
					chatElement.style.cssText = ctc.pos_d + ctc.css;

					if ( ctc.side_d ) {
						chatElement.style.setProperty( '--side', ctc.side_d );

					}

					// Make the chat button visible
					display( chatElement );
				}
			}
		}

		// Show the chat element using jQuery if available, else fallback to plain JS.
		// Also triggers additional plugin behavior like greetings and notifications.
		function display ( chatElement ) {

			/**
			 * cts.se can be if setting is corner then '150' or if center then 'center' etc..
			 * se : show_effect
			 */

			var showEffect = ctc.se || '';

			// showEffect = parseInt(ctc.se, 10);
			// NaN this can works perfect with jQuery show function to display css animations
			showEffect = parseInt( ctc.se );

			if ( ! isNaN( showEffect ) ) {

				// Numeric → corner animation → use ui.show with effect time
				ui.show( chatElement, showEffect, '', 'ht-ctc-display-unset', '' );
			} else {

				// no duration → allow CSS animation to run. like 'center'
				ui.show( chatElement, '', '', 'ht-ctc-display-unset', '' );
			}

			// chatElement.classList.remove('ht_ctc_entry_animation');

			// due to cache still using above logic. but all set. we can remove above code later and use below line directly. (due to cache from php if corner animation not loaded)
			// ui.show( chatElement, '', '', 'ht-ctc-display-unset', '' );

			// Display the greetings dialog if enabled
			greetings_display();

			// Show notification badge (e.g., unread messages or alert indicator)
			display_notifications();

			// Run any additional setup tasks or DOM adjustments for the chat element
			ht_ctc_things( chatElement );
		}

		/**
		 * online content
		 *
		 * @since 3.34
		 */
		// This function marks the greetings header image badge as online
		function online_content () {

			// Check if any element with class `.for_greetings_header_image_badge` exists
			if ( document.querySelector( '.for_greetings_header_image_badge' ) ) {
				// Add the `g_header_badge_online` class to all matching elements
				document.querySelectorAll( '.for_greetings_header_image_badge' )
					.forEach( ( el ) => {
						el.classList.add( 'g_header_badge_online' );
					} );
				ui.show( '.for_greetings_header_image_badge', '', '', 'ht-ctc-display-unset', '' );
			}
		}

		// Display notifications - shows the notification badge if it exists and is not stopped
		function display_notifications () {

			// Check if the notification element exists and the notification badge is not stopped
			const notificationEl = document.querySelector( '.ht_ctc_notification' );

			if ( notificationEl && ctc_getItem( 'n_badge' ) !== 'stop' ) {
				// If badge positioning element exists (for top/right override)
				const badgeEl = document.querySelector( '.ctc_nb' );

				if ( badgeEl ) {

					// Find the closest parent with class .ht_ctc_style
					const main = badgeEl.closest( '.ht_ctc_style' );

					// Select the badge element that needs positioning
					const htCtcBadge = document.querySelector( '.ht_ctc_badge' );

					if ( main && htCtcBadge ) {
						// Get top and right values from data attributes
						const top = main.querySelector( '.ctc_nb' )
							?.getAttribute( 'data-nb_top' );
						const right = main.querySelector( '.ctc_nb' )
							?.getAttribute( 'data-nb_right' );

						// Apply the top and right styles to the badge, if defined
						if ( top !== null ) { htCtcBadge.style.top = top; }
						if ( right !== null ) { htCtcBadge.style.right = right; }
					}
				}

				// Set timeout duration based on ctc.n_time (in seconds), fallback to 150ms
				const n_time = ctc.n_time ? ctc.n_time * 1000 : 150;

				// Show the notification after the timeout with jQuery animation
				setTimeout( () => {

					// ui.show('.ht_ctc_notification', 400, '', 'ht-ctc-display-unset', '');
					notificationEl.style.display = ''; // Remove display:none
				}, n_time );
			}
		}

		// Called after the user clicks to chat or opens the greetings box
		function stop_notification_badge () {

			// Check if the notification element exists
			const notificationEl = document.querySelector( '.ht_ctc_notification' );

			if ( notificationEl ) {

				// Save stop flag to storage
				ctc_setItem( 'n_badge', 'stop' );

				// Remove the element from the DOM
				notificationEl.remove();
			}
		}

		// Animation and CTA hover effect
		function ht_ctc_things ( chatElement ) {

			// Entry animation delay based on class for width animation i.e. after display
			var an_time = chatElement.classList.contains( 'ht_ctc_entry_animation' ) ? 1200 : 120;

			// Add animation class after delay
			setTimeout( function handleCallback () {
				chatElement.classList.add( 'ht_ctc_animation', ctc.ani );
			}, an_time );

			// Hover effect for CTA button
			const chatEl = document.querySelector( '.ht-ctc-chat' );
			const ctaHover = document.querySelector( '.ht-ctc-chat .ht-ctc-cta-hover' );
			if ( chatEl && ctaHover ) {
				chatEl.addEventListener( 'mouseenter', function onMouseEnter () {
					// console.log( 'hover in' );
					// ht-ctc-opacity-show    ht-ctc-opacity-hide
					ui.show( '.ht-ctc-chat .ht-ctc-cta-hover', 120, '', 'ht-ctc-cta-stick', 'ht-ctc-opacity-hide' );
				} );
				chatEl.addEventListener( 'mouseleave', function onMouseLeave () {
					// console.log( 'hover out' );
					ui.hide( '.ht-ctc-chat .ht-ctc-cta-hover', 100, '', 'ht-ctc-opacity-hide', 'ht-ctc-cta-stick' );
				} );
			}

		}

		function ht_ctc_chat_analytics ( values ) {
			// Log the values passed for debugging

			// Check if analytics is enabled
			if ( ctc.analytics ) {
				// If analytics is set to 'session', track only once per session
				if ( 'session' === ctc.analytics ) {
					// If already tracked in this session, skip tracking
					if ( sessionStorage.getItem( 'ht_ctc_analytics' ) ) {
						return;
					} else {
						// This is a unique session
						// Set a flag in sessionStorage so analytics will not be triggered again
						// until the browser is closed
						sessionStorage.setItem( 'ht_ctc_analytics', 'done' );
					}
				}
			}

			// Function to apply dynamic values to a string containing placeholders
			// like {number}, {title}, {url}
			function apply_variables ( templateString ) {

				// Use chat_number if available, fallback to default number
				var number =
					ctc.chat_number && '' !== ctc.chat_number ? ctc.chat_number : ctc.number;

				try {

					// Trigger a custom event so other scripts
					// (e.g., addon plugin, custom scripts)
					// can hook in and modify the value
					document.dispatchEvent( new CustomEvent(
						'ht_ctc_event_apply_variables',
						{ detail: { templateString } },
					) );

					// Check if the custom event handler has modified the value
					// and saved it to window
					templateString =
						typeof window.apply_variables_value !== 'undefined' ?
							window.apply_variables_value :
							templateString;

					// Replace template placeholders in the string with actual dynamic values:
					// {number} → WhatsApp number,
					// {title} → Page/Post title,
					// {url} → Current page URL
					// templateString = templateString.replace(/\{number\}/gi, number);
					templateString = templateString.replace( '{number}', number );
					templateString = templateString.replace( '{title}', post_title );
					templateString = templateString.replace( '{url}', url );
				} catch ( error ) {
					console.error( 'Error processing measurement IDs', error );
				}

				return templateString;
			}

			// some unique id for the meta pixel event to avoid duplicate events
			var pixel_event_id = 'event_' + Math.floor( 10000 + Math.random() * 90000 );

			// Store the unique event ID in the global variable for later use
			ctc.ctc_pixel_event_id = pixel_event_id;

			// Dispatch custom event to notify that analytics event has started
			document.dispatchEvent( new CustomEvent( 'ht_ctc_event_analytics' ) );

			// Get the chat number from settings or fallback
			var id = ctc.chat_number && '' !== ctc.chat_number ? ctc.chat_number : ctc.number;

			// Google Analytics setup
			/**
				 * if installed using GTM then gtag may not work.
				 * so user can create event using dataLayer object.
				 * if google analytics installed using GTM
				 * (from GTM user can create event using gtm datalayer object, ...)
				 *
				 * if google analytics installed directly then gtag works.
				 *
				 * analytics - event names added to ht_ctc_chat_var
				 * (its loads most cases with out issue)
				 * and event params added to ht_ctc_variables.
				 */

			// Create basic event info
			var ga_params = new Map();
			const getGaParamsObject = () => Object.fromEntries( ga_params );
			var ga_category = 'Click to Chat for WhatsApp';
			var ga_action = 'chat: ' + id;
			var ga_label = post_title + ', ' + url;

			// If GA is enabled
			if ( ctc.ga ) {

				// Use custom event name or default
				var g_event_name =
					ctc.g_an_event_name && '' !== ctc.g_an_event_name ?
						ctc.g_an_event_name :
						'click to chat';
				g_event_name = apply_variables( g_event_name );

				// Log ctc_values for debugging

				// Build event parameters if available
				const gAnParams = ctc_values.g_an_params_v4 || ctc_values.g_an_params;
				if ( Array.isArray( gAnParams ) ) {
					gAnParams.forEach( ( param ) => {
						if ( ! param ) {
							return;
						}
						var parameterKey, parameterValue;

						// new method.. (with comment the code)
						/*
						if ( typeof param === 'object' ) {
							parameterKey = param.key;
							parameterValue = param.value;
						}
						*/

						// backward compatibility method..
						if ( typeof param === 'object' ) {
							parameterKey = param.key;
							parameterValue = param.value;
						} else if ( typeof param === 'string' && isSafeObjectKey( param ) ) {
							// const parameterDefinition = ctc_values[ param ];
							const parameterDefinition = getSafeProperty( ctc_values, param );
							if ( parameterDefinition && typeof parameterDefinition === 'object' ) {
								parameterKey = parameterDefinition.key;
								parameterValue = parameterDefinition.value;
							}
						}

						// common logic..
						if ( typeof parameterKey !== 'string' ) {
							return;
						}
						parameterKey = apply_variables( parameterKey );
						parameterValue = apply_variables( parameterValue );
						if ( ! isSafeObjectKey( parameterKey ) ) {
							return;
						}
						ga_params.set( parameterKey, parameterValue );
					} );
				}

				var gtag_count = 0;

				// Keep track of whether we added gtag manually
				var is_ctc_add_gtag = 'no';
				var measurement_ids = [];

				// If Google Tag Manager's dataLayer is present
				if ( typeof dataLayer !== 'undefined' ) {

					try {
						// Define gtag function if it's not available
						if ( typeof gtag === 'undefined' ) {

							// prefer-rest-params
							window.gtag = function handleCallback ( ...args ) {
								dataLayer.push( ...args );
							};
							is_ctc_add_gtag = 'yes';
						}

						var tags_list = [];

						// Helper function to trigger gtag event
						function call_gtag ( tag_id ) {
							tag_id = tag_id.toUpperCase();

							if ( tags_list.includes( tag_id ) ) {
								return;
							}

							tags_list.push( tag_id );

							// Only allow certain tag ID formats
							if ( tag_id.startsWith( 'G-' ) || tag_id.startsWith( 'GT-' ) ) {
								ga_params.set( 'send_to', tag_id );

								gtag( 'event', g_event_name, getGaParamsObject() );

								gtag_count++;
							}
						}

						/**
						 * Helper: Add unique ID to measurement_ids array
						 */
						function addMeasurementId ( id, source ) {
							if ( id && typeof id === 'string' && id.trim() !== '' ) {
								if ( ! measurement_ids.includes( id ) ) {
									measurement_ids.push( id );
								}
							}
						}

						/**
						 * From google_tag_data.tidr.destination
						 */
						try {
							const tidr = window.google_tag_data?.tidr;
							if ( tidr?.destination && typeof tidr.destination === 'object' ) {
								Object.keys( tidr.destination )
									.forEach( tag_id => {
										addMeasurementId( tag_id, 'google_tag_data.destination' );
									} );
							}
						} catch ( err ) {
							console.warn( 'Error reading google_tag_data.tidr.destination', err );
						}

						/**
						 * From google_tag_data.tidr.container → destinations[]
						 */
						try {
							const containers = window.google_tag_data?.tidr?.container;
							if ( containers && typeof containers === 'object' ) {
								Object.values( containers )
									.forEach( container => {
										if ( Array.isArray( container.destinations ) ) {
											container.destinations.forEach( dest => {
												if (
													typeof dest === 'string' &&
													dest.startsWith( 'G-' )
												) {
													addMeasurementId(
														dest,
														'google_tag_data.container.' +
														'destinations',
													);
												}
											} );
										}
									} );
							}
						} catch ( err ) {
							console.warn( 'Error reading google_tag_data.tidr.container', err );
						}

						/**
						 * From dataLayer[] (fallback)
						 */
						try {
							if ( Array.isArray( window.dataLayer ) ) {
								window.dataLayer.forEach( item => {
									if (
										Array.isArray( item ) &&
										item[ 0 ] === 'config' &&
										typeof item[ 1 ] === 'string'
									) {
										addMeasurementId( item[ 1 ], 'dataLayer.config' );
									} else if (
										item?.send_to &&
										typeof item.send_to === 'string'
									) {
										addMeasurementId( item.send_to, 'dataLayer.send_to' );
									}
								} );
							}
						} catch ( err ) {
							console.warn( 'Error scanning dataLayer', err );
						}

						// Call gtag for each unique measurement ID
						measurement_ids.forEach( function handleMeasurementId ( id ) {
							call_gtag( id );
						} );

					} catch ( error ) {
						console.error( 'apply_variables placeholder replacement failed', error );
					}
				}

				// Fallback: if no gtag events were sent and gtag exists, send the default event
				if ( 0 === gtag_count && 'no' === is_ctc_add_gtag ) {
					if ( typeof gtag !== 'undefined' ) {
						gtag( 'event', g_event_name, getGaParamsObject() );
					} else if ( typeof ga !== 'undefined' && typeof ga.getAll !== 'undefined' ) {
						var tracker = ga.getAll();
						tracker[ 0 ].send( 'event', ga_category, ga_action, ga_label );
					} else if ( typeof __gaTracker !== 'undefined' ) {
						__gaTracker( 'send', 'event', ga_category, ga_action, ga_label );
					}
				}
			}

			// Push analytics event to GTM dataLayer
			if ( typeof dataLayer !== 'undefined' ) {

				// if gtm is enabled. i.e. based on the GTM dataLayer object settings.

				if ( ctc.gtm ) {

					let gtm_event_name = ctc.gtm_event_name || 'Click to chat';
					gtm_event_name = apply_variables( gtm_event_name );

					const gtm_params_obj = {};

					gtm_params_obj.event = gtm_event_name;

					const gtmParams = ctc_values.gtm_params_v4 || ctc_values.gtm_params;
					if ( Array.isArray( gtmParams ) ) {
						gtmParams.forEach( ( param ) => {
							if ( ! param ) { return; }
							let defKey, defValue;

							// new method.. (with comment the code)
							/*
							if ( typeof param === 'object' ) {
								defKey = param.key;
								defValue = param.value;
							}
							*/

							// backward compatibility method..
							if ( typeof param === 'object' ) {
								defKey = param.key;
								defValue = param.value;
							} else if ( typeof param === 'string' && isSafeObjectKey( param ) ) {
								// const def = ctc_values[ param ];
								const def = getSafeProperty( ctc_values, param );
								if ( def && typeof def === 'object' ) {
									defKey = def.key;
									defValue = def.value;
								}
							}

							// common logic..
							if ( typeof defKey !== 'string' ) { return; }
							const key = apply_variables( defKey );
							const value = apply_variables( defValue );

							if ( ! isSafeObjectKey( key ) ) { return; }

							// gtm_params_obj[ key ] = value;
							setSafeProperty( gtm_params_obj, key, value );
						} );
					}

					dataLayer.push( gtm_params_obj );
				}

			}

			// Google Ads Conversion Tracking
			if ( ctc.ads ) {
				if ( typeof gtag_report_conversion !== 'undefined' ) {
					gtag_report_conversion();
				}
			}

			// Facebook Pixel Tracking
			if ( ctc.fb ) {

				if ( typeof fbq !== 'undefined' ) {
					// Get event name for FB Pixel or use default
					var pixelEventName =
						ctc.pixel_event_name && '' !== ctc.pixel_event_name ?
							ctc.pixel_event_name :
							'Click to Chat by HoliThemes';

					// Get pixel track type: track or trackCustom
					var pixelTrack =
						ctc_values.pixel_event_type && '' !== ctc_values.pixel_event_type ?
							ctc_values.pixel_event_type :
							'trackCustom';

					var pixelParams = new Map();

					// Prepare pixel parameters
					const pixelParamsArray = ctc_values.pixel_params_v4 || ctc_values.pixel_params;
					if ( Array.isArray( pixelParamsArray ) ) {
						pixelParamsArray.forEach( ( param ) => {
							if ( ! param ) {
								return;
							}
							let pixelParameterKey, pixelParameterValue;

							// new method.. (with comment the code)
							/*
							if ( typeof param === 'object' ) {
								pixelParameterKey = param.key;
								pixelParameterValue = param.value;
							}
							*/

							// backward compatibility method..
							if ( typeof param === 'object' ) {
								pixelParameterKey = param.key;
								pixelParameterValue = param.value;
							} else if ( typeof param === 'string' && isSafeObjectKey( param ) ) {
								// const pixelParameterDefinition = ctc_values[ param ];
								const pixelParameterDefinition = getSafeProperty(
									ctc_values,
									param,
								);
								if (
									pixelParameterDefinition &&
									typeof pixelParameterDefinition === 'object'
								) {
									pixelParameterKey = pixelParameterDefinition.key;
									pixelParameterValue = pixelParameterDefinition.value;
								}
							}

							// common logic..
							if ( typeof pixelParameterKey !== 'string' ) {
								return;
							}
							pixelParameterKey = apply_variables( pixelParameterKey );
							pixelParameterValue = apply_variables( pixelParameterValue );
							if ( ! isSafeObjectKey( pixelParameterKey ) ) {
								return;
							}
							pixelParams.set( pixelParameterKey, pixelParameterValue );
						} );
					}

					ctc.ctc_pixel_event_id = ''; // Reset the global pixel event ID

					// Send event to Facebook Pixel
					fbq(

						// Usually 'track'
						pixelTrack,

						// e.g. 'Click to Chat by HoliThemes', 'Purchase', 'Lead'
						pixelEventName,

						// parameters added at admin settings.
						// e.g. { key: value, key: 'value' }
						Object.fromEntries( pixelParams ),
						{
							eventID: pixel_event_id, // Deduplication key
						},
					);
				}
			}
		}

		/**
		 *  link - chat
		 * @used floating chat, shortcode, custom element. ht_ctc_chat_greetings_box_link click
		 */

		// Function to handle the click event for the chat link
		function ht_ctc_link ( values ) {

			// dispatch event for ctc.number
			document.dispatchEvent( new CustomEvent( 'ht_ctc_event_number', { detail: { ctc } } ) );

			var number = ctc.number;
			var pre_filled = ctc.pre_filled;

			// Check if the clicked element has a data-number attribute
			if (
				values.hasAttribute( 'data-number' ) &&
				'' !== values.getAttribute( 'data-number' )
			) {
				number = values.getAttribute( 'data-number' );
			}

			// Check if the clicked element has a data-pre_filled attribute
			if ( values.hasAttribute( 'data-pre_filled' ) ) {

				const dataPreFilled = values.getAttribute( 'data-pre_filled' ) || '';

				// prefix for pre_filled text might be added.
				// const prefix = ctc.prefix_pre_filled || '';
				const prefix = ( ctc.prefix_pre_filled ) ? ctc.prefix_pre_filled : '';

				// pre_filled = prefix ? `${prefix}${dataPreFilled}` : dataPreFilled;
				pre_filled = prefix + dataPreFilled;

			}

			/**
			 * safari 13.. before replaceAll not supports..
			 */
			try {
				pre_filled = pre_filled.replaceAll( '%', '%25' );

				var update_url = window.location.href;
				pre_filled = pre_filled.replace( /\[url]/gi, update_url );

				// pre_filled = encodeURIComponent(pre_filled);
				pre_filled = encodeURIComponent( decodeURI( pre_filled ) );
			} catch ( error ) {
				console.error( 'prefilled message encoding failed', error );
			}

			// if number is not defined or empty, display no number message.
			if (
				'' === number &&
				( ! ctc.custom_url_m || ctc.custom_url_m === '' ) &&
				( ! ctc.custom_url_d || ctc.custom_url_d === '' )
			) {
				if ( ctc.no_number ) {
					const noNumberEl = document.querySelector( '.ctc-no-number-message' );
					if ( noNumberEl ) {
						noNumberEl.style.display = 'block';
					}
				}
				return;
			}

			// navigations links..
			// 1.base_url
			var base_url = 'https://wa.me/' + number + '?text=' + pre_filled;

			// emoji works well with direct link as wa.me redirect its not working as expected.
			// var base_url = 'https://api.whatsapp.com/send?phone=' + number + '&text=' + pre_filled;

			// 2.url_target - _blank, _self or if popup type just add a name - here popup only
			var url_target = ctc.url_target_d ? ctc.url_target_d : '_blank';

			if ( is_mobile === 'yes' ) {

				// mobile
				if ( ctc.url_structure_m && 'wa_colon' === ctc.url_structure_m ) {

					// whatsapp://.. is selected.
					base_url = 'whatsapp://send?phone=' + number + '&text=' + pre_filled;

					// for whatsapp://.. url open target is _self.
					url_target = '_self';
				}

				// mobile: own url
				if ( ctc.custom_url_m && '' !== ctc.custom_url_m ) {
					base_url = ctc.custom_url_m;
				}
			} else {
				// desktop
				if ( ctc.url_structure_d && 'web' === ctc.url_structure_d ) {

					// web whatsapp is enabled/selected.
					base_url =
						'https://web.whatsapp.com/send' +
						'?phone=' +
						number +
						'&text=' +
						pre_filled;
				}

				// desktop: own url
				if ( ctc.custom_url_d && '' !== ctc.custom_url_d ) {
					base_url = ctc.custom_url_d;
				}
			}

			// 3.specs - specs - if popup then add 'pop_window_features' else 'noopener'
			var pop_window_features =
				'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,' +
				'width=788,height=514,left=100,top=100';
			var specs = 'popup' === url_target ? pop_window_features : 'noopener';

			window.open( base_url, url_target, specs );

			// Set the chat number based on the clicked element —
			// this is the number the user is about to chat with or was navigated to
			ctc.chat_number = number;

			// analytics
			ht_ctc_chat_analytics( values );

			// hook
			hook( number );

			stop_notification_badge();
		}

		// shortcode
		function shortcode () {
			document.addEventListener( 'click', function onShortcodeClick ( event ) {
				const target = event.target.closest( '.ht-ctc-sc-chat' );
				if ( target ) {
					ht_ctc_link( target ); // call your existing function
				}
			} );
		}

		/**
		 * Initializes custom link click handlers for the Click to Chat plugin.
		 *
		 * This function sets up event listeners for elements with the classes or IDs
		 * `.ctc_chat`, `#ctc_chat`, and `[href="#ctc_chat"]`. When these elements are clicked,
		 * the `ht_ctc_link` function is called to handle the chat link functionality.
		 *
		 * If the clicked element has the class `ctc_woo_place`, the default action is prevented.
		 */
		function custom_link () {

			// Event Delegation: handles clicks on elements that may exist now or be added later
			document.addEventListener( 'click', function handleEvent ( event ) {
				// Check if the clicked element (or its parent) matches `.ctc_chat` or `#ctc_chat`
				const el1 = event.target.closest( '.ctc_chat, #ctc_chat' );
				if ( el1 ) {

					// Trigger WhatsApp action
					ht_ctc_link( el1 );

					// Prevent default if it's a WooCommerce-specific placement
					if ( el1.classList.contains( 'ctc_woo_place' ) ) {
						event.preventDefault();
					}
				}

				// Check for anchor links like <a href="#ctc_chat">
				const el2 = event.target.closest( '[href="#ctc_chat"]' );
				if ( el2 ) {
					// Prevent browser jumping to #ctc_chat
					event.preventDefault();

					// Trigger WhatsApp action
					ht_ctc_link( el2 );
				}
			} );

		}

		// hook related values..
		var g_hook_v = ctc.hook_v ? ctc.hook_v : '';

		// webhooks
		function hook ( number ) {

			var h_url = ctc && ctc.hook_url;

			if ( ! h_url ) {
				return;
			}

			// Reset ctc.hook_v to the pair-value object derived from the original array
			// This ensures a fresh start for each click (avoiding processed values carrying over incorrectly)
			if ( Array.isArray( g_hook_v ) ) {

				const pair_values = {};

				g_hook_v.forEach( ( val, index ) => {
					// pair_values[ 'value' + (index + 1) ] = val;
					setSafeProperty( pair_values, 'value' + ( index + 1 ), val );
				} );

				// Update ctc.hook_v so it's available in the event
				ctc.hook_v = pair_values;
			}

			document.dispatchEvent( new CustomEvent(
				'ht_ctc_event_hook',
				{ detail: { ctc, number } },
			) );

			// Use the values from ctc (which may have been modified by early/using eventlistners)
			// var hook_values = ctc.hook_v || {};
			var hook_values = ( ctc.hook_v && typeof ctc.hook_v === 'object' ) ? ctc.hook_v : {};

			// Update URL might be modified by eventlistners
			if ( ctc.hook_url ) {
				h_url = ctc.hook_url;
			}

			// Format data for webhook
			let data;
			const contentType = 'application/x-www-form-urlencoded;charset=UTF-8';

			// To solve CORS error: Use form-urlencoded even for 'json' format
			// This makes it a "simple request" and avoids the preflight check.
			// json, else block produces same output will remove else. and if json checking.
			if ( 'json' === ctc.webhook_format ) {

				// Convert to search params
				var params = new URLSearchParams();
				Object.keys( hook_values )
					.forEach( ( key ) => {
						// params.append( key, hook_values[ key ] );
						// hook_values[ key ]
						const hookVal = getSafeProperty( hook_values, key );

						// Convert objects/arrays to string if needed
						params.append( key, ( typeof hookVal === 'object' ) ? JSON.stringify( hookVal ) : hookVal );
					} );

				// data = params;
				data = params.toString();
			} else {
				// Default fallback

				// data = JSON.stringify(hook_values);
				data = new URLSearchParams( hook_values )
					.toString();
			}

			// ---- Replacing jQuery AJAX with fetch() ----
			fetch( h_url, {
				method: 'POST',

				// mode: 'no-cors',
				headers: {
					'Content-Type': contentType,
				},
				body: data,
			} )
				.catch( error => {
					console.error( 'Error:', error );
				} );
		}

	}

	function onReady () {
		if ( document.readyState !== 'loading' ) {
			initClickToChat(); // DOM already ready
		} else {
			document.addEventListener( 'DOMContentLoaded', initClickToChat );
		}
	}

	onReady();

} )( window, document, htCtcJq );
