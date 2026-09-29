import { Button } from '@bsf/force-ui';
import { CloudArrowDownIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

const ExportToExcel = ( {
	data,
	filename = 'export',
	className = '',
	disabled = false,
} ) => {
	const today = new Date();
	const day = String( today.getDate() ).padStart( 2, '0' );
	const month = String( today.getMonth() + 1 ).padStart( 2, '0' ); // Months are 0-indexed
	const year = today.getFullYear();

	const formattedDate = `${ day }-${ month }-${ year }`;

	// Converts array of objects to CSV string.
	const convertToCSV = ( dataArray ) => {
		if ( ! dataArray || ! dataArray.length ) {
			return '';
		}

		// Get headers from the first object's keys
		const headers = Object.keys( dataArray[ 0 ] );

		// Create CSV header row
		let csvContent = headers.join( ',' ) + '\r\n';

		// Add data rows
		dataArray.forEach( ( item ) => {
			const row = headers.map( ( header ) => {
				// Handle values that need quotes (contain commas, quotes, or newlines)
				let value =
					item[ header ] === null || item[ header ] === undefined
						? ''
						: item[ header ];
				value = String( value );

				// Escape quotes by doubling them and wrap in quotes if needed
				if (
					value.includes( ',' ) ||
					value.includes( '"' ) ||
					value.includes( '\n' )
				) {
					value = '"' + value.replace( /"/g, '""' ) + '"';
				}

				return value;
			} );

			csvContent += row.join( ',' ) + '\r\n';
		} );

		return csvContent;
	};

	// Handles the export button click.
	const handleExport = () => {
		if ( ! data || ! data.length ) {
			return;
		}

		const csvContent = convertToCSV( data );

		// Create a Blob with the CSV content
		const blob = new Blob( [ csvContent ], {
			type: 'text/csv;charset=utf-8;',
		} );

		// Create a download link
		const link = document.createElement( 'a' );

		// Create a URL for the blob
		const url = URL.createObjectURL( blob );

		// Set link properties
		link.setAttribute( 'href', url );
		link.setAttribute( 'download', `${ filename }_${ formattedDate }.csv` );
		link.style.visibility = 'hidden';

		// Append link to document, trigger click, and remove
		document.body.appendChild( link );
		link.click();
		document.body.removeChild( link );
	};

	return (
		<Button
			className={ className }
			iconPosition="left"
			size="sm"
			tag="button"
			type="button"
			variant="primary"
			onClick={ handleExport }
			disabled={ disabled || ! data || data.length === 0 }
			icon={ <CloudArrowDownIcon aria-label="icon" /> }
		>
			{ __( 'Export', 'woo-cart-abandonment-recovery' ) }
		</Button>
	);
};

export default ExportToExcel;
