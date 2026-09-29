import { Container, Dialog, Title } from '@bsf/force-ui';
import {
	ArrowTopRightOnSquareIcon,
	BookOpenIcon,
	PlayCircleIcon,
	ChevronRightIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import AppTooltip from '@Components/common/AppTooltip';

const KnowledgeBase = () => {
	const data = cart_abandonment_admin.knowledge_base;

	return (
		<Dialog
			design="simple"
			exitOnEsc
			scrollLock
			trigger={
				<div
					role="button"
					aria-label={ __(
						'Knowledge Base',
						'woo-cart-abandonment-recovery'
					) }
					tabIndex="0"
					className="flex justify-center items-center cursor-pointer focus-visible:outline-auto"
				>
					<AppTooltip
						content={ __(
							'Knowledge Base',
							'woo-cart-abandonment-recovery'
						) }
						position="bottom"
						className="z-50"
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							fill="none"
							viewBox="0 0 24 24"
							strokeWidth={ 1.5 }
							stroke="currentColor"
							className="h-5 w-5 cursor text-gray-500"
							aria-hidden="true"
							focusable="false"
							role="button"
							tabIndex={ 0 }
						>
							<path
								strokeLinecap="round"
								strokeLinejoin="round"
								d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
							/>
						</svg>
					</AppTooltip>
				</div>
			}
		>
			<Dialog.Backdrop />
			<Dialog.Panel className="gap-0 m-5 sm:m-0 sm:w-3/5">
				<Dialog.Header className="p-5 border-0 border-b border-solid border-gray-200">
					<div className="flex items-center justify-between">
						<Dialog.Title className="text-2xl font-semibold">
							{ __(
								'Knowledge Base',
								'woo-cart-abandonment-recovery'
							) }
						</Dialog.Title>
						<Dialog.CloseButton
							className="[&>svg]:size-5"
							type="button"
						/>
					</div>
				</Dialog.Header>
				<Dialog.Body className="px-0 flex flex-col lg:flex-row">
					<div className="p-5 flex flex-col flex-1 border-0 border-r border-solid border-gray-200 max-h-[550px] overflow-y-auto">
						{ data?.map( ( item, index ) => (
							<a
								key={ index }
								href={ item.url }
								className="flex items-center justify-between no-underline text-gray-800 rounded-md p-2 hover:bg-gray-50 cursor-pointer group"
								target="_blank"
								rel="noreferrer"
							>
								<div className="flex items-center">
									<ChevronRightIcon className="size-3" />
									<span className="text-base ml-2">
										{ item.title }
									</span>
								</div>
								<div className="invisible group-hover:visible">
									<ArrowTopRightOnSquareIcon className="size-5" />
								</div>
							</a>
						) ) }
					</div>
					<Container
						align="center"
						containerType="flex"
						direction="column"
						gap="md"
						justify="start"
						className="p-5 lg:w-1/3"
					>
						<Container.Item className="w-full p-4 border border-solid border-gray-200 rounded-md">
							<Title
								title={ __(
									'Need Help?',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'We aim to answer all priority support requests within 2-3 hours.',
									'woo-cart-abandonment-recovery'
								) }
								icon={
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										strokeWidth={ 1.7 }
										stroke="currentColor"
										strokeLinecap="round"
										strokeLinejoin="round"
									>
										<path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"></path>
										<path d="M21 16v2a4 4 0 0 1-4 4h-5"></path>
									</svg>
								}
								iconPosition="left"
								size="xs"
								tag="h2"
								className="[&_svg]:size-5 [&_svg]:text-text-secondary text-gray-900 font-medium"
							/>
							<a
								href="https://cartflows.com/support"
								className="no-underline mt-2 inline-flex gap-1.5 items-center text-sm font-medium text-flamingo-400 hover:text-primary-600"
								target="_blank"
								rel="noreferrer"
							>
								{ __(
									'Request Support',
									'woo-cart-abandonment-recovery'
								) }
								<ArrowTopRightOnSquareIcon class="h-4 w-4" />
							</a>
						</Container.Item>
						<Container.Item className="w-full p-4 border border-solid border-gray-200 rounded-md">
							<Title
								title={ __(
									'All Documentation',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'Browse documentation, reference material, and tutorials for Cart Abandonment.',
									'woo-cart-abandonment-recovery'
								) }
								icon={ <BookOpenIcon /> }
								iconPosition="left"
								size="xs"
								tag="h2"
								className="[&_svg]:size-5 [&_svg]:text-text-secondary text-gray-900 font-medium"
							/>
							<a
								href="https://cartflows.com/docs-category/cart-abandonment/"
								className="no-underline mt-2 inline-flex gap-1.5 items-center text-sm font-medium text-flamingo-400 hover:text-primary-600"
								target="_blank"
								rel="noreferrer"
							>
								{ __(
									'View documentation',
									'woo-cart-abandonment-recovery'
								) }
								<ArrowTopRightOnSquareIcon class="h-4 w-4" />
							</a>
						</Container.Item>
						<Container.Item className="w-full p-4 border border-solid border-gray-200 rounded-md">
							<Title
								title={ __(
									'Videos',
									'woo-cart-abandonment-recovery'
								) }
								description={ __(
									'Browse tutorial videos on our YouTube channel.',
									'woo-cart-abandonment-recovery'
								) }
								icon={ <PlayCircleIcon /> }
								iconPosition="left"
								size="xs"
								tag="h2"
								className="[&_svg]:size-5 [&_svg]:text-text-secondary text-gray-900 font-medium"
							/>
							<a
								href="https://www.youtube.com/c/CartFlows"
								className="no-underline mt-2 inline-flex gap-1.5 items-center text-sm font-medium text-flamingo-400 hover:text-primary-600"
								target="_blank"
								rel="noreferrer"
							>
								{ __(
									'Youtube Channel',
									'woo-cart-abandonment-recovery'
								) }
								<ArrowTopRightOnSquareIcon class="h-4 w-4" />
							</a>
						</Container.Item>
					</Container>
				</Dialog.Body>
			</Dialog.Panel>
		</Dialog>
	);
};

export default KnowledgeBase;
