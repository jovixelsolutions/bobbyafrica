/**
 * Determine whether homepage-specific checks should be hidden from dashboard views.
 *
 * @param {Object} siteSettings - Site settings from the admin store.
 * @return {boolean} True when homepage checks should be filtered.
 */
export const shouldFilterHomepageCheck = ( siteSettings ) => {
	if ( siteSettings?.home_page_static !== 'page' ) {
		return false;
	}

	if ( ! siteSettings?.home_page_id ) {
		return false;
	}

	return true;
};

/**
 * Remove homepage-specific checks from a report when the site uses a static homepage.
 *
 * @param {Object} report           - SEO report keyed by check slug.
 * @param {Object} siteSettings     - Site settings from the admin store.
 * @param {string} categoryToFilter - Report category to remove.
 * @return {Object} The original or filtered report object.
 */
export const filterHomepageChecks = (
	report,
	siteSettings,
	categoryToFilter = 'general'
) => {
	if ( ! report || ! shouldFilterHomepageCheck( siteSettings ) ) {
		return report;
	}

	return Object.entries( report ).reduce( ( acc, [ key, item ] ) => {
		if ( item?.category !== categoryToFilter ) {
			acc[ key ] = item;
		}
		return acc;
	}, {} );
};
