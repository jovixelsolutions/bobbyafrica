import { Label, Input, Checkbox } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import { useOnboardingContext } from '../OnboardingContext';
import Heading from '@Components/onboarding/Heading';
import NavigationButtons from '@Components/onboarding/NavigationButtons';

const UserDetailInput = ( { title, value, onChange } ) => (
	<div className="w-full">
		<Label tag={ 'p' } className="mt-2">
			{ title }
		</Label>
		<Input size="md" value={ value } onChange={ onChange } />
	</div>
);

const UserDetails = () => {
	const { state, dispatch } = useOnboardingContext();
	const userDetails = state.userDetails;

	const handleData = ( key, value ) => {
		dispatch( {
			type: 'UPDATE_USER_DATA',
			payload: { option: key, value },
		} );
	};
	return (
		<div className="flex flex-col gap-2">
			<Heading
				title={ __(
					'Setup Recovery Report Emails',
					'woo-cart-abandonment-recovery'
				) }
				description={ __(
					'Enable sending recovery report emails.',
					'woo-cart-abandonment-recovery'
				) }
			/>
			<div className="flex justify-between gap-4">
				<UserDetailInput
					title={ __(
						'First Name',
						'woo-cart-abandonment-recovery'
					) }
					value={ userDetails?.user_detail_firstname }
					onChange={ ( value ) =>
						handleData( 'user_detail_firstname', value )
					}
				/>
				<UserDetailInput
					title={ __( 'Last Name', 'woo-cart-abandonment-recovery' ) }
					value={ userDetails?.user_detail_lastname }
					onChange={ ( value ) =>
						handleData( 'user_detail_lastname', value )
					}
				/>
			</div>

			<UserDetailInput
				title={ __( 'Your Email', 'woo-cart-abandonment-recovery' ) }
				value={ userDetails?.user_detail_email }
				onChange={ ( value ) =>
					handleData( 'user_detail_email', value )
				}
			/>

			<div className="mt-6 mb-4">
				<Checkbox
					size="sm"
					checked={ userDetails?.wcar_usage_optin ? true : false }
					onChange={ ( value ) =>
						handleData( 'wcar_usage_optin', value )
					}
					label={ {
						heading: (
							<span className="!text-gray-500">
								{ __(
									'Allow Cart Abandonment Recovery and our other products to track non-sensitive usage tracking data. ',
									'woo-cart-abandonment-recovery'
								) }
								<a
									className="text-flamingo-500 no-underline"
									target="_blank"
									href="https://my.cartflows.com/usage-tracking/?utm_source=dashboard&utm_medium=woo-cart-abandonment-recovery&utm_campaign=docs"
									rel="noreferrer"
								>
									{ __(
										'Learn More',
										'woo-cart-abandonment-recovery'
									) }
								</a>
							</span>
						),
					} }
				/>
			</div>
			<NavigationButtons />
		</div>
	);
};

export default UserDetails;
