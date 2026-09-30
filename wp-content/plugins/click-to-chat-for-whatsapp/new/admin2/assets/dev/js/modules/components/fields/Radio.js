import { escapeHTML, escapeAttr } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

export const renderRadio = ( field, config ) => {
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	const label = field.label || '';
	const isSegment = field.type === 'segment';

	let optionsHtml = '';
	if ( field.options ) {
		const options = Array.isArray( field.options ) ?
			field.options.reduce( ( acc, opt ) => ( { ...acc, [ opt ]: opt } ), {} ) :
			field.options;

		for ( const [ optValue, optLabel ] of Object.entries( options ) ) {
			const id = `${escapeAttr( field.id )}_${escapeAttr( optValue )}`;
			const isCheckedStr = ( String( value ) === String( optValue ) ) ? 'checked' : '';

			optionsHtml += `
            <label class="radio-option ${isSegment ? 'segment-option' : ''}">
                <input 
                    type="radio" 
                    id="${id}" 
                    name="${escapeAttr( name )}" 
                    class="${escapeAttr( inputClass )}" 
                    value="${escapeAttr( optValue )}" 
                    ${isCheckedStr}>
                <span>${escapeHTML( optLabel )}</span>
            </label>
            `;
		}
	}

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <div class="field-radio-wrapper ${isSegment ? 'segment-control' : ''}">
            ${label ? `<label class="field-label">${escapeHTML( label )}</label>` : ''}
            <div class="radio-options" ${isSegment ? `data-count="${Object.keys( field.options || {} ).length}"` : ''}>
                ${optionsHtml}
                ${isSegment ? '<div class="segment-glider"></div>' : ''}
            </div>
        </div>
    `;

	appendHelpText( wrapper, field );

	return wrapper;
};
