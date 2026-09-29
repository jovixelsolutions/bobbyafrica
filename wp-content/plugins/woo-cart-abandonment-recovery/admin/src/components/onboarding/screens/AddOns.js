import { Container, Checkbox, Label } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import { useOnboardingContext } from '../OnboardingContext';
import Heading from '@Components/onboarding/Heading';
import NavigationButtons from '@Components/onboarding/NavigationButtons';

const PluginCard = ( { plugin } ) => (
	<div className="p-2 gap-1 items-center bg-background-primary rounded-md shadow-soft-shadow-inner">
		<Container
			align="start"
			className="gap-1 p-1"
			containerType="flex"
			justify="between"
		>
			<Container.Item className="flex items-start gap-[12px]">
				<img width={ 24 } src={ plugin?.logo } alt={ plugin?.title } />

				<div className="flex flex-col gap-[2px]">
					<Label className="text-sm font-semibold">
						{ plugin?.title }
					</Label>
					<Label className="text-sm" variant="help">
						{ plugin?.desc }
					</Label>
				</div>
			</Container.Item>
			<Container.Item className="flex">
				<Checkbox
					size="sm"
					checked={ plugin?.checked }
					onChange={ plugin?.onChange }
				/>
			</Container.Item>
		</Container>
	</div>
);

const AddOns = () => {
	const addonsPlugins = cart_abandonment_admin.extend_plugins;
	const { state, dispatch } = useOnboardingContext();
	const plugins = state.plugins;

	return (
		<div className="flex flex-col gap-2">
			<Heading
				title={ __(
					'Add More Power to Your Website',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'These tools can help you build your website faster and easier. Try them out and see how they can help your website grow.',
					'woo-cart-abandonment-recovery'
				) }
			/>
			<Container className="mb-4">
				<Container.Item className="p-1 md:w-full lg:w-full bg-field-primary-background rounded-lg">
					<Container className="grid grid-cols-1 gap-1">
						{ addonsPlugins.map( ( plugin, index ) => (
							<PluginCard
								key={ index }
								plugin={ {
									...plugin,
									checked: plugins[ plugin.slug ],
									onChange: ( val ) =>
										dispatch( {
											type: 'UPDATE_PLUGIN_DATA',
											payload: {
												option: plugin.slug,
												value: val,
											},
										} ),
								} }
							/>
						) ) }
					</Container>
				</Container.Item>
			</Container>
			<NavigationButtons />
		</div>
	);
};

export default AddOns;
