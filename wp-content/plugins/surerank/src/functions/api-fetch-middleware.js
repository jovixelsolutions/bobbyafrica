/**
 * apiFetch middleware for SureRank. See #2362.
 *
 * For SureRank's REST namespace (`/surerank/v1/*`):
 *  - 15s timeout on REST calls so a blackholing WAF doesn't freeze
 *    the save button for the browser-default ~60s.
 *  - On a transport-level failure (non-JSON body, network error,
 *    timeout) for a state-changing save we have a mirrored AJAX
 *    handler for, transparently retry via `admin-ajax.php`.
 *  - Normalise remaining errors into messages pointing at the
 *    compatibility guide.
 *
 * Structured server errors (403/404 with `{code, message}` bodies) —
 * including Pro auth denials and expired-nonce responses — surface
 * unchanged so users see the real reason.
 *
 * Non-SureRank apiFetch traffic (e.g. /wp/v2/media) passes through
 * completely untouched — no timeout, no error rewriting, no fallback.
 *
 * @package
 */

import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const SURERANK_NAMESPACE = '/surerank/';

const AJAX_FALLBACK_ACTIONS = {
	'/surerank/v1/post/settings': 'surerank_save_post_settings',
	'/surerank/v1/term/settings': 'surerank_save_term_settings',
	'/surerank/v1/admin/global-settings': 'surerank_save_admin_settings',
};

const NETWORK_TIMEOUT_MS = 15000;

const COMPAT_HELP_URL_FALLBACK =
	'https://surerank.com/docs/security-plugin-firewall-compatibility/';

/**
 * Rewrite transport-level failures into actionable messages. Structured
 * server errors (those with their own `code` and `message`) are returned
 * unchanged — we only want to rewrite when the upstream gave us nothing
 * useful to show.
 *
 * @param {*} error Raw error from apiFetch / fetch.
 * @return {Object} Error object suitable for display.
 */
function normaliseError( error ) {
	const learnMoreUrl =
		window?.surerank_admin_common?.compat_help_url ||
		COMPAT_HELP_URL_FALLBACK;

	if ( error instanceof TypeError && /fetch/i.test( error.message ) ) {
		return {
			code: 'surerank_network_error',
			message: __(
				'Could not reach the server. Please check your connection and try again.',
				'surerank'
			),
			learnMoreUrl,
		};
	}

	if ( error?.code === 'surerank_timeout' ) {
		return {
			code: 'surerank_timeout',
			message: __(
				'The request took too long to complete. A security plugin or firewall may be holding the connection open. See the compatibility guide.',
				'surerank'
			),
			learnMoreUrl,
		};
	}

	if ( /not a valid JSON response/i.test( error?.message || '' ) ) {
		return {
			code: 'surerank_invalid_json_response',
			message: __(
				"A security plugin or firewall is blocking SureRank's save request. See the compatibility guide for fixes.",
				'surerank'
			),
			learnMoreUrl,
		};
	}

	// Upstream gave us a structured error — pass through unchanged
	// so callers see the real `code`/`message` (e.g. a Pro role denial
	// or an expired nonce) without SureRank copy bleeding in.
	return error;
}

/**
 * Transport-level failure signatures that justify an AJAX retry.
 * Structured JSON 403/404 bodies are NOT transport failures; they're
 * legitimate auth/route denials and must surface unchanged.
 *
 * @param {*} error Raw error from apiFetch.
 * @return {boolean} True to retry via AJAX.
 */
function looksLikeRestBlocked( error ) {
	if ( error instanceof TypeError && /fetch/i.test( error.message ) ) {
		return true;
	}
	if ( error?.code === 'surerank_timeout' ) {
		return true;
	}
	return /not a valid JSON response/i.test( error?.message || '' );
}

/**
 * Race a promise against a timeout. On timeout, abort via the optional
 * AbortController and reject with a `surerank_timeout` error.
 *
 * @param {Promise}              promise    Inner promise.
 * @param {number}               ms         Timeout in milliseconds.
 * @param {AbortController|null} controller Optional controller to abort.
 * @return {Promise} Resolves with the inner result or rejects with timeout.
 */
function withTimeout( promise, ms, controller = null ) {
	let timeoutId;
	const timeout = new Promise( ( _resolve, reject ) => {
		timeoutId = setTimeout( () => {
			if ( controller ) {
				try {
					controller.abort();
				} catch ( _ ) {
					/* no-op */
				}
			}
			const err = new Error( 'SureRank request timed out' );
			err.code = 'surerank_timeout';
			reject( err );
		}, ms );
	} );
	return Promise.race( [ promise, timeout ] ).finally( () => {
		clearTimeout( timeoutId );
	} );
}

