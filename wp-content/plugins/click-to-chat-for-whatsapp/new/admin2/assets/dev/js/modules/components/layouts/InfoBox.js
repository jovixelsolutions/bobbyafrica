import { applyConditionalAttributes, escapeHTML, escapeAttr } from '../../core/Utils.js';

const INFO_BOX_ICONS = new Map( [
	[ 'info', 'dashicons dashicons-info' ],
	[ 'warning', 'dashicons dashicons-warning' ],
	[ 'error', 'dashicons dashicons-dismiss' ],
	[ 'success', 'dashicons dashicons-yes-alt' ],
] );

/**
 * field_type: block_infobox, block_infobox_alert
 *
 * Creates an InfoBox or InfoBoxAlert layout component.
 */
export const createInfoBox = ( field ) => {
	const fieldType = field.field_type;
	const classPr = field.class_pr || '';
	const div = document.createElement( 'div' );
	const type = field.type || 'info';
	const baseClass = ( fieldType === 'block_infobox_alert' ) ? 'infobox-alert' : 'info-box';
	div.className = `${baseClass} ${type} ${classPr}`;
	if ( field.id ) { div.id = field.id; }

	const iconClass = field.icon || INFO_BOX_ICONS.get( type ) || INFO_BOX_ICONS.get( 'info' );

	applyConditionalAttributes( div, field );

	// eslint-disable-next-line no-unsanitized/property -- Static HTML wrapper; dynamic values are safely escaped
	div.innerHTML = `
        <div class="info-box-inner">
            <div class="info-box-icon">
                <span class="${escapeAttr( iconClass )}"></span>
            </div>
            <div class="info-box-content">
                ${field.title ? `<h4 class="info-box-title">${escapeHTML( field.title )}</h4>` : ''}
                <div class="info-box-text">${( field.content || field.description || '' )}</div>
            </div>
        </div>
    `;
	return div;
};
