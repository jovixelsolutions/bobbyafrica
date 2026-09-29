import { Button } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import {
	CheckIcon,
	ArrowTopRightOnSquareIcon,
} from '@heroicons/react/24/outline';

import Heading from '@Components/onboarding/Heading';
import { useNavigate } from 'react-router-dom';

const OnboardingComplete = () => {
	const navigate = useNavigate();

	const redirectToDashboard = () => {
		navigate( '?page=woo-cart-abandonment-recovery' );
	};

	return (
		<div className="flex flex-col gap-2">
			<Heading
				title={ __(
					"You're Good to Go! 🚀",
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Great job! 🎉 With Cart Abandonment Recovery active, your store is ready to start recovering the lost revenue automatically.',
					'woo-cart-abandonment-recovery'
				) }
			/>
			<div className="flex flex-col gap-3 py-3">
				<p className="text-gray-900 font-semibold m-0">
					{ __(
						'Here’s What Cart Abandonment Recovery Will Do for You Now:',
						'woo-cart-abandonment-recovery'
					) }
				</p>

				<ul className="list-none pl-0 m-0 flex flex-col gap-3">
					<li className="flex items-center gap-1.5 m-0 text-gray-500 text-sm font-medium">
						<CheckIcon className="h-4 w-4 text-flamingo-500" />
						<strong>
							{ __(
								'Recover lost sales automatically',
								'woo-cart-abandonment-recovery'
							) }
						</strong>
						<span>
							{ __(
								'by reminding shoppers to complete their purchase.',
								'woo-cart-abandonment-recovery'
							) }
						</span>
					</li>
					<li className="flex items-center gap-1.5 m-0 text-gray-500 text-sm font-medium">
						<CheckIcon className="h-4 w-4 text-flamingo-500" />
						<strong>
							{ __(
								'Increase conversions',
								'woo-cart-abandonment-recovery'
							) }
						</strong>
						<span>
							{ __(
								'with timely follow-ups via Emails.',
								'woo-cart-abandonment-recovery'
							) }
						</span>
					</li>
					<li className="flex items-center gap-1.5 m-0 text-gray-500 text-sm font-medium">
						<CheckIcon className="h-4 w-4 text-flamingo-500" />
						<strong>
							{ __(
								'Turn abandoned carts into revenue',
								'woo-cart-abandonment-recovery'
							) }
						</strong>
						<span>
							{ __(
								'without any manual effort.',
								'woo-cart-abandonment-recovery'
							) }
						</span>
					</li>
				</ul>
			</div>

			<div className="flex flex-col gap-3">
				<Button onClick={ redirectToDashboard } variant="primary">
					{ __( 'Go To Dashboard', 'woo-cart-abandonment-recovery' ) }
				</Button>
				<Button
					onClick={ () =>
						window.open(
							'https://cartflows.com/docs-category/cart-abandonment/',
							'_blank'
						)
					}
					variant="ghost"
					iconPosition="right"
					icon={
						<ArrowTopRightOnSquareIcon className="h-5 w-5 text-gray-500" />
					}
				>
					{ __( 'Documentation', 'woo-cart-abandonment-recovery' ) }
				</Button>
			</div>
		</div>
	);
};

export default OnboardingComplete;
