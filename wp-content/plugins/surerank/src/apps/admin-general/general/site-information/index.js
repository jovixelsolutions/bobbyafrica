import { __ } from '@wordpress/i18n';
import { renderField } from '@Onboarding/utils';
import {
	Fragment,
	useState,
	useEffect,
	useMemo,
	useRef,
} from '@wordpress/element';
import { Label, Button, toast } from '@bsf/force-ui';
import apiFetch from '@wordpress/api-fetch';
import { InfoTooltip } from '@AdminComponents/tooltip';
import AdminLoadingSkeleton from '@AdminComponents/loading-skeleton';
import { cn } from '@/functions/utils';
import useOnboardingAuth from '@Onboarding/hooks/use-onboarding-auth';
import useImproveDescription from '@Global/hooks/use-improve-description';
import { ImproveWithAiButton } from '@AdminComponents/improve-with-ai-button';
import useUnsavedChanges from '@Global/hooks/use-unsaved-changes';
import PageContentWrapper from '@AdminComponents/page-content-wrapper';
import withSuspense from '@AdminComponents/hoc/with-suspense';

const websiteTypes = [
	{
		label: __( 'Personal Website', 'surerank' ),
		value: 'personal',
	},
	{
		label: __( 'Business Website', 'surerank' ),
		value: 'business',
	},
	{
		label: __( 'Organization', 'surerank' ),
		value: 'organization',
	},
	{
		label: __( 'Personal Blog', 'surerank' ),
		value: 'blog',
	},
	{
		label: __( 'Community Blog/News Website', 'surerank' ),
		value: 'community',
	},
	{
		label: __( 'E-commerce Store', 'surerank' ),
		value: 'ecommerce',
	},
];

