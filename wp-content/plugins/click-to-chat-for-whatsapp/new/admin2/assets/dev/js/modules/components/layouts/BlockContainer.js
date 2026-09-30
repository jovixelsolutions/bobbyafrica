import { applyConditionalAttributes } from '../../core/Utils.js';

/**
 * field_type: block_container
 *
 * Creates a BlockContainer layout component.
 *
 * Layout Concept:
 * - A simple empty div container.
 * - Used to wrap custom HTML structures, placeholders, or elements that need specific IDs/classes.
 *
 * Example structure:
 * 'fields' => array(
 *     array(
 *         'field_type' => 'block_container',
 *         'id' => 'custom_container_id',
 *         'class_pr' => 'custom-class'
 *     )
 * )
 */
export const createBlockContainer = ( field ) => {
	const container = document.createElement( 'div' );
	const classPr = field.class_pr || '';

	if ( classPr ) { container.className = classPr; }
	if ( field.id ) { container.id = field.id; }

	applyConditionalAttributes( container, field );

	if ( field.data_remove ) {
		// Inert until RepeaterManager activates it (sets `name`) when the list empties;
		// SettingsManager then routes it onto the save payload's `remove` channel.
		const marker = document.createElement( 'input' );
		marker.type = 'hidden';
		marker.dataset.remove = field.data_remove;
		container.appendChild( marker );
	}

	return container;
};
