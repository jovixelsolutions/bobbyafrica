/**
 * Global Display Logic
 *
 * Handles the "Global Display" toggle (Show/Hide/Global) that affects other settings.
 * If 'Global' is selected, other controls might need to reflect that state visually.
 */

let isListenerAttached = false;

/**
 * Initialize Global Display Logic
 *
 * @param {HTMLElement} context - The container to scan for inputs (default: document)
 */
export const initDisplaySettings = ( context = document ) => {
	// 1. Initial State
	// Find all active (checked) inputs related to global display settings.
	const globalInputs = context.querySelectorAll( 'input[name*="[global_display]"]:checked' );

	globalInputs.forEach( input => {
		handleDisplaySettingsChange( input );
	} );

	// 2. Event Listener (Delegated)
	// Bind once to the document to handle all future changes.
	if ( ! isListenerAttached ) {
		document.addEventListener( 'change', ( event ) => {
			if ( event.target.name && event.target.name.includes( '[global_display]' ) ) {
				handleDisplaySettingsChange( event.target );
			}
		} );
		isListenerAttached = true;
	}
};

/**
 * Propagates the Global Display state to related options.
 * @param {HTMLElement} target - The changed input element.
 */
const handleDisplaySettingsChange = ( target ) => {
	const newValue = target.value; // e.g., 'show', 'hide'

	// Extract the prefix to identify the group.
	// Example Name: "ht_ctc_chat_options[display][global_display]"
	// Prefix: "ht_ctc_chat_options[display]"
	const prefix = target.name.split( '[global_display]' )[ 0 ];

	if ( ! prefix ) {
		return;
	}

	// Find all "Global" radio options (value="g") in the same group.
	// These specific radio buttons need to know the parent's global state
	// to perhaps style themselves (e.g. show "Global (Show)" vs "Global (Hide)").
	// Note: We search document-wide because the affected fields might be outside the context if the structure is complex,
	// but mostly they are siblings or cousins. Document search is safer for "Global" logic unless scoped strictly.
	const allGlobalOptions = document.querySelectorAll( 'input[type="radio"][value="g"]' );

	allGlobalOptions.forEach( input => {
		if ( input.name.startsWith( prefix ) ) {
			// Set a data attribute that CSS can target to update the label/icon
			input.setAttribute( 'data-global-state', newValue );
		}
	} );
};
