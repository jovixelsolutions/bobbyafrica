/**
 * Event Bus / Hooks System
 *
 * Provides a central hub for communication between decoupled modules.
 * Standardizes on 'on', 'off', and 'emit'.
 */
import { log, safeRun } from './Utils.js';

export default class Events {
	constructor () {
		this.listeners = new Map();
	}

	/**
	 * Subscribe to an event
	 * @param {string} event
	 * @param {Function} callback
	 */
	on ( event, callback ) {
		if ( ! this.listeners.has( event ) ) {
			this.listeners.set( event, [] );
		}
		this.listeners.get( event )
			.push( callback );

		// Return an "unsubscribe" function for easy cleanup
		return () => this.off( event, callback );
	}

	/**
	 * Unsubscribe from an event
	 * @param {string} event
	 * @param {Function} callback
	 */
	off ( event, callback ) {
		if ( ! this.listeners.has( event ) ) {
			log( 'Events', `off: no listeners for "${event}"` );
			return;
		}
		const queue = this.listeners.get( event );
		const index = queue.indexOf( callback );
		if ( index !== -1 ) {
			queue.splice( index, 1 );
		}
	}

	/**
	 * Publish an event
	 * @param {string} event
	 * @param {any} data
	 */
	emit ( event, data ) {
		if ( ! this.listeners.has( event ) ) {
			log( 'Events', `emit: no listeners for "${event}"` );
			return;
		}
		this.listeners.get( event )
			.forEach( callback => {
				safeRun( () => callback( data ), `${callback?.name} for event: "${event}"` );
			} );
	}
}
