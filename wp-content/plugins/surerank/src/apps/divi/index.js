import {
	cn,
	getStatusIndicatorClasses,
	getStatusIndicatorAriaLabel,
} from '@/functions/utils';
import { STORE_NAME } from '@/store/constants';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { getTooltipText } from '@/apps/seo-popup/utils/page-checks-status-tooltip-text';
import './tooltip.css';
import {
	handleOpenSureRankDrawer,
	sureRankLogoForBuilder,
} from '@SeoPopup/utils/page-builder-functions';
import { ENABLE_PAGE_LEVEL_SEO } from '@/global/constants';
import {
	getPageCheckStatus,
	handleRefreshWithBrokenLinks,
} from '../elementor/page-checks';

const DIVI_TARGET_SELECTOR = '.et-vb-page-bar-tools-action-buttons';

// Divi-style tooltip implementation — dark rounded pill, no arrow
const createSureRankTooltip = ( targetElement, tooltipText ) => {
	if ( ! targetElement || ! tooltipText ) {
		return;
	}

	// Create wrapper with surerank-root class
	const wrapper = document.createElement( 'div' );
	wrapper.className = 'surerank-root';

	// Create tooltip element matching Divi's native tooltip style
	const tooltip = document.createElement( 'div' );
	tooltip.className = cn(
		'surerank-tooltip',
		'absolute',
		'bg-black/95',
		'text-white',
		'px-3',
		'py-[0.3125rem]',
		'rounded',
		'text-xs',
		'font-normal',
		'leading-normal',
		'whitespace-nowrap',
		'invisible',
		'opacity-0',
		'pointer-events-none',
		'z-[9999]',
		'top-0',
		'left-0'
	);
	tooltip.textContent = tooltipText;

	// Append tooltip to wrapper
	wrapper.appendChild( tooltip );

	// Append wrapper to body
	document.body.appendChild( wrapper );

	// Position tooltip function
	const positionTooltip = () => {
		const targetRect = targetElement.getBoundingClientRect();

		// Position below the target element with small gap
		const top = targetRect.bottom + 10;
		const centerX = targetRect.left + targetRect.width / 2;

		tooltip.style.top = top + 'px';
		tooltip.style.left = centerX + 'px';
	};

	// Show tooltip
	const showTooltip = () => {
		positionTooltip();
		tooltip.classList.remove( 'invisible' );
		tooltip.classList.add( 'visible', 'opacity-100' );
	};

	// Hide tooltip
	const hideTooltip = () => {
		tooltip.classList.remove( 'visible', 'opacity-100' );
		tooltip.classList.add( 'invisible', 'opacity-0' );
	};

	const handleMouseEnter = () => {
		showTooltip();
	};

	const handleMouseLeave = () => {
		hideTooltip();
	};

	// Attach event listeners
	targetElement.addEventListener( 'mouseenter', handleMouseEnter );
	targetElement.addEventListener( 'mouseleave', handleMouseLeave );
	targetElement.addEventListener( 'focus', showTooltip );
	targetElement.addEventListener( 'blur', hideTooltip );

	// Return cleanup function
	return () => {
		targetElement.removeEventListener( 'mouseenter', handleMouseEnter );
		targetElement.removeEventListener( 'mouseleave', handleMouseLeave );
		targetElement.removeEventListener( 'focus', showTooltip );
		targetElement.removeEventListener( 'blur', hideTooltip );
		if ( wrapper.parentNode ) {
			wrapper.parentNode.removeChild( wrapper );
		}
	};
};

// Function to create status indicator element
const createStatusIndicator = () => {
	const { status, counts } = getPageCheckStatus();

	// Don't show indicator if no status
	if ( ! status || ! ENABLE_PAGE_LEVEL_SEO ) {
		return null;
	}

	// Status indicator colors based on check status
	const statusClasses = getStatusIndicatorClasses( status );

	// Accessibility label for the indicator
	const ariaLabel = getStatusIndicatorAriaLabel( counts.errorAndWarnings );

	const indicator = document.createElement( 'div' );
	indicator.className = cn(
		'surerank-status-indicator',
		'absolute top-0.5 right-0.5 size-2 rounded-full z-10 duration-200',
		statusClasses
	);
	indicator.setAttribute( 'aria-label', ariaLabel );
	indicator.setAttribute( 'title', ariaLabel );

	return indicator;
};

