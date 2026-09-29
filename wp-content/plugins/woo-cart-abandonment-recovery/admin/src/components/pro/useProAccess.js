import { useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { toast } from '@bsf/force-ui';

import { useStateValue, doApiFetch } from '@Store';

export const useProAccess = () => {
	const navigate = useNavigate();
	const [ state ] = useStateValue();
	const adminData =
		typeof cart_abandonment_admin !== 'undefined'
			? cart_abandonment_admin
			: {};

	const isPro = adminData.is_pro;
	const hasLicense = state?.licenseStatus === '1' ? true : false;

	const canAccessProFeatures = () => {
		return isPro && hasLicense;
	};

	const shouldBlockProFeatures = () => {
		return ! canAccessProFeatures();
	};

	const getUpgradeMessage = ( subTitle = '' ) => {
		if ( ! isPro ) {
			return subTitle;
		} else if ( ! hasLicense ) {
			return __(
				'Activate your license to unlock pro features',
				'woo-cart-abandonment-recovery'
			);
		}
		return __(
			'Upgrade to Pro for advanced features',
			'woo-cart-abandonment-recovery'
		);
	};

	const getProStatusMessage = () => {
		if ( ! isPro ) {
			return __(
				'Pro plugin not installed',
				'woo-cart-abandonment-recovery'
			);
		} else if ( ! hasLicense ) {
			return __(
				'Pro plugin installed but license not activated',
				'woo-cart-abandonment-recovery'
			);
		}
		return '';
	};

	const getActionButtonText = () => {
		if ( 'not-installed' === cart_abandonment_admin?.wcar_pro_status ) {
			return __( 'Go Pro', 'woo-cart-abandonment-recovery' );
		} else if ( 'inactive' === cart_abandonment_admin?.wcar_pro_status ) {
			return __( 'Activate', 'woo-cart-abandonment-recovery' );
		} else if ( ! hasLicense ) {
			return __( 'Activate License', 'woo-cart-abandonment-recovery' );
		}
		return __( 'Upgrade Now', 'woo-cart-abandonment-recovery' );
	};

	const getRecommendedAction = () => {
		return 'upgrade';
	};

	const getStatusSeverity = () => {
		return 'info';
	};

	const getUpgradeToProUrl = ( args = '', customUrl = '' ) => {
		let baseUrl = customUrl || adminData?.upgrade_to_pro_url || '';
		const hasQuestionMark = baseUrl.includes( '?' );

		if ( args !== '' ) {
			baseUrl += hasQuestionMark ? `&${ args }` : `?${ args }`;
		}

		return baseUrl;
	};

	const upgradeActionButton = (
		args = 'utm_source=wcar-dashboard&utm_medium=free-wcar&utm_campaign=go-wcar-pro',
		customUrl = ''
	) => {
		if ( 'not-installed' === cart_abandonment_admin?.wcar_pro_status ) {
			const baseUrl = getUpgradeToProUrl( args, customUrl );
			window.open( baseUrl, '_blank' );
		} else if ( 'inactive' === cart_abandonment_admin?.wcar_pro_status ) {
			const formData = new window.FormData();
			formData.append( 'action', 'cart_abandonment_activate_plugin' );
			formData.append(
				'init',
				'woo-cart-abandonment-recovery-pro/woo-cart-abandonment-recovery-pro.php'
			);
			formData.append(
				'security',
				cart_abandonment_admin.plugin_activation_nonce
			);

			doApiFetch(
				cart_abandonment_admin.ajax_url,
				formData,
				'POST',
				( response ) => {
					if ( response.success ) {
						window.location.reload();
					} else {
						toast.error(
							__(
								'Failed to activate plugin',
								'woo-cart-abandonment-recovery'
							),
							{
								description: response.data?.message || '',
							}
						);
					}
				},
				( error ) => {
					toast.error(
						__(
							'Failed to activate plugin',
							'woo-cart-abandonment-recovery'
						),
						{
							description: error.data?.message || '',
						}
					);
				},
				true
			);
		} else if ( ! hasLicense ) {
			navigate( {
				search: '?page=woo-cart-abandonment-recovery&path=settings&tab=license-settings',
			} );
		}
	};

	const UPGRADE_ACTIONS = {
		PURCHASE: 'purchase',
		ACTIVATE: 'activate',
		UPGRADE: 'upgrade',
	};

	// Return all the things you might want in a component
	return {
		isPro,
		hasLicense,
		canAccessProFeatures,
		shouldBlockProFeatures,
		getUpgradeMessage,
		getProStatusMessage,
		getActionButtonText,
		getRecommendedAction,
		getStatusSeverity,
		getUpgradeToProUrl,
		upgradeActionButton,
		UPGRADE_ACTIONS,
	};
};
