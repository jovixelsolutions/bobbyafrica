/* eslint-disable indent */
import { useEffect, useState } from 'react';
import { Button, Title, Loader } from '@bsf/force-ui';
import { CheckIcon, PlusIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import SectionWrapper from '../common/SectionWrapper';
import InstallationMessage from '@Images/installation-message.svg';

const Ottokit = () => {
	const [ ottokitStatus, setOttokitStatus ] = useState(
		cart_abandonment_admin.extend_plugins.find(
			( plugin ) => 'suretriggers' === plugin.slug
		).status
	);
	const [ buttonProcessing, SetButtonProcessing ] = useState( false );

	useEffect( () => {
		if ( 'active' !== cart_abandonment_admin?.ottokit?.status ) {
			return;
		}

		if ( ! cart_abandonment_admin?.ottokit?.is_ottokit_connected ) {
			window.location.href =
				cart_abandonment_admin?.ottokit?.ottokit_redirect_url;
		}

		const config = cart_abandonment_admin?.ottokit?.config;
		if (
			window.SureTriggers &&
			document.getElementById( 'wcar-iframe-wrapper' )
		) {
			window.SureTriggers.init( config );
		}
	}, [] );

	const getOttokitCta = function ( status ) {
		let statusText = '';

		switch ( status ) {
			case 'inactive':
				statusText = __( 'Activate', 'woo-cart-abandonment-recovery' );
				break;
			case 'installing':
				statusText = __(
					'Installing…',
					'woo-cart-abandonment-recovery'
				);
				break;
			case 'activating':
				statusText = __(
					'Activating…',
					'woo-cart-abandonment-recovery'
				);
				break;
			case 'fail':
				statusText = __( 'Failed', 'woo-cart-abandonment-recovery' );
				break;
			case 'reload':
				statusText = __( 'Reloading', 'woo-cart-abandonment-recovery' );
				break;
			default:
				statusText = __(
					'Install & Activate',
					'woo-cart-abandonment-recovery'
				);
		}

		return statusText;
	};

	const handleInstallActivateOttokit = function ( e ) {
		e.preventDefault();

		if ( 'not-installed' === ottokitStatus ) {
			setOttokitStatus( 'installing' );
			SetButtonProcessing( true );

			const formData = new window.FormData();

			formData.append( 'action', 'cart_abandonment_install_plugin' );
			formData.append( 'slug', 'suretriggers' );
			formData.append(
				'_ajax_nonce',
				cart_abandonment_admin.plugin_installer_nonce
			);

			apiFetch( {
				url: cart_abandonment_admin.ajax_url,
				method: 'POST',
				body: formData,
			} ).then( ( res ) => {
				if ( res.success ) {
					activate_plugin( 'suretriggers/suretriggers.php' );
				} else {
					SetButtonProcessing( false );
					setOttokitStatus( 'fail' );
				}
			} );
		} else if ( 'inactive' === ottokitStatus ) {
			activate_plugin( 'suretriggers/suretriggers.php' );
		}
	};

	const activate_plugin = ( slug ) => {
		setOttokitStatus( 'activating' );
		SetButtonProcessing( true );
		const formData = new window.FormData();
		formData.append( 'action', 'cart_abandonment_activate_plugin' );
		formData.append( 'init', slug );
		formData.append(
			'security',
			cart_abandonment_admin.plugin_activation_nonce
		);
		apiFetch( {
			url: cart_abandonment_admin.ajax_url,
			method: 'POST',
			body: formData,
		} ).then( ( response ) => {
			SetButtonProcessing( false );
			if ( response && response.success ) {
				setOttokitStatus( 'reload' );
				setTimeout( () => {
					window.location.reload();
				}, 2000 );
			} else {
				setOttokitStatus( 'fail' );
			}
		} );
	};

	return (
		<div className="p-8">
			<SectionWrapper>
				{ 'active' === ottokitStatus ? (
					<div
						className="h-[75vh] w-full"
						id="wcar-iframe-wrapper"
					></div>
				) : (
					<div className="p-6 flex justify-between items-center bg-white gap-6">
						<div>
							<img
								src={ InstallationMessage }
								className="h-80 w-80 object-contain"
								alt=""
							/>
						</div>
						<div className="flex flex-col gap-2">
							<Title
								size="sm"
								tag="h4"
								title="Automate Cart Recovery with OttoKit"
								className="[&_h4]:text-gray-900"
							/>
							<p className="m-0 text-base font-normal text-gray-500">
								Supercharge your recovery process by connecting
								WooCommerce Cart Abandonment Recovery with
								OttoKit. Get real-time alerts, automate actions,
								and keep your team in the loop instantly.
							</p>
							<div className="flex flex-col gap-[1px]">
								<div className="flex items-center gap-1.5">
									<CheckIcon className="h-4 w-4 text-gray-500" />
									<span className="text-gray-500 text-base font-normal">
										Connect with tools you already use like
										Slack, Telegram, WhatsApp, Google
										Sheets, and more.
									</span>
								</div>
								<div className="flex items-center gap-1.5">
									<CheckIcon className="h-4 w-4 text-gray-500" />
									<span className="text-gray-500 text-base font-normal">
										Engage customers on channels they use
										like SMS, WhatsApp, or Facebook
										Messenger.
									</span>
								</div>
								<div className="flex items-center gap-1.5">
									<CheckIcon className="h-4 w-4 text-gray-500" />
									<span className="text-gray-500 text-base font-normal">
										Automatically resend failed emails or
										trigger alerts to your team.
									</span>
								</div>
							</div>
							<div className="mt-4">
								<Button
									icon={
										buttonProcessing ? (
											<Loader
												className="text-white"
												size="md"
												variant="primary"
											/>
										) : (
											'reload' !== ottokitStatus && (
												<PlusIcon
													aria-label="icon"
													role="img"
												/>
											)
										)
									}
									iconPosition="left"
									size="md"
									tag="button"
									type="button"
									variant="primary"
									onClick={ handleInstallActivateOttokit }
								>
									{ getOttokitCta( ottokitStatus ) }
								</Button>
							</div>
						</div>
					</div>
				) }
			</SectionWrapper>
		</div>
	);
};

export default Ottokit;
