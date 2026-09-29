import { __ } from '@wordpress/i18n';

import TabWrapper from '@Components/common/TabWrapper';
import ToggleField from '@Components/fields/ToggleField';
import DeleteCouponsField from '@Components/fields/DeleteCouponsField';

const Coupon = ( { settingsData = {} } ) => {
	return (
		<TabWrapper title={ __( 'Coupon', 'woo-cart-abandonment-recovery' ) }>
			<ToggleField
				title={ __(
					'Delete Coupons Automatically',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'This option will set a weekly cron to delete all expired and used coupons automatically in the background.',
					'woo-cart-abandonment-recovery'
				) }
				name={ 'wcf_ca_auto_delete_coupons' }
				value={ settingsData?.wcf_ca_auto_delete_coupons }
			/>
			<DeleteCouponsField
				title={ __(
					'Delete Coupons Manually',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'This will delete all expired and used coupons that were created by Woo Cart Abandonment Recovery.',
					'woo-cart-abandonment-recovery'
				) }
			/>
		</TabWrapper>
	);
};

export default Coupon;
