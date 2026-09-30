/**
 * Contextual Settings Trigger
 *
 * The "Customize" control that opens an item's contextual settings panel: how the control
 * is built, how it resolves what it opens, and how its pressed state is reflected.
 *
 * The picker-facing half of the feature. ContextualPanel.js is the panel itself and knows
 * nothing about what opened it; this module is the only place that knows the two are
 * connected.
 *
 * ANY host, not just grids — a trigger declares what it opens through
 * `data-contextual-group` / `data-contextual-id`, on itself or on an ancestor.
 * `.grid-option` is named below only as a convenience.
 *
 * See dev/docs/developer-reference/admin2-contextual-panel.md.
 */
import {
	toggleContextualPanel,
	showContextualPanel,
	isContextualPanelOpen,
	closeContextualPanel,
} from './ContextualPanel.js';
import { applyConditionalAttributes, safeQuery, safeMatches } from '../../core/Utils.js';
import { appendHelpText } from '../fields/BaseField.js';

/** Bound once for the page, the first time triggers are initialized. */
let closeListenerBound = false;

/** What `data-contextual-watch` is, for the warning safeQuery/safeMatches log. */
const WATCH_ATTR = 'data-contextual-watch';

/**
 * Build the "Customize" control.
 *
 * The caller renders one on every tile that declares a contextual group and lets CSS
 * reveal it on the selected one only — so it is correct on first paint and after any
 * re-render, with no placement logic to run when the selection moves.
 *
 * type="button" is load-bearing: pickers render inside #ctc-settings-form, where a
 * default-type button submits the form.
 *
 * @returns {HTMLButtonElement} The trigger, not yet attached.
 */
export const createContextualTrigger = ( label = 'Customize' ) => {
	const trigger = document.createElement( 'button' );
	trigger.type = 'button';
	trigger.className = 'ctc-cx-trigger';
	trigger.setAttribute( 'aria-expanded', 'false' );

	// The one panel the page has. Safe to set before it exists — aria-controls naming a
	// not-yet-rendered element is simply ignored.
	trigger.setAttribute( 'aria-controls', 'ctc-contextual-panel' );

	const triggerIcon = document.createElement( 'span' );
	triggerIcon.className = 'dashicons dashicons-admin-customizer';
	triggerIcon.setAttribute( 'aria-hidden', 'true' );

	const triggerLabel = document.createElement( 'span' );
	triggerLabel.className = 'ctc-cx-trigger-label';
	triggerLabel.textContent = label;

	trigger.append( triggerIcon, triggerLabel );

	return trigger;
};

/**
 * Work out WHICH settings a trigger opens: the group to fetch, and the item within it.
 *
 * Both walk OUT from the trigger, independently — so the group can be declared once on a
 * parent while the id sits on the individual item, and a lone button can carry both.
 *
 * @param {HTMLElement} trigger The .ctc-cx-trigger that was pressed.
 * @returns {{contextualGroup: string, contextualId: string}} Empty when undeclared.
 */
const resolveContextualTarget = ( trigger ) => {
	const groupHost = trigger.closest( '[data-contextual-group]' );

	return {
		contextualGroup: groupHost ? groupHost.dataset.contextualGroup : '',
		contextualId: resolveContextualId( trigger ),
	};
};

/**
 * The item id a trigger currently points at.
 *
 * Three sources, in order:
 *
 *   watched field  `data-contextual-watch` names a control whose CHOSEN OPTION declares
 *                  the id, so no naming convention is needed between a stored value and
 *                  an item id (`7-1` can open `style_7_1`). Read at click time, so it
 *                  cannot go stale. An option declaring nothing falls back to its value.
 *   declared id    `data-contextual-id` on the trigger or any ancestor.
 *   grid fallback  a tile's own value, where each tile IS the item.
 *
 * @param {HTMLElement} trigger The .ctc-cx-trigger.
 * @returns {string} The item id, or '' when nothing resolves.
 */
const resolveContextualId = ( trigger ) => {
	const watchSelector = trigger.dataset.contextualWatch;

	if ( watchSelector ) {
		/*
		 * Own card first — field ids repeat across cards (`#cta_style` is in both
		 * greetings-1 and PRO's greetings), so a plain document lookup answers with
		 * whichever rendered first. `.ctc-contextual-card` is named alongside `.ctc-card`
		 * because a trigger rendered INSIDE the panel is also inside the PICKER's card.
		 * Document fallback keeps a trigger outside any card working.
		 */
		const scope = trigger.closest( '.ctc-contextual-card, .ctc-card' );
		const watched = ( scope && safeQuery( scope, watchSelector, WATCH_ATTR ) ) ||
			safeQuery( document, watchSelector, WATCH_ATTR );

		if ( ! watched ) { return ''; }

		const chosen = watched.selectedOptions ? watched.selectedOptions[ 0 ] : null;

		return ( chosen && chosen.dataset.contextualId ) || watched.value || '';
	}

	const idHost = trigger.closest( '[data-contextual-id]' );
	if ( idHost ) { return idHost.dataset.contextualId; }

	const tile = trigger.closest( '.grid-option' );

	return tile ? tile.getAttribute( 'data-value' ) || '' : '';
};

