/**
 * Preview Template Registry
 *
 * Lazy registry mapping a style id (e.g. '4') to a template module loader.
 * Templates are pure render functions: ( ctx ) => html string. They are
 * dynamically imported on first use only, so the catalog can grow without
 * increasing the initial page load.
 *
 * PRO (and future features like omnichannel) register their own templates:
 * the App dispatches `ctc_manager_registered_preview` after the PreviewManager
 * is registered — listen for it and call `manager.registry.registerStyle()`.
 */
import { log, importWithRetry } from '../core/Utils.js';

export default class PreviewRegistry {

	constructor () {
		// namespaced key ('style:4', 'greeting:greetings-1') -> () => import( templateModule )
		this.loaders = new Map();

		// namespaced key -> resolved render function
		this.cache = new Map();
	}

	/**
	 * Register a style template.
	 *
	 * @param {string} id     Style id as stored in DB (e.g. '4', '7_1').
	 * @param {Function} loader Returns a promise resolving to the template module
	 *                          (default export = render function).
	 */
	registerStyle ( id, loader ) {
		this.loaders.set( `style:${id}`, loader );
	}

	/**
	 * Register a greetings dialog template.
	 *
	 * @param {string} id     Template id as stored in DB (e.g. 'greetings-1').
	 * @param {Function} loader Returns a promise resolving to the template module.
	 */
	registerGreeting ( id, loader ) {
		this.loaders.set( `greeting:${id}`, loader );
	}

	/**
	 * Whether a template is registered for the style id.
	 *
	 * @param {string} id Style id.
	 * @returns {boolean}
	 */
	hasStyle ( id ) {
		return this.loaders.has( `style:${id}` );
	}

	/**
	 * Resolve the render function for a style id (lazy import, cached).
	 *
	 * @param {string} id Style id.
	 * @returns {Promise<Function|null>} Render function or null if unavailable.
	 */
	getStyleRenderer ( id ) {
		return this.getRenderer( `style:${id}` );
	}

	/**
	 * Resolve the render function for a greetings template id.
	 *
	 * @param {string} id Greetings template id.
	 * @returns {Promise<Function|null>} Render function or null if unavailable.
	 */
	getGreetingRenderer ( id ) {
		return this.getRenderer( `greeting:${id}` );
	}

	/**
	 * Resolve a render function by namespaced key (lazy import, cached).
	 *
	 * @param {string} key Namespaced key.
	 * @returns {Promise<Function|null>} Render function or null if unavailable.
	 */
	async getRenderer ( key ) {
		if ( this.cache.has( key ) ) {
			return this.cache.get( key );
		}

		const loader = this.loaders.get( key );
		if ( ! loader ) {
			return null;
		}

		try {
			const module = await importWithRetry( loader );
			const renderFn = module && module.default;
			if ( typeof renderFn === 'function' ) {
				this.cache.set( key, renderFn );
				return renderFn;
			}
		} catch ( error ) {
			log( 'Preview', `Failed to load template for style ${key}`, error );
		}

		return null;
	}
}
