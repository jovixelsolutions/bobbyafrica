import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { logoIcon } from '../icons.js';
import { iconCtaBlock, hoverRevealRule } from '../cta.js';

export default function renderStyle3 ( ctx ) {
	let imgSize = ctx.value( 'ht_ctc_s3', 's3_img_size' ) || '';
	if ( imgSize === '' ) { imgSize = '50px'; }

	const ctaType = ctx.value( 'ht_ctc_s3', 'cta_type' ) || 'hover';
	const textColor = ctx.value( 'ht_ctc_s3', 'cta_textcolor' ) || '';
	const bgColor = ctx.value( 'ht_ctc_s3', 'cta_bgcolor' ) ?? '#ffffff';
	const fontSize = ctx.value( 'ht_ctc_s3', 'cta_font_size' ) || '';

	const callToAction = ctx.cta || 'WhatsApp us';

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';
	const css = `display:flex;justify-content:center;align-items:center;${rtlCss} `;

	const { ctaCss, ctaClass, titleAttr } = iconCtaBlock( {
		ctaType,
		textColor,
		bgColor: bgColor === '' ? '#ffffff' : bgColor,
		fontSize,
		side2: ctx.side2,
		cta: callToAction,
	} );

	const svgCss = `pointer-events:none; display:block; height:${imgSize}; width:${imgSize};`;

	return `<style>${hoverRevealRule( 'ctc_s_3' )}</style>
	<div ${titleAttr} style="${escapeAttr( css )}" ` +
		`class="ctc_s_3 ctc_nb" data-nb_top="-5px" data-nb_right="-5px">
		<p class="ctc_cta ctc_cta_stick ${escapeAttr( ctaClass )}" style="${escapeAttr( ctaCss )}">
			${escapeHTML( callToAction )}
		</p>
		${logoIcon( imgSize, 'chat', svgCss )}
	</div>`;
}
