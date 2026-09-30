import { applyConditionalAttributes } from '../../core/Utils.js';
import { collapseWithTransition, expandWithTransition } from './Transitions.js';

/**
 * field_type: block_faq
 *
 * Creates a BlockFaq layout component.
 */
export const createBlockFaq = ( field ) => {
	const classPr = field.class_pr || '';
	const container = document.createElement( 'div' );
	container.className = `ctc-faq-item ${classPr}`;

	applyConditionalAttributes( container, field );
	if ( field.id ) { container.id = field.id; }

	const header = document.createElement( 'button' );
	header.className = 'ctc-faq-header';
	header.type = 'button';
	header.setAttribute( 'aria-expanded', 'false' );

	const contentId = `ctc-faq-content-${field.id || Math.random()
		.toString( 36 )
		.substr( 2, 9 )}`;
	header.setAttribute( 'aria-controls', contentId );

	const titleWrp = document.createElement( 'div' );
	titleWrp.className = 'ctc-faq-title-wrap';

	const title = document.createElement( 'span' );
	// eslint-disable-next-line no-unsanitized/property -- FAQ question/title can contain safe HTML formatting from PHP
	title.innerHTML = field.question || field.title || '';
	titleWrp.appendChild( title );
	header.appendChild( titleWrp );

	const chevron = document.createElement( 'i' );
	chevron.className = 'dashicons dashicons-arrow-down-alt2 ctc-faq-chevron';
	header.appendChild( chevron );

	container.appendChild( header );

	const content = document.createElement( 'div' );
	content.className = 'ctc-faq-content';
	content.id = contentId;

	const inner = document.createElement( 'div' );
	inner.className = 'ctc-faq-inner';
	// eslint-disable-next-line no-unsanitized/property -- FAQ answer/content can contain safe HTML formatting from PHP
	inner.innerHTML = field.answer || field.content || field.description || '';
	content.appendChild( inner );

	container.appendChild( content );

	header.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		const isOpen = container.classList.contains( 'active' );

		const parent = container.parentElement;
		if ( parent ) {
			const siblings = parent.querySelectorAll( '.ctc-faq-item.active' );
			siblings.forEach( sibling => {
				if ( sibling !== container ) {
					sibling.classList.remove( 'active' );
					const sibHeader = sibling.querySelector( '.ctc-faq-header' );
					const sibContent = sibling.querySelector( '.ctc-faq-content' );
					if ( sibHeader ) { sibHeader.setAttribute( 'aria-expanded', 'false' ); }
					if ( sibContent ) { collapseWithTransition( sibContent ); }
				}
			} );
		}

		if ( isOpen ) {
			collapseWithTransition( content );
			container.classList.remove( 'active' );
			header.setAttribute( 'aria-expanded', 'false' );
		} else {
			container.classList.add( 'active' );
			header.setAttribute( 'aria-expanded', 'true' );
			expandWithTransition( content, () => container.classList.contains( 'active' ) );
		}
	} );

	return container;
};
