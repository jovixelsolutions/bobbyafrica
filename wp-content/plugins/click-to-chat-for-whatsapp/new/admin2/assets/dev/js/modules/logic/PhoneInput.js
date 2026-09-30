/**
 * International Phone Input Logic.
 *
 * Handles international phone input initialization, dynamic loading of the vendored
 * intl-tel-input ES module, country lookup, hidden input synchronization, and translations.
 *
 * The library is dynamically imported as an ES module using the URL localized by PHP.
 * The instance is stored directly on the input element (`_ctcIti`) for modular access.
 */

import { getCtcStorageItem, setCtcStorageItem } from '../core/Storage.js';
import { log, importWithRetry, retryUrl } from '../core/Utils.js';

/**
 * Resolve the vendored library, once per page.
 *
 * @returns {Promise<Object|null>} the intlTelInput constructor, or null.
 */
let libPromise = null;

const loadLibrary = () => {
	if ( libPromise ) {
		return libPromise;
	}

	const paths = ( window.ht_ctc_admin_var && window.ht_ctc_admin_var.paths ) || {};

	if ( ! paths.phoneInput || ! paths.phoneInput.intlTelInput ) {
		log( 'PhoneInput', 'intlTelInput path not provided' );
		return Promise.resolve( null );
	}

	libPromise = importWithRetry( ( attempt ) =>
		// eslint-disable-next-line no-unsanitized/method -- Path is from trusted plugin configuration localized by PHP
		import( /* webpackIgnore: true */ retryUrl( paths.phoneInput.intlTelInput, attempt ) ) )
		.then( ( module ) => module.default || null )
		.catch( ( error ) => {
			log( 'PhoneInput', 'failed to load intl-tel-input', error );
			libPromise = null; // allow a later retry
			return null;
		} );

	return libPromise;
};

/**
 * Build library `uiTranslations` using strings localized by PHP.
 * Uses Intl.PluralRules for client-side plural selection on `searchSummaryAria`.
 *
 * @param {Object} source Strings from PHP.
 * @param {string} locale Language tag driving plural selection.
 * @returns {Object} uiTranslations for the library.
 */
const buildUiTranslations = ( source, locale ) => {
	if ( ! source || 'object' !== typeof source ) {
		return {};
	}

	const ui = { ...source };
	const aria = ui.searchSummaryAria;

	if ( ! aria || 'object' !== typeof aria ) {
		return ui;
	}

	const exact = aria.exact || {};
	const plural = aria.plural || {};
	let rules = null;

	try {
		rules = new Intl.PluralRules( locale );
	} catch {
		rules = null;
	}

	ui.searchSummaryAria = ( count ) => {
		/* eslint-disable security/detect-object-injection -- `count` is a number from the library; keys are our own generated data. */
		const template = ( count <= 1 && undefined !== exact[ count ] ) ?
			exact[ count ] :
			plural[ rules ? rules.select( count ) : 'other' ] || plural.other;
		/* eslint-enable security/detect-object-injection */

		return undefined === template ?
			String( count ) :
			template.split( '%d' )
				.join( String( count ) );
	};

	return ui;
};

/**
 * Initialize the phone input(s) within a scope.
 *
 * Name is referenced by PHP as modulesPath.phoneInput.method — keep the two in
 * sync when renaming.
 *
 * @param {string}      className Visible input class to initialize.
 * @param {Document|Element} context Scope to search within.
 * @param {Object}      app       App instance (event bus).
 * @returns {Promise<void>}
 */
