import { Link } from 'react-router-dom';
import { Title, Table } from '@bsf/force-ui';
import {
	ArrowUpRightIcon,
	ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';
import parse from 'html-react-parser';

import SectionWrapper from '@Components/common/SectionWrapper';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import { EmptyBlock } from '@Components/common/empty-blocks';
import { BASE_URL } from '@Admin/constants';
import { ProUpgradeCta, ProductReportDummyData } from '@Components/pro';
import { useProAccess } from '@Components/pro/useProAccess';

const ProductReport = ( { dashboardData, isDashboardLoading } ) => {
	const { shouldBlockProFeatures } = useProAccess();
	// Check if product report data exists and has items
	const hasProductData =
		dashboardData?.product_report &&
		dashboardData.product_report.length > 0;
	const start = convertToMMDDYYYY( dashboardData?.duration?.start );
	const end = convertToMMDDYYYY( dashboardData?.duration?.end );
	const duration = `(${ start } to ${ end })`;

	const isFeatureBlocked = shouldBlockProFeatures();

	function convertToMMDDYYYY( dateStr ) {
		if ( ! isDashboardLoading ) {
			const d = new Date( dateStr );
			const mm = String( d.getMonth() + 1 ).padStart( 2, '0' );
			const dd = String( d.getDate() ).padStart( 2, '0' );
			const yyyy = d.getFullYear();
			return `${ mm }-${ dd }-${ yyyy }`;
		}
		return dateStr;
	}

	return (
		<SectionWrapper className="lg:w-1/2 flex flex-col gap-2 min-h-64">
			<div className="flex justify-between items-center">
				<div className="flex items-center gap-2">
					<Title
						iconPosition="right"
						size="xs"
						tag="h2"
						title={ __(
							'Product Reports',
							'woo-cart-abandonment-recovery'
						) }
						description={
							! isFeatureBlocked &&
							! isDashboardLoading &&
							duration
						}
						className="[&>*]:text-gray-900 flex items-center gap-2"
					/>
				</div>
				<Link
					to={ BASE_URL + '&path=product' }
					className="text-gray-900 text-xs font-semibold inline-flex items-center no-underline gap-1"
				>
					{ __( 'View All', 'woo-cart-abandonment-recovery' ) }
					<ArrowUpRightIcon className="h-3 w-3 text-gray-900 font-semibold" />
				</Link>
			</div>

			{ isDashboardLoading ? (
				<SkeletonLoader height="200px" />
			) : (
				<>
					{ isFeatureBlocked ? (
						<div className="relative">
							<ProUpgradeCta
								isVisible={ true }
								mainTitle=""
								subTitle=""
								variation="message"
								description={
									/* translators: %%1$s: Link HTML Start and %2$sof: Link HTML End. */
									__(
										'Product Report shows you which items bring the most recoveries and sales.',
										'woo-cart-abandonment-recovery'
									)
								}
								actionBtnUrlArgs={
									'utm_source=wcar-dashboard&utm_medium=free-wcar&utm_campaign=go-wcar-pro'
								}
							/>
							<ProductReportDummyData minified={ true } />
						</div>
					) : hasProductData ? (
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
								{ dashboardData.product_report.map(
									( item, index ) => (
										<Table.Row key={ index }>
											<Table.Cell>
												{ item.productName }
											</Table.Cell>
											<Table.Cell>
												{ parse( item.abandoned ) }
											</Table.Cell>
											<Table.Cell>
												{ parse( item.recovered ) }
											</Table.Cell>
										</Table.Row>
									)
								) }
							</Table.Body>
						</Table>
					) : (
						<EmptyBlock
							icon={
								<ExclamationTriangleIcon className="h-12 w-12 text-yellow-500" />
							}
							title={ __(
								'No Product Data Yet',
								'woo-cart-abandonment-recovery'
							) }
							description={ __(
								"You'll see product abandonment stats here once customers start adding items to their carts.",
								'woo-cart-abandonment-recovery'
							) }
						/>
					) }
				</>
			) }
		</SectionWrapper>
	);
};

export default ProductReport;
