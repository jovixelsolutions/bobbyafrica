import { useState, useMemo, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Select } from '@bsf/force-ui';
import { Plus } from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';

/**
 * Renders a searchable select component for adding fields to a schema
 *
 * @param {Object}   props                 - Component props
 * @param {Array}    props.availableFields - Array of field objects that can be added
 * @param {Function} props.onAddField      - Callback function when a field is selected
 * @param {string}   props.className       - Optional CSS classes for the wrapper div
 * @param {Object}   props.filterContext   - Context object to pass to filters
 * @return {JSX.Element|null} The AddFieldMenu component or null if no fields available
 */
export const AddFieldMenu = ( {
	availableFields,
	onAddField,
	className = 'p-2 w-full border-t border-border-subtle',
	filterContext = {},
} ) => {
	const [ showFieldSelector, setShowFieldSelector ] = useState( false );

	const fieldOptions = useMemo( () => {
		const options = availableFields.map( ( field ) => ( {
			value: field.id,
			label: field.label,
		} ) );

		return applyFilters(
			'surerank.schema.properties.field_options',
			options,
			{
				...filterContext,
				availableFieldsCount: availableFields.length,
			}
		);
	}, [ availableFields, filterContext ] );

	const handleFieldSelection = useCallback(
		( value ) => {
			if ( ! value ) {
				return;
			}

			if ( value.startsWith( '__' ) ) {
				applyFilters(
					'surerank.schema.properties.handle_field_action',
					null,
					{
						action: value,
						...filterContext,
					}
				);
				setShowFieldSelector( false );
				return;
			}

			onAddField( value );
			setShowFieldSelector( false );
		},
		[ onAddField, filterContext ]
	);

	if ( fieldOptions.length === 0 ) {
		return null;
	}

	return (
		<div className={ className }>
			{ ! showFieldSelector ? (
				<Button
					variant="outline"
					size="md"
					icon={ <Plus className="size-4" /> }
					iconPosition="left"
					onClick={ () => setShowFieldSelector( true ) }
				>
					{ __( 'Add Field', 'surerank' ) }
				</Button>
			) : (
				<Select
					value=""
					onChange={ handleFieldSelection }
					combobox
					size="md"
					open={ showFieldSelector }
					onOpenChange={ setShowFieldSelector }
				>
					<Select.Button
						label={ __( 'Add Field', 'surerank' ) }
						placeholder={ __( 'Search fields…', 'surerank' ) }
					/>
					<Select.Options className="z-[99999]">
						{ fieldOptions.map( ( option ) => (
							<Select.Option
								key={ option.value }
								value={ option.value }
							>
								{ option.label }
							</Select.Option>
						) ) }
					</Select.Options>
				</Select>
			) }
		</div>
	);
};
