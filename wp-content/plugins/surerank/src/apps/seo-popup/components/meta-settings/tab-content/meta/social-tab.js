import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { motion } from 'framer-motion';
import {
	Label,
	Input,
	EditorInput,
	Switch,
	Button,
	Tabs,
	Text,
} from '@bsf/force-ui';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { RefreshCcw, RefreshCw } from 'lucide-react';
import SocialPreview from '@GlobalComponents/social-preview';
import MetaField from './meta-field';
import { STORE_NAME } from '@Store/constants';
import { INPUT_VARIABLE_SUGGESTIONS as variableSuggestions } from '@Global/constants';
import {
	editorValueToString,
	stringValueToFormatJSON,
	truncateText,
} from '@Functions/utils';
import replacement from '@Functions/replacement';
import { flat } from '@Functions/variables';
import { SeoPopupTooltip } from '@AdminComponents/tooltip';
import MediaPreview from '@/apps/admin-components/media-preview';
import { createMediaFrame, getSiteDetails } from '@/global/utils/utils';
import { applyFilters } from '@wordpress/hooks';

const socialMediaTabs = [
	{
		label: __( 'Facebook', 'surerank' ),
		slug: 'facebook',
	},
	{
		label: __( 'X', 'surerank' ),
		slug: 'twitter',
	},
];

const renderIf = ( condition, content, fallbackContent ) => {
	if ( condition ) {
		return typeof content === 'function' ? content() : content;
	}

	return typeof fallbackContent === 'function'
		? fallbackContent()
		: fallbackContent;
};

