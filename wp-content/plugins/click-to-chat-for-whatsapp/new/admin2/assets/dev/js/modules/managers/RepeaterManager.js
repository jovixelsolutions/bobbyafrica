import { getSafeProperty, setSafeProperty } from '../core/Utils.js';
import { addGaParam, addPixelParam, addGtmParam } from '../components/sections/AnalyticsSections.js';
import { addWebhookParam } from '../components/sections/WebhooksSection.js';

/**
 * RepeaterManager
 *
 * Handles add/remove for all dynamic list fields (Analytics params, Webhooks, Agents, etc.).
 *
 * ## Button contract
 *
 * Both add and remove buttons use the same two attributes:
 *
 *   data-callback="<registered-id>"          ← callback to invoke
 *   data-callback-container="<selector>"     ← container scope for callback and marker
 *
 * The button's class determines the flow:
 *   .ctc_repeater_add_button  → add flow (deactivate marker, call callback)
 *   .ctc-remove-button        → remove flow (remove row, activate marker if empty, call callback)
 *
 * ## Remove marker contract
 *
 * A hidden input with `data-remove` lives inside the callback-container (rendered by PHP):
 *
 *   <input type="hidden" data-remove="option_group[key]">
 *
 * When the list is empty, RepeaterManager sets `name` (from data-remove) and marks
 * the marker dirty; SettingsManager routes any named data-remove input onto the
 * explicit `remove` channel of the save payload. When items exist, `name` is
 * removed and the marker is inert.
 */
export default class RepeaterManager {

	static init ( app ) {
		this.app       = app;
		this.repeaters = this.repeaters || {};

		// this.repeaters = {};

		// this.initialized = false;

		this.registerDefaults();

		// Start global event listeners (Click delegation)
		if ( ! this.initialized ) {
			this.bindEvents();
			this.initialized = true;
		}

		// // After save, deactivate all remove markers globally.
		// app.events?.on( 'settings:saved', () => {
		// 	document.querySelectorAll( '[data-remove]' ).forEach( marker => {
		// 		marker.removeAttribute( 'name' );
		// 	} );
		// } );
	}

	static register ( id, callback ) {
		// this.repeaters[ id ] = callback;
		setSafeProperty( this.repeaters, id, callback );
	}

	static registerDefaults () {
		this.register( 'ga', addGaParam );
		this.register( 'pixel', addPixelParam );
		this.register( 'gtm', addGtmParam );
		this.register( 'hook_v', addWebhookParam );
	}

	// ─── Private helpers ────────────────────────────────────────────────────────

	/**
	 * Resolve the callback container from a button.
	 * Falls back to `fallback` (typically row.parentNode) when no selector is set.
	 * @param {HTMLElement} button
	 * @param {HTMLElement} [fallback]
	 * @returns {HTMLElement|null}
	 */
	static _resolveContainer ( button, fallback = null ) {
		const selector = button.dataset.callbackContainer;
		if ( selector ) {
			return button.closest( selector ) || document.querySelector( selector );
		}
		return fallback;
	}

	/**
	 * Activate the remove marker in `container` by setting its `name` attribute.
	 * Checks for remaining .ctc-item elements in the container.
	 * @param {HTMLElement} container
	 */
	static _activateRemoveMarker ( container ) {
		if ( ! container ) { return; }

		const marker = container.querySelector( '[data-remove]' );
		if ( ! marker ) { return; }

		if ( container.querySelectorAll( '.ctc-item' ).length > 0 ) { return; }

		marker.name = marker.dataset.remove;
		this.app?.events?.emit( 'field:dirty', marker );
	}

	/**
	 * Deactivate the remove marker in `container` by removing its `name` attribute.
	 * @param {HTMLElement} container
	 */
	static _deactivateRemoveMarker ( container ) {
		if ( ! container ) { return; }

		const marker = container.querySelector( '[data-remove]' );
		if ( marker ) {
			marker.removeAttribute( 'name' );
			marker.removeAttribute( 'value' );
		}
	}

	// ─── Event binding ──────────────────────────────────────────────────────────

