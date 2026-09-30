import { getSafeProperty, escapeAttr } from '../../core/Utils.js';

export const createWebhookParamRow = ( value, index, dbRow = 'ht_ctc_othersettings' ) => {
	const row = document.createElement( 'div' );
	row.className = 'ctc_an_param hook_v_param row ctc-item';
	row.style.cssText = 'margin-bottom:5px; display:flex; gap:5px; justify-content:center;';

	const inputId = `hook_v_param_${index}`;

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values using Utils.escapeHTML
	row.innerHTML = `
        <div class="input-field">
			<label for="${escapeAttr( inputId )}">Value${index + 1}</label>
            <input 
                name="${escapeAttr( dbRow )}[hook_v][]"
                id="${escapeAttr( inputId )}"
                type="text" 
                class="ht_ctc_hook_v_param_value input-margin"
            >
        </div>
        <div class="input-field">
            <span 
                tabindex="0" 
                role="button" 
                style="margin-left:auto;" 
                class="an_param_remove ctc-remove-button dashicons dashicons-no-alt" 
                title="Remove Value"></span>
        </div>
    `;

	const hookInput = row.querySelector( 'input' );
	if ( hookInput ) {
		hookInput.value = value;
	}

	return row;
};

export const createWebhooksParametersSection = ( field, context = document, config ) => {
	const placeholder = document.createElement( 'div' );
	placeholder.style.display = 'none';

	const optionGroup = 'ht_ctc_othersettings';

	// const options = config.initialSettings[ optionGroup ] || {};
	const options = getSafeProperty( config.initialSettings, optionGroup ) || {};
	const dbRow = optionGroup;

	let hookV = options.hook_v;

	if ( hookV && typeof hookV === 'object' ) {
		if ( Array.isArray( hookV ) ) {
			hookV = hookV.filter( val => val );
		} else {
			const values = [];
			Object.keys( hookV )
				.sort( ( prev, next ) => parseInt( prev ) - parseInt( next ) )
				.forEach( key => {
					// const val = hookV[ key ];
					const val = getSafeProperty( hookV, key );
					if ( val ) { values.push( val ); }
				} );
			hookV = values;
		}
	} else {
		hookV = [];
	}

	const fragment = document.createDocumentFragment();

	if ( hookV.length > 0 ) {
		hookV.forEach( ( value, index ) => {
			if ( value ) {
				const row = createWebhookParamRow( value, index, dbRow );
				fragment.appendChild( row );
			}
		} );
	}

	setTimeout( () => {
		const container = document.querySelector( '.ctc_hook_v_params' );
		if ( container ) {
			container.appendChild( fragment );
		}
	}, 0 );

	return placeholder;
};

/**
 * Adds a new Webhook value row.
 * Receives { container } from RepeaterManager — appends directly to Items_Container.
 * Index derived from live row count — no hidden order input needed (matches old admin pattern).
 * Falls back to global document.querySelector for backward compatibility.
 */
export const addWebhookParam = ( { container } = {} ) => {
	const target = container;
	if ( ! target ) { return; }
	const index = target.querySelectorAll( '.ctc-item' ).length;
	const row = createWebhookParamRow( '', index, 'ht_ctc_othersettings' );
	target.appendChild( row );
};
