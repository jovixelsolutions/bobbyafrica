import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';
import TextField from '@Components/fields/TextField';

const Gdpr = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'GDPR', 'woo-cart-abandonment-recovery' ) }>
			<ToggleField
				title={ __(
					'Enable GDPR Integration',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'By checking this, it will show up confirmation text below the email id on checkout page.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_gdpr_status' }
				value={ settingsData?.wcf_ca_gdpr_status }
			/>
			{ settingsData?.wcf_ca_gdpr_status && (
				<TextField
					title={ __(
						'GDPR Message',
						'woo-cart-abandonment-recovery'
					) }
					description={ __(
						'When a user clicks reply, which email address should that reply be sent to?',
						'woo-cart-abandonment-recovery'
					) }
					name={ 'wcf_ca_gdpr_message' }
					value={ settingsData?.wcf_ca_gdpr_message }
				/>
			) }
		</TabWrapper>
	);
};

export default Gdpr;
