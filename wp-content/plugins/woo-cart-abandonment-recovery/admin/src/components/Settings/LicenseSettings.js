import React, { useState, useEffect, useRef } from 'react';
import { Title, Button, Input, Label, Container, Loader } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import parse from 'html-react-parser';

import apiFetch from '@wordpress/api-fetch';
import { useStateValue, ActionTypes } from '@Store';

function LicenseSettings() {
	const [ state, dispatch ] = useStateValue();
	const isLicenseActivated = state?.licenseStatus === '1' ? true : false;

	const activateBtnText = __(
		'Activate License',
		'woo-cart-abandonment-recovery'
	);
	const deActivateBtnText = __(
		'Deactivate License',
		'woo-cart-abandonment-recovery'
	);

	const [ activateLicenseText, setActivateLicenseText ] = useState( {
		licenseButtonText: ! isLicenseActivated
			? activateBtnText
			: deActivateBtnText,
		licenseActivationProcess: false,
	} );

	const { licenseButtonText, licenseActivationProcess } = activateLicenseText;
	const [ licenseKey, setLicenseKey ] = useState(
		isLicenseActivated ? '**************************' : ''
	);
	const [ licenseErrors, setLicenseErrors ] = useState( false );
	const [ showDeactivateLicenseBtn, setShowDeactivateLicenseBtn ] =
		useState( isLicenseActivated );

	const inputRef = useRef( null );

	const disabledBtnClasses =
		'disabled:pointer-events-none disabled:bg-gray-200 disabled:text-while disabled:border-none disabled:text-gray-400';

	useEffect( () => {
		if ( inputRef.current ) {
			inputRef.current.focus();
		}
	}, [] );

	/**
	 * Ajax call to activate/deactivate the WCAR PRO license key.
	 *
	 * @param { event } event
	 */
	const activateLicense = function ( event ) {
		event.preventDefault();

		setLicenseErrors( false );

		if ( '' === licenseKey && ! isLicenseActivated ) {
			setLicenseErrors(
				__(
					'Please enter a valid license key!',
					'woo-cart-abandonment-recovery'
				)
			);
			return;
		}

		event.target.disabled = true;

		const ajax_nonce = event.currentTarget.dataset.nonce;
		let ajax_action = event.currentTarget.dataset.action;
		setActivateLicenseText( {
			licenseButtonText: __(
				'Processing',
				'woo-cart-abandonment-recovery'
			),
			licenseActivationProcess: true,
		} );

		if ( 'activate_license' === ajax_action ) {
			ajax_action = 'wcar_activate_license';
		} else {
			ajax_action = 'wcar_deactivate_license';
		}

		const formData = new window.FormData();

		formData.append( 'action', ajax_action );
		formData.append( 'license_key', licenseKey );
		formData.append( 'security', ajax_nonce );

		apiFetch( {
			url: cart_abandonment_admin.ajax_url,
			method: 'POST',
			body: formData,
		} ).then( ( respData ) => {
			if ( respData.success ) {
				event.target.blur();

				if ( 'wcar_activate_license' === ajax_action ) {
					dispatch( {
						type: ActionTypes.UPDATE_LICENSE_STATUS,
						payload: '1',
					} );
					setShowDeactivateLicenseBtn( true );
					setActivateLicenseText( {
						licenseButtonText: deActivateBtnText,
						licenseActivationProcess: false,
					} );

					setLicenseKey( '**************************' );
					inputRef.current.classList.add( 'disabled' );
					inputRef.current.readOnly = true;
					event.target.disabled = false;
				} else {
					dispatch( {
						type: ActionTypes.UPDATE_LICENSE_STATUS,
						payload: '',
					} );
					setShowDeactivateLicenseBtn( false );
					setActivateLicenseText( {
						licenseButtonText: activateBtnText,
						licenseActivationProcess: false,
					} );

					inputRef.current.classList.remove( 'disabled' );
					inputRef.current.readOnly = false;
					setLicenseKey( '' );
					event.target.disabled = false;
				}
				window.location.reload();
			} else {
				const msg = respData.data?.error || respData.data || '';

				if ( msg ) {
					setLicenseErrors( msg );
				} else {
					setLicenseErrors(
						__(
							'Unknown error occurred while activating the license.',
							'woo-cart-abandonment-recovery'
						)
					);
				}
				event.target.blur();
				setActivateLicenseText( {
					licenseButtonText: activateBtnText,
					licenseActivationProcess: false,
				} );
			}
		} );
	};

	return (
		<>
			<Title
				description=""
				icon={ null }
				size="md"
				tag="h2"
				title={ __( 'My Account', 'woo-cart-abandonment-recovery' ) }
				className="mb-6 [&_h2]:text-gray-900 text-xl"
			/>
			<div className="h-auto bg-white rounded-xl shadow-sm">
				<Container
					containerType="flex"
					direction="column"
					className="p-6"
				>
					<Container.Item>
						<Label
							className="font-semibold mb-1"
							htmlFor="license-key"
							size="md"
						>
							{ __(
								'License Key',
								'woo-cart-abandonment-recovery'
							) }
						</Label>
						<p className="font-normal text-sm text-gray-600 m-0 mb-4">
							{ __(
								"Enter your valid license key below to activate Cart Abandonment Recovery Pro. If you don't have a license key yet, you can get it from your account.",
								'woo-cart-abandonment-recovery'
							) }
						</p>
					</Container.Item>
					<Container.Item className="w-full relative flex gap-4 flex-col sm:flex-row">
						<div className="flex-grow relative">
							<Input
								type="text"
								size="md"
								prefix={
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										strokeWidth={ 1.5 }
										stroke="currentColor"
										className="h-4 w-6 text-gray-400"
									>
										<path
											strokeLinecap="round"
											strokeLinejoin="round"
											d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"
										/>
									</svg>
								}
								className={ `h-full w-full wcar-license-key disabled:!bg-gray-100 focus:[&>input]:ring-focus ${
									licenseErrors
										? '!border-red-300 focus:border-red-500 !shadow-none !outline-0 !outline-none'
										: ''
								}` }
								name={ 'wcar_license_key' }
								value={ licenseKey }
								onChange={ ( value ) => setLicenseKey( value ) }
								disabled={ isLicenseActivated ? true : false }
								ref={ inputRef }
							/>
							{ isLicenseActivated && (
								<Container
									align="center"
									containerType="flex"
									direction="row"
									className="pointer-events-none absolute top-[0.3rem] h-9 right-0 pr-3"
								>
									<Container.Item>
										<svg
											className="h-5 w-5 text-green-500"
											viewBox="0 0 24 24"
											fill="currentColor"
											aria-hidden="true"
										>
											<path
												fillRule="evenodd"
												d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
												clipRule="evenodd"
											/>
										</svg>
									</Container.Item>
								</Container>
							) }
							{ licenseErrors && (
								<Container
									align="center"
									containerType="flex"
									direction="row"
									className="pointer-events-none absolute top-[0.3rem] h-9 right-0 pr-3"
								>
									<Container.Item>
										<svg
											className="h-5 w-5 text-red-500"
											viewBox="0 0 20 20"
											fill="currentColor"
											aria-hidden="true"
										>
											<path
												fillRule="evenodd"
												d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
												clipRule="evenodd"
											/>
										</svg>
									</Container.Item>
								</Container>
							) }
						</div>
						<div>
							{ showDeactivateLicenseBtn ? (
								<Button
									variant="primary"
									className={ `${ disabledBtnClasses } w-full outline-0 hover:outline-0 focus:ring-0` }
									onClick={ activateLicense }
									icon={
										licenseActivationProcess && (
											<Loader
												className="bg-transparent text-white"
												icon={ null }
												size="sm"
												variant="primary"
											/>
										)
									}
									iconPosition="right"
									data-action="deactivate_license"
									data-nonce={
										cart_abandonment_admin?.pro_nonces
											?.deactivate_license_nonce
									}
								>
									{ licenseButtonText }
								</Button>
							) : (
								<Button
									variant="primary"
									className={ `${ disabledBtnClasses } w-full outline-0 hover:outline-0 focus:ring-0` }
									onClick={ activateLicense }
									icon={
										licenseActivationProcess && (
											<Loader
												className="bg-transparent text-white"
												icon={ null }
												size="sm"
												variant="primary"
											/>
										)
									}
									iconPosition="right"
									data-action="activate_license"
									data-nonce={
										cart_abandonment_admin?.pro_nonces
											?.activate_license_nonce
									}
								>
									{ licenseButtonText }
								</Button>
							) }
						</div>
					</Container.Item>
					{ licenseErrors && (
						<Container.Item>
							<div className="license-errors text-red-600 mt-2">
								{ parse( licenseErrors ) }
							</div>
						</Container.Item>
					) }
				</Container>
			</div>
		</>
	);
}

export default LicenseSettings;
