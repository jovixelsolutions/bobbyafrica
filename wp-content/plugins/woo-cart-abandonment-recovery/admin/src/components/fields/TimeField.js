import { useState, useEffect } from 'react';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import { Input, Select } from '@bsf/force-ui';
import FieldWrapper from '../common/FieldWrapper';
import RenderFields from '../RenderFields';
import { useProAccess } from '@Components/pro/useProAccess';

const TimeField = ( {
	title,
	description,
	name,
	value = '',
	unitOptions = [
		{ id: 'minutes', name: 'Minute(s)' },
		{ id: 'hours', name: 'Hour(s)' },
	],
	fields = null,
	handleChange,
	autoSave = true,
	onSaved,
	isPro = false,
	proUpgradeMessage = '',
} ) => {
	// All hooks must be called at the top level
	const [ state, dispatch ] = useStateValue();
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};
	const initialValue = settingsValues[ name ]?.value ?? value;
	const initialUnit = settingsValues[ name ]?.unit ?? '';
	const [ inputValue, setInputValue ] = useState( initialValue );
	const [ selectedUnit, setSelectedUnit ] = useState(
		unitOptions.find( ( opt ) => opt.id === initialUnit ) ||
			unitOptions[ 0 ]
	);

	const debouncedUpdate = useDebounceDispatch(
		dispatch,
		name,
		onSaved,
		400,
		autoSave
	);

	useEffect( () => {
		setInputValue( initialValue );
		setSelectedUnit(
			unitOptions.find( ( opt ) => opt.id === initialUnit ) ||
				unitOptions[ 0 ]
		);
	}, [ initialValue, initialUnit ] );

	// If fields prop is provided, use the new dynamic structure
	if ( fields && typeof fields === 'object' ) {
		return (
			<FieldWrapper
				title={ title }
				description={ description }
				type="inline"
				isPro={ isPro }
				proUpgradeMessage={ proUpgradeMessage }
			>
				<div className="flex gap-2 items-center">
					{ Object.entries( fields ).map(
						( [ fieldKey, fieldData ] ) => {
							// Get the field name from the field data
							const fieldName = fieldData.name || fieldKey;

							// Get the value for this specific field
							/* eslint-disable no-mixed-spaces-and-tabs */
							const fieldValue =
								value && typeof value === 'object'
									? value[ fieldName ] ||
									  fieldData.default_unit ||
									  ''
									: '';

							// Add className for number fields
							const fieldDataWithClass = { ...fieldData };
							if ( fieldData.type === 'number' ) {
								fieldDataWithClass.className = 'w-20';
							}

							const isAutoSave = fieldData?.autoSave || false;

							return (
								<RenderFields
									key={ fieldKey }
									data={ {
										...fieldDataWithClass,
										label: fieldDataWithClass.label || '',
										desc: fieldDataWithClass.desc || '',
									} }
									value={ fieldValue }
									isActive={ true }
									handleChange={ handleChange }
									autoSave={ isAutoSave }
									disableStyle={ true }
								/>
							);
						}
					) }
				</div>
			</FieldWrapper>
		);
	}

	function handleValueChange( val ) {
		setInputValue( val );
		// Convert to string and ensure proper format for AJAX
		const formattedValue = JSON.stringify( {
			value: String( val ),
			unit: selectedUnit.id,
		} );
		debouncedUpdate( formattedValue );
	}

	function handleUnitChange( option ) {
		setSelectedUnit( option );
		// Convert to string and ensure proper format for AJAX
		const formattedValue = JSON.stringify( {
			value: String( inputValue ),
			unit: option.id,
		} );
		debouncedUpdate( formattedValue );
	}

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="inline"
			isPro={ isPro }
			proUpgradeMessage={ proUpgradeMessage }
		>
			<div className="flex gap-2 items-center">
				<Input
					type="number"
					size="md"
					min={ 1 }
					className="w-20 focus:[&>input]:ring-focus"
					name={ name }
					value={ inputValue }
					onChange={ handleValueChange }
					disabled={ isPro && isFeatureBlocked }
				/>
				<Select
					name={ `${ name }-unit` }
					value={ selectedUnit.name }
					onChange={ handleUnitChange }
					size="md"
					disabled={ isPro && isFeatureBlocked }
				>
					<Select.Button />
					<Select.Options>
						{ unitOptions.map( ( option ) => (
							<Select.Option key={ option.id } value={ option }>
								{ option.name }
							</Select.Option>
						) ) }
					</Select.Options>
				</Select>
			</div>
		</FieldWrapper>
	);
};

export default TimeField;
