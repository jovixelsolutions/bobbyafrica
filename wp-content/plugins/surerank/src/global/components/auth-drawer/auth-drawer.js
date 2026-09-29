import { __ } from '@wordpress/i18n';
import { Drawer, Container, toast } from '@bsf/force-ui';
import { useState, useEffect } from '@wordpress/element';
import { AIAuthScreen } from '@GlobalComponents/fix-it-for-me';
import { getAuth } from '@Functions/api';
import useAuthPolling from '@/global/hooks/use-auth-polling';
import { LEARN_MORE_AI_AUTH } from '@Global/constants';

/**
 * Authentication Drawer Component
 * Reusable drawer that handles AI authentication flow
 * Shows AIAuthScreen and manages authentication state
 *
 * @param {Object}   props          - Component props
 * @param {boolean}  props.open     - Drawer open state
 * @param {Function} props.onClose  - Callback when drawer closes
 * @param {Function} props.children - Function that receives authenticated state and returns content to render when authenticated
 * @return {JSX.Element} AuthDrawer component
 */
const AuthDrawer = ( { open, onClose, children } ) => {
	const [ authenticated, setAuthenticated ] = useState( false );

	const { openAuthPopup } = useAuthPolling( () => {
		setAuthenticated( true );
	} );

	// Check authentication status on mount
	useEffect( () => {
		const isAuth = window?.surerank_globals?.ai_authenticated || false;
		setAuthenticated( isAuth );
	}, [] );

	const handleGetStarted = async () => {
		try {
			const response = await getAuth();
			if ( ! response?.success ) {
				throw new Error(
					response?.message ||
						__( 'Authentication failed', 'surerank' )
				);
			}
			if ( response?.auth_url ) {
				openAuthPopup( response.auth_url );
				return;
			}
			setAuthenticated( true );
		} catch ( err ) {
			toast.error(
				err?.message ||
					__( 'An error occurred during authentication', 'surerank' )
			);
		}
	};

	// If authenticated, render children
	if ( authenticated ) {
		return typeof children === 'function'
			? children( authenticated )
			: children;
	}

	// If not authenticated, show auth screen in a drawer
	return (
		<Drawer
			exitOnEsc
			position="right"
			scrollLock
			setOpen={ onClose }
			open={ open }
			className="z-999999"
			exitOnClickOutside
		>
			<Drawer.Panel>
				<Drawer.Header>
					<Container justify="between" className="gap-2">
						<Drawer.Title>
							{ __( 'Connect SureRank AI', 'surerank' ) }
						</Drawer.Title>
						<Drawer.CloseButton />
					</Container>
				</Drawer.Header>
				<Drawer.Body className="overflow-x-hidden space-y-3">
					<AIAuthScreen
						onClickLearnMore={ () =>
							window.open(
								LEARN_MORE_AI_AUTH,
								'_blank',
								'noopener'
							)
						}
						onClickGetStarted={ handleGetStarted }
					/>
				</Drawer.Body>
			</Drawer.Panel>
			<Drawer.Backdrop />
		</Drawer>
	);
};

export default AuthDrawer;
