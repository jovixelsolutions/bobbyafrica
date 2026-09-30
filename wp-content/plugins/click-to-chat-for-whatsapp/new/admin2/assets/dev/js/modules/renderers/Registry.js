// Fields
import { renderSwitch } from '../components/fields/Switch.js';
import { renderColorPicker } from '../components/fields/ColorPicker.js';
import { renderRadio } from '../components/fields/Radio.js';
import { renderTextarea } from '../components/fields/Textarea.js';
import { renderSelect } from '../components/fields/Select.js';
import { renderButton } from '../components/fields/Button.js';
import { renderHidden } from '../components/fields/Hidden.js';
import { renderInput } from '../components/fields/Input.js';

// Layouts
import { createCard } from '../components/layouts/Card.js';
import { createTabs } from '../components/layouts/Tabs.js';
import { createBlockGroup } from '../components/layouts/BlockGroup.js';
import { createBlockContainer } from '../components/layouts/BlockContainer.js';
import { createBlockButtonAdd } from '../components/layouts/BlockButtonAdd.js';
import { createBlockUploadImage } from '../components/layouts/BlockUploadImage.js';
import { createBlockContent } from '../components/layouts/BlockContent.js';
import { createBlockFeatureBox } from '../components/layouts/BlockFeatureBox.js';
import { createBlockContentDetails } from '../components/layouts/BlockContentDetails.js';
import { createBlockAccordion } from '../components/layouts/BlockAccordion.js';
import { createBlockFaq } from '../components/layouts/BlockFaq.js';
import { createBlockGridSelect } from '../components/layouts/BlockGridSelect.js';
import { createContextualTriggerField } from '../components/layouts/ContextualTrigger.js';
import { createBlockRows } from '../components/layouts/BlockRows.js';
import { createSubHeading } from '../components/layouts/SubHeading.js';
import { createInfoBox } from '../components/layouts/InfoBox.js';
import { createBlockVariables } from '../components/layouts/BlockVariables.js';
import { createProFeature } from '../components/layouts/ProFeature.js';
import { createExternalLink } from '../components/layouts/ExternalLink.js';
import { createSpacer } from '../components/layouts/Spacer.js';
import { createDivider } from '../components/layouts/Divider.js';
import { createRawHtml } from '../components/layouts/RawHtml.js';

// Sections
import { createDynamicSection } from '../components/sections/DynamicSection.js';
import { createWaNumberSection } from '../components/sections/WaNumberSection.js';
import {
	createGaParametersSection,
	createPixelParametersSection,
	createGtmParametersSection,
} from '../components/sections/AnalyticsSections.js';
import { createWebhooksParametersSection } from '../components/sections/WebhooksSection.js';

/**
 * Renderers Registry
 *
 * Design Pattern: "Registry / Factory"
 * This file maps a "string name" (from the JSON config) to a "Function".
 *
 * Why? The database only stores "text", e.g., "field_type": "text".
 * We need to convert that string "text" into the actual component rendering function.
 *
 * STATIC REGISTRATION (Statically Imported Modules):
 * --------------------------------------------------
 * Core fields and layout wrappers (imported at the top of this file) are registered
 * directly to the app's renderers object inside registerDefaultRenderers().
 * Examples:
 *   // 1. Simple pass-through mapping:
 *   renderers.block_faq = createBlockFaq;
 *
 *   // 2. Closure wrapper (injecting app config or sub-renderer factory):
 *   renderers.field_text = ( field ) => renderInput( field, app.config );
 *   renderers.card = ( field ) => createCard( field, createField );
 *
 * DYNAMIC/LAZY REGISTRATION (rendererId):
 * ---------------------------------------
 * Dynamic modules (like sections or complex layouts) can also be registered at runtime.
 * 1. PHP Definition (class-ht-ctc-admin-page-scripts.php):
 *    Registers a module config under `modulesPath` defining the path and a `rendererId`.
 *    e.g., 'my_module' => [ 'path' => '...', 'rendererId' => 'custom_section_type' ]
 *
 * 2. JS Loading (App.js -> loadModule):
 *    When the module is downloaded, App.js checks if `moduleConf.rendererId` is defined.
 *    If it is, it dynamically registers the module's default export:
 *    `app.registerRenderer( moduleConf.rendererId, (field, context) => module.default(field, context, app.config) );`
 *
 * 3. Execution (App.js -> createFieldElement):
 *    When rendering fields, the app retrieves the function using the field type:
 *    `const renderer = app.renderers[ field.field_type ];`
 *    and executes it: `renderer( field, context )`.
 *
 * EXAMPLE: Manually adding a custom renderer to the app instance:
 * -------------------------------------------------------------
 * app.registerRenderer( 'field_my_custom_type', ( field, context ) => {
 *     const el = document.createElement( 'div' );
 *     el.className = 'my-custom-class';
 *     el.textContent = field.label || 'Default Label';
 *     return el;
 * } );
 */
