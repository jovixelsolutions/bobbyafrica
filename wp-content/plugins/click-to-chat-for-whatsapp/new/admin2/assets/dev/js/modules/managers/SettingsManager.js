/* global tinyMCE */

/**
 * Settings Manager
 *
 * Responsibility:
 * 1. Watches the Main Form for changes (`change`, `input` events).
 * 2. Collects "Changed" data only (Delta updates).
 * 3. Sends data to the WordPress API via `app.api`.
 * 4. Handles Auto-Save logic.
 *
 * Pattern: Instantiable class (new SettingsManager(app)) so state is
 * held on the instance, not on the class itself.
 */
// import UIManager from './UIManager.js';
import { log, getSafeProperty, setSafeProperty, decodeHTML, safeRun } from '../core/Utils.js';
import Interface from '../logic/Interface.js';

/**
 * Settings whose value changes how OTHER tabs are rendered server-side.
 *
 * The SPA caches each tab's field HTML (see App.getFieldsForGroup) and only
 * refreshes it on a version change. A few settings, though, alter how fields
 * in other groups render for the same version — e.g. disabling TinyMCE swaps
 * rich editors for plain textareas. When one of these is saved we invalidate
 * the mapped groups' caches so the change reflects without the user having to
 * click "Force Refresh / Clear Cache" manually.
 *
 * Keyed by the field's DOM id → list of settings groups (data-group) to clear.
 */
/**
 * An attribute selector matching one field name.
 *
 * Field names carry brackets (`g[display][home]`), so they need escaping. Not
 * CSS.escape: that targets identifiers rather than quoted strings, and it is a
 * browser-only global. Inside quotes only `"` and `\` have to be neutralized.
 *
 * @param {string} name The input's name attribute.
 * @returns {string} e.g. `[name="g[display][home]"]`.
 */
