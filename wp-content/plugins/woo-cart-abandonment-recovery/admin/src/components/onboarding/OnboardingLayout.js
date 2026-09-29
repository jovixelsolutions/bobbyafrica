import { useNavigate } from 'react-router-dom';
import { Topbar, ProgressSteps, Button } from '@bsf/force-ui';
import { XMarkIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import WcarLogo from '@Images/wcar-icon.svg';
import { useOnboardingNavigation } from './hooks';
import { OnboardingProvider } from './OnboardingContext';
import Welcome from './screens/Welcome';

const NavBar = () => {
	const { getCurrentStepNumber, navigateToStep } = useOnboardingNavigation();
	const navigate = useNavigate();

	const exitGuidedSetup = () => {
		apiFetch( {
			url: cart_abandonment_admin.onboarding.ajaxUrl,
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
		} );
		navigate( {
			search: '?page=woo-cart-abandonment-recovery',
		} );
	};

	return (
		<Topbar className="p-5 bg-primary-background">
			<Topbar.Left>
				<Topbar.Item>
					<img
						src={ WcarLogo }
						alt="Cart Abandonment Recovery Logo"
						aria-label="Cart Abandonment Recovery Logo"
						className="h-8 w-8"
					/>
				</Topbar.Item>
			</Topbar.Left>
			<Topbar.Middle align="center">
				<Topbar.Item className="md:block hidden">
					<ProgressSteps
						completedVariant="number"
						currentStep={ getCurrentStepNumber() }
						size="md"
						type="inline"
						variant="number"
					>
						{ Array.from( { length: 5 }, ( _, index ) => (
							<ProgressSteps.Step
								key={ index }
								size="md"
								onClick={ () => navigateToStep( index + 1 ) }
								className="cursor-pointer hover:bg-background-secondary transition-colors duration-200"
							/>
						) ) }
					</ProgressSteps>
				</Topbar.Item>
			</Topbar.Middle>
			<Topbar.Right>
				<Topbar.Item>
					<Button
						icon={ <XMarkIcon className="h-4 w-4" /> }
						size="xs"
						variant="ghost"
						iconPosition="right"
						onClick={ exitGuidedSetup }
					>
						{ __(
							'Exit Guided Setup',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
				</Topbar.Item>
			</Topbar.Right>
		</Topbar>
	);
};

const OnboardingLayoutContent = () => {
	const { getCurrentStep } = useOnboardingNavigation();
	const currentStep = getCurrentStep();
	return (
		<div className="h-full space-y-7 pb-10">
			{ /* Header */ }
			<NavBar />
			{ /* Content */ }
			<div className="p-7">
				<div className="w-full h-full border-0.5 border-solid border-gray-200 bg-white shadow-sm rounded-xl mx-auto p-7 box-border max-w-xl">
					{ currentStep?.component || <Welcome /> }
				</div>
			</div>
		</div>
	);
};

const OnboardingLayout = () => {
	return (
		<OnboardingProvider>
			<OnboardingLayoutContent />
		</OnboardingProvider>
	);
};

export default OnboardingLayout;

