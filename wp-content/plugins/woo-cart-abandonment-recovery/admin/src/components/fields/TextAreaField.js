import { useState, useEffect } from 'react';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import { TextArea } from '@bsf/force-ui';
import FieldWrapper from '@Components/common/FieldWrapper';
import { useProAccess } from '@Components/pro/useProAccess';

const TextAreaField = ( {
	title,
	description,
	name,
	value,
	onSaved,
	isPro = false,
	proUpgradeMessage = '',
} ) => {
	const [ state, dispatch ] = useStateValue();
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};
	const initial = settingsValues[ name ] ?? value;
	const [ textValue, setTextValue ] = useState( initial );
	const debouncedUpdate = useDebounceDispatch(
		dispatch,
		name,
		onSaved,
		400,
		true
	);

	useEffect( () => {
		setTextValue( initial );
	}, [ initial ] );

	function handleOnChange( val ) {
		setTextValue( val );
		debouncedUpdate( String( val ) );
	}

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="block"
			isPro={ isPro }
			proUpgradeMessage={ proUpgradeMessage }
		>
			<TextArea
				className="w-full focus:[&>input]:ring-focus"
				type="text"
				size="md"
				name={ name }
				value={ textValue }
				onChange={ handleOnChange }
				disabled={ isPro && isFeatureBlocked }
			/>
		</FieldWrapper>
	);
};

export default TextAreaField;
