/**
 * Generic Contextual Panel Component Renderer
 *
 * Renders an inline contextual settings panel drawer in the container after the active
 * row/element when any card, button, or option is selected.
 */
import { getSafeProperty } from '../../core/Utils.js';

/** Fade duration in ms; keep in step with the .ctc-contextual-panel transition. */
const FADE_MS = 250;

/** The single panel element, built on first open. Nothing in it changes per item. */
let panel = null;

/** `group:id` -> the card element holding that item's rendered fields. */
const cards = new Map();

/** Which `group:id` the panel is currently showing, or null when collapsed. */
let openKey = null;

/** Active parent container element where the panel is currently displayed. */
let openContainer = null;

let collapseTimer = null;

/** The Escape handler is bound once for the page, on the first open. */
let escapeBound = false;

/**
 * Toggle ID attributes on a card subtree to avoid duplicate IDs in the DOM.
 *
 * @param {Element} root Card element.
 * @param {boolean} park True to park IDs into data attributes, false to restore.
 */
const shiftIds = ( root, park ) => {
	const fromAttr = park ? 'id' : 'data-ctc-cx-id';
	const toAttr = park ? 'data-ctc-cx-id' : 'id';

	root.querySelectorAll( `[${fromAttr}]` )
		.forEach( el => {
			el.setAttribute( toAttr, el.getAttribute( fromAttr ) );
			el.removeAttribute( fromAttr );
		} );

	const fromFor = park ? 'for' : 'data-ctc-cx-for';
	const toFor = park ? 'data-ctc-cx-for' : 'for';

	root.querySelectorAll( `label[${fromFor}]` )
		.forEach( el => {
			el.setAttribute( toFor, el.getAttribute( fromFor ) );
			el.removeAttribute( fromFor );
		} );

	if ( park && root.id ) {
		root.setAttribute( 'data-ctc-cx-id', root.id );
		root.removeAttribute( 'id' );
	} else if ( ! park && root.hasAttribute( 'data-ctc-cx-id' ) ) {
		root.id = root.getAttribute( 'data-ctc-cx-id' );
		root.removeAttribute( 'data-ctc-cx-id' );
	}
};

/**
 * Build the panel container element once.
 *
 * @returns {Element} The panel.
 */
const buildPanel = () => {
	if ( panel ) { return panel; }

	panel = document.createElement( 'div' );
	panel.className = 'ctc-contextual-panel';
	panel.hidden = true;
	panel.id = 'ctc-contextual-panel';

	return panel;
};

/**
 * Render an item's fields into a card, once. Later opens reuse it.
 *
 * @param {Object} app          Main App instance.
 * @param {string} key          `group:id`.
 * @param {string} contextualId Item identifier, used as the card's DOM scope.
 * @param {Object} config       The item's definition from the group payload.
 * @returns {Element} The card element.
 */
const ensureCard = ( app, key, contextualId, config ) => {
	const existing = cards.get( key );
	if ( existing ) { return existing; }

	const card = document.createElement( 'div' );
	card.className = 'ctc-contextual-card';
	card.setAttribute( 'role', 'group' );
	card.dataset.contextualKey = key;
	card.hidden = true;
	card.id = contextualId;

	panel.appendChild( card );

	const headerEl = document.createElement( 'div' );
	headerEl.className = 'ctc-contextual-header';

	const titleEl = document.createElement( 'h3' );
	titleEl.id = `${contextualId}-title`;
	titleEl.textContent = config.title || contextualId;

	const descEl = document.createElement( 'p' );
	descEl.textContent = config.desc || '';
	descEl.hidden = ! config.desc;

	headerEl.append( titleEl, descEl );
	card.appendChild( headerEl );
	card.setAttribute( 'aria-labelledby', titleEl.id );

	const contentEl = document.createElement( 'div' );
	contentEl.className = 'ctc-contextual-content';

	const fragment = document.createDocumentFragment();

	config.fields.forEach( fieldConfig => {
		const el = app.createFieldElement( fieldConfig );
		if ( el ) {
			fragment.appendChild( el );
		}
	} );

	contentEl.appendChild( fragment );
	card.appendChild( contentEl );

	if ( config.note ) {
		const noteEl = document.createElement( 'p' );
		noteEl.className = 'ctc-contextual-note';

		const noteIcon = document.createElement( 'span' );
		noteIcon.className = 'dashicons dashicons-info-outline';
		noteIcon.setAttribute( 'aria-hidden', 'true' );

		const noteText = document.createElement( 'span' );
		noteText.textContent = config.note;

		noteEl.append( noteIcon, noteText );
		card.appendChild( noteEl );
	}

	shiftIds( card, true );
	cards.set( key, card );

	return card;
};

