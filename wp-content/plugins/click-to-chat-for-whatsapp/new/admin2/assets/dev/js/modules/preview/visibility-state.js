/**
 * Visibility state machine for the chat widget's notification badge and
 * greetings dialog.
 *
 * Pure logic, no DOM. Mirrors the front-end behavior in
 * new/inc/assets/js/dev/app.dev.js (greetings_open / greetings_close /
 * stop_notification_badge) so the admin live preview and the real widget
 * can't drift. Designed so the front end could adopt the same model if/when
 * its imperative handlers are refactored.
 *
 * State (intent — NOT "is it on screen right now"):
 *   greetingOpen — has the visitor/admin opened the greetings dialog?
 *                  (the front end persists this as g_user_action)
 *   badgeStopped — has the notification badge been dismissed?
 *                  (the front end persists this as n_badge = 'stop')
 *   optinDone    — has the opt-in checkbox been ticked? It shows only once.
 *                  (the front end persists this as g_optin = 'y')
 *
 * Visibility is DERIVED from this state by the caller, never stored:
 *   greetingVisible = greetingOpen && greetingsEnabled(settings)
 *   badgeHidden     = badgeStopped || greetingVisible
 *   ctaHidden       = greetingVisible
 *   optinHidden     = optinDone   (the opt-in element only exists inside an
 *                                  open dialog with opt-in enabled anyway)
 * so "opening the dialog hides the badge and the call-to-action" needs no
 * special-case code — it falls out of the derivation.
 */

/**
 * @returns {{ greetingOpen: boolean, badgeStopped: boolean, optinDone: boolean }}
 */
export const initialState = () => ( {
	greetingOpen: true,
	badgeStopped: false,
	optinDone: false,
} );

/**
 * Pure transition. Always returns a NEW state object.
 *
 * @param {{ greetingOpen: boolean, badgeStopped: boolean, optinDone: boolean }} state
 * @param {string} event widget_click | greeting_toggle | greeting_close |
 *   notification_change | greeting_change | optin_change | optin_click
 * @returns {{ greetingOpen: boolean, badgeStopped: boolean, optinDone: boolean }}
 */
export const nextState = ( state, event ) => {
	switch ( event ) {
		// Base widget click with NO greeting configured: opens WhatsApp (handled
		// by the manager) and dismisses the badge. There's no dialog to toggle.
		case 'widget_click':
			return { ...state, badgeStopped: true };

		// Base widget click WITH a greeting: toggle the dialog — open it if
		// closed (even after the × button), close it if open — mirroring the
		// live site. Also dismisses the badge.
		case 'greeting_toggle':
			return { ...state, greetingOpen: ! state.greetingOpen, badgeStopped: true };

		// Close button, or toggling the widget shut. The badge stays dismissed.
		case 'greeting_close':
			return { ...state, greetingOpen: false };

		// Preview-only: the admin edited a badge setting, so reveal the badge
		// (and close the dialog that would otherwise cover it) to show the change.
		case 'notification_change':
			return { ...state, greetingOpen: false, badgeStopped: false };

		// Preview-only: the admin edited a greetings setting, so reopen the
		// dialog to show the change.
		case 'greeting_change':
			return { ...state, greetingOpen: true };

		// Preview-only: the admin edited an opt-in setting — reopen the dialog
		// and un-dismiss the opt-in so the change is visible.
		case 'optin_change':
			return { ...state, greetingOpen: true, optinDone: false };

		// The visitor/admin ticked the opt-in checkbox: it won't show again.
		case 'optin_click':
			return { ...state, optinDone: true };

		default:
			return state;
	}
};

/**
 * Map a changed form field name to a state event, or null when the field
 * doesn't affect badge/greetings visibility. Lets the preview react to the
 * RIGHT settings declaratively, instead of snapshotting every value and
 * diffing strings.
 *
 * @param {string} name e.g. 'ht_ctc_othersettings[notification_badge]'
 * @returns {string|null}
 */
export const eventForField = ( name ) => {
	if ( typeof name !== 'string' || name === '' ) { return null; }
	if ( name.startsWith( 'ht_ctc_othersettings[notification' ) ) { return 'notification_change'; }

	// Opt-in fields live inside ht_ctc_greetings_settings — match them BEFORE
	// the general greetings check so they map to their own reset event.
	if ( name.startsWith( 'ht_ctc_greetings_settings[is_opt_in' ) ||
		name.startsWith( 'ht_ctc_greetings_settings[opt_in' ) ) { return 'optin_change'; }
	if ( name.startsWith( 'ht_ctc_greetings_options[' ) ||
		name.startsWith( 'ht_ctc_greetings_settings[' ) ) { return 'greeting_change'; }
	return null;
};

/**
 * Seed state from persisted ht_ctc_storage so a dismissed badge / closed
 * dialog stays that way across reloads. Takes a single `get(key)` reader so
 * this module owns its own storage-key vocabulary (and new dimensions — e.g.
 * opt-in's `g_optin` — are one line here, not another call-site parameter).
 *
 * Unset or unknown values fall back to the default (dialog open, badge shown,
 * opt-in not yet done), matching the front end: only g_user_action ===
 * 'user_closed' suppresses the dialog, only n_badge === 'stop' counts the badge
 * as dismissed, and only g_optin === 'y' counts the opt-in as completed.
 *
 * @param {(key: string) => *} get reads a ht_ctc_storage key
 * @returns {{ greetingOpen: boolean, badgeStopped: boolean, optinDone: boolean }}
 */
export const stateFromStorage = ( get ) => ( {
	greetingOpen: get( 'g_user_action' ) !== 'user_closed',
	badgeStopped: get( 'n_badge' ) === 'stop',
	optinDone: get( 'g_optin' ) === 'y',
} );

/**
 * Map state to the [key, value] pairs to persist, in the front end's
 * vocabulary so the live widget on the same browser reads them the same way
 * (n_badge 'stop' | 'admin_start' as in Actions.updateNotificationBadgeLS;
 * g_user_action 'user_closed' | 'user_opened'; g_optin 'y' | ''). Pairs (not an
 * object) keep the function pure and let the caller write each key without
 * snake_case locals.
 *
 * @param {{ greetingOpen: boolean, badgeStopped: boolean, optinDone: boolean }} state
 * @returns {Array<[string, string]>}
 */
export const storageFromState = ( state ) => [
	[ 'n_badge', state.badgeStopped ? 'stop' : 'admin_start' ],
	[ 'g_user_action', state.greetingOpen ? 'user_opened' : 'user_closed' ],
	[ 'g_optin', state.optinDone ? 'y' : '' ],
];
