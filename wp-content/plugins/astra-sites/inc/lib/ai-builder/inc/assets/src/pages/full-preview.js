import { useState, useLayoutEffect, useRef } from '@wordpress/element';
import {
	ChevronLeftIcon,
	ChevronRightIcon,
	ArrowPathIcon,
	LockClosedIcon,
	MagnifyingGlassIcon,
	StarIcon,
	EllipsisVerticalIcon,
} from '@heroicons/react/24/outline';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import SiteSkeleton from '../components/site-skeleton';
import { STORE_KEY } from '../store';
import {
	addHttps,
	sendPostMessage as dispatchPostMessage,
} from '../utils/helpers';
import { classNames } from '../helpers';
import { getDataUri } from '../utils/functions';
import { useSearch } from '@tanstack/react-router';

const FullPagePreview = () => {
	const [ loadingIframe, setLoadingIframe ] = useState( true );
	const [ collapsed, setCollapsed ] = useState( window.innerWidth < 1024 );

	const previewContainer = useRef( null );

	const {
		stepData: {
			templateList,
			businessName,
			selectedImages = [],
			businessContact,
		},
		aiSiteLogo,
		aiActiveTypography,
		aiActivePallette,
	} = useSelect( ( select ) => {
		const {
			getWebsiteInfo,
			getAIStepData,
			getSiteLogo,
			getActiveTypography,
			getActiveColorPalette,
		} = select( STORE_KEY );

		return {
			websiteInfo: getWebsiteInfo(),
			stepData: getAIStepData(),
			aiSiteLogo: getSiteLogo(),
			aiActiveTypography: getActiveTypography(),
			aiActivePallette: getActiveColorPalette(),
		};
	}, [] );
	const uuid = useSearch( {
		select: ( value ) => value.uuid,
	} );

	const selectedTemplateItem = templateList?.find(
		( item ) => item?.uuid === uuid
	);

	const sendPostMessage = ( data ) => {
		dispatchPostMessage( data, 'astra-starter-templates-preview' );
	};

	const updateScaling = () => {
		const container = previewContainer.current;
		if ( ! container ) {
			return;
		}

		const iframe = container.children[ 1 ];
		const containerWidth = container.clientWidth;
		const containerHeight = container.clientHeight - 44;
		const iframeWidth = iframe.clientWidth;
		const scaleX = containerWidth / iframeWidth;
		const scaleValue = scaleX;

		// Set the scale for both width and height
		iframe.style.transform = `scale(${ scaleValue })`;
		iframe.style.transformOrigin = 'top left';
		iframe.style.height = `${ containerHeight / scaleValue }px`;
	};

	const handleIframeLoading = () => {
		if ( ! selectedImages?.length ) {
			selectedImages.push( aiBuilderVars?.placeholder_images[ 0 ] );
			selectedImages.push( aiBuilderVars?.placeholder_images[ 1 ] );
		}

		if ( aiSiteLogo?.url ) {
			const mediaData = { ...aiSiteLogo };
			if ( window.location.protocol === 'http:' ) {
				getDataUri( mediaData.url, function ( data ) {
					mediaData.dataUri = data;
				} );
			}
			setTimeout( () => {
				sendPostMessage( {
					param: 'siteLogo',
					data: mediaData,
				} );
			}, 100 );
		}

		if ( ! aiActivePallette?.slug?.includes( 'default' ) ) {
			sendPostMessage( {
				param: 'colorPalette',
				data: aiActivePallette,
			} );
		}

		if ( ! aiActiveTypography?.default ) {
			sendPostMessage( {
				param: 'siteTypography',
				data: aiActiveTypography,
			} );
		}

		if ( Object.values( businessContact ).some( Boolean ) ) {
			const updatedData = [
				{
					type: 'phone',
					value: businessContact.phone,
					fallback: '202-555-0188',
				},
				{
					type: 'email',
					value: businessContact.email,
					fallback: 'contact@example.com',
				},
				{
					type: 'address',
					value: businessContact.address,
					fallback: '2360 Hood Avenue, San Diego, CA, 92123',
				},
			];
			sendPostMessage( {
				param: 'contactInfo',
				data: updatedData,
			} );
		}

		sendPostMessage( {
			param: 'images',
			data: {
				...selectedImages,
			},
			preview_type: 'full',
		} );

		if ( selectedTemplateItem?.content ) {
			sendPostMessage( {
				param: 'content',
				data: selectedTemplateItem.content,
				businessName,
			} );
		}

		setLoadingIframe( false );
		updateScaling();
	};

	const handleResize = () => {
		if ( window.innerWidth < 1024 ) {
			setCollapsed( true );
		} else {
			setCollapsed( false );
		}
	};

	// Check for window resize.
	useLayoutEffect( () => {
		const resizeObserver = new ResizeObserver( handleResize );
		resizeObserver.observe( window.document.body );
		return () => {
			resizeObserver.unobserve( window.document.body );
		};
	}, [] );

	useLayoutEffect( () => {
		requestAnimationFrame( updateScaling );
	}, [ collapsed ] );

	useLayoutEffect( () => {
		const resizeObserver = new ResizeObserver( updateScaling );
		resizeObserver.observe( window.document.body );
		return () => {
			resizeObserver.unobserve( window.document.body );
		};
	}, [] );

	const renderBrowserFrame = () => {
		const message = __(
			'This is just a sneak peek. The actual website and its content will be created in the next step.',
			'ai-builder'
		);

		return (
			<div
				className={ classNames(
					'flex items-center gap-2.5 py-0 px-3 bg-white shadow-sm rounded-t-lg mx-auto h-[44px] z-[1] relative',
					'w-full mx-0'
				) }
			>
				{ /* Traffic light dots */ }
				<div className="flex gap-2 items-center shrink-0">
					<div className="w-3 h-3 rounded-full bg-[#FF5F57]" />
					<div className="w-3 h-3 rounded-full bg-[#FFBD2E]" />
					<div className="w-3 h-3 rounded-full bg-[#28C840]" />
				</div>
				{ /* Navigation buttons */ }
				<div className="flex gap-1 items-center shrink-0 text-gray-500">
					<ChevronLeftIcon className="w-4 h-4" />
					<ChevronRightIcon className="w-4 h-4" />
					<ArrowPathIcon className="w-3.5 h-3.5" />
				</div>
				{ /* Address bar */ }
				<div className="flex items-center gap-1.5 flex-1 min-w-0 bg-[#F1F3F4] rounded-md px-3 py-1">
					<LockClosedIcon className="w-3.5 h-3.5 text-gray-500 shrink-0" />
					<p className="!m-0 truncate !text-xs !text-zip-body-text">
						{ message }
					</p>
				</div>
				{ /* Right-side icons */ }
				<div className="flex gap-1.5 items-center shrink-0 text-gray-500">
					<MagnifyingGlassIcon className="w-4 h-4" />
					<StarIcon className="w-4 h-4" />
					<EllipsisVerticalIcon className="w-4 h-4" />
				</div>
			</div>
		);
	};

	return (
		<div
			id="spectra-onboarding-ai"
			key="spectra-onboarding-ai"
			className="relative font-sans flex flex-wrap h-screen w-screen"
		>
			<main
				id="sp-onboarding-content-wrapper"
				className="flex-1 overflow-hidden h-screen max-w-full bg-white transition-all duration-200 ease-in-out"
			>
				<div className="h-full w-full relative flex">
					<div
						className={ `w-full max-h-full flex flex-col flex-auto items-center bg-preview-background overflow-hidden` }
					>
						<div className="w-full h-full flex-1">
							{ loadingIframe && (
								<div className="w-full h-full p-8 overflow-y-hidden bg-zip-app-light-bg text-center">
									{ renderBrowserFrame() }
									<SiteSkeleton className="shadow-template-preview !h-[calc(100%_-_44px)]" />
								</div>
							) }

							{ selectedTemplateItem?.domain && (
								<div
									className={ classNames(
										'w-full h-full p-8 relative'
									) }
								>
									<div
										ref={ previewContainer }
										className={ classNames(
											'h-full shadow-template-preview w-full mx-0 overflow-hidden relative'
										) }
									>
										{ renderBrowserFrame() }
										<div
											className={ classNames(
												'h-full bg-zip-app-light-bg w-[1700px] mx-0'
											) }
										>
											<iframe
												className={ classNames(
													'h-full z-[1] w-[1700px]'
												) }
												id="astra-starter-templates-preview"
												title="Website Preview"
												height="100%"
												width={ '100%' }
												src={
													addHttps(
														selectedTemplateItem.domain
													) + '?preview_demo=yes'
												}
												onLoad={ handleIframeLoading }
											/>
										</div>
									</div>
								</div>
							) }
						</div>
					</div>
				</div>
			</main>
		</div>
	);
};

export default FullPagePreview;
