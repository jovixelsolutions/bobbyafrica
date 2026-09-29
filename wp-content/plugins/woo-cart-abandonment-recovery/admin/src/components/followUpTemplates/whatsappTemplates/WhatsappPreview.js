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

const WhatsappPreview = () => {
	// Temporary mock data for table preview
	const data = [
		{
			id: 'message-1',
			template_name: 'Welcome Reminder',
			whatsapp_frequency: 10,
			whatsapp_frequency_unit: 'MINUTE',
		},
		{
			id: 'message-2',
			template_name: 'First Follow-up',
			whatsapp_frequency: 1,
			whatsapp_frequency_unit: 'HOUR',
		},
		{
			id: 'message-3',
			template_name: '24h Cart Nudge',
			whatsapp_frequency: 24,
			whatsapp_frequency_unit: 'HOUR',
		},
		{
			id: 'message-4',
			template_name: 'Final Offer Alert',
			whatsapp_frequency: 2,
			whatsapp_frequency_unit: 'DAY',
		},
		{
			id: 'message-5',
			template_name: 'Upsell Suggestion',
			whatsapp_frequency: 12,
			whatsapp_frequency_unit: 'HOUR',
		},
		{
			id: 'message-6',
			template_name: 'Free Shipping Reminder',
			whatsapp_frequency: 8,
			whatsapp_frequency_unit: 'HOUR',
		},
		{
			id: 'message-7',
			template_name: 'Weekend Special Teaser',
			whatsapp_frequency: 3,
			whatsapp_frequency_unit: 'DAY',
		},
		{
			id: 'message-8',
			template_name: 'VIP Notification',
			whatsapp_frequency: 20,
			whatsapp_frequency_unit: 'MINUTE',
		},
		{
			id: 'message-9',
			template_name: 'Low Stock Alert',
			whatsapp_frequency: 30,
			whatsapp_frequency_unit: 'MINUTE',
		},
		{
			id: 'message-10',
			template_name: 'Bundle Promotion',
			whatsapp_frequency: 6,
			whatsapp_frequency_unit: 'HOUR',
		},
		{
			id: 'message-11',
			template_name: 'Seasonal Campaign',
			whatsapp_frequency: 2,
			whatsapp_frequency_unit: 'DAY',
		},
		{
			id: 'message-12',
			template_name: 'Review Request',
			whatsapp_frequency: 3,
			whatsapp_frequency_unit: 'DAY',
		},
		{
			id: 'message-13',
			template_name: 'Win-Back Campaign',
			whatsapp_frequency: 7,
			whatsapp_frequency_unit: 'DAY',
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
				<TemplatesNav currentTab={ 'whatsapp' } />
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
										item.whatsapp_frequency,
										item.whatsapp_frequency_unit
									) }
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

export default WhatsappPreview;

