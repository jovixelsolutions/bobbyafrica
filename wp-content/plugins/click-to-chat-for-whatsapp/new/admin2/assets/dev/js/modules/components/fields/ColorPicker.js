import { escapeHTML, escapeAttr } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from './BaseField.js';

const HEX_COLOR_RE = /^#([0-9A-F]{3}|[0-9A-F]{6})$/i;

/**
 * Expand any valid hex color (3- or 6-digit, with leading #) to its 6-digit form.
 *
 * @param {string} str
 * @returns {string|null} 6-digit hex with leading #, or null if input isn't a valid hex.
 */
const normalizeHexColor = ( str ) => {
	if ( typeof str !== 'string' ) { return null; }
	const trimmed = str.trim();
	if ( ! HEX_COLOR_RE.test( trimmed ) ) { return null; }
	if ( trimmed.length === 7 ) { return trimmed.toUpperCase(); }
	return ( '#' + trimmed[ 1 ] + trimmed[ 1 ] + trimmed[ 2 ] + trimmed[ 2 ] + trimmed[ 3 ] + trimmed[ 3 ] ).toUpperCase();
};

/**
 * Wire the two-way sync + visual state between the native <input type="color">
 * picker and its companion hex text input.
 *
 * @param {HTMLInputElement} picker     - The <input type="color"> element.
 * @param {HTMLInputElement} textInput  - The hex text input.
 * @param {HTMLElement} inputGroup      - The wrapper element that carries the `is-empty` class.
 */
const initColorPickerBehavior = ( picker, textInput, inputGroup ) => {
	picker.addEventListener( 'input', () => {
		textInput.value = picker.value.toUpperCase();
		inputGroup.classList.remove( 'is-empty' );
		textInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	} );
	picker.addEventListener( 'change', () => {
		textInput.value = picker.value.toUpperCase();
		textInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );

	textInput.addEventListener( 'input', ( event ) => {
		const val = event.target.value;
		const normalized = normalizeHexColor( val );
		if ( normalized ) {
			picker.value = normalized;
			inputGroup.classList.remove( 'is-empty' );
		} else if ( val === '' ) {
			inputGroup.classList.add( 'is-empty' );
		}
	} );

	textInput.addEventListener( 'blur', ( event ) => {
		let val = event.target.value.trim();
		if ( val && ! val.startsWith( '#' ) ) { val = '#' + val; }
		const normalized = normalizeHexColor( val );
		if ( normalized ) {
			event.target.value = val.toUpperCase();
			picker.value = normalized;
			inputGroup.classList.remove( 'is-empty' );
		} else if ( val === '' ) {
			inputGroup.classList.add( 'is-empty' );
		}
	} );
};

export const renderColorPicker = ( field, config ) => {
	const { wrapper, value, name, inputClass } = createBaseWrapper( field, config, 'form-group' );

	const defaultColor = normalizeHexColor( field.default );
	const colorValue = value ? value.toUpperCase() : '';
	const pickerValue = ( value && normalizeHexColor( value ) ) || defaultColor || '#000000';
	const isEmpty = ! value ? 'is-empty' : '';

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values
	wrapper.innerHTML = `
        <div class="field-color-wrapper">
            <label for="${escapeAttr( field.id )}">${escapeHTML( field.label || '' )}</label>
            <div class="color-input-group ${isEmpty}">
                <div class="color-swatch-wrapper">
                    <input type="color" aria-label="${escapeAttr( ( field.label || 'Color' ) + ' color picker' )}">
                    <div class="no-color-visual"></div>
                </div>
                <input
                    type="text"
                    id="${escapeAttr( field.id )}"
                    name="${escapeAttr( name )}"
                    class="color-hex-input ${escapeAttr( inputClass )}"
                    placeholder="Default Color">
            </div>
            <div class="color-field-actions">
                <button type="button" class="color-undo-btn is-hidden">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7v6h6"/>
                        <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>
                    </svg>
                </button>
                ${defaultColor ?
		`
                <button type="button" class="color-default-btn" data-tip="Reset to default (${escapeAttr( defaultColor )})" aria-label="${escapeAttr( 'Reset ' + ( field.label || 'color' ) + ' to default ' + defaultColor )}">
                    <span class="color-default-btn-swatch"
                        style="background-color: ${escapeAttr( defaultColor )}"></span>
                </button>` :
		''}
            </div>
        </div>
    `;

	const colorPicker = wrapper.querySelector( 'input[type="color"]' );
	const colorHex = wrapper.querySelector( '.color-hex-input' );

	if ( colorPicker ) { colorPicker.value = pickerValue; }
	if ( colorHex ) { colorHex.value = colorValue; }

	initColorPickerBehavior( colorPicker, colorHex, wrapper.querySelector( '.color-input-group' ) );

	/*
	 * Undo/reset pattern (saved baseline + default + two buttons) is color-only
	 * for now, but could suit other field types too (e.g. text/number fields
	 * with a known default like Image Size 40px). If a second field type needs
	 * it, lift this logic into BaseField and share it instead of duplicating.
	 */
	const defaultBtn = wrapper.querySelector( '.color-default-btn' );
	const undoBtn = wrapper.querySelector( '.color-undo-btn' );
	if ( colorHex && colorPicker && undoBtn ) {
		// Last saved value ('' = blank), as loaded when the page rendered.
		// Undo is a single step back to this saved state.
		const savedValue = colorValue;
		const savedLabel = savedValue === '' ? 'no color' : savedValue;
		undoBtn.setAttribute( 'data-tip', `Undo changes (back to ${savedLabel})` );
		undoBtn.setAttribute( 'aria-label', `Undo ${field.label || 'color'} changes, back to ${savedLabel}` );

		// Show the saved color as the button background, so it's clear which
		// color undo restores. Icon flips light/dark for contrast.
		const savedHex = normalizeHexColor( savedValue );
		if ( savedHex ) {
			const getVal = ( pos ) => parseInt( savedHex.slice( pos, pos + 2 ), 16 );
			const [ red, green, blue ] = [ 1, 3, 5 ].map( getVal );
			const isLight = ( 0.299 * red + 0.587 * green + 0.114 * blue ) > 160;
			undoBtn.classList.add( 'has-color' );
			undoBtn.style.backgroundColor = savedHex;
			undoBtn.style.color = isLight ? 'rgba(0, 0, 0, 0.65)' : '#ffffff';
		}

		// Setting the value and dispatching 'input' is enough: the listener in
		// initColorPickerBehavior syncs the picker swatch and is-empty state.
		const applyValue = ( val ) => {
			colorHex.value = val;
			colorHex.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			colorHex.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		};

		// Each button shows only when clicking it would change something:
		// undo when the value differs from the saved one, reset from the default.
		const savedNorm = savedHex || '';
		const current = () => normalizeHexColor( colorHex.value ) || '';
		const syncButtons = () => {
			undoBtn.classList.toggle( 'is-hidden', current() === savedNorm );
			if ( defaultBtn ) {
				defaultBtn.classList.toggle( 'is-hidden', current() === defaultColor );
			}
		};
		syncButtons();

		colorHex.addEventListener( 'input', syncButtons );
		undoBtn.addEventListener( 'click', () => applyValue( savedValue ) );
		if ( defaultBtn ) {
			defaultBtn.addEventListener( 'click', () => applyValue( defaultColor ) );
		}
	}

	appendHelpText( wrapper, field );

	return wrapper;
};