/**
 * Whether a card's ids are currently parked.
 *
 * Read from the DOM rather than tracked alongside `hidden`, because the two are
 * deliberately not in step: collapsing parks immediately but defers hiding until the fade
 * has run. A card is parked exactly when shiftIds() has moved its own id aside.
 *
 * @param {Element} card Card element.
 * @returns {boolean} True when parked.
 */
const isParked = ( card ) => card.hasAttribute( 'data-ctc-cx-id' );

/**
 * Show one card and park every other. Pass null to park them all.
 *
 * @param {Object}      app Main App instance.
 * @param {string|null} key Which card to show.
 */
const showOnly = ( app, key ) => {
	let shown = null;

	cards.forEach( ( card, cardKey ) => {
		const show = cardKey === key;

		if ( show ) { shown = card; }

		// Shift IDs on parked state transitions.
		if ( show === isParked( card ) ) { shiftIds( card, ! show ); }

		card.hidden = ! show;
	} );

	if ( ! shown ) { return; }

	// Initialize and evaluate conditional field logic for the shown card.
	app.utils.safeRun(
		() => app.utils.initConditionalFieldLogic( shown ),
		'Contextual panel conditional fields',
	);
};

/**
 * Get the column count of a CSS grid container from grid-template-columns.
 *
 * @param {HTMLElement} container Container element.
 * @returns {number} Column count, or 0 if not a grid.
 */
const columnCount = ( container ) => {
	const tracks = window.getComputedStyle( container ).gridTemplateColumns;

	if ( ! tracks || 'none' === tracks ) { return 0; }

	return tracks.trim()
		.split( /\s+/ )
		.filter( token => ! token.startsWith( '[' ) ).length;
};

/**
 * Finds the last element in the clicked element's row to anchor the panel after.
 *
 * @param {HTMLElement} container Parent container.
 * @param {HTMLElement} element   Clicked element.
 * @param {number}      columns   Grid column count.
 * @returns {HTMLElement} Anchor element.
 */
const rowAnchor = ( container, element, columns ) => {
	if ( columns > 0 ) {
		const items = Array.from( container.children )
			.filter( el => el !== panel );

		// Walk up to the direct container child if clicked inside a descendant.
		let item = element;
		while ( item && item.parentElement !== container ) { item = item.parentElement; }

		const index = item ? items.indexOf( item ) : -1;

		if ( index >= 0 ) {
			const endOfRow = ( Math.ceil( ( index + 1 ) / columns ) * columns ) - 1;
			return items[ Math.min( endOfRow, items.length - 1 ) ];
		}
	}

	const members = Array.from( container.querySelectorAll( '.grid-option, .ctc-card, .ctc-item' ) )
		.filter( el => el !== panel && ! panel.contains( el ) );
	const top = element.offsetTop;
	const row = members.filter( el => el.offsetTop === top );

	return row[ row.length - 1 ] || element;
};

/**
 * Moves the panel to sit after the last element of the clicked element's row.
 *
 * @param {HTMLElement} container Parent container.
 * @param {HTMLElement} element   Clicked element.
 */
