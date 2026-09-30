/**
 * Device rules for the preview.
 *
 * Pure logic, no DOM. Mirrors the front end so the preview and the real widget
 * can't drift — same spirit as preview/visibility-state.js.
 *
 * The front end decides device in JS, not in PHP. PHP renders BOTH device
 * variants (.ht_ctc_desktop_chat / .ht_ctc_mobile_chat) and app.dev.js then
 * removes the wrong one, applies that device's position and gates on that
 * device's show/hide (display_chat, app.dev.js). Its test is:
 *
 *   mobile UA regex || screen.width <= 1025
 *
 * so there are exactly TWO tiers and the boundary is 1025px — there is no
 * tablet tier to model. PHP's own wp_is_mobile() check is UA-only, so the two
 * can disagree (a 1024px desktop window is desktop to PHP, mobile to JS). The
 * preview mirrors the JS rule, because that is what decides what a visitor
 * actually sees.
 *
 * Here `device` is the admin's SELECTED device, not a detected one — the
 * preview simulates rather than sniffs.
 */

export const DESKTOP = 'desktop';
export const MOBILE = 'mobile';

/**
 * Width at or below which the front end treats the visitor as mobile.
 * Exported for labels/notes; nothing here measures a viewport.
 */
export const MOBILE_MAX_WIDTH = 1025;

/**
 * @param {string} value
 * @returns {'desktop'|'mobile'} Anything unrecognised falls back to desktop.
 */
export const normalizeDevice = ( value ) => ( value === MOBILE ? MOBILE : DESKTOP );

/**
 * @param {string} device
 * @returns {boolean}
 */
export const isMobile = ( device ) => normalizeDevice( device ) === MOBILE;

/**
 * Which style option this device renders.
 *
 * `same_settings` ("use the same settings for both") makes mobile inherit the
 * desktop style outright — class-ht-ctc-chat.php:215.
 *
 * @param {string}  device
 * @param {boolean} sameSettings
 * @returns {'style_desktop'|'style_mobile'}
 */
export const styleKeyFor = ( device, sameSettings ) => (
	( isMobile( device ) && ! sameSettings ) ? 'style_mobile' : 'style_desktop'
);

/**
 * Which show/hide option gates the widget on this device.
 *
 * @param {string} device
 * @returns {'display_desktop'|'display_mobile'}
 */
export const displayKeyFor = ( device ) => (
	isMobile( device ) ? 'display_mobile' : 'display_desktop'
);

/**
 * Whether the widget renders at all on this device.
 *
 * The front end tests `'show' === ctc.dis_m` / `dis_d`, so anything that is not
 * exactly 'show' hides the widget; an absent key defaults to 'show' as it does
 * in PHP (class-ht-ctc-chat.php:293).
 *
 * @param {string} device
 * @param {(key: string) => *} read reads a ht_ctc_chat_options key
 * @returns {boolean}
 */
export const widgetShowsOn = ( device, read ) => {
	const value = read( displayKeyFor( device ) );
	const resolved = ( value === undefined || value === null || value === '' ) ? 'show' : String( value );
	return resolved === 'show';
};

/**
 * Resolve the position for this device, mirroring commons/position-to-place.php.
 *
 * Two things there are easy to get wrong, and both are load-bearing:
 *   - each mobile key falls back to its DESKTOP counterpart, not to a
 *     hardcoded default, so a half-filled mobile position inherits the rest;
 *   - `same_settings` makes mobile use the desktop position wholesale, without
 *     consulting the mobile keys at all.
 *
 * positionType is always 'fixed': position-to-place.php takes it from the
 * `ht_ctc_fh_position_type_mobile` filter whose default is 'fixed', and nothing
 * in the free plugin hooks that filter — so the stored `position_type_mobile`
 * option does not reach the free front end. Previewing 'absolute' here would
 * make the preview wrong in a NEW direction, so it deliberately does not.
 *
 * @param {string} device
 * @param {(key: string) => *} read reads a ht_ctc_chat_options key
 * @param {boolean} sameSettings
 * @returns {{ side1: string, side1Value: *, side2: string, side2Value: *, positionType: string }}
 */
