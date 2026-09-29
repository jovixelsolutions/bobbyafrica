import { useState, useEffect, useMemo } from '@wordpress/element';
import { useSuspenseSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from '@AdminStore/constants';
import { Label } from '@bsf/force-ui';
import { Info } from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import {
	renderHelpText,
	renderFieldSwitch,
} from '@AdminComponents/schema-utils/render-helper';
import {
	noFieldsAlert,
	processFields,
	getAvailableFields,
	canDeleteField,
	sortFieldsByPriority,
} from '@AdminComponents/schema-utils/utils';
import { AddFieldMenu } from '@AdminComponents/schema-utils/add-field-menu';
import { DeleteFieldButton } from '@AdminComponents/schema-utils/delete-field-button';
import { SeoPopupTooltip } from '@AdminComponents/tooltip';
import {
	widthToTailwindClass,
	groupFieldsIntoRows,
} from '@AdminComponents/schema-utils/layout-utils';
import { cn } from '@/functions/utils';

const Properties = ( { schema, type, handleFieldUpdate, schemaId } ) => {
	const { setMetaSetting } = useDispatch( STORE_NAME );
	const [ schemaType, setSchemaType ] = useState( type );
	const [ fieldItemIds, setFieldItemIds ] = useState( {} );
	const { metaSettingsObject } = useSuspenseSelect( ( select ) => {
		const { getMetaSettings } = select( STORE_NAME );
		return {
			metaSettingsObject: getMetaSettings() || { schemas: {} },
		};
	}, [] );

	const metaSettings = {
		schemas: metaSettingsObject.schemas || {},
	};

	const schemaTypeData = surerank_globals?.schema_type_data || {};

	const [ fields, setFields ] = useState( [] );

	// Initialize schema if missing - only with default fields
	useEffect( () => {
		if ( schemaTypeData[ schema ] ) {
			const currentSchema = metaSettings.schemas[ schemaId ] || {};
			const existingFields = currentSchema.fields || {};
			const schemaFields = Array.isArray( schemaTypeData[ schema ] )
				? schemaTypeData[ schema ]
				: [];
			// Only initialize with default=true or required=true fields
			const defaultFields = processFields( schemaFields, true );
			const mergedFields = { ...defaultFields, ...existingFields };

			if ( Object.keys( existingFields ).length === 1 ) {
				setMetaSetting( 'schemas', {
					...metaSettings.schemas,
					[ schemaId ]: {
						...currentSchema,
						type,
						title: schema,
						fields: mergedFields,
						show_on: currentSchema.show_on || {
							rules: [],
							specific: [],
							specificText: [],
						},
					},
				} );
			}
		}
	}, [
		schema,
		schemaId,
		schemaTypeData,
		metaSettings.schemas,
		setMetaSetting,
		type,
	] );

	// Add a field to the schema
	const addField = ( fieldId ) => {
		const currentSchema = metaSettings.schemas[ schemaId ] || {};
		const existingFields = currentSchema.fields || {};
		const allFields = schemaTypeData[ schema ] || [];
		const fieldToAdd = allFields.find( ( f ) => f.id === fieldId );

		if ( ! fieldToAdd ) {
			return;
		}

		// Get default value for the field
		let defaultValue = fieldToAdd.std !== undefined ? fieldToAdd.std : '';

		// Handle Group fields
		if ( fieldToAdd.type === 'Group' && fieldToAdd.fields ) {
			defaultValue = processFields( [ fieldToAdd ] )[ fieldToAdd.id ];
		}

		const updatedFields = {
			...existingFields,
			[ fieldId ]: defaultValue,
		};

		setMetaSetting( 'schemas', {
			...metaSettings.schemas,
			[ schemaId ]: {
				...currentSchema,
				fields: updatedFields,
			},
		} );
	};

	// Delete a field from the schema
	const deleteField = ( fieldId ) => {
		const currentSchema = metaSettings.schemas[ schemaId ] || {};
		const existingFields = currentSchema.fields || {};

		const updatedFields = { ...existingFields };
		delete updatedFields[ fieldId ];

		setMetaSetting( 'schemas', {
			...metaSettings.schemas,
			[ schemaId ]: {
				...currentSchema,
				fields: updatedFields,
			},
		} );
	};

	// Update fields to render based on schema type
	useEffect( () => {
		if ( schemaTypeData[ schema ] ) {
			const currentSchema = metaSettings.schemas[ schemaId ] || {};
			const existingFields = currentSchema.fields || {};
			const schemaFields = Array.isArray( schemaTypeData[ schema ] )
				? schemaTypeData[ schema ]
				: [];
			const filteredFields = schemaFields.filter(
				( field ) =>
					existingFields[ field.id ] !== undefined ||
					field.required ||
					( field.parent && field.parent_option )
			);

			// Sort fields using shared utility
			const sortedFields = sortFieldsByPriority(
				filteredFields,
				existingFields
			);

			setFields( sortedFields );
		}
	}, [ schema, schemaId, schemaTypeData, metaSettings.schemas ] );

	// We can wrap your original handleUpdate logic here:
	const getFieldValue = ( fieldId, parent = null ) => {
		if ( ! parent ) {
			return metaSettings.schemas[ schemaId ]?.fields?.[ fieldId ] || '';
		}

		return (
			metaSettings.schemas[ schemaId ]?.fields?.[ parent ]?.[ fieldId ] ||
			''
		);
	};

	const onFieldChange = ( fieldId, newValue, parent = null ) => {
		handleFieldUpdate( fieldId, newValue );

		const currentSchema = metaSettings.schemas[ schemaId ] || {};
		const existingFields = currentSchema.fields || {};

		const updatedFields = { ...existingFields };

		if ( parent ) {
			const groupFields =
				schemaTypeData[ schema ].find( ( f ) => f.id === parent )
					?.fields || [];
			const groupType =
				fieldId === '@type'
					? newValue
					: getFieldValue( '@type', parent );
			const filteredGroupFields = groupFields.reduce( ( acc, field ) => {
				// Include field if it has no 'main' or matches the selected groupType
				if ( ! field.main || field.main === groupType ) {
					acc[ field.id ] =
						existingFields[ parent ]?.[ field.id ] ||
						field.std ||
						'';
				}
				return acc;
			}, {} );

			// Update only the relevant field
			filteredGroupFields[ fieldId ] = newValue;

			updatedFields[ parent ] = filteredGroupFields;
		} else {
			updatedFields[ fieldId ] = newValue;

			/**
			 * Cleanup logic for any parent field - remove dependent fields when parent options are removed
			 * Here fields will be removed if there is not any parent option selected. ( from meta settings )
			 */
			const allFields = schemaTypeData[ schema ] || [];
			const hasDependentFields = allFields.some(
				( field ) => field.parent === fieldId
			);

			if ( hasDependentFields ) {
				const oldValue = getFieldValue( fieldId ) || [];
				const newValueArray = Array.isArray( newValue ) ? newValue : [];
				const oldValueArray = Array.isArray( oldValue ) ? oldValue : [];

				const removedOptions = oldValueArray.filter(
					( option ) => ! newValueArray.includes( option )
				);

				let optionsToRemove = removedOptions;
				if (
					! Array.isArray( newValue ) &&
					! Array.isArray( oldValue ) &&
					oldValue !== newValue
				) {
					/**
					 * Single value field changed, remove dependencies of the old value
					 */
					optionsToRemove = oldValue ? [ oldValue ] : [];
				}

				if ( optionsToRemove.length > 0 ) {
					const removedOptionSet = new Set( optionsToRemove );
					allFields.forEach( ( field ) => {
						if (
							field.parent === fieldId &&
							removedOptionSet.has( field.parent_option )
						) {
							delete updatedFields[ field.id ];
						}
					} );
				}
			}
		}

		setMetaSetting( 'schemas', {
			...metaSettings.schemas,
			[ schemaId ]: {
				...currentSchema,
				fields: updatedFields,
			},
		} );

		if ( fieldId === '@type' ) {
			setSchemaType( newValue );
		}
	};

	const hiddenFields = fields.filter(
		( field ) => field.type === 'Hidden' || field.hidden
	);
	const visibleFields = fields.filter(
		( field ) => ! hiddenFields.includes( field )
	);

	// Filter fields that should be shown based on conditional logic
	// Must be before early return to satisfy React Hooks rules
	const fieldsToRender = useMemo( () => {
		return visibleFields.filter( ( field ) => {
			let shouldShowField = field.required || field.show;

			/**
			 * Check if field should be shown based on parent/parent_option conditional logic
			 */
			if ( field.parent && field.parent_option ) {
				const parentValue = getFieldValue( field.parent );
				if ( Array.isArray( parentValue ) ) {
					shouldShowField = parentValue.includes(
						field.parent_option
					);
				} else {
					shouldShowField = parentValue === field.parent_option;
				}
			}

			return shouldShowField;
		} );
	}, [ visibleFields, metaSettings.schemas, schemaId ] );

	// Group fields into rows based on width - memoized for performance
	const rows = useMemo(
		() => groupFieldsIntoRows( fieldsToRender ),
		[ fieldsToRender ]
	);

	// Memoize variable suggestions since surerank_globals is static
	const variableSuggestions = useMemo(
		() =>
			Object.entries( surerank_globals?.schema_variables || {} ).map(
				( [ value, label ] ) => ( { value, label } )
			),
		[]
	);

	if ( visibleFields.length === 0 ) {
		return noFieldsAlert;
	}

	// Get available fields for the "Add Field" dropdown
	const currentSchema = metaSettings.schemas[ schemaId ] || {};
	const existingFields = currentSchema.fields || {};
	const allSchemaFields = Array.isArray( schemaTypeData[ schema ] )
		? schemaTypeData[ schema ]
		: [];
	const availableFields = getAvailableFields(
		allSchemaFields,
		existingFields
	);

	const renderFieldInput = ( field ) => {
		return renderFieldSwitch( field, {
			schemaId,
			schemaType,
			getFieldValue,
			onFieldChange,
			variableSuggestions,
			fieldItemIds,
			setFieldItemIds,
			renderAsGroupComponent: true,
		} );
	};

	return (
		<div className="space-y-4 w-full">
			{ rows.map( ( row, rowIndex ) => (
				<div
					key={ `row-${ rowIndex }` }
					className="grid grid-cols-12 gap-4 w-full"
				>
					{ row.map( ( field ) => (
						<div
							key={ field.id }
							className={ cn(
								'space-y-1.5 p-2',
								widthToTailwindClass( field.width || 'full' )
							) }
						>
							{ /* Label row */ }
							<div className="flex items-center justify-between gap-1.5 w-full">
								<div className="flex items-center gap-1.5">
									<Label
										tag="span"
										size="sm"
										className="space-x-0.5"
									>
										<span>{ field.label }</span>
									</Label>
									{ field.tooltip && (
										<SeoPopupTooltip
											content={ field.tooltip }
											placement="top"
											arrow
											className="z-[99999]"
										>
											<Info
												className="size-4 text-icon-secondary"
												title={ field.tooltip }
											/>
										</SeoPopupTooltip>
									) }
								</div>
								{ canDeleteField( field ) && (
									<DeleteFieldButton
										onDelete={ () =>
											deleteField( field.id )
										}
									/>
								) }
							</div>

							{ /* Field input row */ }
							{ renderFieldInput( field ) }
							{ renderHelpText( field ) }
						</div>
					) ) }
				</div>
			) ) }

			{ applyFilters( 'surerank.schema.properties.extensions', null, {
				schemaId,
				schemaType,
				schema,
				metaSettings,
				currentSchema: metaSettings.schemas[ schemaId ] || {},
				setMetaSetting,
				variableSuggestions,
				getFieldValue,
				onFieldChange,
			} ) }

			<AddFieldMenu
				availableFields={ availableFields }
				onAddField={ addField }
				className="p-2 w-full border-t border-border-subtle"
				filterContext={ {
					schemaId,
					schemaType,
					schema,
					metaSettings,
					currentSchema: metaSettings.schemas[ schemaId ] || {},
					setMetaSetting,
				} }
			/>
		</div>
	);
};

export default Properties;
