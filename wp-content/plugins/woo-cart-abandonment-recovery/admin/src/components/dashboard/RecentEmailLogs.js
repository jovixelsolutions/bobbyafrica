import { Link } from 'react-router-dom';
import parse from 'html-react-parser';
import { Title, Table } from '@bsf/force-ui';
import {
	ArrowUpRightIcon,
	ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import { EmptyBlock } from '@Components/common/empty-blocks';
import { BASE_URL } from '@Admin/constants';
import OrderStatusBadge from '@Components/common/OrderStatusBadge';

const RecentEmailLogs = ( { dashboardData, isDashboardLoading } ) => {
	// Use the new API key
	const logs = dashboardData?.recent_follow_up_reports || [];
	const hasData = logs.length > 0;

	return (
		<SectionWrapper className="lg:w-1/2 flex flex-col gap-2 min-h-64">
			<div className="flex justify-between items-center">
				<Title
					description=""
					icon={ null }
					iconPosition="right"
					size="xs"
					tag="h2"
					title={ __(
						'Recent Follow Up Reports',
						'woo-cart-abandonment-recovery'
					) }
					className="[&>*]:text-gray-900"
				/>
				<Link
					to={ BASE_URL + '&path=follow-up' }
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
					{ hasData ? (
						<Table className="whitespace-nowrap sm:whitespace-normal">
							<Table.Head>
								<Table.HeadCell>
									{ __(
										'User Name',
										'woo-cart-abandonment-recovery'
									) }
								</Table.HeadCell>
								<Table.HeadCell>
									{ __(
										'Email',
										'woo-cart-abandonment-recovery'
									) }
								</Table.HeadCell>
								<Table.HeadCell>
									{ __(
										'Cart Total',
										'woo-cart-abandonment-recovery'
									) }
								</Table.HeadCell>
								<Table.HeadCell>
									{ __(
										'Order Status',
										'woo-cart-abandonment-recovery'
									) }
								</Table.HeadCell>
								<Table.HeadCell>
									{ __(
										'Date & Time',
										'woo-cart-abandonment-recovery'
									) }
								</Table.HeadCell>
							</Table.Head>
							<Table.Body>
								{ logs.map( ( item, index ) => (
									<Table.Row key={ index }>
										<Table.Cell>
											{ item?.userName ||
												__(
													'Not Available',
													'woo-cart-abandonment-recovery'
												) }
										</Table.Cell>
										<Table.Cell>
											{ item?.email || '-' }
										</Table.Cell>
										<Table.Cell>
											{ parse( item?.cartTotal || '-' ) }
										</Table.Cell>
										<Table.Cell>
											<OrderStatusBadge
												status={
													item?.orderStatus || '-'
												}
											/>
										</Table.Cell>
										<Table.Cell>
											{ item?.dateTime || '-' }
										</Table.Cell>
									</Table.Row>
								) ) }
							</Table.Body>
						</Table>
					) : (
						<EmptyBlock
							icon={
								<ExclamationTriangleIcon className="h-12 w-12 text-yellow-500" />
							}
							title={ __(
								'No Follow-Up Reports Yet',
								'woo-cart-abandonment-recovery'
							) }
							description={ __(
								'Reports will show up here once customers start abandoning their carts.',
								'woo-cart-abandonment-recovery'
							) }
						/>
					) }
				</>
			) }
		</SectionWrapper>
	);
};

export default RecentEmailLogs;
