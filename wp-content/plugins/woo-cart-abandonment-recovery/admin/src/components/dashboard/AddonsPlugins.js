import { Title, Container, Label, Button, toast } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import FeatureBadge from '@Components/common/FeatureBadge';
import { doApiFetch } from '@Store';

const AddonsPlugins = () => {
	const addonsPlugins = cart_abandonment_admin.extend_plugins;

	const getCta = function ( status ) {
		let statusText = '';

		switch ( status ) {
			case 'active':
				statusText = __( 'Installed', 'woo-cart-abandonment-recovery' );
				break;
			case 'inactive':
				statusText = __( 'Activate', 'woo-cart-abandonment-recovery' );
				break;
			default:
				statusText = __(
					'Install & Activate',
					'woo-cart-abandonment-recovery'
				);
		}

		return statusText;
	};

	const handleInstallActivate = function ( plugin ) {
		if ( 'not-installed' === plugin.status ) {
			const formData = new window.FormData();

			formData.append( 'action', 'cart_abandonment_install_plugin' );
			formData.append( 'slug', plugin.slug );
			formData.append(
				'_ajax_nonce',
				cart_abandonment_admin.plugin_installer_nonce
			);

			doApiFetch(
				cart_abandonment_admin.ajax_url,
				formData,
				'POST',
				( response ) => {
					if ( response.success ) {
						activate_plugin( plugin.path );
					} else {
						toast.error(
							__(
								'Failed to install plugin',
								'woo-cart-abandonment-recovery'
							),
							{
								description: response.data?.message || '',
							}
						);
					}
				},
				( error ) => {
					toast.error(
						__(
							'Failed to install plugin',
							'woo-cart-abandonment-recovery'
						),
						{
							description: error.data?.message || '',
						}
					);
				},
				true
			);
		} else if ( 'inactive' === plugin.status ) {
			activate_plugin( plugin.path );
		}
	};

	const activate_plugin = ( path ) => {
		const formData = new window.FormData();
		formData.append( 'action', 'cart_abandonment_activate_plugin' );
		formData.append( 'init', path );
		formData.append(
			'security',
			cart_abandonment_admin.plugin_activation_nonce
		);

		doApiFetch(
			cart_abandonment_admin.ajax_url,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					window.location.reload();
				} else {
					toast.error(
						__(
							'Failed to activate plugin',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error(
					__(
						'Failed to activate plugin',
						'woo-cart-abandonment-recovery'
					),
					{
						description: error.data?.message || '',
					}
				);
			},
			true
		);
	};

	return (
		<SectionWrapper className="lg:w-1/2 flex flex-col gap-2">
			<div className="flex justify-between items-center">
				<Title
					description=""
					icon={ null }
					iconPosition="right"
					size="xs"
					tag="h2"
					title={ __(
						'Extend Your Website',
						'woo-cart-abandonment-recovery'
					) }
					className="[&>*]:text-gray-900"
				/>
			</div>
			<Container>
				<Container.Item className="md:w-full lg:w-full bg-field-primary-background rounded-lg">
					<Container className="grid grid-cols-1 sm:grid-cols-2 p-1 gap-1">
						{ addonsPlugins.map( ( plugin, index ) => (
							<Container.Item
								className="md:w-full lg:w-full flex"
								key={ index }
							>
								<Container
									className="flex-1 gap-1 shadow-soft-shadow-inner p-4 rounded-md bg-background-primary"
									containerType="flex"
									direction="column"
								>
									<Container.Item className="flex justify-between">
										<Container
											align="start"
											className="gap-1.5"
										>
											<Container.Item className="[&>svg]:size-5">
												<img
													src={ plugin.logo }
													className="size-5"
													alt=""
												/>
											</Container.Item>
											<Container.Item>
												<Label className="text-sm font-semibold">
													{ plugin.title }
												</Label>
											</Container.Item>
										</Container>
										{
											plugin?.feature && (
												<FeatureBadge
													feature={ plugin?.feature }
													size="xs"
												/>
											)
										}
									</Container.Item>
									<Container.Item className="gap-0.5 p-1">
										<Label
											className="text-sm"
											variant="help"
										>
											{ plugin.desc }
										</Label>
									</Container.Item>
									<Container.Item>
										<Button
											className="shadow-sm"
											size="xs"
											variant="outline"
											disabled={
												'active' === plugin.status
											}
											onClick={ () =>
												handleInstallActivate( plugin )
											}
										>
											{ getCta( plugin.status ) }
										</Button>
									</Container.Item>
								</Container>
							</Container.Item>
						) ) }
					</Container>
				</Container.Item>
			</Container>
		</SectionWrapper>
	);
};

export default AddonsPlugins;
