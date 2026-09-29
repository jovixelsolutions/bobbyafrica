import { Title, Container, Label } from '@bsf/force-ui';
import {
	QuestionMarkCircleIcon,
	ChatBubbleLeftRightIcon,
	StarIcon,
	TicketIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';

const QuickAccess = () => {
	return (
		<SectionWrapper className="lg:w-1/2 flex flex-col gap-2">
			<Container containerType="flex" direction="column" gap="xs">
				<Container.Item className="md:w-full lg:w-full p-1">
					<Title
						description=""
						icon={ null }
						iconPosition="right"
						size="xs"
						tag="h2"
						title={ __(
							'Quick Access',
							'woo-cart-abandonment-recovery'
						) }
						className="[&>*]:text-gray-900"
					/>
				</Container.Item>
				<Container.Item className="flex flex-col md:w-full lg:w-full bg-field-primary-background gap-1 p-1 rounded-lg">
					<a
						href="https://cartflows.com/docs-category/cart-abandonment"
						target="_blank"
						className="p-2 gap-1 items-center bg-background-primary rounded-md shadow-soft-shadow-inner no-underline hover:bg-background-secondary"
						rel="noreferrer"
					>
						<div className="flex gap-1 p-1 items-center">
							<QuestionMarkCircleIcon className="size-4 m-1 text-gray-900" />
							<Label className="py-0 px-1 font-normal cursor-pointer">
								{ __(
									'Help Center',
									'woo-cart-abandonment-recovery'
								) }
							</Label>
						</div>
					</a>
					<a
						href="https://www.facebook.com/groups/cartflows"
						target="_blank"
						className="p-2 gap-1 items-center bg-background-primary rounded-md shadow-soft-shadow-inner no-underline hover:bg-background-secondary"
						rel="noreferrer"
					>
						<div className="flex gap-1 p-1 items-center">
							<ChatBubbleLeftRightIcon className="size-4 m-1 text-gray-900" />
							<Label className="py-0 px-1 font-normal cursor-pointer">
								{ __(
									'Join the Community',
									'woo-cart-abandonment-recovery'
								) }
							</Label>
						</div>
					</a>
					<a
						href="https://wordpress.org/support/plugin/woo-cart-abandonment-recovery/reviews/#new-post"
						target="_blank"
						className="p-2 gap-1 items-center bg-background-primary rounded-md shadow-soft-shadow-inner no-underline hover:bg-background-secondary"
						rel="noreferrer"
					>
						<div className="flex gap-1 p-1 items-center">
							<StarIcon className="size-4 m-1 text-gray-900" />
							<Label className="py-0 px-1 font-normal cursor-pointer">
								{ __(
									'Rate Us',
									'woo-cart-abandonment-recovery'
								) }
							</Label>
						</div>
					</a>
					<a
						href="https://cartflows.com/support"
						target="_blank"
						className="p-2 gap-1 items-center bg-background-primary rounded-md shadow-soft-shadow-inner no-underline hover:bg-background-secondary"
						rel="noreferrer"
					>
						<div className="flex gap-1 p-1 items-center">
							<TicketIcon className="size-4 m-1 text-gray-900" />
							<Label className="py-0 px-1 font-normal cursor-pointer">
								{ __(
									'Open Support Ticket',
									'woo-cart-abandonment-recovery'
								) }
							</Label>
						</div>
					</a>
				</Container.Item>
			</Container>
		</SectionWrapper>
	);
};

export default QuickAccess;
