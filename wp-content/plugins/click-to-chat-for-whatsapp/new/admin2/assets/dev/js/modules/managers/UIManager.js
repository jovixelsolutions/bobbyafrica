/**
 * UI Manager
 * Handles global UI interactions that aren't specific to a single component.
 *
 * Responsibilities:
 * 1. Global Toasts/Notifications
 * 2. Syncing visible inputs with hidden form fields (for Settings API)
 * 3. Handling UI-specific interactions like Grid selection
 */
import { log, friendlyErrorMessage, copyToClipboard } from '../core/Utils.js';
import {
	handleContextualTriggerClick,
	syncPanelToSelection,
	initContextualTriggers,
} from '../components/layouts/ContextualTrigger.js';

export default class UIManager {

	static init ( app ) {
		log( 'UI', 'Initializing...' );
		this.app = app;

		// Subscribe to global toast events
		document.addEventListener( 'ht_ctc_show_toast', ( event ) => {
			if ( event.detail ) {
				this.showToast( event.detail );
			}
		} );

		// Listen for app-level events
		const events = this.app.events;
		if ( events ) {
			events.on( 'ht_ctc_show_toast', ( data ) => {
				this.showToast( data );
			} );

			events.on( 'settings:saved', ( payload ) => {
				this.clearSaveErrorState();
				this.showToast( {
					title: this.app.config.i18n.saved || 'Settings saved successfully.',

					// SettingsManager passes a "what changed" summary
					// (field labels); fall back to the generic line.
					description: payload?.summary || 'Your changes were successfully saved.',
					iconClass: 'dashicons dashicons-yes-alt',
					variant: 'success',
				} );
			} );

			events.on( 'settings:error', ( error ) => {
				// Shared technical→friendly mapping (see Utils.friendlyErrorMessage),
				// so a save failure reads the same as a field-load failure instead of
				// dumping the raw "[500]" / "Unexpected token" message.
				const friendly = friendlyErrorMessage( error );
				const raw = ( error && error.message ) ? error.message : '';

				// Always surface the exact technical message alongside the friendly
				// hint — it's what support needs to diagnose (status code, REST error
				// code, firewall block). Longer duration so it can be read/copied.
				const description = ( raw && raw !== friendly ) ?
					`${friendly} — Details: ${raw}` :
					friendly;

				this.showToast( {
					title: this.app.config.i18n.error || 'Error saving settings.',
					description,
					iconClass: 'dashicons dashicons-warning',
					variant: 'error',
					duration: 12000,
				} );

				// Toasts are transient — also keep a persistent "Save failed"
				// indicator next to the save button until the next successful save.
				this.showSaveErrorState( friendly, raw );
			} );
		}

		// Keeps Customize triggers in step with the panel they open.
		initContextualTriggers( this.app );

		// ~ Start listening for changes on inputs marked with 'update-hidden-field'
		// ~ This bridges the UI inputs (toggle switches) -> Actual Hidden Form Inputs
		this.listenerForInputChanges();

		// Dismiss button + hold-while-reading behavior for the toast
		this.initToastControls();

		// Initialize physical on-page offline/online warning box
		this.initOfflineDetection();
	}

	/**
	 * Wire the toast's dismiss button and its hover/focus hold.
	 *
	 * A toast is not always disposable here: a save failure carries the exact
	 * technical message support asks for, and a PRO toast carries an action
	 * link. Both were on the same fixed timer as "Settings saved", so reaching
	 * for the link or reading the error raced a countdown. Hovering or tabbing
	 * into the toast now holds it; leaving resumes with the time that was left.
	 *
	 * Bound once, on the element that outlives every individual toast.
	 */
	static initToastControls () {
		const toast = document.getElementById( 'toast' );
		if ( ! toast ) { return; }

		toast.querySelector( '.toast-close' )
			?.addEventListener( 'click', () => this.hideToast() );

		toast.addEventListener( 'mouseenter', () => this.pauseToast() );
		toast.addEventListener( 'mouseleave', () => {
			// Keyboard focus inside the toast is its own reason to hold it open.
			if ( toast.contains( document.activeElement ) ) { return; }
			this.resumeToast();
		} );

		// focusin/focusout, not focus/blur: the focusable children are the close
		// button and the action link, and only the bubbling pair sees them.
		toast.addEventListener( 'focusin', ( event ) => {
			// Remember where focus came IN from, so dismissing can hand it back.
			// Only on entry from outside — moving between the toast's own close
			// button and action link must not overwrite the original origin.
			if ( ! toast.contains( event.relatedTarget ) ) {
				this.toastReturnFocus = event.relatedTarget || null;
			}
			this.pauseToast();
		} );
		toast.addEventListener( 'focusout', ( event ) => {
			// Ignore focus moving between the toast's own children.
			if ( toast.contains( event.relatedTarget ) ) { return; }
			this.resumeToast();
		} );

		// Esc dismisses whichever toast is showing, from anywhere on the page.
		document.addEventListener( 'keydown', ( event ) => {
			if ( 'Escape' !== event.key || ! toast.classList.contains( 'show' ) ) { return; }
			this.hideToast();
		} );
	}

