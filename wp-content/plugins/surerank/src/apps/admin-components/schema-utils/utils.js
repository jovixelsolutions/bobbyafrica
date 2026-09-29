import { Alert } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

export const noFieldsAlert = (
	<Alert
		content={ __(
			'This schema does not have any configurable fields',
			'surerank'
		) }
		className="shadow-none"
		variant="info"
	/>
);

export const generateUUID = ( length = 16 ) => {
	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
		/[xy]/g,
		function ( c ) {
			const r = Math.floor( Math.random() * length );
			const v = c === 'x' ? r : ( r % 4 ) + 8; // Replace bitwise operations
			return v.toString( 16 );
		}
	);
};

/**
 * Checks if a schema type exists and is valid in schemaTypeData
 *
 * @param {string} schemaTitle - The schema title/name to check
 * @return {boolean} - True if schema exists and has valid data
 */
export const isSchemaTypeValid = ( schemaTitle ) => {
	const schemaTypeData = surerank_globals?.schema_type_data || {};
	return (
		schemaTypeData[ schemaTitle ] &&
		Array.isArray( schemaTypeData[ schemaTitle ] ) &&
		schemaTypeData[ schemaTitle ].length > 0
	);
};

/**
 * Process fields and create default values structure
 *
 * @param {Array}   fieldsData   - Array of field configurations
 * @param {boolean} onlyDefaults - If true, only process fields with default=true or required=true
 * @return {Object} - Object with field IDs as keys and default values
 */
export const processFields = ( fieldsData, onlyDefaults = false ) => {
	return fieldsData.reduce( ( acc, field ) => {
		// Skip if onlyDefaults and field is not default/required
		if ( onlyDefaults && ! field.default && ! field.required ) {
			return acc;
		}

		if ( field.type === 'Group' && field.fields ) {
			if ( field.cloneable ) {
				// For cloneable groups, create an array with one default item
				const defaultItem = {};
				field.fields.forEach( ( subField ) => {
					if ( subField.type === 'Group' && subField.fields ) {
						const nestedGroup = {};
						subField.fields.forEach( ( nestedField ) => {
							nestedGroup[ nestedField.id ] =
								nestedField.std !== undefined
									? nestedField.std
									: '';
						} );
						defaultItem[ subField.id ] = nestedGroup;
					} else {
						defaultItem[ subField.id ] =
							subField.std !== undefined ? subField.std : '';
					}
				} );
				acc[ field.id ] = [ defaultItem ];
			} else {
				// For non-cloneable groups, process recursively
				const groupFields = {};
				field.fields.forEach( ( subField ) => {
					if ( subField.type === 'Group' && subField.fields ) {
						groupFields[ subField.id ] = processFields(
							subField.fields
						);
					} else {
						groupFields[ subField.id ] =
							subField.std !== undefined ? subField.std : '';
					}
				} );
				acc[ field.id ] = groupFields;
			}
		} else {
			acc[ field.id ] = field.std !== undefined ? field.std : '';
		}
		return acc;
	}, {} );
};

/**
 * Check if a field should be displayed based on parent dependencies
 *
 * @param {Object}   field         - The field configuration
 * @param {Function} getFieldValue - Function to get current field value
 * @return {boolean} - True if field should be shown
 */
export const shouldShowField = ( field, getFieldValue ) => {
	// Always show if no parent dependency
	if ( ! field.parent || ! field.parent_option ) {
		return true;
	}

	const parentValue = getFieldValue( field.parent );

	if ( Array.isArray( parentValue ) ) {
		return parentValue.includes( field.parent_option );
	}

	return parentValue === field.parent_option;
};

/**
 * Get fields that are available to be added (not currently in schema, not required, not hidden)
 *
 * @param {Array}  allFields      - All available fields for the schema type
 * @param {Object} existingFields - Currently active fields in the schema
 * @return {Array} - Fields that can be added
 */
export const getAvailableFields = ( allFields, existingFields ) => {
	return allFields.filter( ( field ) => {
		// Skip if already exists
		if (
			Object.prototype.hasOwnProperty.call( existingFields, field.id )
		) {
			return false;
		}

		// Skip required fields (always shown)
		if ( field.required ) {
			return false;
		}

		// Skip hidden fields
		if ( field.type === 'Hidden' || field.hidden ) {
			return false;
		}

		// Skip fields with parent dependency (conditional fields)
		if ( field.parent && field.parent_option ) {
			return false;
		}

		return true;
	} );
};

/**
 * Check if a field can be deleted (not required, not default)
 *
 * @param {Object} field - The field configuration
 * @return {boolean} - True if field can be deleted
 */
export const canDeleteField = ( field ) => {
	return ! field.required && ! field.default;
};

/**
 * Sort fields by priority: default/required first, parent-dependent middle, manually added last
 *
 * @param {Array}  fields         - Fields to sort
 * @param {Object} existingFields - Currently active fields
 * @return {Array} - Sorted fields
 */
export const sortFieldsByPriority = ( fields, existingFields ) => {
	const existingFieldKeys = Object.keys( existingFields );

	return fields.sort( ( a, b ) => {
		const getPriority = ( field ) => {
			const isDefaultOrRequired = field.default || field.required;
			const isParentDependent = field.parent && field.parent_option;
			const isManuallyAdded =
				existingFields[ field.id ] !== undefined &&
				! isDefaultOrRequired &&
				! isParentDependent;

			if ( isDefaultOrRequired ) {
				return 1; // Default/required fields come first
			}
			if ( isParentDependent ) {
				return 2; // Parent-dependent fields come in middle
			}
			if ( isManuallyAdded ) {
				return 3; // Manually added fields come last
			}
			return 2; // Other fields in middle
		};

		const aPriority = getPriority( a );
		const bPriority = getPriority( b );

		if ( aPriority !== bPriority ) {
			return aPriority - bPriority;
		}

		// If both are manually added, sort by insertion order
		if ( aPriority === 3 ) {
			return (
				existingFieldKeys.indexOf( a.id ) -
				existingFieldKeys.indexOf( b.id )
			);
		}

		return 0;
	} );
};
