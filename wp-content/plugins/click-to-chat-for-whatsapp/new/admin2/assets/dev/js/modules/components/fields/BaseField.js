import { getNestedValue, applyConditionalAttributes } from '../../core/Utils.js';

/**
 * Creates the base wrapper HTML element and resolves standard field attributes (value, name, classes).
 * This eliminates the need to repeat DOM setup for every component.
 *
 * @param {Object} field - Configuration object for the field
 * @param {Object} config - App configuration containing initialSettings
 * @param {String} defaultWrapperClass - Default class for the wrapper (e.g., 'form-group')
 * @returns {Object} An object containing the built wrapper element and resolved properties
 */
export const createBaseWrapper = ( field, config, defaultWrapperClass = 'form-group' ) => {
	const wrapper = document.createElement( 'div' );
	const classPr = field.class_pr || '';
	const inputClass = field.class_field || '';

	wrapper.className = `${defaultWrapperClass} ${classPr}`.trim();
	if ( field.id ) { wrapper.id = `wrapper-${field.id}`; }

	// Apply conditional data attributes for dynamic show/hide
	applyConditionalAttributes( wrapper, field );

	// Resolve the current value from settings
	let value = '';
	let name = '';
	if ( field.option_group && field.id ) {
		value = getNestedValue( config.initialSettings, field.option_group, field.id );
		if ( ( value === '' || value === undefined ) && field.default !== undefined ) {
			value = field.default;
		}
		name = `${field.option_group}[${field.id}]`;
	}

	return {
		wrapper,
		value,
		name,
		inputClass,
		field, // Pass original field config through for convenience
	};
};

/**
 * Appends standard and toggleable help text to the field wrapper.
 *
 * @param {HTMLElement} wrapper - The DOM element to append help text to
 * @param {Object} field - Configuration object containing help text
 */
export const appendHelpText = ( wrapper, field ) => {
	if ( field.field_type === 'field_hidden' ) { return; }

	// Standard help text (always visible)
	if ( field.help ) {
		const helpP = document.createElement( 'p' );
		helpP.className = 'help-text';
		// eslint-disable-next-line no-unsanitized/property -- Help text is defined in PHP and can contain safe HTML like links
		helpP.innerHTML = Array.isArray( field.help ) ? field.help.join( '<br>' ) : field.help;
		wrapper.appendChild( helpP );
	}

	// Adaptive help text (help-click - toggleable)
	if ( field.help_click ) {
		const label = wrapper.querySelector( 'label' );
		if ( label ) {
			const helpToggle = document.createElement( 'button' );
			helpToggle.type = 'button';
			helpToggle.className = 'help-toggle';
			helpToggle.setAttribute( 'aria-label', 'Toggle more information' );
			helpToggle.innerHTML = '<span class="dashicons dashicons-editor-help"></span>';
			label.appendChild( helpToggle );
		}

		const helpP = document.createElement( 'p' );
		helpP.className = 'help-text help-click-text';
		// eslint-disable-next-line no-unsanitized/property -- Help text is defined in PHP and can contain safe HTML like links
		helpP.innerHTML = Array.isArray( field.help_click ) ? field.help_click.join( '<br>' ) : field.help_click;
		wrapper.appendChild( helpP );
	}
};
