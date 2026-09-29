import { Table, Title } from '@bsf/force-ui';
import parse from 'html-react-parser';

import SectionWrapper from '@Components/common/SectionWrapper';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';

const UserOrderDetails = ( { orderDetails, isLoading } ) => {
	return (
		<SectionWrapper className="flex flex-col gap-4">
			<Title
				size="sm"
				tag="h2"
				title="User Order Details"
				className="[&_h2]:text-gray-900"
			/>
			{ isLoading ? (
				<div className="flex flex-col gap-4">
					<SkeletonLoader height="40px" />
					{ [ ...Array( 3 ) ].map( ( _, index ) => (
						<div key={ index } className="flex gap-4">
							<SkeletonLoader
								width="60px"
								height="60px"
								borderRadius="8px"
							/>
							<div className="flex-1 flex flex-col gap-2">
								<SkeletonLoader height="20px" width="70%" />
								<SkeletonLoader height="16px" width="40%" />
							</div>
						</div>
					) ) }
					<div className="mt-2">
						<SkeletonLoader height="80px" />
					</div>
				</div>
			) : (
				<Table>
					<Table.Head>
						<Table.HeadCell>Items</Table.HeadCell>
						<Table.HeadCell>Quantity</Table.HeadCell>
						<Table.HeadCell>Price</Table.HeadCell>
						<Table.HeadCell>Subtotal</Table.HeadCell>
					</Table.Head>
					<Table.Body>
						{ orderDetails.items.map( ( item, index ) => (
							<Table.Row key={ index }>
								<Table.Cell>
									<div className="flex items-center gap-4 text-gray-900">
										<img
											src={ item.imageUrl }
											alt=""
											className="w-12 h-12 object-cover rounded-md border border-solid border-gray-200"
										/>
										{ parse( item.name ) }
									</div>
								</Table.Cell>
								<Table.Cell className="font-medium">
									{ item.quantity }
								</Table.Cell>
								<Table.Cell className="font-medium">
									{ item.price }
								</Table.Cell>
								<Table.Cell className="font-medium">
									{ item.subtotal }
								</Table.Cell>
							</Table.Row>
						) ) }
						<Table.Row className="bg-background-secondary">
							<Table.Cell></Table.Cell>
							<Table.Cell></Table.Cell>
							<Table.Cell>
								<div className="flex flex-col gap-3">
									<span className="text-gray-900 font-medium">
										Discount:
									</span>
									<span className="text-gray-900 font-medium">
										Others:
									</span>
									<span className="text-gray-900 font-medium">
										Shipping:
									</span>
									<span className="text-gray-900 font-medium">
										Cart Total:
									</span>
								</div>
							</Table.Cell>
							<Table.Cell>
								<div className="flex flex-col gap-3">
									<span className="font-medium">
										{ orderDetails.summary.discount }
									</span>
									<span className="font-medium">
										{ orderDetails.summary.others }
									</span>
									<span className="font-medium">
										{ orderDetails.summary.shipping }
									</span>
									<span className="font-medium">
										{ orderDetails.summary.cartTotal }
									</span>
								</div>
							</Table.Cell>
						</Table.Row>
					</Table.Body>
				</Table>
			) }
		</SectionWrapper>
	);
};

export default UserOrderDetails;