export const initPhoneInput = async ( className = 'intl_number', context = document, app = null ) => {
	const currentApp = app || window.HTCtcAdminApp;

	try {
		if ( ! context || typeof context.querySelector !== 'function' ) {
			return;
		}

		if ( ! context.querySelector( '.' + className ) ) {
			return;
		}

		const intlTelInput = await loadLibrary();

		if ( ! intlTelInput ) {
			return;
		}

		const adminVar = window.ht_ctc_admin_var || {};
		const phoneInput = adminVar.paths && adminVar.paths.phoneInput;

		const localeTag = ( phoneInput && phoneInput.locale ) || 'en';
		const uiTranslations = buildUiTranslations(
			( phoneInput && phoneInput.uiStrings ) || null,
			localeTag,
		);

		context.querySelectorAll( '.' + className )
			.forEach( ( element ) => {
				intl_init( element, intlTelInput, uiTranslations );
			} );

		intl_onchange( context, currentApp );
	} catch ( error ) {
		log( 'PhoneInput', 'initPhoneInput error:', error );
	}
};

// Helper to initialize a single element
const intl_init = ( element, intlTelInput, uiTranslations = null ) => {
	// Hoisted so catch block can restore form attributes if initialization fails.
	let attr_value = '';
	let hidden_input_name = '';
	let hiddenInput = null;

	try {
		if ( ! element || ! ( element instanceof Element ) ) { return null; }

		// Prevent Double Initialization
		if ( element.classList.contains( 'iti-loaded' ) ) {
			try {
				const existingInstance = intlTelInput.getInstance( element );
				if ( existingInstance ) {
					return existingInstance;
				}

				// If no instance but has class, remove class to allow re-init
				element.classList.remove( 'iti-loaded' );
			} catch {
				element.classList.remove( 'iti-loaded' );
			}
		}

		element.classList.add( 'iti-loaded' );

		// 1. Get current value, normalized to a '+' prefix.
		attr_value = ( element.hasAttribute( 'value' ) ? element.getAttribute( 'value' ) : element.value ) || '';

		if ( attr_value ) {
			attr_value = attr_value.startsWith( '+' ) ? attr_value : `+${ attr_value }`;

			// Clear value before initialization to avoid regionless NANP constructor exceptions.
			element.value = '';
			element.removeAttribute( 'value' );
		}

		// 2. Identify Hidden Input (Actual data storage)
		const dataName = element.getAttribute( 'data-name' );
		hidden_input_name = dataName || 'ht_ctc_chat_options[number]';

		// 3. Configuration
		const adminVar = window.ht_ctc_admin_var || {};
		const phoneInputPaths = ( adminVar.paths && adminVar.paths.phoneInput ) || {};
		const utilsUrl = phoneInputPaths.intlTelInputUtils || '';

		const values = {
			countrySelectorMode: prefersFullscreenSelector() ? 'FULLSCREEN' : 'DROPDOWN',
			dropdownParent: document.body,

			initialCountry: '',
			initialCountryLookup: countryLookup,

			// Match dropdown width to input field width.
			matchDropdownWidth: true,

			// Recently used countries, most recent first.
			countryOrder: getPreferredCountries(),

			numberDisplayFormat: 'INTERNATIONAL',
			separateDialCode: true,

			// Don't block keystrokes or cap length — accept what is typed.
			strictMode: false,

			containerClass: 'ctc_intl_container',

			// Country names in admin language via browser Intl.DisplayNames.
			countryNameLocale: phoneInputPaths.locale || 'en',

			// Managed hidden input handles saved values.
			hiddenInputs: null,

			// Deferred loading of utils.js (libphonenumber) via requestIdleCallback.
			loadUtils: utilsUrl ?
				() => new Promise( ( resolve, reject ) => {
					const load = () => {
						importWithRetry( ( attempt ) =>
							// eslint-disable-next-line no-unsanitized/method -- Path is from trusted plugin configuration localized by PHP
							import( /* webpackIgnore: true */ retryUrl( utilsUrl, attempt ) ) )
							.then( resolve )
							.catch( reject );
					};

					if ( 'requestIdleCallback' in window ) {
						window.requestIdleCallback( load, { timeout: 1500 } );
						return;
					}

					setTimeout( load, 1500 );
				} ) :
				null,
		};

		if ( uiTranslations ) {
			values.uiTranslations = uiTranslations;
		}

		const intl = intlTelInput( element, values );

		element._ctcIti = intl;

		keepDropdownWidthInSync( element, intl );

		// Initialize hidden input with the stored value prior to setNumber().
		hiddenInput = createHiddenInput( element, hidden_input_name );

		if ( hiddenInput ) {
			if ( attr_value ) {
				hiddenInput.value = attr_value;
			}

			element.removeAttribute( 'name' );

			// Visible input is UI-only; dirty tracking is handled via the hidden input.
			element.dataset.ctcNoTrack = 'true';
		}

		// Seed saved number via setNumber() to safely resolve regionless NANP numbers (e.g. +1 800).
		if ( attr_value ) {
			element.setAttribute( 'value', attr_value );
			intl.setNumber( attr_value );
		}

		if ( hiddenInput && ! attr_value ) {
			const seed = intlBestEffortNumber( intl, element );

			if ( seed ) {
				hiddenInput.value = seed;
			}
		}

		// Re-apply on settlement regardless of whether geo-IP lookup resolves or rejects.
		intl.promise
			.catch( ( err ) => {
				log( 'PhoneInput', 'intl.promise rejected (geo-IP lookup and/or utils failed)', err );
			} )
			.then( () => {
				// Re-seed stored number if the field has not been touched by the user.
				const isIdle = ! element.dataset.userInteracted &&
					document.activeElement !== element;

				if ( attr_value && isIdle ) {
					intl.setNumber( attr_value );
				}

				if ( hiddenInput && ( ! isIdle || ! attr_value ) ) {
					const value = intlBestEffortNumber( intl, element );

					if ( value ) {
						hiddenInput.value = value;
					}
				}
			} )
			.catch( ( err ) => {
				log( 'PhoneInput', 'Error re-applying saved number', err );
			} );

		return intl;

	} catch ( error ) {
		log( 'PhoneInput', 'intl_init global error', error );

		// Restore name and value on the visible input if initialization failed before hidden input creation.
		if ( ! hiddenInput && hidden_input_name ) {
			element.setAttribute( 'name', hidden_input_name );

			if ( attr_value ) {
				element.value = attr_value;
				element.setAttribute( 'value', attr_value );
			}
		}

		return null;
	}
};

