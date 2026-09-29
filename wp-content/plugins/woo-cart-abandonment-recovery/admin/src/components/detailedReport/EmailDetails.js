import { Table, Button, Title, Loader, Badge } from '@bsf/force-ui';
import AppTooltip from '@Components/common/AppTooltip';
import {
	ExclamationTriangleIcon,
	ExclamationCircleIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import { EmptyBlock } from '@Components/common/empty-blocks';

const EmailDetails = ( {
	scheduledEmails,
	isLoading,
	handleRescheduleEmails,
	buttonLoading,
	disabled,
} ) => {
	const emailStatus = ( status ) => {
		const config = {
			'-1': {
				label: __( 'Not Sent', 'woo-cart-abandonment-recovery' ),
				variant: 'red',
			},
			'-2': {
				label: __( 'Not Sent', 'woo-cart-abandonment-recovery' ),
				variant: 'red',
			},
			0: {
				label: __( 'Scheduled', 'woo-cart-abandonment-recovery' ),
				variant: 'yellow',
			},
			1: {
				label: __( 'Sent', 'woo-cart-abandonment-recovery' ),
				variant: 'green',
			},
		};
		if ( String( status ) === '-2' ) {
			return (
				<AppTooltip
					content={ __(
						'Rule Condition Failed',
						'woo-cart-abandonment-recovery'
					) }
					position="top"
				>
					<Badge
						icon={ <ExclamationCircleIcon className="" /> }
						label={ config[ status ].label }
						size="sm"
						type="pill"
						variant={ config[ status ].variant }
						className="w-fit cursor-pointer"
					/>
				</AppTooltip>
			);
		}
		return (
			<Badge
				label={ config[ status ].label }
				size="sm"
				type="pill"
				variant={ config[ status ].variant }
				className="w-fit cursor-default"
			/>
		);
	};
	return (
		<SectionWrapper className="flex flex-col gap-4">
			<div className="flex items-center justify-between">
				<Title
					size="sm"
					tag="h2"
					title={ __(
						'Email Details',
						'woo-cart-abandonment-recovery'
					) }
					className="[&_h2]:text-gray-900"
				/>
				<Button
					className="bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300"
					size="sm"
					tag="button"
					type="button"
					variant="outline"
					onClick={ handleRescheduleEmails }
					disabled={ isLoading || buttonLoading || disabled }
					icon={
						buttonLoading && (
							<Loader
								className="text-flamingo-400 p-0"
								icon={ null }
								size="sm"
								variant="primary"
							/>
						)
					}
					iconPosition="left"
				>
					{ __(
						'Reschedule Emails',
						'woo-cart-abandonment-recovery'
					) }
				</Button>
			</div>
			{ isLoading ? (
				<div className="flex flex-col gap-4">
					<SkeletonLoader height="40px" />
					{ [ ...Array( 3 ) ].map( ( _, index ) => (
						<SkeletonLoader key={ index } height="50px" />
					) ) }
				</div>
			) : scheduledEmails.length > 0 ? (
				<Table>
					<Table.Head>
						<Table.HeadCell>
							{ __(
								'Scheduled Templates',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'Email Subject',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __( 'Coupon', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __( 'Status', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'Scheduled At',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
					</Table.Head>
					<Table.Body>
						{ scheduledEmails.map( ( item, index ) => (
							<Table.Row key={ index }>
								<Table.Cell>{ item.template_name }</Table.Cell>
								<Table.Cell>{ item.email_subject }</Table.Cell>
								<Table.Cell>
									{ item.coupon_code
										? item.coupon_code
										: '-' }
								</Table.Cell>
								<Table.Cell>
									{ emailStatus( item.email_sent ) }
								</Table.Cell>
								<Table.Cell>{ item.scheduled_time }</Table.Cell>
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
						'No data available',
						'woo-cart-abandonment-recovery'
					) }
					description={ __( '', 'woo-cart-abandonment-recovery' ) }
				/>
			) }
		</SectionWrapper>
	);
};

export default EmailDetails;
