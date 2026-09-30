/**
 * Preview Manager
 *
 * Live preview of the chat widget inside the admin SPA.
 * Renders widget styles and greetings templates based on current form values.
 */
import { log, debounce, escapeHTML, escapeAttr, retryUrl } from '../core/Utils.js';
import PreviewRegistry from '../preview/PreviewRegistry.js';
import FormValues from '../preview/form-values.js';
import { escapeCssValue } from '../preview/css.js';
import { singleColorIcon, logoIcon, squareIcon } from '../preview/icons.js';
import * as greetingsParts from '../preview/greetings-parts.js';
import { initialState, nextState, eventForField, stateFromStorage, storageFromState } from '../preview/visibility-state.js';
import { noteForField, noteForTransition, hasUnresolvedVariable, NOTES } from '../preview/notes.js';
import { buildWhatsAppUrl } from '../preview/wa-url.js';
import { resolveClickAction } from '../preview/click-action.js';
import { notificationBadgeHtml, applyBadgeOffset } from '../preview/notification-badge.js';
import { uniquifySvgIds } from '../preview/svg-ids.js';
import SiteView from '../preview/site-view.js';
import {
	DESKTOP,
	MOBILE,
	autoOpensGreetings,
	greetingsAppliesTo,
	greetingsFullWidthOn,
	widgetFullWidthOn,
	isMobile,
	normalizeDevice,
	resolvePosition,
	styleKeyFor,
	widgetShowsOn,
} from '../preview/device.js';

const STORAGE_KEY = 'admin-preview';

// Tabs whose "Select Style" grid this preview updates.
const STYLE_GRID_TABS = [ 'general-settings' ];

// Free styles shipped with template files in modules/preview/templates/.
const FREE_TEMPLATES = [ '1', '2', '3', '3_1', '4', '5', '6', '7', '7_1', '8', '99' ];

// Matches a valid CSS length value (e.g., 10px, 1.5rem, 5%).
const CSS_LENGTH = /^-?(?:\d{1,6}(?:\.\d{1,3})?|\.\d{1,3})(px|%|em|rem|vh|vw)?$/;

// Free greetings dialog templates.
const FREE_GREETINGS = [ 'greetings-1', 'greetings-2' ];

export default class PreviewManager {

	constructor ( app ) {
		this.app = app;
		this.registry = new PreviewRegistry();
		this.form = null;
		this.container = null;
		this.stage = null;
		this.toggle = null;
		this.note = null;
		this.sidebar = null;
		this.enabled = false;
		this.siteView = null;
		this.autoRevealPending = false;
		this.device = DESKTOP;
		this.deviceInputs = [];
		this.lastSiteViewDevice = DESKTOP;
		this.initialRenderDone = false;
		this.uiState = initialState();
		this.greetingVisible = false;
		this.transientNote = '';
		this.optinGateActive = false;
		this.ctaHardHidden = false;
		this.toastVisible = false;
		this.renderDebounced = debounce( () => this.render(), 120 );
		this.gridUidCounter = 0;

		// Extensible providers.
		this.numberProviders = [];
		this.preFilledPrefixProviders = [];
		this.noteProviders = [];
	}

	/**
	 * Resolves WhatsApp number from providers or settings.
	 *
	 * @returns {string} Phone number.
	 */
	resolveNumber () {
		for ( const provider of this.numberProviders ) {
			const num = String( provider( this ) || '' )
				.trim();
			if ( num !== '' ) { return num; }
		}
		return this.values.get( 'ht_ctc_chat_options', 'number' ) || '';
	}

	/**
	 * Resolves pre-filled message prefix from providers.
	 *
	 * @returns {string} Message prefix or empty string.
	 */
	resolvePreFilledPrefix () {
		for ( const provider of this.preFilledPrefixProviders ) {
			const prefix = String( provider( this ) || '' );
			if ( prefix !== '' ) { return prefix; }
		}
		return '';
	}

	/**
	 * Resolves contextual note for a changed field.
	 *
	 * @param {string} name Field name.
	 * @returns {string} Contextual note or empty string.
	 */
	resolveFieldNote ( name ) {
		for ( const provider of this.noteProviders ) {
			const note = String( provider( name, this ) || '' )
				.trim();
			if ( note !== '' ) { return note; }
		}

		const fieldNote = noteForField( name )?.text || '';
		if ( fieldNote ) { return fieldNote; }

		// Show note if value contains dynamic page variables.
		const input = this.form.querySelector( `[name="${name}"]` );
		return hasUnresolvedVariable( input?.value ) ? NOTES.variables : '';
	}

	/**
	 * Replaces supported message variables (e.g. {site}) in text.
	 *
	 * @param {string} text Message text.
	 * @returns {string} Text with replaced variables.
	 */
	applyMessageVariables ( text ) {
		const site = this.app.config?.preview?.site || '';
		if ( site === '' || typeof text !== 'string' ) { return text; }
		return text.replaceAll( '{{site}}', site )
			.replaceAll( '{site}', site );
	}