const placePanel = ( container, element ) => {
	// Avoid re-nesting if trigger is inside the panel.
	if ( panel.contains( container ) ) { return; }

	// Remove before measuring row anchors so the panel itself is not counted.
	if ( panel.parentElement === container ) { panel.remove(); }

	rowAnchor( container, element, columnCount( container ) )
		.after( panel );
};

/**
 * Collapses the panel while preserving cards with unsaved edits.
 */
const collapse = () => {
	if ( ! panel ) { return; }

	openKey = null;
	openContainer = null;
	panel.classList.remove( 'is-open' );

	document.dispatchEvent( new CustomEvent( 'ctc_contextual_panel_closed' ) );

	// Park field IDs immediately.
	cards.forEach( card => {
		if ( ! isParked( card ) ) { shiftIds( card, true ); }
	} );

	clearTimeout( collapseTimer );
	collapseTimer = setTimeout( () => {
		if ( openKey ) { return; }

		panel.hidden = true;
		cards.forEach( card => { card.hidden = true; } );
	}, FADE_MS );
};

/**
 * Binds Escape key listener to close the open panel.
 */
const initEscapeGuard = () => {
	if ( escapeBound ) { return; }
	escapeBound = true;

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' !== event.key || ! openKey ) { return; }
		collapse();
	} );
};

/**
 * Opens the contextual panel for an item, placing it beneath the item's row.
 *
 * @param {Object}      app             Main App instance.
 * @param {string}      contextualGroup Group slug (e.g. 'contextual_styles').
 * @param {string}      contextualId    Item identifier (e.g. 'style_2').
 * @param {HTMLElement} element         Clicked DOM element.
 * @param {HTMLElement} container       Parent container element.
 * @returns {Promise<boolean>} True if opened successfully, false otherwise.
 */
export const showContextualPanel = async (
	app,
	contextualGroup,
	contextualId,
	element,
	container,
) => {
	if ( ! app || ! contextualGroup || ! contextualId || ! container || ! element ) {
		return false;
	}

	const key = `${contextualGroup}:${contextualId}`;

	let contextualGroupData = null;
	try {
		contextualGroupData = await app.getFieldsForGroup( contextualGroup );
	} catch ( error ) {
		console.error( 'CTC: Failed to fetch contextual fields', contextualGroup, error );
		collapse();
		return false;
	}

	// const contextualConfig = contextualGroupData[ contextualId ];
	const contextualConfig = getSafeProperty( contextualGroupData, contextualId, null );
	const fields = contextualConfig ? contextualConfig.fields : null;

	if ( ! fields || fields.length === 0 ) {
		collapse();
		return false;
	}

	buildPanel();
	initEscapeGuard();

	placePanel( container, element );

	ensureCard( app, key, contextualId, contextualConfig );
	showOnly( app, key );

	openKey = key;
	openContainer = container;

	clearTimeout( collapseTimer );
	panel.hidden = false;

	requestAnimationFrame( () => {
		if ( openKey === key ) {
			panel.classList.add( 'is-open' );
		}
	} );

	return true;
};

/**
 * Toggles the contextual panel for an item. Closes if already open for the container.
 *
 * @param {Object}      app             Main App instance.
 * @param {string}      contextualGroup Group slug.
 * @param {string}      contextualId    Item identifier.
 * @param {HTMLElement} element         Trigger element.
 * @param {HTMLElement} container       Parent container element.
 * @returns {Promise<boolean>} True if open, false if closed.
 */
export const toggleContextualPanel = async (
	app,
	contextualGroup,
	contextualId,
	element,
	container,
) => {
	const isOpenHere = openKey &&
		openKey === `${contextualGroup}:${contextualId}` &&
		openContainer === container;

	if ( isOpenHere ) {
		collapse();
		return false;
	}

	return showContextualPanel( app, contextualGroup, contextualId, element, container );
};

/**
 * Checks whether any contextual panel is currently open.
 *
 * @returns {boolean} True if open.
 */
export const isContextualPanelOpen = () => Boolean( openKey );

/**
 * Closes the contextual panel.
 */
export const closeContextualPanel = () => collapse();
