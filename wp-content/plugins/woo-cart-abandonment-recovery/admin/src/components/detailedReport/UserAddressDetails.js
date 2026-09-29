import { Title, Badge } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import OrderStatusBadge from '@Components/common/OrderStatusBadge';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';

const UserAddressDetails = ( {
	userDetails,
	email,
	orderStatus,
	checkoutLink,
	unsubscribed,
	isLoading,
} ) => {
	return (
		<SectionWrapper className="flex flex-col gap-4">
			<div className="flex items-center justify-between">
				<Title
					size="sm"
					tag="h2"
					title={ __(
						'User Address Details',
						'woo-cart-abandonment-recovery'
					) }
					className="[&_h2]:text-gray-900"
				/>
				{ ! isLoading && (
					<div className="flex gap-2">
						<OrderStatusBadge status={ orderStatus } />
						{ '1' === unsubscribed && (
							<Badge
								label="Unsubscribed"
								size="sm"
								type="pill"
								variant="red"
								className="w-fit"
							/>
						) }
					</div>
				) }
			</div>
			{ isLoading ? (
				<div className="flex flex-col md:flex-row gap-8">
					<div className="flex-1 flex flex-col gap-3">
						<SkeletonLoader height="24px" width="150px" />
						{ [ ...Array( 8 ) ].map( ( _, index ) => (
							<SkeletonLoader
								key={ index }
								height="20px"
								// eslint-disable-next-line no-mixed-operators
								width={ `${ 70 + Math.random() * 30 }%` }
							/>
						) ) }
					</div>
					<div className="flex-1 flex flex-col gap-3">
						<SkeletonLoader height="24px" width="150px" />
						{ [ ...Array( 5 ) ].map( ( _, index ) => (
							<SkeletonLoader
								key={ index }
								height="20px"
								// eslint-disable-next-line no-mixed-operators
								width={ `${ 70 + Math.random() * 30 }%` }
							/>
						) ) }
						<div className="mt-2">
							<SkeletonLoader height="24px" width="200px" />
						</div>
					</div>
				</div>
			) : (
				<div className="flex flex-col md:flex-row gap-8">
					{ /* Billing Address */ }
					<div className="flex-1 flex flex-col gap-3">
						<Title
							size="xs"
							tag="h3"
							title={ __(
								'Billing Address:',
								'woo-cart-abandonment-recovery'
							) }
							className="[&_h2]:text-gray-900"
						/>
						<div className="flex flex-col gap-2">
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Full Name:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ `${ userDetails.wcf_first_name } ${ userDetails.wcf_last_name }` }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Contact Email:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ email }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Contact Number:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_phone_number }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Street Address 1:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_billing_address_1 }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Street Address 2:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_billing_address_2 }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Country, City:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_location }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'State:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_billing_state }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Zip Code:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_billing_postcode }
							</p>
						</div>
					</div>
					{ /* Shipping Address */ }
					<div className="flex-1 flex flex-col gap-3">
						<Title
							size="xs"
							tag="h3"
							title={ __(
								'Shipping Address:',
								'woo-cart-abandonment-recovery'
							) }
							className="[&_h2]:text-gray-900"
						/>
						<div className="flex flex-col gap-2">
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Street Address 1:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_shipping_address_1 }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Street Address 2:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_shipping_address_2 }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Country, City:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_shipping_city +
									', ' +
									userDetails.wcf_shipping_country }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'State:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_shipping_state }
							</p>
							<p className="m-0 text-gray-500">
								<span className="text-gray-900">
									{ __(
										'Zip Code:',
										'woo-cart-abandonment-recovery'
									) }
								</span>{ ' ' }
								{ userDetails.wcf_shipping_postcode }
							</p>
						</div>
						<div className="flex gap-4 items-center">
							<span className="text-base text-gray-900 font-medium">
								{ __(
									'Checkout Page:',
									'woo-cart-abandonment-recovery'
								) }
							</span>
							<a
								href={ checkoutLink }
								className="text-xs font-semibold text-flamingo-400 no-underline border-0"
							>
								{ __(
									'Link to Checkout Page',
									'woo-cart-abandonment-recovery'
								) }
							</a>
						</div>
					</div>
				</div>
			) }
		</SectionWrapper>
	);
};

export default UserAddressDetails;

