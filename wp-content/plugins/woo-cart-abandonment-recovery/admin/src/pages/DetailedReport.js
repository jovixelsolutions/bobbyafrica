import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Button, toast } from '@bsf/force-ui';
import { doApiFetch } from '@Store';
import { ChevronLeftIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import EmailDetails from '@Components/detailedReport/EmailDetails';
import UserOrderDetails from '@Components/detailedReport/UserOrderDetails';
import UserAddressDetails from '@Components/detailedReport/UserAddressDetails';
import SmsDetails from '@Components/detailedReport/SmsDetails';
import WhatsappDetails from '@Components/detailedReport/WhatsappDetails';

const DetailedReport = () => {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ scheduledEmails, setScheduledEmails ] = useState( [] );
	const [ scheduledMessages, setScheduledMessages ] = useState( [] );
	const [ scheduledSms, setScheduledSms ] = useState( [] );
	const [ userDetails, setUserDetails ] = useState( {} );
	const [ orderDetails, setOrderDetails ] = useState( {} );
	const [ orderStatus, setOrderStatus ] = useState( '' );
	const [ email, setEmail ] = useState( '' );
	const [ checkoutLink, setCheckoutLink ] = useState( '' );
	const [ sessionId, setSessionId ] = useState( '' );
	const [ unsubscribed, setUnsubscribed ] = useState( '' );
	const [ emailButtonLoading, setEmailButtonLoading ] = useState( false );
	const [ smsButtonLoading, setSmsButtonLoading ] = useState( false );
	const [ messageButtonLoading, setMessageButtonLoading ] = useState( false );
	const navigate = useNavigate();
	const urlParams = new URLSearchParams( useLocation().search );
	const reportId = urlParams.get( 'id' );

	// Load data
	useEffect( () => {
		const fetchData = async () => {
			setIsLoading( true );
			await doApiFetch(
				'/wcar/api/admin/detailed-report/',
				{ id: reportId },
				'POST',
				( response ) => {
					setScheduledEmails( response.scheduled_emails || [] );
					setScheduledSms( response.scheduled_sms || [] );
					setScheduledMessages(
						response.scheduled_whatsapp_messages || []
					);
					setUserDetails( response.user_details || {} );
					setOrderDetails( response.order_details || {} );
					setOrderStatus( response.details?.order_status || '' );
					setEmail( response.details?.email || '' );
					setCheckoutLink( response.checkout_link || '' );
					setSessionId( response.details?.session_id || '' );
					setUnsubscribed( response.details?.unsubscribed || '' );
					setIsLoading( false );
				},
				() => {
					setIsLoading( false );
				}
			);
		};
		if ( reportId ) {
			fetchData();
		}
	}, [ reportId ] );

	const handleBackToReports = () => {
		navigate( {
			search: '?page=woo-cart-abandonment-recovery&path=follow-up',
		} );
	};

	const handleRescheduleEmails = () => {
		if ( ! sessionId ) {
			return;
		}
		setEmailButtonLoading( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.reschedule_emails_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_reschedule_emails' );
		formData.append( 'session_id', sessionId );
		formData.append( 'security', nonce );
		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__(
							'Email Scheduled Successfully',
							'woo-cart-abandonment-recovery'
						)
					);
					setScheduledEmails( response.data?.scheduled_emails || [] );
				} else {
					toast.error(
						__(
							'Email Scheduling failed',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
				setEmailButtonLoading( false );
			},
			( error ) => {
				toast.error(
					__(
						'Email Scheduling failed',
						'woo-cart-abandonment-recovery'
					),
					{
						description: error.data?.message || '',
					}
				);
				setEmailButtonLoading( false );
			},
			true,
			false
		);
	};

	const handleRescheduleSms = () => {
		if ( ! sessionId ) {
			return;
		}
		setSmsButtonLoading( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.reschedule_sms_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_pro_reschedule_sms' );
		formData.append( 'session_id', sessionId );
		formData.append( 'security', nonce );
		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__(
							'SMS Scheduled Successfully',
							'woo-cart-abandonment-recovery'
						)
					);
					setScheduledSms( response.data?.scheduled_sms || [] );
				} else {
					toast.error(
						__(
							'SMS Scheduling failed',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
				setSmsButtonLoading( false );
			},
			( error ) => {
				toast.error(
					__(
						'SMS Scheduling failed',
						'woo-cart-abandonment-recovery'
					),
					{
						description: error.data?.message || '',
					}
				);
				setSmsButtonLoading( false );
			},
			true,
			false
		);
	};

	const handleRescheduleMessages = () => {
		if ( ! sessionId ) {
			return;
		}
		setMessageButtonLoading( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce =
			cart_abandonment_admin?.reschedule_whatsapp_messages_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_pro_reschedule_whatsapp_messages' );
		formData.append( 'session_id', sessionId );
		formData.append( 'security', nonce );
		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__(
							'Messages Scheduled Successfully',
							'woo-cart-abandonment-recovery'
						)
					);
					setScheduledMessages(
						response.data?.scheduled_messages || []
					);
				} else {
					toast.error(
						__(
							'Message Scheduling failed',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
				setMessageButtonLoading( false );
			},
			( error ) => {
				toast.error(
					__(
						'Message Scheduling failed',
						'woo-cart-abandonment-recovery'
					),
					{
						description: error.data?.message || '',
					}
				);
				setMessageButtonLoading( false );
			},
			true,
			false
		);
	};

	return (
		<div className="p-4 md:p-8">
			<SectionWrapper className="p-4 flex flex-col gap-5">
				<Button
					className="w-fit bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300"
					size="sm"
					tag="button"
					type="button"
					variant="outline"
					icon={ <ChevronLeftIcon className="h-5 w-5" /> }
					iconPosition="left"
					onClick={ handleBackToReports }
				>
					Back to Reports
				</Button>
				<div className="p-1 rounded-lg flex flex-col gap-1 bg-light-background">
					{ /* Email Details Section */ }
					<EmailDetails
						scheduledEmails={ scheduledEmails }
						handleRescheduleEmails={ handleRescheduleEmails }
						isLoading={ isLoading }
						buttonLoading={ emailButtonLoading }
						disabled={ 'Blacklisted' === orderStatus }
					/>
					<SmsDetails
						scheduledSms={ scheduledSms }
						handleRescheduleSms={ handleRescheduleSms }
						isLoading={ isLoading }
						buttonLoading={ smsButtonLoading }
						disabled={ 'Blacklisted' === orderStatus }
					/>
					<WhatsappDetails
						scheduledMessages={ scheduledMessages }
						handleRescheduleMessage={ handleRescheduleMessages }
						isLoading={ isLoading }
						buttonLoading={ messageButtonLoading }
						disabled={ 'Blacklisted' === orderStatus }
					/>
					{ /* User Address Details Section */ }
					<UserAddressDetails
						userDetails={ userDetails }
						email={ email }
						orderStatus={ orderStatus }
						checkoutLink={ checkoutLink }
						unsubscribed={ unsubscribed }
						isLoading={ isLoading }
					/>
					{ /* User Order Details Section */ }
					<UserOrderDetails
						orderDetails={ orderDetails }
						isLoading={ isLoading }
					/>
				</div>
			</SectionWrapper>
		</div>
	);
};

export default DetailedReport;

