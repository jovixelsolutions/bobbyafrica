import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { Button, toast } from '@bsf/force-ui';
import { formatSeoChecks, cn } from '@/functions/utils';
import { STORE_NAME } from '@/store/constants';
import { fetchBrokenLinkStatus } from '../link-checks';
import { RefreshCcw } from 'lucide-react';
import { CHECK_TYPES, ENABLE_PAGE_LEVEL_SEO } from '@/global/constants';

// Function to check broken links for Elementor editor
export const checkBrokenLinks = async (
	links,
	postId,
	allLinks,
	setBrokenLinkState,
	setPageSeoCheck,
	brokenLinkState,
	pageSeoChecks
) => {
	const totalLinks = allLinks.length;
	const brokenLinksArray = [];

	for ( const url of links ) {
		let isBroken = false;

		try {
			const result = await fetchBrokenLinkStatus( {
				postId,
				userAgent: window.navigator.userAgent,
				url,
				allLinks,
			} );

			if ( ! result.success ) {
				isBroken = true;
			}
		} catch {
			isBroken = true;
		}

		// Update checkedLinks and collect broken links
		setBrokenLinkState( ( prev ) => {
			const updatedChecked = new Set( prev.checkedLinks );
			const updatedBroken = new Set( prev.brokenLinks );

			updatedChecked.add( url );
			if ( isBroken ) {
				updatedBroken.add( url );
				brokenLinksArray.push( url ); // Add to array
			}

			// Update linkCheckProgress synchronously
			setPageSeoCheck( 'linkCheckProgress', {
				current: updatedChecked.size,
				total: totalLinks,
			} );

			return {
				...prev,
				checkedLinks: updatedChecked,
				brokenLinks: updatedBroken,
			};
		} );
	}

	// Final state update: mark checking as complete and update SEO checks
	setBrokenLinkState( ( prev ) => {
		const updatedChecks = [ ...pageSeoChecks ].filter(
			( c ) => c.id !== 'broken_links'
		);

		if ( brokenLinksArray.length > 0 ) {
			updatedChecks.push( {
				id: 'broken_links',
				title: __(
					'One or more broken links found on the page.',
					'surerank'
				),
				status: 'error',
				type: 'page',
				data: [
					__(
						'These broken links were found on the page:',
						'surerank'
					),
					{ list: [ ...brokenLinksArray ] },
				],
			} );
		}

		// Update all SEO checks at once
		CHECK_TYPES.forEach( ( type ) => {
			setPageSeoCheck(
				type,
				updatedChecks.filter( ( item ) => item.type === type )
			);
		} );
		setPageSeoCheck( 'isCheckingLinks', false );
		setPageSeoCheck( 'linkCheckProgress', {
			current: totalLinks,
			total: totalLinks,
		} );

		return {
			...prev,
			isChecking: false,
		};
	} );
};