/**
 * Build a FormData body for admin-ajax.php: action + wp_rest nonce +
 * each apiFetch `data` field. Nested objects are JSON-encoded so the
 * PHP handler can decode them (see extract_meta_data() in
 * inc/ajax/save-endpoints.php).
 *
 * @param {string} action wp_ajax action name.
 * @param {Object} data   apiFetch payload.
 * @return {FormData} FormData ready to POST to admin-ajax.php.
 */
function buildAjaxBody( action, data ) {
	const form = new FormData();
	form.append( 'action', action );

	const nonce =
		window?.surerank_admin_common?.rest_nonce ||
		window?.wpApiSettings?.nonce ||
		'';
	if ( nonce ) {
		form.append( '_wpnonce', nonce );
	}

	if ( data && typeof data === 'object' ) {
		Object.keys( data ).forEach( ( key ) => {
			const value = data[ key ];
			if ( value === null || value === undefined ) {
				return;
			}
			form.append(
				key,
				typeof value === 'object'
					? JSON.stringify( value )
					: String( value )
			);
		} );
	}

	return form;
}

/**
 * Retry a failed REST save via admin-ajax.php. Returns the unwrapped
 * payload on success (matching the shape the original REST response
 * would have produced via Send_Json::success).
 *
 * @param {string} path    REST path that originally failed.
 * @param {Object} options Original apiFetch options.
 * @return {Promise<any>} Unwrapped AJAX response payload, or rejects if AJAX also failed.
 */
async function retryViaAjax( path, options ) {
	const normalisedPath = path.split( '?' )[ 0 ].replace( /\/$/, '' );
	const action = AJAX_FALLBACK_ACTIONS[ normalisedPath ];
	if ( ! action ) {
		throw new Error( 'no_ajax_fallback' );
	}

	const ajaxUrl =
		window?.surerank_admin_common?.ajax_url ||
		window?.ajaxurl ||
		'/wp-admin/admin-ajax.php';

	const controller = new AbortController();
	const response = await withTimeout(
		fetch( ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: buildAjaxBody( action, options?.data || {} ),
			signal: controller.signal,
		} ),
		NETWORK_TIMEOUT_MS,
		controller
	);

	if ( ! response.ok ) {
		throw new Error( 'ajax_http_' + response.status );
	}

	const body = await response.json();

	// wp_send_json_success wraps payload in { success: true, data: {...} }.
	if ( body && body.success === true ) {
		return body.data || body;
	}

	if ( body && body.success === false ) {
		const err = new Error(
			body.data?.message || 'AJAX fallback returned an error.'
		);
		err.code = body.data?.code || 'surerank_ajax_error';
		throw err;
	}

	throw new Error( 'ajax_unrecognised_response' );
}

/**
 * @param {Object}   options apiFetch options.
 * @param {Function} next    Next middleware / underlying fetch.
 * @return {Promise<any>} Response payload, or rejects with a normalised error suitable for display.
 */
async function surerankApiFetchMiddleware( options, next ) {
	const path = options?.path || '';

	// Non-SureRank requests (e.g. /wp/v2/media) pass through untouched.
	// A 15s timeout here would break large media uploads.
	if ( path.indexOf( SURERANK_NAMESPACE ) !== 0 ) {
		return next( options );
	}

	const method = ( options?.method || 'GET' ).toUpperCase();
	const isStateChanging =
		method === 'POST' || method === 'PUT' || method === 'PATCH';

	try {
		return await withTimeout( next( options ), NETWORK_TIMEOUT_MS );
	} catch ( error ) {
		const canRetryViaAjax =
			isStateChanging &&
			looksLikeRestBlocked( error ) &&
			AJAX_FALLBACK_ACTIONS[
				path.split( '?' )[ 0 ].replace( /\/$/, '' )
			];

		if ( canRetryViaAjax ) {
			try {
				return await retryViaAjax( path, options );
			} catch ( _ajaxError ) {
				throw normaliseError( error );
			}
		}

		throw normaliseError( error );
	}
}

// Multiple SureRank bundles can share wp.apiFetch on the same admin
// page; a global flag prevents duplicate registration (which would
// run the middleware twice per request and double AJAX retries).
if ( ! window.__surerankApiFetchMiddlewareRegistered ) {
	window.__surerankApiFetchMiddlewareRegistered = true;
	apiFetch.use( surerankApiFetchMiddleware );
}
