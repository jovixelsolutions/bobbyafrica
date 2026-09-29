import { select } from '@wordpress/data';
import { STORE_NAME } from '@/store/constants';
import { handleOpenSureRankDrawer } from '@SeoPopup/utils/page-builder-functions';
import {
	getPageCheckStatus,
	handleRefreshWithBrokenLinks,
} from '../elementor/page-checks';
import { getTooltipText } from '@/apps/seo-popup/utils/page-checks-status-tooltip-text';

/**
 * Breakdance editor page integration.
 *
 * This script runs in the Breakdance editor page (the SPA host page), loaded
 * via wp_print_scripts() in the Breakdance footer hook. It follows the same
 * pattern as the Bricks integration (src/apps/bricks/index.js).
 *
 * Responsibilities:
 * 1. Wait for the SureRank store to initialize.
 * 2. Trigger initial SEO page checks via handleRefreshWithBrokenLinks().
 * 3. Send status updates to the toolbar button via window.postMessage().
 * 4. Listen for open-popup requests from the toolbar button and open the modal.
 */

/**
 * Send the current SEO check status to the toolbar button.
 *
 * The toolbar button (injected via registerBuilderPlugin) listens for
 * window message events to update its status indicator and tooltip.
 */
const sendStatusToToolbar = () => {
	const { status, counts } = getPageCheckStatus();
	const tooltipText = getTooltipText( counts );

	window.postMessage(
		{ type: 'surerank-status-update', status, counts, tooltipText },
		window.location.origin
	);
};

/**
 * Listen for messages from the toolbar button.
 *
 * - surerank-open-popup: toolbar button clicked, open the SEO popup.
 * - surerank-request-status: button was (re-)injected and needs current status
 *   immediately (e.g. after Breakdance preview mode remounts the toolbar).
 */
window.addEventListener( 'message', ( event ) => {
	if ( ! event.data ) {
		return;
	}
	if ( event.data.type === 'surerank-open-popup' ) {
		handleOpenSureRankDrawer();
	} else if ( event.data.type === 'surerank-request-status' ) {
		sendStatusToToolbar();
	}
} );

/**
 * Wait for the SureRank @wordpress/data store to initialize, then boot the
 * integration. Uses the same pattern as the Elementor/Bricks integrations.
 */
const waitForStoreInit = () => {
	let retryCount = 0;
	let storeUnsubscribe = null;
	let isInitialized = false;
	const maxRetries = 50; // 50 × 100ms = 5 seconds max.

	const cleanup = () => {
		if ( storeUnsubscribe && typeof storeUnsubscribe === 'function' ) {
			storeUnsubscribe();
			storeUnsubscribe = null;
		}
	};

	const onStoreReady = () => {
		isInitialized = true;
		cleanup();

		// Trigger initial SEO checks for this page via REST API.
		handleRefreshWithBrokenLinks();

		// Subscribe to store changes and forward status updates to toolbar button.
		wp?.data?.subscribe?.( () => {
			sendStatusToToolbar();
		} );
	};

	const checkStoreAndInitialize = () => {
		if ( isInitialized ) {
			return;
		}

		try {
			const storeSelectors = select( STORE_NAME );

			if (
				! storeSelectors ||
				typeof storeSelectors.getVariables !== 'function'
			) {
				if ( retryCount < maxRetries ) {
					retryCount++;
					setTimeout( checkStoreAndInitialize, 100 );
				}
				return;
			}

			const variables = storeSelectors.getVariables();

			if ( variables ) {
				onStoreReady();
			} else if ( ! storeUnsubscribe ) {
				storeUnsubscribe = wp?.data?.subscribe?.( () => {
					try {
						const currentVariables =
							select( STORE_NAME )?.getVariables();
						if ( currentVariables && ! isInitialized ) {
							onStoreReady();
						}
					} catch {
						// Silently handle subscription errors.
					}
				} );

				// Fallback timeout to prevent infinite waiting.
				setTimeout( () => {
					if ( ! isInitialized ) {
						if ( select( STORE_NAME )?.getVariables() ) {
							onStoreReady();
						}
					}
				}, 3000 );
			}
		} catch {
			if ( retryCount < maxRetries ) {
				retryCount++;
				setTimeout( checkStoreAndInitialize, 100 );
			}
		}
	};

	checkStoreAndInitialize();
};

waitForStoreInit();
