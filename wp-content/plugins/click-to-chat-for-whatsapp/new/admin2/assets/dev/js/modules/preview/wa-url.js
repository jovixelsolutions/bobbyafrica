/**
 * Build the WhatsApp link for the preview's click-to-navigate, mirroring the
 * desktop branch of app.dev.js (front end). Pure — no DOM.
 *
 * Desktop URL structure (ht_ctc_chat_options[url_structure_d]):
 *   'custom_url' + custom_url_d → the custom URL as-is
 *   'web'                       → https://web.whatsapp.com/send?phone=…&text=…
 *   'default' / anything else   → https://wa.me/<number>?text=…
 *
 * Mobile structures (whatsapp://, custom_url_m) are intentionally ignored —
 * the preview is desktop-only for now.
 *
 * @param {{ number?: string, preFilled?: string, urlStructure?: string, customUrl?: string }} args
 * @returns {string|null} the URL, or null when there's no number and no custom URL.
 */
export const buildWhatsAppUrl = ( { number, preFilled, urlStructure, customUrl } = {} ) => {
	// A custom URL wins and needs no number (e.g. a channel link).
	if ( urlStructure === 'custom_url' && customUrl ) { return String( customUrl ); }

	const digits = String( number || '' )
		.replace( /\D/g, '' );
	if ( digits === '' ) { return null; }

	// Mirror the front end's pre-filled encoding (% → %25, then encode).
	let text = String( preFilled || '' );
	try {
		text = encodeURIComponent( decodeURI( text.replaceAll( '%', '%25' ) ) );
	} catch {
		text = encodeURIComponent( text );
	}

	if ( urlStructure === 'web' ) {
		return `https://web.whatsapp.com/send?phone=${digits}&text=${text}`;
	}
	return `https://wa.me/${digits}?text=${text}`;
};
