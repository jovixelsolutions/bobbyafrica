import { __ } from '@wordpress/i18n';
import {
	Select,
	EditorInput,
	Button,
	Label,
	Input,
	Text,
	DatePicker,
} from '@bsf/force-ui';
import {
	editorValueToString,
	stringValueToFormatJSON,
	cn,
} from '@Functions/utils';
import { Trash, Plus, Info, Calendar } from 'lucide-react';
import { generateUUID } from '@AdminComponents/schema-utils/utils';
import { SeoPopupTooltip } from '@AdminComponents/tooltip';
import { useState, useEffect, useRef, useMemo } from '@wordpress/element';
import { widthToTailwindClass, groupFieldsIntoRows } from './layout-utils';

const WORD_BREAK_ALL_EDITOR_INPUT = [ 'url', 'logo' ];
const STYLES_OVERRIDE_FOR_EDITOR_INPUT = {
	wordBreak: 'break-all',
};

const isFieldVisible = ( field ) => ! field.hidden && field.type !== 'Hidden';

const flattenOptions = ( options ) => {
	if ( ! options ) {
		return {};
	}
	return Array.isArray( options )
		? options.reduce( ( acc, group ) => {
				if ( group.options ) {
					return { ...acc, ...group.options };
				}
				return acc;
		  }, {} )
		: options;
};

const createDefaultItem = ( fields ) => {
	const defaultItem = {};
	fields.forEach( ( subField ) => {
		if ( subField.type === 'Group' && subField.fields ) {
			const nestedGroup = {};
			subField.fields.forEach( ( nestedField ) => {
				nestedGroup[ nestedField.id ] = nestedField.std || '';
			} );
			defaultItem[ subField.id ] = nestedGroup;
		} else {
			defaultItem[ subField.id ] = subField.std || '';
		}
	} );
	return defaultItem;
};

const renderFieldLabel = ( field ) => (
	<div className="flex items-center justify-start gap-1.5 w-full">
		<Label tag="span" size="sm" className="space-x-0.5">
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
);

// Custom DateTime component with DatePicker
const DateTimeField = ( {
	field,
	currentValue,
	onFieldChange,
	placeholder,
	variableSuggestions,
} ) => {
	const [ isDatePickerOpen, setIsDatePickerOpen ] = useState( false );
	const [ keyCounter, setKeyCounter ] = useState( 0 );
	const containerRef = useRef( null );

	// Convert selected date to ISO string
	const formatForOutput = ( selectedDate ) => {
		if ( ! selectedDate ) {
			return '';
		}
		try {
			const date = new Date( selectedDate );
			if ( isNaN( date.getTime() ) ) {
				return '';
			}
			return date.toISOString();
		} catch ( error ) {
			return '';
		}
	};

	// Convert ISO string to user-friendly display format
	const formatForDisplay = ( isoString ) => {
		if ( ! isoString ) {
			return '';
		}
		try {
			const date = new Date( isoString );
			if ( isNaN( date.getTime() ) ) {
				return '';
			}
			return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
		} catch ( error ) {
			return '';
		}
	};

	const handleDateApply = ( selectedDate ) => {
		onFieldChange( field.id, formatForOutput( selectedDate ) );
		setIsDatePickerOpen( false );
		// Force EditorInput to re-render with new value
		setKeyCounter( ( prev ) => prev + 1 );
	};

	const handleDateCancel = () => {
		setIsDatePickerOpen( false );
	};

	useEffect( () => {
		function handleClickOutside( event ) {
			if (
				isDatePickerOpen &&
				containerRef.current &&
				! containerRef.current.contains( event.target )
			) {
				setIsDatePickerOpen( false );
			}
		}

		// Bind the event listener
		document.addEventListener( 'mousedown', handleClickOutside );
		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ isDatePickerOpen ] );

	return (
		<div ref={ containerRef } className="w-full relative">
			<div className="flex items-center gap-2 w-full">
				<EditorInput
					key={ `${ field.id }-${ keyCounter }` }
					by="label"
					trigger="@"
					options={ variableSuggestions }
					placeholder={ placeholder }
					defaultValue={ stringValueToFormatJSON(
						// Show formatted date if it's an ISO string, otherwise show as-is for variables
						currentValue &&
							! currentValue.startsWith( '%' ) &&
							! currentValue.includes( '@' )
							? formatForDisplay( currentValue )
							: currentValue,
						variableSuggestions
					) }
					onChange={ ( editorState ) =>
						onFieldChange(
							field.id,
							editorValueToString( editorState.toJSON() ) !== ''
								? editorValueToString( editorState.toJSON() )
								: ''
						)
					}
					aria-label={ field.label }
					className="flex-grow max-w-full"
					size="md"
					style={
						WORD_BREAK_ALL_EDITOR_INPUT.includes( field.id )
							? STYLES_OVERRIDE_FOR_EDITOR_INPUT
							: {}
					}
				/>
				<Button
					variant="ghost"
					size="md"
					onClick={ () => setIsDatePickerOpen( ( prev ) => ! prev ) }
					className="flex-shrink-0"
					aria-label={ __( 'Open date picker', 'surerank' ) }
					icon={
						<Calendar
							strokeWidth={ 1.5 }
							className="text-icon-secondary"
						/>
					}
				/>
			</div>
			{ isDatePickerOpen && (
				<div className="absolute z-10 mb-2 rounded-lg shadow-lg right-0 bg-background-primary bottom-full">
					<DatePicker
						applyButtonText={ __( 'Apply', 'surerank' ) }
						cancelButtonText={ __( 'Cancel', 'surerank' ) }
						selectionType="single"
						showOutsideDays={ false }
						variant="normal"
						onApply={ handleDateApply }
						onCancel={ handleDateCancel }
						selected={
							currentValue && ! currentValue.startsWith( '%' )
								? new Date( currentValue )
								: null
						}
					/>
				</div>
			) }
		</div>
	);
};

