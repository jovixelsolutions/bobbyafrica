import ToggleField from './ToggleField';
import TextField from './TextField';
import NumberField from './NumberField';
import SelectField from './SelectField';
import TimeField from './TimeField';
import RichTextField from './RichTextField';
import TestEmail from './TestEmail';

const RenderFields = ( {
	data,
	value,
	handleChange,
	isActive = true,
	errors,
} ) => {
	if ( ! isActive ) {
		return null;
	}

	switch ( data.type ) {
		case 'toggle':
			return (
				<ToggleField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					handleChange={ handleChange }
				/>
			);
		case 'text':
			return (
				<TextField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					handleChange={ handleChange }
					optionsArray={ data.options }
					error={ errors[ data.name ] }
				/>
			);
		case 'number':
			return (
				<NumberField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					handleChange={ handleChange }
				/>
			);
		case 'select':
			return (
				<SelectField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					optionsArray={ data.options }
					handleChange={ handleChange }
				/>
			);
		case 'time':
			return (
				<TimeField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					unitOptions={ data.unit_options }
					handleChange={ handleChange }
				/>
			);
		case 'richtext':
			return (
				<RichTextField
					title={ data.label }
					name={ data.name }
					id={ data.id }
					value={ value }
					handleChange={ handleChange }
					error={ errors[ data.name ] }
				/>
			);
		case 'test_email':
			return (
				<TestEmail
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
				/>
			);
		default:
			return null;
	}
};

export default RenderFields;
