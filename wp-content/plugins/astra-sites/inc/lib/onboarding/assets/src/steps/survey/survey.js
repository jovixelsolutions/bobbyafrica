import React, { useEffect } from 'react';
import { trackOnboardingStep } from '../../utils/functions';
import { __ } from '@wordpress/i18n';

const SurveyForm = ( { formDetails, updateFormDetails } ) => {
	useEffect( () => {
		// Track survey step when component mounts
		trackOnboardingStep( 'survey' );
	}, [] );

	return (
		<>
			<p className="label-text row-label !mb-2">
				{ __( 'Tell us a little bit about yourself', 'astra-sites' ) }
			</p>
			<div className="survey-fields-wrap">
				<input
					type="text"
					className="survey-text-input"
					name="first_name"
					placeholder={ __( 'Your First Name', 'astra-sites' ) }
					value={ formDetails.first_name }
					onChange={ ( e ) =>
						updateFormDetails( 'first_name', e.target.value )
					}
				/>
				<input
					type="email"
					className="survey-text-input"
					name="email"
					placeholder={ __( 'Your Work Email', 'astra-sites' ) }
					value={ formDetails.email }
					onChange={ ( e ) =>
						updateFormDetails( 'email', e.target.value )
					}
				/>
			</div>
		</>
	);
};

export default SurveyForm;