// Common function to render cloneable group fields with stable ID management
export const renderCloneableGroupField = ( {
	field,
	schemaId,
	getFieldValue,
	onFieldChange,
	variableSuggestions,
	fieldItemIds,
	setFieldItemIds,
	renderHelpTextFunction = null,
} ) => {
	let existingValues = getFieldValue( field.id ) || [];

	// Ensure existingValues is always an array
	if ( ! Array.isArray( existingValues ) ) {
		if ( typeof existingValues === 'object' && existingValues !== null ) {
			existingValues = Object.values( existingValues );
		} else {
			existingValues = [];
		}
	}

	// Ensure at least one empty item exists
	if ( existingValues.length === 0 ) {
		existingValues = [ createDefaultItem( field.fields ) ];
	}

	// Ensure all nested groups have their required fields (like @type)
	existingValues = existingValues.map( ( item ) => {
		const updatedItem = { ...item };
		field.fields.forEach( ( subField ) => {
			if ( subField.type === 'Group' && subField.fields ) {
				// Make sure the nested group exists
				if (
					! updatedItem[ subField.id ] ||
					typeof updatedItem[ subField.id ] !== 'object'
				) {
					updatedItem[ subField.id ] = {};
				}

				// Ensure all required fields exist in the nested group
				subField.fields.forEach( ( nestedField ) => {
					if (
						nestedField.required &&
						updatedItem[ subField.id ][ nestedField.id ] ===
							undefined
					) {
						updatedItem[ subField.id ][ nestedField.id ] =
							nestedField.std || '';
					}
				} );
			}
		} );
		return updatedItem;
	} );

	// Generate stable IDs for this field's items
	const fieldKey = `${ schemaId }-${ field.id }`;
	if (
		! fieldItemIds[ fieldKey ] ||
		fieldItemIds[ fieldKey ].length !== existingValues.length
	) {
		const newIds = existingValues.map(
			( _, index ) =>
				fieldItemIds[ fieldKey ]?.[ index ] ||
				`item-${ Date.now() }-${ index }-${ Math.random()
					.toString( 36 )
					.substr( 2, 9 ) }`
		);
		setFieldItemIds( ( prev ) => ( {
			...prev,
			[ fieldKey ]: newIds,
		} ) );
	}

	const currentIds = fieldItemIds[ fieldKey ] || [];
	const itemsWithIds = existingValues.map( ( item, index ) => ( {
		...item,
		_id: currentIds[ index ] || `temp-${ index }`,
	} ) );

	const handleAddNewItem = () => {
		const newItem = createDefaultItem( field.fields );
		const updatedValues = [ ...existingValues, newItem ];
		const newId = `item-${ Date.now() }-${
			existingValues.length
		}-${ Math.random().toString( 36 ).substr( 2, 9 ) }`;

		setFieldItemIds( ( prev ) => ( {
			...prev,
			[ fieldKey ]: [ ...( prev[ fieldKey ] || [] ), newId ],
		} ) );

		onFieldChange( field.id, updatedValues );
	};

	const handleRemoveItem = ( index ) => {
		const updatedValues = existingValues.filter( ( _, i ) => i !== index );
		const updatedIds = currentIds.filter( ( _, i ) => i !== index );

		setFieldItemIds( ( prev ) => ( {
			...prev,
			[ fieldKey ]: updatedIds,
		} ) );

		onFieldChange( field.id, updatedValues );
	};

	const handleItemFieldChange = ( itemIndex, fieldId, value ) => {
		const updatedValues = [ ...existingValues ];
		updatedValues[ itemIndex ] = {
			...updatedValues[ itemIndex ],
			[ fieldId ]: value,
		};
		onFieldChange( field.id, updatedValues );
	};

	return (
		<>
			{ itemsWithIds.map( ( item, index ) => (
				<div key={ item._id } className="rounded-lg mb-4 space-y-1">
					<div className="flex items-center justify-between">
						<Text
							size={ 14 }
							lineHeight={ 20 }
							weight={ 500 }
							className="text-text-primary py-2"
						>
							{ field.cloneItemHeading
								? `${ field.cloneItemHeading } ${ index + 1 }`
								: `Item ${ index + 1 }` }
						</Text>
						{ itemsWithIds.length > 1 && (
							<Button
								variant="ghost"
								size="sm"
								onClick={ () => handleRemoveItem( index ) }
								icon={
									<Trash
										strokeWidth={ 1.5 }
										className="text-icon-secondary"
									/>
								}
							/>
						) }
					</div>

					<div className="grid grid-cols-12 gap-4 w-full">
						{ field.fields.map( ( subField ) => {
							if ( ! isFieldVisible( subField ) ) {
								return null;
							}

							// Handle nested Group fields
							if (
								subField.type === 'Group' &&
								subField.fields
							) {
								return (
									<div
										key={ subField.id }
										className={ cn(
											'space-y-2',
											widthToTailwindClass(
												subField.width || 'full'
											)
										) }
									>
										{ subField.label &&
											renderFieldLabel( subField ) }
										<div className="grid grid-cols-12 gap-4 w-full">
											{ subField.fields.map(
												( nestedField ) => {
													if (
														! isFieldVisible(
															nestedField
														)
													) {
														return null;
													}

													return (
														<div
															key={
																nestedField.id
															}
															className={ cn(
																'space-y-1.5',
																widthToTailwindClass(
																	nestedField.width ||
																		'full'
																)
															) }
														>
															{ renderFieldLabel(
																nestedField
															) }
															<div className="flex items-center justify-start gap-1.5 w-full">
																{ renderFieldCommon(
																	{
																		field: {
																			...nestedField,
																			id: nestedField.id,
																		},
																		getFieldValue:
																			() => {
																				const groupValue =
																					item[
																						subField
																							.id
																					] ||
																					{};
																				return (
																					groupValue[
																						nestedField
																							.id
																					] ||
																					nestedField.std ||
																					''
																				);
																			},
																		onFieldChange:
																			(
																				fieldId,
																				value
																			) => {
																				const currentGroupValue =
																					item[
																						subField
																							.id
																					] ||
																					{};
																				const updatedGroupValue =
																					{
																						...currentGroupValue,
																						[ fieldId ]:
																							value,
																					};
																				handleItemFieldChange(
																					index,
																					subField.id,
																					updatedGroupValue
																				);
																			},
																		variableSuggestions,
																		renderAsGroupComponent: false,
																		itemIndex:
																			index,
																		parentFieldId:
																			field.id,
																	}
																) }
															</div>
															{ renderHelpTextFunction &&
																renderHelpTextFunction(
																	nestedField
																) }
														</div>
													);
												}
											) }
										</div>
										{ renderHelpTextFunction &&
											renderHelpTextFunction( subField ) }
									</div>
								);
							}

							return (
								<div
									key={ subField.id }
									className={ cn(
										'space-y-1.5',
										widthToTailwindClass(
											subField.width || 'full'
										)
									) }
								>
									{ renderFieldLabel( subField ) }
									<div className="flex items-center justify-start gap-1.5 w-full">
										{ renderFieldCommon( {
											field: {
												...subField,
												id: subField.id,
											},
											getFieldValue: () =>
												item[ subField.id ] ||
												subField.std ||
												'',
											onFieldChange: ( fieldId, value ) =>
												handleItemFieldChange(
													index,
													fieldId,
													value
												),
											variableSuggestions,
											renderAsGroupComponent: false,
											itemIndex: index,
											parentFieldId: field.id,
										} ) }
									</div>
									{ renderHelpTextFunction &&
										renderHelpTextFunction( subField ) }
								</div>
							);
						} ) }
					</div>
				</div>
			) ) }

			<Button
				variant="outline"
				className="w-fit"
				size="sm"
				onClick={ handleAddNewItem }
				icon={ <Plus /> }
			>
				{ __( 'Add New', 'surerank' ) }
			</Button>
		</>
	);
};

