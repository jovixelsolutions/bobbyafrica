/**
 * Preview CSS helpers
 */

/**
 * Sanitize a value interpolated into a <style> block.
 * Strips characters that could close the style tag or open new rules,
 * while keeping normal CSS values (colors, sizes, %) intact.
 *
 * Preview-only: saved values are sanitized server-side by HT_CTC_Sanitizer
 * before they render on the front end.
 *
 * @param {string} value
 * @returns {string}
 */
export const escapeCssValue = ( value ) => {
	return String( value ?? '' )
		.replace( /[<>{}]/g, '' );
};
