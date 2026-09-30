import { applyConditionalAttributes, escapeHTML } from '../../core/Utils.js';

/**
 * field_type: block_rows
 *
 * Creates a BlockRows layout component.
 *
 * Layout Concept:
 * - The component represents a single horizontal row (`div.fields-row`).
 * - Its `fields` property is expected to be a 2D structure (array of arrays).
 * - Each inner array represents a Column (`div.field-col`).
 * - Fields within each inner array (column) are stacked vertically.
 * - Columns are aligned horizontally next to each other.
 *
 * Example structure:
 * 'fields' => array(
 *     array( // Column 1
 *         array( 'field_type' => 'field_text', ... ), // Stacked vertically
 *         array( 'field_type' => 'field_color', ... )
 *     ),
 *     array( // Column 2
 *         array( 'field_type' => 'field_checkbox', ... ) // Aligned horizontally to Column 1
 *     )
 * )
 *
 * Note: A safety fallback is included to gracefully handle 1D arrays/objects by
 * treating each top-level item as a separate column.
 */
export const createBlockRows = ( field, context, renderField ) => {
	const classPr = field.class_pr || '';

	const div = document.createElement( 'div' );
	div.className = `rows-wrapper ${classPr}`;

	applyConditionalAttributes( div, field );
	if ( field.label ) {
		const label = document.createElement( 'label' );
		// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values using Utils.escapeHTML
		label.innerHTML = escapeHTML( field.label );
		div.insertBefore( label, div.firstChild );
	}

	const fieldsContainer = document.createElement( 'div' );
	fieldsContainer.className = 'fields-row';

	if ( Array.isArray( field.fields ) ) {
		field.fields.forEach( row => {
			const col = document.createElement( 'div' );
			col.className = 'field-col';
			if ( Array.isArray( row ) ) {
				row.forEach( subField => {
					const el = renderField( subField );
					if ( el ) { col.appendChild( el ); }
				} );
			} else {
				const el = renderField( row );
				if ( el ) { col.appendChild( el ); }
			}
			fieldsContainer.appendChild( col );
		} );
	} else if ( typeof field.fields === 'object' ) {
		Object.values( field.fields )
			.forEach( subFields => {
				const col = document.createElement( 'div' );
				col.className = 'field-col';
				if ( Array.isArray( subFields ) ) {
					subFields.forEach( subField => {
						const el = renderField( subField );
						if ( el ) { col.appendChild( el ); }
					} );
				} else {
					const el = renderField( subFields );
					if ( el ) { col.appendChild( el ); }
				}
				fieldsContainer.appendChild( col );
			} );
	}

	div.appendChild( fieldsContainer );
	if ( field.help ) {
		const helpP = document.createElement( 'p' );
		helpP.className = 'help-text';
		// eslint-disable-next-line no-unsanitized/property -- Help text defined in PHP can contain safe HTML
		helpP.innerHTML = field.help;
		div.appendChild( helpP );
	}
	return div;
};
