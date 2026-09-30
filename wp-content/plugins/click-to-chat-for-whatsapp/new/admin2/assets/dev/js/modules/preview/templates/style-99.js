/**
 * Preview Template — Style 99 (own image / GIF)
 *
 * JS port of new/inc/styles/style-99.php. Note: the desktop image option key
 * is 's99_dekstop_img_url' (typo preserved from the DB schema).
 */
import { escapeAttr } from '../../core/Utils.js';

export default function renderStyle99 ( ctx ) {
	const opt = ( key, fallback ) => {
		const value = ctx.value( 'ht_ctc_s99', key );
		return ( value === '' || value === undefined || value === null ) ? fallback : value;
	};

	const callToAction = ctx.cta || '';

	let img;
	let imgCss = '';

	if ( ctx.device === 'mobile' ) {
		img = ctx.value( 'ht_ctc_s99', 's99_mobile_img_url' ) || '';
		imgCss += `height: ${opt( 's99_mobile_img_height', '40px' )}; `;
		const width = opt( 's99_mobile_img_width', '40px' );
		if ( width !== '' ) { imgCss += `width: ${width}; `; }
	} else {
		img = ctx.value( 'ht_ctc_s99', 's99_dekstop_img_url' ) || '';
		imgCss += `height: ${opt( 's99_desktop_img_height', '50px' )}; `;
		const width = opt( 's99_desktop_img_width', '50px' );
		if ( width !== '' ) { imgCss += `width: ${width}; `; }
	}

	// fallback image
	if ( img === '' ) {
		img = `${ctx.pluginUrl}new/inc/assets/img/whatsapp-logo.svg`;
	}

	return `<img class="own-img ctc_s_99 ctc_cta" title="${escapeAttr( callToAction )}" ` +
		`src="${escapeAttr( img )}" style="${escapeAttr( imgCss )}" ` +
		`alt="${escapeAttr( callToAction )}">`;
}
