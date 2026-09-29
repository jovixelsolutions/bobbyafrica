import { Select } from '@bsf/force-ui';

import FieldWrapper from '@Components/common/FieldWrapper';

const SelectField = ( {
	title,
	description,
	name,
	value,
	optionsArray,
	handleChange,
} ) => {
	const initial =
		optionsArray.find( ( option ) => option.id === value ) ||
		optionsArray[ 0 ];
	function handleOnChange( val ) {
		handleChange( name, val.id );
	}

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="inline"
			size="small"
		>
			<div>
				<Select
					onChange={ handleOnChange }
					size="md"
					name={ name }
					value={ initial.name }
				>
					<Select.Button
						className="whitespace-nowrap"
						placeholder="Select an option"
					/>
					<Select.Options>
						{ optionsArray.map( ( option ) => (
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

export default SelectField;
