/**
 * Localise every element id inside a rendered template (and the references to
 * it) so SVG `url(#id)` / `href="#id"` lookups stay self-contained — needed
 * when the same icon/gradient is rendered into multiple style-grid cells (and
 * the floating preview), which would otherwise share ids and resolve to the
 * wrong element in some engines.
 *
 * @param {HTMLElement} root   Container holding one rendered template.
 * @param {string}      suffix Unique suffix for this swap (e.g. '-cg3').
 */
export const uniquifySvgIds = ( root, suffix ) => {
	const owners = root.querySelectorAll( '[id]' );
	if ( ! owners.length ) { return; }

	const idMap = new Map();

	owners.forEach( ( node ) => {
		const oldId = node.getAttribute( 'id' );
		if ( oldId && ! idMap.has( oldId ) ) {
			idMap.set( oldId, oldId + suffix );
			node.setAttribute( 'id', oldId + suffix );
		}
	} );

	// Update references. `#id` appears as url(#id) (any attr / inline style) or
	// as an href value. The lookahead keeps `#htwaicona-chat` from also matching
	// inside `#htwaicona-chat-s4`.
	root.querySelectorAll( '*' )
		.forEach( ( node ) => {
			Array.from( node.attributes )
				.forEach( ( attr ) => {
					if ( ! attr.value.includes( '#' ) ) { return; }
					let next = attr.value;
					idMap.forEach( ( newId, oldId ) => {
						const escaped = oldId.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
						next = next.replace(
							// eslint-disable-next-line security/detect-non-literal-regexp -- pattern built from escaped element ids in the rendered template
							new RegExp( `#${escaped}(?=[)\\s"']|$)`, 'g' ),
							`#${newId}`,
						);
					} );
					if ( next !== attr.value ) { node.setAttribute( attr.name, next ); }
				} );
		} );
};
