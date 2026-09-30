import { applyConditionalAttributes } from '../../core/Utils.js';

const appendRenderedFields = ( parent, fields, renderField ) => {
	if ( ! fields || ! renderField ) { return; }
	const fragment = document.createDocumentFragment();
	fields.forEach( subField => {
		const el = renderField( subField );
		if ( el ) { fragment.appendChild( el ); }
	} );
	parent.appendChild( fragment );
};

const createDragHandle = () => {
	const handle = document.createElement( 'div' );
	handle.className = 'drag-handle';
	handle.innerHTML = '<span class="dashicons dashicons-sort"></span>';
	return handle;
};

const fillOmniChannelGroup = ( div, field, renderField ) => {
	const fields = [ ...field.fields ];
	const headerField = fields.shift();

	const header = document.createElement( 'div' );
	header.className = 'omni-item-header';

	if ( field.draggable ) {
		header.appendChild( createDragHandle() );
	}

	if ( renderField ) {
		const headerEl = renderField( headerField );
		if ( headerEl ) {
			const group = headerEl.closest( '.form-group' ) || headerEl;
			group.classList.add( 'no-margin' );
			header.appendChild( headerEl );
		}
	}

	const expandIcon = document.createElement( 'div' );
	expandIcon.className = 'expand-icon';
	expandIcon.innerHTML = '<span class="dashicons dashicons-arrow-down-alt2"></span>';
	header.appendChild( expandIcon );

	const body = document.createElement( 'div' );
	body.className = 'omni-item-body';
	appendRenderedFields( body, fields, renderField );

	div.appendChild( header );
	div.appendChild( body );
};

const fillStandardGroup = ( div, field, renderField ) => {
	if ( field.draggable ) {
		div.appendChild( createDragHandle() );
		div.classList.add( 'has-drag-handle' );
	}
	appendRenderedFields( div, field.fields, renderField );
};

/**
 * field_type: block_group
 *
 * Creates a BlockGroup layout component.
 *
 * Layout Concept:
 * - A container that groups and renders fields sequentially.
 * - By default, fields are appended vertically in a 1D flat array.
 * - Commonly used to wrap multiple fields under a single conditional visibility trigger.
 *
 * Example structure:
 * 'fields' => array(
 *     array( 'field_type' => 'field_checkbox', ... ),
 *     array( 'field_type' => 'field_text', ... )
 * )
 */
export const createBlockGroup = ( field, context, renderField ) => {
	const classPr = field.class_pr || '';
	const div = document.createElement( 'div' );
	div.className = `field-group ${classPr}`;
	if ( field.id ) { div.id = field.id; }
	applyConditionalAttributes( div, field );

	if ( classPr.includes( 'omni-channel-item' ) ) {
		fillOmniChannelGroup( div, field, renderField );
	} else {
		fillStandardGroup( div, field, renderField );
	}

	return div;
};