// Function to refresh page SEO checks for Elementor editor
export const refreshPageChecks = async (
	setIsRefreshing,
	setBrokenLinkState,
	setPageSeoCheck,
	staticSelect,
	pageSeoChecks,
	brokenLinkState
) => {
	const dynamicPostId =
		staticSelect( STORE_NAME ).getVariables()?.post?.ID?.value ||
		staticSelect( STORE_NAME ).getActivePostId() ||
		0;
	setIsRefreshing( true );

	const isTaxonomyListing = surerank_seo_popup?.is_taxonomy === '1';
	const apiPath = isTaxonomyListing
		? '/surerank/v1/checks/taxonomy'
		: '/surerank/v1/checks/page';
	const timestamp = Date.now();
	const apiParams = isTaxonomyListing
		? { term_ids: [ dynamicPostId ], _t: timestamp }
		: { post_ids: [ dynamicPostId ], _t: timestamp };

	try {
		const response = await apiFetch( {
			path: addQueryArgs( apiPath, apiParams ),
			method: 'GET',
		} );

		const checks = formatSeoChecks(
			response?.data[ dynamicPostId ]?.checks
		);
		const allLinks =
			response.data[ dynamicPostId ]?.checks?.all_links || [];

		// Reset brokenLinkState, keeping only broken links that still exist
		setBrokenLinkState( ( prev ) => {
			const allLinksSet = new Set( allLinks );
			const cleanedBrokenLinks = new Set();
			prev.brokenLinks.forEach( ( link ) => {
				if ( allLinksSet.has( link ) ) {
					cleanedBrokenLinks.add( link );
				}
			} );

			return {
				isChecking: false,
				checkedLinks: new Set(),
				brokenLinks: cleanedBrokenLinks,
				allLinks,
			};
		} );

		// Populate ignoredList from the full server response before filtering out
		// broken_links. The server stamps `ignore: true` on ignored checks via
		// get_updated_ignored_check_list(). Extracting from the unfiltered array
		// ensures broken_links (removed below) is included when it was ignored.
		const serverIgnoredIds = checks
			.filter( ( check ) => check.ignore === true )
			.map( ( check ) => check.id );
		setPageSeoCheck( 'ignoredList', serverIgnoredIds );

		const cleanedChecks = [ ...checks ].filter(
			( item ) => item.id !== 'broken_links'
		);

		// Update pageSeoChecks with cleaned checks
		CHECK_TYPES.forEach( ( type ) => {
			setPageSeoCheck(
				type,
				cleanedChecks.filter( ( item ) => item.type === type )
			);
		} );

		// Mark initialization complete so the status indicator and accordion
		// render with real data (broken-links check may still be running).
		setPageSeoCheck( 'initializing', false );

		if ( allLinks.length === 0 ) {
			setPageSeoCheck( 'isCheckingLinks', false );
			setPageSeoCheck( 'linkCheckProgress', { current: 0, total: 0 } );
		} else {
			setPageSeoCheck( 'isCheckingLinks', true );
			setPageSeoCheck( 'linkCheckProgress', {
				current: 0,
				total: allLinks.length,
			} );

			await checkBrokenLinks(
				allLinks,
				dynamicPostId,
				allLinks,
				setBrokenLinkState,
				setPageSeoCheck,
				brokenLinkState,
				cleanedChecks
			);
		}
	} catch ( error ) {
		toast.error( error.message );
		// Reset states on error
		setBrokenLinkState( {
			isChecking: false,
			checkedLinks: new Set(),
			brokenLinks: new Set(),
			allLinks: [],
		} );
		setPageSeoCheck( 'isCheckingLinks', false );
		setPageSeoCheck( 'linkCheckProgress', { current: 0, total: 0 } );
	} finally {
		setIsRefreshing( false );
	}
};

/**
 * Check if the page is in frontend.
 *
 * @return {boolean} True if the page is in frontend
 */
export const isFrontend = () => {
	return (
		!! surerank_seo_popup?.is_frontend && ! surerank_seo_popup?.is_taxonomy
	);
};

export const isElementorBuilder = () => {
	return (
		typeof window !== 'undefined' &&
		typeof window.elementor !== 'undefined' &&
		window.elementor.hasOwnProperty( 'elements' )
	);
};

export const isBricksBuilder = () => {
	return !! surerank_globals?.is_bricks;
};

export const isBreakdanceBuilder = () => {
	return !! surerank_globals?.is_breakdance;
};

export const isAvadaBuilder = () => {
	// Check for Fusion Builder frontend context
	if (
		typeof window !== 'undefined' &&
		typeof window.FusionPageBuilder !== 'undefined'
	) {
		return true;
	}
	return false;
};

export const isListingPage = () => {
	return surerank_seo_popup?.editor_type === 'listing';
};

export const isPageBuilderActive = () => {
	return (
		isBricksBuilder() ||
		isBreakdanceBuilder() ||
		isElementorBuilder() ||
		isAvadaBuilder() ||
		// Consider frontend as page builder active as page requires refresh.
		isFrontend() ||
		// Listing pages use server-side checks — no live editor available.
		isListingPage()
	);
};

/**
 * Check if SEO analysis should be disabled
 * Returns true if SEO analysis should be disabled due to:
 * - Page level SEO being disabled
 * - Active Bricks builder
 * - Active Avada builder
 *
 *
 * @return {boolean} True if SEO analysis should be disabled
 */
export const isSeoAnalysisDisabled = () => {
	return ! ENABLE_PAGE_LEVEL_SEO || isAvadaBuilder();
};

export const RefreshButton = ( { isRefreshing, isChecking, onClick } ) => {
	return (
		<Button
			variant="outline"
			size="xs"
			onClick={ onClick }
			disabled={ isRefreshing || isChecking }
			icon={
				<RefreshCcw
					className={ cn(
						'size-4',
						( isRefreshing || isChecking ) && 'animate-spin'
					) }
				/>
			}
		>
			{ isRefreshing || isChecking
				? __( 'Refreshing', 'surerank' )
				: __( 'Refresh', 'surerank' ) }
		</Button>
	);
};