// Add the GroupFieldRenderer component
export const GroupFieldRenderer = ( {
	field,
	schemaType,
	getFieldValue,
	onFieldChange,
	variableSuggestions,
} ) => {
	const groupType = field.fields?.find( ( f ) => f.id === '@type' )
		? getFieldValue( '@type', field.id )
		: null;

	// Filter out hidden fields before grouping into rows (memoized)
	const visibleFields = useMemo( () => {
		if ( ! field.fields ) {
			return [];
		}
		return field.fields.filter( ( subField ) => {
			if ( ! isFieldVisible( subField ) ) {
				return false;
			}

			if ( subField.main && groupType && subField.main !== groupType ) {
				return false;
			}

			return true;
		} );
	}, [ field.fields, groupType ] );

	// Group fields into rows based on width (memoized)
	const rows = useMemo(
		() => groupFieldsIntoRows( visibleFields ),
		[ visibleFields ]
	);

	if ( ! field.fields || field.fields.length === 0 ) {
		return null;
	}

	return (
		<div className="space-y-2 w-full border-l-2 border-gray-100 pt-2">
			{ rows.map( ( row, rowIndex ) => (
				<div
					key={ `row-${ rowIndex }` }
					className="grid grid-cols-12 gap-4 w-full"
				>
					{ row.map( ( subField ) => (
						<div
							key={ subField.id }
							className={ cn(
								'space-y-1.5',
								widthToTailwindClass( subField.width || 'full' )
							) }
						>
							{ renderFieldLabel( subField ) }
							<div className="flex items-center justify-start gap-1.5 w-full">
								{ renderFieldCommon( {
									field: subField,
									schemaType,
									getFieldValue: ( fieldId ) =>
										getFieldValue( fieldId, field.id ),
									onFieldChange: ( fieldId, value ) =>
										onFieldChange(
											fieldId,
											value,
											field.id
										),
									variableSuggestions,
									renderAsGroupComponent: false,
								} ) }
							</div>
						</div>
					) ) }
				</div>
			) ) }
		</div>
	);
};

