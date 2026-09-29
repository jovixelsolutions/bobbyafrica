import { Input } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';

const NumberField = ( { title, description, name, value, handleChange } ) => {
	function handleOnChange( val ) {
		if ( val > 0 ) {
			handleChange( name, val );
		} else {
			handleChange( name, '' );
		}
	}

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="inline"
			size="small"
		>
			<Input
				className="w-full focus:[&>input]:ring-focus"
				type="number"
				size="md"
				name={ name }
				value={ value }
				onChange={ handleOnChange }
			/>
		</FieldWrapper>
	);
};

export default NumberField;