/**
 * The element the panel should open beneath: the whole item the trigger belongs to.
 *
 * A grid tile, so the drawer spans the row rather than splitting it. Otherwise the
 * field's form-group, so the panel lands under the control it customizes. The trigger
 * itself is the last resort.
 *
 * @param {HTMLElement} trigger The .ctc-cx-trigger that was pressed.
 * @returns {HTMLElement} The anchor.
 */
const resolveAnchor = ( trigger ) =>

	/*
	 * `.field-col` BEFORE `.form-group`, and that order is the whole point. The panel is a
	 * full-width drawer placed as a sibling of the anchor; `.field-col` is a nowrap flex
	 * row, so anchoring on the inner `.form-group` leaves the panel beside the trigger in
	 * the leftover width. Anchoring on the column puts it in block flow instead.
	 */
	trigger.closest( '.grid-option' ) ||
	trigger.closest( '.field-col' ) ||
	trigger.closest( '.form-group' ) ||
	trigger;

/**
 * One trigger's pressed state — the aria attribute and the icon that shows it.
 *
 * The trigger is the only way in AND the only way out, so it has to read as a disclosure
 * rather than a one-way action: pencil when it will open, up-arrow when pressing it will
 * collapse what is already open.
 *
 * @param {HTMLElement} trigger  A .ctc-cx-trigger.
 * @param {boolean}     expanded Whether its panel is open.
 */
const setTriggerState = ( trigger, expanded ) => {
	trigger.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );

	const icon = trigger.querySelector( '.dashicons' );
	if ( ! icon ) { return; }

	icon.classList.toggle( 'dashicons-admin-customizer', ! expanded );
	icon.classList.toggle( 'dashicons-arrow-up-alt2', expanded );
};

/**
 * Reflect the panel's state on every trigger on the page: at most one is ever pressed.
 *
 * Cleared document-wide, not per picker: the one panel moves between pickers, so opening
 * from the Mobile grid must un-press the Desktop grid's trigger.
 *
 * @param {HTMLElement|null} trigger  The trigger that now owns the panel, if any.
 * @param {boolean}          expanded Whether the panel ended up open.
 */
const syncTriggerState = ( trigger, expanded ) => {
	document.querySelectorAll( '.ctc-cx-trigger' )
		.forEach( button => setTriggerState( button, false ) );

	if ( trigger ) {
		setTriggerState( trigger, expanded );
	}
};

/**
 * Listen for closes the trigger did not cause.
 *
 * Escape closes the panel from anywhere inside it, with no trigger involved — so the
 * panel announces the close and un-pressing whatever was pressed is this module's job.
 */
export const initContextualTriggers = ( app ) => {
	if ( closeListenerBound ) { return; }
	closeListenerBound = true;

	document.addEventListener(
		'ctc_contextual_panel_closed',
		() => syncTriggerState( null, false ),
	);

	// A watched field changing is the same event as a grid selection changing: an OPEN
	// panel has to follow it rather than keep showing the item no longer chosen.
	document.addEventListener( 'change', async ( event ) => {
		if ( ! app || ! isContextualPanelOpen() ) { return; }

		const trigger = document.querySelector( '.ctc-cx-trigger[aria-expanded="true"][data-contextual-watch]' );
		if ( ! trigger ) { return; }

		// Only react to the field this trigger actually watches — and only that trigger's
		// own copy of it, since the selector may match several cards.
		const watchSelector = trigger.dataset.contextualWatch;
		if ( ! safeMatches( event.target, watchSelector, WATCH_ATTR ) ) { return; }

		// Same scoping as resolveContextualId(), for the same reason.
		const scope = trigger.closest( '.ctc-contextual-card, .ctc-card' );
		if ( scope && ! scope.contains( event.target ) ) { return; }

		await openFor( app, trigger, { toggle: false } );
	} );
};

