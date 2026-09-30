import { applyVariables, applyConditionalAttributes } from '../../core/Utils.js';

/**
 * field_type: block_content
 *
 * Creates a BlockContent layout component.
 */
export const createBlockContent = ( field ) => {
	let content = field.content || field.description || '';
	let title = field.title || '';

	if ( field.variables ) {
		content = applyVariables( content, field.variables );
		if ( title ) {
			title = applyVariables( title, field.variables );
		}
	}

	const classPr = field.class_pr || '';
	const el = document.createElement( 'div' );
	if ( classPr ) { el.className = classPr; }
	if ( field.style ) { el.style = field.style; } else { el.style.margin = '0px 10px'; }

	const titleHtml = title ? `<strong>${title}</strong><br>` : '';
	// eslint-disable-next-line no-unsanitized/property -- title and content are processed via applyVariables; safe HTML from PHP
	el.innerHTML = titleHtml + content;

	applyConditionalAttributes( el, field );

	return el;
};