/**
 * Determine if mobile/narrow viewport prefers fullscreen country selector.
 *
 * @returns {boolean}
 */
const prefersFullscreenSelector = () => {
	try {
		if ( typeof window === 'undefined' || typeof window.matchMedia !== 'function' ) {
			return false;
		}

		return window.matchMedia( '(max-width: 500px)' ).matches ||
			window.matchMedia( '(pointer: coarse)' ).matches ||
			window.matchMedia( '(max-height: 600px)' ).matches;
	} catch {
		return false;
	}
};

/**
 * Keep dropdown width and height in sync with the input container on open.
 *
 * @param {Element} element Visible input.
 * @returns {void}
 */
const keepDropdownWidthInSync = ( element ) => {
	try {
		const wrapper = element.closest( '.iti' );

		if ( ! wrapper ) { return; }

		element.addEventListener( 'open:countryselector', () => {
			try {
				const button = wrapper.querySelector( '.iti__selected-country' );
				const panelId = button && button.getAttribute( 'aria-controls' );
				const panel = panelId ? document.getElementById( panelId ) : null;

				if ( ! panel || panel.closest( '.iti--fullscreen-popup' ) ) {
					return;
				}

				const width = wrapper.offsetWidth;

				if ( width > 0 ) {
					panel.style.width = `${width}px`;
				}

				panel.style.height = '';
				const height = panel.offsetHeight;

				if ( height > 0 ) {
					panel.style.height = `${height}px`;
				}
			} catch ( error ) {
				log( 'PhoneInput', 'dropdown width sync failed', error );
			}
		} );
	} catch ( error ) {
		log( 'PhoneInput', 'keepDropdownWidthInSync error', error );
	}
};

