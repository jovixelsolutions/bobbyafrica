import { __ } from '@wordpress/i18n';

import { useStateValue } from '@Store';
import Heading from '@Components/onboarding/Heading';
import NavigationButtons from '@Components/onboarding/NavigationButtons';
import ConditionsHelper from '@Utils/conditions';
import RenderFields from '@Components/RenderFields';

const RecoverySettings = () => {
	const [ state ] = useStateValue();
	const settingsData = state?.settingsData || {};
	const fieldSettings =
		settingsData?.fields?.onboarding[ 'recovery-settings' ] || {};
	const settingsValues = settingsData?.values || {};
	const conditions = new ConditionsHelper();

	return (
		<div className="flex flex-col gap-2">
			<Heading
				title={ __(
					'Configure Recovery Settings',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Personalize the look and feel of your cart to create a seamless shopping experience.',
					'woo-cart-abandonment-recovery'
				) }
			/>
			<div>
				{ fieldSettings.fields &&
					Object.keys( fieldSettings.fields ).map( ( field ) => {
						const data = fieldSettings.fields[ field ];
						const value =
							settingsValues[ data?.name ] ?? data.value ?? '';
						const isActive = conditions.isActiveControl(
							data,
							settingsValues
						);
						return (
							<RenderFields
								key={ data?.name || field }
								data={ data }
								value={ value }
								isActive={ isActive }
							/>
						);
					} ) }
			</div>
			<NavigationButtons />
		</div>
	);
};

export default RecoverySettings;