	/**
	 * Hold the visible toast open: stop its dismiss timer and its progress bar,
	 * banking however much of the countdown was left.
	 */
	static pauseToast () {
		const toast = document.getElementById( 'toast' );
		if ( ! toast || ! toast.classList.contains( 'show' ) || this.toastPaused ) { return; }

		clearTimeout( this.toastTimeout );
		this.toastRemaining = Math.max(
			0,
			this.toastRemaining - ( Date.now() - this.toastResumedAt ),
		);
		this.toastPaused = true;
		toast.classList.add( 'is-paused' );
	}

	/**
	 * Resume a held toast from where its countdown stopped.
	 */
	static resumeToast () {
		const toast = document.getElementById( 'toast' );
		if ( ! toast || ! this.toastPaused ) { return; }

		// Tabbing out of the close button while the pointer still rests on the
		// toast fires focusout, but hovering alone is reason enough to hold.
		if ( toast.matches( ':hover' ) ) { return; }

		this.toastPaused = false;
		toast.classList.remove( 'is-paused' );

		// Give a nearly-expired toast a moment to be seen again rather than
		// vanishing the instant the pointer leaves.
		this.startToastTimer( Math.max( this.toastRemaining, 600 ) );
	}

	/**
	 * (Re)start the dismiss countdown.
	 *
	 * @param {number} ms Milliseconds until the toast slides out.
	 */
	static startToastTimer ( ms ) {
		clearTimeout( this.toastTimeout );
		this.toastRemaining = ms;
		this.toastResumedAt = Date.now();
		this.toastTimeout = setTimeout( () => this.hideToast(), ms );
	}

	/**
	 * Slide the toast out and announce it, whether it timed out or was dismissed.
	 *
	 * 'toast:hidden' is deliberately late: the live Preview floats at the widget
	 * position and can sit under the toast, so it waits for the 0.3s slide-out
	 * (toast.css) before re-rendering itself.
	 */
	static hideToast () {
		const toast = document.getElementById( 'toast' );
		if ( ! toast ) { return; }

		/*
		 * Where focus is BEFORE the toast is hidden. .toast (no .show) is
		 * visibility:hidden, which correctly drops the element from the tab
		 * order — and takes focus with it, to <body>. A keyboard user who
		 * tabbed in would then have to tab the whole admin page to get back.
		 *
		 * "It auto-hides anyway" does not cover this case: focus INSIDE the
		 * toast pauses the dismiss timer by design, so a focused toast never
		 * times out. Escape or the close button is the only way out, which is
		 * exactly the path that strands them.
		 *
		 * Guarded on focus actually being inside: a toast that times out on
		 * its own, or is dismissed by mouse from elsewhere on the page, must
		 * not yank focus to somewhere the user never was.
		 */
		const returnTo = toast.contains( document.activeElement ) ? this.toastReturnFocus : null;

		clearTimeout( this.toastTimeout );
		this.toastPaused = false;
		toast.classList.remove( 'show', 'is-paused' );

		if ( returnTo && returnTo.isConnected && returnTo.focus ) { returnTo.focus(); }
		this.toastReturnFocus = null;

		clearTimeout( this.toastHiddenTimeout );
		this.toastHiddenTimeout = setTimeout( () => {
			this.app?.events?.emit( 'toast:hidden' );
		}, 300 );
	}

