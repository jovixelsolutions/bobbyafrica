import { select, dispatch } from '@wordpress/data';
import { STORE_NAME } from '@/store/constants';
import { calculateCheckStatus } from '@SeoPopup/utils/calculate-check-status';
import { refreshPageChecks } from '@SeoPopup/components/page-seo-checks/analyzer/utils/page-builder';
import { ENABLE_PAGE_LEVEL_SEO } from '@/global/constants';

let brokenLinkState = {
	isChecking: false,
	checkedLinks: new Set(),
	brokenLinks: new Set(),
	allLinks: [],
};
let refreshCalled = false;

const setBrokenLinkState = ( updater ) => {
	if ( typeof updater === 'function' ) {
		brokenLinkState = updater( brokenLinkState );
	} else {
		brokenLinkState = updater;
	}
};

const setRefreshCalled = ( value ) => {
	refreshCalled = value;
};

// Function to handle refresh with broken links - adapted for Elementor context
export const handleRefreshWithBrokenLinks = async () => {
	const storeDispatch = dispatch( STORE_NAME );
	const storeSelectors = select( STORE_NAME );

	if ( ! storeSelectors || ! storeDispatch || ! ENABLE_PAGE_LEVEL_SEO ) {
		return;
	}

	if ( refreshCalled ) {
		return;
	}

	try {
		setRefreshCalled( true ); // Ensure subsequent calls don't auto-refresh

		// Get current page SEO checks
		const pageSeoChecks = storeSelectors.getPageSeoChecks() || {};

		await refreshPageChecks(
			() => {},
			setBrokenLinkState,
			storeDispatch.setPageSeoCheck,
			select,
			pageSeoChecks,
			brokenLinkState
		);
	} catch ( error ) {
		// Silently ignore errors
	}
};

// Function to get page check status using WordPress data
export const getPageCheckStatus = () => {
	try {
		const storeSelectors = select( STORE_NAME );
		if (
			! storeSelectors ||
			typeof storeSelectors.getPageSeoChecks !== 'function'
		) {
			return {
				status: null,
				initializing: true,
				counts: { errorAndWarnings: 0, error: 0, warning: 0 },
			};
		}

		// Trigger ignored list retrieval
		const pageSeoChecks = storeSelectors.getPageSeoChecks() || {};
		const { categorizedChecks = {}, initializing = true } = pageSeoChecks;

		const { status, counts } = calculateCheckStatus( categorizedChecks );

		if ( initializing ) {
			dispatch( STORE_NAME ).setPageSeoCheck( 'initializing', false );
		}

		return {
			status,
			initializing,
			counts,
		};
	} catch ( error ) {
		// Return safe defaults if store is not available
		return {
			status: null,
			initializing: false,
			counts: { errorAndWarnings: 0, error: 0, warning: 0 },
		};
	}
};
