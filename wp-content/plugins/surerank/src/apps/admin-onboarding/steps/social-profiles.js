import { __ } from '@wordpress/i18n';
import { useState, useMemo, useRef, useEffect } from '@wordpress/element';
import {
	FacebookIcon,
	TwitterIcon,
	InstagramIcon,
	LinkedInIcon,
	YouTubeIcon,
	PinterestIcon,
	TikTokIcon,
	MediumIcon,
	TumblrIcon,
	ThreadsIcon,
	YelpIcon,
	WhatsAppIcon,
	TelegramIcon,
	BlueSkyIcon,
} from '../icons';
import { Button, DropdownMenu, Title, Text } from '@bsf/force-ui';
import { PlusIcon, LinkIcon } from 'lucide-react';
import { useNavigate } from '@tanstack/react-router';
import StepNavButtons from '../components/nav-buttons';
import { useOnboardingState } from '@Onboarding/store';
import useOnboardingAuth from '../hooks/use-onboarding-auth';
import { submitOnboardingData } from './user-details';
import { focusHelper } from '../utils';

const iconMap = {
	facebook: FacebookIcon,
	twitter: TwitterIcon,
	instagram: InstagramIcon,
	linkedin: LinkedInIcon,
	youtube: YouTubeIcon,
	pinterest: PinterestIcon,
	tiktok: TikTokIcon,
	medium: MediumIcon,
	tumblr: TumblrIcon,
	threads: ThreadsIcon,
	yelp: YelpIcon,
	whatsapp: WhatsAppIcon,
	telegram: TelegramIcon,
	link: LinkIcon,
	bluesky: BlueSkyIcon,
};

// Default social profiles to show
const defaultSocialIds = [
	'facebook',
	'instagram',
	'twitter',
	'youtube',
	'whatsapp',
	'pinterest',
];

const socialProfiles = surerank_admin_common?.social_profiles
	.filter( ( item ) => defaultSocialIds.includes( item.id ) )
	.map( ( item ) => ( {
		...item,
		icon: iconMap[ item.id ] || iconMap.link,
	} ) );

const dropdownOptions = surerank_admin_common?.social_profiles
	.filter( ( item ) => ! defaultSocialIds.includes( item.id ) )
	.map( ( item ) => ( {
		...item,
		icon: iconMap[ item.id ] || iconMap.link,
	} ) );

/**
 * Get the initial list of social profiles to render.
 *
 * @param {Object} formState - The form state.
 * @return {Array} The initial list of social profiles to render.
 */
const getInitialList = ( formState ) => {
	return [
		...socialProfiles,
		...dropdownOptions.filter( ( item ) => item.id in formState ),
	];
};

