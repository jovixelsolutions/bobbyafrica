import { escapeHTML, escapeAttr, safeUrl } from '../../core/Utils.js';

/**
 * field_type: block_pro_feature
 *
 * Creates a ProFeature layout component.
 *
 * @param {Object} field Field configuration object.
 * @returns {HTMLElement} Pro feature element.
 */
export const createProFeature = ( field ) => {
	const div = document.createElement( 'div' );
	div.className = 'pro-feature-box';
	const icon = field.icon || 'dashicons dashicons-awards';

	// eslint-disable-next-line no-unsanitized/property -- Static HTML wrapper; dynamic values are safely escaped
	div.innerHTML = `
        <div class="pro-feature-inner">
            <div class="pro-feature-icon">
                <span class="${escapeAttr( icon )}"></span>
            </div>
            <div class="pro-feature-content">
                <div class="pro-feature-header">
                    <label class="pro-feature-title">${escapeHTML( field.title || '' )}</label>
                    <span class="pro-badge"><span class="dashicons dashicons-star-filled"></span> ${escapeHTML( field.badge || 'PRO' )}</span>
                </div>
                <p class="pro-feature-desc">${escapeHTML( field.description || '' )}</p>
                ${field.button_text ? `<a href="${escapeAttr( safeUrl( field.url ) )}" target="_blank" rel="noopener" class="pro-feature-btn">${escapeHTML( field.button_text )} <span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text">(opens in a new tab)</span></a>` : ''}
            </div>
        </div>
    `;
	return div;
};