	init () {
		this.form = document.getElementById( 'ctc-settings-form' );
		if ( ! this.form ) { return; }

		this.values = new FormValues( this.form, this.app.config?.initialSettings );

		this.registerFreeTemplates();
		this.buildContainer();
		this.injectFrontCss();
		this.bindToggle();
		this.bindDevice();
		this.bindSiteView();
		this.bindFormEvents();
		this.bindToastEvents();

		window.addEventListener( 'resize', () => {
			if ( this.enabled ) { this.renderDebounced(); }
		} );

		this.uiState = stateFromStorage( ( key ) => this.app.storage.getCtcStorageItem( key ) );
		this.ctaHardHidden = this.uiState.greetingOpen && this.greetingEnabled();

		const storedPref = this.app.storage.getCtcStorageItem( STORAGE_KEY );
		const hasExplicitPref = storedPref === 'on' || storedPref === 'off';
		this.enabled = storedPref === 'on';
		this.autoRevealPending = ! hasExplicitPref;
		if ( this.toggle ) { this.toggle.checked = this.enabled; }
		if ( this.enabled ) {
			this.render()
				.finally( () => { this.initialRenderDone = true; } );
		} else {
			this.initialRenderDone = true;
		}

		document.addEventListener(
			'ctc_manager_registered_preview',
			() => this.enhanceStyleGrids(),
			{ once: true },
		);

		this.app.events?.on( 'tab:changed', ( tabId ) => {
			if ( STYLE_GRID_TABS.includes( tabId ) ) { this.enhanceStyleGrids(); }
		} );
	}

	/**
	 * Injects front-end stylesheet for preview styling.
	 */
	injectFrontCss () {
		const url = this.app.config?.paths?.front_css;
		if ( ! url || document.querySelector( 'link[data-ctc-front-css]' ) ) { return; }
		const link = document.createElement( 'link' );
		link.rel = 'stylesheet';
		link.href = url;
		link.setAttribute( 'data-ctc-front-css', '1' );
		document.head.appendChild( link );
	}

	/**
	 * Gets the target container element for mounting the preview.
	 *
	 * @returns {HTMLElement|null}
	 */
	mountTarget () {
		if ( this.siteView?.isOpen && this.siteView.viewport ) {
			return this.siteView.viewport;
		}
		return document.body;
	}

	/**
	 * Moves the preview container to the current mount target.
	 */
	mountPreview () {
		if ( ! this.container ) { return; }

		const target = this.mountTarget();
		if ( ! target ) { return; }

		if ( this.container.parentElement !== target ) {
			target.appendChild( this.container );
		}

		this.container.classList.toggle(
			'ctc-in-site-view',
			Boolean( this.siteView?.isOpen ),
		);

		this.injectFrontCss();
	}

	/**
	 * Registers lazy loaders for free style and greetings templates.
	 */
	registerFreeTemplates () {
		const base = this.app.config?.preview?.templatesBasePath;
		if ( ! base ) {
			log( 'Preview', 'No templatesBasePath in config — preview templates unavailable.' );
			return;
		}

		const ver = this.app.config?.version ?
			`?ver=${encodeURIComponent( this.app.config.version )}` :
			'';

		FREE_TEMPLATES.forEach( ( id ) => {
			const url = `${base}style-${id}.js${ver}`;
			this.registry.registerStyle( id, ( attempt ) =>
				// eslint-disable-next-line no-unsanitized/method -- URL is built from trusted plugin configuration localized by PHP
				import( /* webpackIgnore: true */ retryUrl( url, attempt ) ) );
		} );

		FREE_GREETINGS.forEach( ( id ) => {
			const url = `${base}${id}.js${ver}`;
			this.registry.registerGreeting( id, ( attempt ) =>
				// eslint-disable-next-line no-unsanitized/method -- URL is built from trusted plugin configuration localized by PHP
				import( /* webpackIgnore: true */ retryUrl( url, attempt ) ) );
		} );
	}

