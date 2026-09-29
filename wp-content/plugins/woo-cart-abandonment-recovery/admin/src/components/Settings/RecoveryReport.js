import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';
import TextAreaField from '@Components/fields/TextAreaField';

const RecoveryReport = ( { settingsData = {} } ) => {
	return (
		<TabWrapper
			title={ __( 'Recovery Report', 'woo-cart-abandonment-recovery' ) }
		>
			<ToggleField
				title={ __(
					'Send recovery report emails',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Enable sending recovery report emails.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_send_recovery_report_emails_to_admin' }
				value={ true }
			/>
			{ true ===
				settingsData?.wcf_ca_send_recovery_report_emails_to_admin && (
				<TextAreaField
					title={ __(
						'Email Address',
						'woo-cart-abandonment-recovery'
					) }
					description={ __(
						'Email address to send recovery report emails. For multiple emails, add each email address per line.',
						'woo-cart-abandonment-recovery'
					) }
					name={ 'wcf_ca_admin_email' }
					value={ 'aadhavanm@bsf.io' }
				/>
			) }
			<ToggleField
				title={ __(
					'Notify When Cart is Abandoned',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Send real-time alerts to admins when users leave items in their cart without completing the purchase.',
					'woo-cart-abandonment-recovery'
				) }
				name={ '' }
				value={ false }
			/>
			<ToggleField
				title={ __(
					'Notify When Cart is Recovered',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Send real-time alerts to admins when a user returns and completes a purchase from their abandoned cart.',
					'woo-cart-abandonment-recovery'
				) }
				name={ '' }
				value={ true }
			/>
		</TabWrapper>
	);
};

export default RecoveryReport;
