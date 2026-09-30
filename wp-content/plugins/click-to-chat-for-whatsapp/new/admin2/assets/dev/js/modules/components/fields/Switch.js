import { escapeHTML, escapeAttr } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

// Renders a boolean field as either a CSS toggle switch or a plain checkbox.
// field.type === 'switch' → styled toggle (label + slider); anything else → plain checkbox.
export const renderSwitch = ( field, config ) => {
	// 1. Get shared setup
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	// 2. Component specific logic
	const configValue = field.value || '1'; // fallback value attribute when unchecked
	// Treat '0' and 'false' as unchecked; everything else (including '1', 'yes', etc.) as checked
	const isDbChecked = ( value && String( value ) !== '0' && String( value ) !== 'false' );
	const isChecked = isDbChecked ? 'checked' : '';
	const inputValue = isChecked ? value : configValue;
	const label = field.label || '';

	// 'switch' → CSS toggle (checkbox + slider span); default → plain checkbox
	const isSwitch = field.type === 'switch';

	if ( isSwitch ) {
		// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
		wrapper.innerHTML = `
        <div class="field-switch-wrapper">
            <label for="${escapeAttr( field.id )}">${escapeHTML( label )}</label>
            <label class="switch">
                <input 
                    type="checkbox" 
                    id="${escapeAttr( field.id )}" 
                    name="${escapeAttr( name )}" 
                    class="${escapeAttr( inputClass )}">
                <span class="slider"></span>
            </label>
        </div>
        `;
	} else {
		// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
		wrapper.innerHTML = `
        <label for="${escapeAttr( field.id )}">
            <input 
                type="checkbox" 
                id="${escapeAttr( field.id )}" 
                name="${escapeAttr( name )}" 
                class="${escapeAttr( inputClass )}"
            >${escapeHTML( label )}
        </label>
        `;
	}

	// Set value safely via properties
	const checkboxInput = wrapper.querySelector( 'input[type="checkbox"]' );
	if ( checkboxInput ) {
		checkboxInput.value = inputValue;
		checkboxInput.checked = isDbChecked;
	}

	// 3. Append standard help texts
	appendHelpText( wrapper, field );

	return wrapper;
};