	/**
	 * Builds and configures the floating preview DOM container.
	 */
	buildContainer () {
		this.container = document.createElement( 'div' );
		this.container.id = 'ht-ctc-admin-preview';
		this.container.className = 'ht-ctc';
		this.container.style.cssText = 'position:fixed;display:none;z-index:99999;cursor:pointer;';
		this.container.title = 'Click to Chat — preview';

		this.container.setAttribute( 'role', 'region' );
		this.container.setAttribute( 'aria-label', 'Live preview of your chat widget' );

		const stateCss = document.createElement( 'style' );
		stateCss.textContent =
			'#ht-ctc-admin-preview.ctc-state-badge-hidden .ht_ctc_notification{display:none !important;}' +
			'#ht-ctc-admin-preview.ctc-state-greeting-open .ctc_cta_stick,' +
			'#ht-ctc-admin-preview.ctc-state-greeting-open .ht-ctc-cta-hover{display:none !important;}' +
			'#ht-ctc-admin-preview.ctc-cta-hidden .ctc_cta_stick,' +
			'#ht-ctc-admin-preview.ctc-cta-hidden .ht-ctc-cta-hover{display:none !important;}' +
			'#ht-ctc-admin-preview .ctc_opt_in{display:none !important;}' +
			'#ht-ctc-admin-preview.ctc-state-optin-show .ctc_opt_in{display:block !important;}' +
			'#ht-ctc-admin-preview{--ctc-preview-vw:390px;}' +
			'#ht-ctc-admin-preview.ctc-in-site-view{--ctc-preview-vw:100%;}' +
			'#ht-ctc-admin-preview.ctc-mobile-g-fullwidth .ht_ctc_chat_greetings_box{' +
			'position:fixed !important;top:auto !important;' +
			'bottom:0 !important;margin:7px !important;' +
			'min-width:0 !important;max-width:none !important;' +
			'width:calc(var(--ctc-preview-vw) - 14px) !important;}' +
			'#ht-ctc-admin-preview.ctc-mobile-g-fullwidth.ctc-g-side-left .ht_ctc_chat_greetings_box{' +
			'right:auto !important;left:0 !important;}' +
			'#ht-ctc-admin-preview.ctc-mobile-g-fullwidth.ctc-g-side-right .ht_ctc_chat_greetings_box{' +
			'left:auto !important;right:0 !important;}' +
			'#ht-ctc-admin-preview.ctc-mobile-g-fullwidth .ctc_g_message_box_width{max-width:85% !important;}' +
			'#ht-ctc-admin-preview.ctc-mobile-w-fullwidth{' +
			'width:var(--ctc-preview-vw) !important;left:auto !important;right:0 !important;}' +
			'#ht-ctc-admin-preview.ctc-mobile-w-fullwidth .s1_btn,' +
			'#ht-ctc-admin-preview.ctc-mobile-w-fullwidth .ht-ctc-style-8,' +
			'#ht-ctc-admin-preview.ctc-mobile-w-fullwidth .ht-ctc-style-8 .s_8{width:100% !important;}';
		this.container.appendChild( stateCss );

		this.greetingsBox = document.createElement( 'div' );
		this.greetingsBox.className = 'ht_ctc_chat_greetings_box';
		this.greetingsBox.style.cssText = 'display:none;position:absolute;bottom:calc(100% + 12px);max-width:420px;cursor:auto;z-index:9;';
		this.container.appendChild( this.greetingsBox );

		this.stage = document.createElement( 'div' );
		this.stage.className = 'ht_ctc_style ht_ctc_chat_style';
		this.container.appendChild( this.stage );

		this.container.addEventListener( 'click', ( event ) => {
			const numberedChat = event.target.closest( '.ctc_chat[data-number]' );
			const action = resolveClickAction( {
				optin: Boolean( event.target.closest( '.ctc_opt_in' ) ),
				closeBtn: Boolean( event.target.closest( '.ctc_greetings_close_btn' ) ),
				greetingCta: Boolean( event.target.closest( '.ht_ctc_chat_greetings_box_link' ) ),
				numberedChat: Boolean( numberedChat ),
				baseWidget: Boolean( event.target.closest( '.ht_ctc_chat_style' ) ),
			}, {
				greetingEnabled: this.greetingEnabled(),
				optinEnabled: this.optinEnabled(),
				optinDone: this.uiState.optinDone,
			} );
			this.runClickAction( action, {
				number: numberedChat?.dataset.number || '',
				preFilled: numberedChat?.hasAttribute( 'data-pre_filled' ) ?
					numberedChat.dataset.pre_filled || '' :
					undefined,
			} );
		} );

		this.mountPreview();
	}

	/**
	 * Executes the resolved preview click action.
	 *
	 * @param {string|null} action Action name.
	 * @param {Object}      [payload] Action payload.
	 */
	runClickAction ( action, payload = {} ) {
		switch ( action ) {
			case 'optin_accept':
				this.applyEvent( 'optin_click' );
				break;
			case 'greeting_close':
				this.applyEvent( 'greeting_close' );
				break;
			case 'optin_reveal':
				this.optinGateActive = true;
				this.syncStateClasses();
				break;
			case 'navigate':
				this.openWhatsApp();
				break;
			case 'numbered_navigate':
				this.openWhatsApp( payload.number, payload.preFilled );
				break;
			case 'greeting_toggle':
				this.applyEvent( 'greeting_toggle' );
				if ( this.uiState.greetingOpen ) {
					this.ctaHardHidden = true;
					this.syncStateClasses();
				}
				break;
			case 'base_navigate':
				this.applyEvent( 'widget_click' );
				this.openWhatsApp();
				break;
			default:
				break;
		}
	}

	/**
	 * Advances preview visibility state based on user interaction or setting changes.
	 *
	 * @param {string} event Event name.
	 */
	applyEvent ( event ) {
		const prev = this.uiState;
		this.uiState = nextState( prev, event );
		this.persistState();

		this.optinGateActive = ( event === 'optin_change' );
		this.transientNote = noteForTransition( prev, this.uiState );

		if ( prev.greetingOpen && ! this.uiState.greetingOpen ) {
			this.greetingsBox.style.display = 'none';
			this.greetingVisible = false;
		}

		this.syncStateClasses();
		if ( this.enabled ) { this.renderDebounced(); }
	}

	/**
	 * Persists preview state to storage.
	 */
	persistState () {
		storageFromState( this.uiState )
			.forEach( ( [ key, value ] ) => this.app.storage.setCtcStorageItem( key, value ) );
	}

