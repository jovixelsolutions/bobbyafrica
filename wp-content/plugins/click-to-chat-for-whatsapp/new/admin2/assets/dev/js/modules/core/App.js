/**
 * Main App Orchestrator
 *
 * Central class that manages the application lifecycle.
 */
import Events from './Events.js';
import API from './API.js';
import * as Utils from './Utils.js';
import { log } from './Utils.js';
import * as Storage from './Storage.js';
import { initConditionalFieldLogic } from '../logic/Conditional.js';

export default class App {

	/**
	 * Setup the application.
	 *
	 * @param {Object} config Global configuration from PHP.
	 */
	constructor ( config ) {
		this.config = config;

		this.api = new API( config );
		this._events = new Events();
		this.utils = { ...Utils, initConditionalFieldLogic };
		this.storage = Storage;

		this.renderers = {};
		this.managers = {};

		// In-flight field fetch requests keyed by group to avoid duplicate calls.
		this.pendingFieldFetches = new Map();

		// Cache-key suffix for localStorage field definitions (version + pro version + locale).
		const proSuffix = config.pro_version ? `_pro${config.pro_version}` : '';
		const localeSuffix = config.locale && 'en_US' !== config.locale ? `_${config.locale}` : '';
		this.fieldsCacheSuffix = `_${config.version}${proSuffix}${localeSuffix}`;

		this.clearOldCaches();
	}

	/**
	 * Garbage collection for old version caches in localStorage.
	 */
	clearOldCaches () {
		try {
			const currentPrefix = 'ht_ctc_fields_';
			const expectedSuffix = this.fieldsCacheSuffix;

			const keysToRemove = [];

			for ( let i = 0; i < localStorage.length; i++ ) {
				const key = localStorage.key( i );
				if ( key && key.startsWith( currentPrefix ) ) {
					if ( ! key.endsWith( expectedSuffix ) ) {
						keysToRemove.push( key );
					}
				}
			}

			keysToRemove.forEach( staleKey => localStorage.removeItem( staleKey ) );
		} catch ( error ) {
			log( 'App', 'Error clearing old caches', error );
		}
	}

	/**
	 * Invalidate cached fields for one or more settings groups in localStorage and window.
	 *
	 * @param {string|string[]} groups Group identifier or array of group identifiers.
	 */
	clearFieldsCache ( groups ) {
		const list = Array.isArray( groups ) ? groups : [ groups ];

		list.forEach( group => {
			if ( ! group || typeof group !== 'string' ) { return; }

			try {
				const prefix = `ht_ctc_fields_${group}`;

				// Drop localStorage entries for this group.
				for ( let i = localStorage.length - 1; i >= 0; i-- ) {
					const key = localStorage.key( i );
					if ( key && key.startsWith( prefix ) ) {
						localStorage.removeItem( key );
					}
				}

				// Drop PHP-preloaded window global copy.
				// window[ prefix ] = undefined;
				Utils.setSafeProperty( window, prefix, undefined );
			} catch ( error ) {
				log( 'App', `Error clearing fields cache for ${group}`, error );
			}
		} );
	}

	/**
	 * Register a Manager (feature module).
	 *
	 * @param {string}       name         Manager name.
	 * @param {Object|Class} ManagerClass Manager class or object with init method.
	 */
	registerManager ( name, ManagerClass ) {
		log( 'App', `Registering manager: ${name}` );
		if ( typeof ManagerClass.init === 'function' ) {
			const isSuccess = this.utils.safeRun( () => ManagerClass.init( this ), name );

			if ( isSuccess ) {
				Utils.setSafeProperty( this.managers, name, ManagerClass );
			} else {
				return;
			}
		} else {
			let managerInstance;
			try {
				managerInstance = new ManagerClass( this );
			} catch ( error ) {
				console.error( `CTC: Manager instantiation failed for ${name}:`, error );
				return;
			}

			if ( typeof managerInstance.init === 'function' ) {
				const isSuccess = this.utils.safeRun( () => managerInstance.init(), name );
				if ( ! isSuccess ) {
					return;
				}
			}

			Utils.setSafeProperty( this.managers, name, managerInstance );
		}

		// Dispatch custom event for external extensions to hook into registered manager.
		document.dispatchEvent( new CustomEvent( `ctc_manager_registered_${name}`, {
			detail: { manager: Utils.getSafeProperty( this.managers, name ), app: this },
		} ) );
	}

