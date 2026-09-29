import { useState } from 'react';
import { Button, Loader, toast } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';
import { doApiFetch } from '@Store';

const DeleteCouponsField = ( { title, description } ) => {
	const [ isDeleting, setIsDeleting ] = useState( false );

	const handleTrigger = () => {
		setIsDeleting( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = wcf_ca_localized_vars._delete_coupon_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcf_ca_delete_garbage_coupons' );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__(
							'Coupons deleted',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data || '',
						}
					);
				} else {
					toast.error(
						__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data || '',
						}
					);
				}
				setIsDeleting( false );
			},
			( error ) => {
				toast.error(
					__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error.data || '',
					}
				);
				setIsDeleting( false );
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
					disabled={ isDeleting }
					icon={
						isDeleting && (
							<Loader
								className="text-primary-600"
								size="md"
								variant="primary"
							/>
						)
					}
					iconPosition="left"
				>
					Delete
				</Button>
			</div>
		</FieldWrapper>
	);
};

export default DeleteCouponsField;