	/**
	 * Show a persistent "Save failed" indicator next to the save button.
	 *
	 * Toasts disappear; this stays until the next successful save so the user
	 * can re-read the friendly hint and copy the exact technical message into
	 * a support ticket. Clicking the badge toggles a details popover.
	 *
	 * The DOM is built once (buildSaveErrorState) and reused: repeat failures
	 * only update the text nodes, and a successful save just hides it.
	 * All content is set via textContent (server messages are untrusted);
	 * styling lives in components/save-error.css (theme-aware).
	 *
	 * @param {string} friendly Human-friendly hint (Utils.friendlyErrorMessage).
	 * @param {string} raw      Exact technical error message.
	 */
	static showSaveErrorState ( friendly, raw ) {
		if ( ! this.saveError && ! this.buildSaveErrorState() ) { return; }

		const ui = this.saveError;
		const time = new Date()
			.toLocaleTimeString();

		ui.time = time;
		ui.friendly = friendly || 'Unknown error.';
		ui.raw = ( raw && raw !== friendly ) ? raw : '';

		ui.title.textContent = `Last save failed at ${time}`;
		ui.hint.textContent = ui.friendly;
		ui.detail.textContent = ui.raw;
		ui.detail.style.display = ui.raw ? '' : 'none';
		ui.copyBtn.style.display = ui.raw ? '' : 'none';
		ui.copyBtn.textContent = 'Copy details';

		ui.wrap.classList.add( 'visible' );

		// Open the details popover automatically so the user sees the failure
		// without having to discover the badge is clickable. The outside-click
		// listener (buildSaveErrorState) hides it again on any click elsewhere.
		this.toggleSaveErrorPop( true );
	}

	/**
	 * Build the save-error badge/popover DOM and cache element refs.
	 * Runs at most once per page load, on the first save failure.
	 *
	 * @returns {boolean} False when the save button isn't on the page.
	 */
	static buildSaveErrorState () {
		const saveBtn = document.getElementById( 'save-button' );
		if ( ! saveBtn || ! saveBtn.parentNode ) { return false; }

		const wrap = document.createElement( 'span' );
		wrap.id = 'ctc-save-error-state';
		wrap.className = 'ctc-save-error';

		const badge = document.createElement( 'button' );
		badge.type = 'button';
		badge.className = 'ctc-save-error-badge';
		badge.setAttribute( 'aria-expanded', 'false' );
		badge.title = 'Last save failed — click for details';

		// badge.setAttribute( 'data-tip', 'Last save failed — click for details' );
		// badge.setAttribute( 'data-tip-pos', 'bottom' );
		badge.innerHTML = '<span class="dashicons dashicons-warning"></span>';
		badge.appendChild( document.createTextNode( 'Save failed' ) );

		const pop = document.createElement( 'div' );
		pop.className = 'ctc-save-error-pop';

		const title = document.createElement( 'p' );
		title.className = 'ctc-save-error-title';

		const hint = document.createElement( 'p' );
		hint.className = 'ctc-save-error-hint';

		const detail = document.createElement( 'code' );
		detail.className = 'ctc-save-error-detail';

		const copyBtn = document.createElement( 'button' );
		copyBtn.type = 'button';
		copyBtn.className = 'button button-small';
		copyBtn.textContent = 'Copy details';
		copyBtn.addEventListener( 'click', () => {
			const ui = this.saveError;
			const text = `Click to Chat save failed at ${ui.time}\n${ui.friendly}\n${ui.raw}`;

			// Shared helper — falls back to execCommand on non-secure/.local
			// contexts where navigator.clipboard is undefined.
			copyToClipboard( text )
				.then( () => { copyBtn.textContent = 'Copied!'; } )
				.catch( () => { copyBtn.textContent = 'Copy failed'; } );
		} );

		badge.addEventListener( 'click', ( event ) => {
			event.stopPropagation();
			this.toggleSaveErrorPop();
		} );

		// Single outside-click close listener for the life of the page —
		// cheap no-op while the popover is closed.
		document.addEventListener( 'click', ( event ) => {
			if ( wrap.classList.contains( 'open' ) && ! wrap.contains( event.target ) ) {
				this.toggleSaveErrorPop( false );
			}
		} );

		pop.append( title, hint, detail, copyBtn );
		wrap.append( badge, pop );
		saveBtn.parentNode.insertBefore( wrap, saveBtn.nextSibling );

		this.saveError = { wrap, badge, title, hint, detail, copyBtn, time: '', friendly: '', raw: '' };
		return true;
	}

