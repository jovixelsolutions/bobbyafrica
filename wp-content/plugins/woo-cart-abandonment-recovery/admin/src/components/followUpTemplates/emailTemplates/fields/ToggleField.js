import { Switch } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';

const ToggleField = ( { title, description, name, value, handleChange } ) => {
	function handleOnChange( state ) {
		handleChange( name, state );
	}

	// Generate a unique ID
	const toggleId = `toggle-${ name.replace( /[\[\]]/g, '-' ) }`;

	return (
		<FieldWrapper title={ title } description={ description } size="small">
			<div>
				<Switch
					id={ toggleId }
					aria-label={ `${ title } - ${
						value ? 'Enabled' : 'Disabled'
					}` }
					aria-checked={ value }
					value={ value }
					name={ name }
					onChange={ handleOnChange }
					size="sm"
					className="border-none"
					role="switch"
				/>
			</div>
		</FieldWrapper>
	);
};

export default ToggleField;