	/**
	 * Checks whether greetings dialog is configured for the active device.
	 *
	 * @returns {boolean} True if greetings dialog is enabled.
	 */
	greetingEnabled () {
		const templateId = String( this.values.get( 'ht_ctc_greetings_options', 'greetings_template' ) || '' );
		if ( templateId === '' || templateId === 'no' ) { return false; }

		const gDevice = String( this.values.get( 'ht_ctc_greetings_settings', 'g_device' ) || 'all' );
		return greetingsAppliesTo( this.device, gDevice );
	}

	/**
	 * Checks whether greetings opt-in is enabled.
	 *
	 * @returns {boolean} True if opt-in is enabled.
	 */
	optinEnabled () {
		return ( this.values.get( 'ht_ctc_greetings_settings', 'is_opt_in' ) || '' ) !== '';
	}

	/**
	 * Opens WhatsApp URL based on configured settings.
	 *
	 * @param {string} [overrideNumber] Optional phone number override.
	 * @param {string} [overridePreFilled] Optional pre-filled text override.
	 */
	openWhatsApp ( overrideNumber = '', overridePreFilled = undefined ) {
		const target = this.values.get( 'ht_ctc_chat_options', 'url_target_d' ) || '_blank';
		if ( target === '_self' ) {
			this.transientNote = NOTES.sameTab;
		} else {
			const prefix = this.resolvePreFilledPrefix();
			const preFilled = overridePreFilled !== undefined ?
				prefix + overridePreFilled :
				( prefix !== '' ? prefix : this.values.get( 'ht_ctc_chat_options', 'pre_filled' ) );
			const url = buildWhatsAppUrl( {
				number: overrideNumber || this.resolveNumber(),
				preFilled: this.applyMessageVariables( preFilled ),
				urlStructure: this.values.get( 'ht_ctc_chat_options', 'url_structure_d' ),
				customUrl: this.values.get( 'ht_ctc_chat_options', 'custom_url_d' ),
			} );
			if ( url ) {
				this.transientNote = '';

				const features = ( target === 'popup' ) ?
					'scrollbars=no,resizable=no,status=no,location=no,' +
						'toolbar=no,menubar=no,width=788,height=514,left=100,top=100' :
					'noopener';
				window.open( url, target, features );
			} else {
				this.transientNote = NOTES.noNumber;
			}
		}
		if ( this.enabled ) { this.renderDebounced(); }
	}

	/**
	 * Synchronizes container CSS state classes with current UI state.
	 */
	syncStateClasses () {
		if ( ! this.container ) { return; }
		const badgeHidden = this.uiState.badgeStopped || this.greetingVisible;
		this.container.classList.toggle( 'ctc-state-badge-hidden', badgeHidden );
		this.container.classList.toggle( 'ctc-state-greeting-open', this.greetingVisible );
		this.container.classList.toggle( 'ctc-cta-hidden', this.ctaHardHidden );
		this.container.classList.toggle( 'ctc-state-optin-show', this.optinGateActive && ! this.uiState.optinDone );
	}

	bindToggle () {
		this.toggle = document.getElementById( 'ctc-preview-toggle' );
		this.note = document.getElementById( 'ctc-preview-note' );
		this.sidebar = document.querySelector( '.right-sidebar' );

		if ( ! this.toggle ) { return; }

		this.toggle.addEventListener( 'change', () => this.setEnabled( this.toggle.checked ) );
		this.bindDismiss();
	}

	/**
	 * Checks whether an active toast notification covers the preview.
	 *
	 * @returns {boolean} True if toast overlaps the preview.
	 */
	toastCoversPreview () {
		return this.toastVisible && ! this.siteView?.isOpen;
	}

	/**
	 * Toggles the preview on or off and persists preference.
	 *
	 * @param {boolean} enabled Whether preview is enabled.
	 */
	setEnabled ( enabled ) {
		this.enabled = enabled;
		this.autoRevealPending = false;
		if ( this.toggle ) { this.toggle.checked = enabled; }
		this.app.storage.setCtcStorageItem( STORAGE_KEY, enabled ? 'on' : 'off' );

		if ( enabled ) {
			this.render();
		} else {
			this.container.style.display = 'none';
		}
	}

	/**
	 * Checks whether right sidebar preview controls are reachable.
	 *
	 * @returns {boolean}
	 */
	controlsReachable () {
		return Boolean( this.sidebar?.offsetParent );
	}

	/**
	 * Binds Escape key to close the preview when sidebar controls are unreachable.
	 */
	bindDismiss () {
		document.addEventListener( 'keydown', ( event ) => {
			if ( 'Escape' !== event.key ) { return; }
			if ( ! this.enabled || this.siteView?.isOpen ) { return; }
			if ( this.controlsReachable() ) { return; }

			this.setEnabled( false );
		} );
	}

	/**
	 * Binds desktop / mobile device switch controls.
	 */
	bindDevice () {
		this.deviceInputs = Array.from( document.querySelectorAll( 'input[data-ctc-device]' ) );
		this.syncDeviceInputs();

		this.deviceInputs.forEach( ( input ) => {
			input.addEventListener( 'change', () => {
				if ( input.checked ) { this.setDevice( input.value ); }
			} );
		} );
	}

