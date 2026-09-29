import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';
import WebhookURLField from '@Components/fields/WebhookURLField';
import SelectField from '@Components/fields/SelectField';
import NumberField from '@Components/fields/NumberField';
import TimeField from '@Components/fields/TimeField';

const Webhook = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'Webhook', 'woo-cart-abandonment-recovery' ) }>
			<ToggleField
				title={ __(
					'Enable Webhook',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Allows you to trigger webhook automatically upon cart abandonment and recovery.',
					'woo-cart-abandonment-recovery'
				) }
				name="wcf_ca_zapier_tracking_status"
				value={ settingsData?.wcf_ca_zapier_tracking_status }
			/>
			{ settingsData.wcf_ca_zapier_tracking_status && (
				<>
					<WebhookURLField
						title={ __(
							'Webhook URL',
							'woo-cart-abandonment-recovery'
						) }
						description={ __(
							'Add the Webhook URL below.',
							'woo-cart-abandonment-recovery'
						) }
						name="wcf_ca_zapier_cart_abandoned_webhook"
						value={
							settingsData?.wcf_ca_zapier_cart_abandoned_webhook
						}
					/>
					<ToggleField
						title={ __(
							'Create Coupon Code',
							'woo-cart-abandonment-recovery'
						) }
						description={ __(
							'Auto-create the special coupon for the abandoned cart to send over the emails.',
							'woo-cart-abandonment-recovery'
						) }
						name="wcf_ca_coupon_code_status"
						value={ settingsData?.wcf_ca_coupon_code_status }
					/>
					{ settingsData?.wcf_ca_coupon_code_status && (
						<>
							<SelectField
								title={ __(
									'Discount Type',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'Select the Discount Type.',
									'woo-cart-abandonment-recovery'
								) }
								name="wcf_ca_discount_type"
								value={ settingsData?.wcf_ca_discount_type }
								optionsArray={ [
									{
										id: 'percent',
										name: 'Percentage Discount',
									},
									{
										id: 'fixed_cart',
										name: 'Fixed Cart Discount',
									},
								] }
							/>
							<NumberField
								title={ __(
									'Coupon Amount',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'Consider cart abandoned after above entered minutes of item being added to cart and order not placed.',
									'woo-cart-abandonment-recovery'
								) }
								name="wcf_ca_coupon_amount"
								value={ settingsData?.wcf_ca_coupon_amount }
							/>
							<TimeField
								title={ __(
									'Coupon Expires After',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'Set the expiry time for the coupon.',
									'woo-cart-abandonment-recovery'
								) }
								name="wcf_ca_coupon_expiry"
								value={
									JSON.parse(
										settingsData?.wcf_ca_coupon_expiry
									)?.value
								}
								unitKey={
									JSON.parse(
										settingsData?.wcf_ca_coupon_expiry
									)?.unit
								}
								unitOptions={ [
									{ id: 'hours', name: 'Hour(s)' },
									{ id: 'days', name: 'Days(s)' },
								] }
							/>
						</>
					) }
				</>
			) }
		</TabWrapper>
	);
};

export default Webhook;
