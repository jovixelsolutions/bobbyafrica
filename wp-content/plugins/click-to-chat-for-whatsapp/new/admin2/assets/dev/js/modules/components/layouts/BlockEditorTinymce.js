import { log, decodeHTML } from '../../core/Utils.js';
import { createBaseWrapper, appendHelpText } from '../fields/BaseField.js';

/**
 * field_type: block_editor_tinymce
 *
 * Creates a BlockEditorTinymce layout component.
 */
export const createBlockEditorTinymce = ( field, context, config ) => {
	log( 'Layouts', 'Initializing TinyMCE Editor', field.id );

	const { wrapper, value: rawValue, name, inputClass } = createBaseWrapper( field, config, 'form-group ctc-enterkey-newline' );
	const editorId = `${field.id}_${Date.now()}_${Math.floor( Math.random() * 1000 )}`;

	let value = rawValue ? decodeHTML( rawValue ) : '';

	// Preprocess content with wpautop if available to preserve paragraphs
	if ( typeof wp !== 'undefined' && wp?.editor?.autop ) {
		value = wp.editor.autop( value );
	}

	if ( field.label ) {
		const label = document.createElement( 'label' );
		label.htmlFor = editorId;
		label.textContent = field.label;
		wrapper.appendChild( label );
	}

	const textarea = document.createElement( 'textarea' );
	textarea.id = editorId;
	textarea.name = name;
	textarea.className = inputClass;
	textarea.rows = field.rows || 10;
	textarea.value = value;

	wrapper.appendChild( textarea );
	appendHelpText( wrapper, field );

	const doInitEditor = () => {
		if ( ! window.wp || ! wp.editor ) {
			console.warn( 'CTC: wp.editor not available for field:', field.id );
			return;
		}

		wp.editor.remove( editorId );

		const editorType = field.editor_type || 'full';

		const setupEditor = function setupEditor ( editor ) {
			editor.on( 'input change keyup paste', function syncEditorContent () {
				if ( textarea.dataset.ctcSyncingFromServer === 'true' ) {
					return;
				}
				editor.save();
				textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
				textarea.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			} );
		};

		const baseTinymceSettings = {
			fontsize_formats: '6px 8px 10px 12px 13px 14px 15px 16px 18px 20px 24px 28px 32px 36px',

			/**
			 Neutral mid-gray: greetings dialogs render this content on user-chosen
			 backgrounds (dark header, light main, ...), so the editor surface must keep
			 both light text (e.g. #fff) and dark text readable on a single color.
			*/
			content_style: 'body { background-color: #26a69a; }',

			// content_style: 'body { background-color: #82878c; }',
			elementpath: true,
		};

		const tinymceSettings = editorType === 'lite' ?
			{
				...baseTinymceSettings,
				toolbar1: 'bold link italic underline forecolor backcolor fontsizeselect fontselect undo redo removeformat',
				toolbar2: false,
				height: 150,
			} :
			{
				...baseTinymceSettings,
				wordpress_adv_hidden: false,
				height: 250,
				toolbar1: 'formatselect | bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more | wp_adv',
				toolbar2: 'fontsizeselect fontselect | strikethrough hr | forecolor backcolor pastetext | removeformat | charmap | outdent indent | undo redo | wp_help',
			};

		tinymceSettings.setup = setupEditor;

		wp.editor.initialize( editorId, {
			textarea_name: name,
			editor_height: editorType === 'lite' ? 150 : 250,
			mediaButtons: editorType !== 'lite',
			quicktags: true,
			tinymce: tinymceSettings,
		} );
	};

	if ( textarea.isConnected ) {
		doInitEditor();
	} else {
		const observer = new MutationObserver( () => {
			if ( textarea.isConnected ) {
				observer.disconnect();
				doInitEditor();
			}
		} );

		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	}

	return wrapper;
};

export default createBlockEditorTinymce;