	/**
	 * Registers additional device switch inputs (e.g. from site view toolbar).
	 *
	 * @param {HTMLElement} scope Element containing input[data-ctc-device].
	 */
	adoptDeviceInputs ( scope ) {
		if ( ! scope ) { return; }

		Array.from( scope.querySelectorAll( 'input[data-ctc-device]' ) )
			.filter( ( input ) => ! this.deviceInputs.includes( input ) )
			.forEach( ( input ) => {
				this.deviceInputs.push( input );
				input.addEventListener( 'change', () => {
					if ( input.checked ) { this.setDevice( input.value ); }
				} );
			} );

		this.syncDeviceInputs();
	}

	syncDeviceInputs () {
		this.deviceInputs.forEach( ( input ) => {
			input.checked = normalizeDevice( input.value ) === this.device;
		} );
	}

	/**
	 * Switches the previewed device (desktop or mobile).
	 *
	 * @param {string} device 'desktop' | 'mobile'
	 */
	setDevice ( device ) {
		const next = normalizeDevice( device );
		if ( next === this.device ) { return; }

		this.device = next;
		this.syncDeviceInputs();

		const gInit = String( this.values.get( 'ht_ctc_greetings_settings', 'g_init' ) || '' );
		this.transientNote = '';

		if ( gInit === 'default' ) {
			const opens = autoOpensGreetings( next, gInit );
			this.uiState = { ...this.uiState, greetingOpen: opens };

			if ( ! opens && this.greetingEnabled() ) {
				this.transientNote = 'On mobile, "Open by default" greetings stay closed until the visitor taps the widget.';
			}
		}

		this.ctaHardHidden = false;
		this.siteView?.setDevice( next );

		if ( this.enabled ) { this.render(); }
	}

	/**
	 * Initializes the "View on my site" overlay integration.
	 */
	bindSiteView () {
		const button = document.getElementById( 'ctc-preview-site-view' );
		const url = this.app.config?.preview?.homeUrl || '';

		if ( ! button || ! url ) {
			button?.remove();
			return;
		}

		this.siteView = new SiteView( {
			url,
			onOpen: () => {
				this.mountPreview();
				this.adoptDeviceInputs( this.siteView?.root );
				this.setDevice( this.lastSiteViewDevice );
				this.siteView?.setDevice( this.device );
				this.syncDeviceInputs();

				if ( ! this.enabled ) {
					this.enabled = true;
					if ( this.toggle ) { this.toggle.checked = true; }
					this.autoRevealPending = false;
				}
				this.render();
			},

			onClose: () => {
				this.lastSiteViewDevice = this.device;
				this.mountPreview();
				this.setDevice( DESKTOP );
				if ( this.enabled ) { this.render(); }
			},
		} );

		button.addEventListener( 'click', () => this.siteView.open() );
	}

	/**
	 * Reveals preview on first edit if not explicitly toggled off.
	 */
	maybeAutoReveal () {
		if ( ! this.autoRevealPending ) { return; }
		if ( ! this.controlsReachable() ) { return; }

		this.autoRevealPending = false;
		this.enabled = true;
		if ( this.toggle ) { this.toggle.checked = true; }

		document.dispatchEvent( new CustomEvent( 'ctc_open_sidebar_tab', {
			detail: { tab: 'preview' },
		} ) );
	}

	bindFormEvents () {
		const onFieldChange = ( name ) => {
			this.maybeAutoReveal();

			const fieldNote = this.resolveFieldNote( name );

			if ( name.includes( 'style_mobile' ) ) {
				this.setDevice( MOBILE );
			} else if ( name.includes( 'style_desktop' ) ) {
				this.setDevice( DESKTOP );
			}

			this.ctaHardHidden = false;

			const stateEvent = eventForField( name );
			if ( stateEvent ) {
				this.applyEvent( stateEvent );
				if ( fieldNote ) { this.transientNote = fieldNote; }
				return;
			}

			this.transientNote = fieldNote;
			if ( this.enabled ) { this.renderDebounced(); }
		};

		const onFormEvent = ( event ) => {
			const name = event?.target?.name || '';
			if ( ! name.startsWith( 'ht_ctc_' ) ) { return; }
			onFieldChange( name );
		};

		this.form.addEventListener( 'input', onFormEvent );
		this.form.addEventListener( 'change', onFormEvent );

		this.app.events?.on( 'field:dirty', ( payload ) => {
			onFieldChange( payload?.name || payload?.target?.name || '' );
		} );
	}

	/**
	 * Binds toast show/hide events to temporarily hide preview if overlapping.
	 */
	bindToastEvents () {
		const events = this.app.events;
		if ( ! events ) { return; }

		events.on( 'toast:show', () => {
			this.toastVisible = true;
			if ( this.enabled && this.container && this.toastCoversPreview() ) {
				this.container.style.display = 'none';
			}
		} );

		events.on( 'toast:hidden', () => {
			this.toastVisible = false;
			if ( this.enabled ) { this.render(); }
		} );
	}

	/**
	 * Validates and formats a CSS length value.
	 *
	 * @param {string} value    Input value.
	 * @param {string} fallback Fallback value.
	 * @returns {string} Validated length string.
	 */
	cssLength ( value, fallback = '15px' ) {
		const length = String( value ?? '' )
			.trim();

		return CSS_LENGTH.test( length ) ? length : fallback;
	}