	/**
	 * Open/close the save-error details popover.
	 *
	 * @param {boolean} [open] Force state; omit to toggle.
	 */
	static toggleSaveErrorPop ( open ) {
		const ui = this.saveError;
		if ( ! ui ) { return; }
		const show = ( typeof open === 'boolean' ) ? open : ! ui.wrap.classList.contains( 'open' );
		ui.wrap.classList.toggle( 'open', show );
		ui.badge.setAttribute( 'aria-expanded', show ? 'true' : 'false' );
	}

	/**
	 * Hide the save-error indicator (on the next successful save).
	 */
	static clearSaveErrorState () {
		if ( this.saveError ) {
			this.saveError.wrap.classList.remove( 'visible' );
			this.toggleSaveErrorPop( false );
		}
	}

	static initOfflineDetection () {
		const updateOfflineBanner = () => {
			let banner = document.getElementById( 'ctc-offline-banner' );
			if ( ! navigator.onLine ) {
				if ( ! banner ) {
					banner = document.createElement( 'div' );
					banner.id = 'ctc-offline-banner';
					banner.innerHTML = `
						<div style="
							display: flex;
							align-items: center;
							justify-content: center;
							padding: 12px;
							background-color: #fcf0f1;
							border-left: 4px solid #d63638;
							margin: 15px 0;
							border-radius: 4px;
							box-shadow: 0 1px 2px rgba(0,0,0,.05);
						">
							<span class="dashicons dashicons-warning"
							style="color: #d63638; margin-right: 8px;"></span>

							<p style="margin: 0;">
								<strong>Network Offline:</strong>
								Waiting for connection.
								Your actions are paused and will resume automatically.
							</p>
						</div>
					`;

					// Insert at the top of the main container wrap or header
					const targetContainer = document.querySelector( '.ht-ctc-admin-main-wrap' ) || document.querySelector( '.wrap' );
					if ( targetContainer ) {
						targetContainer.insertBefore( banner, targetContainer.firstChild );
					} else {
						document.body.appendChild( banner );
					}
				}
			} else {
				if ( banner ) {
					// Optional: Show a quick "Restored" success state before removing
					banner.innerHTML = `
						<div style="
							display: flex;
							align-items: center;
							justify-content: center;
							padding: 12px;
							background-color: #edfaee;
							border-left: 4px solid #46b450;
							margin: 15px 0;
							border-radius: 4px;
							box-shadow: 0 1px 2px rgba(0,0,0,.05);
						">
							<span class="dashicons dashicons-saved"
							style="color: #46b450; margin-right: 8px;"></span>

							<p style="margin: 0;">
								<strong>Network Restored:</strong>
								Operations are resuming.
							</p>
						</div>
					`;

					// Remove the banner after 3 seconds
					setTimeout( () => {
						if ( banner.parentNode ) {
							banner.parentNode.removeChild( banner );
						}
					}, 3000 );
				}
			}
		};

		window.addEventListener( 'offline', updateOfflineBanner );
		window.addEventListener( 'online', updateOfflineBanner );

		// Run once on load to verify current state
		updateOfflineBanner();
	}

