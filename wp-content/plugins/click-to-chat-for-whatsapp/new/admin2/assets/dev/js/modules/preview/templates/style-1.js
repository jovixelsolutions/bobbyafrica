/**
 * Preview Template — Style 1
 *
 * Mirrors new/inc/styles/style-1.php. Style 1 uses the active theme's button
 * design on the frontend, so this template can only preview its configured
 * colors, icon, text, and mobile full-width rule.
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { singleColorIcon } from '../icons.js';

export default function renderStyle1 ( ctx ) {
	const textColor = ctx.value( 'ht_ctc_s1', 's1_text_color' ) ?? '';
	const bgColor = ctx.value( 'ht_ctc_s1', 's1_bg_color' ) ?? '';
	const addIcon = ctx.value( 'ht_ctc_s1', 's1_add_icon' ) ?? '';
	let iconColor = ctx.value( 'ht_ctc_s1', 's1_icon_color' ) ?? '';
	let iconSize = ctx.value( 'ht_ctc_s1', 's1_icon_size' ) ?? '';

	if ( iconSize === '' ) {
		iconSize = '15';
	}
	if ( iconColor === '' ) {
		iconColor = '#ffffff';
	}

	let callToAction = String( ctx.cta ?? '' );
	if ( callToAction === '' ) {
		callToAction = 'WhatsApp us';
	}

	let buttonCss = 'cursor:pointer; display:flex; align-items:center; justify-content:center;';
	buttonCss += textColor !== '' ? `color:${textColor};` : '';
	buttonCss += bgColor !== '' ? `background-color:${bgColor};` : '';
	buttonCss += 'padding:5px 7px;';

	let icon = '';
	if ( addIcon !== '' ) {
		icon = singleColorIcon( {
			color: iconColor,
			iconSize,
			type: ctx.type || 'chat',
			svgCss: 'margin-right:6px;',
		} );
	}

	// Full Width on Mobile is NOT emitted as the frontend's
	// `@media(max-width:1201px)` block. That query is about the browser window,
	// and the preview's window is the admin — so it fired on a narrow admin
	// screen (making the DESKTOP preview full width) and never fired on a wide
	// one, which is backwards both ways. PreviewManager puts the rule on the
	// container for the selected device instead; see ctc-mobile-w-fullwidth.

	const buttonStyle = escapeAttr( buttonCss );
	const html = `<button style="${buttonStyle}" ` +
		`class="ctc-analytics s1_btn ctc_s_1">
		${icon}
		<span class="ctc_cta">${escapeHTML( callToAction )}</span>
	</button>`;

	return {
		html,
		note: 'Style 1 inherits the active theme’s button design on the frontend.',
	};
}
