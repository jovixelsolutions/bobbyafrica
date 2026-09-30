/**
 * Preview Template — Greetings 2 (compact card layout)
 *
 * JS port of new/inc/greetings/greetings-2.php. Class names, inline CSS, and
 * option defaults must stay in sync with the PHP template.
 *
 * Rich-text content (main/bottom) is admin-authored and kses-sanitized on
 * save — injected as HTML, same trust level as the PHP template.
 */
import { escapeAttr } from '../../core/Utils.js';
import { richContent, optInBlock, gCtaBlock } from '../greetings-parts.js';

export default function renderGreetings2 ( ctx ) {
	const gOpt = ( key ) => ctx.value( 'ht_ctc_greetings_options', key ) || '';

	const mainContent = richContent( gOpt( 'main_content' ), ctx );
	const bottomContent = richContent( gOpt( 'bottom_content' ), ctx );
	const gCta = gOpt( 'call_to_action' ) || 'WhatsApp';

	// css
	let mainCss = 'padding: 18px 20px 15px 20px;';
	let sendCss = 'text-align:center; padding: 11px 20px 9px 20px; cursor:pointer;';
	let bottomCss = 'padding: 2px 20px 2px 20px;text-align:center; font-size:12px;';

	const bgColor = ctx.value( 'ht_ctc_greetings_2', 'bg_color' ) || '#ffffff';

	mainCss += `background-color:${bgColor};`;
	bottomCss += `background-color:${bgColor};`;
	sendCss += `background-color:${bgColor};`;

	// greetings-2 always uses cta style 1.
	const ctaStyle = '1';

	const main = `<div class="ctc_g_content" style="${escapeAttr( mainCss )}">
		<div class="ctc_g_message_box" style="">${mainContent}</div>
	</div>`;

	const send = `<div class="ctc_g_sentbutton" style="${escapeAttr( sendCss )}">
		${optInBlock( ctx )}
		<div class="ht_ctc_chat_greetings_box_link">${gCtaBlock( ctaStyle, ctx, gCta )}</div>
	</div>`;

	let bottom = '';
	if ( bottomContent !== '' ) {
		bottom = `<div class="ctc_g_bottom" style="${escapeAttr( bottomCss )}">` +
			`${bottomContent}</div>`;
	}

	return `${main}${send}${bottom}`;
}

// ============================================================================
// PHP-PARITY RESEARCH — CORRECTIONS REMAIN COMMENTED
// ============================================================================
// - The active renderer is the exact feature-branch implementation.
// - greetings-parts.js is absent locally, so this module cannot currently
//   import. That helper also depends on the incomplete css.js export.
// - Unsaved main/bottom/opt-in rich HTML needs the same explicit allowlist and
//   safe variable substitution described for Greetings 1 before innerHTML.
// - Replace {site}, {title}, and {url} deterministically; document unsupported
//   shortcodes, translations, frontend filters, and page-level substitutions.
// - Empty/missing bg_color intentionally falls back to #ffffff. Nonempty live
//   color values still require safe CSS-value handling before interpolation.
// - Greetings 2 always uses CTA Style 1 and blank CTA falls back to WhatsApp.
// - The CTA link wrapper requires ctc-analytics; shared gCta1 must also restore
//   PHP's analytics class and exact Style 1 option/default behavior.
// - Opt-in enablement follows key-presence/value semantics and its rich label
//   must preserve safe formatting while removing dangerous markup and URLs.
// - Greetings 2 intentionally renders its main content container when content
//   is empty; bottom content remains conditional, matching PHP.
// - The outer greetings manager still lacks template-2 shadow, modal/next,
//   device/init state, positioning, mobile full width, size, and interaction.
