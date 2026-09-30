import { applyConditionalAttributes, getNestedValue } from '../../core/Utils.js';

/**
 * field_type: block_upload_image
 *
 * Creates a BlockUploadImage layout component.
 */
export const createBlockUploadImage = ( field, context, config ) => {
	const classPr = field.class_pr || '';
	const inputClass = field.class_field || '';

	const group = document.createElement( 'div' );
	group.className = `form-group ctc-image-upload-wrapper ${classPr}`.trim();

	applyConditionalAttributes( group, field );

	const value = getNestedValue( config.initialSettings, field.option_group, field.id ) || '';
	const name = `${field.option_group}[${field.id}]`;

	if ( field.label ) {
		const label = document.createElement( 'label' );
		label.textContent = field.label;
		group.appendChild( label );
	}

	const input = document.createElement( 'input' );
	input.type = 'text';
	input.name = name;
	input.className = `g_header_image regular-text ${inputClass}`.trim();
	input.value = value;
	input.id = field.id;

	input.style.display = 'none';

	const thumbnailHeight = field.thumbnail_height || '50px';
	const thumbnailShape = field.thumbnail_shape || 'circle';

	const preview = document.createElement( 'img' );
	preview.className = 'g_header_image_preview';
	preview.style.height = thumbnailHeight;

	if ( thumbnailShape === 'circle' || thumbnailShape === 'square' ) {
		preview.style.width = thumbnailHeight;
	} else {
		preview.style.width = 'auto';
	}
	preview.style.borderRadius = ( thumbnailShape === 'circle' ) ? '50%' : '0px';
	preview.style.flexShrink = '0';
	preview.style.objectFit = 'cover';
	preview.style.display = value ? 'block' : 'none';
	preview.src = value || '';

	const addBtn = document.createElement( 'button' );
	addBtn.className = 'button ctc_add_image_wp';
	addBtn.type = 'button';
	addBtn.textContent = 'Add Header Image';

	const removeBtn = document.createElement( 'button' );
	removeBtn.className = 'button ctc_remove_image_wp';
	removeBtn.type = 'button';
	removeBtn.textContent = 'Remove';
	removeBtn.style.marginLeft = '8px';
	removeBtn.style.display = value ? 'inline-block' : 'none';

	const rowWrapper = document.createElement( 'div' );
	rowWrapper.style.display = 'flex';
	rowWrapper.style.alignItems = 'center';
	rowWrapper.style.gap = '10px';
	rowWrapper.style.flexWrap = 'wrap';

	rowWrapper.appendChild( preview );
	rowWrapper.appendChild( input );
	rowWrapper.appendChild( addBtn );
	rowWrapper.appendChild( removeBtn );

	group.appendChild( rowWrapper );

	if ( field.help ) {
		const help = document.createElement( 'p' );
		help.className = 'help-text';
		// eslint-disable-next-line no-unsanitized/property -- Help text defined in PHP can contain safe HTML
		help.innerHTML = field.help;
		group.appendChild( help );
	}

	return group;
};
