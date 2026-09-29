import { useState } from 'react';
import { Input, Button, Loader, toast } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';
import { doApiFetch } from '@Store';

const TestEmail = ( { title, description, value } ) => {
	const [ email, setEmail ] = useState( '' );
	const [ isSending, setIsSending ] = useState( false );

	const validateFields = () => {
		if ( ! email ) {
			toast.error( 'Please enter an email address' );
			return false;
		}
		if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
			toast.error( 'Please enter a valid email address' );
			return false;
		}
		if ( ! value.email_subject ) {
			toast.error( 'Please enter a email subject' );
			return false;
		}
		if ( ! value.email_body ) {
			toast.error( 'Please enter a email body' );
			return false;
		}
		return true;
	};

	const handleClick = () => {
		if ( ! validateFields() ) {
			return;
		}
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.send_preview_email_nonce;

		const formData = new FormData();
		formData.append( 'action', 'wcar_send_preview_email' );
		formData.append( 'email_send_to', email );
		formData.append( 'email_subject', value.email_subject );
		formData.append( 'email_body', value.email_body );
		formData.append( 'security', nonce );

		setIsSending( true );
		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success( 'Test email sent successfully!' );
				} else {
					toast.error(
						__(
							'Failed to send test email',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
				setIsSending( false );
			},
			( error ) => {
				toast.error(
					__(
						'Failed to send test email',
						'woo-cart-abandonment-recovery'
					),
					{
						description: error.data?.message || '',
					}
				);
				setIsSending( false );
			},
			true
		);
	};

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="block"
			size="small"
		>
			<div className="flex gap-2 items-center">
				<div className="flex-1">
					<Input
						className="w-full focus:[&>input]:ring-focus"
						type="email"
						size="md"
						value={ email }
						onChange={ setEmail }
						placeholder="Enter Email"
					/>
				</div>
				<Button
					className="w-fit bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300"
					size="md"
					tag="button"
					variant="outline"
					icon={
						isSending && (
							<Loader
								className="text-primary-600"
								size="md"
								variant="primary"
							/>
						)
					}
					iconPosition="left"
					onClick={ handleClick }
					disabled={ isSending }
				>
					Send a Test Email
				</Button>
			</div>
		</FieldWrapper>
	);
};

export default TestEmail;
