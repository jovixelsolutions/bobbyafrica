/**
 * field_type: block_sub_heading
 */
import { escapeHTML } from '../../core/Utils.js';

export const createSubHeading = ( field ) => {
	const classPr = field.class_pr || '';
	const div = document.createElement( 'div' );
	div.className = `sub-heading ${classPr}`;
	if ( field.id ) { div.id = field.id; }

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	div.innerHTML = `
        ${field.title ? `<h4>${escapeHTML( field.title )}</h4>` : ''}
        ${field.description ? `<p class="description">${escapeHTML( field.description )}</p>` : ''}
    `;
	return div;
};
