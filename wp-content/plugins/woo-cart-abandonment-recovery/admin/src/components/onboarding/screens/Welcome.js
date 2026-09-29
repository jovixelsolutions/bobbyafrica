import { Button, Text } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import { Check } from 'lucide-react';
import { useOnboardingNavigation } from '../hooks';

const features = [
	__(
		'Recover lost revenue automatically and effortlessly',
		'woo-cart-abandonment-recovery'
	),
	__(
		'Quick and easy setup, no technical skills needed',
		'woo-cart-abandonment-recovery'
	),
	__(
		'Track, log, and resend emails with ease',
		'woo-cart-abandonment-recovery'
	),
	__( 'Fully customizable to your store', 'woo-cart-abandonment-recovery' ),
	__(
		'Dynamic coupon generation with expiry control',
		'woo-cart-abandonment-recovery'
	),
];

const Welcome = () => {
	const { navigateToNextRoute } = useOnboardingNavigation();

	return (
		<div className="space-y-6">
			<div className="space-y-1.5">
				<Text as="h2" size={ 30 } weight={ 600 }>
					{ __(
						'Welcome to Cart Abandonment Recovery',
						'woo-cart-abandonment-recovery'
					) }
				</Text>
				<Text size={ 16 } weight={ 500 } color="secondary">
					{ __(
						'Track abandoned carts in real time & recover lost revenue automatically.',
						'woo-cart-abandonment-recovery'
					) }
				</Text>
			</div>
			<iframe
				className="w-full aspect-video rounded-lg"
				src="https://www.youtube.com/embed/EXz2joiA6Uc?autoplay=1&mute=1"
				title="YouTube video player"
				frameBorder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				allowFullScreen
			></iframe>
			<ul>
				{ features.map( ( feature, index ) => (
					<li key={ index } className="flex items-center gap-1">
						<Check
							className="size-3 text-gray-600"
							strokeWidth={ 1.5 }
						/>
						<Text size={ 16 } weight={ 500 } color="secondary">
							{ feature }
						</Text>
					</li>
				) ) }
			</ul>

			<Button className="mb-4" onClick={ navigateToNextRoute }>
				{ __(
					'Get Started with Onboarding',
					'woo-cart-abandonment-recovery'
				) }
			</Button>
		</div>
	);
};

export default Welcome;

