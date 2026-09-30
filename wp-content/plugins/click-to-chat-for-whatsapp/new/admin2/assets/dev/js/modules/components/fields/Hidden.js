import { createBaseWrapper } from './BaseField.js';

export const renderHidden = ( field, config ) => {
	const { value, name, inputClass } = createBaseWrapper( field, config, '' );

	const input = document.createElement( 'input' );
	input.type = 'hidden';
	input.name = name;
	input.id = field.id;
	input.value = value;
	input.className = inputClass;

	return input;
};