	static bindEvents () {
		const addBtnClass    = 'ctc_repeater_add_button';
		const removeBtnClass = 'ctc-remove-button';

		// Global Click Delegation for Add/Remove Items
		document.addEventListener( 'click', ( event ) => {

			// add button for repeater fields.
			const addButton = event.target.closest( `.${ addBtnClass }` );

			// remove button globally. (common for all sections). get closest row and remove it.
			const removeButton = event.target.closest( `.${ removeBtnClass }` );

			if ( addButton ) {
				event.preventDefault();
				this.addItem( addButton );
			} else if ( removeButton ) {
				event.preventDefault();
				this.removeItem( removeButton );
			}
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key !== 'Enter' && event.key !== ' ' ) { return; }

			const addButton    = event.target.closest( `.${ addBtnClass }` );
			const removeButton = event.target.closest( `.${ removeBtnClass }` );
			const target       = addButton || removeButton;

			// If it's a real <button>, the browser already triggers a 'click' event. (on click handled separately)
			// We only need to manually handle non-button elements (like span, div, a).
			if ( ! target || target.tagName === 'BUTTON' ) { return; }

			event.preventDefault();
			if ( addButton )    { this.addItem( addButton ); }
			if ( removeButton ) {
				this.removeItem( removeButton );
			}
		} );
	}

	// ─── Core actions ───────────────────────────────────────────────────────────

	/**
	 * Add flow: deactivate marker, call registered callback.
	 * @param {HTMLElement} button
	 */
	static addItem ( button ) {
		const callbackName = button.dataset.callback;

		// const callback = this.repeaters[ callbackName ];
		const callback = getSafeProperty( this.repeaters, callbackName );
		if ( ! callback ) { return; }

		// Deactivate remove marker before the new row lands.
		// data-callback-container is required on add buttons — no meaningful fallback.
		const container = this._resolveContainer( button, null );
		this._deactivateRemoveMarker( container );

		callback( { container, button } );

		// Mark all named inputs in the sync container dirty so the full list is saved.
		// const syncContainer = container?.closest( '.ctc-group-sync' ) ?? container;
		const syncContainer = container?.closest( '.ctc-group-sync' );
		if ( syncContainer ) {
			syncContainer.querySelectorAll( '[name]' )
				.forEach( el => {
					if ( el.name ) {
						this.app?.events?.emit( 'field:dirty', el );
					}
				} );
		}
	}

	/**
	 * Removes an item when its remove button (.ctc-remove-button) is clicked.
	 *
	 * Flow: remove row, activate marker if empty, call registered callback.
	 * 1. Locates the parent '.ctc-item' row to remove.
	 * 2. Identifies the synchronization container (either '.ctc-group-sync' or the direct parent)
	 *    to mark sibling fields as "dirty" for the SettingsManager after removal.
	 * 3. Removes the row from the DOM.
	 * 4. Checks the button for a 'data-callback' attribute. If present, resolves the
	 * 4. Checks the button for a 'data-callback' attribute. If present, resolves the
	 *    parent container using 'data-callback-container' (if provided) and executes the registered
	 *    callback function.
	 *
	 * @param {HTMLElement} button The button element that triggered the removal.
	 */
	static removeItem ( button ) {
		const row = button.closest( '.ctc-item' );
		if ( ! row ) { return; }

		// ctc-group-sync - purpose to mark all elements are marked as change.
		const syncContainer = row.closest( '.ctc-group-sync' );

		// Resolve the owning container from data-callback-container attribute.
		// Walk up from the button so multiple instances on the same page are handled correctly.
		const containerSelector = this._resolveContainer( button, row.parentNode );

		// 1. Remove the row — everything below sees the post-removal DOM state.
		row.remove();

		// 2. Activate remove marker if the container is now empty.
		this._activateRemoveMarker( containerSelector );

		// 3. Fire the registered callback with the container.
		const callbackName = button.dataset.callback;
		if ( callbackName ) {
			// const removerFn = this.repeaters[ callbackName ];
			const removerFn = getSafeProperty( this.repeaters, callbackName );
			if ( typeof removerFn === 'function' ) {
				removerFn( { container: containerSelector, button } );
			}
		}

		// 4. Mark all remaining named inputs dirty.
		if ( syncContainer ) {
			syncContainer.querySelectorAll( '[name]' )
				.forEach( el => {
					if ( el.name ) {
						// Use Event Bus
						this.app.events?.emit( 'field:dirty', el );
					}
				} );
		}
	}

}
