import DOMPurify from 'dompurify';

/**
 * Sanitize a string of HTML while explicitly allowing the `target` attribute.
 *
 * By default DOMPurify may strip attributes such as `target` which are
 * commonly used on anchor tags for opening links in a new tab. Since the
 * plugin renders messages that include `<a>` elements we need to keep
 * `target` intact so that links still open correctly.
 *
 * @param {string} dirty HTML to clean
 * @return {string} Cleaned HTML string
 */
export const sanitizeHTML = ( dirty ) =>
	DOMPurify.sanitize( dirty, { ADD_ATTR: [ 'target' ] } );
