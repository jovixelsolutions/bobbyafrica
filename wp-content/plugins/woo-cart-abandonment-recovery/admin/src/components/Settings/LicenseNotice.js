import React from 'react';
import { __ } from '@wordpress/i18n';
import { Container, Title, Button } from '@bsf/force-ui';

function LicenseNotice( props ) {
	const { navigate } = props;
	const { cart_abandonment_admin } = window;

	if ( cart_abandonment_admin?.license_status ) {
		return null;
	}

	return (
		<div className="relative">
			<Container
				align="center"
				containerType="flex"
				direction="row"
				justify="center"
				className="absolute top-0 left-0 w-full h-full bg-opacity-50 backdrop-blur-sm bg-white/30 z-[9]"
			>
				<Container.Item className="absolute p-6 bg-white shadow-md rounded-lg text-center">
					<Container
						containerType="flex"
						direction="row"
						justify="center"
						className="mb-4"
					>
						<svg
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							className="h-8 w-8 text-red-500 stroke-1"
						>
							<path
								strokeLinecap="round"
								strokeLinejoin="round"
								d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"
							/>
						</svg>
					</Container>
					<Title
						size="md"
						tag="h2"
						title={ __(
							'Activate Your License',
							'woo-cart-abandonment-recovery'
						) }
						description={ __(
							'Please activate your license to get access to all pro features.',
							'woo-cart-abandonment-recovery'
						) }
						className="mb-6 text-center [&_h2]:text-gray-900 [&_p]:!text-gray-600 text-xl"
					/>
					<Button
						variant="primary"
						onClick={ () => {
							navigate( 'license-settings' );
						} }
						className="inline-flex justify-center items-center gap-1.5 rounded px-3 py-2 text-sm font-normal shadow-sm text-flamingo-500 hover:text-white border border-solid border-flamingo-500 bg-white hover:bg-flamingo-500 outline-0 hover:outline-0 focus:ring-0"
					>
						{ __(
							'Activate License',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
				</Container.Item>
			</Container>
			<div className="pointer-events-none select-none px-6">
				<div className="opacity-50">
					<p className="text-gray-500">
						{ __(
							'Pro features are locked until license is activated.',
							'woo-cart-abandonment-recovery'
						) }
					</p>
				</div>
			</div>
		</div>
	);
}

export default LicenseNotice;