	/**
	 * Register a Field Renderer.
	 *
	 * @param {string}   type       Field type.
	 * @param {Function} rendererFn Renderer function generating HTML.
	 */
	registerRenderer ( type, rendererFn ) {
		// this.renderers[ type ] = rendererFn;
		Utils.setSafeProperty( this.renderers, type, rendererFn );
	}

	/**
	 * Create HTML element for a specific field using registered renderers.
	 *
	 * @param {Object}           field   Field configuration.
	 * @param {Document|Element} context Parent context.
	 * @returns {HTMLElement} Rendered field element or error element.
	 */
	createFieldElement ( field, context = document ) {
		const fieldType = field.field_type;
		const renderer = Utils.getSafeProperty( this.renderers, fieldType );

		if ( typeof renderer === 'function' ) {
			try {
				return renderer( field, context );
			} catch ( error ) {
				console.error( `CtC: Error rendering field ${fieldType}:`, error );
				const errorDiv = document.createElement( 'div' );
				errorDiv.className = 'ctc-field-error';
				// eslint-disable-next-line no-unsanitized/property -- Static HTML wrapper; dynamic values are safely escaped via Utils.escapeHTML
				errorDiv.innerHTML = '<strong>Error:</strong> Failed to render ' +
					`<code>${Utils.escapeHTML( String( fieldType ) )}</code>. ` +
					`<small>${Utils.escapeHTML( String( error.message ) )}</small>`;
				return errorDiv;
			}
		}

		console.warn( `CtC: No renderer for: ${fieldType}` );
		const errorDiv = document.createElement( 'div' );
		errorDiv.className = 'ctc-field-error';
		// eslint-disable-next-line no-unsanitized/property -- Static HTML wrapper; dynamic values are safely escaped via Utils.escapeHTML
		errorDiv.innerHTML = '<strong>Error:</strong> Field type ' +
			`<code>${Utils.escapeHTML( String( fieldType ) )}</code> N/A.`;
		return errorDiv;
	}

	/**
	 * Retrieve field schema for a given settings group.
	 *
	 * @param {string} group Settings group identifier.
	 * @returns {Promise<Array|Object>} Field schema.
	 */
	async getFieldsForGroup ( group ) {
		const cacheKey = `ht_ctc_fields_${group}${this.fieldsCacheSuffix}`;
		const windowKey = `ht_ctc_fields_${group}`;

		const cached = this.getCachedFields( cacheKey, windowKey );
		if ( cached ) { return cached; }

		const inFlight = this.pendingFieldFetches.get( group );
		if ( inFlight ) { return inFlight; }

		// Clear pending fetch on settle so retries are possible.
		const request = this.fetchFieldsFromAPI( group, cacheKey )
			.finally( () => this.pendingFieldFetches.delete( group ) );

		this.pendingFieldFetches.set( group, request );

		return request;
	}

	/**
	 * Look up cached fields from window global or LocalStorage.
	 *
	 * @param {string} cacheKey  LocalStorage key.
	 * @param {string} windowKey Property name to read off `window`.
	 * @returns {Array|Object|null} Cached fields, or null on miss.
	 */
	getCachedFields ( cacheKey, windowKey ) {
		// const preloaded = window[ windowKey ];
		const preloaded = Utils.getSafeProperty( window, windowKey );
		if ( preloaded ) { return preloaded; }

		const cached = this.storage.getItem( cacheKey, false );
		if ( ! cached ) { return null; }

		try {
			return JSON.parse( cached );
		} catch {
			localStorage.removeItem( cacheKey );
			return null;
		}
	}

	/**
	 * Fetch fields from REST API and cache the response.
	 *
	 * @param {string} group    Settings group identifier.
	 * @param {string} cacheKey LocalStorage key to cache under.
	 * @returns {Promise<Array|Object>}
	 */
	async fetchFieldsFromAPI ( group, cacheKey ) {
		const apiGroup = group.replace( /_/g, '-' );
		const url = `${this.api.endpoints.GET_FIELDS}?group=${apiGroup}&v=${this.config.version}`;
		const result = await this.api.request( url );

		if ( result.success && result.fields ) {
			this.storage.setItem( cacheKey, JSON.stringify( result.fields ), false );
			return result.fields;
		}

		throw new Error( result.message || 'Failed to retrieve settings from server.' );
	}