export const renderCloneableField = ( {
	field,
	getFieldValue,
	onFieldChange,
	variableSuggestions,
	placeholder = '',
} ) => {
	const existingValues = getFieldValue( field.id ) || {};

	if ( Object.keys( existingValues ).length === 0 ) {
		existingValues[ generateUUID( 7 ) ] = ''; // Ensure first key is unique
	}

	const handleAddNewField = () => {
		const newId = generateUUID( 7 );
		const updatedValues = {
			...existingValues,
			[ newId ]: '',
		};
		onFieldChange( field.id, updatedValues );
	};

	const handleFieldChange = ( key, value ) => {
		onFieldChange( field.id, {
			...existingValues,
			[ key ]: value,
		} );
	};

	return (
		<div className="flex flex-col gap-2 w-full">
			{ Object.entries( existingValues ).map( ( [ key, value ] ) => (
				<div key={ key } className="flex items-center gap-1.5 w-full">
					{ renderFieldCommon( {
						field: {
							...field,
							id: field.id,
						},
						getFieldValue: () => value || field.std || '',
						onFieldChange: ( fieldId, newValue ) =>
							handleFieldChange( key, newValue ),
						variableSuggestions,
						placeholder,
						renderAsGroupComponent: false,
					} ) }
					<Button
						variant="ghost"
						size="md"
						onClick={ () => {
							const updatedValues = { ...existingValues };
							delete updatedValues[ key ]; // Remove entry
							onFieldChange( field.id, updatedValues );
						} }
						icon={
							<Trash
								strokeWidth={ 1.5 }
								className="text-icon-secondary"
							/>
						}
					/>
				</div>
			) ) }
			<Button
				variant="outline"
				className="w-fit"
				size="sm"
				onClick={ handleAddNewField }
				icon={ <Plus /> }
			>
				{ __( 'Add New', 'surerank' ) }
			</Button>
		</div>
	);
};

