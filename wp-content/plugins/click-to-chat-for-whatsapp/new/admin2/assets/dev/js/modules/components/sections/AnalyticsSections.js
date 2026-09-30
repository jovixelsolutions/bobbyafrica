import { getSafeProperty, escapeAttr } from '../../core/Utils.js';

/**
 * Generic Parameter Row Creator (Key/Value pair)
 *
 * Used by GA, Pixel, and GTM parameter sections.
 *
 * v4 structure: params are stored as a single keyed list of { key, value } rows
 * under `<paramsKey>`. The row's array key is the stored index (0,1,2.. for
 * defaults, a time-based value for user-added rows) and is written straight into
 * the input names: `<dbRow>[<paramsKey>][<index>][key|value]`. There is no longer
 * a tracker/reference input or an order counter.
 *
 * @param {Object} rowConfig
 * @param {string} rowConfig.paramsKey - e.g. 'g_an_params'
 * @param {(string|number)} rowConfig.index - stored row index (db key)
 * @param {Object} rowConfig.data - { key: '', value: '' }
 * @param {string} rowConfig.dbRow - e.g. 'ht_ctc_othersettings'
 * @param {string} rowConfig.rowClass - additional CSS class for the row, e.g. 'g_an_param'
 * @param {string} rowConfig.keyClass - class for the key input, e.g. 'ht_ctc_g_an_param_key'
 * @param {string} rowConfig.valClass - class for the value input, e.g. 'ht_ctc_g_an_param_value'
 * @returns {HTMLElement}
 */
const createParameterRow = ( { paramsKey, index, data = { key: '', value: '' }, dbRow, rowClass, keyClass, valClass } ) => {
	const row = document.createElement( 'div' );
	row.className = `ctc_an_param ${rowClass} row ctc-item`;
	row.style.cssText = 'margin-bottom:5px; display:flex; gap:5px; justify-content:center;';

	const idx = String( index );
	const keyId = `${paramsKey}_${idx}_key`;
	const valId = `${paramsKey}_${idx}_value`;

	// e.g. ht_ctc_othersettings[g_an_params][0]
	const baseName = `${escapeAttr( dbRow )}[${escapeAttr( paramsKey )}][${escapeAttr( idx )}]`;

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values using Utils.escapeHTML
	row.innerHTML = `
        <div class="input-field">
            <label for="${escapeAttr( keyId )}">Event Parameter</label>
            <input
                name="${baseName}[key]"
                id="${escapeAttr( keyId )}"
                type="text"
                class="${escapeAttr( keyClass )} input-margin"
            >
        </div>
        <div class="input-field">
            <label for="${escapeAttr( valId )}">Value</label>
            <input
                name="${baseName}[value]"
                id="${escapeAttr( valId )}"
                type="text"
                class="${escapeAttr( valClass )} input-margin"
            >
        </div>
        <div class="input-field">
            <span
                tabindex="0"
                role="button"
                style="margin-left:auto;"
                class="an_param_remove ctc-remove-button dashicons dashicons-no-alt"
                title="Remove Parameter"
            ></span>
        </div>
    `;

	const keyInput = row.querySelector( `#${keyId}` );
	const valInput = row.querySelector( `#${valId}` );
	if ( keyInput ) { keyInput.value = data.key; }
	if ( valInput ) { valInput.value = data.value; }

	return row;
};

/**
 * Normalize a stored params value into an ordered list of { index, data } entries,
 * preserving each row's stored key. Accepts both a sequential array (defaults) and
 * an object with mixed numeric / time-based keys (after a user adds rows).
 *
 * @param {*} params
 * @returns {Array<{index: (string|number), data: Object}>}
 */
const normalizeParamEntries = ( params ) => {
	const entries = [];

	if ( Array.isArray( params ) ) {
		// sequential array: the positional index IS the stored key (0,1,2..)
		params.forEach( ( data, index ) => {
			if ( data && typeof data === 'object' && data.key && data.value ) {
				entries.push( { index, data } );
			}
		} );
	} else if ( params && typeof params === 'object' ) {
		// Sort is required, not cosmetic: JS auto-orders only integer-index keys (< 2^32)
		// ascending. Time-based keys like 1781675442226 exceed that and are ordered by
		// insertion instead, so without this sort a row could render out of order.
		Object.keys( params )
			.sort( ( prev, next ) => parseInt( prev ) - parseInt( next ) )
			.forEach( ( key ) => {
				// const data = params[ key ];
				const data = getSafeProperty( params, key );
				if ( data && typeof data === 'object' && data.key && data.value ) {
					entries.push( { index: key, data } );
				}
			} );
	}

	return entries;
};

