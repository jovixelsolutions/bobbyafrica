import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';
import MultiSelectField from '@Components/fields/MultiSelectField';
import NumberField from '@Components/fields/NumberField';

const General = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'General', 'woo-cart-abandonment-recovery' ) }>
			<ToggleField
				title={ __(
					'Enable Tracking',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Cart will be considered abandoned if order is not completed in cart abandoned cut-off time.',
					'woo-cart-abandonment-recovery'
				) }
				name="wcf_ca_status"
				value={ settingsData?.wcf_ca_status }
			/>
			<NumberField
				title={ __(
					'Cart abandoned cut-off time',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Consider cart abandoned after above entered minutes of item being added to cart and order not placed.',
					'woo-cart-abandonment-recovery'
				) }
				after={ __( 'minutes', 'woo-cart-abandonment-recovery' ) }
				name="wcf_ca_cron_run_time"
				min={ 10 }
				value={ settingsData?.wcf_ca_cron_run_time }
			/>
			<MultiSelectField
				title={ __(
					'Disable Tracking For',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'It will ignore selected users from abandonment process when they logged in, and hence they can not receive mail for cart abandoned by themselves.',
					'woo-cart-abandonment-recovery'
				) }
				name="wcf_ca_ignore_users"
				value={ settingsData?.wcf_ca_ignore_users }
				optionsArray={ cart_abandonment_admin?.supported_wp_roles }
			/>
			<MultiSelectField
				title={ __(
					'Exclude email sending For',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'It will not send future recovery emails to selected order status and will mark as recovered.',
					'woo-cart-abandonment-recovery'
				) }
				name="wcf_ca_excludes_orders"
				value={ settingsData?.wcf_ca_excludes_orders }
				optionsArray={ cart_abandonment_admin?.order_statuses }
			/>
			<ToggleField
				title={ __(
					'Notify recovery to admin',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'This option will send an email to admin on new order recovery.',
					'woo-cart-abandonment-recovery'
				) }
				name="wcar_email_admin_on_recovery"
				value={ settingsData?.wcar_email_admin_on_recovery }
			/>
		</TabWrapper>
	);
};

export default General;