const nameSelector = ( name ) => {
	const escaped = String( name )
		.replace( /["\\]/g, '\\$&' );

	return `[name="${escaped}"]`;
};

const CACHE_INVALIDATION_MAP = new Map( [
	// Disable Intl input library — changes how the WhatsApp number field renders.
	[ 'no-intl', [ 'general_settings', 'greetings_settings' ] ],

	// Disable TinyMCE Editor — switches rich editors to plain textareas.
	[ 'disable_tinymce', [ 'greetings_settings', 'woo_overwrite_settings', 'woo_add_whatsapp_settings' ] ],
] );

export default class SettingsManager {

	constructor ( app ) {
		this.app = app;

		this.form = null;
		this.saveButton = null;
		this.autoSaveToggle = null;
		this.autoSaveTimeout = null;
		this.isAutoSaveEnabled = false;

		// Concurrency guard for save(). `isSaving` is true while a POST is in
		// flight; `pendingSave` records that another save was requested during
		// that flight so we can flush exactly one trailing save when it finishes.
		this.isSaving = false;
		this.pendingSave = false;

		// Timer for the transient "Saved" state on the button; cleared whenever a
		// new save starts so a stale one can't reset the button mid-flight.
		this.savedStateTimeout = null;

		// Label for the save shortcut, as the platform writes it. `platform` on
		// userAgentData is the reliable half of a deprecated API; the rest are
		// fallbacks for browsers that don't expose it.
		const platform = navigator.userAgentData?.platform || navigator.platform || navigator.userAgent || '';
		this.saveShortcutLabel = /mac|iphone|ipad|ipod/i.test( platform ) ? '⌘S' : 'Ctrl+S';
	}

	init () {
		log( 'Settings', 'Initializing...' );

		// We look for either our specific form ID or the standard WordPress options form.
		this.form = document.getElementById( 'ctc-settings-form' ) || document.querySelector( 'form[action="options.php"]' );
		this.saveButton = document.getElementById( 'save-button' );
		this.autoSaveToggle = document.getElementById( 'auto-save-toggle' );

		if ( ! this.form ) { return; }

		// Check if it's an options.php form (legacy/standard WP)
		if ( this.form.action && typeof this.form.action === 'string' && this.form.action.includes( 'options.php' ) ) {
			return;
		}

		// Hard guarantee against accidental native form submission. Saves go through
		// handleSaveClick → REST; a native submit would put every field value
		// (WhatsApp number, custom CSS, etc.) into the URL / history / server logs.
		this.form.addEventListener( 'submit', ( event ) => event.preventDefault() );

		if ( ! this.saveButton ) { return; }

		this.initEventListeners();

		// Use Event Bus for communication instead of global window leakage
		const events = this.app.events;
		if ( events ) {
			events.on( 'field:dirty', ( input ) => this.markChanged( input ) );
		}
	}

	initEventListeners () {

		// Form Change Listener
		this.form.addEventListener( 'change', ( event ) => {
			this.handleAutoSaveToggle( event );
			this.markChanged( event.target );
		} );

		// Form Input Listener
		this.form.addEventListener( 'input', ( event ) => {
			this.markChanged( event.target );
		} );

		// Enter to Save Listener
		this.form.addEventListener( 'keydown', ( event ) => this.handleEnterKeySave( event ) );

		// Cmd/Ctrl+S to save. On the document, not the form, so it works with
		// focus anywhere on the page (sidebar, header, preview panel).
		document.addEventListener( 'keydown', ( event ) => this.handleSaveShortcut( event ) );

		// Save Button Listener
		this.saveButton.addEventListener( 'click', () => this.handleSaveClick() );

		// Warn before leaving if there are unsaved changes
		window.addEventListener( 'beforeunload', ( event ) => this.handleBeforeUnload( event ) );

		this.updateSaveHint();
	}

	/**
	 * Cmd/Ctrl+S → save the settings instead of the browser's "Save page as…".
	 *
	 * The reflex is universal in editors and admin panels, and the dialog it
	 * otherwise opens lands on top of a form with unsaved changes.
	 *
	 * @param {KeyboardEvent} event
	 */
	handleSaveShortcut ( event ) {
		if ( 's' !== ( event.key || '' ).toLowerCase() ) { return; }

		// Shift/Alt variants are the browser's own ("Save as", screenshot) — leave them.
		if ( ! ( event.metaKey || event.ctrlKey ) || event.shiftKey || event.altKey ) { return; }

		event.preventDefault();
		if ( this.saveButton && ! this.saveButton.disabled ) {
			this.saveButton.click();
		}
	}

	/**
	 * Keep the save button's tooltip describing the button's actual state.
	 *
	 * It carries two things nothing else on the page says: what the amber
	 * "unsaved changes" tint means, and that the keyboard shortcut exists.
	 */
	updateSaveHint () {
		if ( ! this.saveButton ) { return; }

		const isDirty = !! this.form?.querySelector( '[data-changed="true"]' );
		const state = isDirty ? 'Unsaved changes' : 'No unsaved changes';

		this.saveButton.title = `${state} · ${this.saveShortcutLabel}`;
	}

	/**
	 * Swap the save button between its three states.
	 *
	 * Only classes — the states themselves are stacked in one grid cell and
	 * shown/hidden by buttons.css, which keeps the button one width and lets
	 * the responsive icon-only rules apply to every state equally.
	 *
	 * @param {HTMLElement|null} saveBtn
	 * @param {'default'|'loading'|'saved'} state
	 */
	setSaveButtonState ( saveBtn, state ) {
		if ( ! saveBtn ) { return; }

		saveBtn.classList.toggle( 'is-loading', 'loading' === state );
		saveBtn.classList.toggle( 'is-saved', 'saved' === state );
	}

	/**
	 * Warn user if they try to leave the page with unsaved changes.
	 * @param {Event} event
	 */
	handleBeforeUnload ( event ) {
		const changedFields = this.form.querySelectorAll( '[data-changed="true"]' );
		if ( changedFields && changedFields.length > 0 ) {
			event.preventDefault();
			event.returnValue = '';
		}
	}

	/**
	 * Handle Auto Save Toggle Change
	 * @param {Event} event
	 */
	handleAutoSaveToggle ( event ) {
		if ( event.target && event.target.id === 'auto-save-toggle' ) {
			event.stopPropagation();
			const toggle = event.target;
			this.isAutoSaveEnabled = toggle.checked;

			toggle.dataset.changed = 'true';

			clearTimeout( this.autoSaveTimeout );
			this.autoSaveTimeout = setTimeout( () => this.performAutoSave( true ), 500 );
		}
	}

	/**
	 * Handle Enter key to save
	 * @param {KeyboardEvent} event
	 */
	handleEnterKeySave ( event ) {
		if ( event.key === 'Enter' && ! event.shiftKey ) {
			const tagName = event.target.tagName.toLowerCase();
			if ( tagName !== 'textarea' &&
					tagName !== 'button' &&
					! event.target.closest( '.ctc-enterkey-newline' ) ) {
				event.preventDefault();
				if ( this.saveButton ) { this.saveButton.click(); }
			}
		}
	}

	/**
	 * Handle Save Button Click
	 */
	async handleSaveClick () {
		if ( ! await this.validateForm( this.form ) ) {
			return;
		}
		const payload = this.collectChanges( this.form );
		if ( ! this.hasChanges( payload ) ) {
			this.app.events?.emit( 'ht_ctc_show_toast', {
				title: 'No Changes',
				description: 'There are no changes to save.',
				iconClass: 'dashicons dashicons-info',
				iconColor: 'blue',
			} );
			if ( this.saveButton ) {
				this.saveButton.classList.remove( 'has-unsaved-changes' );
			}
			this.updateSaveHint();
			return;
		}

		await this.save( payload, this.saveButton, this.form );
	}

	/**
	 * Mark input as changed by adding data-changed="true" attribute
	 * and trigger auto-save if enabled.
	 * @param {HTMLElement} input
	 */
	markChanged ( input ) {
		if ( input.name || input.dataset.name ) {

			// Skip inputs explicitly tagged as UI-only (e.g. the visible intl phone widget).
			if ( input.dataset.ctcNoTrack ) { return; }

			/** Grouped Settings Sync
			 * If ANY input within a container with class 'ctc-group-sync' has changed,
			 * force ALL sibling inputs in that container to be included in the save payload.
			 */
			input.closest( '.ctc-group-sync' )
				?.querySelectorAll( 'input, select, textarea' )
				.forEach( el => {
					if ( ( el.name || el.dataset.name ) && ! el.dataset.ctcNoTrack ) {
						el.dataset.changed = 'true';
					}
				} );

			input.dataset.changed = 'true';

			if ( this.saveButton ) {
				this.saveButton.classList.add( 'has-unsaved-changes' );
				this.updateSaveHint();
			}

			// Trigger auto-save after delay
			const autoSaveToggle = document.getElementById( 'auto-save-toggle' );
			if ( autoSaveToggle && autoSaveToggle.checked ) {
				clearTimeout( this.autoSaveTimeout );
				this.autoSaveTimeout = setTimeout( () => this.performAutoSave(), 2000 );
			}
		}
	}

	/**
	 * Validate a form's validity.
	 * @param {HTMLFormElement} form
	 * @param {boolean} silent If true, only checks validity without showing tooltips or toasts.
	 * @returns {boolean}
	 */
	async validateForm ( form, silent = false ) {
		if ( ! form ) { return true; }

		if ( silent ) {
			return typeof form.checkValidity === 'function' ? form.checkValidity() : true;
		}

		if ( typeof form.reportValidity === 'function' && ! form.reportValidity() ) {
			const firstInvalid = form.querySelector( ':invalid' );
			if ( firstInvalid ) {
				const labelEl = form.querySelector( `label[for="${firstInvalid.id}"]` );
				const labelText = labelEl ? labelEl.textContent.trim() : firstInvalid.name || 'Field';
				this.app.events?.emit( 'ht_ctc_show_toast', {
					title: 'Validation Error',
					description: `${labelText}: ${firstInvalid.validationMessage}`,
					iconClass: 'dashicons dashicons-warning',
					iconColor: 'red',
				} );

				const panel = firstInvalid.closest( '.settings-panel' );
				if ( panel && panel.id ) {
					await Interface.activateTab( panel.id, true );
				}

				if ( firstInvalid.id ) {
					Interface.scrollToElement( firstInvalid.id );
				}
				firstInvalid.focus();
			}
			return false;
		}
		return true;
	}

	/**
	 * Save without a button press: the debounced auto-save, and the trailing
	 * flush after a save that had requests queued behind it.
	 *
	 * @param {boolean} force Run even when the auto-save toggle is off.
	 */
	async performAutoSave ( force = false ) {
		const autoSaveToggle = document.getElementById( 'auto-save-toggle' );
		const isEnabled = autoSaveToggle ? autoSaveToggle.checked : false;

		if ( ! isEnabled && ! force ) {
			log( 'Settings', 'Auto-save skipped (disabled and not forced).' );
			return;
		}
		if ( ! await this.validateForm( this.form, true ) ) {
			// Skip auto-saving if the form is invalid. Checking silently avoids showing disruptive alerts while typing.
			log( 'Settings', 'Auto-save skipped due to form validation error.' );
			return;
		}

		const payload = this.collectChanges( this.form );
		if ( ! this.hasChanges( payload ) ) { return; }

		await this.save( payload, null, this.form );
	}

	/**
	 * Whether a collected payload contains anything to send.
	 * @param {{settings: Object, remove: Object}} payload
	 * @returns {boolean}
	 */
	hasChanges ( payload ) {
		return Object.keys( payload.settings ).length > 0 ||
			Object.keys( payload.remove ).length > 0;
	}

	/**
	 * Collect all changed inputs from the form where data-changed="true".
	 *
	 * Returns two channels:
	 *   - settings: { group: { ...changed values } }  — data only.
	 *   - remove:   { group: [ "key", "parent[child]" ] } — explicit deletion paths
	 *     (unchecked checkboxes, emptied repeater lists), kept separate from the
	 *     settings data.
	 *
	 * @param {HTMLFormElement} form
	 * @returns {{settings: Object, remove: Object}}
	 */
	collectChanges ( form ) {

		const changedInputs = form.querySelectorAll( '[data-changed="true"]' );
		const settings = {};
		const remove = {};
		const processedNames = new Set();
		const { setNestedValue } = this.app.utils;

		/**
		 * Route a field into the remove channel.
		 * name="group[parent][child]" → remove[group].push("parent[child]").
		 * @param {string} name
		 */
		const addRemovePath = ( name ) => {
			const keys = name.match( /[^[\]]+/g );
			if ( ! keys || keys.length < 2 ) { return; } // Can't remove a whole option group.
			const group = keys[ 0 ];
			const path = keys[ 1 ] + keys.slice( 2 )
				.map( key => `[${key}]` )
				.join( '' );

			// if ( ! remove[ group ] ) { remove[ group ] = []; }
			if ( ! getSafeProperty( remove, group ) ) {
				setSafeProperty( remove, group, [] );
			}

			// const list = remove[ group ];
			const list = getSafeProperty( remove, group );
			if ( ! list.includes( path ) ) { list.push( path ); }
		};

		changedInputs.forEach( input => {
			const name = input.name;

			if ( ! name ) { return; }

			/**
			 * Replace-strategy groups (card carries .is_replace_entire_group, the
			 * client-side mirror of the server's is_replace_entire_group()): the
			 * server overwrites the WHOLE group and ignores removal paths, so
			 * deletion is expressed by absence — never route these inputs into
			 * the remove channel. Checked lazily: only remove-channel candidates
			 * (data-remove markers, unchecked checkboxes) pay the closest() walk.
			 */
			const inReplaceGroup = () =>
				input.closest( '.is_replace_entire_group' ) !== null;

			// Remove markers (hidden inputs with data-remove, activated by
			// RepeaterManager when a repeater list is emptied): explicit removal.
			if ( input.dataset.remove !== undefined ) {
				if ( processedNames.has( name ) ) { return; }
				processedNames.add( name );
				if ( ! inReplaceGroup() ) {
					addRemovePath( name );
				}
				return;
			}

			// Handle array fields (name ends with [])
			if ( name.endsWith( '[]' ) ) {
				const baseName = name.slice( 0, -2 );
				const keys = baseName.match( /[^[\]]+/g );
				let current = settings;

				if ( keys && keys.length > 1 ) {
					// Complex nested array: group[sub][]
					keys.forEach( ( key, i ) => {
						if ( i === keys.length - 1 ) {
							// if ( ! current[ key ] ) { current[ key ] = []; }
							if ( ! getSafeProperty( current, key ) ) {
								setSafeProperty( current, key, [] );
							}

							// current = current[ key ];
							current = getSafeProperty( current, key );
						} else {
							// if ( ! current[ key ] ) { current[ key ] = {}; }
							if ( ! getSafeProperty( current, key ) ) {
								setSafeProperty( current, key, {} );
							}

							// current = current[ key ];
							current = getSafeProperty( current, key );
						}
					} );
				} else {
					// Simple array: tags[]
					// if ( ! settings[ baseName ] ) { settings[ baseName ] = []; }
					if ( ! getSafeProperty( settings, baseName ) ) {
						setSafeProperty( settings, baseName, [] );
					}

					// current = settings[ baseName ];
					current = getSafeProperty( settings, baseName );
				}

				// Don't push value if it's a checkbox and it's unchecked
				if ( input.type === 'checkbox' && ! input.checked ) { return; }

				// Prevent empty hidden fallback inputs from inserting blank array items
				if ( input.type === 'hidden' && input.value === '' ) { return; }

				current.push( input.value );
				return;
			}

			// Skip if already processed this name
			if ( processedNames.has( name ) ) { return; }

			// Handle different input types
			let value;
			if ( input.type === 'radio' ) {
				if ( ! input.checked ) { return; }
				value = input.value;

			} else if ( input.type === 'checkbox' ) {
				/**
				 * Checkbox Logic:
				 * - Checked: Send value (default '1') in settings.
				 * - Unchecked: Send the key path on the remove channel, so the
				 *   server unsets it and isset() checks in PHP fail correctly.
				 */
				if ( ! input.checked ) {
					processedNames.add( name );
					if ( ! inReplaceGroup() ) {
						addRemovePath( name );
					}
					return;
				}
				value = input.value || '1';
			} else {
				value = input.value;
			}

			processedNames.add( name );

			// Reconstruct nested object structure from name="group[sub][val]"
			const keys = name.match( /[^[\]]+/g );
			if ( keys && keys.length ) {

				// CRITICAL FIX: Prevent empty hidden fallback inputs from wiping out arrays
				// Example: <input type="hidden" name="group[params]" value="">  (Fallback)
				//          <input name="group[params][]" value="param1">        (Array Item)
				if ( input.type === 'hidden' && value === '' ) {
					let temp = settings;
					for ( const key of keys ) {
						// eslint-disable-next-line security/detect-object-injection -- Accessing nested property during form data collection
						temp = temp ? temp[ key ] : undefined;
					}
					if ( Array.isArray( temp ) ) {
						return; // we have an array, skip overwriting with ''
					}
				}

				setNestedValue( settings, keys, value );
			} else {
				// settings[ name ] = value;
				setSafeProperty( settings, name, value );
			}
		} );

		return { settings, remove };
	}

	/**
	 * A compact value/state signature for a form control, used to detect whether
	 * a field was re-edited between collection and save success. Checkbox/radio
	 * carry their checked state since their .value alone doesn't reflect it.
	 * @param {HTMLElement} el
	 * @returns {string}
	 */
	fieldSignature ( el ) {
		if ( ! el ) { return ''; }
		if ( el.type === 'checkbox' || el.type === 'radio' ) {
			return `${el.checked ? 1 : 0}|${el.value}`;
		}
		return String( el.value );
	}

	/**
	 * Save changed data to the REST API.
	 * @param {{settings: Object, remove: Object}} payload Collected via collectChanges().
	 * @param {HTMLElement|null} saveBtn
	 * @param {HTMLFormElement} settingsForm
	 */
	async save ( payload, saveBtn, settingsForm ) {
		// Concurrency guard: never allow two saves in flight at once. Both the
		// manual Save button and the debounced auto-save reach here; overlapping
		// POSTs race on the same options server-side (last-write-wins) and can
		// clobber each other. If a save is already running, record that another is
		// needed and bail — the in-flight save flushes a single trailing save when
		// it finishes, coalescing any number of requests made during the flight.
		if ( this.isSaving ) {
			this.pendingSave = true;
			log( 'Settings', 'Save already in progress; queued a trailing save.' );
			return;
		}
		this.isSaving = true;

		// Snapshot exactly the fields included in THIS payload (synchronously,
		// before the await — so it matches what collectChanges() just saw). On
		// success we clear only these dirty flags. Re-querying [data-changed] at
		// success time would also clear fields the user edits DURING the in-flight
		// POST, silently dropping those edits. We also remember each field's
		// submitted value so a field re-edited mid-flight keeps its flag and is
		// saved on the next run.
		const submittedFields = Array.from( settingsForm.querySelectorAll( '[data-changed="true"]' ) )
			.map( el => ( { el, signature: this.fieldSignature( el ) } ) );

		// Whether to land on the "Saved" confirmation rather than straight back
		// to default — set only on the success path below.
		let didSave = false;

		// A "Saved" badge still counting down from a previous save must not
		// outlive this one and reset the button while it says "Saving…".
		clearTimeout( this.savedStateTimeout );

		try {
			if ( saveBtn ) {
				saveBtn.disabled = true;
				this.setSaveButtonState( saveBtn, 'loading' );
			}

			const api = this.app.getApi();
			const body = { settings: payload.settings };

			// Only include the remove channel when there are deletions to apply.
			if ( Object.keys( payload.remove ).length > 0 ) {
				body.remove = payload.remove;
			}
			const result = await api.request( api.getEndpoints().SAVE, {
				method: 'POST',
				body,
			} );

			if ( result.success ) {
				// Emit success event for other modules. Payload: the sanitized
				// settings from the server plus a human summary of what was
				// saved (UIManager puts it in the success toast).
				this.app.events?.emit( 'settings:saved', {
					settings: result.settings,
					summary: this.saveSummary( submittedFields ),
				} );

				// If any saved field controls how other tabs render, drop their stale
				// caches so the change reflects without a manual "Force Refresh".
				this.invalidateDependentCaches( submittedFields );

				// Fields the user re-edited while the POST was in flight. The response
				// predates those edits, so writing it back would overwrite them with
				// the older saved value — skip those inputs entirely.
				const reEdited = new Set( submittedFields
					.filter( ( { el, signature } ) => this.fieldSignature( el ) !== signature )
					.map( ( { el } ) => el ) );

				// Update form with sanitized values from server
				if ( result.settings ) {
					this.updateFormValues( settingsForm, result.settings, '', reEdited );
				}

				// Reset changed state — but only for the fields we actually sent, and
				// only if they're untouched since collection. A field re-edited during
				// the in-flight POST keeps its flag so its new value is saved next run.
				submittedFields.forEach( ( { el, signature } ) => {
					if ( this.fieldSignature( el ) === signature ) {
						el.dataset.changed = 'false';
					}
				} );

				// Only clear the "unsaved changes" indicator if nothing remains dirty —
				// a field re-edited mid-flight (above) should keep the button lit.
				if ( this.saveButton && ! settingsForm.querySelector( '[data-changed="true"]' ) ) {
					this.saveButton.classList.remove( 'has-unsaved-changes' );
				}

				this.updateSaveHint();
				didSave = true;

			} else {
				throw new Error( result.message || 'Unknown error' );
			}
		} catch ( error ) {
			console.error( 'Save error:', error );
			this.app.events?.emit( 'settings:error', error );
		} finally {
			// Release the lock as soon as the request settles (the button delay
			// below is purely cosmetic and must not hold the lock open).
			this.isSaving = false;

			if ( saveBtn ) {
				// Artificial delay for better UX (so user sees "Saving..." even if fast)
				setTimeout( () => {
					saveBtn.disabled = false;

					if ( ! didSave ) {
						this.setSaveButtonState( saveBtn, 'default' );
						return;
					}

					// Confirm in place, where the click happened. The toast says the
					// same thing in the opposite corner, which is easy to miss when
					// you are looking at the button you just pressed.
					this.setSaveButtonState( saveBtn, 'saved' );
					this.savedStateTimeout = setTimeout(
						() => this.setSaveButtonState( saveBtn, 'default' ),
						1600,
					);
				}, 500 );
			}

			// Flush a single trailing save for any request that arrived mid-flight.
			// force=true so a queued manual save still runs even if auto-save is off;
			// performAutoSave re-collects fresh changes and no-ops when there are none.
			if ( this.pendingSave ) {
				this.pendingSave = false;
				this.performAutoSave( true );
			}
		}
	}

	/**
	 * Human-readable summary of a save for the success toast, e.g.
	 * "Updated: Call to Action, Desktop Style +2 more". Labels come from each
	 * field's .form-group wrapper; fields without one fall back to the last
	 * key of their input name (prettified). Returns '' when nothing resolves,
	 * so the toast keeps its generic line.
	 *
	 * @param {Array<{el: HTMLElement}>} submittedFields Fields sent in this save.
	 * @returns {string}
	 */
	saveSummary ( submittedFields ) {
		const labels = [];
		const seen = new Set();

		submittedFields.forEach( ( { el } ) => {
			let label = el.closest( '.form-group' )
				?.querySelector( 'label' )
				?.textContent.trim()
				.replace( /\s+/g, ' ' ) || '';

			if ( label === '' && typeof el.name === 'string' ) {
				// e.g. ht_ctc_chat_options[r_nums][] → "R nums"
				const keys = [ ...el.name.matchAll( /\[([^\]]+)\]/g ) ]
					.map( ( match ) => match[ 1 ] );
				const last = keys[ keys.length - 1 ] || '';
				label = last.replace( /[_-]+/g, ' ' )
					.trim();
				label = label.charAt( 0 )
					.toUpperCase() + label.slice( 1 );
			}

			if ( label !== '' && ! seen.has( label ) ) {
				seen.add( label );
				labels.push( label );
			}
		} );

		if ( labels.length === 0 ) { return ''; }

		const shown = labels.slice( 0, 3 )
			.join( ', ' );
		const more = labels.length - 3;
		return ( more > 0 ) ? `Updated: ${shown} +${more} more` : `Updated: ${shown}`;
	}

	/**
	 * Invalidate cached field data for groups that depend on a just-saved setting.
	 *
	 * Looks at the fields included in this save; for any listed in
	 * CACHE_INVALIDATION_MAP, clears the mapped groups' caches and forces any
	 * already-rendered (but not currently visible) panels to re-render on their
	 * next visit — so the dependent tabs pick up the change automatically.
	 *
	 * @param {Array<{el: HTMLElement}>} submittedFields Fields sent in this save.
	 */
	invalidateDependentCaches ( submittedFields ) {
		const groups = new Set();

		submittedFields.forEach( ( { el } ) => {
			const mapped = el && el.id ? CACHE_INVALIDATION_MAP.get( el.id ) : null;
			if ( Array.isArray( mapped ) ) {
				mapped.forEach( group => groups.add( group ) );
			}
		} );

		if ( groups.size === 0 ) { return; }

		const groupList = Array.from( groups );
		log( 'Settings', 'Invalidating dependent field caches', groupList );

		// Drop cached field HTML (localStorage + preloaded window copy).
		if ( typeof this.app.clearFieldsCache === 'function' ) {
			this.app.clearFieldsCache( groupList );
		}

		// Reset already-loaded panels (except the active one, to avoid blanking the
		// current view) so a later tab switch re-fetches the fresh fields.
		groupList.forEach( group => {
			const panel = document.querySelector( `.settings-panel[data-group="${group}"]` );
			if ( panel && ! panel.classList.contains( 'active' ) && panel.dataset.loaded === 'true' ) {
				panel.dataset.loaded = 'false';
				const container = panel.querySelector( '.fields-container' );
				if ( container ) { container.innerHTML = ''; }
			}
		} );
	}

	/**
	 * Recursively update form inputs with data from the server.
	 * Matches inputs by name attribute using bracket notation.
	 *
	 * @param {HTMLFormElement} form
	 * @param {Object} data
	 * @param {string} prefix
	 * @param {Set<HTMLElement>|null} skip Inputs re-edited since the save was
	 *   collected; the server value is older than what the user is looking at,
	 *   so these are left untouched (and keep their dirty flag).
	 */
	updateFormValues ( form, data, prefix = '', skip = null ) {
		for ( const [ key, value ] of Object.entries( data ) ) {
			const name = prefix ? `${prefix}[${key}]` : key;

			if ( value !== null && typeof value === 'object' ) {
				this.updateFormValues( form, value, name, skip );
			} else {
				const input = form.querySelector( nameSelector( name ) );

				if ( input && ! skip?.has( input ) ) {
					const newValue = value === null ? '' : String( value );

					if ( input.type === 'checkbox' ) {
						const isChecked = newValue !== '' && newValue !== '0' && newValue !== 'false';
						if ( input.checked !== isChecked ) {
							input.checked = isChecked;
							input.dataset.changed = 'false';
						}
					} else if ( input.type === 'radio' ) {
						const radioGroup = form.querySelectorAll( nameSelector( name ) );
						radioGroup.forEach( radio => {
							if ( skip?.has( radio ) ) { return; }
							const shouldBeChecked = String( radio.value ) === newValue;
							if ( radio.checked !== shouldBeChecked ) {
								radio.checked = shouldBeChecked;
								radio.dataset.changed = 'false';
							}
						} );
					} else if ( input.type !== 'file' && input.type !== 'submit' && input.tagName !== 'BUTTON' ) {
						// All other fields. Skip file inputs, submit buttons, and other non-value fields (e.g., buttons) since they don't have a meaningful value to update.
						let targetValue = newValue;

						if ( input.type === 'time' ) {
							// Native time inputs only accept strict HH:mm format.
							const cleanVal = newValue.replace( /\s+/g, '' );
							const match = cleanVal.match( /^(\d{1,2}):(\d{2})$/ );
							if ( match ) {
								targetValue = `${match[ 1 ].padStart( 2, '0' )}:${match[ 2 ]}`;
							}
						}

						if ( input.value !== targetValue ) {
							input.value = decodeHTML( targetValue );

							// For intl input: update the visible field alongside the hidden one.
							if ( input.classList.contains( 'intl_number_hidden' ) ) {
								const container = input.closest( '.ctc_intl_container' );
								if ( container ) {
									const sibling = container.querySelector( '.intl_number' );
									if ( sibling ) {
										// The intl-tel-input instance is stashed on the element by
										// PhoneInput.js (no global — the library is an ES module).
										const instance = sibling._ctcIti;
										if ( instance ) {
											const formattedValue = targetValue && ! targetValue.startsWith( '+' ) ? `+${targetValue}` : targetValue;
											delete sibling.dataset.userInteracted;
											instance.setNumber( formattedValue );
											sibling.dataset.userInteracted = 'true'; // Add "userInteracted" flag here or remove from PhoneInput.js [ 'focus', 'click', 'keydown' ] once: true
										} else {
											sibling.value = targetValue;
										}
									}
								}
							}

							// Update custom editor if mounted on this textarea.
							if ( input._ctcEditorInstance ) {
								safeRun( () => {
									input._ctcEditorInstance.setValue( decodeHTML( targetValue ) );
								}, 'updateFormValues' );
							} else if ( typeof tinyMCE !== 'undefined' ) {
								// Update TinyMCE editor if instance exists.
								const editor = tinyMCE.get( input.id );
								if ( editor ) {
									safeRun( () => {
										input.dataset.ctcSyncingFromServer = 'true';
										editor.setContent( decodeHTML( targetValue ) );
										setTimeout( () => {
											delete input.dataset.ctcSyncingFromServer;
										}, 0 );
									}, 'updateFormValues' );
								}
							}

							input.dataset.changed = 'false';
						}
					}
				}
			}
		}
	}

}
