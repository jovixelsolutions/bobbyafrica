import { escapeHTML, escapeAttr, applyDataAttributes } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

/**
 * field_type: field_select
 *
 * `options` is a map of value => label. A value may instead give an object when the
 * option needs to carry data of its own:
 *
 *   'options' => array(
 *       '1'   => 'Style 1',                              // plain label
 *       '7_1' => array(                                  // label + data
 *           'label'      => 'Style 7 Extend',
 *           'attributes' => array( 'data-contextual-id' => 'style_7_1' ),
 *       ),
 *   )
 *
 * That is how an option says what it stands for beyond its stored value — a contextual
 * trigger watching this select reads the chosen option's `data-contextual-id` rather than
 * deriving one from the value, so the two need no naming convention between them.
 */
export const renderSelect = ( field, config ) => {
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <label for="${escapeAttr( field.id )}">${escapeHTML( field.label || '' )}</label>
        <div class="select-wrapper">
            <select 
                id="${escapeAttr( field.id )}" 
                name="${escapeAttr( name )}" 
                class="${escapeAttr( inputClass )}"
            ></select>
        </div>
    `;

	const select = wrapper.querySelector( 'select' );

	/*
	 * Options are built as elements rather than markup so their attributes go through
	 * applyDataAttributes(), which allows data-* only — the same gate grid options use.
	 */
	if ( field.options ) {
		for ( const [ key, option ] of Object.entries( field.options ) ) {
			const isDetailed = option && 'object' === typeof option;
			const el = document.createElement( 'option' );

			el.value = key;
			el.textContent = isDetailed ? ( option.label || '' ) : option;

			/*
			 * Marked here rather than by assigning select.value after the loop. A stored
			 * value that matches no option — an unseeded group, a field with no `default`,
			 * a value an older version offered — sets selectedIndex to -1 and renders the
			 * control BLANK, which then saves back as ''. Marking the match instead leaves
			 * nothing marked when there is no match, so the browser falls back to the first
			 * option, the way the `selected` attribute did when these were built as markup.
			 *
			 * Both sides are coerced: option keys arrive from PHP as strings, stored values
			 * may not.
			 */
			if ( String( value ) === String( key ) ) { el.selected = true; }

			if ( isDetailed ) {
				applyDataAttributes( el, option.attributes );
			}

			select.appendChild( el );
		}
	}

	appendHelpText( wrapper, field );

	return wrapper;
};
