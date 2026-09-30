/**
 * field_type: tabs
 *
 * Creates a Tabs layout component.
 *
 * Layout Concept:
 * - A nested tab navigation structure within a card or panel.
 * - Allows switching between different sub-views (tabs).
 *
 * Example structure:
 * 'fields' => array(
 *     array(
 *         'field_type' => 'tabs',
 *         'tabs' => array(
 *             'desktop-style' => array(
 *                 'label' => 'Desktop',
 *                 'fields' => array( ... )
 *             ),
 *             'mobile-style' => array(
 *                 'label' => 'Mobile',
 *                 'fields' => array( ... )
 *             )
 *         )
 *     )
 * )
 */
export const createTabs = ( field, context, renderField ) => {
	const classPr = field.class_pr || '';
	const div = document.createElement( 'div' );
	div.className = `tabs ${classPr}`;
	const tabList = document.createElement( 'div' );
	tabList.className = 'tab-list';
	tabList.setAttribute( 'role', 'tablist' );

	let first = true;
	for ( const [ tabId, tabData ] of Object.entries( field.tabs ) ) {
		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = `tab-button ${first ? 'active' : ''}`;
		btn.setAttribute( 'data-tab', tabId );
		btn.setAttribute( 'role', 'tab' );
		btn.setAttribute( 'aria-selected', first ? 'true' : 'false' );
		btn.setAttribute( 'aria-controls', `${tabId}-tab` );
		btn.id = `tab-btn-${tabId}`;
		btn.textContent = tabData.label;
		tabList.appendChild( btn );

		const content = document.createElement( 'div' );
		content.className = `tab-content ${first ? 'active' : ''}`;
		content.id = `${tabId}-tab`;
		content.setAttribute( 'role', 'tabpanel' );
		content.setAttribute( 'aria-labelledby', `tab-btn-${tabId}` );

		if ( tabData.fields && renderField ) {
			const fragment = document.createDocumentFragment();
			tabData.fields.forEach( subField => {
				const el = renderField( subField );
				if ( el ) { fragment.appendChild( el ); }
			} );
			content.appendChild( fragment );
		}
		div.appendChild( content );
		first = false;
	}
	div.insertBefore( tabList, div.firstChild );
	return div;
};