	/**
	 * @param {object}  options
	 * @param {string}  options.variant One of 'success' | 'error' | '' (neutral).
	 *                                  Sets a class the stylesheet colours the
	 *                                  status icon AND the progress bar from, so
	 *                                  one message is one colour. Prefer this to
	 *                                  iconColor, which is kept only for outside
	 *                                  callers (PRO) that pass a literal colour.
	 */
	static showToast ( {
		title = '',
		description = '',
		iconClass = 'dashicons dashicons-yes-alt',
		iconColor = '',
		variant = '',
		duration = 3000,
		action = null,
	} = {} ) {
		const toast = document.getElementById( 'toast' );
		if ( ! toast ) { return; }

		// 1. Reset: Clear timers & Force Animation Restart (Reflow)
		// (also cancel a pending 'toast:hidden' emit from a toast that is
		// currently sliding out — this new toast owns the corner again).
		if ( this.toastTimeout ) { clearTimeout( this.toastTimeout ); }
		if ( this.toastHiddenTimeout ) { clearTimeout( this.toastHiddenTimeout ); }

		// Drop any hold from the outgoing toast; the new one starts its own.
		this.toastPaused = false;
		toast.classList.remove( 'show', 'is-paused', 'is-success', 'is-error' );
		if ( variant ) { toast.classList.add( `is-${variant}` ); }
		void toast.offsetWidth;

		// 2. Update Content
		const updateText = ( selector, text ) => {
			const el = toast.querySelector( selector );
			if ( el ) { el.textContent = text; }
		};

		updateText( '.toast-title', title );
		updateText( '.toast-description', description );

		// Optional action link (e.g. a PRO upgrade CTA). Hidden for normal toasts.
		const actionEl = toast.querySelector( '.toast-action' );
		if ( actionEl ) {
			if ( action && action.text && action.url ) {
				updateText( '.toast-action-text', action.text );
				actionEl.href = action.url;
				actionEl.style.display = '';
			} else {
				actionEl.style.display = 'none';
				actionEl.removeAttribute( 'href' );
			}
		}

		// Direct child only — the dismiss button's glyph is also a .dashicons
		// inside .toast-content, and this line rewrites className wholesale.
		const icon = toast.querySelector( '.toast-content > .dashicons' );
		if ( icon ) {
			icon.className = iconClass;
			icon.style.color = iconColor;
		}

		// 3. Sync Animation & Show
		const progress = toast.querySelector( '.toast-progress' );
		if ( progress ) {
			progress.style.animationDuration = `${duration}ms`;
		}

		toast.classList.add( 'show' );

		// Announce the toast lifecycle so other modules can react — the live
		// Preview floats at the widget position and can overlap the toast, so
		// it hides on 'toast:show' and re-renders itself on 'toast:hidden'.
		this.app?.events?.emit( 'toast:show' );

		this.startToastTimer( duration );

		// A toast raised while the pointer is already resting in the corner
		// (a second save from the same spot) should hold, same as one hovered
		// after it appears — :hover is a state, not only an event.
		if ( toast.matches( ':hover' ) ) { this.pauseToast(); }
	}