/**
 * Open (or toggle) the panel a trigger points at, and reflect the result on it.
 *
 * Shared by the click handler and the watched-field listener so both routes resolve the
 * target the same way and leave the same trigger state behind.
 *
 * `toggle` is what separates them. A PRESS on the open item means close it. A watched
 * field moving to a value that happens to already be open is not a dismissal — the panel
 * should stay and show it.
 *
 * @param {Object}      app            Main App instance.
 * @param {HTMLElement} trigger        The .ctc-cx-trigger.
 * @param {Object}      options        Behaviour flags.
 * @param {boolean}     options.toggle Whether re-selecting the open item closes it.
 */
const openFor = async ( app, trigger, { toggle = true } = {} ) => {
	const anchor = resolveAnchor( trigger );
	const container = anchor.parentElement;
	if ( ! container ) { return; }

	const { contextualGroup, contextualId } = resolveContextualTarget( trigger );
	if ( ! contextualGroup || ! contextualId ) { return; }

	const open = toggle ? toggleContextualPanel : showContextualPanel;

	const expanded = await open(
		app,
		contextualGroup,
		contextualId,
		anchor,
		container,
	);

	syncTriggerState( trigger, expanded );
};

/**
 * Handle a click that may have landed on a trigger.
 *
 * Returns whether it was handled so the caller can stop. That matters: a trigger sits
 * inside the tile, which is itself a click target that syncs the picker's value — and
 * falling through would mark the form dirty every time the panel is merely opened. Once
 * the click is on a trigger it is ours whatever happens next, which is why every early
 * exit below still reports true.
 *
 * @param {Object} app   Main App instance.
 * @param {Event}  event The click event.
 * @returns {Promise<boolean>} True if the click was a trigger's and is fully handled.
 */
export const handleContextualTriggerClick = async ( app, event ) => {
	const trigger = event.target.closest( '.ctc-cx-trigger' );
	if ( ! trigger ) { return false; }

	event.preventDefault();

	if ( ! app ) { return true; }

	await openFor( app, trigger );

	return true;
};

/**
 * Move an open panel to a newly selected tile.
 *
 * Selecting a tile does not open its panel — that is the trigger's job — but an
 * ALREADY-OPEN panel has to follow the new selection.
 *
 * Shows rather than toggles: the same item id appears in more than one picker, so a
 * selection can land on the open item in a DIFFERENT grid, where toggling would close the
 * panel instead of moving it. A no-op when nothing is open, the common case.
 *
 * @param {Object}      app        Main App instance.
 * @param {HTMLElement} gridOption The tile that was just selected.
 * @param {HTMLElement} grid       Its .grid container.
 */
export const syncPanelToSelection = async ( app, gridOption, grid ) => {
	if ( ! app || ! isContextualPanelOpen() ) { return; }

	// No trigger means nothing customizable here (Disable, a locked tile). Close rather
	// than return, or the panel is stranded under a tile it does not describe, still
	// showing the previous item's fields.
	const trigger = gridOption.querySelector( '.ctc-cx-trigger' );
	if ( ! trigger ) {
		closeContextualPanel();
		return;
	}

	// Declared a trigger but nothing resolvable behind it — same reasoning as above.
	const { contextualGroup, contextualId } = resolveContextualTarget( trigger );
	if ( ! contextualGroup || ! contextualId ) {
		closeContextualPanel();
		return;
	}

	const expanded = await showContextualPanel(
		app,
		contextualGroup,
		contextualId,
		gridOption,
		grid,
	);

	syncTriggerState( trigger, expanded );
};

/**
 * field_type: block_contextual_trigger
 *
 * A standalone Customize control — the same button a grid tile carries, but on its own
 * row beside the field it customizes. For the PHP declaration, see
 * dev/docs/developer-reference/admin2-contextual-panel.md.
 *
 * @param {Object} field The field definition.
 * @returns {HTMLElement} A form-group wrapping the trigger.
 */
export const createContextualTriggerField = ( field ) => {
	const wrapper = document.createElement( 'div' );
	wrapper.className = `form-group ctc-cx-trigger-field ${field.class_pr || ''}`.trim();

	const trigger = createContextualTrigger( field.label || 'Customize' );

	if ( field.contextual_group ) {
		trigger.dataset.contextualGroup = field.contextual_group;
	}

	if ( field.contextual_watch ) {
		trigger.dataset.contextualWatch = field.contextual_watch;
	} else if ( field.contextual_id ) {
		trigger.dataset.contextualId = field.contextual_id;
	}

	wrapper.appendChild( trigger );

	applyConditionalAttributes( wrapper, field );

	// The shared one, not a local <p class="help-text">: it also handles a `help`
	// declared as an array, and `help_click`. Hand-rolling it here meant this was
	// the one field type where a help string with a doc link rendered as raw markup.
	appendHelpText( wrapper, field );

	return wrapper;
};
