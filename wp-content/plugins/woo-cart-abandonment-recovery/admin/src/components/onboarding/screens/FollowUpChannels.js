import { __ } from '@wordpress/i18n';

import { useStateValue } from '@Store';
import Heading from '@Components/onboarding/Heading';
import NavigationButtons from '@Components/onboarding/NavigationButtons';
import ConditionsHelper from '@Utils/conditions';
import RenderFields from '@Components/RenderFields';

const FollowUpChannels = () => {
	const [ state ] = useStateValue();
	const settingsData = state?.settingsData || {};
	const fieldSettings =
		settingsData?.fields?.onboarding[ 'follow-up-channels' ] || {};
	const settingsValues = settingsData?.values || {};
	const conditions = new ConditionsHelper();
	return (
		<div className="flex flex-col gap-2">
			<Heading
				title={ __(
					'Select Your Follow Up Sequence Channels',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Set up cart settings to match your store’s needs in just a few clicks.',
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

export default FollowUpChannels;
