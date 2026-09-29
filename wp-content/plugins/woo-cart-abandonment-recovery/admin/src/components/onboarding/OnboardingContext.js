import { createContext, useContext, useReducer } from '@wordpress/element';

const OnboardingContext = createContext();

const initialState = {
	userDetails: cart_abandonment_admin.onboarding.defaults[ 'user-details' ],
	plugins: cart_abandonment_admin.onboarding.defaults.plugins,
};

const onboardingReducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'UPDATE_USER_DATA':
			return {
				...state,
				userDetails: {
					...state.userDetails,
					[ action.payload.option ]: action.payload.value,
				},
			};

		case 'UPDATE_PLUGIN_DATA':
			return {
				...state,
				plugins: {
					...state.plugins,
					[ action.payload.option ]: action.payload.value,
				},
			};

		default:
			return state;
	}
};

export const OnboardingProvider = ( { children } ) => {
	const [ state, dispatch ] = useReducer( onboardingReducer, initialState );
	const value = { state, dispatch };

	return (
		<OnboardingContext.Provider value={ value }>
			{ children }
		</OnboardingContext.Provider>
	);
};

export const useOnboardingContext = () => {
	const context = useContext( OnboardingContext );
	if ( ! context ) {
		throw new Error(
			'useOnboardingContext must be used within OnboardingProvider'
		);
	}
	return context;
};
