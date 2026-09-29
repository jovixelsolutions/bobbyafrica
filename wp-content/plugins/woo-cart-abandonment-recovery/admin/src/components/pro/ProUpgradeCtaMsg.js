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
import { Title } from '@bsf/force-ui';
import { useProAccess } from './useProAccess';

/**
 * ProUpgradeCtaMsg Component - Simplified with ForceUI
 * Shows by default when pro features are not accessible
 * Positioned within parent container, not full screen
 *
 * @param {Object} root0       - Component props
 * @param {Object} root0.props - Props object containing all component props
 */
const ProUpgradeCtaMsg = ( { props } ) => {
	const { getActionButtonText, upgradeActionButton } = useProAccess();
	// Destructure props from the props object
	const { mainTitle, subTitle, description, actionBtnUrlArgs, actionbtnUrl } =
		props;

	return (
		<div className="absolute inset-0 z-10 flex items-center justify-center">
			{ /* White blurred background overlay - reduced blur for better readability */ }
			<div className="absolute inset-0 bg-white/20 backdrop-blur-[2px]"></div>

			{ /* Modal content - reduced height */ }
			<div className="relative z-20 bg-white rounded-lg shadow-2xl w-full max-w-lg p-4 border border-gray-200">
				{ /* Heading */ }
				<div className="text-center">
					{ mainTitle && (
						<Title
							size="lg"
							tag="h2"
							title={ mainTitle }
							className="mb-2"
						/>
					) }

					{ /* Sub-heading */ }
					{ subTitle && (
						<p className="text-lg text-gray-600">{ subTitle }</p>
					) }

					{ /* Short Message */ }
					{ description && (
						<p className="text-sm text-gray-500 m-0">
							{ description }
						</p>
					) }

					<span
						className="text-flamingo-400 font-semibold underline cursor-pointer"
						onKeyDown={ ( e ) => {
							if ( e.key === 'Enter' ) {
								upgradeActionButton(
									actionBtnUrlArgs,
									actionbtnUrl
								);
							}
						} }
						onClick={ () =>
							upgradeActionButton(
								actionBtnUrlArgs,
								actionbtnUrl
							)
						}
						role="button"
						tabIndex="0"
					>
						{ getActionButtonText() }
					</span>
				</div>
			</div>
		</div>
	);
};

export default ProUpgradeCtaMsg;
