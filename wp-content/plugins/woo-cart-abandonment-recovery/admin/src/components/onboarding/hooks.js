import { useLocation, useNavigate } from 'react-router-dom';
import ONBOARDING_ROUTES_CONFIG from './onboardingRoutesConfig';

/**
 * This hook will return functions that will handle the navigation of the onboarding process.
 * It will be used to handle the back and continue buttons.
 */
export const useOnboardingNavigation = () => {
	const navigate = useNavigate();
	const urlParams = new URLSearchParams( useLocation().search );
	const currentRoute = urlParams.get( 'step' ) || 'welcome';
	const BASE_PATH =
		'?page=woo-cart-abandonment-recovery&path=onboarding&step=';

	const getNextRoute = ( currentPath ) => {
		const currentIndex = ONBOARDING_ROUTES_CONFIG.findIndex(
			( route ) => route.url === currentPath
		);

		return ONBOARDING_ROUTES_CONFIG[ currentIndex + 1 ].url;
	};

	const getPreviousRoute = ( currentPath ) => {
		const currentIndex = ONBOARDING_ROUTES_CONFIG.findIndex(
			( route ) => route.url === currentPath
		);

		return ONBOARDING_ROUTES_CONFIG[ currentIndex - 1 ].url;
	};

	const navigateToNextRoute = () => {
		const nextRoute = getNextRoute( currentRoute );
		navigate( {
			search: BASE_PATH + nextRoute,
		} );
	};

	const navigateToPreviousRoute = () => {
		const previousRoute = getPreviousRoute( currentRoute );
		navigate( {
			search: BASE_PATH + previousRoute,
		} );
	};

	const getCurrentStepNumber = () => {
		const currentIndex = ONBOARDING_ROUTES_CONFIG.findIndex(
			( route ) => route.url === currentRoute
		);

		return currentIndex + 1;
	};

	const getCurrentStep = () => {
		return ONBOARDING_ROUTES_CONFIG.find(
			( route ) => route.url === currentRoute
		);
	};

	const navigateToStep = ( stepNumber ) => {
		// Convert step number to index (stepNumber is 1-based, array is 0-based)
		const targetIndex = stepNumber - 1;

		// Check if step number is valid
		if (
			targetIndex < 0 ||
			targetIndex >= ONBOARDING_ROUTES_CONFIG.length
		) {
			return;
		}

		const targetRoute = ONBOARDING_ROUTES_CONFIG[ targetIndex ];

		// Check if we can navigate to this step by checking requirements
		for ( let i = 0; i <= targetIndex; i++ ) {
			const route = ONBOARDING_ROUTES_CONFIG[ i ];

			// Skip routes without requirements
			if (
				! route?.requires?.stateKeys ||
				route?.requires?.stateKeys?.length === 0
			) {
				continue;
			}
		}

		// If all requirements are met, navigate to the target step
		navigate( {
			search: BASE_PATH + targetRoute.url,
		} );
	};

	return {
		getNextRoute,
		getPreviousRoute,
		navigateToNextRoute,
		navigateToPreviousRoute,
		getCurrentStepNumber,
		getCurrentStep,
		navigateToStep,
	};
};
