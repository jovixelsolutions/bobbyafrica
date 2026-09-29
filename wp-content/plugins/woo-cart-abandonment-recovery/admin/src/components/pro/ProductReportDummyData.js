/**
 * ProductReportDummyData Component
 *
 * Displays realistic dummy product data for product reports when pro plugin is not active.
 * Provides visual representation of what users can expect with the pro version.
 *
 * @package
 * @since 1.0.0
 */

import React, { useEffect, useState } from 'react';
import { Table } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

/**
 * ProductReportDummyData Component
 *
 * @param {boolean} minified - Whether to show a minified version of the data
 *
 */
const ProductReportDummyData = ( { minified = false } ) => {
	// Generate realistic dummy product data
	const [ dummyData, setDummyData ] = useState( [
		{
			id: 1,
			productName: 'T-Shirt with Logo',
			abandoned: '45 ($2,250.00)',
			recovered: '12 ($600.00)',
		},
		{
			id: 2,
			productName: 'Beanie with Logo',
			abandoned: '38 ($3,800.00)',
			recovered: '15 ($1,500.00)',
		},
		{
			id: 3,
			productName: 'Logo Collection',
			abandoned: '62 ($9,300.00)',
			recovered: '18 ($2,700.00)',
		},
		{
			id: 4,
			productName: 'Hoodie',
			abandoned: '29 ($1,450.00)',
			recovered: '8 ($400.00)',
		},
		{
			id: 5,
			productName: 'Hoodie with Logo',
			abandoned: '23 ($11,500.00)',
			recovered: '6 ($3,000.00)',
		},
		{
			id: 6,
			productName: 'T-Shirt',
			abandoned: '41 ($4,100.00)',
			recovered: '14 ($1,400.00)',
		},
		{
			id: 7,
			productName: 'Belt',
			abandoned: '17 ($3,400.00)',
			recovered: '5 ($1,000.00)',
		},
		{
			id: 8,
			productName: 'Cap',
			abandoned: '89 ($2,670.00)',
			recovered: '34 ($1,020.00)',
		},
		{
			id: 9,
			productName: 'Sunglasses',
			abandoned: '56 ($2,800.00)',
			recovered: '21 ($1,050.00)',
		},
		{
			id: 10,
			productName: 'Hoodie with Pocket',
			abandoned: '73 ($2,190.00)',
			recovered: '28 ($840.00)',
		},
		{
			id: 11,
			productName: 'Hoodie with Zipper',
			abandoned: '73 ($2,190.00)',
			recovered: '28 ($840.00)',
		},
		{
			id: 12,
			productName: 'Long Sleeve Tee',
			abandoned: '73 ($2,190.00)',
			recovered: '28 ($840.00)',
		},
		{
			id: 13,
			productName: 'Polo',
			abandoned: '73 ($2,190.00)',
			recovered: '28 ($840.00)',
		},
	] );

	useEffect( () => {
		if ( minified ) {
			setDummyData( dummyData.slice( 0, 5 ) );
		}
	}, [] );

	return (
		<div className="relative">
			{ /* Main content */ }
			<div className="overflow-x-auto cursor-pointer">
				<Table>
					<Table.Head>
						<Table.HeadCell>
							{ __(
								'Product Name',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'No. of time Abandoned',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'No. of time Recovered',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
					</Table.Head>
					<Table.Body>
						{ dummyData.map( ( item, index ) => (
							<Table.Row
								key={ index }
								className={ minified ? 'h-[52px]' : '' }
							>
								<Table.Cell>{ item.productName }</Table.Cell>
								<Table.Cell>{ item.abandoned }</Table.Cell>
								<Table.Cell>{ item.recovered }</Table.Cell>
							</Table.Row>
						) ) }
					</Table.Body>
				</Table>
			</div>
		</div>
	);
};

export default ProductReportDummyData;
