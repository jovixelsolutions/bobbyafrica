import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { logoIcon } from '../icons.js';

export default function renderStyle4 ( ctx ) {
	const opt = ( key, fallback ) => {
		const value = ctx.value( 'ht_ctc_s4', key );
		return ( value === '' || value === undefined || value === null ) ? fallback : value;
	};

	const textColor = opt( 's4_text_color', '#000000' );
	const bgColor = opt( 's4_bg_color', '#e4e4e4' );
	const imgUrl = ctx.value( 'ht_ctc_s4', 's4_img_url' ) || '';
	const imgPosition = opt( 's4_img_position', 'left' );
	const imgSize = opt( 's4_img_size', '32px' );

	const callToAction = ctx.cta || 'WhatsApp us';

	let margin, order;
	if ( imgPosition === 'left' ) {
		margin = '0 8px 0 -12px;';
		order = '0';
	} else {
		margin = '0 -12px 0 8px;';
		order = '1';
	}

	const rtlCss = ctx.isRtl ? 'flex-direction:row-reverse;' : '';

	const chipCss = [
		'display:flex;',
		'justify-content: center;',
		'align-items: center;',
		`background-color:${bgColor};`,
		`color:${textColor};`,
		'padding:0 12px;',
		'border-radius:25px;',
		'font-size:13px;',
		'line-height:32px;',
		rtlCss,
	].join( ' ' );
	const chipSvgCss = `margin:${margin}order:${order};`;
	const chipImgCss = `margin:${margin}order:${order};height:${imgSize};` +
		`width:${imgSize};border-radius:50%`;
	const svgCss = `pointer-events:none; display: block; height:${imgSize}; width:${imgSize};`;

	let img;
	if ( imgUrl === '' ) {
		img = `<span class="s4_img" style="${escapeAttr( chipSvgCss )}">${logoIcon( imgSize, 'chat-s4', svgCss )}</span>`;
	} else {
		img = `<img class="s4_img" style="${escapeAttr( chipImgCss )}" ` +
			`src="${escapeAttr( imgUrl )}" alt="${escapeAttr( callToAction )}">`;
	}

	return `<div class="ctc_chip ctc_s_4 ctc_nb" style="${escapeAttr( chipCss )}" ` +
		`data-nb_top="-10px" data-nb_right="-10px">
		${img}
		<span class="ctc_cta">${escapeHTML( callToAction )}</span>
	</div>`;
}
