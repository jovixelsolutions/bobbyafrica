/**
 * Preview Template — Style 8 (button with icon)
 *
 * JS port of new/inc/styles/style-8.php. Class names, inline CSS, and option
 * defaults must stay in sync with the PHP template.
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { singleColorIcon } from '../icons.js';
import { escapeCssValue } from '../css.js';

export default function renderStyle8 ( ctx ) {
	const opt = ( key, fallback ) => {
		const value = ctx.value( 'ht_ctc_s8', key );
		return ( value === '' || value === undefined || value === null ) ? fallback : value;
	};

	const iconColor = opt( 's8_icon_color', '#ffffff' );
	const iconColorOnHover = opt( 's8_icon_color_on_hover', '#ffffff' );
	const txtColor = opt( 's8_txt_color', '#ffffff' );
	const txtColorOnHover = opt( 's8_txt_color_on_hover', '#ffffff' );
	const bgColor = opt( 's8_bg_color', '#26a69a' );
	const bgColorOnHover = opt( 's8_bg_color_on_hover', '#26a69a' );

	const iconPosition = opt( 's8_icon_position', 'left' );
	const btnSize = opt( 's8_btn_size', 'btn' );
	const iconSize = opt( 's8_icon_size', '16px' );

	const textSize = ctx.value( 'ht_ctc_s8', 's8_text_size' ) ?? '16px';
	const textSizeCss = ( textSize === '' ) ? '' : `font-size: ${textSize};`;

	const callToAction = ctx.cta || 'WhatsApp us';

	const height = ( btnSize === 'btn-large' ) ? '54px' : '36px';

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';

	const iconCss = ( iconPosition === 'right' ) ? 'order:1;margin-left:15px;' : 'order:0;margin-right:15px;';

	const textCss = `height: 100%; color:${txtColor}; ${textSizeCss} `;

	const mainSpanCss = [
		'display: flex;',
		rtlCss,
		'padding: 0 2rem;',
		'letter-spacing: .5px;',
		'transition: .2s ease-out;',
		'text-align:center;',
		'justify-content: center;',
		'align-items: center;',
		'border-radius:4px;',
		`height:${height};`,
		`line-height:${height};`,
		'vertical-align:middle;',
		'box-shadow:0 2px 2px 0 rgba(0,0,0,.14), ' +
			'0 1px 5px 0 rgba(0,0,0,.12), ' +
			'0 3px 1px -2px rgba(0,0,0,.2);',
		'box-sizing:inherit;',
		`background-color:${bgColor};`,
		'overflow:hidden;',
	].join( ' ' );

	const iconCssVal = escapeCssValue( iconCss );
	const iconColorOnHoverVal = escapeCssValue( iconColorOnHover );
	const txtColorOnHoverVal = escapeCssValue( txtColorOnHover );
	const bgColorOnHoverVal = escapeCssValue( bgColorOnHover );

	const hoverStyles = [
		`.ht-ctc-style-8 .s_8 .s_8_icon{${iconCssVal};}`,
		'.ht-ctc .ht-ctc-style-8:hover .s_8 svg g path{' +
			`fill:${iconColorOnHoverVal} !important;}`,
		'.ht-ctc .ht-ctc-style-8:hover .s_8 .ht-ctc-s8-text{' +
			`color:${txtColorOnHoverVal} !important;}`,
		'.ht-ctc .ht-ctc-style-8:hover .s_8{',
		'box-shadow: 0 3px 3px 0 rgba(7,6,6,.14), ',
		'0 1px 7px 0 rgba(0,0,0,.12), ',
		'0 3px 1px -1px rgba(0,0,0,.2) !important; ',
		'transition: .2s ease-out !important; ',
		`background-color:${bgColorOnHoverVal} !important; }`,
	].join( '' );

	let icon = '';
	if ( iconPosition !== 'hide' ) {
		icon = singleColorIcon( {
			color: iconColor,
			iconSize,
			type: 'chat',
			svgCss: 'display:block;',
		} );
	}

	return `<style id="ht-ctc-s8">${hoverStyles}</style>
	<div class="ht-ctc-style-8 ctc_s_8">
		<span class="s_8" style="${escapeAttr( mainSpanCss )}">
		<span class="s_8_icon">${icon}</span>
		<span class="ht-ctc-s8-text s8_span ctc_cta" style="${escapeAttr( textCss )}">
			${escapeHTML( callToAction )}
		</span>
		</span>
	</div>`;
}
