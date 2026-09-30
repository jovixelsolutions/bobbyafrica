/**
 * Preview Template — Style 2
 *
 * Mirrors new/inc/styles/style-2.php.
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { squareIcon } from '../icons.js';
import { iconCtaBlock, hoverRevealRule } from '../cta.js';

export default function renderStyle2 ( ctx ) {
	let imgSize = ctx.value( 'ht_ctc_s2', 's2_img_size' ) || '';
	if ( imgSize === '' ) { imgSize = '50px'; }

	const ctaType = ctx.value( 'ht_ctc_s2', 'cta_type' ) || 'hover';
	const textColor = ctx.value( 'ht_ctc_s2', 'cta_textcolor' ) || '';
	const bgColor = ctx.value( 'ht_ctc_s2', 'cta_bgcolor' ) ?? '#ffffff';
	const fontSize = ctx.value( 'ht_ctc_s2', 'cta_font_size' ) || '';

	const callToAction = ctx.cta || 'WhatsApp us';

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';
	const css = `display: flex; justify-content: center; align-items: center; ${rtlCss} `;

	const { ctaCss, ctaClass, titleAttr } = iconCtaBlock( {
		ctaType,
		textColor,
		bgColor: bgColor === '' ? '#ffffff' : bgColor,
		fontSize,
		side2: ctx.side2,
		cta: callToAction,
	} );

	const svgCss = `pointer-events:none; display:block; height:${imgSize}; width:${imgSize};`;

	return `<style>${hoverRevealRule( 'ctc_s_2' )}</style>
	<div ${titleAttr} style="${escapeAttr( css )}" class="ctc_s_2">
		<p class="ctc_cta ctc_cta_stick ${escapeAttr( ctaClass )}" style="${escapeAttr( ctaCss )}">
			${escapeHTML( callToAction )}
		</p>
		${squareIcon( imgSize, 'chat', svgCss )}
	</div>`;
}
