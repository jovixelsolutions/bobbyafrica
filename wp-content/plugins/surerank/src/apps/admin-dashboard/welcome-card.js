import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { X, ArrowRight } from 'lucide-react';
import { Text, Button } from '@bsf/force-ui';
import { cn } from '@/functions/utils';

const WelcomeCard = ( { className = '', isProActive = false } ) => {
	const [ popupVideo, setPopupVideo ] = useState( null );

	// Get video data from localized script
	const welcomeVideo = window?.surerank_admin_common?.welcome_video || {};
	const thumbnailUrl =
		welcomeVideo.thumbnail ||
		'https://img.youtube.com/vi/GEeTu2D74Z8/maxresdefault.jpg';
	const videoUrl =
		welcomeVideo.url ||
		'https://www.youtube.com/embed/GEeTu2D74Z8?autoplay=1&rel=0';

	useEffect( () => {
		const handleKeyDown = ( e ) => {
			if ( e.key === 'Escape' ) {
				setPopupVideo( null );
			}
		};
		window.addEventListener( 'keydown', handleKeyDown );
		return () => window.removeEventListener( 'keydown', handleKeyDown );
	}, [] );

	return (
		<>
			<div
				className={ cn(
					'w-full h-fit bg-background-primary border-0.5 border-solid border-border-subtle rounded-xl p-5 shadow-sm flex overflow-hidden',
					isProActive ? 'flex-col gap-3' : 'flex-row gap-4',
					! isProActive && className
				) }
			>
				{ /* Content Section - Show first when Pro is not active */ }
				{ ! isProActive && (
					<div className="flex flex-col gap-6 px-1 flex-1">
						<div className="flex flex-col gap-1">
							<Text
								size={ 24 }
								lineHeight={ 32 }
								weight={ 600 }
								color="primary"
							>
								{ __( 'Welcome to SureRank!', 'surerank' ) }
							</Text>
							<Text
								size={ 14 }
								lineHeight={ 20 }
								weight={ 400 }
								color="secondary"
							>
								{ __(
									"Boost your website's visibility with AI-powered SEO tools built for WordPress. Optimize content, improve rankings, and grow organic traffic without the complexity.",
									'surerank'
								) }
							</Text>
						</div>
						<div className="w-fit">
							<Button
								variant="outline"
								size="md"
								icon={ <ArrowRight className="size-4" /> }
								iconPosition="right"
								onClick={ () => {
									const utmParams = new URLSearchParams( {
										utm_source: 'plugin',
										utm_medium: 'dashboard',
										utm_campaign: 'welcome_card',
									} );
									window.open(
										`https://surerank.com/docs/?${ utmParams.toString() }`,
										'_blank',
										'noopener,noreferrer'
									);
								} }
							>
								{ __( 'Learn More', 'surerank' ) }
							</Button>
						</div>
					</div>
				) }

				{ /* Thumbnail Section */ }
				<div
					className={ cn(
						'relative bg-gray-100 rounded-md overflow-hidden cursor-pointer group',
						isProActive
							? 'w-full aspect-video'
							: 'w-[45%] max-w-[390px] flex-shrink-0 aspect-video'
					) }
					onClick={ () => setPopupVideo( videoUrl ) }
					role="button"
					tabIndex={ 0 }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' || e.key === ' ' ) {
							setPopupVideo( videoUrl );
						}
					} }
				>
					<img
						src={ thumbnailUrl }
						alt={ __( 'Welcome to SureRank', 'surerank' ) }
						className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
					/>
					<div className="absolute inset-0 flex items-center justify-center bg-black/5 group-hover:bg-black/10 transition-colors">
						{ /* Youtube Style Play Button */ }
						<div className="w-[68px] h-[48px] bg-[#FF0000] rounded-[12px] flex items-center justify-center shadow-lg transition-transform group-hover:scale-110 opacity-90 group-hover:opacity-100">
							<svg
								height="24"
								viewBox="0 0 24 24"
								width="24"
								focusable="false"
								className="fill-white w-6 h-6 ml-1"
							>
								<path d="M8 5v14l11-7z"></path>
							</svg>
						</div>
					</div>
				</div>

				{ /* Content Section - Show after thumbnail when Pro is active */ }
				{ isProActive && (
					<div className="flex flex-col gap-6 px-1">
						<div className="flex flex-col gap-1">
							<Text
								size={ 16 }
								lineHeight={ 24 }
								weight={ 600 }
								color="primary"
							>
								{ __( 'Welcome to SureRank!', 'surerank' ) }
							</Text>
							<Text
								size={ 14 }
								lineHeight={ 20 }
								weight={ 400 }
								color="secondary"
							>
								{ __(
									"Boost your website's visibility with AI-powered SEO tools built for WordPress. Optimize content, improve rankings, and grow organic traffic without the complexity.",
									'surerank'
								) }
							</Text>
						</div>
						<div className="w-fit">
							<Button
								variant="outline"
								size="md"
								icon={ <ArrowRight className="size-4" /> }
								iconPosition="right"
								onClick={ () => {
									const utmParams = new URLSearchParams( {
										utm_source: 'plugin',
										utm_medium: 'dashboard',
										utm_campaign: 'welcome_card',
									} );
									window.open(
										`https://surerank.com/docs/?${ utmParams.toString() }`,
										'_blank',
										'noopener,noreferrer'
									);
								} }
							>
								{ __( 'Learn More', 'surerank' ) }
							</Button>
						</div>
					</div>
				) }
			</div>

			{ /* Popup Video */ }
			{ popupVideo && (
				<div
					className="fixed inset-0 flex items-center justify-center bg-black/70 cursor-pointer z-[9999]"
					onClick={ () => setPopupVideo( null ) }
				>
					{ /* Close Button */ }
					<div className="absolute top-10 right-8 text-white cursor-pointer hover:bg-white/10 p-2 rounded-full transition-colors">
						<X
							size={ 24 }
							onClick={ ( e ) => {
								e.stopPropagation();
								setPopupVideo( null );
							} }
						/>
					</div>

					<div
						className="relative rounded-lg shadow-2xl cursor-default w-full max-w-4xl aspect-video mx-4"
						onClick={ ( e ) => e.stopPropagation() }
					>
						<iframe
							className="w-full h-full rounded-lg"
							src={ popupVideo }
							title={ __( 'Welcome to SureRank', 'surerank' ) }
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
							allowFullScreen
						></iframe>
					</div>
				</div>
			) }
		</>
	);
};

export default WelcomeCard;
