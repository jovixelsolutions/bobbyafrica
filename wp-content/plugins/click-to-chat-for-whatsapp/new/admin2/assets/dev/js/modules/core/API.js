/**
 * API Module
 *
 * Handles all AJAX requests to the WordPress backend.
 * Checks for the WordPress Nonce (security) and manages endpoints.
 */
import { log } from './Utils.js';

export default class API {

	/**
	 * Initialize the API handler.
	 *
	 * @param {Object} config - The main application configuration object.
	 */
	constructor ( config ) {
		// 1. Get Nonce from config (important for security)
		// 2. Define API endpoints (save settings, get fields)

		// this.config = config;
		this.wprest_nonce = config.wprest_nonce || '';
		this.disabled = false;
		this.disabledReason = '';

		// Per-attempt request timeout (ms). Guards against a stalled PHP worker /
		// proxy leaving the fetch hanging (browser default is ~300s) with the UI
		// stuck in a loading state. A timed-out attempt is treated as a retriable
		// network failure (see request()).
		this.timeoutMs = config?.api?.timeoutMs || 30000;

		if ( ! this.wprest_nonce ) {
			log( 'API', 'Missing wprest_nonce. API requests blocked for security (403).' );
			this.disabled = true;
			this.disabledReason = 'Missing security nonce';
		}

		// Endpoints are localized by PHP via rest_url() (see
		// class-ht-ctc-admin-page-scripts.php), so they already respect the site's
		// permalink structure, custom REST prefix and subdirectory install. We do
		// NOT hardcode a '/wp-json/...' fallback: that literal is wrong on
		// plain-permalink (?rest_route=) and relocated-REST sites and would
		// silently POST to a 404. If an endpoint is missing, fail loud (disable) —
		// the same way we treat a missing nonce.
		this.endpoints = {
			SAVE: config?.api?.settings?.save || '',
			GET_FIELDS: config?.api?.settings?.getFields || '',
		};

		if ( ! this.endpoints.SAVE || ! this.endpoints.GET_FIELDS ) {
			log( 'API', 'Missing REST endpoint(s) in config. API requests disabled.', this.endpoints );
			this.disabled = true;
			this.disabledReason = this.disabledReason || 'Missing REST endpoint';
		}
	}

	/**
	 * Wrapper for fetch requests.
	 *
	 * @param {string} url - The URL to request.
	 * @param {Object} options - Fetch options (method, body, headers).
	 * @param {number} retries - Remaining retry attempts (GET / network failures only).
	 * @param {number} delay - Backoff delay (ms) before the next retry.
	 * @param {number} timeout - Per-attempt abort timeout (ms).
	 * @returns {Promise<Object>} - The JSON response.
	 */
	async request ( url, options = {}, retries = 3, delay = 1000, timeout = this.timeoutMs ) {
		// Block requests if the API was disabled at construction (missing nonce or
		// missing/unresolvable REST endpoint).
		if ( this.disabled ) {
			throw new Error( `API disabled: ${this.disabledReason || 'unavailable'}` );
		}

		// Prepare the request:
		// 1. Merge default headers (Content-Type, X-WP-Nonce).
		// 2. Stringify the body if it's an object.
		// 3. Execute the fetch request.
		// 4. Handle HTTP errors (e.g., 404, 500) by throwing an error with the server message.
		const defaultOptions = {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': this.wprest_nonce,
			},
		};

		const finalOptions = { ...defaultOptions, ...options };
		if ( options.body && typeof options.body === 'object' ) {
			finalOptions.body = JSON.stringify( options.body );
		}

		// Abort this attempt if the server doesn't respond within `timeout` ms.
		// Each (re)try gets a fresh controller, so the timeout is per-attempt.
		const controller = new AbortController();
		const timeoutId = setTimeout( () => controller.abort(), timeout );
		finalOptions.signal = controller.signal;

