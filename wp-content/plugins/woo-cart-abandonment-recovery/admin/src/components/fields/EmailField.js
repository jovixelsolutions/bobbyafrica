import { useState, useEffect } from 'react';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import { Input, toast } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';

const EmailField = ( {
	title,
	description,
	name,
	value,
	badge,
	handleChange,
	autoSave = true,
	error,
} ) => {
	const [ state, dispatch ] = useStateValue();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};

	// Use provided value when handleChange is provided (form mode)
	// Otherwise use global state value (auto-save mode)
	const currentValue =
		handleChange && ! autoSave
			? value ?? ''
			: settingsValues[ name ] ?? value ?? '';
	const [ email, setEmail ] = useState( currentValue );
	const debouncedUpdate = useDebounceDispatch(
		dispatch,
		name,
		undefined,
		400,
		true
	);

	useEffect( () => {
		const newValue =
			handleChange && ! autoSave
				? value ?? ''
				: settingsValues[ name ] ?? value ?? '';
		setEmail( newValue );
	}, [ settingsValues[ name ], value, handleChange, autoSave ] );

	const handleOnChange = ( val ) => {
		setEmail( val );
	};

	const handleBlur = () => {
		if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
			toast.error( 'Please enter a valid email address' );
			return false;
		}
		if ( email !== currentValue ) {
			// If handleChange is provided and autoSave is false, use form mode
			if ( handleChange && ! autoSave ) {
				handleChange( name, email );
				return;
			}

			// Otherwise use auto-save mode
			if ( autoSave ) {
				debouncedUpdate( email );
			}
		}
	};

	return (
		<FieldWrapper title={ title } description={ description } type="block">
			<div>
				<Input
					className={ `${
						badge ? 'w-24 ' : 'w-full'
					} focus:[&>input]:ring-focus ${
						error ? 'border-red-500' : ''
					}` }
					type="email"
					size="md"
					name={ name }
					value={ email }
					onChange={ handleOnChange }
					onBlur={ handleBlur }
				/>
				{ error && (
					<p className="text-red-500 text-sm mt-1">{ error }</p>
				) }
			</div>
		</FieldWrapper>
	);
};

export default EmailField;