export function renderFieldCommon( {
	field,
	getFieldValue,
	onFieldChange,
	variableSuggestions,
	placeholder = '',
	renderAsGroupComponent = false,
	itemIndex = null,
	parentFieldId = null,
} ) {
	if ( ! field ) {
		return null;
	}

	const currentFieldValue = getFieldValue( field.id ) || field.std || '';

	const uniqueKey = parentFieldId
		? `${ parentFieldId }-${ itemIndex }-${ field.id }`
		: field.id; // PREVENT KEY COLLISIONS IN NESTED RENDERING

	switch ( field.type ) {
		case 'Select': {
			const options = flattenOptions( field.options );

			return (
				<div key={ field.id } className="w-full">
					<Select
						size="md"
						value={ currentFieldValue }
						onChange={ ( value ) =>
							onFieldChange( field.id, value )
						}
					>
						<Select.Button
							render={ ( selectedValue ) => {
								// Find the label for the selected value
								const selectedOption = Object.entries(
									options
								).find( ( [ key ] ) => key === selectedValue );
								return selectedOption
									? selectedOption[ 1 ]
									: selectedValue;
							} }
							placeholder={ __( 'Select an option', 'surerank' ) }
						/>
						<Select.Options className="z-50">
							{ Object.entries( options ).map(
								( [ key, label ] ) => (
									<Select.Option key={ key } value={ key }>
										{ label }
									</Select.Option>
								)
							) }
						</Select.Options>
					</Select>
				</div>
			);
		}

		case 'MultiSelect': {
			const options = flattenOptions( field.options );

			let currentValues = [];
			if ( Array.isArray( currentFieldValue ) ) {
				currentValues = currentFieldValue;
			} else {
				currentValues = currentFieldValue ? [ currentFieldValue ] : [];
			}

			const getSelectedLabels = ( selectedValue, optionValues ) => {
				return optionValues[ selectedValue ] || selectedValue;
			};

			return (
				<div key={ field.id } className="w-full">
					<Select
						size="md"
						value={ currentValues }
						onChange={ ( values ) =>
							onFieldChange( field.id, values )
						}
						multiple
					>
						<Select.Button
							render={ ( selectedValue ) =>
								getSelectedLabels( selectedValue, options )
							}
						/>
						<Select.Options className="z-50">
							{ Object.entries( options ).map(
								( [ key, label ] ) => (
									<Select.Option key={ key } value={ key }>
										{ label }
									</Select.Option>
								)
							) }
						</Select.Options>
					</Select>
				</div>
			);
		}

		case 'Group': {
			if ( renderAsGroupComponent ) {
				return (
					<GroupFieldRenderer
						key={ field.id }
						field={ field }
						getFieldValue={ getFieldValue }
						onFieldChange={ onFieldChange }
						variableSuggestions={ variableSuggestions }
					/>
				);
			}

			// If not rendering as group component, return null
			// This branch is typically not used as Group fields should use GroupFieldRenderer
			return null;
		}

		case 'SelectGroup': {
			const groupOptions = Object.values( field?.options || {} );
			return (
				<div key={ field.id } className="w-full">
					<Select
						size="md"
						value={ currentFieldValue }
						onChange={ ( value ) =>
							onFieldChange( field.id, value )
						}
						combobox
						placeholder={ __(
							'Search or select an option',
							'surerank'
						) }
						aria-label={ field.label }
					>
						<Select.Button
							placeholder={ __(
								'Search or select an option',
								'surerank'
							) }
							render={ ( selectedValue ) => {
								// Find the label for the selected value across all groups
								for ( const group of groupOptions ) {
									const selectedOption = Object.entries(
										group.options
									).find(
										( [ key ] ) => key === selectedValue
									);
									if ( selectedOption ) {
										return selectedOption[ 1 ];
									}
								}
								return selectedValue;
							} }
						/>
						<Select.Options>
							{ groupOptions.map( ( group, index ) => (
								<Select.OptionGroup
									key={ index }
									label={ group.label }
								>
									{ Object.entries( group.options ).map(
										( [ key, label ] ) => (
											<Select.Option
												key={ key }
												value={ key }
											>
												{ label }
											</Select.Option>
										)
									) }
								</Select.OptionGroup>
							) ) }
						</Select.Options>
					</Select>
				</div>
			);
		}

		case 'DateTime': {
			return (
				<DateTimeField
					field={ field }
					currentValue={ currentFieldValue }
					onFieldChange={ onFieldChange }
					placeholder={ placeholder }
					variableSuggestions={ variableSuggestions }
				/>
			);
		}

		case 'Title': {
			return (
				<div className="w-full">
					<Input
						key={ field.id }
						by="label"
						placeholder={ placeholder }
						defaultValue={ currentFieldValue }
						aria-label={ field.label }
						className="flex-grow max-w-full mdx"
						size="md"
						type="text"
						onChange={ ( value ) => {
							onFieldChange( field.id, value );
						} }
					/>
				</div>
			);
		}

		default:
			return (
				<EditorInput
					key={ uniqueKey }
					by="label"
					trigger="@"
					options={ variableSuggestions }
					placeholder={ placeholder }
					defaultValue={ stringValueToFormatJSON(
						currentFieldValue,
						variableSuggestions,
						'value'
					) }
					onChange={ ( editorState ) => {
						onFieldChange(
							field.id,
							editorValueToString( editorState.toJSON() )
						);
					} }
					className="flex-grow"
					wrapperClassName="[&>ul>li]:capitalize"
					{ ...( WORD_BREAK_ALL_EDITOR_INPUT.includes( field.id ) && {
						style: STYLES_OVERRIDE_FOR_EDITOR_INPUT,
					} ) }
				/>
			);
	}
}