	/**
	 * Applies positioning styles to the preview container based on active device settings.
	 *
	 * @returns {{ side1: string, side2: string }} Position sides.
	 */
	applyPosition () {
		const read = ( key ) => this.values.get( 'ht_ctc_chat_options', key );
		const pos = resolvePosition( this.device, read, this.sameSettings() );

		const side1 = pos.side1 === 'top' ? 'top' : 'bottom';
		const side2 = pos.side2 === 'left' ? 'left' : 'right';
		const side1Value = this.cssLength( pos.side1Value );
		const side2Value = this.cssLength( pos.side2Value );

		const style = this.container.style;
		style.top = '';
		style.bottom = '';
		style.left = '';
		style.right = '';

		style.setProperty( side1, side1Value );
		style.setProperty( side2, side2Value );

		style.transformOrigin = `${side1} ${side2}`;
		style.transform = '';

		return { side1, side2 };
	}

	/**
	 * Sets the contextual preview note.
	 *
	 * @param {string} message Note message text.
	 */
	setNote ( message ) {
		const text = message || '';

		this.siteView?.setPreviewNote( text );

		if ( ! this.note ) { return; }

		const changed = this.note.textContent !== text;
		this.note.textContent = text;

		if ( text && changed && this.initialRenderDone ) {
			document.dispatchEvent( new CustomEvent( 'ctc_open_sidebar_tab', {
				detail: { tab: 'preview' },
			} ) );
		}
	}

	/**
	 * Checks whether shared desktop/mobile settings are enabled.
	 *
	 * @returns {boolean}
	 */
	sameSettings () {
		return Boolean( this.values.get( 'ht_ctc_chat_options', 'same_settings' ) );
	}

	/**
	 * Resolves active style ID for the previewed device.
	 *
	 * @returns {string} Style ID.
	 */
	currentStyleId () {
		const desktop = String( this.values.get( 'ht_ctc_chat_options', 'style_desktop' ) || '4' );
		const key = styleKeyFor( this.device, this.sameSettings() );
		if ( key === 'style_desktop' ) { return desktop; }

		return String( this.values.get( 'ht_ctc_chat_options', key ) || desktop );
	}

	/**
	 * Renders the preview widget and greetings dialog.
	 */
	async render () {
		if ( ! this.container ) { return; }

		const styleId = this.currentStyleId();

		const read = ( group, key ) => this.values.get( group, key );
		this.container.classList.toggle(
			'ctc-mobile-w-fullwidth',
			widgetFullWidthOn( this.device, styleId, read ),
		);

		const renderFn = await this.registry.getStyleRenderer( styleId );

		if ( ! this.enabled ) { return; }

		const { side1, side2 } = this.applyPosition();
		const ctx = this.buildContext( side2 );
		const notes = [];

		// Check device visibility setting and note if hidden.
		if ( ! widgetShowsOn( this.device, ( key ) => this.values.get( 'ht_ctc_chat_options', key ) ) ) {
			notes.push( isMobile( this.device ) ?
				'Hidden on mobile on your live site — Display settings have "Display on mobile" set to hide.' :
				'Hidden on desktop on your live site — Display settings have "Display on desktop" set to hide.' );
		}

		if ( ! renderFn ) {
			const fallbackStyle = 'background:#fff;border:1px solid #dcdcde;' +
				'border-radius:6px;padding:8px 12px;font-size:12px;color:#50575e;' +
				'box-shadow:0 1px 4px rgba(0,0,0,.12);';
			// eslint-disable-next-line no-unsanitized/property -- Static markup; style id is escaped
			this.stage.innerHTML = `<div style="${fallbackStyle}">` +
				`${escapeHTML( `No live preview yet for Style ${styleId}` )}</div>`;
			notes.push( `Live preview is not yet available for Style ${styleId}.` );
		} else {
			try {
				const rendered = renderFn( ctx );
				const html = ( typeof rendered === 'string' ) ? rendered : rendered.html;
				const note = ( typeof rendered === 'string' ) ? '' : ( rendered.note || '' );

				// eslint-disable-next-line no-unsanitized/property -- Templates escape all dynamic values (escapeHTML/escapeAttr/escapeCssValue)
				this.stage.innerHTML = notificationBadgeHtml( this.values ) + html;
				applyBadgeOffset( this.stage );
				if ( note ) { notes.push( note ); }
			} catch ( error ) {
				log( 'Preview', `Render failed for style ${styleId}`, error );
				this.container.style.display = 'none';
				return;
			}
		}

		const greetingsNote = await this.renderGreetings( ctx, side2, side1 );
		if ( greetingsNote ) { notes.push( greetingsNote ); }

		// Open greetings dialog dismisses the notification badge.
		if ( this.greetingVisible && ! this.uiState.badgeStopped ) {
			this.uiState.badgeStopped = true;
			this.persistState();
		}

		this.syncStateClasses();

		const renderNote = notes.join( ' ' );
		this.setNote( renderNote || this.transientNote );

		this.container.style.display = this.toastCoversPreview() ? 'none' : 'block';

		this.container.title = this.controlsReachable() ?
			'Click to Chat — preview' :
			'Click to Chat — preview (press Esc to hide)';

		if ( this.container.style.display === 'block' ) {
			this.fitToBounds();
		}

		// Dispatch post-render hook for extensions.
		document.dispatchEvent( new CustomEvent( 'ctc_preview_rendered', {
			detail: { stage: this.stage, container: this.container },
		} ) );
	}

