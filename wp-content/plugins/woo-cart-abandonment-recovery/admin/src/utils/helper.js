/**
 * Generate pagination pages array with ellipsis support
 *
 * @param {number} totalPages  - Total number of pages
 * @param {number} currentPage - Current active page
 * @return {Array} Array of page numbers and ellipsis markers
 */
export const generatePaginationPages = ( totalPages, currentPage ) => {
	const pages = [];

	if ( totalPages <= 7 ) {
		// If total pages is 7 or less, show all pages
		for ( let i = 1; i <= totalPages; i++ ) {
			pages.push( i );
		}
	} else {
		// Always show first 3 pages
		for ( let i = 1; i <= 3; i++ ) {
			pages.push( i );
		}

		// Add ellipsis if there are more pages
		if ( currentPage > 5 ) {
			pages.push( 'ellipsis' );
		}

		// Show current page and surrounding pages if not in first/last 3
		if ( currentPage > 3 && currentPage < totalPages - 2 ) {
			pages.push( currentPage );
		}

		// Add ellipsis before last 3 pages if needed
		if ( currentPage < totalPages - 4 ) {
			pages.push( 'ellipsis-end' );
		}

		// Always show last 3 pages
		for ( let i = totalPages - 2; i <= totalPages; i++ ) {
			if ( i > 3 ) {
				pages.push( i );
			}
		}
	}

	return pages;
};