/**
 * Create (or reuse) the hidden input that carries the real value.
 *
 * Checks both the .ctc_intl_container wrapper and parent node for existing hidden inputs,
 * ensuring the target form name attribute is synchronized if an existing input is reused.
 *
 * @param {Element} element          Visible input.
 * @param {string}  hidden_input_name Form name for the hidden input.
 * @returns {Element|null}
 */
const createHiddenInput = ( element, hidden_input_name ) => {
	try {
		const container = element.closest( '.ctc_intl_container' ) || element.parentNode;

		if ( ! container ) { return null; }

		const existing = container.querySelector( 'input.intl_number_hidden' ) ||
			( element.parentNode && element.parentNode.querySelector( 'input.intl_number_hidden' ) );

		if ( existing ) {
			if ( hidden_input_name ) {
				existing.name = hidden_input_name;
			}
			return existing;
		}

		const hiddenInput = document.createElement( 'input' );
		hiddenInput.type = 'hidden';
		if ( hidden_input_name ) {
			hiddenInput.name = hidden_input_name;
		}
		hiddenInput.classList.add( 'intl_number_hidden' );

		container.appendChild( hiddenInput );

		return hiddenInput;
	} catch ( error ) {
		log( 'PhoneInput', 'createHiddenInput error', error );
		return null;
	}
};

/**
 * Reconstruct a best-effort E.164 phone number from input and country dial code.
 * Falls back safely if the utils module is still loading.
 *
 * @param {Object}  intl    intlTelInput instance.
 * @param {Element} element Visible input.
 * @returns {string}
 */
const intlBestEffortNumber = ( intl, element ) => {
	try {
		const formatted = intl.getNumber();

		if ( formatted ) {
			return formatted;
		}
	} catch {
		// utils not ready — fall through to raw reconstruction.
	}

	const raw = element && element.value ?
		String( element.value )
			.trim() :
		'';

	if ( '' === raw ) {
		return '';
	}

	if ( '+' === raw.charAt( 0 ) ) {
		return raw;
	}

	try {
		const country = intl.getSelectedCountry();

		if ( country && country.dialCode ) {
			const digits = raw.replace( /\D/g, '' )
				.replace( /^0/, '' );

			return `+${ country.dialCode }${ digits }`;
		}
	} catch {
		// no country data — return raw digits rather than nothing.
	}

	return raw;
};

/**
 * Resolve the hidden input paired with a visible intl field.
 *
 * @param {Element} element Visible input.
 * @returns {Element|null}
 */
const getHiddenInput = ( element ) => {
	const container = element.closest( '.ctc_intl_container' ) || element.parentNode;

	return container ? container.querySelector( 'input.intl_number_hidden' ) : null;
};

const intl_onchange = ( context = document, currentApp ) => {
	try {
		if ( ! context || typeof context.querySelectorAll !== 'function' ) {
			return;
		}

		const intlInputs = context.querySelectorAll( '.intl_number' );

		intlInputs.forEach( ( input ) => {

			// Guard against binding twice if the section is re-rendered.
			if ( input.dataset.ctcIntlBound === 'true' ) {
				return;
			}
			input.dataset.ctcIntlBound = 'true';

			// Mark as user-interacted on first real interaction
			[ 'focus', 'click', 'keydown' ].forEach( ( evt ) => {
				input.addEventListener( evt, function markInteracted () {
					this.dataset.userInteracted = 'true';
				}, { once: true } );
			} );

			[ 'input', 'countrychange' ].forEach( ( evtName ) => {
				input.addEventListener( evtName, function handleIntlChange () {
					try {
						const changed = this._ctcIti;

						if ( ! changed ) {
							return;
						}

						const hiddenInput = getHiddenInput( this );

						if ( hiddenInput ) {
							// Retain stored string while untouched to preserve valid national trunk zeros (e.g. Italy).
							const stored = this.getAttribute( 'value' ) || '';
							const isIdle = ! this.dataset.userInteracted &&
								document.activeElement !== this;

							hiddenInput.value = ( isIdle && stored ) ?
								stored :
								intlBestEffortNumber( changed, this );

							if ( this.dataset.userInteracted ) {
								hiddenInput.dataset.changed = 'true';

								// Use Event Bus instead of global window leakage
								currentApp?.events?.emit( 'field:dirty', hiddenInput );
							}
						}
					} catch {
						// Silently skip if something goes wrong in the event handler
					}
				} );
			} );

			// Track country changes separately
			input.addEventListener( 'countrychange', function handleCountryChange () {
				try {
					const changed = this._ctcIti;

					if ( changed ) {
						const countryData = changed.getSelectedCountry();

						if ( countryData && countryData.iso2 ) {
							add_prefer_countrys( countryData.iso2 );
						}
					}
				} catch {
					// ignore
				}
			} );
		} );
	} catch ( error ) {
		log( 'PhoneInput', 'intl_onchange error', error );
	}
};

