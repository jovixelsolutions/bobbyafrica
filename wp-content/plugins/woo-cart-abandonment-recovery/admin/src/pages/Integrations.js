import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Container, Sidebar, Menu, Label } from '@bsf/force-ui';
import {
	LinkIcon,
	ChatBubbleLeftEllipsisIcon,
} from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import { UnplugIcon } from 'lucide-react';

import { useStateValue } from '@Store';
import Ottokit from '@Components/integrations/Ottokit';
import RenderFields from '@Components/RenderFields';
import ConditionsHelper from '@Utils/conditions';
import TabWrapper from '@Components/common/TabWrapper';

const iconMap = {
	'webhook-integration': <LinkIcon className="h-6 w-6 text-gray-500" />,
	'sms-integration': (
		<ChatBubbleLeftEllipsisIcon className="h-6 w-6 text-gray-500" />
	),
	'whatsapp-integration': (
		<svg
			width="20"
			height="20"
			viewBox="0 0 20 20"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<path
				d="M10 1.875C8.57754 1.87537 7.1801 2.24905 5.94733 2.9587C4.71456 3.66834 3.68965 4.68909 2.97503 5.91893C2.26042 7.14878 1.88113 8.54462 1.87507 9.96697C1.86902 11.3893 2.23641 12.7883 2.94054 14.0242L2.20893 16.5848C2.16117 16.7519 2.15899 16.9289 2.20261 17.0971C2.24623 17.2654 2.33406 17.419 2.45701 17.542C2.57996 17.6649 2.73355 17.7527 2.90187 17.7963C3.07018 17.8399 3.24709 17.8377 3.41427 17.79L5.975 17.0584C7.05611 17.6748 8.26435 18.0345 9.50653 18.11C10.7487 18.1855 11.9916 17.9747 13.1394 17.4938C14.2872 17.013 15.3092 16.2749 16.1266 15.3365C16.944 14.3982 17.5349 13.2847 17.8537 12.0818C18.1726 10.879 18.2109 9.61897 17.9657 8.39898C17.7205 7.179 17.1983 6.03163 16.4394 5.04538C15.6805 4.05914 14.7052 3.26037 13.5888 2.71068C12.4723 2.161 11.2445 1.87508 10 1.875ZM10 17.4739C8.65749 17.4749 7.33958 17.1137 6.1851 16.4286C6.14704 16.4059 6.10475 16.3913 6.06082 16.3856C6.0169 16.3799 5.97227 16.3832 5.92969 16.3954L3.23566 17.1651C3.17993 17.181 3.12096 17.1817 3.06486 17.1672C3.00875 17.1526 2.95756 17.1234 2.91658 17.0824C2.8756 17.0414 2.84633 16.9902 2.8318 16.9341C2.81727 16.878 2.818 16.819 2.83393 16.7633L3.60359 14.0695C3.61575 14.0269 3.61908 13.9823 3.61338 13.9384C3.60767 13.8945 3.59306 13.8522 3.57042 13.8141C2.72848 12.3958 2.37931 10.7388 2.5773 9.10129C2.7753 7.4638 3.50934 5.93777 4.66509 4.76092C5.82084 3.58406 7.33338 2.82247 8.96711 2.59478C10.6008 2.36708 12.264 2.68607 13.6975 3.50206C15.1311 4.31804 16.2545 5.5852 16.8927 7.10614C17.531 8.62707 17.6482 10.3164 17.2262 11.9109C16.8043 13.5054 15.8667 14.9156 14.5597 15.9218C13.2527 16.928 11.6495 17.4737 10 17.4739ZM14.104 11.506L12.4417 10.5561C12.2913 10.4703 12.1208 10.426 11.9476 10.4278C11.7744 10.4297 11.6049 10.4776 11.4564 10.5666L10.3346 11.2396C9.65547 10.8956 9.1038 10.3439 8.75975 9.66487L9.4328 8.54316C9.52181 8.39463 9.56969 8.2251 9.57153 8.05195C9.57337 7.87881 9.5291 7.70829 9.44327 7.55791L8.49339 5.89565C8.41583 5.76032 8.30395 5.64784 8.16903 5.56956C8.03412 5.49127 7.88095 5.44995 7.72496 5.44976C7.03713 5.44993 6.37737 5.72256 5.89009 6.20799C5.40281 6.69343 5.1277 7.35212 5.12496 8.0399C5.1237 8.93692 5.2993 9.82538 5.64172 10.6545C5.98413 11.4836 6.48665 12.237 7.12053 12.8718C7.7544 13.5065 8.5072 14.0101 9.33587 14.3537C10.1645 14.6972 11.0528 14.8741 11.9499 14.8741H11.9596C12.6475 14.8714 13.3062 14.5963 13.7917 14.109C14.2771 13.6218 14.5498 12.9621 14.55 12.2743C14.5498 12.1183 14.5085 11.9652 14.4302 11.8303C14.3519 11.6954 14.2394 11.5835 14.104 11.506ZM11.9587 14.2242H11.9499C11.1383 14.2242 10.3346 14.0642 9.58484 13.7533C8.83509 13.4425 8.15398 12.9869 7.58047 12.4126C7.00696 11.8383 6.5523 11.1566 6.24249 10.4064C5.93268 9.6563 5.77381 8.85244 5.77496 8.04086C5.77721 7.52521 5.98365 7.03146 6.34911 6.66766C6.71457 6.30386 7.20928 6.09964 7.72496 6.09971C7.76638 6.09977 7.80706 6.11074 7.84289 6.13152C7.87872 6.1523 7.90843 6.18216 7.92904 6.21809L8.87893 7.88043C8.90753 7.93056 8.92228 7.98739 8.92166 8.0451C8.92105 8.10281 8.90509 8.15931 8.87543 8.20882L8.11256 9.48017C8.08559 9.52511 8.06989 9.5759 8.0668 9.62822C8.06371 9.68054 8.07333 9.73283 8.09483 9.78063C8.51901 10.7247 9.27474 11.4803 10.2188 11.9045C10.2666 11.926 10.3189 11.9356 10.3713 11.9325C10.4236 11.9294 10.4744 11.9137 10.5194 11.8867L11.7907 11.1239C11.8402 11.0943 11.8968 11.0783 11.9545 11.0777C12.0122 11.077 12.0691 11.0918 12.1192 11.1204L13.7816 12.0703C13.8175 12.0909 13.8474 12.1206 13.8682 12.1564C13.889 12.1922 13.8999 12.2329 13.9 12.2743C13.9 12.7899 13.6958 13.2846 13.332 13.65C12.9682 14.0155 12.4744 14.2219 11.9587 14.2242Z"
				fill="#6B7280"
				stroke="#6B7280"
				strokeWidth="0.6"
			/>
		</svg>
	),
	'ottokit-integration': <UnplugIcon className="h-6 w-6 text-gray-500" />,
};

