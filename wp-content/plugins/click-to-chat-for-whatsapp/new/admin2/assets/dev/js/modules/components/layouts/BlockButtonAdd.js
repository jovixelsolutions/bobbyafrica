/**
 * field_type: block_button_add
 */
import { applyConditionalAttributes, escapeHTML, escapeAttr } from '../../core/Utils.js';

/**
 * Self-contained repeater structure when `container_class` is provided:
 *
 *   div.ctc-repeater-wrapper        					 ← class_pr, conditional attrs
 *     div.[container_class] .ctc-repeater-items         ← Items_Container, remove marker
 *     button                        					 ← add button
 * ctc-group-sync might need to be added to container from php for group sync.
 *
 * Falls back to button-only rendering when `container_class` is absent
 * (backward compatibility for legacy configs).
 *
 * @param {*} field
 * @returns
 */
export const createBlockButtonAdd = ( field ) => {
	const buttonClass = field.button_class || '';
	const dataCallback = field.data_callback || '';
	const dataCallbackContainer = field.data_callback_container || '';

	if ( field.container_class ) {
		const wrapper = document.createElement( 'div' );
		wrapper.className = 'ctc-repeater-wrapper' + ( field.class_pr ? ` ${field.class_pr}` : '' );
		applyConditionalAttributes( wrapper, field );

		const itemsContainer = document.createElement( 'div' );
		itemsContainer.className = field.container_class + ' ctc-repeater-items';

		// Include a hidden input as a remove marker if `data_remove` is specified.
		// Inert until RepeaterManager activates it (sets `name`) when the list empties;
		// SettingsManager then routes it onto the save payload's `remove` channel.
		if ( field.data_remove ) {
			const marker = document.createElement( 'input' );
			marker.type = 'hidden';
			marker.dataset.remove = field.data_remove;
			itemsContainer.appendChild( marker );
		}

		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = ( buttonClass ? `${buttonClass} ` : '' ) + 'ctc-repeater-add-btn ctc_repeater_add_button';

		// RepeaterManager will use these data attributes to handle add button clicks
		btn.dataset.callback = dataCallback;
		btn.dataset.callbackContainer = dataCallbackContainer;

		// eslint-disable-next-line no-unsanitized/property -- Static icon + safely escaped label
		btn.innerHTML = '<span class="dashicons dashicons-plus-alt2"></span><span>' + escapeHTML( field.label || 'Add Parameter' ) + '</span>';

		wrapper.appendChild( itemsContainer );
		wrapper.appendChild( btn );
		return wrapper;
	}

	const btnContainer = document.createElement( 'div' );
	if ( field.class_pr ) { btnContainer.className = field.class_pr; }

	applyConditionalAttributes( btnContainer, field );

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	btnContainer.innerHTML = `
        <button type="button"
            class="${escapeAttr( buttonClass )} ctc-repeater-add-btn ctc_repeater_add_button"
            data-callback="${escapeAttr( dataCallback )}"
            data-callback-container="${escapeAttr( dataCallbackContainer )}"
        ><span class="dashicons dashicons-plus-alt2"></span><span>${escapeHTML( field.label || 'Add Parameter' )}</span></button>
    `;
	return btnContainer;
};
