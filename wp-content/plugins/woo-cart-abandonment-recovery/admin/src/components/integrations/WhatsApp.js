import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';

const WhatsApp = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'WhatsApp', 'woo-cart-abandonment-recovery' ) }>
			<ToggleField
				title={ __(
					'Enable Webhook',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Allows you to trigger webhook automatically upon cart abandonment and recovery.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_whatsapp_tracking_status' }
				value={ settingsData?.wcf_whatsapp_tracking_status }
			/>
		</TabWrapper>
	);
};

export default WhatsApp;
