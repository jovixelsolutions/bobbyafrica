import { safeUrl } from '../../core/Utils.js';

/**
 * Creates an ExternalLink layout component (field_type: block_external_link).
 *
 * @param {Object} field Field configuration.
 * @returns {HTMLAnchorElement} The external link element.
 */
export const createExternalLink = ( field ) => {
	const classPr = field.class_pr || '';
	const link = document.createElement( 'a' );
	link.href = safeUrl( field.url );
	link.className = `external-link external-link--block ${classPr}`.trim();
	link.target = '_blank';

	// eslint-disable-next-line no-unsanitized/property -- Static HTML for link icon and label
	link.innerHTML = `${field.label || ''} <span class="${field.icon || 'dashicons dashicons-external'}"></span>`;
	return link;
};
