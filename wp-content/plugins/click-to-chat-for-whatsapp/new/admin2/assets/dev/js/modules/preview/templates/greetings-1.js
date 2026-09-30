/**
 * Preview Template — Greetings 1 (header + chat-bubble layout)
 *
 * JS port of new/inc/greetings/greetings-1.php. Class names, inline CSS, and
 * option defaults must stay in sync with the PHP template.
 *
 * Rich-text content (header/main/bottom) is admin-authored and kses-sanitized
 * on save — injected as HTML, same trust level as the PHP template.
 */
import { escapeAttr } from '../../core/Utils.js';
import { richContent, optInBlock, gCtaBlock } from '../greetings-parts.js';
import { escapeCssValue } from '../css.js';

export default function renderGreetings1 ( ctx ) {
	const gOpt = ( key ) => ctx.value( 'ht_ctc_greetings_options', key ) || '';
	const g1Opt = ( key ) => ctx.value( 'ht_ctc_greetings_1', key ) || '';

	const headerContent = richContent( gOpt( 'header_content' ), ctx );
	const mainContent = richContent( gOpt( 'main_content' ), ctx );
	const bottomContent = richContent( gOpt( 'bottom_content' ), ctx );
	const gCta = gOpt( 'call_to_action' ) || 'WhatsApp';
	const gHeaderImage = gOpt( 'g_header_image' );

	// css
	let headerCss = 'display: flex; align-items: center; padding: 12px 25px 12px 25px;';
	let mainCss = '';
	let messageBoxCss = 'margin: 8px 5px;';
	const sendCss = 'text-align:center; padding: 11px 25px 9px 25px; cursor:pointer;background-color:#ffffff;';
	const bottomCss = 'padding: 2px 25px 2px 25px; text-align:center; font-size:12px;background-color:#ffffff;';

	const headerBgColor = g1Opt( 'header_bg_color' ) || '#ffffff';
	const mainBgColor = g1Opt( 'main_bg_color' ) || '#ffffff';
	const messageBoxBgColor = g1Opt( 'message_box_bg_color' );
	const mainBgImage = ( g1Opt( 'main_bg_image' ) !== '' ) ? 'yes' : '';

	headerCss += `background-color:${headerBgColor};`;
	mainCss += `background-color:${mainBgColor};`;

	const rtl = ctx.isRtl;

	// Respect greeting size setting.
	const gSize = ctx.value( 'ht_ctc_greetings_settings', 'g_size' ) || 's';

	let mainPaddingBottom = ( mainBgImage === 'yes' ) ? '72px' : '40px';
	let messageBoxMinusWidth = '20px';

	if ( gSize === 's' ) {
		messageBoxMinusWidth = '15px';
	} else if ( gSize === 'm' ) {
		mainPaddingBottom = '98px';
		messageBoxMinusWidth = '30px';
	} else if ( gSize === 'l' ) {
		mainPaddingBottom = '108px';
		messageBoxMinusWidth = '40px';
	}

	mainCss += rtl ?
		`padding: 18px 18px ${mainPaddingBottom} 24px;` :
		`padding: 18px 24px ${mainPaddingBottom} 18px;`;

	let headerImageCss = 'border-radius:50%;height:50px; width:50px;';
	headerImageCss += rtl ? 'margin-left:9px;' : 'margin-right:9px;';

	if ( messageBoxBgColor !== '' ) {
		messageBoxCss += 'padding:6px 8px 8px 9px;';
	}

	const ctaStyle = g1Opt( 'cta_style' ) || '7_1';

	headerCss += ( gHeaderImage !== '' ) ? 'line-height:1.1;' : 'line-height:1.3;';

	// <style> block: bg image overlay + message-box bubble color/clip-path.
	let styleBlock = '';
	if ( mainBgImage === 'yes' ) {
		const imgUrl = escapeCssValue( ctx.pluginUrl ) + 'new/inc/assets/img/wa_bg.png';
		styleBlock += '.ctc_g_content_for_bg_image:before{content:"";position:absolute;' +
			`top:0;left:0;width:100%;height:100%;background:url('${imgUrl}');opacity:0.07;}`;
	}
	if ( messageBoxBgColor !== '' ) {
		const bgVal = escapeCssValue( messageBoxBgColor );
		const minusWidthVal = escapeCssValue( messageBoxMinusWidth );

		styleBlock += `:root{--ctc_g_message_box_bg_color:${bgVal};}`;
		styleBlock += '\n.template-greetings-1 .ctc_g_message_box{' +
			`position:relative;max-width:calc(100% - ${minusWidthVal});` +
			'background-color:var(--ctc_g_message_box_bg_color);' +
			'box-shadow:0 1px 0.5px 0 rgba(0,0,0,.14);}';
		styleBlock += '\n.template-greetings-1 .ctc_g_message_box:before{' +
			'content:"";position:absolute;top:0px;height:18px;width:9px;' +
			'background-color:var(--ctc_g_message_box_bg_color);}';
		if ( rtl ) {
			styleBlock += '.ctc_g_message_box{border-radius:7px 0px 7px 7px;}.ctc_g_message_box:before{left:100%;clip-path:polygon(0% 0%, 0% 50%, 100% 0%);-webkit-clip-path:polygon(0% 0%, 0% 50%, 100% 0%);}';
		} else {
			styleBlock += '.ctc_g_message_box{border-radius:0px 7px 7px 7px;}.ctc_g_message_box:before{right:99.7%;clip-path:polygon(0% 0%, 100% 0%, 100% 50%);-webkit-clip-path:polygon(0% 0%, 100% 0%, 100% 50%);}';
		}
	}

	// Header
	let header = '';
	if ( headerContent !== '' ) {
		let headerImage = '';
		if ( gHeaderImage !== '' ) {
			const onlineStatusEnabled = ctx.value( 'ht_ctc_greetings_options', 'g_header_online_status' );
			const badgeColor = ctx.value( 'ht_ctc_greetings_options', 'g_header_online_status_color' ) || '#06e376';

			// Positioning/size comes from main.css (.g_header_badge_online); only dynamic colors go inline.
			const escHeaderBg = escapeAttr( headerBgColor );
			const escBadgeColor = escapeAttr( badgeColor );
			const badge = onlineStatusEnabled ?
				'<span class="for_greetings_header_image_badge g_header_badge_online" ' +
				`style="border:2px solid ${escHeaderBg};background-color:${escBadgeColor};">` +
				'</span>' :
				'';
			headerImage = '<div class="greetings_header_image" ' +
				`style="${escapeAttr( headerImageCss )}">
				<img style="display:inline-block; border-radius:50%; height:50px; width:50px;" ` +
					`src="${escapeAttr( gHeaderImage )}" alt="header-image">${badge}
			</div>`;
		}
		header = `<div class="ctc_g_heading" style="${escapeAttr( headerCss )}">
			${headerImage}
			<div class="ctc_g_header_content">${headerContent}</div>
		</div>`;
	}

	// Main content
	let main = '';
	if ( mainContent !== '' ) {
		const messageBox = '<div class="ctc_g_message_box ctc_g_message_box_width" ' +
			`style="${escapeAttr( messageBoxCss )}">${mainContent}</div>`;
		if ( mainBgImage === 'yes' ) {
			main = `<div class="ctc_g_content" style="${escapeAttr( mainCss )} position:relative;">
				<div class="ctc_g_content_for_bg_image">${messageBox}</div>
			</div>`;
		} else {
			main = `<div class="ctc_g_content" style="${escapeAttr( mainCss )}">` +
				`${messageBox}</div>`;
		}
	}

	// Send button (opt-in + CTA)
	const send = `<div class="ctc_g_sentbutton" style="${escapeAttr( sendCss )}">
		${optInBlock( ctx )}
		<div class="ht_ctc_chat_greetings_box_link">${gCtaBlock( ctaStyle, ctx, gCta )}</div>
	</div>`;

	// Bottom content
	let bottom = '';
	if ( bottomContent !== '' ) {
		bottom = `<div class="ctc_g_bottom" style="${escapeAttr( bottomCss )}">` +
			`${bottomContent}</div>`;
	}

	const styleTag = ( styleBlock !== '' ) ? `<style>${styleBlock}</style>` : '';

	return `${styleTag}${header}${main}${send}${bottom}`;
}