const SiteInformationSettings = () => {
	const [ formState, setFormState ] = useState( {} );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( true );

	const organizationOptions = Object.values(
		surerank_globals?.schema_type_options?.Organization || {}
	);

	const { isAuthenticated, isConnecting, handleConnect } = useOnboardingAuth(
		{
			skipCheck: true,
		}
	);

	// Use the unsaved changes hook for tracking and navigation blocking
	const { resetInitialSettings, getButtonIcon, getSaveButtonClassName } =
		useUnsavedChanges( {
			currentSettings: formState,
			enableNavigationBlock: false,
			enableBeforeUnload: true,
			isUpdating: isSaving,
		} );

	const shouldAutoImprove = useRef( false );

	// Use the improve description hook
	const { isImproving, improveDescription, hasMinimumWords } =
		useImproveDescription( {
			businessDescription: formState.business_description,
			websiteName: formState.website_name,
			organizationType: formState.organization_type,
			onSuccess: ( description ) => {
				setFormState( ( prev ) => ( {
					...prev,
					business_description: description,
				} ) );
			},
		} );

	/**
	 * Fetch Knowledge Graph settings from API on mount
	 */
	useEffect( () => {
		const fetchSettings = async () => {
			setIsLoading( true );
			try {
				const response = await apiFetch( {
					path: '/surerank/v1/knowledge-graph',
					method: 'GET',
				} );

				if ( response?.success && response?.data ) {
					setFormState( response.data );

					// Reset initial settings after loading data to avoid showing unsaved changes indicator
					setTimeout( () => {
						resetInitialSettings();
					}, 100 );
				}
			} catch ( error ) {
				toast.error(
					__( 'Failed to load Knowledge Graph settings.', 'surerank' )
				);
			} finally {
				setIsLoading( false );
			}
		};

		fetchSettings();
	}, [ resetInitialSettings ] );

	const handleChangeSelection = ( name ) => ( value ) => {
		setFormState( ( prev ) => {
			if ( name === 'website_logo' ) {
				return {
					...prev,
					[ name ]: value?.url ?? '',
				};
			}
			return {
				...prev,
				[ name ]: value?.value ?? value,
			};
		} );
	};

	useEffect( () => {
		if ( isAuthenticated && shouldAutoImprove.current ) {
			shouldAutoImprove.current = false;
			improveDescription();
		}
	}, [ isAuthenticated, improveDescription ] );

	const handleConnectAndImprove = () => {
		shouldAutoImprove.current = true;
		handleConnect();
	};

	const textareaRows = useMemo( () => {
		const text = formState.business_description || '';
		const lineBreaks = ( text.match( /\n/g ) || [] ).length + 1;
		const textLength = text.length;
		const calculatedRows = Math.max(
			lineBreaks,
			Math.ceil( textLength / 60 )
		);
		return Math.min( Math.max( calculatedRows, 4 ), 8 );
	}, [ formState.business_description ] );

	const baseFields = [
		{
			label: __( 'This Website Represents', 'surerank' ),
			name: 'website_type',
			type: 'select',
			options: websiteTypes || [],
		},
		{
			label: __( 'Organization Type', 'surerank' ),
			name: 'organization_type',
			type: 'selectGroup',
			options: organizationOptions,
			conditionalOn: 'website_type',
			conditionalValues: [
				'business',
				'organization',
				'ecommerce',
				'community',
			],
		},
		{
			label: __( 'Website Name', 'surerank' ),
			name: 'website_name',
			type: 'text',
			conditionalOn: 'website_type',
			conditionalValues: [
				'business',
				'organization',
				'ecommerce',
				'community',
			],
		},
		{
			label: __( 'Phone Number (Optional)', 'surerank' ),
			name: 'website_owner_phone',
			type: 'text',
		},
		{
			label: (
				<>
					<div className="flex items-center justify-between gap-2 w-full">
						<div className="flex items-center justify-start gap-1">
							<Label tag="span" size="sm">
								{ __( 'Describe what you do', 'surerank' ) }
							</Label>
							<InfoTooltip
								content={ __(
									'Please describe what you do in a few sentences. This description will be used for content generation and other purposes.',
									'surerank'
								) }
							/>
						</div>
						<ImproveWithAiButton
							isAuthenticated={ isAuthenticated }
							isConnecting={ isConnecting }
							hasMinimumWords={ hasMinimumWords }
							isImproving={ isImproving }
							onImprove={ improveDescription }
							onConnect={ handleConnectAndImprove }
						/>
					</div>
				</>
			),
			name: 'business_description',
			type: 'textarea',
			width: 'full',
			rows: textareaRows,
		},
		{
			label: __( 'Website Owner Name', 'surerank' ),
			name: 'website_owner_name',
			type: 'text',
			conditionalOn: 'website_type',
			conditionalValues: [ 'personal', 'blog' ],
		},
		{
			label: __( 'Website Logo', 'surerank' ),
			name: 'website_logo',
			type: 'file',
			width: 'full',
			accept: 'image/*',
			description: __(
				'Recommended Logo size 112 X 112 or more, PNG / JPG format',
				'surerank'
			),
		},
	];

	const filteredFields = baseFields.filter( ( field ) => {
		if ( field.conditionalOn === undefined ) {
			return true;
		}
		return field.conditionalValues?.includes(
			formState[ field.conditionalOn ]
		);
	} );

	const handleSaveChanges = async () => {
		setIsSaving( true );
		try {
			const response = await apiFetch( {
				path: '/surerank/v1/knowledge-graph',
				method: 'POST',
				data: formState,
			} );

			if ( response?.success ) {
				toast.success(
					__( 'Settings saved successfully!', 'surerank' )
				);

				// Reset after a small delay to ensure state has updated
				setTimeout( () => {
					resetInitialSettings();
				}, 100 );
			} else {
				throw new Error(
					response?.message || 'Failed to save settings'
				);
			}
		} catch ( error ) {
			toast.error(
				error?.message || __( 'Error saving settings', 'surerank' )
			);
		} finally {
			setIsSaving( false );
		}
	};

	if ( isLoading ) {
		return <AdminLoadingSkeleton />;
	}

	return (
		<PageContentWrapper
			title={ __( 'Site Information', 'surerank' ) }
			description={ __(
				'Configure site information for schema settings',
				'surerank'
			) }
		>
			<div className="p-6 bg-white shadow-sm rounded-xl">
				<div className="flex flex-col gap-6 max-w-4xl">
					<div className="flex flex-wrap gap-6">
						{ filteredFields.map( ( field, index ) => (
							<Fragment key={ field.name }>
								{ renderField(
									field,
									formState[ field.name ],
									handleChangeSelection( field.name ),
									null,
									{
										initialFocus: index === 0,
									}
								) }
							</Fragment>
						) ) }
					</div>

					<div className="flex justify-start pt-4">
						<Button
							variant="primary"
							onClick={ handleSaveChanges }
							loading={ isSaving }
							icon={ getButtonIcon() }
							className={ cn( getSaveButtonClassName() ) }
						>
							{ isSaving
								? __( 'Saving…', 'surerank' )
								: __( 'Save', 'surerank' ) }
						</Button>
					</div>
				</div>
			</div>
		</PageContentWrapper>
	);
};

export default withSuspense( SiteInformationSettings );
