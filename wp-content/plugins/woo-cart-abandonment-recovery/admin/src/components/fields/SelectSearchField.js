import { useState, useEffect } from 'react';
import { Select, toast } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import { useStateValue, doApiFetch } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import FieldWrapper from '@Components/common/FieldWrapper';
import { useProAccess } from '@Components/pro/useProAccess';

const SelectSearchField = ( {
	title,
	description,
	name,
	placeholder = '',
	value,
	action,
	actionNonce,
	handleChange,
	autoSave = true,
	disableStyle,
	isPro = false,
	proUpgradeMessage = '',
} ) => {
	const [ state, dispatch ] = useStateValue();
	const { shouldBlockProFeatures } = useProAccess();
	const [ optionsArray, setOptionsArray ] = useState( [] );
	const isFeatureBlocked = shouldBlockProFeatures();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};

	// Use provided value when handleChange is provided (form mode)
	// Otherwise use global state value (auto-save mode)
	const currentValue =
		handleChange && ! autoSave ? value : settingsValues[ name ] ?? value;
	const [ selectValue, setSelectValue ] = useState(
		optionsArray.find( ( opt ) => opt.id === currentValue ) || {}
	);
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
				? value
				: settingsValues[ name ] ?? value;
		setSelectValue(
			optionsArray.find( ( opt ) => opt.id === newValue ) || {}
		);
	}, [
		settingsValues[ name ],
		value,
		handleChange,
		autoSave,
		optionsArray,
	] );

	const fetchOptions = () => {
		const ajaxUrl = cart_abandonment_admin?.ajax_url;

		const formData = new FormData();
		formData.append( 'action', action );
		formData.append( 'security', cart_abandonment_admin?.[ actionNonce ] );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					setOptionsArray( response.data?.options );
				} else {
					toast.error(
						__( 'Error', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error( __( 'Error', 'woo-cart-abandonment-recovery' ), {
					description: error.data?.message || '',
				} );
			},
			true
		);
	};

	function handleOnChange( val ) {
		setSelectValue( val );

		// If handleChange is provided and autoSave is false, use form mode
		if ( handleChange && ! autoSave ) {
			handleChange( name, val.id );
			return;
		}

		// Otherwise use auto-save mode
		// Update global state immediately
		dispatch( {
			type: 'UPDATE_SETTINGS_DATA',
			payload: {
				option: name,
				value: val.id,
			},
		} );

		// Debounced update for database (only in auto-save mode)
		if ( autoSave ) {
			debouncedUpdate( val.id );
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
			<div className="flex gap-2 items-center">
				<div>
					<Select
						onChange={ handleOnChange }
						size="md"
						name={ name }
						value={ selectValue?.name }
						disabled={ isPro && isFeatureBlocked }
						combobox={ true }
						searchPlaceholder="Search..."
						searchFn={ fetchOptions }
					>
						<Select.Button
							className="whitespace-nowrap"
							placeholder={
								placeholder ??
								__(
									'Select an option',
									'woo-cart-abandonment-recovery'
								)
							}
						/>
						<Select.Options>
							{ optionsArray.map( ( option ) => (
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

export default SelectSearchField;