// ============================================================================
// PHP-PARITY RESEARCH — CORRECTIONS REMAIN COMMENTED
// ============================================================================
// - The active renderer is the exact feature-branch implementation.
// - greetings-parts.js is absent locally, and css.js lacks escapeCssValue, so
//   this module cannot currently import from a clean or working checkout.
// - Unsaved header/main/bottom/opt-in HTML is live form data and has not passed
//   the PHP save sanitizer. Before innerHTML assignment, greetings-parts.js
//   must sanitize it with an explicit element/attribute/URL-scheme allowlist.
// - Variable substitution must deterministically replace {site}, {title}, and
//   {url}; the branch helper handles only {site}. Shortcodes, translations,
//   filters, and page-level values need a preview limitation note.
// - main_bg_image uses PHP isset semantics. A present empty checkbox value is
//   enabled, while the active branch incorrectly treats it as disabled.
// - Empty header/main background colors intentionally fall back to #ffffff.
//   Message-box color remains empty when explicitly cleared.
// - cta_style uses PHP isset semantics. Explicit empty/unsupported styles must
//   render no CTA include; the branch helper currently falls back to 7_1.
// - Header image src needs URL validation equivalent to esc_url/ctc_sanitize_url.
//   Its alt text should mirror PHP pathinfo instead of hardcoded header-image.
// - [FIXED] Online-status badge rendered with CSS classes from main.css (loaded via
//   PreviewManager.injectFrontCss); only dynamic border/background colors go inline.
// - The CTA link wrapper requires ctc-analytics, and both shared CTA renderers
//   are also missing PHP analytics classes.
// - Opt-in uses key-presence for is_opt_in and must preserve sanitized rich
//   formatting while stripping event handlers and dangerous URLs.
// - The outer greetings manager still lacks PHP wrapper state for modal/next,
//   top/bottom placement, g_device, g_init, mobile full width, and size/shadow.
