/**
 * Preview Template — Style 7 Extend (pill with icon)
 *
 * JS port of new/inc/styles/style-7_1.php, following its admin-demo branch
 * ($is_ctc_admin = 'yes') since the preview runs on a plugin admin page.
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { singleColorIcon } from '../icons.js';
import { escapeCssValue } from '../css.js';

export default function renderStyle71 ( ctx ) {
	const opt = ( key, fallback ) => {
		const value = ctx.value( 'ht_ctc_s7_1', key );
		return ( value === '' || value === undefined || value === null ) ? fallback : value;
	};

	const iconSize = opt( 's7_icon_size', '20px' );
	const iconColor = opt( 's7_icon_color', '#ffffff' );
	const iconColorHover = opt( 's7_icon_color_hover', '#f4f4f4' );
	const bgColor = opt( 's7_bgcolor', '#25D366' );
	const bgColorHover = opt( 's7_bgcolor_hover', '#00d34d' );
	const borderSize = opt( 's7_border_size', '12px' );

	const ctaType = opt( 'cta_type', 'hover' );
	let ctaFontSize = ctx.value( 'ht_ctc_s7_1', 'cta_font_size' ) || '';
	ctaFontSize = ( ctaFontSize !== '' ) ? `font-size: ${ctaFontSize}; ` : '';

	const callToAction = ctx.cta || 'WhatsApp us';

	// Admin-demo branch: $is_ctc_admin === 'yes' (preview runs in wp-admin).
	let ctaOrder = '1';
	let hoverCtaPaddingCss = 'padding: 0px 21px 0px 0px;';
	if ( ctx.side2 === 'right' ) {
		ctaOrder = '0';
		hoverCtaPaddingCss = 'padding: 0px 0px 0px 21px;';
	}

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';

	let n1Styles = `display:flex;justify-content:center;align-items:center;${rtlCss} `;
	let ctaCss = `${ctaFontSize}`;
	let iconPaddingCss = '';
	let ctaClass = 'ht-ctc-cta ';
	let hoverStyles = '';

	if ( ctaType === 'hover' ) {
		n1Styles += `background-color: ${bgColor}; border-radius:25px;`;
		ctaCss += ` display: none; order: ${ctaOrder}; color: ${iconColor}; ` +
			`${hoverCtaPaddingCss}  margin:0 10px; ` +
			'border-radius: 25px; ';
		ctaClass += ' ht-ctc-cta-hover ctc_cta_stick ';
		iconPaddingCss += `padding: ${borderSize};background-color: ${bgColor};` +
			'border-radius: 25px; ';
		const bgHoverVal = escapeCssValue( bgColorHover );
		const iconHoverVal = escapeCssValue( iconColorHover );
		hoverStyles = [
			'.ht-ctc .ctc_s_7_1:hover .ctc_s_7_icon_padding, ',
			'.ht-ctc .ctc_s_7_1:hover{' +
				`background-color:${bgHoverVal} !important;border-radius: 25px;}`,
			'.ht-ctc .ctc_s_7_1:hover .ctc_s_7_1_cta{' +
				`color:${iconHoverVal} !important;}`,
			'.ht-ctc .ctc_s_7_1:hover svg g path{' +
				`fill:${iconHoverVal} !important;}`,
			'.ht-ctc .ctc_s_7_1:hover .ht-ctc-cta-hover{display:block !important;}',
		].join( '' );
	} else if ( ctaType === 'show' ) {
		n1Styles += `background-color:${bgColor};border-radius:25px;`;
		ctaCss += `color: ${iconColor}; border-radius:10px; margin:0 10px; order: ${ctaOrder}; `;
		iconPaddingCss += 'padding: 12px; border-radius:25px;';
		ctaCss += hoverCtaPaddingCss;
		const bgHoverVal = escapeCssValue( bgColorHover );
		const iconHoverVal = escapeCssValue( iconColorHover );
		hoverStyles = [
			`.ht-ctc .ctc_s_7_1:hover{background-color:${bgHoverVal} !important;}`,
			`.ht-ctc .ctc_s_7_1:hover .ctc_s_7_1_cta{color:${iconHoverVal} !important;}`,
			`.ht-ctc .ctc_s_7_1:hover svg g path{fill:${iconHoverVal} !important;}`,
		].join( '' );
	}

	const svgCss = `pointer-events:none; display:block; height:${iconSize}; width:${iconSize};`;

	const icon = singleColorIcon( {
		color: iconColor,
		iconSize,
		type: 'chat',
		svgCss,
	} );

	return `<style id="ht-ctc-s7_1">${hoverStyles}</style>
	<div class="ctc_s_7_1 ctc_nb" style="${escapeAttr( n1Styles )}" ` +
		`data-nb_top="-7.8px" data-nb_right="-7.8px">
		<p class="ctc_s_7_1_cta ctc_cta ${escapeAttr( ctaClass )}" style="${escapeAttr( ctaCss )}">
			${escapeHTML( callToAction )}
		</p>
		<div class="ctc_s_7_icon_padding" style="${escapeAttr( iconPaddingCss )}">${icon}</div>
	</div>`;
}
