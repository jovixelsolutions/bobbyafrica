import { applyConditionalAttributes, escapeHTML } from '../../core/Utils.js';

/**
 * field_type: block_divider
 *
 * Creates a Divider layout component.
 */
export const createDivider = ( field ) => {
	const div = document.createElement( 'div' );
	div.className = `ctc-divider ${field.class_pr || ''}`;

	if ( field.text ) {
		// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
		div.innerHTML = `<span>${escapeHTML( field.text )}</span>`;
		div.classList.add( 'has-text' );
	} else {
		div.innerHTML = '<hr>';
	}
	applyConditionalAttributes( div, field );
	return div;
};
