import { Table, Input, Button, Switch } from '@bsf/force-ui';
import {
	TrashIcon,
	MagnifyingGlassIcon,
	PlusIcon,
	PencilIcon,
	DocumentDuplicateIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import { ProUpgradeCta } from '@Components/pro';
import TemplatesNav from '../TemplatesNav';

const SmsPreview = () => {
	// Temporary mock data for table preview
	const data = [
		{
			id: 'sms-1',
			template_name: 'Welcome Back Reminder',
			sms_frequency: 15,
			sms_frequency_unit: 'MINUTE',
			sent: 12000,
			click_rate: 18.5,
			conversion_rate: 6.2,
			unsubscribe_rate: 0.8,
		},
		{
			id: 'sms-2',
			template_name: 'First Follow-up',
			sms_frequency: 2,
			sms_frequency_unit: 'HOUR',
			sent: 9800,
			click_rate: 15.1,
			conversion_rate: 5.4,
			unsubscribe_rate: 0.6,
		},
		{
			id: 'sms-3',
			template_name: '24h Nudge',
			sms_frequency: 1,
			sms_frequency_unit: 'DAY',
			sent: 8700,
			click_rate: 12.3,
			conversion_rate: 4.1,
			unsubscribe_rate: 0.5,
		},
		{
			id: 'sms-4',
			template_name: 'Last Chance Offer',
			sms_frequency: 3,
			sms_frequency_unit: 'DAY',
			sent: 7600,
			click_rate: 20.7,
			conversion_rate: 8.9,
			unsubscribe_rate: 1.2,
		},
		{
			id: 'sms-5',
			template_name: 'Cart Value Upsell',
			sms_frequency: 6,
			sms_frequency_unit: 'HOUR',
			sent: 6400,
			click_rate: 17.9,
			conversion_rate: 7.3,
			unsubscribe_rate: 0.9,
		},
		{
			id: 'sms-6',
			template_name: 'Free Shipping Push',
			sms_frequency: 12,
			sms_frequency_unit: 'HOUR',
			sent: 7100,
			click_rate: 22.4,
			conversion_rate: 10.1,
			unsubscribe_rate: 1.0,
		},
		{
			id: 'sms-7',
			template_name: 'Weekend Deal Teaser',
			sms_frequency: 2,
			sms_frequency_unit: 'DAY',
			sent: 5800,
			click_rate: 14.6,
			conversion_rate: 5.0,
			unsubscribe_rate: 0.7,
		},
		{
			id: 'sms-8',
			template_name: 'VIP Customer Ping',
			sms_frequency: 30,
			sms_frequency_unit: 'MINUTE',
			sent: 3200,
			click_rate: 28.9,
			conversion_rate: 14.8,
			unsubscribe_rate: 0.3,
		},
		{
			id: 'sms-9',
			template_name: 'Low Stock Alert',
			sms_frequency: 45,
			sms_frequency_unit: 'MINUTE',
			sent: 4100,
			click_rate: 31.2,
			conversion_rate: 16.5,
			unsubscribe_rate: 0.4,
		},
		{
			id: 'sms-10',
			template_name: 'Bundle Reminder',
			sms_frequency: 4,
			sms_frequency_unit: 'HOUR',
			sent: 5300,
			click_rate: 19.8,
			conversion_rate: 9.2,
			unsubscribe_rate: 0.8,
		},
		{
			id: 'sms-11',
			template_name: 'Seasonal Promo',
			sms_frequency: 1,
			sms_frequency_unit: 'DAY',
			sent: 8900,
			click_rate: 16.4,
			conversion_rate: 6.7,
			unsubscribe_rate: 1.1,
		},
		{
			id: 'sms-12',
			template_name: 'Review Request',
			sms_frequency: 2,
			sms_frequency_unit: 'DAY',
			sent: 6700,
			click_rate: 9.5,
			conversion_rate: 3.2,
			unsubscribe_rate: 0.6,
		},
		{
			id: 'sms-13',
			template_name: 'Win-Back Offer',
			sms_frequency: 5,
			sms_frequency_unit: 'DAY',
			sent: 4500,
			click_rate: 13.7,
			conversion_rate: 4.9,
			unsubscribe_rate: 1.4,
		},
	];

	const formatDuration = ( value, unit ) => {
		const units = {
			MINUTE: 'Minutes',
			HOUR: 'Hours',
			DAY: 'Days',
		};
		unit = units[ unit ];
		const formattedUnit =
			parseInt( value ) === 1
				? unit.slice( 0, -1 ) // remove 's' for singular: "days" -> "day"
				: unit;

		return `${ value } ${ formattedUnit }`;
	};

	return (
		<div className="flex flex-col gap-4">
			<div className="flex flex-col md:flex-row gap-4 md:gap-0 justify-between relative">
				<TemplatesNav currentTab={ 'sms' } />
				<div className="flex flex-col md:flex-row gap-4">
					<Input
						placeholder={ __(
							'Search…',
							'woo-cart-abandonment-recovery'
						) }
						prefix={
							<MagnifyingGlassIcon className="h-6 w-6 text-gray-500" />
						}
						size="md"
						type="text"
						aria-label={ __(
							'Search',
							'woo-cart-abandonment-recovery'
						) }
						className="w-full lg:w-52"
						disabled={ true }
					/>
					<Button
						iconPosition="left"
						size="sm"
						tag="button"
						type="button"
						variant="outline"
						disabled={ true }
					>
						{ __(
							'Restore Default Templates',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
					<Button
						className=""
						icon={ <PlusIcon aria-label="icon" role="img" /> }
						iconPosition="left"
						size="sm"
						tag="button"
						type="button"
						variant="primary"
						disabled={ true }
					>
						{ __(
							'Create New Template',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
				</div>
			</div>
			<div className="relative">
				<Table
					checkboxSelection={ true }
					className="whitespace-nowrap sm:whitespace-normal"
				>
					<Table.Head>
						<Table.HeadCell>
							{ __(
								'Template Name',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'Trigger After',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Sent',
									'woo-cart-abandonment-recovery'
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Click Rate',
									'woo-cart-abandonment-recovery'
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Conversion Rate',
									'woo-cart-abandonment-recovery'
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Unsubscribed',
									'woo-cart-abandonment-recovery'
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							{ __( 'Status', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
						<Table.HeadCell className="text-right">
							{ __( 'Actions', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
					</Table.Head>
					<Table.Body>
						{ data.map( ( item ) => (
							<Table.Row key={ item.id } value={ item }>
								<Table.Cell>
									<span className="cursor-pointer hover:text-flamingo-400 focus-visible:text-flamingo-400">
										{ item.template_name }
									</span>
								</Table.Cell>

								<Table.Cell>
									{ formatDuration(
										item.sms_frequency,
										item.sms_frequency_unit
									) }
								</Table.Cell>
								<Table.Cell className="text-center">
									{ item?.sent || '-' }
								</Table.Cell>
								<Table.Cell className="text-center">
									{ item?.click_rate
										? `${ item.click_rate }%`
										: '-' }
								</Table.Cell>
								<Table.Cell className="text-center">
									{ item?.conversion_rate
										? `${ item.conversion_rate }%`
										: '-' }
								</Table.Cell>
								<Table.Cell className="text-center">
									{ item?.unsubscribe_rate
										? `${ item.unsubscribe_rate }%`
										: '-' }
								</Table.Cell>
								<Table.Cell>
									<Switch
										value={ true }
										size="md"
										className="border-none moderncart-toggle-field"
										role="switch"
									/>
								</Table.Cell>
								<Table.Cell>
									<div className="flex items-center justify-end gap-2">
										<Button
											variant="ghost"
											icon={
												<PencilIcon className="h-6 w-6" />
											}
											size="xs"
											className="text-gray-500 hover:text-flamingo-400"
											aria-label={ __(
												'Edit',
												'woo-cart-abandonment-recovery'
											) }
										/>
										<Button
											variant="ghost"
											icon={
												<DocumentDuplicateIcon className="h-6 w-6" />
											}
											size="xs"
											className="text-gray-500 hover:text-flamingo-400"
											aria-label={ __(
												'Duplicate',
												'woo-cart-abandonment-recovery'
											) }
										/>
										<Button
											variant="ghost"
											icon={
												<TrashIcon className="h-6 w-6" />
											}
											size="xs"
											className="text-gray-500 hover:text-red-600"
										/>
									</div>
								</Table.Cell>
							</Table.Row>
						) ) }
					</Table.Body>
				</Table>
				<ProUpgradeCta
					isVisible={ true }
					highlightText={ __(
						'Unlock Pro Features',
						'woo-cart-abandonment-recovery'
					) }
					mainTitle={ __(
						'Cart Abandonment Recovery Pro is Here 🔥',
						'woo-cart-abandonment-recovery'
					) }
					description={ __(
						"You've seen how emails bring shoppers back. With Pro, you'll unlock advanced tools that recover more carts, boost profits, and grow your store faster.",
						'woo-cart-abandonment-recovery'
					) }
					usps={ [
						__(
							'Product Reports',
							'woo-cart-abandonment-recovery'
						),
						__(
							'SMS + WhatsApp Followups',
							'woo-cart-abandonment-recovery'
						),
						__( 'Smart Rules', 'woo-cart-abandonment-recovery' ),
						__(
							'Advanced Automations',
							'woo-cart-abandonment-recovery'
						),
						__( 'And More…', 'woo-cart-abandonment-recovery' ),
					] }
					actionBtnUrlArgs={
						'utm_source=wcar-dashboard&utm_medium=free-wcar&utm_campaign=go-wcar-pro'
					}
					footerMessage={ '' }
					backgroundBlur={ true }
				/>
			</div>
		</div>
	);
};

export default SmsPreview;