	/**
	 * Fetch multiple field groups in a single batch request and cache each.
	 *
	 * @param {string[]} groups Settings group identifiers.
	 * @returns {Promise<Object>} Map of group to fields for resolved groups.
	 */
	fetchFieldsBatch ( groups ) {
		const apiGroups = groups.map( group => group.replace( /_/g, '-' ) )
			.join( ',' );
		const url = `${this.api.endpoints.GET_FIELDS}?groups=${apiGroups}&v=${this.config.version}`;

		const request = ( async () => {
			const result = await this.api.request( url );

			if ( ! result.success || ! result.groups ) {
				throw new Error( result.message || 'Failed to retrieve settings from server.' );
			}

			Object.entries( result.groups )
				.forEach( ( [ group, fields ] ) => {
					const cacheKey = `ht_ctc_fields_${group}${this.fieldsCacheSuffix}`;
					this.storage.setItem( cacheKey, JSON.stringify( fields ), false );
				} );

			return result.groups;
		} )();

		groups.forEach( group => {
			const forGroup = request
				.then( all => {
					// const fields = all[ group ];
					const fields = Utils.getSafeProperty( all, group );
					if ( ! fields ) {
						const msg = `Settings fields for "${ group }" were not returned by server.`;
						throw new Error( msg );
					}
					return fields;
				} )
				.finally( () => this.pendingFieldFetches.delete( group ) );

			// eslint-disable-next-line no-empty-function -- Prevent unhandled rejection if no caller joins.
			forGroup.catch( () => {} );

			this.pendingFieldFetches.set( group, forGroup );
		} );

		return request;
	}

