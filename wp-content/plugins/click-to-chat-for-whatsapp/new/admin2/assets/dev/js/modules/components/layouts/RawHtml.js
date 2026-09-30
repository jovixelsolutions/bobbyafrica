import { applyVariables } from '../../core/Utils.js';

/**
 * field_type: block_raw_html
 *
 * Creates a RawHtml layout component.
 */
export const createRawHtml = ( field ) => {
	const div = document.createElement( 'div' );
	if ( field.class_pr ) {
		div.className = field.class_pr;
	}
	if ( field.content ) {
		let contentHtml = field.content;
		if ( field.variables ) {
			contentHtml = applyVariables( contentHtml, field.variables );
		}
		// eslint-disable-next-line no-unsanitized/property -- Raw HTML content provided by the plugin settings
		div.innerHTML = contentHtml;
	}
	return div;
};
