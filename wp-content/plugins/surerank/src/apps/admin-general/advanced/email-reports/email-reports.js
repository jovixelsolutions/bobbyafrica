import { __ } from '@wordpress/i18n';
import { useSuspenseSelect, useDispatch } from '@wordpress/data';
import {
	Text,
	Select,
	Input,
	Button,
	Switch,
	toast,
	Loader,
} from '@bsf/force-ui';
import { Link } from '@tanstack/react-router';
import PageContentWrapper from '@AdminComponents/page-content-wrapper';
import { GeneratePageContentOnly } from '@Functions/page-content-generator';
import WEEK_DAYS from '@Global/constants/week-days';
import withSuspense from '@AdminComponents/hoc/with-suspense';
import { STORE_NAME } from '@AdminStore/constants';
import Alert from '@Global/components/alert';
import useFormValidation from '@Global/hooks/use-form-validation';
import useUnsavedChanges from '@Global/hooks/use-unsaved-changes';
import { saveEmailReportsSettings, sendTestEmailReport } from '@Functions/api';
import { useState, useCallback } from '@wordpress/element';
import { cn } from '@/functions/utils';
import { applyFilters } from '@wordpress/hooks';

const TEST_EMAIL_BUTTON_TEXT = __( 'Test Email', 'surerank' );
const SENDING_TEXT = __( 'Sending…', 'surerank' );
const WEEKLY_LABEL = __( 'Weekly', 'surerank' );
const MONTHLY_LABEL = __( 'Monthly', 'surerank' );

const inputFields = [
	{
		name: 'recipientEmail',
		label: __( 'Send Email To', 'surerank' ),
		type: 'email',
		required: true,
	},
];