	/**
	 * Updates a hidden input field with a new value and triggers auto-save.
	 *
	 * @param {string} targetSelector - The ID or CSS Query Selector of the hidden input field to update.
	 * @param {string} newValue - The new value to set.
	 */
	static updateTargetInput ( targetSelector, newValue ) {
		const input = document.querySelector( targetSelector );

		if ( ! input ) {
			// console.error( `Input with selector "${targetSelector}" not found.` );
			return;
		}

		// Update value and mark as changed
		if ( input.value !== newValue ) {
			input.value = newValue;
			input.dataset.changed = 'true';

			// Trigger a change event so conditional logic (data-watch) will catch it
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );

			// Trigger auto-save if available via Event Bus
			this.app.events?.emit( 'field:dirty', input );
		}
	}

	/**
	 * Sets up a global listener for input changes to sync UI controls with hidden fields.
	 *
	 * Concept: "UI Bridge" or "Data Sync"
	 * Purpose: Connects a UI control (like a fancy switch, custom dropdown, or other interactive element)
	 *          to a hidden form field that actually stores and submits the data.
	 *
	 * How it works:
	 * 1. A UI element (source) has the class `ctc-sync-source-change`.
	 * 2. It also has a `data-sync-target` attribute pointing to the ID of the hidden field.
	 * 3. When the source changes, this listener updates the hidden field's value.
	 * 4. Auto-save is then triggered on the hidden field.
	 */
	static listenerForInputChanges () {

		// Use Event Delegation on the document to handle dynamically added elements.
		document.addEventListener( 'change', ( event ) => {

			// Check if the changed element is a sync source
			const sourceInput = event.target.closest( '.ctc-sync-source-change' );
			if ( ! sourceInput ) { return; }

			// Get the target selector from data attributes i.e. data-sync-target="some-element-id"
			const targetSelector = sourceInput.dataset.syncTarget;
			if ( ! targetSelector ) { return; }

			// Determine value to sync
			let newValue = sourceInput.value;

			// Special handling for Checkboxes
			if ( sourceInput.type === 'checkbox' ) {
				// If checked, use the checkbox's value (default 'on'), or '1' if value is missing but usually it has a value.
				// If unchecked, use empty string or a specific "unchecked value" if defined.
				const checkedValue = sourceInput.value || '1';
				const uncheckedValue = sourceInput.getAttribute( 'data-unchecked-value' ) || '';

				newValue = sourceInput.checked ? checkedValue : uncheckedValue;
			}

			// Update the hidden field
			this.updateTargetInput( targetSelector, newValue );
		} );

		/*
		 * 3. Click-as-input sync.
		 *
		 * `.ctc-sync-source-click` marks any element that stands in for a form field: click
		 * it, and its value is pushed into the input named by `data-sync-target`. Nothing
		 * about this is grid-specific — a grid tile is only one user of it.
		 *
		 * The class marks the CLICKABLE REGION; the value and target may live on it or on
		 * an ancestor, which is why both are resolved with closest(). A grid tile keeps
		 * them on `.grid-option` and puts the class on its inner select region, so the
		 * Customize button can sit outside the clickable area entirely.
		 */
		document.addEventListener( 'click', ( event ) => {
			const sourceClick = event.target.closest( '.ctc-sync-source-click' );

			// Locked elements are display-only: no real value (data-value="undefined"), so
			// syncing would overwrite the target with junk. The class may be on a child of
			// the locked element, hence closest() rather than a :not() in the selector.
			if ( ! sourceClick || sourceClick.closest( '.is-locked' ) ) { return; }

			const host = sourceClick.closest( '[data-sync-target]' );
			if ( ! host ) { return; }

			// `value` for real form controls, `data-value` for elements pretending to be one.
			const newValue = sourceClick.value || host.dataset.value;

			this.updateTargetInput( host.dataset.syncTarget, newValue );
		} );

		// 4. Grid option click — the visual radio behavior, plus the Customize trigger
		// that sits alongside it.
		document.addEventListener( 'click', async ( event ) => {
			/*
			 * The trigger is a sibling of the select region, so it never reaches the sync
			 * listener above. It is still inside .grid-option, so claim it here to skip the
			 * selection pass below.
			 */
			if ( await handleContextualTriggerClick( this.app, event ) ) { return; }

			/*
			 * Selection counts only when the click landed in the tile's interactive region —
			 * the SAME region that syncs the value. Matching `.grid-option` instead would
			 * let the tile's padding, or any slack the grid row stretches it by, highlight a
			 * tile whose value was never synced: selected on screen, unchanged underneath.
			 */
			const selectEl = event.target.closest( '.grid-option-select' );
			if ( ! selectEl ) { return; }

			const gridOption = selectEl.closest( '.grid-option' );
			if ( ! gridOption ) { return; }

			// Locked options are display-only — ignore selection clicks. PRO-locked
			// options additionally surface an upgrade hint via the toast.
			if ( gridOption.classList.contains( 'is-locked' ) ) {
				if ( gridOption.classList.contains( 'pro-option' ) ) {
					this.showToast( {
						title: 'A PRO feature',
						description: 'Unlock this and more with Click to Chat PRO.',
						iconClass: 'dashicons dashicons-star-filled',
						iconColor: 'var(--pro-color)',
						duration: 6000,
						action: {
							text: 'Upgrade to PRO',

							// Campaign-tagged by PHP so this click is attributable; the bare
							// pricing page is the fallback if the config key is absent.
							url: window.ht_ctc_admin_var?.proUrl || 'https://holithemes.com/plugins/click-to-chat/pricing/',
						},
					} );
				}
				return;
			}

			if ( null === gridOption.getAttribute( 'data-value' ) ) { return; }

			const mainGrid = gridOption.closest( '.grid' );
			if ( ! mainGrid ) { return; }

			const wasSelected = gridOption.classList.contains( 'selected' );

			// Highlight only — the VALUE is moved by the sync listener above, because the
			// tile's select region carries .ctc-sync-source-click.
			mainGrid.querySelectorAll( '.grid-option' )
				.forEach( opt => {
					const selected = opt === gridOption;
					opt.classList.toggle( 'selected', selected );
					opt.querySelector( '.grid-option-select' )
						?.setAttribute( 'aria-pressed', selected ? 'true' : 'false' );
				} );

			// An open contextual panel follows the new selection; a no-op when none is open.
			if ( ! wasSelected ) {
				await syncPanelToSelection( this.app, gridOption, mainGrid );
			}
		} );
	}

}
