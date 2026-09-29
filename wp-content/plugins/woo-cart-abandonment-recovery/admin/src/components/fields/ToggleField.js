import { useState, useEffect } from 'react';
import { Switch } from '@bsf/force-ui';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import FieldWrapper from '@Components/common/FieldWrapper';
import { useProAccess } from '@Components/pro/useProAccess';

/**
 * Normalizes toggle values to handle both old checkbox values ('on'/'off') and new toggle values ('true'/'false')
 *
 * @param {string|boolean} value - The value to normalize
 * @return {boolean} - The normalized boolean value
 */
const normalizeToggleValue = ( value ) => {
	if ( value === 'on' || value === '1' ) {
		return true;
	}
	if (
		value === '' ||
		value === 'off' ||
		value === 'false' ||
		value === '0'
	) {
		return false;
	}
	return value ?? false;
};

/**
 * Converts a boolean value to a string for database storage
 *
 * @param {boolean} value - The boolean value to convert
 * @return {string} - The string representation ('on' or blank string)
 */
const convertToggleToStorage = ( value ) => {
	return value ? 'on' : '';
};

const ToggleField = ( {
	title,
	description,
	name,
	value,
	tooltip,
	manageState,
	handleChange,
	autoSave = true,
	disabled,
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
			? normalizeToggleValue( value )
			: normalizeToggleValue( settingsValues[ name ] ?? value );
	const [ enable, setEnable ] = useState( currentValue );
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
				? normalizeToggleValue( value )
				: normalizeToggleValue( settingsValues[ name ] ?? value );
		setEnable( newValue );
	}, [ settingsValues[ name ], value, handleChange, autoSave ] );

	function handleOnChange( changedState ) {
		const newState = ! changedState;

		// Update local state immediately
		setEnable( newState );

		// If handleChange is provided and autoSave is false, use form mode
		if ( handleChange && ! autoSave ) {
			handleChange( name, newState );
			return;
		}

		const storageValue = convertToggleToStorage( newState );
		// Otherwise use auto-save mode
		// Update global state immediately
		dispatch( {
			type: 'UPDATE_SETTINGS_DATA',
			payload: {
				option: name,
				value: storageValue,
			},
		} );

		if ( 'function' === typeof manageState ) {
			manageState( newState );
		}

		// Debounced update for database (only in auto-save mode)
		if ( autoSave ) {
			debouncedUpdate( storageValue );
		}
	}

	// Generate a unique ID for this toggle for better accessibility
	const toggleId = `toggle-${ name.replace( /[\[\]]/g, '-' ) }`;

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			tooltip={ tooltip }
			isPro={ isPro }
			proUpgradeMessage={ proUpgradeMessage }
		>
			<div>
				<Switch
					id={ toggleId }
					aria-label={ `${ title } - ${
						enable ? 'Enabled' : 'Disabled'
					}` }
					aria-checked={ enable }
					value={ enable }
					name={ name }
					onChange={ () => {
						handleOnChange( enable );
					} }
					onKeyDown={ ( e ) => {
						// Handle keyboard events for accessibility
						if ( e.key === 'Enter' || e.key === ' ' ) {
							e.preventDefault();
							handleOnChange( enable );
						}
					} }
					size="md"
					className="border-none moderncart-toggle-field"
					role="switch"
					disabled={ ( isPro && isFeatureBlocked ) || disabled }
				/>
			</div>
		</FieldWrapper>
	);
};

export default ToggleField;
