/**
 * Global Pro Upgrade Modal Component
 *
 * A comprehensive, reusable modal component for handling all pro upgrade scenarios.
 * Fully parameterized to handle different features, statuses, and custom content.
 *
 * @package
 * @since 1.0.0
 */

import React from 'react';
import { Title, Text, Button } from '@bsf/force-ui';
import ctaBanner from '@Images/cta-banner.svg';
import { useProAccess } from '@Components/pro/useProAccess';

import { CheckIcon, BoltIcon } from '@heroicons/react/24/outline';

/**
 * ProUpgradeCtaModal Component - Simplified with ForceUI
 * Shows by default when pro features are not accessible
 * Positioned within parent container, not full screen
 *
 * @param {Object} root0       - Component props
 * @param {Object} root0.props - Props object containing all component props
 */
const ProUpgradeCtaModal = ( { props } ) => {
	const { getUpgradeMessage, getActionButtonText, upgradeActionButton } =
		useProAccess();
	// Destructure props from the props object
	const {
		highlightText,
		mainTitle,
		description,
		subTitle,
		uspTitle,
		usps,
		actionBtnText,
		actionbtnUrl,
		actionBtnUrlArgs,
		footerMessage,
		backgroundBlur,
	} = props;

	return (
		<div className="absolute inset-0 z-10 flex items-center justify-center">
			{ /* White blurred background overlay - reduced blur for better readability */ }
			{ backgroundBlur && (
				<div className="absolute inset-0 bg-white/20 backdrop-blur-[2px]"></div>
			) }

			{ /* Modal content - reduced height */ }
			<div className="relative z-20 bg-white rounded-lg shadow-2xl w-full max-w-xl mx-4 p-6 border border-gray-200">
				<div className="wcar-cta-content--wrapper flex flex-col gap-2.5 p-6 rounded-xl border border-solid border-gray-200">
					{ /* CTA Banner */ }
					<div className="wcar-cta-content--banner text-center mb-4">
						<img
							src={ ctaBanner }
							alt="Call to Action"
							aria-label="Call to Action"
						/>
					</div>

					{ /* Heading */ }
					<div className="wcar-cta-content-title">
						<Text
							as="span"
							color="primary"
							size={ 14 }
							className="text-primary-600 flex items-center gap-1 mt-0 mb-2"
						>
							<BoltIcon className="w-5 h-5 inline-block" />
							{ highlightText }
						</Text>
						<Title size="md" tag="h2" title={ mainTitle } />

						{ /* Sub-heading */ }
						{ subTitle && (
							<p className="text-lg text-gray-600">
								{ getUpgradeMessage( subTitle ) }
							</p>
						) }

						{ description && (
							<p className="text-sm text-gray-500 mt-2.5 mb-0">
								{ description }
							</p>
						) }
					</div>

					{ /* USPs List */ }
					<div className="wcar-cta-content--usps">
						{ uspTitle && (
							<h3 className="text-lg font-semibold text-gray-900 m-0">
								{ uspTitle }
							</h3>
						) }
						{ usps && (
							<ol className="grid grid-cols-2 text-gray-700 m-0">
								{ usps.map( ( usp, index ) => (
									<li
										key={ index }
										className="flex gap-1 items-center mt-1"
									>
										<CheckIcon className="w-3.5 h-3.5 text-primary-600" />
										<span className="text-sm leading-relaxed">
											{ usp }
										</span>
									</li>
								) ) }
							</ol>
						) }
					</div>

					{ /* Action button - single button only */ }
					<Button
						variant="primary"
						size="md"
						className="px-8 py-3 w-full mt-2.5"
						onClick={ () => {
							upgradeActionButton(
								actionBtnUrlArgs,
								actionbtnUrl
							);
						} }
					>
						{ actionBtnText
							? actionBtnText
							: getActionButtonText() }
					</Button>

					{ /* Additional info */ }
					{ footerMessage && (
						<div className="text-center text-sm text-gray-500">
							<p>{ footerMessage }</p>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};

export default ProUpgradeCtaModal;
