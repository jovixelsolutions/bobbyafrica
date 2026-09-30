import { log, safeRun, getSafeProperty } from '../core/Utils.js';
import { setCtcStorageItem } from '../core/Storage.js';

/**
 * Module-level state to prevent redundant initializations.
 */
let isInitialized = false;

/**
 * Registry of available actions.
 * Maps action names (from PHP) directly to handler functions.
 */
const ActionRegistry = {

	/**
	 * Clears plugin-related fields cache from LocalStorage.
	 */
	clearPluginFieldsLocalStorage: () => {
		let count = 0;

		// Iterate backwards while removing to avoid index shifts
		for ( let i = localStorage.length - 1; i >= 0; i-- ) {
			const key = localStorage.key( i );
			if ( key && key.startsWith( 'ht_ctc_' ) ) {
				localStorage.removeItem( key );
				count++;
			}
		}

		document.dispatchEvent( new CustomEvent( 'ht_ctc_show_toast', {
			detail: {
				title: 'Success',
				description: `Cleared ${count} cached items. Refreshing page...`,
				iconClass: 'dashicons dashicons-yes-alt',
				iconColor: 'green',
			},
		} ) );

		// Reload the page to ensure clean state
		setTimeout( () => {
			window.location.reload();
		}, 1500 );
	},

	/**
	 * FLOW:
	 *   Admin changes badge setting → n_badge = 'admin_start' → reload frontend → badge shows ✓
	 *   User clicks chat            → n_badge = 'stop'        → badge hidden forever ✓
	 *   Admin changes setting again → n_badge = 'admin_start' → overrides stop → badge shows ✓
	 *
	 * on change of notification settings - update local storage: front.
	 * on save changes clear stuff - local storage: front.
	 *  for better user interface - while testing, admin side..
	 *      for notification badge
	 * Reset the frontend notification badge state for preview.
	 */
	updateNotificationBadgeLS: () => {
		setCtcStorageItem( 'n_badge', 'admin_start' );
		log( 'Actions', 'updateNotificationBadgeLS: n_badge → admin_start' );
	},
};

/**
 * Factory for creating delegated event listeners for UI actions.
 * @param {string} eventType - The DOM event to listen for (e.g., 'click', 'change').
 * @param {string} attrName  - The data attribute to look for (e.g., 'data-action-onclick').
 */
const bindAction = ( eventType, attrName ) => {
	document.addEventListener( eventType, ( event ) => {
		const actionElement = event.target.closest( `[${attrName}]` );
		if ( ! actionElement ) {
			return;
		}

		const actionName = actionElement.getAttribute( attrName );

		// const actionHandler = ActionRegistry[ actionName ];
		const actionHandler = getSafeProperty( ActionRegistry, actionName );

		if ( typeof actionHandler === 'function' ) {
			if ( eventType === 'click' ) {
				event.preventDefault();
			}

			// Use safeRun to prevent one failing action from breaking the UI
			safeRun( () => actionHandler( event, actionElement ), `Action: ${actionName}` );
		} else {
			console.warn( `CtC Actions: Unknown ${eventType} action "${actionName}" triggered.` );
		}
	} );
};

/**
 * Handle custom actions triggered from the UI.
 * Uses centralized event delegation for better performance.
 */
export const initActions = () => {
	// Prevent double initialization when switching tabs
	if ( isInitialized ) {
		return;
	}
	isInitialized = true;

	bindAction( 'click', 'data-action-onclick' );
	bindAction( 'change', 'data-action-onchange' );
};