	/**
	 * Load and render settings for a tab.
	 *
	 * @param {string} tabId          Tab element ID.
	 * @param {string} containerClass Target container class name.
	 */
	async loadTabSettings ( tabId, containerClass = '' ) {
		const panel = document.getElementById( tabId );

		if ( ! panel || panel.dataset.loaded === 'true' || panel.dataset.loading === 'true' ) { return; }

		panel.dataset.loading = 'true';

		const targetClass = containerClass || 'fields-container';

		const container = Array.from( panel.children )
			.find( child => child.classList.contains( targetClass ) );

		if ( ! container ) {
			return;
		}

		const group = ( panel.getAttribute( 'data-group' ) || tabId ).replace( /-/g, '_' );

		try {
			const fields = await this.getFieldsForGroup( group );

			if ( fields ) {
				// Concurrently load modules assigned to this tab.
				if ( this.config.modulesPath ) {
					const modulesToLoad = Object.entries( this.config.modulesPath )
						.filter( ( [ , moduleConf ] ) =>
							moduleConf.tabs && moduleConf.tabs.includes( tabId ) );

					if ( modulesToLoad.length > 0 ) {
						await Promise.allSettled( modulesToLoad.map( ( [ key, moduleConf ] ) =>
							this.loadModule( key, moduleConf ) ) );
					}
				}

				await this.renderTabFields( fields, container );
				panel.dataset.loaded = 'true';

				initConditionalFieldLogic( document );

				// Run post-render module methods.
				if ( this.config.modulesPath ) {
					Object.entries( this.config.modulesPath )
						.filter( ( [ , moduleConf ] ) =>
							moduleConf.tabs &&
							moduleConf.tabs.includes( tabId ) &&
							moduleConf.method )
						.forEach( ( [ key, moduleConf ] ) =>
							this.runModuleMethod(
								moduleConf._loadedModule,
								moduleConf,
								panel,
								key,
							) );
				}

				// Load DOM-driven modules based on rendered panel content.
				await this.loadModulesForPanel( panel );
			}
		} catch ( error ) {
			console.error( `Error loading ${tabId}:`, error );

			const friendlyMessage = Utils.friendlyErrorMessage( error );

			const errorWrapper = document.createElement( 'div' );
			errorWrapper.className = 'ctc-error-container';
			errorWrapper.style.padding = '20px';
			errorWrapper.style.textAlign = 'center';

			// eslint-disable-next-line no-unsanitized/property -- Static HTML; dynamic error details are safely escaped via Utils.escapeHTML
			errorWrapper.innerHTML = `
				<div class="ctc-error-message" style="margin-bottom: 15px; color: #d63638;">
					<p style="margin: 0; font-weight: 600;">Error loading settings</p>
					<div style="margin-top: 5px; line-height: 1.4;">
						<small style="opacity: 0.8; display: block; margin-bottom: 4px;">Technical Detail: ${Utils.escapeHTML( String( error.message || 'N/A' ) )}</small>
						<span style="font-size: 13px;">${Utils.escapeHTML( friendlyMessage )}</span>
					</div>
				</div>
			`;

			const retryBtn = document.createElement( 'button' );
			retryBtn.className = 'button button-secondary';

			if ( friendlyMessage.includes( 'Session expired' ) ) {
				retryBtn.innerText = 'Refresh Page';
				retryBtn.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					window.location.reload();
				}, { once: true } );
			} else {
				retryBtn.innerText = 'Retry';
				retryBtn.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					this.loadTabSettings( tabId, containerClass );
				}, { once: true } );
			}

			errorWrapper.appendChild( retryBtn );
			container.innerHTML = '';
			container.appendChild( errorWrapper );
		} finally {
			panel.dataset.loading = 'false';
		}
	}

	/**
	 * Renders fields into a container using batched requestAnimationFrame chunks.
	 *
	 * @param {Array|Object} fields    Field configurations.
	 * @param {HTMLElement}  container Container element.
	 * @returns {Promise<void>}
	 */
	renderTabFields ( fields, container ) {
		return new Promise( ( resolve ) => {
			container.innerHTML = '';

			let fieldsToRender = [];
			if ( Array.isArray( fields ) ) {
				fieldsToRender = fields;
			} else if ( fields && typeof fields === 'object' ) {
				fieldsToRender = Object.values( fields )
					.reduce( ( acc, val ) => acc.concat( val ), [] );
			}

			const totalFields = fieldsToRender.length;
			if ( totalFields === 0 ) {
				resolve();
				return;
			}

			const chunkSize = 20;
			let index = 0;

			const renderChunk = () => {
				const fragment = document.createDocumentFragment();
				const max = Math.min( index + chunkSize, totalFields );

				for ( ; index < max; index++ ) {
					// const field = fieldsToRender[ index ];
					const field = Utils.getSafeProperty( fieldsToRender, String( index ) );
					const el = this.createFieldElement( field );

					if ( el ) { fragment.appendChild( el ); }
				}

				container.appendChild( fragment );

				if ( index < totalFields ) {
					requestAnimationFrame( renderChunk );
				} else {
					resolve();
				}
			};

			requestAnimationFrame( renderChunk );
		} );
	}

	/**
	 * Load and initialize phone input on demand.
	 *
	 * @param {string}           containerClass Visible input class to initialize.
	 * @param {Document|Element} context        Scope to search within.
	 * @returns {Promise<void>}
	 */
	async loadAndInitIntlInput ( containerClass = 'intl_number', context = document ) {
		const phoneConf = this.config.modulesPath?.phoneInput;
		if ( phoneConf && phoneConf.path ) {
			try {
				const module = await Utils.importWithRetry( ( attempt ) =>
					// eslint-disable-next-line no-unsanitized/method -- Path is from trusted plugin configuration localized by PHP
					import( /* webpackIgnore: true */ Utils.retryUrl( phoneConf.path, attempt ) ) );
				if ( module && typeof module.initPhoneInput === 'function' ) {
					module.initPhoneInput( containerClass, context, this );
				}
			} catch ( error ) {
				console.warn( 'CtC: Error loading PhoneInput module dynamically', error );
			}
		}
	}

	/**
	 * Dynamic-import a single modulesPath entry and register what it exposes.
	 *
	 * @param {string} key        Module key.
	 * @param {Object} moduleConf Module config from config.modulesPath.
	 * @returns {Promise<Object|null>} The imported module, or null on failure.
	 */
	async loadModule ( key, moduleConf ) {
		try {
			const module = await Utils.importWithRetry( ( attempt ) =>
				// eslint-disable-next-line no-unsanitized/method -- Path is from trusted plugin configuration localized by PHP
				import( /* webpackIgnore: true */ Utils.retryUrl( moduleConf.path, attempt ) ) );

			moduleConf._loadedModule = module;

			if ( moduleConf.managerId && module.default ) {
				this.registerManager( moduleConf.managerId, module.default );
			}

			if ( moduleConf.rendererId && typeof module.default === 'function' ) {
				this.registerRenderer(
					moduleConf.rendererId,
					( field, context ) => module.default( field, context, this.config ),
				);
			}

			return module;
		} catch ( error ) {
			console.error( `CtC: Error loading dynamic module: ${key}`, error );
			return null;
		}
	}

	/**
	 * Load modules required by selectors found within the rendered panel.
	 *
	 * @param {HTMLElement} panel Rendered tab panel element.
	 */
	async loadModulesForPanel ( panel ) {
		if ( ! panel || ! this.config.modulesPath ) { return; }

		const matched = Object.entries( this.config.modulesPath )
			.filter( ( [ , moduleConf ] ) =>
				moduleConf.selector &&
				! moduleConf._loadedModule &&
				panel.querySelector( moduleConf.selector ) );

		if ( ! matched.length ) { return; }

		await Promise.allSettled( matched.map( async ( [ key, moduleConf ] ) => {
			const moduleObj = await this.loadModule( key, moduleConf );
			this.runModuleMethod( moduleObj, moduleConf, panel, key );
		} ) );
	}

	/**
	 * Invoke a module's post-load init method, if declared.
	 *
	 * @param {Object|null} moduleObj  Imported module.
	 * @param {Object}      moduleConf Module config.
	 * @param {*}           context    Context (panel element or document).
	 * @param {string}      key        Module key.
	 */
	runModuleMethod ( moduleObj, moduleConf, context, key ) {
		if ( ! moduleObj || ! moduleConf.method ) { return; }
		try {
			// const methodFn = moduleObj[ moduleConf.method ];
			const methodFn = Utils.getSafeProperty( moduleObj, moduleConf.method );
			if ( typeof methodFn === 'function' ) {
				methodFn( moduleConf.arg || context, context, this );
			}
		} catch ( error ) {
			console.error( `CtC: Error initializing dynamic module method: ${key}`, error );
		}
	}

	/**
	 * Load delayed modules that are scheduled to initialize after boot.
	 */
	loadDelayedModules () {
		if ( ! this.config.modulesPath ) { return; }

		Object.entries( this.config.modulesPath )
			.filter( ( [ , moduleConf ] ) => moduleConf && moduleConf.delay && moduleConf.path )
			.forEach( ( [ key, moduleConf ] ) => {
				setTimeout( async () => {
					const module = await this.loadModule( key, moduleConf );
					this.runModuleMethod( module, moduleConf, document, key );
				}, moduleConf.delay );
			} );
	}

	// Getters for core systems
	get events () { return this._events; }

	getApi () { return this.api; }

	/**
	 * Pre-fetches settings fields for inactive tabs and contextual groups in the background.
	 */
	async preloadBackgroundTabs () {
		const wanted = [];

		document.querySelectorAll( '.settings-panel' )
			.forEach( panel => {
				const group = ( panel.getAttribute( 'data-group' ) || panel.id ).replace( /-/g, '_' );
				if ( group && group !== 'general_settings' ) { wanted.push( group ); }

				const extraGroups = panel.getAttribute( 'data-groups' );
				if ( extraGroups ) {
					extraGroups.split( ',' )
						.forEach( extra => {
							const clean = extra.trim()
								.replace( /-/g, '_' );
							if ( clean ) { wanted.push( clean ); }
						} );
				}
			} );

		// Skip groups that are already cached or currently in flight.
		const missing = [ ...new Set( wanted ) ].filter( group => {
			if ( this.pendingFieldFetches.has( group ) ) { return false; }
			const cacheKey = `ht_ctc_fields_${group}${this.fieldsCacheSuffix}`;
			return ! this.getCachedFields( cacheKey, `ht_ctc_fields_${group}` );
		} );

		if ( ! missing.length ) { return; }

		try {
			await this.fetchFieldsBatch( missing );
		} catch ( error ) {
			log( 'App', 'Preload batch failed', error );
		}
	}
}
