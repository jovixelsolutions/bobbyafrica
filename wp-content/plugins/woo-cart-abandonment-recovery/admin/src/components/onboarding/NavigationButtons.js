import { useState } from 'react';
import { Button, Loader } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';

import { useOnboardingNavigation } from './hooks';
import { useOnboardingContext } from './OnboardingContext';

const NavigationButtons = () => {
	const [ isSaving, setIsSaving ] = useState( false );
	const {
		getCurrentStepNumber,
		navigateToNextRoute,
		navigateToPreviousRoute,
	} = useOnboardingNavigation();
	const { state } = useOnboardingContext();

	const handleFinishSetup = () => {
		setIsSaving( true );
		apiFetch( {
			url: cart_abandonment_admin.onboarding.ajaxUrl,
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify( state ),
		} )
			.then( () => {
				setIsSaving( false );
				navigateToNextRoute();
			} )
			.catch( () => {
				setIsSaving( false );
			} );
	};

	const currentStepNumber = getCurrentStepNumber();
	return (
		<div className="flex justify-between">
			<Button
				icon={ <ChevronLeftIcon className="h-6 w-6" /> }
				onClick={ navigateToPreviousRoute }
				disabled={ 1 === getCurrentStepNumber() }
				variant="outline"
			>
				{ __( 'Back', 'woo-cart-abandonment-recovery' ) }
			</Button>

			{ currentStepNumber < 5 ? (
				<Button
					icon={ <ChevronRightIcon className="h-6 w-6" /> }
					iconPosition="right"
					onClick={ navigateToNextRoute }
					variant="primary"
				>
					{ __( 'Next', 'woo-cart-abandonment-recovery' ) }
				</Button>
			) : (
				<Button
					iconPosition="right"
					onClick={ handleFinishSetup }
					variant="primary"
					disabled={ isSaving }
					icon={
						isSaving ? (
							<Loader
								className="text-flamingo-400 p-0"
								icon={ null }
								size="sm"
								variant="primary"
							/>
						) : (
							<ChevronRightIcon className="h-6 w-6" />
						)
					}
				>
					{ __( 'Finish Setup', 'woo-cart-abandonment-recovery' ) }
				</Button>
			) }
		</div>
	);
};

export default NavigationButtons;
