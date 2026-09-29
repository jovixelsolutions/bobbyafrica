import { useState, useEffect } from 'react';
import { Input, Select } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';

const TimeField = ( {
	title,
	description,
	name,
	value = {},
	unitOptions = [
		{ id: 'MINUTE', name: 'Minute(s)' },
		{ id: 'HOUR', name: 'Hour(s)' },
		{ id: 'DAY', name: 'Day(s)' },
	],
	handleChange,
} ) => {
	const [ inputValue, setInputValue ] = useState( '' );
	const [ selectedUnit, setSelectedUnit ] = useState( unitOptions[ 0 ] );

	// Sync state with props when value changes
	useEffect( () => {
		const safeValue =
			typeof value === 'object' && value !== null ? value : {};
		const inputVal = safeValue[ name?.frequency ] ?? '';
		const unitVal =
			unitOptions.find(
				( option ) => option.id === safeValue[ name?.unit ]
			) ?? unitOptions[ 0 ];

		setInputValue( inputVal );
		setSelectedUnit( unitVal );
	}, [ value, name?.frequency, name?.unit ] );

	/* eslint-disable */
	function handleValueChange( val ) {
		if ( val > 0 ) {
			setInputValue( val );
			handleChange && handleChange( name?.frequency, val );
		} else {
			setInputValue( '' );
			handleChange && handleChange( name?.frequency, '' );
		}
	}

	function handleUnitChange( option ) {
		setSelectedUnit( option );
		handleChange && handleChange( name?.unit, option.id );
	}
	/* eslint-enable */

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="inline"
			size="small"
		>
			<div className="flex gap-2 items-center">
				<div className="w-full">
					<Input
						type="number"
						size="md"
						min={ 1 }
						className="focus:[&>input]:ring-focus"
						name={ name }
						value={ inputValue }
						onChange={ handleValueChange }
					/>
				</div>
				<div>
					<Select
						name={ `${ name }-unit` }
						value={ selectedUnit.name }
						onChange={ handleUnitChange }
						size="md"
					>
						<Select.Button className="w-fit whitespace-nowrap" />
						<Select.Options>
							{ unitOptions.map( ( option ) => (
								<Select.Option
									key={ option.id }
									value={ option }
								>
									{ option.name }
								</Select.Option>
							) ) }
						</Select.Options>
					</Select>
				</div>
			</div>
		</FieldWrapper>
	);
};

export default TimeField;