		try {
			const response = await fetch( url, finalOptions );

			// Read the body as text first, then parse defensively. Calling
			// response.json() directly would throw a status-less SyntaxError on a
			// non-JSON body (PHP fatal / HTML error page, proxy 502, empty body)
			// BEFORE we inspect response.ok — which both loses the HTTP status and
			// bypasses the status-based no-retry check below. Keeping the status in
			// the thrown message lets that check work for HTML error responses too.
			const rawBody = await response.text();
			let result = null;
			let parseError = null;
			try {
				result = rawBody ? JSON.parse( rawBody ) : null;
			} catch ( parseErr ) {
				parseError = parseErr;
			}

			if ( ! response.ok ) {
				// Keep the WP REST error `code` (e.g. rest_cookie_invalid_nonce) in
				// the message: friendlyErrorMessage keys off it to distinguish an
				// expired session from other 403s. A non-JSON error body means the
				// request never reached WordPress (hosting firewall / ModSecurity /
				// proxy error page) — say so instead of a bare 'HTTP error'.
				let serverMessage;
				if ( result && result.message ) {
					serverMessage = result.code ?
						`${result.code}: ${result.message}` :
						result.message;
				} else if ( parseError && rawBody ) {
					serverMessage = 'Non-JSON error response (request likely blocked before reaching WordPress — hosting firewall / security module)';
				} else {
					serverMessage = 'HTTP error';
				}
				throw new Error( `[${response.status}] ${serverMessage}` );
			}

			// 2xx but the body wasn't usable JSON (PHP notice prepended, empty body).
			// Re-throw the parse error so its "Unexpected token" wording still feeds
			// the friendly-message mapping in App.js; treated as retriable for GET.
			if ( parseError || null === result ) {
				throw parseError || new Error( 'Invalid or empty JSON response from server' );
			}

			return result;
		} catch ( rawError ) {
			// Normalize our own timeout abort into a clear, retriable error.
			// (Declared as a new const — not reassigning the caught binding.)
			const error = ( rawError && rawError.name === 'AbortError' ) ?
				new Error( `Request timed out after ${timeout}ms` ) :
				rawError;

			// Auto retry logic for GET requests or network failures
			const isGet = finalOptions.method === 'GET';

			// Do not retry explicitly identified Client/Auth errors (e.g., 403 Session Expired)
			// Retrying these is pointless and forces the user to wait 7+ seconds for the error UI
			if ( error.message && error.message.match( /\[(400|401|403|404|405|429)\]/ ) ) {
				log( 'API', 'Request failed with fatal client error. Skipping retries.', error );
				throw error;
			}

			if ( retries > 0 ) {
				// Offline → wait for the connection to come back, then resubmit ONCE.
				// This path applies to POST too: a save shouldn't be lost to a
				// transient blip. It consumes no retry — we're waiting to *send*,
				// not re-sending a request the server may already have processed
				// (so there's no double-submit risk here).
				if ( ! navigator.onLine ) {
					log( 'API', 'Network offline. Waiting for connection to resume...' );

					// Dispatch offline toast
					document.dispatchEvent( new CustomEvent( 'ht_ctc_show_toast', {
						detail: {
							title: 'Network Offline',
							description: 'Waiting for connection to resume...',
							iconClass: 'dashicons dashicons-warning',
							iconColor: '#f56e28',
							duration: 60000, // Wait a long time
						},
					} ) );

					await new Promise( resolve => {
						const onOnline = () => {
							window.removeEventListener( 'online', onOnline );
							resolve();
						};
						window.addEventListener( 'online', onOnline );
					} );
					log( 'API', 'Network restored. Retrying request immediately...' );

					// Dispatch online toast
					document.dispatchEvent( new CustomEvent( 'ht_ctc_show_toast', {
						detail: {
							title: 'Network Restored',
							description: 'Resuming operations...',
							iconClass: 'dashicons dashicons-saved',
							iconColor: '#46b450',
							duration: 3000,
						},
					} ) );

					// Retry without consuming retry count
					return this.request( url, options, retries, delay );
				}

				// Online but the attempt still failed (5xx, timeout, transient drop).
				// Only auto-retry GETs with backoff: blindly re-sending a POST risks
				// a double-submit when the server processed the first request but the
				// response was lost. A save carries the user's data, so fail loudly
				// and let them retry rather than silently double-applying it.
				if ( isGet ) {
					log( 'API', `Request failed, retrying in ${delay}ms... (${retries} retries left)`, error );
					await new Promise( resolve => setTimeout( resolve, delay ) );
					return this.request( url, options, retries - 1, delay * 1.5 ); // Exponential backoff
				}
			}

			log( 'API', 'Request failed', error );
			throw error;
		} finally {
			// Clear this attempt's timeout (no-op if it already fired).
			clearTimeout( timeoutId );
		}
	}

	/**
	 * Return valid endpoints
	 */
	getEndpoints () {
		return this.endpoints;
	}
}
