import { escapeHTML, escapeAttr, applyDataAttributes } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

/**
 * Renders a button field.
 *
 * Two attribute buckets, distinguished by where they land:
 *   'attributes'        => on the field wrapper (label + button + help text)
 *   'button_attributes' => on the <button> itself
 *    (we could also call this 'field_attributes' but that would be confusing since the wrapper is also a field)
 *
 * Use `button_attributes` for anything click-related — a `data-action-onclick` out on
 * the wrapper would also fire when the label or help text is clicked, since Actions.js
 * resolves the handler with closest().
 *
 *   'field_type'        => 'field_button',
 *   'button_attributes' => array( 'data-action-onclick' => 'clearPluginFieldsLocalStorage' ),
 */
export const renderButton = ( field, config ) => {
	const { wrapper, inputClass } = createBaseWrapper( field, config, 'form-group' );

	const buttonClass = inputClass || 'button button-secondary';

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <div class="field-button-wrapper">
            ${field.label ? `<label class="field-label">${escapeHTML( field.label )}</label>` : ''}
            <button 
                type="button" 
                id="${escapeAttr( field.id )}" 
                class="${escapeAttr( buttonClass )}"
            >
                ${escapeHTML( field.button_text || 'Click Here' )}
            </button>
        </div>
    `;

	applyDataAttributes( wrapper.querySelector( 'button' ), field.button_attributes );

	appendHelpText( wrapper, field );

	return wrapper;
};
