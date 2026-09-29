import { useEffect, useCallback, useRef, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_KEY } from '../store';
import Modal from './modal';
import Button from './button';
import LoadingSpinner from './loading-spinner';
import { isValidAuthMessage, resolveAuthOrigin } from '../utils/auth-message';
const { imageDir } = aiBuilderVars;
import { CheckIcon } from '@heroicons/react/24/outline';

const SignupLoginModal = () => {
	const { setSignupLoginModal } = useDispatch( STORE_KEY );
	const { signupLoginModal } = useSelect( ( select ) => {
		const { getSignupLoginModalInfo } = select( STORE_KEY );
		return {
			signupLoginModal: getSignupLoginModalInfo(),
		};
	}, [] );
	const { zipwp_auth } = wpApiSettings || {};
	const { screen_url, redirect_url, source, utmSource, partner_id } =
		zipwp_auth || {};
	const { isPremiumTemplate, onAuthSuccess } = signupLoginModal || {};

	const [ isAuthLoading, setIsAuthLoading ] = useState( false );
	const authChildWindow = useRef( null );
	const onAuthSuccessRef = useRef( onAuthSuccess );

	// Resolve the expected ZipWP auth origin once. Used to reject cross-origin
	// postMessage events that would otherwise be able to spoof auth success.
	const expectedAuthOrigin = resolveAuthOrigin( screen_url );

	// Keep callback ref in sync so the message handler always has the latest.
	useEffect( () => {
		onAuthSuccessRef.current = onAuthSuccess;
	}, [ onAuthSuccess ] );

	const encodedRedirectUrl = encodeURIComponent(
		redirect_url +
			'&should_resume=1&security=' +
			aiBuilderVars.zipwp_auth_nonce
	);

	const saveAuthToken = useCallback(
		async ( { token, creditToken, email } ) => {
			const formData = new FormData();
			formData.append( 'action', 'astra-sites-save_auth_token' );
			formData.append( '_ajax_nonce', aiBuilderVars._ajax_nonce );
			formData.append( 'token', token );
			formData.append( 'credit_token', creditToken );
			formData.append( 'email', email );

			const response = await fetch( aiBuilderVars.ajax_url, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
			} );

			return response.json();
		},
		[]
	);

	// Listen for ZIPWP_AUTH_SUCCESS postMessage from the popup window.
	useEffect( () => {
		const handleMessage = async ( event ) => {
			// Reject messages from any origin other than the ZipWP auth origin
			// and from any source other than the popup this component opened.
			// Prevents CSRF via forged postMessage from attacker-controlled pages.
			if (
				! isValidAuthMessage(
					event,
					expectedAuthOrigin,
					authChildWindow.current
				)
			) {
				return;
			}

			const { token, credit_token: creditToken, email } = event.data;

			// Save tokens to the WordPress database.
			const result = await saveAuthToken( {
				token,
				creditToken,
				email,
			} );

			if ( result?.success ) {
				aiBuilderVars.zip_token_exists = true;

				if ( result.data?.zip_plans ) {
					aiBuilderVars.zip_plans = result.data.zip_plans;
				}

				setIsAuthLoading( false );
				setSignupLoginModal( { open: false } );

				if ( typeof onAuthSuccessRef.current === 'function' ) {
					onAuthSuccessRef.current();
				}
			}
		};

		window.addEventListener( 'message', handleMessage );
		return () => window.removeEventListener( 'message', handleMessage );
	}, [ saveAuthToken, setSignupLoginModal, expectedAuthOrigin ] );

	const handleClickNext = ( ask = 'register' ) => {
		const currentUrl = window.location.href;
		const currentUrlObj = new URL( currentUrl );
		currentUrlObj.hash = '';

		// add should_resume=1 and skip_redirect_last_step=1 to the URL
		currentUrlObj.searchParams.set( 'should_resume', '1' );
		currentUrlObj.searchParams.set( 'skip_redirect_last_step', '1' );

		// change hash to /design
		currentUrlObj.hash = '/design';

		const newUrl = currentUrlObj.toString();

		let url = `${ screen_url }?type=token&redirect_url=${ encodedRedirectUrl }&ask=/${ ask }&source=${ source }${
			partner_id ? `&aff=${ partner_id }` : ''
		}&utm_source=${ utmSource }&utm_medium=plugin&utm_campaign=build-with-ai&utm_content=start-building`;

		// if it's a premium template, add premium_design=true to the URL
		// so zipwp can redirect back to designs page if user wants to change design
		if ( isPremiumTemplate ) {
			url += `&premium_design=true&change_design_redirect=${ encodeURIComponent(
				newUrl
			) }`;
		}

		// Append mode=popup so ZipWP sends postMessage instead of redirecting.
		url += '&mode=popup';

		// Open auth in a child browser window, centered on screen.
		const width = 1280;
		const height = 828;
		const left = window.screenX + ( window.outerWidth - width ) / 2;
		const top = window.screenY + ( window.outerHeight - height ) / 2;
		const features = `width=${ width },height=${ height },left=${ left },top=${ top },scrollbars=yes,resizable=yes`;

		const childWindow = window.open( url, 'zipwp-auth', features );

		if ( childWindow ) {
			authChildWindow.current = childWindow;
			setIsAuthLoading( true );
		} else {
			// Popup blocked — fall back to redirect.
			window.location.href = url.replace( '&mode=popup', '' );
			setSignupLoginModal( { open: false } );
		}
	};

	const handleCloseModal = () => {
		if ( authChildWindow.current && ! authChildWindow.current.closed ) {
			authChildWindow.current.close();
		}
		setIsAuthLoading( false );
		setSignupLoginModal( { open: false } );
	};

	return (
		<Modal
			open={ signupLoginModal?.open }
			setOpen={ ( toggle, type ) => {
				if ( type === 'close-icon' ) {
					handleCloseModal();
				}
			} }
			width={ 480 }
			height="408"
			overflowHidden={ false }
			className={ 'px-8 pt-8 pb-5 font-sans' }
		>
			<div>
				<div className="flex items-center gap-3">
					{ /* <ClipboardIcon className="w-8 h-8 text-accent-st" /> */ }
					<img
						width={ 237 }
						src={ `${ imageDir }/st-zipwp-logo.png` }
						alt=""
					/>
				</div>

				<div className="mt-6">
					<div className="text-zip-body-text text-base font-normal leading-6 flex flex-col space-y-4">
						<h2 className="font-bold leading-6">
							{ __(
								'Great Job! Your Site is Ready! 🎉',
								'ai-builder'
							) }
						</h2>

						<p className="text-base text-light-theme-text-inactive font-normal leading-5">
							{ __(
								'Sign up for a free ZipWP account to import and customize your website!',
								'ai-builder'
							) }
						</p>
					</div>
					<div className="mt-5">
						<ul className="list-none space-y-2">
							<li className="flex items-center text-base leading-5 font-normal">
								<CheckIcon
									strokeWidth={ 2 }
									className="w-5 h-5 text-light-theme-highlight-cta mr-2"
								/>
								<span className="text-black">
									{ __(
										'Customize your website with ease',
										'ai-builder'
									) }
								</span>
							</li>
							<li className="flex items-center">
								<CheckIcon
									strokeWidth={ 2 }
									className="w-5 h-5 text-light-theme-highlight-cta mr-2"
								/>
								<span className="text-black">
									{ __(
										'Launch faster than ever',
										'ai-builder'
									) }
								</span>
							</li>
							<li className="flex items-center">
								<CheckIcon
									strokeWidth={ 2 }
									className="w-5 h-5 text-light-theme-highlight-cta mr-2"
								/>
								<span className="text-black">
									{ __(
										"Need help? We're a message away",
										'ai-builder'
									) }
								</span>
							</li>
						</ul>
					</div>
					<div className="flex items-center gap-3 justify-center mt-9 flex-col">
						<Button
							type="submit"
							variant="primary"
							size="medium"
							className="min-w-full h-[40px] text-sm font-semibold leading-5 px-5 w-full xs:w-auto"
							disabled={ isAuthLoading }
							onClick={ () => {
								handleClickNext( 'register' );
							} }
						>
							{ isAuthLoading ? (
								<LoadingSpinner />
							) : (
								__( 'Create ZipWP Account', 'ai-builder' )
							) }
						</Button>
						<span className="text-sm">
							{ __( 'Already have an account?', 'ai-builder' ) }{ ' ' }
							<span
								className={ `text-accent-st ${
									isAuthLoading
										? 'pointer-events-none opacity-50'
										: 'cursor-pointer hover:underline'
								}` }
								onClick={ () => {
									if ( ! isAuthLoading ) {
										handleClickNext( 'login' );
									}
								} }
							>
								{ ' ' }
								{ __( 'Click here to login.', 'ai-builder' ) }
							</span>
						</span>
					</div>
				</div>
			</div>
		</Modal>
	);
};

export default SignupLoginModal;