const SocialProfiles = () => {
	const [ { socialProfilesURLs = {}, websiteDetails = {} }, dispatch ] =
		useOnboardingState();
	const { isAuthenticated } = useOnboardingAuth();
	const navigate = useNavigate();
	// Local states
	const [ formState, setFormState ] = useState( socialProfilesURLs );
	const [ socialProfileLists, setSocialProfileLists ] = useState(
		getInitialList( formState )
	);
	const scrollContainerRef = useRef( null );

	// Auto-scroll to bottom when new field is added
	useEffect( () => {
		if ( scrollContainerRef.current ) {
			scrollContainerRef.current.scrollTo( {
				top: scrollContainerRef.current.scrollHeight,
				behavior: 'smooth',
			} );
		}
	}, [ socialProfileLists.length ] );

	const handleAddProfileToList = ( socialMediaItem ) => {
		setSocialProfileLists( ( prev ) => [ ...prev, socialMediaItem ] );
	};

	const filteredDropdownOptions = useMemo( () => {
		return dropdownOptions.filter(
			( item ) =>
				! socialProfileLists.some(
					( listItem ) => listItem.id === item.id
				)
		);
	}, [ socialProfileLists ] );

	const handleChange = ( id ) => ( event ) => {
		setFormState( ( prev ) => ( { ...prev, [ id ]: event.target.value } ) );
	};

	const handleSaveForm = () => {
		dispatch( {
			socialProfilesURLs: formState,
		} );
	};

	const handleClickNext = async ( setIsLoading ) => {
		dispatch( {
			socialProfilesURLs: formState,
		} );

		// If authenticated, skip user-details and submit data directly
		if ( isAuthenticated ) {
			setIsLoading( ( prev ) => ( { ...prev, next: true } ) );
			try {
				await submitOnboardingData( websiteDetails, formState, {
					agree_to_terms: false,
				} );
			} catch ( error ) {
				// Silently handle API errors.
			}
			navigate( { to: '/finish' } );
			// Throw to prevent default navigation to user-details
			throw new Error( '', { cause: 'form-validation' } );
		}
	};

	return (
		<div className="flex flex-col gap-6">
			<div className="space-y-1">
				<Title
					tag="h4"
					title={ __( 'Social Profiles', 'surerank' ) }
					size="md"
				/>
				<p className="w-full">
					{ __(
						'Please enter your social media profiles. These links can appear in the knowledge panel of the search results for your website.',
						'surerank'
					) }
				</p>
			</div>
			{ /* Settings / options */ }
			<div className="flex flex-col gap-4">
				{ /* Scrollable container for social profile inputs */ }
				<div
					ref={ scrollContainerRef }
					className="flex flex-col gap-6 p-2 max-h-[500px] overflow-y-auto"
				>
					{ socialProfileLists.map(
						( { label, id, placeholder, icon: Icon }, index ) => (
							<div key={ id } className="flex flex-col gap-1.5">
								{ /* Input field with prefix */ }
								<div className="relative flex items-center">
									{ /* Prefix with icon and label */ }
									<div className="absolute left-0 flex items-center gap-2 pl-4 pointer-events-none">
										<Icon className="size-5 text-text-secondary flex-shrink-0" />
										<Text
											size={ 14 }
											className="capitalize"
											color="text-text-secondary"
											weight={ 500 }
										>
											{ label }
										</Text>
									</div>
									<input
										id={ `social-${ id }` }
										type="url"
										className="w-full py-3 pr-4 pl-[calc(1rem+1px+0.5rem+80px+0.5rem+1ch+0.5rem)] text-sm text-text-primary placeholder:text-text-quaternary border border-solid border-border-subtle rounded-lg bg-white focus:border-border-strong focus:ring-2 focus:ring-border-subtle focus:outline-none transition-all duration-200 shadow-sm"
										placeholder={ placeholder }
										onChange={ handleChange( id ) }
										value={ formState[ id ] || '' }
										{ ...( index === 0 && {
											ref: focusHelper,
										} ) }
									/>
								</div>
							</div>
						)
					) }
				</div>
				{ /* Add More button - always visible outside scroll container */ }
				{ !! filteredDropdownOptions.length && (
					<div className="px-2">
						<DropdownMenu>
							<DropdownMenu.Trigger>
								<Button
									type="button"
									variant="outline"
									className="w-max"
									size="xs"
									icon={ <PlusIcon /> }
									iconPosition="right"
								>
									{ __( 'Add More', 'surerank' ) }
								</Button>
							</DropdownMenu.Trigger>
							<DropdownMenu.Portal id="surerank-root">
								<DropdownMenu.ContentWrapper>
									<DropdownMenu.Content className="w-60">
										<DropdownMenu.List>
											{ filteredDropdownOptions.map(
												( {
													label,
													id,
													icon: Icon,
													placeholder,
												} ) => (
													<DropdownMenu.Item
														key={ id }
														onClick={ () =>
															handleAddProfileToList(
																{
																	label,
																	id,
																	icon: Icon,
																	placeholder,
																}
															)
														}
													>
														<div className="flex items-center gap-3 w-full">
															<Icon className="size-5 text-text-secondary flex-shrink-0" />
															<Text
																size={ 14 }
																color="text-field-label"
																weight={ 500 }
															>
																{ label }
															</Text>
														</div>
													</DropdownMenu.Item>
												)
											) }
										</DropdownMenu.List>
									</DropdownMenu.Content>
								</DropdownMenu.ContentWrapper>
							</DropdownMenu.Portal>
						</DropdownMenu>
					</div>
				) }
			</div>
			<StepNavButtons
				className="my-0"
				skipProps={ {
					onClick: handleClickNext,
				} }
				nextProps={ {
					onClick: handleClickNext,
				} }
				backProps={ {
					onClick: handleSaveForm,
				} }
			/>
		</div>
	);
};

export default SocialProfiles;
