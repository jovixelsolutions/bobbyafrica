import { __ } from '@wordpress/i18n';
import { Drawer, Container, toast } from '@bsf/force-ui';
import { useState, useEffect } from '@wordpress/element';
import { AIAuthScreen } from '@GlobalComponents/fix-it-for-me';
import GenerateContent from './generate-content';
import { getAuth } from '@Functions/api';
import useAuthPolling from '@/global/hooks/use-auth-polling';
import { LEARN_MORE_AI_AUTH } from '@Global/constants';

const AIModal = ( { fieldType, onClose, onUseThis } ) => {
	const [ authenticated, setAuthenticated ] = useState( false );
	const [ isFixing, setIsFixing ] = useState( false );

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

	const handleUseContent = async ( content ) => {
		if ( isFixing ) {
			return;
		}
		setIsFixing( true );
		try {
			await onUseThis( content );
			onClose();
		} catch ( err ) {
			// Keep modal open on error
			setIsFixing( false );
		}
	};

	return (
		<Drawer
			exitOnEsc
			position="right"
			scrollLock
			setOpen={ onClose }
			open={ true }
			className="z-999999"
			exitOnClickOutside
		>
			<Drawer.Panel>
				<Drawer.Header>
					<Container justify="between" className="gap-2">
						<Drawer.Title>
							{ authenticated
								? __( 'Generate with AI', 'surerank' )
								: __( 'Connect SureRank AI', 'surerank' ) }
						</Drawer.Title>
						<Drawer.CloseButton />
					</Container>
				</Drawer.Header>
				<Drawer.Body className="overflow-x-hidden space-y-3">
					{ ! authenticated ? (
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
					) : (
						<GenerateContent
							fieldType={ fieldType }
							onUseThis={ handleUseContent }
							fixing={ isFixing }
						/>
					) }
				</Drawer.Body>
			</Drawer.Panel>
			<Drawer.Backdrop />
		</Drawer>
	);
};

export default AIModal;
