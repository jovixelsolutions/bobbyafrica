import { escapeAttr } from '../../core/Utils.js';

/**
 * Dynamic Section
 * field_type: "section_dynamic",
 * just like created at class-ht-ctc-admin-dashboard.php create panel and inside fields-container.
 * and dispatch event ht_ctc_register_section_dynamic - which calls loadTabSettings
 * @param {*} field
 * @returns
 */
export const createDynamicSection = ( field ) => {
	const div = document.createElement( 'div' );
	div.id = field.id;
	div.setAttribute( 'data-group', field.group || field.option_group );
	div.setAttribute( 'data-loaded', 'false' );
	if ( field.class_pr ) { div.className = field.class_pr; }

	const containerId = `${field.id}_${field.group || field.option_group}`;

	// eslint-disable-next-line no-unsanitized/property -- Contains static HTML/Safely escaped dynamic values using Utils.escapeHTML
	div.innerHTML = `
        <div class="fields-container" id="${escapeAttr( containerId )}">
            <div class="loading-spinner">
                <span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span>
                Loading...
            </div>
        </div>
    `;

	setTimeout( () => {
		document.dispatchEvent( new CustomEvent( 'ht_ctc_register_section_dynamic', { detail: { element: div, id: field.id } } ) );
	}, 0 );

	return div;
};