	/**
	 * Scales container down if it exceeds the viewport bounds.
	 */
	fitToBounds () {
		const el = this.container;
		if ( ! el ) { return; }

		if ( el.classList.contains( 'ctc-mobile-w-fullwidth' ) ) {
			el.style.transform = '';
			return;
		}

		const naturalWidth = el.offsetWidth;
		const naturalHeight = el.offsetHeight;
		if ( ! naturalWidth || ! naturalHeight ) { return; }

		const bounds = ( this.siteView?.isOpen && this.siteView.viewport ) ?
			this.siteView.viewport.getBoundingClientRect() :
			{ width: window.innerWidth, height: window.innerHeight };

		const maxWidth = Math.min( bounds.width * 0.45, 360 );
		const maxHeight = Math.min( bounds.height * 0.55, 360 );

		const scale = Math.min( 1, maxWidth / naturalWidth, maxHeight / naturalHeight );
		if ( scale < 1 ) {
			el.style.transform = `scale(${scale})`;
		}
	}

	/**
	 * Builds template render context.
	 *
	 * @param {string} side2 'left' | 'right' side anchor.
	 * @returns {Object} Template render context.
	 */
	buildContext ( side2 ) {
		return {
			cta: String( this.values.get( 'ht_ctc_chat_options', 'call_to_action' ) ?? '' ),
			side2,
			isRtl: document.documentElement.dir === 'rtl',
			device: this.device,
			site: this.app.config?.preview?.site || '',
			wpTzOffset: Number( this.app.config?.preview?.wpTzOffset ) || 0,
			pluginUrl: this.app.config?.paths?.plugin_url || '',
			proPluginUrl: this.app.config?.paths?.pro_plugin_url || '',
			value: ( group, key ) => this.values.get( group, key ),
			groupValue: ( group ) => this.values.groupValues( group ),
			esc: { attr: escapeAttr, html: escapeHTML, css: escapeCssValue },
			parts: { ...greetingsParts, singleColorIcon, logoIcon, squareIcon },
		};
	}

	/**
	 * Renders live style previews in style picker grid cells.
	 *
	 * @returns {Promise<void>}
	 */
	async enhanceStyleGrids () {
		const selector = '.grid-widget-preview[data-style-id]';
		const cells = STYLE_GRID_TABS
			.map( ( tabId ) => document.getElementById( tabId ) )
			.filter( Boolean )
			.flatMap( ( panel ) => Array.from( panel.querySelectorAll( selector ) ) );

		if ( ! cells.length ) { return; }

		const ctx = this.buildContext( 'right' );
		await Promise.all( cells.map( ( cell ) => this.renderStyleGridCell( cell, ctx ) ) );
	}

	/**
	 * Renders a single style template into a grid cell.
	 *
	 * @param {HTMLElement} cell The `.grid-widget-preview` element.
	 * @param {Object}      ctx  Shared template render context.
	 * @returns {Promise<void>}
	 */
	async renderStyleGridCell ( cell, ctx ) {
		const styleId = cell.getAttribute( 'data-style-id' );
		if ( ! styleId || ! this.registry.hasStyle( styleId ) ) { return; }

		const renderFn = await this.registry.getStyleRenderer( styleId );
		if ( typeof renderFn !== 'function' ) { return; }

		let html;
		try {
			const rendered = renderFn( ctx );
			html = ( typeof rendered === 'string' ) ? rendered : rendered.html;
		} catch ( error ) {
			log( 'Preview', `Grid cell render failed for style ${styleId}`, error );
			return;
		}

		const stage = document.createElement( 'div' );
		stage.className = 'ht_ctc_style ht_ctc_chat_style';
		// eslint-disable-next-line no-unsanitized/property -- Templates escape all dynamic values (escapeHTML/escapeAttr/escapeCssValue)
		stage.innerHTML = html;

		// Remove style block IDs to avoid duplicate element IDs.
		stage.querySelectorAll( 'style[id]' )
			.forEach( ( node ) => node.removeAttribute( 'id' ) );

		// Ensure SVG IDs are unique per grid cell.
		uniquifySvgIds( stage, `-cg${++this.gridUidCounter}` );

		cell.replaceChildren( stage );
		cell.classList.add( 'has-live-preview' );
	}

	/**
	 * Scales a rendered grid cell to fit within its container.
	 *
	 * @param {HTMLElement} cell The `.grid-widget-preview` element.
	 */
	fitGridCell ( cell ) {
		const stage = cell.firstElementChild;
		if ( ! stage ) { return; }

		const availableWidth = cell.clientWidth - 4;
		const availableHeight = cell.clientHeight - 4;
		if ( availableWidth <= 0 || availableHeight <= 0 ) { return; }

		stage.style.transform = '';
		const naturalWidth = stage.offsetWidth;
		const naturalHeight = stage.offsetHeight;
		if ( ! naturalWidth || ! naturalHeight ) { return; }

		const scale = Math.min( 1, availableWidth / naturalWidth, availableHeight / naturalHeight );
		if ( scale < 1 ) {
			stage.style.transform = `scale(${scale})`;
		}
	}