export const registerDefaultRenderers = ( app ) => {
	const renderers = app.renderers;
	const createField = app.createFieldElement.bind( app );

	// Layouts
	renderers.card = ( field ) => createCard( field, createField );
	renderers.tabs = ( field, context ) => createTabs( field, context, createField );

	renderers.block_group = ( field, context ) => createBlockGroup( field, context, createField );
	renderers.block_container = createBlockContainer;
	renderers.block_button_add = createBlockButtonAdd;
	renderers.block_upload_image = ( field, context ) =>
		createBlockUploadImage( field, context, app.config );
	renderers.block_content = ( field ) => createBlockContent( field );
	renderers.block_feature_box = ( field ) => createBlockFeatureBox( field );
	renderers.block_content_details = ( field ) => createBlockContentDetails( field, createField );
	renderers.block_accordion = ( field ) => createBlockAccordion( field, createField );
	renderers.block_faq = createBlockFaq;
	renderers.block_grid_select = ( field ) => createBlockGridSelect( field, app.config );
	renderers.block_contextual_trigger = createContextualTriggerField;
	renderers.block_rows = ( field, context ) => createBlockRows( field, context, createField );
	renderers.block_sub_heading = createSubHeading;
	renderers.block_infobox = createInfoBox;
	renderers.block_variables = createBlockVariables;
	renderers.block_infobox_alert = createInfoBox;
	renderers.block_pro_feature = createProFeature;
	renderers.block_external_link = createExternalLink;
	renderers.block_spacer = createSpacer;
	renderers.block_divider = createDivider;
	renderers.block_raw_html = createRawHtml;

	// Fields
	renderers.field_text = ( field ) => renderInput( field, app.config );
	renderers.field_textarea = ( field ) => renderTextarea( field, app.config );
	renderers.field_checkbox = ( field ) => renderSwitch( field, app.config );
	renderers.field_radio = ( field ) => renderRadio( field, app.config );
	renderers.field_select = ( field ) => renderSelect( field, app.config );
	renderers.field_number = ( field ) => renderInput( field, app.config );
	renderers.field_color = ( field ) => renderColorPicker( field, app.config );
	renderers.field_hidden = ( field ) => renderHidden( field, app.config );
	renderers.field_button = ( field ) => renderButton( field, app.config );

	// sections
	renderers.section_whatsapp_number = ( field, context ) =>
		createWaNumberSection( field, context, app.config );
	renderers.section_google_analytics_params = ( field, context ) =>
		createGaParametersSection( field, context, app.config );
	renderers.section_google_tag_manager_params = ( field, context ) =>
		createGtmParametersSection( field, context, app.config );
	renderers.section_pixel_analytics_params = ( field, context ) =>
		createPixelParametersSection( field, context, app.config );
	renderers.section_webhooks_params = ( field, context ) =>
		createWebhooksParametersSection( field, context, app.config );

	// dynamic section
	renderers.section_dynamic = createDynamicSection;

};
