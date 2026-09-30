/**
 * Local Storage Wrapper
 *
 * This module provides a simple interface for interacting with localStorage.
 * It also provides a specific interface for interacting with the 'ht_ctc_storage' object. then we can parse like json.parse()
 */
import { log, getSafeProperty, setSafeProperty } from './Utils.js';

export const getItem = ( item, raw = true ) => {
	try {
		return localStorage.getItem( item );
	} catch ( error ) {
		log( 'Storage', 'getItem error', error );
		return null;
	}
};

export const setItem = ( name, value, raw = true ) => {
	try {
		localStorage.setItem( name, value );
	} catch ( error ) {
		log( 'Storage', 'setItem error', error );
	}
};

// ------------------------------------------------------------------------
// CtC Storage: Manages the 'ht_ctc_storage' JSON object in LocalStorage
// ------------------------------------------------------------------------

/**
 * Update a specific key inside the 'ht_ctc_storage' object.
 * This preserves other keys in the object.
 *
 * @param {string} name - Key to update/add
 * @param {*} value - Value to store
 */
export const setCtcStorageItem = ( name, value ) => {
	try {
		// Validate key name length
		if ( typeof name !== 'string' || name.length === 0 || name.length > 100 ) {
			log( 'Storage', 'Invalid key name length', name );
			return;
		}

		// Prevent prototype pollution - improve the way of adding all this..
		const dangerousKeys = [ '__proto__', 'constructor', 'prototype' ];
		if ( dangerousKeys.includes( name ) ) {
			log( 'Storage', 'Blocked dangerous key', name );
			return;
		}

		// Only allow alphanumeric, underscore, and hyphen
		if ( ! /^[a-zA-Z0-9_-]+$/.test( name ) ) {
			log( 'Storage', 'Invalid key format', name );
			return;
		}

		const key = 'ht_ctc_storage';
		let storage = {};

		const current = localStorage.getItem( key );
		if ( current ) {
			try {
				storage = JSON.parse( current );
			} catch ( error ) {
				log( 'Storage', 'JSON parse error', error );
				storage = {};
			}
		}

		// Ensure we are working with an object
		if ( typeof storage !== 'object' || storage === null || Array.isArray( storage ) ) {
			storage = {};
		}

		// storage[ name ] = value;
		setSafeProperty( storage, name, value );
		localStorage.setItem( key, JSON.stringify( storage ) );
	} catch ( error ) {
		log( 'Storage', 'setCtcStorageItem error', error );
	}
};

/**
 * Retrieve a specific key from the 'ht_ctc_storage' object.
 *
 * @param {string} name - Key to retrieve
 * @returns {*} - The value or null if not found
 */
export const getCtcStorageItem = ( name ) => {
	try {
		const key = 'ht_ctc_storage';
		const current = localStorage.getItem( key );

		if ( ! current ) {
			return null;
		}

		const storage = JSON.parse( current );
		if ( typeof storage === 'object' && storage !== null && ! Array.isArray( storage ) ) {
			// return storage[ name ];
			return getSafeProperty( storage, name );
		}

		return null;
	} catch ( error ) {
		log( 'Storage', 'getCtcStorageItem error', error );
		return null;
	}
};
