/**
 * Validate an incoming postMessage event before treating it as a ZipWP
 * authentication success payload.
 *
 * Rejects messages that do not originate from the expected ZipWP auth origin
 * or from the popup window we opened, preventing CSRF via forged postMessage
 * from attacker-controlled pages.
 *
 * @since 1.2.76
 *
 * @param {MessageEvent} event          The incoming message event.
 * @param {string|null}  expectedOrigin The expected origin, e.g. "https://app.zipwp.com".
 * @param {Window|null}  expectedSource The popup window opened by this component.
 * @return {boolean} True if the event is a trustworthy ZIPWP_AUTH_SUCCESS payload.
 */
export const isValidAuthMessage = ( event, expectedOrigin, expectedSource ) => {
	if ( ! event || typeof event !== 'object' ) {
		return false;
	}

	if ( ! expectedOrigin || event.origin !== expectedOrigin ) {
		return false;
	}

	if ( ! expectedSource || event.source !== expectedSource ) {
		return false;
	}

	if ( ! event.data || event.data.type !== 'ZIPWP_AUTH_SUCCESS' ) {
		return false;
	}

	const { token, credit_token: creditToken, email } = event.data;

	if ( ! token || ! creditToken || ! email ) {
		return false;
	}

	return true;
};

/**
 * Resolve the expected ZipWP auth origin from the configured screen URL.
 *
 * @since 1.2.76
 *
 * @param {string|undefined|null} screenUrl The configured ZipWP auth screen URL.
 * @return {string|null} The resolved origin or null if the URL is invalid.
 */
export const resolveAuthOrigin = ( screenUrl ) => {
	if ( ! screenUrl ) {
		return null;
	}
	try {
		return new URL( screenUrl ).origin;
	} catch ( e ) {
		return null;
	}
};
