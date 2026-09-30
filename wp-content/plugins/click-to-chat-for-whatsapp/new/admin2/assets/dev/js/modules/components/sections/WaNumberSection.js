import { getNestedValue, applyConditionalAttributes, escapeHTML, escapeAttr } from '../../core/Utils.js';

export const createWaNumberSection = ( field, context, config ) => {
	const classPr = field.class_pr || '';
	const group = document.createElement( 'div' );

	group.className = `form-group ${classPr}`;

	applyConditionalAttributes( group, field );

	const optionGroupName = field.option_group;
	let value = getNestedValue( config.initialSettings, optionGroupName, field.id );
	if ( value === '' && field.default !== undefined ) { value = field.default; }

	const name = `${field.option_group}[${field.id}]`;

	const inputClass = field.class_field || 'intl_number';
	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values using Utils.escapeHTML
	group.innerHTML = `
        <label for="${escapeAttr( field.id )}">${escapeHTML( field.label || '' )}</label>
        <input type="text" id="${escapeAttr( field.id )}" name="${escapeAttr( name )}" data-name="${escapeAttr( name )}" class="${escapeAttr( inputClass )}" placeholder="${escapeAttr( field.placeholder || '' )}">
    `;

	const numberInput = group.querySelector( 'input' );
	if ( numberInput ) {
		numberInput.value = value;
	}

	if ( field.help ) {
		const helpP = document.createElement( 'p' );
		helpP.className = 'help-text';
		// eslint-disable-next-line no-unsanitized/property -- Help text defined in PHP can contain safe HTML formatting
		helpP.innerHTML = Array.isArray( field.help ) ? field.help.join( '<br>' ) : field.help;
		group.appendChild( helpP );
	}

	// Defer initialization to the next tick. This guarantees the input element
	// is fully appended to the active DOM tree before intl-tel-input runs.
	setTimeout( () => {
		if ( window.HTCtcAdminApp &&
			typeof window.HTCtcAdminApp.loadAndInitIntlInput === 'function' ) {
			window.HTCtcAdminApp.loadAndInitIntlInput( 'intl_number', group );
		}
	}, 0 );

	return group;
};
