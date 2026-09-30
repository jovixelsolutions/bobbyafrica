/**
 * Preview notes — short messages shown in the preview note area to set
 * expectations when the live preview can't fully represent a setting.
 *
 * Pure data + selectors, no DOM. Two sources:
 *   - field notes  : the admin edited a setting the preview can't reflect
 *                    ('no-demo') or shows differently from the live site
 *                    ('visibility-differs') — so the preview doesn't look broken.
 *   - state notes  : a consequence of interacting with the preview widget
 *                    (opt-in shows once; badge auto-hides) — 'info'.
 */

/**
 * Field-name prefix → note. The first match (by prefix) wins, so list more
 * specific prefixes first. Editing a matching field surfaces its note.
 *
 * @type {Array<{ match: string, type: string, text: string }>}
 */
export const FIELD_NOTES = [
	{
		match: 'ht_ctc_greetings_settings[g_position',
		type: 'no-demo',
		text: 'Greetings position (e.g. modal dialog) is applied on your live site, not in this preview.',
	},
	{
		match: 'ht_ctc_greetings_settings[g_init',
		type: 'no-demo',
		text: 'Initial stage controls the dialog on first load. "Open by default" opens it on desktop only — switch the preview device to see each case.',
	},
	{
		match: 'ht_ctc_greetings_settings[g_size',
		type: 'no-demo',
		text: 'Greetings dialog size applies on your live site, not in this preview.',
	},
	{
		match: 'ht_ctc_code_blocks[custom_css',
		type: 'no-demo',
		text: 'Custom CSS is applied on your live site, not in this preview.',
	},
	{
		match: 'ht_ctc_othersettings[an_',
		type: 'no-demo',
		text: 'Animations play on your live site — the preview stays static.',
	},
	{
		match: 'ht_ctc_othersettings[show_effect',
		type: 'no-demo',
		text: 'The entry effect plays when the widget appears on your live site, not in this preview.',
	},

	// Extension-specific field notes are registered by their respective extensions via PreviewManager.noteProviders.
	{
		match: 'ht_ctc_s1[',
		type: 'visibility-differs',
		text: 'Style 1 uses your theme’s button, so the live result can differ from this preview.',
	},
	{
		match: 'ht_ctc_chat_options[display]',
		type: 'visibility-differs',
		text: 'Display rules (incl. WooCommerce pages) apply on your live site; the preview always shows the widget.',
	},

	// Mobile-specific options are previewed directly via the device switch (see preview/device.js).
];

/**
 * The note for a changed field, or null when the field has none.
 *
 * @param {string} name e.g. 'ht_ctc_greetings_settings[g_position]'
 * @returns {{ match: string, type: string, text: string }|null}
 */
export const noteForField = ( name ) => {
	if ( typeof name !== 'string' || name === '' ) { return null; }
	return FIELD_NOTES.find( ( note ) => name.startsWith( note.match ) ) || null;
};

/*
 * A message variable the preview cannot fill in.
 *
 * {site} is the exception — PreviewManager.applyMessageVariables substitutes it
 * from config.preview.site, so it is excluded here (and the `{{site}}` form
 * reduces to it, so that is covered too). Everything else needs a page: the
 * frontend resolves {title}/{url} per request, and product variables need a
 * product. Those stay literal, which is correct but looks like a typo the admin
 * should go and fix — hence the note.
 */
const UNRESOLVED_VARIABLE = /\{(?!site\})[a-z0-9_]+\}/i;

/**
 * Whether a written value contains a variable the preview leaves literal.
 *
 * @param {*} value
 * @returns {boolean}
 */
export const hasUnresolvedVariable = ( value ) =>
	typeof value === 'string' && UNRESOLVED_VARIABLE.test( value );

/**
 * Single-line notes shown ONE at a time in the preview note area (the latest
 * interaction wins — they are never concatenated). Each is set on the event it
 * describes, so it's contextual rather than persistent.
 */
export const NOTES = {
	variables: 'Variables like {title} and {url} are filled in per page on your live site — the preview leaves them as written. {site} is already filled in.',
	optinOnce: 'On your live site the opt-in shows once per visitor — after they accept, it won’t show again.',
	badgeHidden: 'The notification badge hides once the chat or greeting is opened; change a badge setting to show it again.',
	sameTab: 'No preview link for navigation when Same-tab open (URL Structure).',
	noNumber: 'Add a WhatsApp number to test the chat link.',
};

/**
 * The contextual note for a visibility-state transition, or '' when the change
 * isn't noteworthy (so a stale note clears). Pure — drives PreviewManager's
 * single transientNote.
 *
 * @param {{ badgeStopped: boolean, optinDone: boolean }} prev
 * @param {{ badgeStopped: boolean, optinDone: boolean }} next
 * @returns {string}
 */
export const noteForTransition = ( prev, next ) => {
	if ( ! prev.optinDone && next.optinDone ) { return NOTES.optinOnce; }
	if ( ! prev.badgeStopped && next.badgeStopped ) { return NOTES.badgeHidden; }
	return '';
};
