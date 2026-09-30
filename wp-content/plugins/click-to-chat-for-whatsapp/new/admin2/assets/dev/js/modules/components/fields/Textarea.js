import { escapeHTML, escapeAttr } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

export const renderTextarea = ( field, config ) => {
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	const textareaValue = ( value == null ? '' : String( value ) ).replace( /<\/(textarea)/gi, '&lt;/$1' );

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <label for="${escapeAttr( field.id )}">${escapeHTML( field.label || '' )}</label>
        <textarea id="${escapeAttr( field.id )}" name="${escapeAttr( name )}" class="${escapeAttr( inputClass )}" rows="${escapeAttr( String( field.rows || 4 ) )}" placeholder="${escapeAttr( field.placeholder || '' )}">${textareaValue}</textarea>
    `;

	appendHelpText( wrapper, field );

	return wrapper;
};