const EmailReportsSettings = () => {
	const { authenticated, hasSiteSelected } = useSuspenseSelect(
		( select ) => {
			const selectors = select( STORE_NAME );
			return {
				...selectors.getSearchConsole(),
			};
		}
	);

	const emailReportsSettings = useSuspenseSelect( ( select ) =>
		select( STORE_NAME ).getEmailReportsSettings()
	);

	const { setEmailReportsSettings } = useDispatch( STORE_NAME );

	const { errors, validate, clearFieldError } = useFormValidation(
		{ recipientEmail: emailReportsSettings.recipientEmail },
		emailReportsSettings.enabled ? inputFields : []
	);

	const [ isUpdating, setIsUpdating ] = useState( false );
	const [ isSendingTest, setIsSendingTest ] = useState( false );

	// Use the unsaved changes hook for tracking and navigation blocking
	const { resetInitialSettings, getButtonIcon, getSaveButtonClassName } =
		useUnsavedChanges( {
			currentSettings: emailReportsSettings,
			enableNavigationBlock: false,
			enableBeforeUnload: true,
			isUpdating,
		} );

	const handleToggleChange = ( checked ) => {
		setEmailReportsSettings( { enabled: checked } );
	};

	const handleEmailChange = ( value ) => {
		clearFieldError( 'recipientEmail' );
		setEmailReportsSettings( { recipientEmail: value } );
	};

	const handleFrequencyChange = ( value ) => {
		setEmailReportsSettings( { frequency: value } );
	};

	const handleScheduleChange = ( value ) => {
		setEmailReportsSettings( { scheduledOn: value } );
	};

	const handleMonthlyDateChange = ( value ) => {
		const numValue = parseInt( value, 10 );
		// Clamp value between 1 and 31
		const clampedValue = Math.min( Math.max( numValue, 1 ), 31 );
		setEmailReportsSettings( { monthlyDate: clampedValue } );
	};

	const handleTestEmail = async () => {
		if ( ! validate() ) {
			return;
		}

		if ( isSendingTest ) {
			return;
		}

		setIsSendingTest( true );
		try {
			const response = await sendTestEmailReport(
				emailReportsSettings.recipientEmail
			);

			if ( ! response?.success ) {
				throw new Error(
					response?.message ??
						__( 'Failed to send test email.', 'surerank' )
				);
			}

			toast.success( __( 'Test email sent successfully', 'surerank' ), {
				message:
					response?.message ??
					__( 'Test email sent successfully!', 'surerank' ),
			} );
		} catch ( error ) {
			toast.error( error.message, {
				description: __(
					'Please check your email configuration and try again.',
					'surerank'
				),
			} );
		} finally {
			setIsSendingTest( false );
		}
	};

	const saveEmailReportsSettingsCallback = useCallback(
		async ( settings ) => {
			if ( isUpdating ) {
				return;
			}

			setIsUpdating( true );
			try {
				const response = await saveEmailReportsSettings( settings );
				if ( ! response?.success ) {
					throw new Error(
						response?.message ??
							__(
								'Failed to save email reports settings.',
								'surerank'
							)
					);
				}

				// Update the store with saved settings to ensure sync
				if ( response?.data ) {
					setEmailReportsSettings( response.data );
				}

				toast.success(
					__( 'Settings saved successfully!', 'surerank' )
				);

				// Reset after a small delay to ensure store has updated
				setTimeout( () => {
					resetInitialSettings();
				}, 100 );
			} catch ( error ) {
				toast.error( __( 'Failed to save settings', 'surerank' ), {
					description: error.message,
				} );
			} finally {
				setIsUpdating( false );
			}
		},
		[ isUpdating, resetInitialSettings, setEmailReportsSettings ]
	);

	const handleSave = () => {
		if ( validate() ) {
			saveEmailReportsSettingsCallback( emailReportsSettings );
		}
	};

	let renderContent = (
		<>
			{ /* Email Summary Toggle */ }
			<div className="flex flex-col gap-2 p-2">
				<Switch
					label={ {
						heading: __( 'Email Summary', 'surerank' ),
						description: __(
							'Email SEO performance reports for your website, helping you track rankings and improvements without manual work.',
							'surerank'
						),
					} }
					checked={ emailReportsSettings.enabled }
					onChange={ handleToggleChange }
				/>
			</div>

			{ /* Send Email To */ }
			{ emailReportsSettings.enabled && (
				<>
					<div className="flex flex-col gap-1.5 p-2">
						<Text
							as="label"
							htmlFor="email"
							size={ 14 }
							weight={ 500 }
							color="primary"
							className="w-fit after:content-['*'] after:inline-block after:ml-0.5 after:text-text-error"
						>
							{ __( 'Send Email To', 'surerank' ) }
						</Text>
						<div className="flex gap-2">
							<div className="flex-1">
								<Input
									id="email"
									name="recipientEmail"
									type="email"
									size="md"
									value={
										emailReportsSettings.recipientEmail
									}
									onChange={ handleEmailChange }
									error={ errors.recipientEmail }
								/>
							</div>
							<Button
								variant="outline"
								size="md"
								onClick={ handleTestEmail }
								icon={ isSendingTest && <Loader /> }
							>
								{ isSendingTest
									? SENDING_TEXT
									: TEST_EMAIL_BUTTON_TEXT }
							</Button>
						</div>
					</div>

					<div className="flex gap-4 p-2">
						<div className="flex flex-col gap-1.5 flex-1">
							<Text
								as="label"
								htmlFor="frequency"
								size={ 14 }
								weight={ 500 }
								color="primary"
								className="w-fit"
							>
								{ __( 'Frequency', 'surerank' ) }
							</Text>
							<Select
								size="md"
								value={
									emailReportsSettings.frequency ?? 'weekly'
								}
								onChange={ handleFrequencyChange }
							>
								<Select.Button
									type="button"
									id="frequency"
									render={ ( value ) =>
										value === 'monthly'
											? MONTHLY_LABEL
											: WEEKLY_LABEL
									}
								/>
								<Select.Portal id="surerank-root">
									<Select.Options>
										<Select.Option value="weekly">
											{ WEEKLY_LABEL }
										</Select.Option>
										<Select.Option value="monthly">
											{ MONTHLY_LABEL }
										</Select.Option>
									</Select.Options>
								</Select.Portal>
							</Select>
						</div>

						{ emailReportsSettings.frequency === 'weekly' ? (
							<div className="flex flex-col gap-1.5 flex-1">
								<Text
									as="label"
									htmlFor="schedule"
									size={ 14 }
									weight={ 500 }
									color="primary"
									className="w-fit"
								>
									{ __( 'Schedule Summary', 'surerank' ) }
								</Text>
								<Select
									size="md"
									value={ emailReportsSettings.scheduledOn }
									onChange={ handleScheduleChange }
								>
									<Select.Button
										type="button"
										id="schedule"
										render={ ( value ) =>
											WEEK_DAYS[ value ?? 'sunday' ]
										}
									/>
									<Select.Portal id="surerank-root">
										<Select.Options>
											{ Object.entries( WEEK_DAYS ).map(
												( [ value, label ] ) => (
													<Select.Option
														key={ value }
														value={ value }
													>
														{ label }
													</Select.Option>
												)
											) }
										</Select.Options>
									</Select.Portal>
								</Select>
							</div>
						) : (
							<div className="flex flex-col gap-1.5 flex-1">
								<Text
									as="label"
									htmlFor="monthlyDate"
									size={ 14 }
									weight={ 500 }
									color="primary"
									className="w-fit"
								>
									{ __( 'Day of Month', 'surerank' ) }
								</Text>
								<Input
									id="monthlyDate"
									name="monthlyDate"
									type="number"
									size="md"
									min={ 1 }
									max={ 31 }
									value={
										emailReportsSettings.monthlyDate ?? 1
									}
									onChange={ handleMonthlyDateChange }
								/>
							</div>
						) }
					</div>

					{ emailReportsSettings.frequency === 'monthly' && (
						<div className="flex flex-col gap-1.5 px-2 pb-2">
							<Text size={ 12 } weight={ 400 } color="tertiary">
								{ __(
									'If the selected day does not exist in a month (e.g., 31st in February), the email will be sent on the last day of that month.',
									'surerank'
								) }
							</Text>
						</div>
					) }

					{ /* Pro Extension Point: Additional Fields */ }
					{ applyFilters(
						'surerank_email_reports_additional_fields',
						null,
						{
							emailReportsSettings,
							setEmailReportsSettings,
						}
					) }
				</>
			) }
		</>
	);

	// If search console is not authenticated or site is not selected, show alert
	if ( ! authenticated || ! hasSiteSelected ) {
		renderContent = (
			<Alert
				title={ __(
					'Connect Google Search Console to Enable Email Summary',
					'surerank'
				) }
				message={ __(
					'Email summary uses GSC data to show your site’s search performance. Connect now to unlock and configure report settings.',
					'surerank'
				) }
				color="info"
				footer={
					<Button
						variant="link"
						tag={ Link }
						to={
							!! surerank_globals?.enable_google_console
								? '/search-console'
								: '/tools/manage-features'
						}
						className="w-fit [&>span]:p-0 no-underline"
					>
						{ __( 'Connect Now', 'surerank' ) }
					</Button>
				}
			/>
		);
	}

	return (
		<div className="flex flex-col w-full max-w-full overflow-hidden">
			{ renderContent }
			{ /* Save Button - only show when GSC is connected */ }
			{ authenticated && hasSiteSelected && (
				<div className="flex justify-start pt-6 ml-2 mb-2">
					<Button
						variant="primary"
						size="md"
						onClick={ handleSave }
						icon={ getButtonIcon() }
						className={ cn( getSaveButtonClassName() ) }
					>
						{ isUpdating
							? __( 'Saving…', 'surerank' )
							: __( 'Save', 'surerank' ) }
					</Button>
				</div>
			) }
		</div>
	);
};

export const PAGE_CONTENT = [
	{
		container: null,
		content: [
			{
				id: 'email-reports-settings',
				type: 'custom',
				component: <EmailReportsSettings />,
				searchKeywords: [
					'email reports',
					'reports',
					'seo reports',
					'email summary',
				],
			},
		],
	},
];

const EmailReports = () => {
	return (
		<PageContentWrapper title={ __( 'Email Reports', 'surerank' ) }>
			<GeneratePageContentOnly
				json={ PAGE_CONTENT }
				hideGlobalSaveButton={ true }
			/>
		</PageContentWrapper>
	);
};

export default withSuspense( EmailReports );
