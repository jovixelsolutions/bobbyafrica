import { useState } from 'react';
import { Button, Loader, toast } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';
import { doApiFetch, useStateValue } from '@Store';

const UiSwitch = ( { title, description, name } ) => {
	const [ isSwitching, setIsSwitching ] = useState( false );
	const [ state ] = useStateValue();

	const settingsData = state.settingsData || {};

	const handleTrigger = () => {
		setIsSwitching( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.save_setting_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_save_setting' );
		formData.append( 'security', nonce );
		formData.append( 'option_name', name );
		formData.append( 'value', '' );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__(
							'UI Switched successfully!',
							'woo-cart-abandonment-recovery'
						),
						{
							description: '',
						}
					);
					setTimeout( () => {
						window.location.href = cart_abandonment_admin.admin_url;
					}, 1000 );
				} else {
					toast.error(
						__(
							'UI Switch failed!',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data || '',
						}
					);
				}
				setIsSwitching( false );
			},
			( error ) => {
				toast.error(
					__( 'UI Switch failed!', 'woo-cart-abandonment-recovery' ),
					{
						description: error.data || '',
					}
				);
				setIsSwitching( false );
			},
			true,
			false
		);
	};

	return (
		<FieldWrapper title={ title } description={ description } type="inline">
			<div className="my-auto">
				<Button
					className="py-2 px-4 bg-red-50 text-red-600 outline-red-600 hover:bg-red-50 hover:outline-red-600"
					size="md"
					tag="button"
					type="button"
					variant="outline"
					onClick={ handleTrigger }
					disabled={
						isSwitching || settingsData?.values[ name ] === ''
					}
					icon={
						isSwitching && (
							<Loader
								className="text-primary-600"
								size="md"
								variant="primary"
							/>
						)
					}
					iconPosition="left"
				>
					{ __( 'Switch UI', 'woo-cart-abandonment-recovery' ) }
				</Button>
			</div>
		</FieldWrapper>
	);
};

export default UiSwitch;
