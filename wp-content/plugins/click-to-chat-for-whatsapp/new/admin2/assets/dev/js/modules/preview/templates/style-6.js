/**
 * Preview Template — Style 6 (plain text link)
 *
 * JS port of new/inc/styles/style-6.php. The PHP template uses inline
 * onmouseover/onmouseout handlers; the preview replicates the same hover
 * effect with a scoped :hover rule instead (visually identical).
 */
import { escapeAttr, escapeHTML } from '../../core/Utils.js';
import { escapeCssValue } from '../css.js';

export default function renderStyle6 ( ctx ) {
	const txtColor = ctx.value( 'ht_ctc_s6', 's6_txt_color' ) || '';
	const txtColorOnHover = ctx.value( 'ht_ctc_s6', 's6_txt_color_on_hover' ) || '';
	const txtDecoration = ctx.value( 'ht_ctc_s6', 's6_txt_decoration' ) ?? 'none';
	const txtDecorationOnHover = ctx.value( 'ht_ctc_s6', 's6_txt_decoration_on_hover' ) ?? 'underline';

	const callToAction = ctx.cta || 'WhatsApp us';

	let styles = '';
	if ( txtColor !== '' ) { styles += `color: ${txtColor}; `; }
	if ( txtDecoration !== '' ) { styles += `text-decoration: ${txtDecoration}; `; }

	let hoverStyles = '';
	if ( txtColorOnHover !== '' ) { hoverStyles += `color: ${escapeCssValue( txtColorOnHover )} !important; `; }
	if ( txtDecorationOnHover !== '' ) { hoverStyles += `text-decoration: ${escapeCssValue( txtDecorationOnHover )} !important; `; }

	const hoverRule = ( hoverStyles !== '' ) ? `<style>.ht-ctc .ctc_s_6:hover{${hoverStyles}}</style>` : '';

	return `${hoverRule}
	<a class="ctc_s_6 ctc_cta" style="${escapeAttr( styles )}">${escapeHTML( callToAction )}</a>`;
}
