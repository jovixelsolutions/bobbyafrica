import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import TextField from '@Components/fields/TextField';

const Email = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'Email', 'woo-cart-abandonment-recovery' ) }>
			<TextField
				title={ __( '"From" Name', 'woo-cart-abandonment-recovery' ) }
				description={ __(
					'Name will appear in email sent.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_from_name' }
				value={ settingsData?.wcf_ca_from_name }
			/>
			<TextField
				title={ __(
					'"From" Address',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Email which send from.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_from_email' }
				value={ settingsData?.wcf_ca_from_email }
			/>
			<TextField
				title={ __(
					'"Reply To" Address',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'When a user clicks reply, which email address should that reply be sent to?',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_reply_email' }
				value={ settingsData?.wcf_ca_reply_email }
			/>
		</TabWrapper>
	);
};

export default Email;
