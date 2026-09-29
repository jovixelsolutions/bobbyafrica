import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { Label, Alert, Accordion, Text } from '@bsf/force-ui';
import { Info, Trash } from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import WpSchemaProNotice from '@/global/components/wp-schema-pro-notice';
import { cn } from '@Functions/utils';
import {
	renderFieldCommon,
	renderFieldSwitch,
} from '@AdminComponents/schema-utils/render-helper';
import { AddFieldMenu } from '@AdminComponents/schema-utils/add-field-menu';
import { DeleteFieldButton } from '@AdminComponents/schema-utils/delete-field-button';
import {
	noFieldsAlert,
	generateUUID,
	isSchemaTypeValid,
	processFields,
	shouldShowField,
	getAvailableFields,
	canDeleteField,
	sortFieldsByPriority,
} from '@AdminComponents/schema-utils/utils';
import Modal from '@/apps/admin-general/schema/modal';
import { SeoPopupTooltip } from '@AdminComponents/tooltip';
import ConfirmationPopover from '@/global/components/confirmation-popover';

const SchemaTab = ( { postMetaData, globalDefaults, updatePostMetaData } ) => {
	const closeModal = () => setIsModalOpen( false );
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ selectedSchema, setSelectedSchema ] = useState( '' );
	const [ selectedType, setSelectedType ] = useState( '' );
	const [ expandedSchemaId, setExpandedSchemaId ] = useState( null );
	const [ fieldItemIds, setFieldItemIds ] = useState( {} );
	const isWpSchemaProActive = surerank_globals?.wp_schema_pro_active || false;

	const defaultSchemasObject = surerank_globals?.default_schemas || {};
	const defaultSchemas = Object.entries( defaultSchemasObject ).map(
		( [ id, schema ] ) => ( {
			id,
			...schema,
		} )
	);

	const globalSchemas = globalDefaults.schemas || {};
	const schemas = postMetaData.schemas
		? postMetaData.schemas
		: globalSchemas || {};

	const validSchemas = useMemo(
		() =>
			Object.entries( schemas ).filter( ( [ , schema ] ) => {
				return isSchemaTypeValid( schema?.title );
			} ),
		[ schemas ]
	);
	const schemaTypeData = surerank_globals?.schema_type_data || {};
	const schemaTypeOptions = surerank_globals?.schema_type_options || {};

	const variableSuggestions = useMemo(
		() =>
			Object.entries( surerank_globals?.schema_variables || {} ).map(
				( [ value, label ] ) => ( {
					value,
					label,
				} )
			),
		[]
	);

	const handleAddField = ( schemaId, fieldId ) => {
		const schemaTitle = schemas[ schemaId ]?.title;
		const allFields = schemaTypeData[ schemaTitle ] || [];
		const fieldToAdd = allFields.find( ( f ) => f.id === fieldId );

		if ( ! fieldToAdd ) {
			return;
		}

		let defaultValue = fieldToAdd.std !== undefined ? fieldToAdd.std : '';

		if ( fieldToAdd.type === 'Group' && fieldToAdd.fields ) {
			defaultValue = processFields( [ fieldToAdd ] )[ fieldToAdd.id ];
		}

		const updatedSchemas = {
			...schemas,
			[ schemaId ]: {
				...schemas[ schemaId ],
				fields: {
					...schemas[ schemaId ].fields,
					[ fieldId ]: defaultValue,
				},
			},
		};

		const cleanedSchemas = cleanSchemas( updatedSchemas );
		updatePostMetaData( {
			schemas: cleanedSchemas,
		} );
	};

	const handleDeleteField = ( schemaId, fieldId ) => {
		const updatedFields = { ...schemas[ schemaId ].fields };
		delete updatedFields[ fieldId ];

		const updatedSchemas = {
			...schemas,
			[ schemaId ]: {
				...schemas[ schemaId ],
				fields: updatedFields,
			},
		};

		const cleanedSchemas = cleanSchemas( updatedSchemas );
		updatePostMetaData( {
			schemas: cleanedSchemas,
		} );
	};

	const cleanSchemas = ( schemasData ) => {
		const cleanedSchemas = {};
		Object.entries( schemasData ).forEach( ( [ schemaId, schema ] ) => {
			const { show_on, not_show_on, ...rest } = schema; // we removed show_on and not_show_on, as we are using post meta data for schema now, cause we edited now.
			const cleanedSchema = {
				...rest,
				parent: true,
			};
			cleanedSchemas[ schemaId ] = cleanedSchema;
		} );
		return cleanedSchemas;
	};

	useEffect( () => {
		const updatedSchemas = {};
		Object.entries( schemas ).forEach( ( [ schemaId, schema ] ) => {
			const schemaFields = schemaTypeData[ schema.title ] || [];
			const existingFields = schema.fields || {};

			// Only initialize with default=true or required=true fields
			const defaultFields = processFields( schemaFields, true );
			const mergedFields = { ...defaultFields, ...existingFields };

			if ( Object.keys( existingFields ).length === 0 ) {
				mergedFields[ '@type' ] = schema?.type || '';
				updatedSchemas[ schemaId ] = {
					...schema,
					fields: mergedFields,
				};
			}
		} );

		if ( Object.keys( updatedSchemas ).length > 0 ) {
			const cleanedSchemas = cleanSchemas( {
				...schemas,
				...updatedSchemas,
			} );

			if (
				JSON.stringify( cleanedSchemas ) !== JSON.stringify( schemas )
			) {
				updatePostMetaData( {
					schemas: cleanedSchemas,
				} );
			}
		}
	}, [ schemas, schemaTypeData, updatePostMetaData ] );

	const handleDeleteSchema = ( schemaId ) => {
		const updatedSchemas = { ...schemas };
		delete updatedSchemas[ schemaId ];

		const cleanedSchemas = cleanSchemas( updatedSchemas );
		updatePostMetaData( {
			schemas: cleanedSchemas,
		} );
	};

	const handleAddSchema = () => {
		const schemaUniqueId = generateUUID();
		const newSchema = {
			title: selectedSchema,
			type: selectedType,
			show_on: {
				rules: [],
				specific: [],
				specificText: [],
			},
			fields: {},
		};

		const updatedSchemas = { ...schemas, [ schemaUniqueId ]: newSchema };
		const cleanedSchemas = cleanSchemas( updatedSchemas );

		updatePostMetaData( {
			schemas: cleanedSchemas,
		} );

		setExpandedSchemaId( schemaUniqueId );
		setIsModalOpen( false );
		setSelectedSchema( '' );
		setSelectedType( '' );
	};

	const handleFieldUpdate = ( schemaId, fieldKey, newValue ) => {
		const updatedSchemas = {
			...schemas,
			[ schemaId ]: {
				...schemas[ schemaId ],
				fields: {
					...schemas[ schemaId ].fields,
					[ fieldKey ]: newValue,
				},
			},
		};
		const cleanedSchemas = cleanSchemas( updatedSchemas );
		updatePostMetaData( {
			schemas: cleanedSchemas,
		} );
	};

	const getFieldValue = ( schemaId, fieldId ) => {
		return schemas[ schemaId ]?.fields?.[ fieldId ] || '';
	};

	const onFieldChange = ( schemaId, fieldId, newValue ) => {
		handleFieldUpdate( schemaId, fieldId, newValue );
		if ( fieldId === '@type' ) {
			const updatedSchemas = { ...schemas };
			updatedSchemas[ schemaId ].type =
				updatedSchemas[ schemaId ]?.title || ''; // added for backward compatibility.
			updatedSchemas[ schemaId ].fields[ '@type' ] = newValue;
			updatePostMetaData( {
				schemas: updatedSchemas,
			} );
		}
	};

	/**
	 * Render a single subfield within a non-cloneable Group field
	 *
	 * @param {Object} field    - The parent Group field configuration
	 * @param {Object} subField - The subfield to render
	 * @param {string} schemaId - The schema ID
	 * @return {JSX.Element|null} - Rendered subfield or null if hidden
	 */
	const renderGroupSubField = ( field, subField, schemaId ) => {
		if ( subField.type === 'Hidden' ) {
			return null;
		}

		const groupValue = getFieldValue( schemaId, field.id ) || {};

		const handleSubFieldChange = ( fldId, newVal ) => {
			const currentGroupValue = getFieldValue( schemaId, field.id ) || {};
			const updatedGroupValue = {
				...currentGroupValue,
				[ fldId ]: newVal,
			};
			onFieldChange( schemaId, field.id, updatedGroupValue );
		};

		return (
			<div
				key={ subField.id }
				className="flex flex-col items-start justify-start gap-1.5 w-full"
			>
				<div className="flex items-center gap-1.5">
					<Label tag="span" size="sm">
						{ subField.label }
					</Label>
					{ subField.tooltip && (
						<SeoPopupTooltip
							content={ subField.tooltip }
							placement="top"
							arrow
							className="z-[99999]"
						>
							<Info
								className="size-4 text-icon-secondary"
								title={ subField.tooltip }
							/>
						</SeoPopupTooltip>
					) }
				</div>
				<div className="flex items-center gap-1.5 w-full">
					{ renderFieldCommon( {
						field: subField,
						schemaType: schemas[ schemaId ].type,
						getFieldValue: ( fldId ) =>
							groupValue[ fldId ] || subField.std || '',
						onFieldChange: handleSubFieldChange,
						variableSuggestions,
						renderAsGroupComponent: false,
					} ) }
				</div>
			</div>
		);
	};

	/**
	 * Render a field based on its type and configuration
	 *
	 * @param {Object} field    - The field configuration
	 * @param {string} schemaId - The schema ID
	 * @return {JSX.Element} - Rendered field component
	 */
	const renderField = ( field, schemaId ) => {
		if ( field.type === 'Group' && ! field.cloneable ) {
			return (
				<div className="flex flex-col w-full space-y-3 border border-border-subtle rounded-lg p-3">
					{ field.fields.map( ( subField ) =>
						renderGroupSubField( field, subField, schemaId )
					) }
				</div>
			);
		}

		return renderFieldSwitch( field, {
			schemaId,
			schemaType: schemas[ schemaId ].type,
			getFieldValue: ( fieldId ) => getFieldValue( schemaId, fieldId ),
			onFieldChange: ( fieldId, newVal ) =>
				onFieldChange( schemaId, fieldId, newVal ),
			variableSuggestions,
			fieldItemIds,
			setFieldItemIds,
			renderAsGroupComponent: true,
		} );
	};

	const renderSchemaFields = ( schemaId ) => {
		const schemaTitle = schemas[ schemaId ]?.title;

		// Check if schema type is valid first
		if ( ! isSchemaTypeValid( schemaTitle ) ) {
			return noFieldsAlert;
		}

		const allSchemaFields = schemaTypeData[ schemaTitle ] || [];
		const existingFields = schemas[ schemaId ]?.fields || {};

		if (
			allSchemaFields.length === 0 ||
			allSchemaFields.every(
				( field ) =>
					field.type === 'Hidden' || field.id === 'schema_name'
			)
		) {
			return noFieldsAlert;
		}

		// Filter fields to show: must exist in existingFields OR be required OR have parent dependency
		const filteredFields = allSchemaFields.filter( ( field ) => {
			if ( field.type === 'Hidden' || field.type === 'SchemaDocs' ) {
				return false;
			}

			// Show if field exists in the schema
			if (
				Object.prototype.hasOwnProperty.call( existingFields, field.id )
			) {
				return true;
			}

			// Show if required
			if ( field.required ) {
				return true;
			}

			// Show if has parent dependency that is active
			if ( field.parent && field.parent_option ) {
				return shouldShowField( field, ( fieldId ) =>
					getFieldValue( schemaId, fieldId )
				);
			}

			return false;
		} );

		const fieldsToShow = sortFieldsByPriority(
			filteredFields,
			existingFields
		);

		const availableFields = getAvailableFields(
			allSchemaFields,
			existingFields
		);

		return (
			<>
				{ fieldsToShow.map( ( field ) => {
					if ( field.parent && field.parent_option ) {
						const shouldShow = shouldShowField(
							field,
							( fieldId ) => getFieldValue( schemaId, fieldId )
						);
						if ( ! shouldShow ) {
							return null;
						}
					}

					const canDelete = canDeleteField( field );

					return (
						<div
							key={ field.id }
							className="flex flex-col items-start justify-start gap-1.5 w-full p-1"
						>
							{ /* Label + tooltip + delete button */ }
							<div className="flex items-center justify-between gap-1.5 w-full">
								<div className="flex items-center gap-1.5">
									<Label tag="span" size="sm">
										{ field.label }
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
								{ canDelete && (
									<DeleteFieldButton
										onDelete={ () =>
											handleDeleteField(
												schemaId,
												field.id
											)
										}
									/>
								) }
							</div>

							{ /* Field render */ }
							{ renderField( field, schemaId ) }
						</div>
					);
				} ) }

				{ applyFilters( 'surerank.schema.properties.extensions', null, {
					schemaId,
					schemaType: schemas[ schemaId ]?.type || schemaTitle,
					schema: schemaTitle,
					metaSettings: { schemas },
					currentSchema: schemas[ schemaId ] || {},
					setMetaSetting: ( key, value ) => {
						if ( key === 'schemas' ) {
							updatePostMetaData( { schemas: value } );
						}
					},
					variableSuggestions,
					getFieldValue: ( fieldId ) =>
						getFieldValue( schemaId, fieldId ),
					onFieldChange: ( fieldId, newValue ) =>
						onFieldChange( schemaId, fieldId, newValue ),
				} ) }

				<AddFieldMenu
					availableFields={ availableFields }
					onAddField={ ( fieldId ) =>
						handleAddField( schemaId, fieldId )
					}
					className="p-2 w-full border-t border-border-subtle mt-2"
					filterContext={ {
						schemaId,
						schemaType: schemas[ schemaId ]?.type || schemaTitle,
						schema: schemaTitle,
						metaSettings: { schemas },
						currentSchema: schemas[ schemaId ] || {},
						setMetaSetting: ( key, value ) => {
							if ( key === 'schemas' ) {
								updatePostMetaData( { schemas: value } );
							}
						},
					} }
				/>
			</>
		);
	};

	if ( isWpSchemaProActive ) {
		return <WpSchemaProNotice />;
	}

	return (
		<div className="pt-2 gap-2">
			<div className="flex items-center mb-4.5 -mt-0.5">
				<Text size={ 14 } weight={ 500 } color="label">
					{ __( 'Schemas in Use', 'surerank' ) }
				</Text>
			</div>
			<div
				className={ cn(
					'w-full bg-background-secondary flex flex-col items-center justify-center rounded p-1'
				) }
			>
				{ validSchemas.length > 0 ? (
					<Accordion
						type="simple"
						iconType="arrow"
						className="w-full space-y-1"
						autoClose={ false }
					>
						{ validSchemas.map( ( [ schemaId, schema ] ) => {
							return (
								<Accordion.Item
									key={ schemaId }
									value={ schemaId }
									className="bg-background-primary rounded-md"
									defaultExpanded={
										schemaId === expandedSchemaId
									}
								>
									<Accordion.Trigger
										iconType="arrow"
										className="hover:bg-background-primary rounded-md flex justify-between items-center [&>div]:w-full p-2 gap-2 [&>svg]:size-4 cursor-pointer"
									>
										<span className="text-base font-normal text-text-primary leading-6 ml-1">
											{ schema.title }
										</span>
										<ConfirmationPopover
											onConfirm={ () =>
												handleDeleteSchema( schemaId )
											}
											placement="bottom"
											offset={ {
												mainAxis: 8,
												crossAxis: -28,
											} }
										>
											<div
												className="inline-flex ml-auto"
												role="button"
												tabIndex={ 0 }
											>
												<Trash className="size-3.5 text-icon-secondary cursor-pointer" />
											</div>
										</ConfirmationPopover>
									</Accordion.Trigger>
									<Accordion.Content>
										<div className="mt-3 space-y-4">
											{ renderSchemaFields( schemaId ) }
										</div>
									</Accordion.Content>
								</Accordion.Item>
							);
						} ) }
					</Accordion>
				) : (
					<Alert
						className="w-full shadow-none"
						content={ __( 'No schemas configured.', 'surerank' ) }
						variant="info"
					/>
				) }
			</div>
			<div className="w-full mt-6 rounded">
				<Modal
					selectedSchema={ selectedSchema }
					setSelectedSchema={ setSelectedSchema }
					selectedType={ selectedType }
					setSelectedType={ setSelectedType }
					schemaTypeOptions={ schemaTypeOptions }
					defaultSchemas={ defaultSchemas }
					handleAddSchema={ handleAddSchema }
					isModalOpen={ isModalOpen }
					closeModal={ closeModal }
				/>
			</div>
		</div>
	);
};

export default SchemaTab;
