import { safeQuery } from '../core/Utils.js';

/**
 * Conditional Logic for Fields
 *
 * Manages visibility of fields based on the state of other "controller" fields.
 *
 * Concepts:
 * - Controller: The field that determines visibility (e.g., a checkbox or select).
 * - Dependent: The field or section that is shown/hidden based on the Controller.
 *
 * Attributes on Dependent Elements:
 * - `data-watch`: The ID of the Controller field to watch.
 * - `data-show-when`: Value(s) that cause this element to be SHOWN.
 * - `data-hide-when`: Value(s) that cause this element to be HIDDEN (Overrides show).
 *
 * Examples:
 * 1. Checkbox Toggle:
 *    - Controller: <input type="checkbox" id="feature_flag">
 *    - Dependent: <div data-watch="feature_flag" data-show-when="1">...</div>
 *    - Logic: Show when checked.
 *
 * 2. Select/Radio Choice:
 *    - Controller: <select id="theme"><option value="dark">Dark</option><option value="light">Light</option></select>
 *    - Dependent: <div data-watch="theme" data-show-when="dark">...</div>
 *    - Logic: Show only when 'dark' is selected.
 *
 * 3. Specific Checkbox Value:
 *    - Controller: <input type="checkbox" id="g_an" value="ga4">
 *    - Dependent: <div data-watch="g_an" data-show-when="ga4">...</div>
 *    - Logic: Show when checkbox is checked AND value is 'ga4'.
 *
 * 4. Multi-Watch & Show On Change:
 *    - Controllers: <input type="checkbox" id="enable_group"> and <input type="checkbox" id="enable_share">
 *    - Dependent: <div data-watch="enable_group, enable_share" data-show-on-change="true">...</div>
 *    - Logic: Monitors both inputs. It records their initial values when the page loads, and shows the dependent element only if ANY of the watched inputs are changed by the user.
 */

/**
 * Updates the visibility of a dependent element based on the controller's state.
 *
 * @param {Array<string>} controllerIds - The IDs strings of the input/select element controlling visibility.
 * @param {string|null} showValues - 'data-show-when' values (comma-separated).
 * @param {HTMLElement} conditionalSection - The dependent element to show/hide.
 * @param {string|null} hideValues - 'data-hide-when' values (comma-separated).
 */
export const updateConditionalVisibility = (
	controllerIds,
	showValues,
	conditionalSection,
	hideValues = null,
) => {
	// Special Case: "Show On Change" mode
	// Evaluates ALL controller IDs to see if ANY have changed from their initial loaded value.
	if ( conditionalSection.getAttribute( 'data-show-on-change' ) === 'true' ) {
		let hasChanged = false;
		controllerIds.forEach( id => {

			// const field = document.getElementById( id );
			// safeQuery: `id` is one comma-separated piece of a PHP-declared
			// data-watch, so a malformed one must not throw out of this loop.
			const field = safeQuery( document, id, 'data-watch' );

			// console.log('field: ', field);

			if ( ! field || field.dataset.ctcInitVal === undefined ) { return; }
			const currentVal = field.type === 'checkbox' ? ( field.checked ? '1' : '0' ) : field.value;
			if ( field.dataset.ctcInitVal !== currentVal ) {
				hasChanged = true;
			}
		} );
		conditionalSection.classList.toggle( 'ctc_init_display_none', ! hasChanged );
		return;
	}

	// Standard Mode: Uses the FIRST controller ID for standard logic (backwards compatibility).
	// const controllerField = document.getElementById( controllerIds[ 0 ] );
	const controllerField = safeQuery( document, controllerIds[ 0 ], 'data-watch' );

	// console.log('controllerField: ', controllerField);

	if ( ! controllerField ) { return; }

	// Default: Assume visible unless specific conditions fail.
	// However, if 'showValues' is present, we start hidden until matched.
	let shouldShow = showValues ? false : true;

	// Helper to parse comma-separated values
	const parseValues = ( valString ) => valString ?
		valString.split( ',' )
			.map( value => value.trim() ) :
		[];

	const currentVal = getFieldValue( controllerField );

	// 1. Handle "Show When" Logic
	// If the current value is in the 'showValues' list, show it.
	if ( showValues ) {
		const allowedValues = parseValues( showValues );

		// Special keyword: '!empty' means "show when field has a non-empty value".
		// Used for the image upload field: show dependent fields when an image URL is present.
		if ( allowedValues.includes( '!empty' ) ) {
			shouldShow = !! currentVal && currentVal.trim() !== '';
		} else if ( controllerField.type === 'checkbox' ) {
			// For Checkboxes:
			// 1. If not checked -> Value is effectively '0' or false.
			// 2. If checked -> Value is the input's value attribute (often '1' or a specific string like 'ga4').
			if ( ! controllerField.checked ) {
				// Not checked. Only show if '0' is explicitly allowed (rare case for "show when unchecked").
				if ( allowedValues.includes( '0' ) ) {
					shouldShow = true;
				}
			} else {
				// Checked. Show if '1' is allowed (generic "is checked") OR if specific value matches.
				if ( allowedValues.includes( '1' ) || allowedValues.includes( currentVal ) ) {
					shouldShow = true;
				}
			}
		} else {
			// For Radio, Select, Text, etc.
			// Show if current value matches any allowed value.
			if ( allowedValues.includes( currentVal ) ) {
				shouldShow = true;
			}
		}
	} else {
		// No strict 'show-when' rule, so default to showing
		// (unless 'hide-when' triggers below).
		shouldShow = true;
	}

	// 2. Handle "Hide When" Logic (Overrides Show)
	if ( hideValues ) {
		const forbiddenValues = parseValues( hideValues );
		let effectiveValue = currentVal;
		if ( controllerField.type === 'checkbox' && ! controllerField.checked ) {
			effectiveValue = '0';
		}
		if ( forbiddenValues.includes( effectiveValue ) ) {
			shouldShow = false;
		}
	}

	// Apply Visibility Class
	// 'ctc_init_display_none' is the CSS utility class for display: none.
	// Toggle: Add class (hide) if !shouldShow.
	conditionalSection.classList.toggle( 'ctc_init_display_none', ! shouldShow );
};

