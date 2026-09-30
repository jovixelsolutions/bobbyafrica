/**
 * Shared building blocks for greetings dialog preview templates.
 *
 * JS ports of new/inc/greetings/greetings_styles/{opt-in,g-cta-1,g-cta-7_1}.php.
 * Class names, inline CSS, and option defaults must stay in sync with PHP.
 */
import { escapeAttr, escapeHTML, decodeHTML, autop as coreAutop } from '../core/Utils.js';
import { singleColorIcon } from './icons.js';
import { escapeCssValue } from './css.js';
import { sanitizeRichHtml } from './sanitize.js';

/**
 * Prepare a greetings rich-text field for preview injection.
 *
 * Decodes stored HTML entities (mirrors the frontend's html_entity_decode),
 * substitutes the {site} variable ({title}/{url} are page-dependent and stay
 * literal in preview), then runs autop to wrap plain text in <p> tags.
 *
 * @param {string} content
 * @param {Object} [ctx] Preview context (for ctx.site).
 * @returns {string}
 */
export const richContent = ( content, ctx ) => {
	let trimmed = decodeHTML( content )
		.trim();
	if ( trimmed === '' ) { return ''; }
	if ( ctx?.site ) {
		trimmed = trimmed.replaceAll( '{site}', escapeHTML( ctx.site ) );
	}

	// Live unsaved editor content bypasses the server-side wp_kses_post, so
	// every rich-text field is allowlist-sanitized before innerHTML injection.
	return sanitizeRichHtml( coreAutop( trimmed ) );
};

/**
 * Opt-in checkbox — port of opt-in.php. The PHP version starts hidden and is
 * revealed by frontend JS; the preview renders it visible directly.
 *
 * @param {Object} ctx Preview context.
 * @returns {string}
 */
export const optInBlock = ( ctx ) => {
	const isOptIn = ctx.value( 'ht_ctc_greetings_settings', 'is_opt_in' ) || '';
	if ( isOptIn === '' ) { return ''; }

	const optIn = ctx.value( 'ht_ctc_greetings_settings', 'opt_in' ) || 'Privacy Policy';
	const ctcOptStyle = 'display:inline-flex;justify-content:center;align-items:center;' +
		'padding:0 4px;';

	// Opt-in text may be live unsaved editor input — sanitize before injection.
	return `<div class="ctc_opt_in" style="text-align:center;">
		<div class="ctc_opt" style="${ctcOptStyle}">
			<input type="checkbox" id="ctc_opt_preview" style="margin: 0 5px;">
			<label for="ctc_opt_preview">${sanitizeRichHtml( decodeHTML( optIn ) )}</label>
		</div>
	</div>`;
};

/**
 * Greetings CTA — style 1 (theme button) — port of g-cta-1.php.
 *
 * @param {Object} ctx Preview context.
 * @param {string} gCta Greetings call to action text.
 * @returns {string}
 */
export const gCta1 = ( ctx, gCta ) => {
	const textColor = ctx.value( 'ht_ctc_s1', 's1_text_color' ) || '';
	const bgColor = ctx.value( 'ht_ctc_s1', 's1_bg_color' ) || '';
	const addIcon = ctx.value( 'ht_ctc_s1', 's1_add_icon' ) || '';
	let iconColor = ctx.value( 'ht_ctc_s1', 's1_icon_color' ) || '';
	let iconSize = ctx.value( 'ht_ctc_s1', 's1_icon_size' ) || '';

	if ( iconSize === '' ) { iconSize = '15'; }
	if ( iconColor === '' ) { iconColor = '#ffffff'; }

	let css = 'padding:9px;width:100%;cursor:pointer; display:flex; align-items:center; justify-content:center;';
	css += ( textColor !== '' ) ? `color:${textColor};` : '';
	css += ( bgColor !== '' ) ? `background-color:${bgColor};` : '';

	let iconHtml = '';
	if ( addIcon !== '' ) {
		iconHtml = singleColorIcon( {
			color: iconColor,
			iconSize,
			type: 'greetings_chat',
			svgCss: 'margin-right:6px;',
		} );
	}

	const escapedCss = escapeAttr( css );
	const escapedCta = escapeHTML( gCta );
	return `<button style="${escapedCss}" class="g_s1_cta_btn ctc_cta">` +
		`${iconHtml}${escapedCta}</button>`;
};