	/**
	 * Observes a grid cell container for resize events to re-fit content.
	 *
	 * @param {HTMLElement} cell The `.grid-widget-preview` element.
	 */
	observeGridCell ( cell ) {
		if ( cell.dataset.ctcFitObserved || typeof ResizeObserver !== 'function' ) { return; }
		cell.dataset.ctcFitObserved = '1';

		if ( ! this.gridFitObserver ) {
			this.gridFitObserver = new ResizeObserver( ( entries ) => {
				entries.forEach( ( entry ) => this.fitGridCell( entry.target ) );
			} );
		}
		this.gridFitObserver.observe( cell );
	}

	/**
	 * Renders greetings dialog template.
	 *
	 * @param {Object} ctx   Shared template render context.
	 * @param {string} side2 'left' | 'right' side anchor.
	 * @param {string} side1 'top' | 'bottom' anchor.
	 * @returns {Promise<string>} Note text, or empty string.
	 */
	async renderGreetings ( ctx, side2, side1 = 'bottom' ) {
		const templateId = String( this.values.get( 'ht_ctc_greetings_options', 'greetings_template' ) || '' );
		const disabled = templateId === '' || templateId === 'no';

		const gDevice = String( this.values.get( 'ht_ctc_greetings_settings', 'g_device' ) || 'all' );
		const appliesToDevice = greetingsAppliesTo( this.device, gDevice );

		if ( disabled || ! appliesToDevice || ! this.uiState.greetingOpen ) {
			this.greetingsBox.style.display = 'none';
			this.greetingVisible = false;
			this.container.classList.remove( 'ctc-mobile-g-fullwidth', 'ctc-g-side-left', 'ctc-g-side-right' );

			if ( ! disabled && ! appliesToDevice ) {
				return isMobile( this.device ) ?
					'Greetings is set to desktop only, so it does not show on mobile.' :
					'Greetings is set to mobile only, so it does not show on desktop.';
			}
			return '';
		}

		const renderFn = await this.registry.getGreetingRenderer( templateId );
		if ( ! this.enabled ) { return ''; }

		if ( ! renderFn ) {
			this.greetingsBox.style.display = 'none';
			this.greetingVisible = false;
			return `Live preview is not yet available for the "${templateId}" greetings template.`;
		}

		const gSize = this.values.get( 'ht_ctc_greetings_settings', 'g_size' ) || 's';
		let minWidth = '300px';
		if ( gSize === 'm' ) { minWidth = '330px'; } else if ( gSize === 'l' ) { minWidth = '360px'; }

		const gPosition = String( this.values.get( 'ht_ctc_greetings_settings', 'g_position' ) || '' );
		const gFullWidth = greetingsFullWidthOn( this.device, String( gSize ), gPosition );
		this.container.classList.toggle( 'ctc-mobile-g-fullwidth', gFullWidth );
		this.container.classList.toggle( 'ctc-g-side-left', gFullWidth && side2 === 'left' );
		this.container.classList.toggle( 'ctc-g-side-right', gFullWidth && side2 !== 'left' );

		const closeSide = ctx.isRtl ? 'left' : 'right';

		try {
			const html = renderFn( ctx );

			const boxLayoutStyles = 'max-height:84vh;overflow-y:auto;' +
				'box-shadow:0px 1px 9px 0px rgba(0,0,0,.14);border-radius:8px;clear:both;';

			const closeBtnStyles = 'position:absolute;top:0;' +
				`${closeSide}:0;cursor:pointer;padding:5px;margin:4px;border-radius:50%;` +
				'background-color:unset !important;z-index:9999;line-height:1;';

			const svgStyles = 'color:lightgray;background-color:unset !important;border-radius:50%;';

			const pathD = 'M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708' +
				'L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647' +
				'a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z';

			// eslint-disable-next-line no-unsanitized/property -- Templates escape all dynamic values; rich-text content is kses-sanitized on save
			this.greetingsBox.innerHTML = '<div class="ht_ctc_chat_greetings_box_layout" ' +
				`style="${boxLayoutStyles}">
				<span class="ctc_greetings_close_btn" style="${closeBtnStyles}">
					<svg style="${svgStyles}" xmlns="http://www.w3.org/2000/svg" ` +
						`width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
						<path d="${pathD}"/>
					</svg>
				</span>
				<div class="ctc_greetings_template template-${escapeAttr( templateId )}">` +
					`${html}</div>
			</div>`;

			this.greetingsBox.classList.remove( 'template-greetings-1', 'template-greetings-2' );
			this.greetingsBox.classList.add( `template-${templateId}` );
			this.greetingsBox.style.minWidth = minWidth;
			this.greetingsBox.style.left = '';
			this.greetingsBox.style.right = '';
			this.greetingsBox.style.setProperty( side2, '0px' );

			// Position dialog above or below widget based on vertical anchor.
			this.greetingsBox.style.top = '';
			this.greetingsBox.style.bottom = '';
			this.greetingsBox.style.setProperty(
				side1 === 'top' ? 'top' : 'bottom',
				'calc(100% + 12px)',
			);

			this.greetingsBox.style.display = 'block';
			this.greetingVisible = true;
		} catch ( error ) {
			log( 'Preview', `Greetings render failed for ${templateId}`, error );
			this.greetingsBox.style.display = 'none';
			this.greetingVisible = false;
		}

		return '';
	}
}