// Main setup function — called after store is initialized
const setupDiviIntegration = () => {
	let done = false;
	let tooltipCleanup = null;
	let unsubscribe = null;
	let observer = null;

	const inject = () => {
		if ( done ) {
			return;
		}

		const container = document.querySelector( DIVI_TARGET_SELECTOR );
		if ( ! container ) {
			return;
		}

		done = true;

		// Create surerank-root wrapper for TailwindCSS
		const sureRankWrapper = document.createElement( 'div' );
		sureRankWrapper.className = 'surerank-root';

		// Create the button matching Divi's own button class
		const btn = document.createElement( 'button' );
		btn.className = 'et-vb-page-bar-button';
		btn.type = 'button';
		btn.setAttribute( 'aria-label', __( 'Open SureRank SEO', 'surerank' ) );
		btn.tabIndex = 0;

		// Wrapper with relative positioning for the status indicator
		const wrap = document.createElement( 'div' );
		wrap.className = 'relative';
		wrap.style.cssText =
			'width:28px;min-width:28px;height:28px;display:flex;align-items:center;justify-content:center;';
		wrap.innerHTML = sureRankLogoForBuilder( 'w-5 h-5' );

		btn.appendChild( wrap );
		btn.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();
			handleOpenSureRankDrawer();
		} );

		sureRankWrapper.appendChild( btn );
		container.appendChild( sureRankWrapper );

		// Function to update the status indicator
		const updateStatusIndicator = () => {
			const existing = wrap.querySelector( '.surerank-status-indicator' );
			if ( existing ) {
				existing.remove();
			}

			const indicator = createStatusIndicator();
			if ( indicator ) {
				wrap.appendChild( indicator );
			}
		};

		// Function to update the tooltip
		const updateTooltip = () => {
			if ( tooltipCleanup ) {
				tooltipCleanup();
			}
			const { counts } = getPageCheckStatus();
			tooltipCleanup = createSureRankTooltip(
				btn,
				getTooltipText( counts )
			);
		};

		// Initial status indicator update
		updateStatusIndicator();

		// Refresh page checks on page load if not already called
		handleRefreshWithBrokenLinks();

		// Subscribe to store changes to update the status and tooltip.
		unsubscribe = wp?.data?.subscribe?.( () => {
			updateStatusIndicator();
			updateTooltip();
		} );

		// Add tooltip to the button and store cleanup function
		const { counts } = getPageCheckStatus();
		tooltipCleanup = createSureRankTooltip( btn, getTooltipText( counts ) );
	};

	// Try injecting immediately
	inject();

	// If the Divi page bar isn't rendered yet, use MutationObserver
	if ( ! done ) {
		observer = new MutationObserver( () => {
			inject();
			if ( done ) {
				observer.disconnect();
			}
		} );
		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	}

	// Cleanup on page unload
	window.addEventListener( 'beforeunload', () => {
		if ( tooltipCleanup ) {
			tooltipCleanup();
			tooltipCleanup = null;
		}
		if ( unsubscribe && typeof unsubscribe === 'function' ) {
			unsubscribe();
			unsubscribe = null;
		}
		if ( observer ) {
			observer.disconnect();
			observer = null;
		}
	} );
};

// Wait for store initialization before setting up Divi integration
const waitForStoreInit = () => {
	let retryCount = 0;
	let storeUnsubscribe = null;
	let isInitialized = false;
	const maxRetries = 50; // Maximum 5 seconds of retrying (50 * 100ms)

	const cleanup = () => {
		if ( storeUnsubscribe && typeof storeUnsubscribe === 'function' ) {
			storeUnsubscribe();
			storeUnsubscribe = null;
		}
	};

	const checkStoreAndInitialize = () => {
		// Prevent multiple initializations
		if ( isInitialized ) {
			return;
		}

		try {
			const storeSelectors = select( STORE_NAME );

			// Check if store exists and has the required functions
			if (
				! storeSelectors ||
				typeof storeSelectors.getVariables !== 'function'
			) {
				// Store not available yet, retry with limit
				if ( retryCount < maxRetries ) {
					retryCount++;
					setTimeout( checkStoreAndInitialize, 100 );
				}
				return;
			}

			const variables = storeSelectors.getVariables();

			if ( variables ) {
				// Store is initialized, proceed with setup
				isInitialized = true;
				cleanup();
				setupDiviIntegration();
			} else if ( ! storeUnsubscribe ) {
				// Store exists but not initialized, subscribe once
				storeUnsubscribe = wp?.data?.subscribe?.( () => {
					try {
						const currentVariables =
							select( STORE_NAME )?.getVariables();
						if ( currentVariables && ! isInitialized ) {
							isInitialized = true;
							cleanup();
							setupDiviIntegration();
						}
					} catch ( error ) {
						// Silently handle subscription errors
					}
				} );

				// Fallback timeout to prevent infinite waiting
				setTimeout( () => {
					if ( ! isInitialized ) {
						const fallbackVariables =
							select( STORE_NAME )?.getVariables();
						if ( fallbackVariables ) {
							isInitialized = true;
							cleanup();
							setupDiviIntegration();
						}
					}
				}, 3000 );
			}
		} catch ( error ) {
			// Handle errors gracefully with retry limit
			if ( retryCount < maxRetries ) {
				retryCount++;
				setTimeout( checkStoreAndInitialize, 100 );
			}
		}
	};

	// Start the initialization check
	checkStoreAndInitialize();
};

waitForStoreInit();
