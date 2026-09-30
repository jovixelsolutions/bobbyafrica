import { applyConditionalAttributes } from '../../core/Utils.js';
import { getCtcStorageItem, setCtcStorageItem } from '../../core/Storage.js';
import { collapseWithTransition, expandWithTransition } from './Transitions.js';

/**
 * field_type: block_accordion
 *
 * Creates a BlockAccordion layout component.
 *
 * Layout Concept:
 * - A collapsible accordion container.
 * - The header acts as a button to toggle visibility of the body/content.
 * - Its `fields` property expects a 1D flat array of fields that render inside the accordion body.
 *
 * Example structure:
 * 'fields' => array(
 *     array( 'field_type' => 'field_text', ... )
 * )
 */
export const createBlockAccordion = ( field, renderField ) => {
	const classPr = field.class_pr || '';

	const container = document.createElement( 'div' );
	container.className = `ctc-accordion ${classPr}`;

	applyConditionalAttributes( container, field );
	if ( field.id ) { container.id = field.id; }

	const header = document.createElement( 'button' );
	header.className = 'ctc-accordion-header';
	header.type = 'button';
	header.setAttribute( 'aria-expanded', 'false' );

	const contentId = `ctc-accordion-content-${field.id || Math.random()
		.toString( 36 )
		.substr( 2, 9 )}`;
	header.setAttribute( 'aria-controls', contentId );

	const titleWrap = document.createElement( 'div' );
	titleWrap.className = 'ctc-accordion-title-wrap';

	if ( field.icon ) {
		const icon = document.createElement( 'i' );
		icon.className = field.icon + ' ctc-accordion-icon';
		titleWrap.appendChild( icon );
	}

	const title = document.createElement( 'span' );
	title.textContent = field.title || '';
	titleWrap.appendChild( title );

	header.appendChild( titleWrap );

	const chevron = document.createElement( 'i' );
	chevron.className = 'dashicons dashicons-arrow-down-alt2 ctc-accordion-chevron';
	header.appendChild( chevron );

	container.appendChild( header );

	const content = document.createElement( 'div' );
	content.className = 'ctc-accordion-content';
	content.id = contentId;
	content.setAttribute( 'role', 'region' );
	content.setAttribute( 'aria-labelledby', header.id || '' );

	const inner = document.createElement( 'div' );
	inner.className = 'ctc-accordion-inner';

	if ( field.content || field.description ) {
		const desc = document.createElement( 'div' );
		desc.className = 'description';
		desc.style.marginBottom = '1.25rem';
		// eslint-disable-next-line no-unsanitized/property -- Content/Description defined in PHP can contain safe HTML
		desc.innerHTML = field.content || field.description;
		inner.appendChild( desc );
	}

	if ( field.fields && renderField ) {
		const fragment = document.createDocumentFragment();
		field.fields.forEach( subField => {
			const childEl = renderField( subField );
			if ( childEl ) {
				fragment.appendChild( childEl );
			}
		} );
		inner.appendChild( fragment );
	}

	content.appendChild( inner );
	container.appendChild( content );

	const storageKey = field.id ? 'ctc_acc_' + field.id : null;

	const toggle = () => {
		const isOpen = container.classList.contains( 'active' );

		if ( isOpen ) {
			collapseWithTransition( content );
			container.classList.remove( 'active' );
			header.setAttribute( 'aria-expanded', 'false' );

			if ( storageKey ) {
				setCtcStorageItem( storageKey, 'closed' );
			}
		} else {
			container.classList.add( 'active' );
			header.setAttribute( 'aria-expanded', 'true' );
			expandWithTransition( content, () => container.classList.contains( 'active' ) );

			if ( storageKey ) {
				setCtcStorageItem( storageKey, 'open' );
			}
		}
	};

	header.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		toggle();
	} );

	let initialState = 'closed';

	if ( field.default_state ) {
		initialState = field.default_state;
	}

	if ( field.open === 'open' || field.open === true ) {
		initialState = 'open';
	}

	if ( storageKey ) {
		const stored = getCtcStorageItem( storageKey );
		if ( stored ) {
			initialState = stored;
		}
	}

	if ( initialState === 'open' ) {
		container.classList.add( 'active' );
		header.setAttribute( 'aria-expanded', 'true' );
		content.style.maxHeight = 'none';
	}

	return container;
};
