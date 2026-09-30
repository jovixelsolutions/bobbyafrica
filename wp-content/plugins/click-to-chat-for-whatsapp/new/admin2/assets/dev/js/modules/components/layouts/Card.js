import { applyConditionalAttributes, escapeHTML } from '../../core/Utils.js';

/**
 * field_type: card
 *
 * Creates a Card layout component.
 *
 * Layout Concept:
 * - A standard dashboard panel card container.
 * - Wraps a group of settings with a title and description.
 * - Its `fields` property expects a 1D flat array of fields that render inside the card body.
 *
 * Example structure:
 * $fields[] = array(
 *     'field_type' => 'card',
 *     'title' => 'My Card Title',
 *     'description' => 'My card description',
 *     'fields' => array(
 *         array( 'field_type' => 'field_text', ... )
 *     )
 * )
 */
export const createCard = ( field, renderField ) => {
	const classPr = field.class_pr || '';
	const card = document.createElement( 'div' );
	card.className = `ctc-card ${classPr}`;
	if ( field.id ) { card.id = field.id; }

	applyConditionalAttributes( card, field );

	let headerHtml = `<div class="ctc-card-header"><h3>${escapeHTML( field.title || '' )}</h3>`;
	if ( field.description ) {
		headerHtml += `<p>${escapeHTML( field.description )}</p>`;
	}
	headerHtml += '</div>';

	const content = document.createElement( 'div' );
	content.className = 'ctc-card-content';

	if ( classPr.includes( 'is-sortable' ) ) {
		content.classList.add( 'is-sortable' );
	}

	if ( field.fields && renderField ) {
		const fragment = document.createDocumentFragment();
		field.fields.forEach( subField => {
			const el = renderField( subField );
			if ( el ) { fragment.appendChild( el ); }
		} );
		content.appendChild( fragment );
	}

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	card.innerHTML = headerHtml;
	card.appendChild( content );
	return card;
};
