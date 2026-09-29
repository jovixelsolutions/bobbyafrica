import { __ } from '@wordpress/i18n';
import {
	Fragment,
	createInterpolateElement,
	useState,
} from '@wordpress/element';
import { Title, Text } from '@bsf/force-ui';
import { useOnboardingState } from '@Onboarding/store';
import { renderField } from '../utils';
import StepNavButtons from '../components/nav-buttons';
import apiFetch from '@wordpress/api-fetch';
import { ONBOARDING_URL } from '@Global/constants/api';
import useFormValidation from '@Global/hooks/use-form-validation';

const inputFields = [
	{
		label: __( 'First Name', 'surerank' ),
		name: 'first_name',
		type: 'text',
		width: 'half',
		required: true,
	},
	{
		label: __( 'Last Name', 'surerank' ),
		name: 'last_name',
		type: 'text',
		width: 'half',
		required: false,
	},
	{
		label: __( 'Email Address', 'surerank' ),
		name: 'email',
		type: 'email',
		width: 'full',
		required: true,
	},
	{
		label: (
			<span>
				{ ' ' }
				{ createInterpolateElement(
					__(
						'Stay in the loop and help shape SureRank! Get feature updates, and help us build a better SureRank by sharing how you use the plugin. <a>Privacy Policy</a>',
						'surerank'
					),
					{
						a: (
							<Text
								as="a"
								tabIndex="0"
								color="link"
								href={ surerank_globals.privacy_policy_url }
								target="_blank"
								rel="noopener noreferrer"
								className="no-underline focus:ring-0 focus:underline hover:no-underline"
							/>
						),
					}
				) }
			</span>
		),
		name: 'agree_to_terms',
		value: 'agree_to_terms',
		type: 'checkbox',
		required: false,
	},
];

/**
 * Submit onboarding data to the API
 *
 * @param {Object} websiteDetails     - Website details from state
 * @param {Object} socialProfilesURLs - Social profiles from state
 * @param {Object} userDetails        - User details from state
 * @return {Promise} API response
 */
export const submitOnboardingData = async (
	websiteDetails = {},
	socialProfilesURLs = {},
	userDetails = {}
) => {
	const about_page =
		websiteDetails?.about_page?.value ?? websiteDetails?.about_page;
	const contact_page =
		websiteDetails?.contact_page?.value ?? websiteDetails?.contact_page;

	const payload = {
		website_type: websiteDetails.website_type || '',
		website_name: websiteDetails.website_name || '',
		website_owner_name: websiteDetails.website_owner_name || '',
		business_description: websiteDetails.business_description || '',
		organization_type: websiteDetails.organization_type || 'Organization',
		website_owner_phone: websiteDetails.website_owner_phone || '',
		website_logo: websiteDetails.website_logo?.url || '',
		about_page: about_page ? parseInt( about_page ) : 0,
		contact_page: contact_page ? parseInt( contact_page ) : 0,
		social_profiles: socialProfilesURLs || {},
		first_name: userDetails.first_name || '',
		last_name: userDetails.last_name || '',
		email: userDetails.email || '',
		agree_to_terms:
			typeof userDetails.agree_to_terms === 'boolean'
				? userDetails.agree_to_terms
				: false,
	};

	return await apiFetch( {
		path: ONBOARDING_URL,
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify( payload ),
	} );
};

const UserDetails = () => {
	const [
		{ userDetails = {}, websiteDetails = {}, socialProfilesURLs = {} },
		dispatch,
	] = useOnboardingState();
	const leadDetails =
		surerank_admin_common?.website_details?.website_lead_details || {};
	// Local form state
	const [ formState, setFormState ] = useState( {
		first_name: userDetails.first_name || leadDetails.first_name || '',
		last_name: userDetails.last_name || leadDetails.last_name || '',
		email: userDetails.email || leadDetails.email || '',
		agree_to_terms:
			typeof userDetails.agree_to_terms === 'boolean'
				? userDetails.agree_to_terms
				: true,
		...userDetails,
	} );
	const { errors, validate, clearFieldError } = useFormValidation(
		formState,
		inputFields
	);

	const handleSaveForm = () => {
		dispatch( { userDetails: formState } );
	};

	const handleChangeSelection = ( name ) => ( value ) => {
		clearFieldError( name );
		const newFormState = {
			...formState,
			[ name ]: value,
		};
		setFormState( newFormState );
		dispatch( { userDetails: newFormState } );
	};

	const handleSubmit = ( event ) => {
		event.preventDefault();
	};

	const handleClickNext =
		( skip = false ) =>
		async ( setIsLoading ) => {
			setIsLoading( ( prevState ) => ( { ...prevState, next: true } ) );

			if ( ! skip && ! validate() ) {
				setIsLoading( ( prevState ) => ( {
					...prevState,
					next: false,
				} ) );
				throw new Error( __( 'Form validation error!', 'surerank' ), {
					cause: 'form-validation',
				} );
			}

			try {
				await submitOnboardingData(
					websiteDetails,
					socialProfilesURLs,
					! skip
						? {
								first_name: formState.first_name || '',
								last_name: formState.last_name || '',
								email: formState.email || '',
								agree_to_terms: formState.agree_to_terms,
						  }
						: {
								agree_to_terms: false,
						  }
				);
			} catch ( error ) {
				// Silently handle API errors.
			}
		};

	return (
		<form
			className="flex flex-col gap-6"
			onSubmit={ handleSubmit }
			noValidate
		>
			<div className="space-y-1">
				<Title
					tag="h4"
					title={ __( 'Okay, just one last step…', 'surerank' ) }
					size="md"
				/>
				<Text>
					{ __(
						'Help us tailor your SureRank experience and keep you updated with SEO tips, feature improvements, and helpful recommendations.',
						'surerank'
					) }
				</Text>
			</div>

			<div className="flex flex-wrap gap-6">
				{ inputFields.map( ( field, index ) => (
					<Fragment key={ field.name }>
						{ renderField(
							field,
							formState[ field.name ],
							handleChangeSelection( field.name ),
							errors[ field.name ],
							{
								initialFocus: index === 0,
							}
						) }
					</Fragment>
				) ) }
			</div>

			{ /* Nav Buttons */ }
			<StepNavButtons
				nextProps={ {
					onClick: handleClickNext( ! formState.agree_to_terms ),
					children: __( 'Finish', 'surerank' ),
				} }
				backProps={ {
					onClick: handleSaveForm,
				} }
				skipProps={ {
					onClick: handleClickNext( true ),
				} }
			/>
		</form>
	);
};

export default UserDetails;