/**
 * Generic Parameters Section Creator
 *
 * Reads saved params from config, creates rows preserving their stored keys, and
 * appends them to the target container.
 *
 * @param {Object} sectionConfig
 * @param {string} sectionConfig.paramsKey - key in options for the params list, e.g. 'g_an_params'
 * @param {string} sectionConfig.containerSelector - CSS selector for the parent container, e.g. '.ctc_g_an_params'
 * @param {Object} sectionConfig.rowConfig - config to pass to createParameterRow (rowClass, keyClass, valClass)
 * @param {Object} config - App config containing initialSettings
 * @returns {HTMLElement}
 */
const createParameters = ( sectionConfig, config ) => {

	// log( 'an', sectionConfig, config );

	const placeholder = document.createElement( 'div' );
	placeholder.style.display = 'none';

	const optionGroup = 'ht_ctc_othersettings';

	// const options = config.initialSettings[ optionGroup ] || {};
	const options = getSafeProperty( config.initialSettings, optionGroup ) || {};
	const dbRow = optionGroup;

	// const params = options[ sectionConfig.paramsKey ];
	const params = getSafeProperty( options, sectionConfig.paramsKey );

	// log( 'an', 'raw params', params );

	const entries = normalizeParamEntries( params );

	// log( 'an', 'normalized entries', entries );
	// log( 'an', '----------' );

	const fragment = document.createDocumentFragment();

	entries.forEach( ( { index, data } ) => {
		const row = createParameterRow( {
			...sectionConfig.rowConfig,
			paramsKey: sectionConfig.paramsKey,
			index,
			data,
			dbRow,
		} );
		fragment.appendChild( row );
	} );

	setTimeout( () => {
		const container = document.querySelector( sectionConfig.containerSelector );
		if ( container ) {
			container.appendChild( fragment );
		}
	}, 0 );

	return placeholder;
};

// ========================================================================
// Tracker Configurations (GA, Pixel, GTM)
// ========================================================================

/**
 * Per-tracker config — single source of truth. Drives the section creator,
 * row creator, and repeater "add" action for each tracker. When adding a new
 * tracker (e.g. Bing UET), add an entry here and the thin delegate exports
 * below; no other code paths need touching.
 *
 * Example DB shape (GA — Pixel and GTM mirror this pattern with their own keys):
 *   g_an_params: {
 *     0: { key: 'number', value: '{number}' },
 *     1: { key: 'title',  value: '{title}'  },
 *     2: { key: 'url',    value: '{url}'    }
 *   }
 *   // a row added by the user lands under a time-based key, e.g. 1718...: { key, value }
 */
const TRACKERS = {
	ga: {
		paramsKey: 'g_an_params',
		containerSelector: '.ctc_g_an_params',
		rowConfig: {
			rowClass: 'g_an_param',
			keyClass: 'ht_ctc_g_an_param_key',
			valClass: 'ht_ctc_g_an_param_value',
		},
	},
	pixel: {
		paramsKey: 'pixel_params',
		containerSelector: '.ctc_pixel_params',
		rowConfig: {
			rowClass: 'pixel_param',
			keyClass: 'ht_ctc_pixel_param_key',
			valClass: 'ht_ctc_pixel_param_value',
		},
	},
	gtm: {
		paramsKey: 'gtm_params',
		containerSelector: '.ctc_gtm_params',
		rowConfig: {
			rowClass: 'gtm_param',
			keyClass: 'ht_ctc_gtm_param_key',
			valClass: 'ht_ctc_gtm_param_value',
		},
	},
};

const makeSectionCreator = ( cfg ) => ( field, context = document, config ) =>
	createParameters( cfg, config );

/**
 * 'field_type' => section_google_analytics_params
 */
export const createGaParametersSection = makeSectionCreator( TRACKERS.ga );
export const createPixelParametersSection = makeSectionCreator( TRACKERS.pixel );
export const createGtmParametersSection = makeSectionCreator( TRACKERS.gtm );

/**
 * Strictly-increasing, time-based index used as the db key for newly added rows.
 * Guards against two rows colliding on the same millisecond during rapid adds.
 */
let ctcLastParamIndex = 0;
const uniqueParamIndex = () => {
	let idx = Date.now();
	if ( idx <= ctcLastParamIndex ) {
		idx = ctcLastParamIndex + 1;
	}
	ctcLastParamIndex = idx;
	return idx;
};

const addTrackerParam = ( cfg, { container } = {} ) => {
	const target = container ?? document.querySelector( cfg.containerSelector );
	if ( ! target ) { return; }
	const index = uniqueParamIndex();
	const row = createParameterRow( {
		...cfg.rowConfig,
		paramsKey: cfg.paramsKey,
		index,
		data: { key: '', value: '' },
		dbRow: 'ht_ctc_othersettings',
	} );
	target.appendChild( row );
};

export const addGaParam = ( opts ) => addTrackerParam( TRACKERS.ga, opts );
export const addPixelParam = ( opts ) => addTrackerParam( TRACKERS.pixel, opts );
export const addGtmParam = ( opts ) => addTrackerParam( TRACKERS.gtm, opts );
