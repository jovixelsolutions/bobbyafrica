import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import TextAreaField from '@Components/fields/TextAreaField';
import ToggleField from '@Components/fields/ToggleField';

const Advanced = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'Advanced', 'woo-cart-abandonment-recovery' ) }>
			<TextAreaField
				title={ __(
					'UTM parameters',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'The UTM parameters will be appended to the checkout page links which is available in the recovery emails.',
					'woo-cart-abandonment-recovery'
				) }
				placeholder={ __(
					' Add UTM parameter per line.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_global_param' }
				value={ settingsData?.wcf_ca_global_param }
			/>
			<ToggleField
				title={ __(
					'Delete Plugin Data',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Enabling this option will delete the plugin data while deleting the Plugin.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_delete_plugin_data' }
				value={ settingsData?.wcf_ca_delete_plugin_data }
			/>
			<ToggleField
				title={ __(
					'Enable Usage Tracking',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Allow anonymous usage tracking to help improve WooCommerce Cart Abandonment Recovery. We only collect non-sensitive data to enhance your experience. <a href="" class="no-underline text-flamingo-400 font-medium">Learn more</a>',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcar_usage_optin' }
				value={ settingsData?.wcar_usage_optin }
			/>
		</TabWrapper>
	);
};

export default Advanced;
