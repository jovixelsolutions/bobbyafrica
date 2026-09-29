/**
 * Layout Utilities for Schema Fields
 *
 * Provides utility functions for handling column-based layouts in schema fields.
 * Supports responsive grid layouts with fractional width specifications.
 */

/**
 * Converts width property to Tailwind grid column classes
 * Includes responsive classes for mobile-first design
 *
 * @param {string} width - Width value (e.g., '1/2', 'full')
 * @return {string} Tailwind CSS classes
 */
export const widthToTailwindClass = ( width ) => {
	const widthMap = {
		full: 'col-span-12',
		'1/2': 'col-span-12 md:col-span-6',
		'1/3': 'col-span-12 md:col-span-4',
		'2/3': 'col-span-12 md:col-span-8',
		'1/4': 'col-span-12 md:col-span-3',
		'3/4': 'col-span-12 md:col-span-9',
	};

	return widthMap[ width ] || 'col-span-12';
};

/**
 * Groups fields into rows based on their width properties
 * Ensures fields don't exceed 100% width per row
 *
 * @param {Array} fields - Array of field objects
 * @return {Array} Array of rows, each containing field objects
 */
export const groupFieldsIntoRows = ( fields ) => {
	const rows = [];
	let currentRow = [];
	let currentRowWidth = 0;

	/* Width value to decimal conversion */
	const parseWidth = ( width ) => {
		const widthMap = {
			full: 1.0,
			'1/2': 0.5,
			'1/3': 0.333,
			'2/3': 0.667,
			'1/4': 0.25,
			'3/4': 0.75,
		};
		return widthMap[ width ] || 1.0;
	};

	for ( const field of fields ) {
		const fieldWidth = parseWidth( field.width || 'full' );

		/* If adding this field would exceed row width, start new row */
		if ( currentRowWidth + fieldWidth > 1.0 ) {
			if ( currentRow.length > 0 ) {
				rows.push( currentRow );
			}
			currentRow = [ field ];
			currentRowWidth = fieldWidth;
		} else {
			currentRow.push( field );
			currentRowWidth += fieldWidth;
		}

		/* If row is exactly full, start new row */
		if ( currentRowWidth >= 1.0 ) {
			rows.push( currentRow );
			currentRow = [];
			currentRowWidth = 0;
		}
	}

	/* Add any remaining fields */
	if ( currentRow.length > 0 ) {
		rows.push( currentRow );
	}

	return rows;
};
