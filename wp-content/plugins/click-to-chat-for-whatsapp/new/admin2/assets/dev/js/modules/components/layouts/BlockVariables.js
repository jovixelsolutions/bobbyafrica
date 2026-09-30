import { applyConditionalAttributes, escapeHTML, copyToClipboard } from '../../core/Utils.js';

/**
 * Layout Component: Block Variables (field_type: block_variables)
 *
 * Renders template variables in a grid of click-to-copy tiles.
 *
 * Field Configuration:
 *   title         - Optional heading (default: 'Variables').
 *   badge         - Optional badge next to title (e.g. 'PRO').
 *   note          - Optional note line under the grid.
 *   pro           - When true, tiles default to PRO (crown-badged) styling.
 *   variables     - Object map of token => description (string or { desc, pro }).
 *                   e.g. { '{title}': 'Page title', '{url}': 'Page URL' }
 *   pro_variables - Optional object map of token => description for PRO-only tiles.
 *                   e.g. { '{time}': 'Click time' }
 */
export const createBlockVariables = ( field ) => {
	const el = document.createElement( 'div' );
	el.className = `block-variables ${field.class_pr || ''}`.trim();
	if ( field.id ) { el.id = field.id; }

	// Corner cues come from the shared sprite (HT_CTC_Icons) via <use>.
	const icon = ( name, cls ) => `<svg class="ctc-icon ${cls}" aria-hidden="true">` +
		`<use href="#ctc-icon-${name}"></use></svg>`;
	const copyIcon = icon( 'copy', 'variable-copy-icon' );
	const checkIcon = icon( 'check', 'variable-check-icon' );
	const crownIcon = icon( 'crown', 'variable-pro-icon' );

	const proDefault = !! field.pro;

	// Build a single tile button. `forcePro` (or a per-token flag) adds the
	// crown badge and PRO styling.
	const tile = ( token, value, forcePro ) => {
		const isObj = value && typeof value === 'object';
		const label = isObj ? ( value.desc || '' ) : value;
		const isPro = forcePro ||
			( ( isObj && value.pro !== undefined ) ? !! value.pro : proDefault );
		const safeToken = escapeHTML( token );
		const safeLabel = escapeHTML( label );

		const proClass = isPro ? ' is-pro' : '';
		const proCue = isPro ? crownIcon : '';
		const proLabel = isPro ? ` — ${safeLabel} (PRO feature)` : '';
		const tip = 'Click to copy';

		return `<button type="button" class="variable-tile${proClass}"` +
			` data-token="${safeToken}" data-tip="${tip}"` +
			( isPro ? ` aria-label="${safeToken}${proLabel}"` : '' ) + '>' +
			`<code>${safeToken}</code>` +
			`<span class="variable-desc">${safeLabel}</span>` +
			`<span class="variable-tile-cue">${proCue}${copyIcon}${checkIcon}</span>` +
			'</button>';
	};

	const variables = field.variables || {};
	const proVariables = field.pro_variables || {};

	const items = Object.entries( variables )
		.map( ( [ token, value ] ) => tile( token, value, false ) )
		.concat( Object.entries( proVariables )
			.map( ( [ token, value ] ) => tile( token, value, true ) ) )
		.join( '' );

	const title = field.title || 'Variables';
	const badge = field.badge ?
		`<span class="variables-badge">${escapeHTML( field.badge )}</span>` :
		'';
	const note = field.note ?
		`<p class="variables-note">${escapeHTML( field.note )}</p>` :
		'';

	applyConditionalAttributes( el, field );

	// eslint-disable-next-line no-unsanitized/property -- static wrapper; tokens and labels are escaped above
	el.innerHTML = `
        <span class="variables-header">
            <span class="variables-title">${escapeHTML( title )}</span>${badge}
        </span>
        <div class="variables-grid">${items}</div>
        ${note}
        <span class="screen-reader-text" aria-live="polite"></span>
    `;

	const liveRegion = el.querySelector( '[aria-live]' );

	// Click any tile to copy its token.
	el.addEventListener( 'click', ( event ) => {
		const tile = event.target.closest( '.variable-tile' );
		if ( ! tile || ! tile.dataset.token ) { return; }
		copyToClipboard( tile.dataset.token )
			.then( () => {
				const chip = tile.querySelector( 'code' );
				tile.classList.add( 'copied' );
				const original = chip.textContent;
				chip.textContent = 'Copied';
				liveRegion.textContent = `${original} copied to clipboard`;
				setTimeout( () => {
					chip.textContent = original;
					tile.classList.remove( 'copied' );
				}, 900 );
			} )
			.catch( () => {
				// Clipboard unavailable; tile stays as-is.
			} );
	} );

	return el;
};
