/**
 * field_type: block_spacer
 *
 * Creates a Spacer layout component.
 */
export const createSpacer = ( field ) => {
	const div = document.createElement( 'div' );
	const height = field.height || '20px';
	div.style.height = height;
	div.className = `ctc-spacer ${field.class_pr || ''}`;
	return div;
};
