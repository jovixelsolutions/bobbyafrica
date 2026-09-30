/**
 * Reads option values for the live preview from the settings form, falling
 * back to the saved values (config.initialSettings) — so unsaved edits win.
 *
 * Extracted from PreviewManager so the (intricate) form-name parsing — nested
 * names like `group[agent_1][title]`, array fields `group[x][]`, radios and
 * checkboxes, deep-merge with saved values — is unit-testable in isolation.
 * Templates receive `ctx.value` / `ctx.groupValue` bound to an instance.
 */
import { getNestedValue, getSafeProperty, setSafeProperty } from '../core/Utils.js';

export default class FormValues {

	/**
	 * @param {HTMLFormElement} form
	 * @param {Object} initialSettings saved option groups (config.initialSettings)
	 */
	constructor ( form, initialSettings = {} ) {
		this.form = form;
		this.initialSettings = initialSettings || {};
	}

	/**
	 * Current value for option group + key: live form input first (unsaved
	 * edits win), then the saved value from initialSettings.
	 *
	 * @param {string} group Option group (e.g. 'ht_ctc_chat_options').
	 * @param {string} key   Field id.
	 * @returns {string|undefined}
	 */
	get ( group, key ) {
		const inputs = this.form.querySelectorAll( `[name="${group}[${key}]"]` );

		for ( const input of inputs ) {
			if ( input.type === 'radio' ) {
				if ( input.checked ) { return input.value; }
				continue; // unchecked radio — keep looking
			}
			if ( input.type === 'checkbox' ) {
				return input.checked ? ( input.value || '1' ) : '';
			}
			return input.value;
		}

		return getNestedValue( this.initialSettings, group, key );
	}

	/**
	 * Full option-group object: saved values deep-merged with live (unsaved)
	 * form values parsed from input names. Needed by templates with nested
	 * structures (multi-agent, greetings form fields).
	 *
	 * @param {string} group Option group name.
	 * @returns {Object}
	 */
	groupValues ( group ) {
		let merged = {};

		// const saved = this.initialSettings?.[ group ];
		const saved = getSafeProperty( this.initialSettings || {}, group );
		if ( saved && typeof saved === 'object' ) {
			try {
				merged = JSON.parse( JSON.stringify( saved ) );
			} catch {
				merged = {};
			}
		}

		const live = {};

		// Filter in JS rather than a `[name^="group["]` CSS selector — the '['
		// in the prefix value is fragile in some selector engines (jsdom).
		const prefix = `${group}[`;
		Array.from( this.form.querySelectorAll( '[name]' ) )
			.filter( ( input ) => String( input.name )
				.startsWith( prefix ) )
			.forEach( ( input ) => {
				if ( input.type === 'radio' && ! input.checked ) { return; }
				let value = input.value;
				if ( input.type === 'checkbox' ) {
					if ( ! input.checked ) { return; }
					value = input.value || '1';
				}

				const isArrayField = input.name.endsWith( '[]' );
				const nameWithoutArray = isArrayField ? input.name.slice( 0, -2 ) : input.name;
				const path = nameWithoutArray.match( /[^[\]]+/g ) || [];
				const keys = path.slice( 1 ); // drop the group segment
				if ( keys.length === 0 && ! isArrayField ) { return; }

				let target = live;
				keys.forEach( ( key, index ) => {
					const isLast = index === keys.length - 1;
					if ( isLast && ! isArrayField ) {
						// target[ key ] = value;
						setSafeProperty( target, key, value );
						return;
					}

					// let next = target[ key ];
					let next = getSafeProperty( target, key );
					if ( ! next || typeof next !== 'object' ) {
						next = ( isLast && isArrayField ) ? [] : {};

						// target[ key ] = next;
						setSafeProperty( target, key, next );
					}
					target = next;
				} );
				if ( isArrayField && Array.isArray( target ) ) {
					target.push( value );
				}
			} );

		// Live values win; arrays are replaced (not merged) so removed
		// repeater rows don't linger.
		const deepMerge = ( target, source ) => {
			Object.keys( source )
				.forEach( ( key ) => {
					// const value = source[ key ];
					const value = getSafeProperty( source, key );

					// const existing = target[ key ];
					const existing = getSafeProperty( target, key );
					if ( value !== null && typeof value === 'object' && ! Array.isArray( value ) &&
						existing !== null && typeof existing === 'object' && ! Array.isArray( existing ) ) {
						deepMerge( existing, value );
					} else {
						// target[ key ] = value;
						setSafeProperty( target, key, value );
					}
				} );
			return target;
		};

		return deepMerge( merged, live );
	}
}
