import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { cn, getStatusIndicatorClasses } from '@/functions/utils';
import { getTooltipText } from '@/apps/seo-popup/utils/page-checks-status-tooltip-text';
import { ENABLE_PAGE_LEVEL_SEO } from '@/global/constants';
import {
	sureRankLogoForBuilder,
	handleOpenSureRankDrawer,
} from '@SeoPopup/utils/page-builder-functions';
import { STORE_NAME } from '@/store/constants';
import {
	getPageCheckStatus,
	handleRefreshWithBrokenLinks,
} from '../elementor/page-checks';

/* global jQuery */
/* eslint-disable */

// eslint-disable-next-line wrap-iife
( function ( $ ) {
	const createStatusIndicator = () => {
		const { status } = getPageCheckStatus();

		if ( ! status || ! ENABLE_PAGE_LEVEL_SEO ) {
			return null;
		}

		const indicator = $( '<div></div>' );
		const statusClasses = getStatusIndicatorClasses( status );

		indicator.addClass(
			cn(
				'surerank-status-indicator absolute top-1.5 right-2.5 size-1.5 rounded-full z-10 duration-200',
				statusClasses
			)
		);

		// aria and title removed per design.
		return indicator;
	};

	const updateStatusIndicator = ( button ) => {
		button.find( '.surerank-status-indicator' ).remove();

		// also refresh tooltip text
		button.attr( 'data-balloon', getBalloonText() );

		const indicator = createStatusIndicator();
		if ( indicator ) {
			button.append( indicator );
		}
	};

	/**
	 * Get balloon text for the Bricks button. Relies on page-checks util.
	 *
	 * @return {string}
	 */
	const getBalloonText = () => {
		const { counts } = getPageCheckStatus();
		return getTooltipText( counts );
	};

	const isStoreReady = () => {
		const storeSelectors = wp?.data?.select?.( STORE_NAME );

		return Boolean(
			storeSelectors &&
				typeof storeSelectors.getPageSeoChecks === 'function'
		);
	};

	document.addEventListener( 'DOMContentLoaded', function () {
		let unsubscribeStatus;
		let unsubscribeRefresh;

		const setupBricksButton = ( toolbar ) => {
			if ( $( '#surerank-bricks-toolbar-button' ).length ) {
				return;
			}

			const button =
				$( `<li class="surerank-root" id="surerank-bricks-toolbar-button" aria-label="${ __(
					'Open SureRank SEO',
					'surerank'
				) }" data-balloon-pos="bottom" data-balloon="${ getBalloonText() }" tabindex="0" class="relative">
							<span class="bricks-svg-wrapper">${ sureRankLogoForBuilder(
								'surerank'
							) }</span>
						</li>` );

			button.on( 'click', handleOpenSureRankDrawer );

			const insertionTarget = toolbar.children().eq( 3 );
			if ( insertionTarget.length ) {
				insertionTarget.after( button );
			} else {
				toolbar.append( button );
			}

			updateStatusIndicator( button );

			unsubscribeStatus = wp?.data?.subscribe?.( () => {
				updateStatusIndicator( button );
			} );

			// Trigger page checks refresh on load, but only once post variables are ready.
			unsubscribeRefresh = wp?.data?.subscribe?.( () => {
				const postId =
					select( STORE_NAME )?.getVariables()?.post?.ID?.value;
				if ( postId ) {
					handleRefreshWithBrokenLinks();
					unsubscribeRefresh?.();
					unsubscribeRefresh = null;
				}
			} );
		};

		const bootBricksIntegration = ( attempt = 0 ) => {
			const toolbar = $( '#bricks-toolbar .right, #bricks-toolbar .end' );

			if ( toolbar.length && isStoreReady() ) {
				setupBricksButton( toolbar );
				return;
			}

			if ( attempt >= 50 ) {
				unsubscribeStatus?.();
				unsubscribeRefresh?.();
				return;
			}

			setTimeout( () => bootBricksIntegration( attempt + 1 ), 100 );
		};

		bootBricksIntegration();
	} );
} )( jQuery );