/**
 * Detect user country via ipinfo API with daily local storage caching.
 *
 * @returns {Promise<string>} lowercase ISO2 country code.
 */
const countryLookup = () => {
	const country_code_date = new Date()
		.toDateString();

	try {
		const storedDate = getCtcStorageItem( 'country_code_date' );
		const stored = getCtcStorageItem( 'country_code' );

		if ( storedDate === country_code_date && stored ) {
			return Promise.resolve( String( stored )
				.toLowerCase() );
		}
	} catch {
		// fall through to the network lookup
	}

	const controller = new AbortController();
	const timeoutId = setTimeout( () => controller.abort(), 2000 );

	return fetch( 'https://ipinfo.io/json', {
		signal: controller.signal,
		mode: 'cors',
		credentials: 'omit',
	} )
		.then( ( response ) => {
			if ( ! response.ok ) {
				return Promise.reject( new Error( 'HTTP error' ) );
			}
			return response.json();
		} )
		.then( ( resp ) => {
			// Validate country code format (2 letter ISO code)
			const code = ( resp && resp.country && /^[A-Z]{2}$/i.test( resp.country ) ) ? resp.country : 'us';

			try {
				setCtcStorageItem( 'country_code', code );
				setCtcStorageItem( 'country_code_date', country_code_date );
				add_prefer_countrys( code );
			} catch {
				// ignore storage error
			}

			return code.toLowerCase();
		} )
		.catch( ( err ) => {
			// Default to US on country detection error.
			console.warn( '[ht_ctc] PhoneInput: country detection failed, defaulting to US', err );
			return 'us';
		} )
		.finally( () => {
			clearTimeout( timeoutId );
		} );
};

/**
 * Recently selected countries, newest first.
 *
 * @returns {Array} validated ISO2 codes.
 */
const getPreferredCountries = () => {
	try {
		const stored = getCtcStorageItem( 'pre_countries' );

		if ( ! Array.isArray( stored ) ) { return []; }

		return stored.filter( ( code ) => typeof code === 'string' && /^[A-Z]{2}$/i.test( code ) );
	} catch {
		return [];
	}
};

const add_prefer_countrys = ( country_code ) => {
	try {
		// Validate and sanitize country code
		if ( ! country_code || typeof country_code !== 'string' || ! /^[A-Z]{2}$/i.test( country_code ) ) {
			country_code = 'US';
		} else {
			country_code = country_code.toUpperCase();
		}

		let pre_countries = getPreferredCountries();

		pre_countries = pre_countries.filter( ( code ) => code.toUpperCase() !== country_code );
		pre_countries.unshift( country_code );

		if ( pre_countries.length > 3 ) {
			pre_countries = pre_countries.slice( 0, 3 );
		}

		setCtcStorageItem( 'pre_countries', pre_countries );
	} catch {
		// ignore storage error
	}
};