const Integrations = () => {
	const [ integrationsTab, setIntegrationsTab ] = useState( '' );
	const navigate = useNavigate();
	const query = new URLSearchParams( useLocation().search );
	const [ state ] = useStateValue();
	const settingsData = state?.settingsData || {};
	const fieldSettings = settingsData?.fields?.integrations || {};
	const settingsValues = settingsData?.values || {};

	const tabs = Object.values( fieldSettings ).sort(
		( a, b ) => a.priority - b.priority
	);

	const slugs = tabs.map( ( tab ) => tab.slug );
	const active = slugs.includes( query.get( 'tab' ) )
		? query.get( 'tab' )
		: getIntegrationsTab();

	function getIntegrationsTab() {
		return integrationsTab || slugs[ 0 ];
	}

	const navigateTo = ( navigateTab ) => {
		setIntegrationsTab( navigateTab );
		navigate( {
			search: `?page=woo-cart-abandonment-recovery&path=integrations&tab=${ navigateTab }`,
		} );
	};

	const conditions = new ConditionsHelper();

	const navigation = tabs.map( ( tab ) => {
		// Special handling for Ottokit tab
		if ( tab.slug === 'ottokit-integration' ) {
			return {
				name: tab.title,
				slug: tab.slug,
				icon: iconMap[ tab.slug ] || (
					<UnplugIcon className="h-6 w-6 text-gray-500" />
				),
				component: <Ottokit />,
			};
		}

		return {
			name: tab.title,
			slug: tab.slug,
			icon: iconMap[ tab.slug ] || (
				<LinkIcon className="h-6 w-6 text-gray-500" />
			),
			component: (
				<TabWrapper title={ tab.title }>
					{ tab.fields &&
						Object.keys( tab.fields ).map( ( field ) => {
							const data = tab.fields[ field ];
							const value =
								settingsValues[ data.name ] ?? data.value ?? '';
							const isActive = conditions.isActiveControl(
								data,
								settingsValues
							);
							return (
								<RenderFields
									key={ data.name || field }
									data={ data }
									value={ value }
									isActive={ isActive }
								/>
							);
						} ) }
				</TabWrapper>
			),
		};
	} );

	return (
		<div className="flex gap-4 bg-primary-background max-h-[calc(100%_-_6rem)]">
			<div className="h-full min-h-screen flex flex-col w-auto sticky top-0 lg:top-4 sidebar">
				<Sidebar
					borderOn
					className="!h-full md:pl-3 lg:p-4 lg:w-64 border-none box-border flex-grow"
				>
					<Sidebar.Body>
						<Sidebar.Item>
							<Menu size="md" className="p-0">
								<Menu.List open>
									{ navigation.map( ( item ) => (
										<Menu.Item
											key={ item.slug }
											active={ active === item.slug }
											onClick={ () =>
												navigateTo( item.slug )
											}
											className={ `lg:justify-start justify-center rounded-md ${
												active === item.slug
													? 'bg-flamingo-50'
													: ''
											} hover:bg-flamingo-50` }
										>
											{ item.icon }
											<span className="lg:block hidden text-gray-900">
												{ item.name }
											</span>
										</Menu.Item>
									) ) }
								</Menu.List>
							</Menu>
							<Container
								containerType="flex"
								direction="column"
								gap="xs"
								className="lg:p-4 mt-7 lg:border lg:border-solid lg:border-flamingo-400 rounded-md"
							>
								<Container.Item className="lg:flex gap-2 items-center hidden">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										strokeWidth={ 2 }
										stroke="currentColor"
										className="h-5 w-5 text-flamingo-400"
										strokeLinecap="round"
										strokeLinejoin="round"
									>
										<path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"></path>
										<path d="M21 16v2a4 4 0 0 1-4 4h-5"></path>
									</svg>
									<Label size="md" className="font-semibold">
										{ __(
											'Need Support?',
											'woo-cart-abandonment-recovery'
										) }
									</Label>
								</Container.Item>
								<Container.Item className="lg:block hidden">
									<p className="font-normal text-sm text-text-secondary m-0 pb-2">
										{ __(
											"We're happy to help!",
											'woo-cart-abandonment-recovery'
										) }
									</p>
								</Container.Item>
								<Container.Item>
									<a
										href="https://cartflows.com/support"
										target="_blank"
										rel="noreferrer"
										className="flex justify-center p-2.5 no-underline text-white hover:text-white bg-primary-600 hover:bg-primary-600 rounded-md box-content outline-0 hover:outline-0 focus:ring-0 focus-visible:ring-1 ring-black"
									>
										<svg
											xmlns="http://www.w3.org/2000/svg"
											fill="none"
											viewBox="0 0 24 24"
											strokeWidth={ 1.7 }
											stroke="currentColor"
											className="h-5 w-5 block lg:hidden"
											strokeLinecap="round"
											strokeLinejoin="round"
										>
											<path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"></path>
											<path d="M21 16v2a4 4 0 0 1-4 4h-5"></path>
										</svg>
										<span className="text-center w-full text-sm font-semibold lg:block hidden p-0">
											{ __(
												'Request Support',
												'woo-cart-abandonment-recovery'
											) }
										</span>
									</a>
								</Container.Item>
							</Container>
						</Sidebar.Item>
					</Sidebar.Body>
				</Sidebar>
			</div>
			<Container
				className={ `w-full ${
					'ottokit-integration' !== active ? 'max-w-[43.5rem]' : ''
				} mx-auto mt-8 pr-4 pb-5 gap-0 relative` }
				direction="column"
			>
				{
					navigation.find( ( item ) => item.slug === active )
						?.component
				}
			</Container>
		</div>
	);
};

export default Integrations;

