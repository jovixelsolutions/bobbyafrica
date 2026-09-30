import { applyConditionalAttributes, safeUrl } from '../../core/Utils.js';

/**
 * field_type: block_feature_box
 *
 * Creates a Feature Box component (used for Greetings actions and Shortcodes).
 */
export const createBlockFeatureBox = ( field ) => {
	const el = document.createElement( 'div' );
	el.className = 'ctc-feature-box ' + ( field.class_pr || '' );

	// Create header
	const header = document.createElement( 'div' );
	header.className = 'ctc-feature-header';

	const titleGroup = document.createElement( 'div' );
	titleGroup.className = 'ctc-feature-title-group';

	if ( field.icon ) {
		const iconSpan = document.createElement( 'span' );
		iconSpan.className = 'ctc-feature-icon';
		if ( field.icon.startsWith( 'dashicons' ) ) {
			// eslint-disable-next-line no-unsanitized/property -- safe HTML from PHP configuration
			iconSpan.innerHTML = `<span class="dashicons ${ field.icon }"></span>`;
		} else {
			// eslint-disable-next-line no-unsanitized/property -- safe HTML from PHP configuration
			iconSpan.innerHTML = '<svg class="ctc-icon" aria-hidden="true">' +
				`<use href="#ctc-icon-${ field.icon }"></use></svg>`;
		}
		titleGroup.appendChild( iconSpan );
	}

	if ( field.label ) {
		const labelSpan = document.createElement( 'span' );
		labelSpan.className = 'ctc-feature-label';
		labelSpan.textContent = field.label;
		titleGroup.appendChild( labelSpan );
	}

	header.appendChild( titleGroup );

	// Badge stays next to the title (left).
	if ( field.badge ) {
		const badgeSpan = document.createElement( 'span' );
		badgeSpan.className = 'ctc-feature-badge ' + ( field.badge_class || '' );
		badgeSpan.textContent = field.badge;
		header.appendChild( badgeSpan );
	}

	// Optional header link (e.g. "more info"), rendered top-right so the header
	// stays balanced — mirrors where the badge sits on greetings feature boxes.
	if ( field.link && field.link.url ) {
		const linkEl = document.createElement( 'a' );
		linkEl.className = 'external-link ctc-feature-link';
		linkEl.href = safeUrl( field.link.url );
		linkEl.target = '_blank';
		linkEl.rel = 'noopener noreferrer';
		// eslint-disable-next-line no-unsanitized/property -- static label + icon glyph
		linkEl.innerHTML = `${ field.link.label || 'more info' } ` +
			`<span class="${ field.link.icon || 'dashicons dashicons-external' }"></span>`;
		header.appendChild( linkEl );
	}

	el.appendChild( header );

	// Create description
	if ( field.content || field.description ) {
		const descDiv = document.createElement( 'div' );
		descDiv.className = 'ctc-feature-desc';
		// eslint-disable-next-line no-unsanitized/property -- Safe HTML from PHP configuration / static description
		descDiv.innerHTML = field.content || field.description || '';
		el.appendChild( descDiv );
	}

	applyConditionalAttributes( el, field );
	return el;
};
