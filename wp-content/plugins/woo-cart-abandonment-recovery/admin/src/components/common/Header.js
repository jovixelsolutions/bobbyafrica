import { Topbar, Badge, HamburgerMenu, DropdownMenu } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import { Link, useLocation } from 'react-router-dom';
import { ChevronDownIcon } from '@heroicons/react/24/outline';
import React from 'react';

import WcarLogo from '@Images/wcar-icon.svg';
import WhatsNew from '@Components/whats-new';
import KnowledgeBase from '@Components/common/KnowledgeBase';
import { useStateValue } from '@Store';

const Header = () => {
	const [ state ] = useStateValue();
	const urlParams = new URLSearchParams( useLocation().search );
	const currentPath = urlParams.get( 'path' ) || 'dashboard';
	const isLicenseActivated = state?.licenseStatus === '1' ? true : false;

	const menus = [
		{
			name: __( 'Dashboard', 'woo-cart-abandonment-recovery' ),
			path: 'dashboard',
		},
		{
			name: __( 'Follow Up Templates', 'woo-cart-abandonment-recovery' ),
			path: 'follow-up-templates',
		},
		{
			name: __( 'Reports', 'woo-cart-abandonment-recovery' ),
			path: 'reports',
		},
		{
			name: __( 'Integrations', 'woo-cart-abandonment-recovery' ),
			path: 'integrations',
		},
		{
			name: __( 'Settings', 'woo-cart-abandonment-recovery' ),
			path: 'settings',
		},
	];

	return (
		<div className="z-30 relative">
			<Topbar className="py-0 h-16 min-h-[unset] border-0 border-b border-solid border-border-subtle">
				<Topbar.Left>
					<Topbar.Item>
						<img
							src={ WcarLogo }
							alt="WooCommerce Cart Abandonment Logo"
							aria-label="WooCommerce Cart Abandonment Logo"
							className="h-8 w-8"
						/>
					</Topbar.Item>
				</Topbar.Left>
				<Topbar.Middle align="left" className="h-full hidden md:block">
					<Topbar.Item className="h-full gap-2">
						{ menus.map( ( menu ) =>
							menu.path !== 'reports' ? (
								<Link
									key={ menu.path }
									to={
										cart_abandonment_admin.admin_url +
										`&path=${ menu.path }`
									}
									className={ `px-4 py-2 text-sm inline-flex items-center border-b-2 ${
										currentPath === menu.path
											? 'text-gray-900 border-flamingo-400'
											: 'text-gray-600 hover:text-gray-900 border-transparent'
									} font-medium box-border no-underline h-full border-solid border-0 transition-colors duration-200` }
								>
									{ menu.name }
								</Link>
							) : (
								<DropdownMenu
									key={ menu.path }
									className="h-full"
								>
									<DropdownMenu.Trigger
										className={ `px-4 py-2 text-sm inline-flex gap-1 items-center border-0 border-b-2 cursor-pointer ${
											[
												'follow-up',
												'product',
												'detailed-report',
											].includes( currentPath )
												? 'text-gray-900 border-flamingo-400'
												: 'text-gray-600 hover:text-gray-900 border-transparent'
										} font-medium box-border h-full border-solid transition-colors duration-200 focus-visible:outline focus-visible:outline-2` }
									>
										{ __(
											'Reports',
											'woo-cart-abandonment-recovery'
										) }
										<ChevronDownIcon
											className={ `h-4 w-4 ${
												[
													'follow-up',
													'product',
													'detailed-report',
												].includes( currentPath )
													? 'text-gray-900'
													: 'text-gray-600'
											}` }
										/>
									</DropdownMenu.Trigger>
									<DropdownMenu.ContentWrapper className="z-999999">
										<DropdownMenu.Content>
											<DropdownMenu.List>
												<DropdownMenu.Item className="p-0 [&>*:not(svg)]:m-0">
													<Link
														to={
															cart_abandonment_admin.admin_url +
															'&path=follow-up'
														}
														className={ `p-2 w-full text-sm inline-flex items-center text-gray-600 hover:text-gray-900 font-medium box-border no-underline transition-colors duration-200` }
													>
														{ __(
															'Follow Up',
															'woo-cart-abandonment-recovery'
														) }
													</Link>
												</DropdownMenu.Item>
												<DropdownMenu.Item className="p-0 [&>*:not(svg)]:m-0">
													<Link
														to={
															cart_abandonment_admin.admin_url +
															'&path=product'
														}
														className={ `p-2 text-sm w-full inline-flex items-center text-gray-600 hover:text-gray-900 font-medium box-border no-underline transition-colors duration-200` }
													>
														{ __(
															'Product',
															'woo-cart-abandonment-recovery'
														) }
													</Link>
												</DropdownMenu.Item>
											</DropdownMenu.List>
										</DropdownMenu.Content>
									</DropdownMenu.ContentWrapper>
								</DropdownMenu>
							)
						) }
					</Topbar.Item>
				</Topbar.Middle>
				<Topbar.Right>
					{ cart_abandonment_admin?.is_pro_active && (
						<Topbar.Item>
							<Badge
								label={
									isLicenseActivated
										? 'Valid License'
										: 'Activate License'
								}
								size="xs"
								variant={ isLicenseActivated ? 'green' : 'red' }
							/>
						</Topbar.Item>
					) }
					<Topbar.Item>
						<Badge
							label={
								cart_abandonment_admin?.is_pro_active &&
								isLicenseActivated
									? 'Pro'
									: 'Core'
							}
							size="xs"
							variant="neutral"
						/>
					</Topbar.Item>
					{ cart_abandonment_admin?.knowledge_base && (
						<Topbar.Item>
							<KnowledgeBase />
						</Topbar.Item>
					) }
					{ cart_abandonment_admin?.whats_new_rss_feed && (
						<Topbar.Item>
							<WhatsNew />
						</Topbar.Item>
					) }
					<Topbar.Item className="md:hidden">
						<HamburgerMenu>
							<HamburgerMenu.Toggle className="size-6" />
							<HamburgerMenu.Options>
								{ menus.map( ( menu ) =>
									menu.path !== 'reports' ? (
										<HamburgerMenu.Option
											key={ menu.path }
											tag="div"
										>
											<Link
												to={
													cart_abandonment_admin.admin_url +
													`&path=${ menu.path }`
												}
												className={ `${
													currentPath === menu.path
														? 'text-gray-900'
														: 'text-gray-600 hover:text-gray-900'
												} w-full font-medium box-border no-underline transition-colors duration-200` }
											>
												{ menu.name }
											</Link>
										</HamburgerMenu.Option>
									) : (
										<React.Fragment key={ menu.path }>
											<HamburgerMenu.Option tag="div">
												<Link
													to={
														cart_abandonment_admin.admin_url +
														'&path=follow-up'
													}
													className={ `${
														[
															'follow-up',
															'product',
															'detailed-report',
														].includes(
															currentPath
														)
															? 'text-gray-900'
															: 'text-gray-600 hover:text-gray-900'
													} w-full font-medium box-border no-underline transition-colors duration-200` }
												>
													{ __(
														'Follow Up Reports',
														'woo-cart-abandonment-recovery'
													) }
												</Link>
											</HamburgerMenu.Option>
											<HamburgerMenu.Option tag="div">
												<Link
													to={
														cart_abandonment_admin.admin_url +
														'&path=product'
													}
													className={ `${
														[
															'follow-up',
															'product',
															'detailed-report',
														].includes(
															currentPath
														)
															? 'text-gray-900'
															: 'text-gray-600 hover:text-gray-900'
													} w-full font-medium box-border no-underline transition-colors duration-200` }
												>
													{ __(
														'Product Reports',
														'woo-cart-abandonment-recovery'
													) }
												</Link>
											</HamburgerMenu.Option>
										</React.Fragment>
									)
								) }
							</HamburgerMenu.Options>
						</HamburgerMenu>
					</Topbar.Item>
				</Topbar.Right>
			</Topbar>
		</div>
	);
};

export default Header;