/**
 * Helper to get the current value of a field.
 * Handles Checkboxes vs standard inputs.
 */
const getFieldValue = ( field ) => {
	if ( field.type === 'checkbox' ) {
		// For checkboxes, we return the value attribute.
		// NOTE: Whether it's checked or not is handled in the main logic primarily.
		return field.value;
	} else if ( field.type === 'radio' ) {
		// If watching a radio group by name isn't supported by ID, this assumes ID watching.
		// If watching by ID (specific radio option), we usually want the value of the CHECKED option in that group.
		if ( field.name ) {
			const selector = `input[name="${CSS.escape( field.name )}"]:checked`;
			const checkedRadio = field.form ?
				field.form.querySelector( selector ) :
				document.querySelector( selector );
			return checkedRadio ? checkedRadio.value : '';
		}
		return field.value;
	}
	return field.value;
};

/**
 * Initialize Conditional Logic
 * Scans the provided context (or document) for elements with `data-watch` attributes.
 * Binds 'change' listeners to the controller fields.
 */
export const initConditionalFieldLogic = ( context = document ) => {
	const conditionalSections = context.querySelectorAll( '[data-watch]' );

	conditionalSections.forEach( conditionalSection => {
		const controllerIdsStr = conditionalSection.getAttribute( 'data-watch' );
		if ( ! controllerIdsStr ) { return; }

		const controllerIds = controllerIdsStr.split( ',' )
			.map( id => id.trim() );

		// Track if all watched controllers are present in the DOM.
		// If some are missing (e.g., in a tab not yet loaded), we'll skip the initial visibility update
		// but will re-scan and bind them when the tab eventually loads.
		let allFound = true;
		controllerIds.forEach( controllerId => {
			// Find the controller element (e.g. '#enable_group')
			const controllerField = safeQuery( document, controllerId, 'data-watch' );
			if ( ! controllerField ) {
				allFound = false;
				return;
			}

			// Prevent duplicate listeners:
			// Since initConditionalFieldLogic can be called multiple times (e.g., on every tab load),
			// we track which controllers have already been bound to this specific dependent section.
			const boundAttr = `data-ctc-bound-${controllerId.replace( /[^a-zA-Z0-9]/g, '_' )}`;
			if ( conditionalSection.hasAttribute( boundAttr ) ) { return; }

			const showValues = conditionalSection.getAttribute( 'data-show-when' );
			const hideValues = conditionalSection.getAttribute( 'data-hide-when' );
			const showOnChange = conditionalSection.getAttribute( 'data-show-on-change' ) === 'true';

			// Store Initial State for "Show On Change"
			if ( showOnChange && controllerField.dataset.ctcInitVal === undefined ) {
				controllerField.dataset.ctcInitVal = controllerField.type === 'checkbox' ? ( controllerField.checked ? '1' : '0' ) : controllerField.value;
			}

			// Bind Listeners

			// Special Handling for Radio Groups:
			// If 'controllerField' is a radio button, simply listening to IT only catches when IT is selected.
			// It doesn't catch when IT is deselected (because another sibling was selected).
			// So we must listen to ALL radios in the same group.
			if ( controllerField.type === 'radio' && controllerField.name ) {
				const radios = Array.from( document.getElementsByName( controllerField.name ) );
				radios.forEach( radio => {
					// We add listener to ALL radios in the group.
					// When ANY of them changes, we re-evaluate the visibility.
					radio.addEventListener( 'change', () => {
						updateConditionalVisibility(
							controllerIds,
							showValues,
							conditionalSection,
							hideValues,
						);
					} );
				} );
			} else {
				// Standard Single Field (Checkbox, Select, Text)
				controllerField.addEventListener( 'change', () => {
					updateConditionalVisibility(
						controllerIds,
						showValues,
						conditionalSection,
						hideValues,
					);
				} );
			}

			// Optional: support 'input' for real-time text matching
			if ( controllerField.type === 'text' || controllerField.type === 'number' ) {
				controllerField.addEventListener( 'input', () => {
					updateConditionalVisibility(
						controllerIds,
						showValues,
						conditionalSection,
						hideValues,
					);
				} );
			}

			// Mark this specific controller as bound to this section to avoid duplicate listeners on next scan
			conditionalSection.setAttribute( boundAttr, 'true' );
		} );

		// Initial Visibility Evaluation:
		// We only run this if ALL controllers are present in the DOM.
		// If any are missing, the section stays in its default state (often hidden via PHP)
		// until the missing controllers are loaded and bound during a future scan.
		if ( allFound ) {
			const showValues = conditionalSection.getAttribute( 'data-show-when' );
			const hideValues = conditionalSection.getAttribute( 'data-hide-when' );
			updateConditionalVisibility(
				controllerIds,
				showValues,
				conditionalSection,
				hideValues,
			);
		}
	} );
};
