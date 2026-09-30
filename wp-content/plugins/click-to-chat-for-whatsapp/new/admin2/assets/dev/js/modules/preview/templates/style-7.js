/**
 * Preview Template — Style 7 (icon with customizable padding)
 *
 * JS port of new/inc/styles/style-7.php. Class names, inline CSS, and option
 * defaults must stay in sync with the PHP template.
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { singleColorIcon } from '../icons.js';
import { escapeCssValue } from '../css.js';

export default function renderStyle7 ( ctx ) {
	const opt = ( key, fallback ) => {
		const value = ctx.value( 'ht_ctc_s7', key );
		return ( value === '' || value === undefined || value === null ) ? fallback : value;
	};

	const iconSize = opt( 's7_icon_size', '20px' );
	const iconColor = opt( 's7_icon_color', '#ffffff' );
	const iconColorHover = opt( 's7_icon_color_hover', '#f4f4f4' );
	const borderSize = opt( 's7_border_size', '12px' );
	const borderColor = opt( 's7_border_color', '#25D366' );
	const borderColorHover = opt( 's7_border_color_hover', '#25d366' );
	const borderRadius = opt( 's7_border_radius', '50%' );

	const ctaType = opt( 'cta_type', 'hover' );
	const ctaTextColor = ctx.value( 'ht_ctc_s7', 'cta_textcolor' ) || '';
	const ctaBgColor = opt( 'cta_bgcolor', '#ffffff' );
	let ctaFontSize = ctx.value( 'ht_ctc_s7', 'cta_font_size' ) || '';
	ctaFontSize = ( ctaFontSize !== '' ) ? `font-size: ${ctaFontSize};` : '';

	const callToAction = ctx.cta || 'WhatsApp us';

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';

	const n1Styles = `display:flex;justify-content:center;align-items:center;${rtlCss} `;
	const iconCss = `font-size: ${iconSize}; color: ${iconColor}; ` +
		`padding: ${borderSize}; background-color: ${borderColor}; ` +
		`border-radius: ${borderRadius};`;

	// Call to action - order: if side_2 is right then cta is left
	const ctaOrder = ( ctx.side2 === 'right' ) ? '0' : '1';

	let ctaCss = `padding: 0px 16px; ${ctaFontSize} color: ${ctaTextColor}; ` +
		`background-color: ${ctaBgColor}; border-radius:10px; margin:0 10px; `;
	let ctaClass = 'ht-ctc-cta ';
	let title = '';
	if ( ctaType === 'hover' ) {
		ctaCss += ` display: none; order: ${ctaOrder}; `;
		ctaClass += ' ht-ctc-cta-hover ';
	} else if ( ctaType === 'show' ) {
		ctaCss += `order: ${ctaOrder}; `;
	} else if ( ctaType === 'hide' ) {
		ctaCss += ' display: none; ';
		title = `title="${escapeAttr( callToAction )}"`;
	}

	const svgCss = `pointer-events:none; display:block; height:${iconSize}; width:${iconSize};`;

	// Hover styles — same selectors as the PHP template; ':hover' shows the
	// hover-type cta in the preview (frontend behavior is JS-driven there).
	const bgHoverVal = escapeCssValue( borderColorHover );
	const iconHoverVal = escapeCssValue( iconColorHover );
	const hoverStyles = [
		'.ht-ctc .ctc_s_7:hover .ctc_s_7_icon_padding, ',
		`.ht-ctc .ctc_s_7:hover .ctc_cta_stick{background-color:${bgHoverVal} !important;}`,
		`.ht-ctc .ctc_s_7:hover svg g path{fill:${iconHoverVal} !important;}`,
		'.ht-ctc .ctc_s_7:hover .ht-ctc-cta-hover{display:block !important;}',
	].join( '' );

	const icon = singleColorIcon( {
		color: iconColor,
		iconSize,
		type: 'chat',
		svgCss,
	} );

	return `<style id="ht-ctc-s7">${hoverStyles}</style>
	<div ${title} class="ctc_s_7 ctc_nb" style="${escapeAttr( n1Styles )}" ` +
		`data-nb_top="-7.8px" data-nb_right="-7.8px">
		<p class="ctc_s_7_cta ctc_cta ctc_cta_stick ${escapeAttr( ctaClass )}" ` +
			`style="${escapeAttr( ctaCss )}">
			${escapeHTML( callToAction )}
		</p>
		<div class="ctc_s_7_icon_padding" style="${escapeAttr( iconCss )}">${icon}</div>
	</div>`;
}
