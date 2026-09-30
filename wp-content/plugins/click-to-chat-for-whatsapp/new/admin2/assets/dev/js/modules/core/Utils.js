/**
 * Core Utilities
 */

/* global __DEV__ */
let _debugFilter = '*';

// Initialize debug filter in dev mode
if ( typeof __DEV__ !== 'undefined' && __DEV__ ) {
	_debugFilter = sessionStorage.getItem( 'ctc_debug' ) || '*';

	// Expose filter control to browser console: ctcDebug('App') or ctcDebug('*')
	window.ctcDebug = ( filter ) => {
		_debugFilter = ( typeof filter === 'string' ) ? filter : '*';
		sessionStorage.setItem( 'ctc_debug', _debugFilter );
		console.log( `CtC: Debug filter set to: ${_debugFilter || '(muted)'}` );
	};
}

/**
 * Development debug logger with module filtering.
 *
 * @param {string} module - Module name for filtering (e.g. 'App', 'Settings').
 * @param {...*} args - Values to log.
 */
export const log = ( typeof __DEV__ !== 'undefined' && __DEV__ ) ?
	( module, ...args ) => {
		const filter = _debugFilter || '*';
		if ( filter === '*' || filter.split( ',' )
			.includes( module ) ) {
			console.log( `CtC: [${module}]`, ...args );
		}
	} :
	() => { /* noop */ };

const unsafeKeys = [
	'__proto__',
	'prototype',
	'constructor',
	'__defineGetter__',
	'__defineSetter__',
	'__lookupGetter__',
	'__lookupSetter__',
];

/**
 * Validates whether a key is safe against prototype pollution.
 *
 * @param {string} key - Object key to test.
 * @returns {boolean} True if the key is safe.
 */
export const isSafeObjectKey = ( key ) => {
	return (
		typeof key === 'string' &&
		key.length > 0 &&
		/^[a-zA-Z0-9_-]+$/.test( key ) &&
		! unsafeKeys.includes( key )
	);
};

/**
 * Safely accesses an object's own property, guarded against prototype pollution.
 *
 * @param {Object} obj - Target object.
 * @param {string} key - Property key.
 * @param {*} [fallback=undefined] - Fallback value if property is absent or invalid.
 * @returns {*} Property value or fallback.
 */
export const getSafeProperty = ( obj, key, fallback = undefined ) => {
	if (
		obj &&
		typeof obj === 'object' &&
		isSafeObjectKey( key ) &&
		Object.prototype.hasOwnProperty.call( obj, key )
	) {
		// eslint-disable-next-line security/detect-object-injection -- Key is validated by isSafeObjectKey above to prevent prototype pollution
		return obj[ key ];
	}
	return fallback;
};

/**
 * Escapes characters that can trigger XSS.
 *
 * @param {string} str - The string to escape.
 * @returns {string} The escaped string.
 */
const _escapeDiv = typeof document !== 'undefined' ? document.createElement( 'div' ) : null;

export const escapeHTML = ( str ) => {
	if ( typeof str !== 'string' ) {
		return str;
	}

	// Prefer WP's built-in if available (wp-includes/js/dist/escape-html.min.js)
	if ( window.wp?.escapeHtml?.escapeHTML ) {
		return window.wp.escapeHtml.escapeHTML( str );
	}

	// Use DOM when available
	if ( _escapeDiv ) {
		_escapeDiv.textContent = str;
		return _escapeDiv.innerHTML;
	}

	return '';
};

/**
 * Checks whether the Clipboard API is usable in the current context.
 *
 * @returns {boolean} True if navigator.clipboard.writeText is available.
 */
const canCopyToClipboard = () => !! ( navigator.clipboard && navigator.clipboard.writeText );

/**
 * Copies text to the clipboard with fallback for non-secure contexts.
 *
 * @param {string} text - Text to copy.
 * @returns {Promise<void>} Resolves on success, rejects on failure.
 */