export function renderHelpText( field ) {
	if ( field?.type !== 'Text' ) {
		return null;
	}

	return (
		/**
		 * @description Help text not shown for schema_name, Group, Select, and SelectGroup fields
		 */
		<Text size={ 14 } weight={ 400 } color="help">
			{ __( 'Type @ to view variable suggestions', 'surerank' ) }
		</Text>
	);
}

/**
 * Renders a field based on its type (Group, cloneable, or common)
 *
 * @param {Object}   field                          - Field configuration
 * @param {Object}   options                        - Rendering options
 * @param {string}   options.schemaId               - Schema ID
 * @param {string}   options.schemaType             - Schema type
 * @param {Function} options.getFieldValue          - Function to get field value
 * @param {Function} options.onFieldChange          - Function to handle field changes
 * @param {Array}    options.variableSuggestions    - Variable suggestions for autocomplete
 * @param {Object}   options.fieldItemIds           - Field item IDs state
 * @param {Function} options.setFieldItemIds        - Setter for field item IDs
 * @param {boolean}  options.renderAsGroupComponent - Whether to render as group component
 * @return {JSX.Element} Rendered field
 */
export const renderFieldSwitch = ( field, options ) => {
	const {
		schemaId,
		schemaType,
		getFieldValue,
		onFieldChange,
		variableSuggestions,
		fieldItemIds,
		setFieldItemIds,
		renderAsGroupComponent = true,
	} = options;

	if ( field.type === 'Group' && field.cloneable ) {
		return (
			<div className="flex flex-col w-full">
				{ renderCloneableGroupField( {
					field,
					schemaId,
					getFieldValue,
					onFieldChange,
					variableSuggestions,
					fieldItemIds,
					setFieldItemIds,
				} ) }
			</div>
		);
	}

	if ( field.type === 'Group' && ! field.cloneable ) {
		return (
			<GroupFieldRenderer
				field={ field }
				schemaType={ schemaType }
				getFieldValue={ getFieldValue }
				onFieldChange={ onFieldChange }
				variableSuggestions={ variableSuggestions }
			/>
		);
	}

	if ( field.cloneable ) {
		return (
			<div className="flex items-center justify-start gap-1.5 w-full">
				{ renderCloneableField( {
					field,
					schemaType,
					getFieldValue,
					onFieldChange,
					variableSuggestions,
					renderAsGroupComponent,
				} ) }
			</div>
		);
	}

	return (
		<div className="flex items-center justify-start gap-1.5 w-full">
			{ renderFieldCommon( {
				field,
				schemaType,
				getFieldValue,
				onFieldChange,
				variableSuggestions,
				renderAsGroupComponent,
			} ) }
		</div>
	);
};