/**
 * Greetings CTA — style 7 Extend (green pill) — port of g-cta-7_1.php.
 *
 * @param {Object} ctx Preview context.
 * @param {string} gCta Greetings call to action text.
 * @returns {string}
 */
export const gCta71 = ( ctx, gCta ) => {
	const iconSize = ctx.value( 'ht_ctc_s7_1', 's7_icon_size' ) || '';
	const iconColor = ctx.value( 'ht_ctc_s7_1', 's7_icon_color' ) || '';
	const iconColorHover = ctx.value( 'ht_ctc_s7_1', 's7_icon_color_hover' ) || '';
	const bgColor = ctx.value( 'ht_ctc_s7_1', 's7_bgcolor' ) || '';
	const bgColorHover = ctx.value( 'ht_ctc_s7_1', 's7_bgcolor_hover' ) || '';

	let ctaFontSize = ctx.value( 'ht_ctc_s7_1', 'cta_font_size' ) || '';
	ctaFontSize = ( ctaFontSize !== '' ) ? `font-size: ${ctaFontSize}` : '';

	// if side_2 is right then cta is left
	const ctaOrder = ( ctx.side2 === 'right' ) ? '0' : '1';

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';

	let n1Styles = `display:flex;justify-content:center;align-items:center;${rtlCss} `;
	n1Styles += `padding:5px; background-color:${bgColor};border-radius:25px; cursor: pointer;`;

	let ctaCss = `${ctaFontSize}; `;
	ctaCss += `padding:1px 0px; color:${iconColor}; border-radius:10px; `;
	ctaCss += `margin:0 10px; order:${ctaOrder}; `;

	const bgHoverVal = escapeCssValue( bgColorHover );
	const iconHoverVal = escapeCssValue( iconColorHover );
	const hoverStyles = [
		`.ht-ctc .g_ctc_s_7_1:hover{background-color:${bgHoverVal} !important;}`,
		`.ht-ctc .g_ctc_s_7_1:hover .g_ctc_s_7_1_cta{color:${iconHoverVal} !important;}`,
		`.ht-ctc .g_ctc_s_7_1:hover svg g path{fill:${iconHoverVal} !important;}`,
	].join( '' );

	const svgCss = `pointer-events:none; display:block; height:${iconSize}; width:${iconSize};`;

	const icon = singleColorIcon( {
		color: iconColor,
		iconSize,
		type: 'greetings_chat',
		svgCss,
	} );

	return `<style id="ht-ctc-s7_1">${hoverStyles}</style>
	<div class="g_ctc_s_7_1" style="${escapeAttr( n1Styles )}">
		<p class="g_ctc_s_7_1_cta ctc_cta ht-ctc-cta " style="${escapeAttr( ctaCss )}">
			${escapeHTML( gCta )}
		</p>
		<div class="g_ctc_s_7_icon_padding" style="">${icon}</div>
	</div>`;
};

/**
 * Resolve the greetings CTA block by style id.
 *
 * @param {string} ctaStyle '1' | '7_1'
 * @param {Object} ctx Preview context.
 * @param {string} gCta Greetings call to action text.
 * @returns {string}
 */
export const gCtaBlock = ( ctaStyle, ctx, gCta ) => {
	return ( ctaStyle === '1' ) ? gCta1( ctx, gCta ) : gCta71( ctx, gCta );
};

// Rich-text fields (richContent/optInBlock) are allowlist-sanitized via
// ./sanitize.js because live unsaved editor values bypass the server-side
// wp_kses_post. Known accepted preview limitations: PHP shortcodes,
// translations, frontend filters, and page-level {title}/{url} substitution
// are not simulated.
