import { applyConditionalAttributes } from '../../core/Utils.js';

/**
 * field_type: block_content_details
 *
 * Creates a BlockContentDetails layout component.
 */
export const createBlockContentDetails = ( field, renderField ) => {
	const content = field.content || field.description || '';

	const classPr = field.class_pr || '';
	const el = document.createElement( 'details' );
	el.className = 'ctc_details';
	if ( classPr ) { el.className += ` ${classPr}`; }
	if ( field.style ) { el.style = field.style; } else { el.style.margin = '0px 10px'; }

	let innerHTML = '';
	if ( field.title ) { innerHTML += `<summary>${field.title}</summary>`; }
	if ( content ) {
		innerHTML += `<p class="description" style="margin:8px 10px 0px 10px;">${content}</p>`;
	}
	// eslint-disable-next-line no-unsanitized/property -- Static HTML skeleton for details component
	el.innerHTML = innerHTML;

	if ( field.fields && renderField ) {
		const fieldsContainer = document.createElement( 'div' );
		fieldsContainer.className = 'ctc-details-content';
		fieldsContainer.style.padding = '10px 0';

		const fragment = document.createDocumentFragment();
		field.fields.forEach( subField => {
			const childEl = renderField( subField );
			if ( childEl ) { fragment.appendChild( childEl ); }
		} );
		fieldsContainer.appendChild( fragment );
		el.appendChild( fieldsContainer );
	}

	applyConditionalAttributes( el, field );

	return el;
};
