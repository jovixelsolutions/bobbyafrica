import { __ } from '@wordpress/i18n';
import { useState, useEffect, useSyncExternalStore } from '@wordpress/element';
import { toast } from '@bsf/force-ui';
import { getAuth } from '@Functions/api';
import useAuthPolling from '@Global/hooks/use-auth-polling';

const getInitialAuthState = () => {
	const { ai_authenticated } = window.surerank_globals || {};

	return ai_authenticated;
};

// Auth store with subscription support for cross-component sync
const authListeners = new Set();
const authStore = {
	isAuthenticated: getInitialAuthState(),
};

const subscribeToAuth = ( listener ) => {
	authListeners.add( listener );
	return () => authListeners.delete( listener );
};

const getAuthSnapshot = () => authStore.isAuthenticated;

const setAuthState = ( value ) => {
	authStore.isAuthenticated = value;
	authListeners.forEach( ( listener ) => listener() );
};

// eslint-disable-next-line
/**
 * Custom hook for handling authentication in onboarding
 *
 * @param  {Object}   options          -   Configuration options
 * @param  {boolean}  options.skipCheck - Skip checking auth on mount (default: false)
 * @return {Object}                    -  Authentication state and handlers
 */
const useOnboardingAuth = ( { skipCheck = false } = {} ) => {
	// Use useSyncExternalStore for cross-component auth state sync
	const isAuthenticated = useSyncExternalStore(
		subscribeToAuth,
		getAuthSnapshot
	);
	const [ isConnecting, setIsConnecting ] = useState( false );

	// Auth success handler
	const handleAuthSuccess = () => {
		setIsConnecting( false );
		setAuthState( true );
	};

	// Auth failure handler
	const handleAuthFailure = () => {
		setIsConnecting( false );
	};

	// Initialize auth polling hook
	const { openAuthPopup } = useAuthPolling(
		handleAuthSuccess,
		handleAuthFailure
	);

	// Connect handler
	const handleConnect = async () => {
		setIsConnecting( true );

		try {
			const response = await getAuth();

			if ( ! response?.success ) {
				toast.error(
					__( 'Failed to get authentication URL', 'surerank' )
				);
				setIsConnecting( false );
				return;
			}

			// Already authenticated (no auth_url returned means auth is complete)
			if ( ! response?.auth_url ) {
				setIsConnecting( false );
				setAuthState( true );
				return;
			}

			// Open auth popup and start polling
			openAuthPopup( response.auth_url );
		} catch ( error ) {
			toast.error(
				__( 'An error occurred while connecting', 'surerank' ),
				{
					description: error?.message || '',
				}
			);
			setIsConnecting( false );
		}
	};

	// On mount, check real auth status from server in case the token was saved
	// after the page loaded (e.g. OAuth redirect back to onboarding with ?access_key).
	useEffect( () => {
		if ( skipCheck || isAuthenticated ) {
			return;
		}

		getAuth()
			.then( ( response ) => {
				if ( response?.success && ! response?.auth_url ) {
					setAuthState( true );
				}
			} )
			.catch( () => {} );
	}, [] );

	return {
		isAuthenticated,
		isConnecting,
		handleConnect,
	};
};

export default useOnboardingAuth;