const SocialTab = ( { postMetaData, updatePostMetaData, globalDefaults } ) => {
	const { variables, postDynamicData } = useSelect( ( select ) => {
		const { getVariables, getPostDynamicData } = select( STORE_NAME );
		return {
			variables: getVariables(),
			postDynamicData: getPostDynamicData(),
		};
	}, [] );

	const defaultGlobalMeta = globalDefaults;

	const [ activeTab, setActiveTab ] = useState( 'facebook' );
	const [ imageInputMode, setImageInputMode ] = useState( {
		facebook: postMetaData?.facebook_image_mode || 'uploader',
		twitter: postMetaData?.twitter_image_mode || 'uploader',
	} );
	const [ imageGenModalOpen, setImageGenModalOpen ] = useState( false );

	// Auto-detect custom field usage in image URLs and switch to custom mode
	useEffect( () => {
		const facebookImageUrl = postMetaData?.facebook_image_url || '';
		const twitterImageUrl = postMetaData?.twitter_image_url || '';

		const newImageInputMode = { ...imageInputMode };
		let hasChanges = false;

		// Check Facebook image URL for custom field pattern
		if ( facebookImageUrl.includes( '%custom_field.' ) ) {
			if ( imageInputMode.facebook !== 'custom' ) {
				newImageInputMode.facebook = 'custom';
				hasChanges = true;
			}
		}

		// Check Twitter image URL for custom field pattern
		if ( twitterImageUrl.includes( '%custom_field.' ) ) {
			if ( imageInputMode.twitter !== 'custom' ) {
				newImageInputMode.twitter = 'custom';
				hasChanges = true;
			}
		}

		// Update state only if there are changes
		if ( hasChanges ) {
			setImageInputMode( newImageInputMode );

			// Also update the saved mode in post meta
			if ( newImageInputMode.facebook === 'custom' ) {
				updatePostMetaData( { facebook_image_mode: 'custom' } );
			}
			if ( newImageInputMode.twitter === 'custom' ) {
				updatePostMetaData( { twitter_image_mode: 'custom' } );
			}
		}
	}, [ postMetaData?.facebook_image_url, postMetaData?.twitter_image_url ] );

	const getPreviewData = useCallback(
		( key, fallbackValue ) => {
			let mainKey = activeTab;

			if (
				'twitter' === activeTab &&
				!! postMetaData?.twitter_same_as_facebook
			) {
				mainKey = 'facebook';
			}

			return (
				postMetaData?.[ `${ mainKey }_${ key }` ] ||
				fallbackValue?.[ `${ mainKey }_${ key }` ]
			);
		},
		[ activeTab, postMetaData, variables, postDynamicData ]
	);

	const variablesArray = flat( variables );
	const titlePreview = truncateText(
		replacement(
			getPreviewData( 'title', defaultGlobalMeta ),
			variablesArray,
			postDynamicData
		),
		null
	);
	const descriptionPreview = truncateText(
		replacement(
			getPreviewData( 'description', defaultGlobalMeta ),
			variablesArray,
			postDynamicData
		),
		78
	);

	const fallbackImage = postMetaData?.auto_generated_og_image
		? postMetaData?.auto_generated_og_image
		: defaultGlobalMeta?.fallback_image;

	const defaultGlobalImage = getPreviewData( 'image_url', defaultGlobalMeta )
		? getPreviewData( 'image_url', defaultGlobalMeta )
		: fallbackImage;

	let finalFallbackImage = postMetaData?.[ `${ activeTab }_image_url` ]
		? postMetaData?.[ `${ activeTab }_image_url` ]
		: defaultGlobalImage;

	if ( activeTab === 'twitter' ) {
		if ( postMetaData?.twitter_same_as_facebook ) {
			finalFallbackImage =
				postMetaData?.facebook_image_url || fallbackImage;
		}
	}

	// Process custom field placeholders in image URL
	if (
		finalFallbackImage &&
		imageInputMode[ activeTab ] === 'custom' &&
		finalFallbackImage.includes( '%' )
	) {
		finalFallbackImage = replacement(
			finalFallbackImage,
			variablesArray,
			postDynamicData
		);
	}

	// If still contains unreplaced placeholders, don't use it for preview
	if ( finalFallbackImage && finalFallbackImage.includes( '%' ) ) {
		finalFallbackImage = '';
	}

	const hasImageSelected = !! postMetaData?.[ `${ activeTab }_image_url` ];

	const handleTabChange = ( { value: { slug } } ) => {
		setActiveTab( slug );
	};

	const handleRemoveImage = () => {
		updatePostMetaData( {
			[ `${ activeTab }_image_url` ]: '',
			[ `${ activeTab }_image_id` ]: '',
		} );
	};

	// Editor refs.
	const titleEditor = useRef( null );
	const descriptionEditor = useRef( null );

	const handleUpdatePostMetaData = ( key, value ) => {
		// if value is same as previous value, return
		if ( postMetaData[ key ] === value ) {
			return;
		}
		updatePostMetaData( {
			[ key ]: value,
		} );
	};

	const ImageModeToggleButton = ( { targetMode, tooltipContent } ) => (
		<SeoPopupTooltip content={ tooltipContent } placement="top-end">
			<Button
				size="xs"
				variant="outline"
				className="size-10"
				aria-label={ __( 'Switch image input mode', 'surerank' ) }
				icon={ <RefreshCw size={ 24 } /> }
				onClick={ () => {
					setImageInputMode( {
						...imageInputMode,
						[ activeTab ]: targetMode,
					} );
					handleUpdatePostMetaData(
						`${ activeTab }_image_mode`,
						targetMode
					);
				} }
			/>
		</SeoPopupTooltip>
	);

	const handleClickInput = ( event ) => {
		event.preventDefault();

		const mediaUploader = createMediaFrame( {
			title: 'Select Image',
			button: {
				text: 'Use this image',
			},
			multiple: false,
		} );

		mediaUploader.on( 'select', () => {
			const attachment = mediaUploader
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			updatePostMetaData( {
				[ `${ activeTab }_image_url` ]: attachment.url,
				[ `${ activeTab }_image_id` ]: attachment.id,
			} );
		} );

		mediaUploader.open();
	};

	const handleClearFacebookCache = () => {
		const url =
			window?.wp?.data?.select( 'core/editor' )?.getPermalink() ||
			variables?.term?.permalink?.value;

		window.open(
			`https://developers.facebook.com/tools/debug/?q=${ url }`,
			'_blank'
		);
	};

	return (
		<div className="flex flex-col gap-2 max-h-full w-full p-2">
			<Tabs.Group
				className="w-full"
				size="md"
				variant="rounded"
				activeItem={ activeTab }
				onChange={ handleTabChange }
			>
				{ socialMediaTabs.map( ( { label, slug } ) => (
					<Tabs.Tab
						key={ slug }
						slug={ slug }
						text={ label }
						className="text-sm"
					/>
				) ) }
			</Tabs.Group>

			<motion.div
				key={ activeTab }
				className="flex flex-col gap-2 flex-1 overflow-y-auto"
				initial={ { opacity: 0 } }
				animate={ { opacity: 1 } }
				exit={ { opacity: 0 } }
				transition={ { duration: 0.2 } }
			>
				{ /* Use data from Facebook tab toggle button */ }
				{ activeTab === 'twitter' && (
					<div className="flex items-center gap-3 p-2">
						<Switch
							id="facebook_same_as_twitter"
							name="facebook_same_as_twitter"
							size="sm"
							defaultValue={
								!! postMetaData?.twitter_same_as_facebook
							}
							onChange={ ( value ) => {
								handleUpdatePostMetaData(
									'twitter_same_as_facebook',
									value ? '1' : false
								);
							} }
						/>
						<Label htmlFor="facebook_same_as_twitter" size="sm">
							{ __( 'Use Data from Facebook Tab', 'surerank' ) }
						</Label>
					</div>
				) }

				{ renderIf(
					activeTab === 'twitter' &&
						!! postMetaData?.twitter_same_as_facebook,
					null,
					<>
						<div className="p-2 space-y-1.5">
							{ /* Label & Generate Image */ }
							<div className="flex items-center justify-between gap-2">
								<div className="flex items-center gap-1">
									<Label tag="span" size="sm">
										{ __( 'Social Image', 'surerank' ) }
									</Label>
								</div>
								{ applyFilters(
									'surerank-pro.og-image-trigger-button',
									null,
									{
										onClick: () =>
											setImageGenModalOpen( true ),
									}
								) }
							</div>

							{ /* Conditional Input */ }
							{ imageInputMode[ activeTab ] === 'uploader' ? (
								<>
									{ /* File Input */ }
									<div className="flex items-center gap-2 [&>div]:w-full">
										<Input
											className="m-0 w-full [&>input]:m-0 [&>input]:transition-colors [&>input]:duration-150 [&>input]:ease-in-out"
											type="file"
											size="md"
											onClick={ handleClickInput }
										/>
										<ImageModeToggleButton
											targetMode="custom"
											tooltipContent={ __(
												'Switch to custom field',
												'surerank'
											) }
										/>
									</div>

									{ /* Help text for uploader mode */ }
									<Text
										size={ 12 }
										weight={ 400 }
										color="help"
									>
										{ __(
											'Upload at least 600x315px image. Recommended size is 1200x630px.',
											'surerank'
										) }
									</Text>

									{ hasImageSelected && (
										<MediaPreview
											imageId={
												postMetaData?.[
													`${ activeTab }_image_id`
												]
											}
											onRemove={ handleRemoveImage }
										/>
									) }
								</>
							) : (
								<>
									{ /* Text Input for Custom Field */ }
									<div className="flex items-center gap-2">
										<EditorInput
											className="w-full"
											by="label"
											defaultValue={ stringValueToFormatJSON(
												postMetaData?.[
													`${ activeTab }_image_url`
												],
												variableSuggestions,
												'value'
											) }
											trigger="@"
											options={ variableSuggestions }
											onChange={ ( editorState ) => {
												handleUpdatePostMetaData(
													`${ activeTab }_image_url`,
													editorValueToString(
														editorState.toJSON()
													)
												);
											} }
											placeholder={
												/* translators: %custom_field.field_name% is a placeholder for custom field variables */
												__(
													'Enter image URL or %custom_field.field_name%',
													'surerank'
												)
											}
										/>
										<ImageModeToggleButton
											targetMode="uploader"
											tooltipContent={ __(
												'Switch to uploader',
												'surerank'
											) }
										/>
									</div>
									{ /* Hint text */ }
									<Text
										size={ 12 }
										weight={ 400 }
										color="help"
									>
										{
											/* translators: %custom_field.field_name% is a placeholder example for custom field variables */
											__(
												'Enter a custom field variable like %custom_field.field_name% or a direct image URL. Type @ to view variable suggestions.',
												'surerank'
											)
										}
									</Text>
								</>
							) }
						</div>
						{ /* Social Title */ }
						<MetaField
							label={ __( 'Social Title', 'surerank' ) }
							editorRef={ titleEditor }
							defaultValue={ stringValueToFormatJSON(
								postMetaData?.[ `${ activeTab }_title` ],
								variableSuggestions,
								'value'
							) }
							variableSuggestions={ variableSuggestions }
							onChange={ ( editorState ) => {
								handleUpdatePostMetaData(
									`${ activeTab }_title`,
									editorValueToString( editorState.toJSON() )
								);
							} }
							fieldKey={ `${ activeTab }_title` }
							onUseThis={ ( fieldKey, content ) => {
								handleUpdatePostMetaData( fieldKey, content );
							} }
						/>

						{ /* Social Description */ }
						<MetaField
							label={ __( 'Social Description', 'surerank' ) }
							editorRef={ descriptionEditor }
							defaultValue={ stringValueToFormatJSON(
								postMetaData?.[ `${ activeTab }_description` ],
								variableSuggestions,
								'value'
							) }
							variableSuggestions={ variableSuggestions }
							onChange={ ( editorState ) => {
								handleUpdatePostMetaData(
									`${ activeTab }_description`,
									editorValueToString( editorState.toJSON() )
								);
							} }
							className="[&+div]:items-start [&+div]:pt-1"
							fieldKey={ `${ activeTab }_description` }
							onUseThis={ ( fieldKey, content ) => {
								handleUpdatePostMetaData( fieldKey, content );
							} }
						/>
					</>
				) }
				<div className="p-2 space-y-2">
					{ /* Label */ }
					<div className="flex items-center justify-between">
						<Label tag="span" size="sm">
							{ sprintf(
								// Translators: %s: Facebook or Twitter
								__( '%s Preview', 'surerank' ),
								activeTab === 'facebook' ? 'Facebook' : 'X'
							) }
						</Label>
						<div className="flex items-center gap-2">
							{ activeTab === 'facebook' && (
								<SeoPopupTooltip
									content={ __(
										"Click to update Facebook's share preview cache. This will update the preview with the latest content.",
										'surerank'
									) }
									placement="top-end"
									offset={ {
										alignmentAxis: '0',
										mainAxis: '8',
									} }
									arrow
								>
									<Button
										size="sm"
										className="p-0.5"
										onClick={ handleClearFacebookCache }
										icon={ <RefreshCcw /> }
										variant="ghost"
									/>
								</SeoPopupTooltip>
							) }
						</div>
					</div>
					{ /* Preview */ }
					<SocialPreview
						type={ activeTab }
						title={ titlePreview }
						description={ descriptionPreview }
						imageURL={ finalFallbackImage }
						twitterLargePreview={
							activeTab === 'twitter' &&
							globalDefaults?.twitter_card_type ===
								'summary_large_image'
						}
						siteURL={ variables?.site?.site_url?.value?.replace(
							/(^\w+:|^)\/\//,
							''
						) }
						hideRemoveButton={ true }
						forMetaBox
					/>
				</div>
			</motion.div>

			{ /* Image Generation Modal */ }
			{ applyFilters( 'surerank-pro.og-image-modal', null, {
				open: imageGenModalOpen,
				setOpen: setImageGenModalOpen,
				postTitle:
					replacement(
						postMetaData?.[ `${ activeTab }_title` ],
						variablesArray,
						postDynamicData
					) || getSiteDetails().siteTitle,
				postDescription:
					replacement(
						postMetaData?.[ `${ activeTab }_description` ],
						variablesArray,
						postDynamicData
					) || getSiteDetails().siteDescription,
				onSelectImage: ( image ) => {
					updatePostMetaData( {
						[ `${ activeTab }_image_url` ]: image.url,
						[ `${ activeTab }_image_id` ]: image.id,
					} );
				},
			} ) }
		</div>
	);
};

export default SocialTab;
