/**
 * Resolve a preview-widget click into an action — the routing that mirrors the
 * live site (greetings(): base widget toggles the dialog / navigates; the
 * dialog CTA navigates or, with opt-in, reveals the opt-in first).
 *
 * Pure: the caller does the DOM matching (which interactive ancestor the click
 * landed in) and runs the side effects; this just decides WHICH action, so the
 * branching is unit-testable in isolation.
 *
 * @param {{ optin: boolean, closeBtn: boolean, greetingCta: boolean, numberedChat: boolean, baseWidget: boolean }} hit
 *   which interactive ancestor was clicked (from event.target.closest)
 * @param {{ greetingEnabled: boolean, optinEnabled: boolean, optinDone: boolean }} ctx
 * @returns {('optin_accept'|'greeting_close'|'optin_reveal'|'navigate'|'numbered_navigate'|'greeting_toggle'|'base_navigate'|null)}
 */
export const resolveClickAction = ( hit, ctx ) => {
	// Most specific elements first (opt-in / close sit inside the dialog).
	if ( hit.optin ) { return 'optin_accept'; }
	if ( hit.closeBtn ) { return 'greeting_close'; }

	// Dialog CTA: opt-in gate intercepts the first click, else navigate.
	if ( hit.greetingCta ) {
		return ( ctx.optinEnabled && ! ctx.optinDone ) ? 'optin_reveal' : 'navigate';
	}

	// A chat trigger carrying its own number (.ctc_chat[data-number] — the
	// same generic contract the frontend's ht_ctc_link honors): navigates with
	// that element's number, behind the same opt-in gate as the CTA.
	if ( hit.numberedChat ) {
		return ( ctx.optinEnabled && ! ctx.optinDone ) ? 'optin_reveal' : 'numbered_navigate';
	}

	// Base widget: toggle the dialog when there is one, otherwise navigate.
	if ( hit.baseWidget ) {
		return ctx.greetingEnabled ? 'greeting_toggle' : 'base_navigate';
	}

	return null;
};