export const resolvePosition = ( device, read, sameSettings ) => {
	const desktop = {
		side1: read( 'side_1' ),
		side1Value: read( 'side_1_value' ),
		side2: read( 'side_2' ),
		side2Value: read( 'side_2_value' ),
		positionType: 'fixed',
	};

	if ( ! isMobile( device ) || sameSettings ) {
		return desktop;
	}

	const orDesktop = ( value, fallback ) => (
		( value === undefined || value === null || value === '' ) ? fallback : value
	);

	return {
		side1: orDesktop( read( 'mobile_side_1' ), desktop.side1 ),
		side1Value: orDesktop( read( 'mobile_side_1_value' ), desktop.side1Value ),
		side2: orDesktop( read( 'mobile_side_2' ), desktop.side2 ),
		side2Value: orDesktop( read( 'mobile_side_2_value' ), desktop.side2Value ),
		positionType: 'fixed',
	};
};

/**
 * Whether the greetings dialog exists on this device.
 *
 * app.dev.js removes the box when it is desktop-only on mobile, or mobile-only
 * on desktop. 'all' (and an absent key — PHP omits g_device when it is 'all')
 * means both.
 *
 * @param {string} device
 * @param {string} gDevice 'all' | 'mobile' | 'desktop'
 * @returns {boolean}
 */
export const greetingsAppliesTo = ( device, gDevice ) => {
	if ( ! gDevice || gDevice === 'all' ) { return true; }
	return gDevice === ( isMobile( device ) ? MOBILE : DESKTOP );
};

/**
 * Whether the greetings dialog goes full width on this device.
 *
 * Not a hidden rule — the g_size options say it outright ("Desktop: Medium,
 * Mobile: Full width"). 'm' and 'l' add .ctc_m_full_width, 's' does not, and a
 * modal dialog clears it again (class-ht-ctc-chat-greetings.php).
 *
 * @param {string} device
 * @param {string} gSize     's' | 'm' | 'l'
 * @param {string} gPosition 'modal' | 'next' | ''
 * @returns {boolean}
 */
export const greetingsFullWidthOn = ( device, gSize, gPosition ) => (
	isMobile( device ) && ( gSize === 'm' || gSize === 'l' ) && gPosition !== 'modal'
);

/**
 * Whether the widget itself goes full width on this device.
 *
 * Styles 1 and 8 each carry their own "Full Width on Mobile" checkbox, read
 * with key-presence semantics (the frontend's isset).
 *
 * @param {string} device
 * @param {string} styleId
 * @param {(group: string, key: string) => *} read
 * @returns {boolean}
 */
export const widgetFullWidthOn = ( device, styleId, read ) => {
	if ( ! isMobile( device ) ) { return false; }

	let value;
	if ( String( styleId ) === '1' ) {
		value = read( 'ht_ctc_s1', 's1_m_fullwidth' );
	} else if ( String( styleId ) === '8' ) {
		value = read( 'ht_ctc_s8', 's8_m_fullwidth' );
	} else {
		return false;
	}

	return value !== undefined && value !== null && value !== '';
};

/**
 * Whether the front end AUTO-OPENS the greetings dialog on this device.
 *
 * The one people never notice: `g_init: 'default'` means "auto-open on desktop
 * only" — app.dev.js opens on init for 'default' only when is_mobile !== 'yes'.
 * 'open' opens on both. Unset opens on neither.
 *
 * @param {string} device
 * @param {string} gInit 'default' | 'open' | ''
 * @returns {boolean}
 */
export const autoOpensGreetings = ( device, gInit ) => {
	if ( gInit === 'open' ) { return true; }
	if ( gInit === 'default' ) { return ! isMobile( device ); }
	return false;
};
