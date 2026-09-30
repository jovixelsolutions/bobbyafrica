/**
 * DOM-based HTML sanitizer for the live-preview rich-text fields.
 *
 * Saved greetings content is wp_kses_post-sanitized server-side, but the
 * preview also renders LIVE unsaved editor values, so everything that ends up
 * in the preview's innerHTML must pass through this allowlist first. Scope is
 * intentionally narrower than wp_kses_post: only what the greetings editor
 * legitimately produces.
 */

const ALLOWED_TAGS = new Set( [
	'a',
	'b',
	'strong',
	'em',
	'i',
	'u',
	's',
	'br',
	'p',
	'span',
	'div',
	'ul',
	'ol',
	'li',
	'blockquote',
	'code',
] );

// Removed together with their contents (unwrapping them would leak payloads
// like <style> text or nested <svg> markup as live nodes).
const DROP_WITH_CONTENT = new Set( [
	'script',
	'style',
	'iframe',
	'object',
	'embed',
	'svg',
	'math',
	'form',
	'input',
	'textarea',
	'select',
	'button',
	'template',
	'link',
	'meta',
	'base',
] );

const GLOBAL_ATTRS = new Set( [ 'class', 'title', 'aria-label', 'style' ] );
const LINK_ATTRS = new Set( [ 'href', 'target', 'rel' ] );

// Inline-style properties the editor's formatting can produce. Values are
// validated separately; url()/expression()/custom properties never survive.
const ALLOWED_CSS_PROPS = new Set( [
	'color',
	'background-color',
	'font-size',
	'font-weight',
	'font-style',
	'font-family',
	'text-align',
	'text-decoration',
	'line-height',
	'letter-spacing',
	'margin',
	'margin-top',
	'margin-right',
	'margin-bottom',
	'margin-left',
	'padding',
	'padding-top',
	'padding-right',
	'padding-bottom',
	'padding-left',
] );

// Conservative CSS value shape: words, numbers, units, hex colors, %,
// commas, dots, spaces, quotes (font-family) and rgb()/hsl() style parens.
const CSS_VALUE_RE = /^[\w\s#%(),.'"!/-]*$/;
const CSS_VALUE_BLOCK_RE = /url\s*\(|expression|@import|behavior|javascript/i;

const SAFE_HREF_RE = /^(https?:|mailto:|tel:|\/(?!\/)|#)/i;

const filterStyle = ( styleValue ) => {
	const kept = [];
	for ( const decl of styleValue.split( ';' ) ) {
		const idx = decl.indexOf( ':' );
		if ( idx === -1 ) { continue; }
		const prop = decl.slice( 0, idx )
			.trim()
			.toLowerCase();
		const value = decl.slice( idx + 1 )
			.trim();
		if ( ! ALLOWED_CSS_PROPS.has( prop ) ) { continue; }
		if ( ! CSS_VALUE_RE.test( value ) || CSS_VALUE_BLOCK_RE.test( value ) ) { continue; }
		kept.push( `${prop}:${value}` );
	}
	return kept.join( ';' );
};

const sanitizeElement = ( el ) => {
	const tag = el.tagName.toLowerCase();
	const allowed = ( tag === 'a' ) ? new Set( [ ...GLOBAL_ATTRS, ...LINK_ATTRS ] ) : GLOBAL_ATTRS;

	for ( const attr of [ ...el.attributes ] ) {
		const name = attr.name.toLowerCase();
		if ( ! allowed.has( name ) || ! ALLOWED_TAGS.has( tag ) ) {
			el.removeAttribute( attr.name );
			continue;
		}
		if ( name === 'style' ) {
			const filtered = filterStyle( attr.value );
			if ( filtered === '' ) {
				el.removeAttribute( attr.name );
			} else {
				el.setAttribute( 'style', filtered );
			}
		} else if ( name === 'href' && ! SAFE_HREF_RE.test( attr.value.trim() ) ) {
			el.removeAttribute( attr.name );
		} else if ( name === 'target' && attr.value !== '_blank' ) {
			el.removeAttribute( attr.name );
		}
	}

	if ( tag === 'a' && el.getAttribute( 'target' ) === '_blank' ) {
		el.setAttribute( 'rel', 'noopener noreferrer' );
	}
};

const walk = ( node ) => {
	for ( const child of [ ...node.childNodes ] ) {
		if ( child.nodeType === Node.ELEMENT_NODE ) {
			const tag = child.tagName.toLowerCase();
			if ( DROP_WITH_CONTENT.has( tag ) ) {
				child.remove();
				continue;
			}
			if ( ! ALLOWED_TAGS.has( tag ) ) {
				// Unknown-but-harmless tag: keep its (sanitized) children,
				// drop the wrapper — mirrors kses stripping unknown tags.
				walk( child );
				while ( child.firstChild ) {
					node.insertBefore( child.firstChild, child );
				}
				child.remove();
				continue;
			}
			sanitizeElement( child );
			walk( child );
		} else if ( child.nodeType !== Node.TEXT_NODE ) {
			// Comments, CDATA, processing instructions.
			child.remove();
		}
	}
};

/**
 * Sanitize an HTML fragment against the preview allowlist.
 *
 * @param {string} html Raw HTML (already entity-decoded / autop'd).
 * @returns {string} Safe HTML for innerHTML injection.
 */
export const sanitizeRichHtml = ( html ) => {
	if ( typeof html !== 'string' || html === '' ) { return ''; }
	const doc = new DOMParser()
		.parseFromString( html, 'text/html' );
	walk( doc.body );
	return doc.body.innerHTML;
};
