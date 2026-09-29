import { useState } from 'react';
import { Button } from '@bsf/force-ui';
import { XMarkIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import { ActionTypes, doApiFetch, useStateValue } from '@Store';

const LegacyUiNotice = () => {
	const [ isHiding, setIsHiding ] = useState( false );
	const [ state, dispatch ] = useStateValue();

	const isDismissed = Boolean( state?.legacyUiNoticeDismissed || false );

	const handleDismiss = () => {
		setIsHiding( true );
		// Save dismiss state via AJAX
		const formData = new FormData();
		formData.append( 'action', 'wcar_save_setting' );
		formData.append( 'option_name', 'car_legacy_ui_notice_dismissed' );
		formData.append( 'value', 'true' );
		formData.append(
			'security',
			cart_abandonment_admin?.save_setting_nonce
		);
		const ajaxUrl = cart_abandonment_admin?.ajax_url || window.ajaxurl;
		// Wait for animation before hiding
		setTimeout( () => {
			dispatch( {
				type: ActionTypes.DISMISS_LEGACY_UI_NOTICE,
				payload: true,
			} );
		}, 300 );
		doApiFetch( ajaxUrl, formData, 'POST', null, null, true, false );
	};

	if ( isDismissed ) {
		return null;
	}

	return (
		<div
			className={ `overflow-hidden transition-all duration-300 ${
				isHiding ? 'max-h-0' : 'max-h-10'
			}` }
		>
			<div className="p-2 bg-[#FDE6D7] flex gap-2 items-center z-10 relative">
				<p className="m-0 text-[13px] flex-1 text-center">
					<span className="font-semibold">
						{ __(
							'End of Support for Legacy UI:',
							'woo-cart-abandonment-recovery'
						) }
					</span>{ ' ' }
					{ __(
						'As part of ongoing improvements to the Cart Abandonment Recovery, we are deprecating the Legacy UI.',
						'woo-cart-abandonment-recovery'
					) }
				</p>
				<Button
					className=""
					icon={ <XMarkIcon class="h-6 w-6 text-text-primary" /> }
					iconPosition="left"
					size="xs"
					tag="button"
					type="button"
					variant="ghost"
					onClick={ handleDismiss }
				/>
			</div>
		</div>
	);
};

export default LegacyUiNotice;

