import { useState, useEffect } from 'react';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import { Input } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';
import { useProAccess } from '@Components/pro/useProAccess';

const NumberField = ( {
	title,
	description,
	name,
	value,
	badge,
	min,
	after = '',
	suffix = '',
	handleChange,
	autoSave = true,
	disableStyle,
	className = '',
	isPro = false,
	proUpgradeMessage = '',
} ) => {
	const [ state, dispatch ] = useStateValue();
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};

	// Use provided value when handleChange is provided (form mode)
	// Otherwise use global state value (auto-save mode)
	const currentValue =
		handleChange && ! autoSave
			? value ?? ''
			: settingsValues[ name ] ?? value ?? '';
	const [ numberValue, setNumberValue ] = useState( currentValue );
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
		setNumberValue( newValue );
	}, [ settingsValues[ name ], value, handleChange, autoSave ] );

	function handleOnChange( val ) {
		setNumberValue( val );

		// If handleChange is provided and autoSave is false, use form mode
		if ( handleChange && ! autoSave ) {
			handleChange( name, String( val ) );
			return;
		}

		// Otherwise use auto-save mode
		if ( autoSave ) {
			if ( parseInt( val ) >= parseInt( min || 0 ) ) {
				debouncedUpdate( String( val ) );
			}
		}
	}

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="inline"
			disableStyle={ disableStyle }
			isPro={ isPro }
			proUpgradeMessage={ proUpgradeMessage }
		>
			<div className="flex sm:justify-end items-center gap-2">
				<Input
					className={ `${
						className || ( badge ? 'w-24' : 'w-20 rounded-r-md' )
					} focus:[&>input]:ring-focus` }
					suffix={
						suffix && (
							<span className="text-badge-color-gray p-0.5 text-center text-xs font-medium">
								{ suffix }
							</span>
						)
					}
					type="number"
					size="md"
					name={ name }
					value={ numberValue }
					onChange={ handleOnChange }
					min={ min || 0 }
					disabled={ isPro && isFeatureBlocked }
				/>
				{ after && (
					<span className="text-badge-color-gray p-0.5 text-center text-xs font-medium">
						{ after }
					</span>
				) }
			</div>
		</FieldWrapper>
	);
};

export default NumberField;
