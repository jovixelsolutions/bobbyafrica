/**
 * Shared call-to-action block logic for icon styles (2, 3, 3_1).
 *
 * Mirrors the identical PHP blocks in style-2.php / style-3.php /
 * style-3_1.php: cta_type hover|show|hide → css, class, title.
 */
import { escapeAttr } from '../core/Utils.js';

/**
 * @param {Object} args - { ctaType, textColor, bgColor, fontSize, side2, cta, greetingsOpen }
 * @returns {Object} { ctaCss, ctaClass, titleAttr }
 */
export const iconCtaBlock = ( args ) => {
	const textColor = ( args.textColor !== '' ) ? `color: ${args.textColor}` : '';
	const bgColor = ( args.bgColor !== '' ) ? `background-color: ${args.bgColor}` : '';
	const fontSize = ( args.fontSize !== '' ) ? `font-size: ${args.fontSize}` : '';

	// if side_2 is right then cta is left
	const ctaOrder = ( args.side2 === 'right' ) ? '0' : '1';

	let ctaCss = 'padding: 0px 16px; line-height: 1.6; ';
	ctaCss += `${fontSize}; ${bgColor}; ${textColor}; `;
	ctaCss += 'border-radius:10px; margin:0 10px; ';
	let ctaClass = 'ht-ctc-cta ';
	let titleAttr = '';

	if ( args.ctaType === 'hover' ) {
		ctaCss += ` display: none; order: ${ctaOrder}; `;
		ctaClass += ' ht-ctc-cta-hover ';
	} else if ( args.ctaType === 'show' ) {
		ctaCss += `order: ${ctaOrder}; `;
	} else if ( args.ctaType === 'hide' ) {
		ctaCss += ' display: none; ';
		titleAttr = `title="${escapeAttr( args.cta )}"`;
	}

	// Frontend Behavior: Aggressively hide CTA if greetings box is open
	if ( args.greetingsOpen ) {
		ctaCss += ' display: none !important; ';

		// Remove hover class to prevent CSS from revealing it
		ctaClass = ctaClass.replace( 'ht-ctc-cta-hover', '' );
	}

	return { ctaCss, ctaClass, titleAttr };
};

/**
 * Hover rule that reveals the hover-type cta in the preview.
 * (On the frontend this reveal is JS-driven; in the preview a pure
 * CSS :hover keeps templates self-contained.)
 *
 * @param {string} styleClass Widget root class (e.g. 'ctc_s_2')
 * @param {boolean} greetingsOpen Whether the greetings box is open
 * @returns {string} CSS rule
 */
export const hoverRevealRule = ( styleClass, greetingsOpen = false ) => {
	if ( greetingsOpen ) { return ''; }
	return `.ht-ctc .${styleClass}:hover .ht-ctc-cta-hover{display:block !important;}`;
};