export const copyToClipboard = ( text ) => {
	if ( canCopyToClipboard() ) {
		return navigator.clipboard.writeText( text );
	}

	// Non-secure context fallback.
	const ta = document.createElement( 'textarea' );
	ta.value = text;
	ta.style.position = 'fixed';
	ta.style.opacity = '0';
	document.body.appendChild( ta );
	ta.select();
	try {
		document.execCommand( 'copy' );
		return Promise.resolve();
	} catch ( err ) {
		return Promise.reject( err );
	} finally {
		ta.remove();
	}
};

/**
 * Returns a sanitized URL safe for href and src attributes.
 *
 * Restricts to http(s), mailto, tel, anchors (#), and relative paths.
 *
 * @param {*} url - Candidate URL.
 * @returns {string} Safe URL or '#' if invalid.
 */
export const safeUrl = ( url ) => {
	if ( typeof url !== 'string' ) { return '#'; }
	const trimmed = url.trim();
	if ( ! trimmed ) { return '#'; }
	if ( /^(https?:|mailto:|tel:|#|\/)/i.test( trimmed ) ) { return trimmed; }
	return '#';
};

/**
 * Escapes characters for use within HTML attributes.
 *
 * @param {string} str - The string to escape.
 * @returns {string} The escaped string.
 */
export const escapeAttr = ( str ) => {
	if ( typeof str !== 'string' ) {
		return str;
	}

	return str.replace( /[&<>"']/g, ( char ) => {
		switch ( char ) {
			case '&': return '&amp;';
			case '<': return '&lt;';
			case '>': return '&gt;';
			case '"': return '&quot;';
			case "'": return '&#39;';
			default: return char;
		}
	} );
};

/**
 * Decodes HTML entities into plain text.
 *
 * Note: Output should be assigned to .value or .textContent, not .innerHTML.
 *
 * @param {string} str - String containing HTML entities to decode.
 * @returns {string} Decoded plain text.
 */
const _domParser = typeof DOMParser !== 'undefined' ? new DOMParser() : null;
const _decodeTextArea = typeof document !== 'undefined' ? document.createElement( 'textarea' ) : null;

export const decodeHTML = ( str ) => {
	if ( typeof str !== 'string' ) {
		return str;
	}

	// Prefer WP's built-in if available (wp-includes/js/dist/html-entities.min.js)
	if ( window.wp?.htmlEntities?.decodeEntities ) {
		return window.wp.htmlEntities.decodeEntities( str );
	}

	// Escape only `</textarea` to prevent breaking out of the DOMParser wrapper.
	const safeStr = str.replace( /<\/\s*textarea\s*>/gi, '&lt;/textarea&gt;' );

	if ( _domParser ) {
		const doc = _domParser.parseFromString( `<textarea>${safeStr}</textarea>`, 'text/html' );
		return doc.querySelector( 'textarea' ).value;
	}

	if ( _decodeTextArea ) {
		// eslint-disable-next-line no-unsanitized/property -- Safe: safeStr strips breakouts, textarea ignores scripts natively.
		_decodeTextArea.innerHTML = safeStr;
		return _decodeTextArea.value;
	}

	return str;
};

/**
 * Minimal wpautop: wraps double-newline separated chunks in <p>.
 *
 * @param {string} str - The string to format.
 * @returns {string} The formatted string.
 */
export const autop = ( str ) => {
	if ( typeof str !== 'string' ) {
		return str;
	}
	const trimmed = str.trim();
	if ( trimmed === '' ) {
		return '';
	}

	// Prefer WP's built-in if available (wp-includes/js/dist/autop.min.js)
	if ( window.wp?.autop?.autop ) {
		return window.wp.autop.autop( trimmed );
	}

	if ( trimmed.startsWith( '<' ) ) {
		return trimmed;
	}

	return trimmed
		.split( /\n\s*\n/ )
		.map( ( part ) => `<p>${part.replace( /\n/g, '<br>' )}</p>` )
		.join( '' );
};

/**
 * Safely sets an object property, guarded against prototype pollution.
 *
 * @param {Object} obj - Target object.
 * @param {string} key - Property key.
 * @param {*} value - Value to assign.
 * @returns {boolean} True if successfully assigned.
 */
export const setSafeProperty = ( obj, key, value ) => {
	if (
		obj &&
		typeof obj === 'object' &&
		isSafeObjectKey( key ) &&
		(
			Object.prototype.hasOwnProperty.call( obj, key ) ||
			! ( key in obj )
		)
	) {
		// eslint-disable-next-line security/detect-object-injection -- Key is validated by isSafeObjectKey above
		obj[ key ] = value;
		return true;
	}
	return false;
};

/**
 * Debounce a function to optimize rapid firing events.
 *
 * @param {Function} func - The function to debounce.
 * @param {number} [wait=100] - Delay in milliseconds.
 * @returns {Function} Debounced function.
 */
export const debounce = ( func, wait = 100 ) => {
	let timeout;
	return function debouncedFunction ( ...args ) {
		clearTimeout( timeout );
		timeout = setTimeout( () => func.apply( this, args ), wait );
	};
};

/**
 * Retrieves a nested property value from an object using an option group and bracketed field ID path.
 *
 * @param {Object} obj - Settings object.
 * @param {string} optionGroup - Option group name (e.g. 'ht_ctc_chat_options').
 * @param {string} fieldId - Field identifier or sub-path (e.g. 'number' or 'style][mobile').
 * @returns {*} Value of the field, or empty string if not found.
 */
export const getNestedValue = ( obj, optionGroup, fieldId ) => {
	if ( ! obj || ! optionGroup ) { return ''; }

	// Combine optionGroup and fieldId into a full path
	// e.g. group="ht_ctc_chat_options", id="channels][whatsapp][val" -> "ht_ctc_chat_options[channels][whatsapp][val]"
	// Or if fieldId is simple "number" -> "ht_ctc_chat_options[number]"
	const fullPath = `${optionGroup}[${fieldId}]`;

	const keys = fullPath.split( /\[|\]/ )
		.filter( key => key );

	let current = obj;
	for ( const key of keys ) {
		// current = current[ key ];
		current = getSafeProperty( current, key );
		if ( ! current ) { return ''; }
	}

	return ( current !== undefined ) ? current : '';
};

/**
 * Set a nested value in an object based on an array of keys.
 *
 * @param {Object} obj - The object to modify.
 * @param {Array<string>} keys - Array of keys representing the path to the nested property.
 * @param {*} value - The value to set at the nested property.
 */
export const setNestedValue = ( obj, keys, value ) => {
	let current = obj;
	keys.forEach( ( key, index ) => {
		if ( index === keys.length - 1 ) {
			// current[ key ] = value;
			setSafeProperty( current, key, value );
		} else {
			// if ( ! current[ key ] ) { current[ key ] = {}; }
			if ( ! getSafeProperty( current, key ) ) {
				setSafeProperty( current, key, {} );
			}

			// current = current[ key ];
			current = getSafeProperty( current, key );
		}
	} );
};

/**
 * Replaces `{placeholder}` tokens in a content string.
 *
 * Resolves placeholders from custom variables or global runtime configuration.
 *
 * @param {string} content - Template string containing `{key}` placeholders.
 * @param {Object|boolean} [variables=true] - Variable map or boolean indicating whether to resolve from runtime config.
 * @returns {string} String with placeholders replaced.
 */
const runtime = {
	...( window.ht_ctc_admin_var || {} ),
	...( window.ht_ctc_admin_var?.initialSettings?.ht_ctc_chat_options || {} ),
};

export const applyVariables = ( content, variables = true ) => {
	if ( ! content || typeof content !== 'string' ) {
		return content;
	}

	let result = content;

	// 1. Process custom variables if provided as an object
	if ( variables && typeof variables === 'object' && ! Array.isArray( variables ) ) {
		Object.entries( variables )
			.forEach( ( [ key, value ] ) => {
				const placeholder = `{${key}}`;
				result = result.split( placeholder )
					.join( value );
			} );
	}

	// 2. Resolve global runtime variables
	if ( variables ) {
		result = result.replace( /\{(\w+)\}/g, ( match, key ) => {
			// const val = runtime[ key ];
			const val = getSafeProperty( runtime, key );
			log( 'Utils', 'applyVariables()', '\n', `match: ${match}, key: ${key}, ${val ? `${match} replaced with ${val}` : `${match} not found in runTime to replace`}` );
			return val !== undefined ? val : match;
		} );
	}

	return result;
};

/**
 * Translates a network or REST error into a user-friendly message.
 *
 * @param {Error|string} error - The caught error or error message.
 * @returns {string} User-facing message.
 */
export const friendlyErrorMessage = ( error ) => {
	const message = ( error && error.message ) ? error.message : String( error || '' );

	if ( ! message ) { return 'An unknown error occurred.'; }

	// Unreachable server / offline
	if ( message.includes( 'Failed to fetch' ) || message.includes( 'NetworkError' ) ) {
		return 'Unable to connect to the server. Please check your internet connection or if the site is reachable.';
	}

	// Request timeout
	if ( message.includes( 'timed out' ) ) {
		return 'The server took too long to respond. Please try again in a moment.';
	}

	// Expired REST nonce
	if ( message.includes( 'rest_cookie_invalid_nonce' ) || message.includes( 'rest_nonce' ) ) {
		return 'Your session has expired. Please reload this page and save again (reloading will discard unsaved changes on this screen).';
	}

	// Server firewall / WAF block before WordPress
	if ( ( message.includes( '[403]' ) || message.includes( '[401]' ) ) && message.includes( 'Non-JSON error response' ) ) {
		return 'The server blocked this request before it reached WordPress — usually a hosting firewall or security module (ModSecurity/WAF), often triggered by URLs or code in the settings. Please contact your hosting support with the details below.';
	}

	// Permission or nonce denial
	if ( message.includes( '[403]' ) || message.includes( '[401]' ) ) {
		return 'Security verification failed. Please reload the page and try again. If it keeps happening, a security plugin or user-role restriction may be blocking the request.';
	}

	// Invalid JSON response (e.g. PHP error or output conflict)
	if ( message.includes( 'Unexpected token' ) || message.includes( 'Invalid or empty JSON' ) ) {
		return 'The server returned an invalid response. This is often caused by a PHP error or conflict with another plugin.';
	}

	return message;
};

/**
 * Wrapper to safely execute a function and catch errors without crashing the app.
 *
 * @param {Function} fn - Function to execute.
 * @param {string} [context='Feature'] - Context name for error reporting.
 * @returns {boolean} True if executed successfully, false if an error was caught.
 */
export const safeRun = ( fn, context = 'Feature' ) => {
	try {
		fn();
		return true;
	} catch ( error ) {
		console.error( `CTC: safeRun: ${context} failed:`, error );

		// Dispatch error event so UI can show a specific warning icon if needed
		document.dispatchEvent( new CustomEvent( 'ht_ctc_error', { detail: { context, error } } ) );
		return false;
	}
};

/**
 * Applies data-* attributes from a configuration map to an element.
 *
 * @param {HTMLElement} element - Target DOM element.
 * @param {Object} attributes - Map of attribute names to values.
 */
export const applyDataAttributes = ( element, attributes ) => {
	if ( ! element || ! attributes || typeof attributes !== 'object' ) {
		return;
	}

	for ( const [ name, value ] of Object.entries( attributes ) ) {
		if ( ! /^data-[a-z0-9-]+$/.test( name ) ) {
			console.warn( `CTC: applyDataAttributes: ignored non data-* attribute "${name}"` );
			continue;
		}

		if ( value === null || value === undefined || value === false ) {
			continue;
		}

		element.setAttribute( name, String( value ) );
	}
};

/**
 * Safely queries a selector, catching syntax errors for unparseable selectors.
 *
 * @param {Element|Document} root - Search root.
 * @param {string} selector - CSS selector string.
 * @param {string} [source='selector'] - Origin identifier for warnings.
 * @returns {Element|null} Matching element or null.
 */
export const safeQuery = ( root, selector, source = 'selector' ) => {
	try {
		return root.querySelector( selector );
	} catch {
		console.warn( `CTC: invalid ${source} "${selector}" — ignored` );
		return null;
	}
};

/**
 * Safely tests Element.matches, catching syntax errors for unparseable selectors.
 *
 * @param {Element} element - Target element.
 * @param {string} selector - CSS selector string.
 * @param {string} [source='selector'] - Origin identifier for warnings.
 * @returns {boolean} True if element matches selector.
 */
export const safeMatches = ( element, selector, source = 'selector' ) => {
	try {
		return element.matches( selector );
	} catch {
		console.warn( `CTC: invalid ${source} "${selector}" — ignored` );
		return false;
	}
};

/**
 * Applies conditional display attributes to a DOM element based on field configuration.
 *
 * @param {HTMLElement} element - Target DOM element.
 * @param {Object} field - Field configuration object.
 */
export const applyConditionalAttributes = ( element, field ) => {
	if ( ! element || ! field ) {
		return;
	}

	// data-watch attributes (handled by Conditions.js)
	if ( field.data_watch ) {
		element.setAttribute( 'data-watch', field.data_watch );

		if ( field.data_show_on_change ) {
			element.setAttribute( 'data-show-on-change', field.data_show_on_change );
		}

		if ( field.data_show_when !== undefined ) {
			element.setAttribute( 'data-show-when', field.data_show_when );
		} else if ( ! field.data_hide_when && ! field.data_show_on_change ) {
			element.setAttribute( 'data-show-when', '1' );
		}

		if ( field.data_hide_when ) {
			element.setAttribute( 'data-hide-when', field.data_hide_when );
		}
	}

	// Arbitrary data-* attributes declared in configuration
	applyDataAttributes( element, field.attributes );
};

/**
 * Append a cache-busting query parameter for module import retries.
 *
 * @param {string} url - Module URL.
 * @param {number} [attempt=0] - Attempt count.
 * @returns {string} Updated URL.
 */
export const retryUrl = ( url, attempt = 0 ) => {
	if ( ! attempt || typeof url !== 'string' ) {
		return url;
	}

	return `${ url }${ url.includes( '?' ) ? '&' : '?' }ctc_retry=${ attempt }`;
};

/**
 * Shared offline handler that displays a single notification while network connectivity is lost
 * and resolves once connection is restored.
 *
 * @returns {Promise<void>} Resolves when connection returns.
 */
let offlineWait = null;

const waitForOnline = () => {
	if ( offlineWait ) {
		return offlineWait;
	}

	log( 'Utils', 'Network offline. Waiting for connection to resume for module import...' );

	document.dispatchEvent( new CustomEvent( 'ht_ctc_show_toast', {
		detail: {
			title: 'Network Offline',
			description: 'Waiting for connection to resume...',
			iconClass: 'dashicons dashicons-warning',
			iconColor: '#f56e28',
			duration: 60000,
		},
	} ) );

	offlineWait = new Promise( resolve => {
		const onOnline = () => {
			window.removeEventListener( 'online', onOnline );

			offlineWait = null;

			log( 'Utils', 'Network restored. Retrying module import immediately...' );

			document.dispatchEvent( new CustomEvent( 'ht_ctc_show_toast', {
				detail: {
					title: 'Network Restored',
					description: 'Resuming operations...',
					iconClass: 'dashicons dashicons-saved',
					iconColor: '#46b450',
					duration: 3000,
				},
			} ) );

			resolve();
		};

		window.addEventListener( 'online', onOnline );
	} );

	return offlineWait;
};

/**
 * Dynamically import a module with retry logic.
 *
 * @param {Function} importFn - Function returning a dynamic import promise.
 * @param {number} [retries=3] - Number of retries left.
 * @param {number} [delay=1000] - Delay between retries in milliseconds.
 * @param {number} [attempt=0] - Current attempt count.
 * @returns {Promise<any>}
 */
export const importWithRetry = async ( importFn, retries = 3, delay = 1000, attempt = 0 ) => {
	try {
		return await importFn( attempt );
	} catch ( error ) {
		if ( retries > 0 ) {
			if ( ! navigator.onLine ) {
				await waitForOnline();
				return importWithRetry( importFn, retries, delay, attempt + 1 );
			}

			log( 'Utils', `Module import failed, retrying in ${delay}ms... (${retries} retries left)`, error );
			await new Promise( resolve => setTimeout( resolve, delay ) );
			return importWithRetry( importFn, retries - 1, delay * 1.5, attempt + 1 );
		}
		throw error;
	}
};
