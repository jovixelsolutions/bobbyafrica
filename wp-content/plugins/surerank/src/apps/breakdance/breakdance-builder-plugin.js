// Breakdance executes this via new Function() (no module context).
// Access wp.i18n from the global scope; fall back to identity if unavailable.
const __ =
	window.wp && window.wp.i18n && window.wp.i18n.__
		? window.wp.i18n.__
		: ( text ) => text;
/**
 * SureRank SEO — Breakdance editor toolbar button.
 *
 * This script runs inside the Breakdance builder SPA context where no
 * WordPress JS libraries (wp.data, React, jQuery) are available. It must
 * be entirely self-contained.
 *
 * Responsibilities:
 * 1. Inject a SureRank button before the undo/redo section of the toolbar.
 * 2. On click, send a postMessage to the editor page to open the SEO popup.
 * 3. Receive status updates from the editor integration script and update the button indicator.
 */
( function () {
	'use strict';

	const BUTTON_ID = 'surerank-breakdance-toolbar-button';

	const SURERANK_SVG =
		'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
		'<path d="M13.5537 1.5C17.8453 1.5 21.3251 4.97895 21.3252 9.27051C21.3252 12.347 19.5368 15.0056 16.9434 16.2646H21.3252V22.5H18.0889C14.9086 22.5 12.2861 20.1186 11.9033 17.042H11.9014L11.9033 13.7852C14.8283 13.7661 17.0342 11.3894 17.0342 8.45996V6.0293C14.137 6.02947 11.6948 7.97682 10.9443 10.6338C10.1605 9.53345 8.87383 8.8165 7.41992 8.81641H6.38086V9.85352H6.38379C6.44515 12.0356 8.23375 13.786 10.4307 13.7861H10.7061L10.6934 17.042H10.6865C10.2943 20.1082 7.67678 22.4785 4.50391 22.4785H2.6748V1.5H13.5537Z" fill="currentColor"/>' +
		'</svg>';

	const STATUS_COLORS = {
		good: '#22c55e',
		warning: '#f59e0b',
		error: '#ef4444',
	};

	let indicator = null;
	let tooltipEl = null;
	let observer = null;

	/**
	 * Open the SureRank SEO popup.
	 *
	 * Posts a message to the current window. The editor integration script
	 * (loaded via wp_print_scripts) listens for this and dispatches to the store.
	 */
	function openSureRankPopup() {
		window.postMessage(
			{ type: 'surerank-open-popup' },
			window.location.origin
		);
	}

	/**
	 * Update the status indicator dot color based on the SEO check status.
	 *
	 * @param {string} status - 'good', 'warning', or 'error'.
	 */
	function updateIndicator( status ) {
		if ( ! indicator ) {
			return;
		}
		const color = STATUS_COLORS[ status ] || '';
		indicator.style.backgroundColor = color;
		indicator.style.display = color ? 'block' : 'none';
	}

	/**
	 * Update the tooltip text.
	 *
	 * @param {string} text
	 */
	function updateTooltipText( text ) {
		if ( ! tooltipEl ) {
			return;
		}
		tooltipEl.textContent = text || 'SureRank SEO';
	}

	/**
	 * Create and inject the SureRank toolbar button before the undo/redo section.
	 *
	 * Toolbar structure (from compiled Breakdance CSS):
	 * - .topbar-section — grid section (gap: 6px, height: 54px, padding: 0 6px)
	 * - .topbar-section-br — adds right border separator (border-right: 1px solid var(--gray200))
	 * - .undo-redo-top-bar-section — the undo/redo button group
	 * - .breakdance-toolbar-icon-button — native icon button class
	 *
	 * @param {Element} undoRedoSection
	 */
	function createButton( undoRedoSection ) {
		// Section wrapper matching Breakdance's native topbar-section layout.
		const section = document.createElement( 'div' );
		section.className = 'topbar-section topbar-section-br';
		section.style.cssText = 'position: relative;';

		// Button using Breakdance's native icon button class.
		const button = document.createElement( 'button' );
		button.id = BUTTON_ID;
		button.className = 'breakdance-toolbar-icon-button';
		button.type = 'button';
		button.setAttribute(
			'aria-label',
			__( 'Open SureRank SEO', 'surerank' )
		);
		button.style.cssText = 'position: relative; cursor: pointer;';
		button.innerHTML = SURERANK_SVG;

		// Status indicator dot (positioned top-right of the button icon).
		indicator = document.createElement( 'span' );
		indicator.style.cssText =
			'position: absolute;' +
			'top: 9px;' +
			'right: 10px;' +
			'width: 7px;' +
			'height: 7px;' +
			'border-radius: 50%;' +
			'display: none;' +
			'pointer-events: none;';
		button.appendChild( indicator );

		// Tooltip — Breakdance-style dark pill below the button.
		tooltipEl = document.createElement( 'div' );
		tooltipEl.style.cssText =
			'position: absolute;' +
			'top: calc(100% + 8px);' +
			'left: 50%;' +
			'transform: translateX(-50%);' +
			'background: rgba(0,0,0,0.85);' +
			'color: #fff;' +
			'font-size: 11px;' +
			'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;' +
			'font-weight: 400;' +
			'line-height: 1.4;' +
			'padding: 4px 10px;' +
			'border-radius: 4px;' +
			'white-space: nowrap;' +
			'pointer-events: none;' +
			'opacity: 0;' +
			'transition: opacity 0.15s ease;' +
			'z-index: 9999;';
		tooltipEl.textContent = 'SureRank SEO';

		button.addEventListener( 'mouseenter', function () {
			tooltipEl.style.opacity = '1';
		} );
		button.addEventListener( 'mouseleave', function () {
			tooltipEl.style.opacity = '0';
		} );
		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			openSureRankPopup();
		} );

		section.appendChild( button );
		section.appendChild( tooltipEl );

		// Insert before the undo/redo section.
		undoRedoSection.parentNode.insertBefore( section, undoRedoSection );
	}

	/**
	 * Attempt to inject the button. Returns true if successful.
	 *
	 * @return {boolean} True if the button was injected, false if the target element was not found.
	 */
	function tryInject() {
		if ( document.getElementById( BUTTON_ID ) ) {
			return true;
		}

		const undoRedoSection = document.querySelector(
			'.undo-redo-top-bar-section'
		);
		if ( ! undoRedoSection ) {
			return false;
		}

		createButton( undoRedoSection );

		// Request current status from index.js immediately after injection.
		// Needed because the store may have finished checks before this button
		// existed (initial load) or won't fire again (after preview remount).
		window.postMessage(
			{ type: 'surerank-request-status' },
			window.location.origin
		);

		return true;
	}

	/**
	 * Listen for status update messages from the editor integration script.
	 */
	window.addEventListener( 'message', function ( event ) {
		if ( ! event.data || event.data.type !== 'surerank-status-update' ) {
			return;
		}

		updateIndicator( event.data.status );

		if ( event.data.tooltipText ) {
			updateTooltipText( event.data.tooltipText );
		}
	} );

	// Attempt immediate inject; start a persistent observer so the button is
	// re-injected whenever Breakdance preview mode unmounts the toolbar.
	tryInject();

	observer = new MutationObserver( function () {
		tryInject();
	} );

	const root = document.body || document.documentElement;
	observer.observe( root, { childList: true, subtree: true } );
}() );
