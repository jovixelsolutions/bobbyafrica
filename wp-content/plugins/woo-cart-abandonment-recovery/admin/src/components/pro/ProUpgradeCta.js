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
import { __ } from '@wordpress/i18n';
import ProUpgradeCtaModal from './ProUpgradeCtaModal';
import ProUpgradeCtaMsg from './ProUpgradeCtaMsg';

/**
 * ProUpgradeCta Component - Simplified with ForceUI
 * Shows by default when pro features are not accessible
 * Positioned within parent container, not full screen
 *
 * @param {Object}  root0                  - Component props.
 * @param {boolean} root0.isVisible        - Whether the modal is visible.
 * @param {string}  root0.mainTitle        - Main Popup Title.
 * @param {string}  root0.subTitle         - Subtitle of the popup.
 * @param {string}  root0.description      - Popup Description.
 * @param {string}  root0.uspTitle         - USP Title.
 * @param {Array}   root0.usps             - USPs in array format.
 * @param {string}  root0.actionBtnText    - Button Text.
 * @param {string}  root0.actionbtnUrl     - Button custom URL.
 * @param {string}  root0.actionBtnUrlArgs - Button args used to send the UTM paramaters.
 * @param {string}  root0.footerMessage    - Footer message.
 * @param {string}  root0.variation        - Message variations: modal, message.
 * @param {string}  root0.highlightText    - The Highlight Text to display before the main heading.
 * @param {boolean} root0.backgroundBlur   - Whether to add blurred background to the modal.
 */
const ProUpgradeCta = ( {
	isVisible = false,
	highlightText = __( 'Upgrade to Pro', 'woo-cart-abandonment-recovery' ),
	mainTitle = __( 'Unlock Pro Features', 'woo-cart-abandonment-recovery' ),
	subTitle = '',
	description = '',
	uspTitle = '',
	usps = [],
	actionBtnText = '',
	actionbtnUrl = '',
	actionBtnUrlArgs = '',
	footerMessage = '',
	variation = 'modal',
	backgroundBlur = false,
} ) => {
	if ( ! isVisible ) {
		return null;
	}

	// Prepare props object to pass to child components
	const componentProps = {
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
	};

	return (
		<div className="absolute inset-0 z-10 flex items-center justify-center">
			{ /* White blurred background overlay - reduced blur for better readability */ }
			{ backgroundBlur && (
				<div className="absolute inset-0 bg-white/20 backdrop-blur-[2px]"></div>
			) }

			{ variation === 'modal' ? (
				<ProUpgradeCtaModal props={ componentProps } />
			) : (
				<ProUpgradeCtaMsg props={ componentProps } />
			) }
		</div>
	);
};

export default ProUpgradeCta;
