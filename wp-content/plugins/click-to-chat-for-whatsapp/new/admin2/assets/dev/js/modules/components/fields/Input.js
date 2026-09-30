import { applyVariables, escapeHTML, escapeAttr, decodeHTML } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

export const renderInput = ( field, config ) => {
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	const type = ( field.field_type === 'field_number' ) ? 'number' : 'text';
	const placeholder = field.variables ? applyVariables( field.placeholder ) : field.placeholder || '';
	const minAttr = ( field.field_type === 'field_number' && typeof field.min !== 'undefined' ) ? ` min="${escapeAttr( field.min )}"` : '';
	const maxAttr = ( field.field_type === 'field_number' && typeof field.max !== 'undefined' ) ? ` max="${escapeAttr( field.max )}"` : '';

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <label for="${escapeAttr( field.id )}">${escapeHTML( field.label || '' )}</label>
        <input 
            type="${type}" 
            id="${escapeAttr( field.id )}" 
            name="${escapeAttr( name )}" 
            class="${escapeAttr( inputClass )}"
            placeholder="${escapeAttr( placeholder )}"
            ${minAttr}
            ${maxAttr}
        >
    `;

	const textInput = wrapper.querySelector( 'input' );
	if ( textInput ) {
		textInput.value = decodeHTML( value );
		textInput.placeholder = decodeHTML( placeholder );
	}

	appendHelpText( wrapper, field );

	return wrapper;
};
