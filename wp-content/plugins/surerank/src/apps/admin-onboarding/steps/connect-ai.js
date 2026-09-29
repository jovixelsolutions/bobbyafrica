import { __ } from '@wordpress/i18n';
import { Button, Container, Text } from '@bsf/force-ui';
import { Check, ChevronLeft, ChevronRight, CircleCheckBig } from 'lucide-react';
import { useNavigateStep } from '@Onboarding/hooks';
import useOnboardingAuth from '@Onboarding/hooks/use-onboarding-auth';

const connectAIBanner =
	surerank_globals.admin_assets_url +
	'/images/onboarding-connect-ai-banner.svg';

const aiCapabilities = [
	__( 'Craft optimized titles and descriptions automatically', 'surerank' ),
	__( 'Fix page-level and site-wide checks', 'surerank' ),
	__( 'Link suggestions based on page context', 'surerank' ),
];

const ConnectAI = () => {
	const { nextStep, previousStep } = useNavigateStep();
	const { isAuthenticated, isConnecting, handleConnect } =
		useOnboardingAuth();

	const handleNext = () => {
		nextStep();
	};

	const handleSkip = () => {
		nextStep();
	};

	const handleBack = () => {
		previousStep();
	};

	const handleSubmit = ( event ) => {
		event.preventDefault();
	};

	return (
		<form className="flex flex-col gap-4" onSubmit={ handleSubmit }>
			{ /* Header Image */ }
			<div className="p-1">
				<div className="relative rounded-lg overflow-hidden">
					<img
						src={ connectAIBanner }
						alt={ __( 'SureRank AI Banner', 'surerank' ) }
						className="w-full h-auto block"
					/>
				</div>
			</div>

			{ /* Title and Description - left aligned */ }
			<Container className="px-1 pb-1 gap-2" direction="column">
				<Text color="primary" weight={ 600 } size={ 24 }>
					{ __(
						'Meet SureRank AI, Your Personal SEO Copilot',
						'surerank'
					) }
				</Text>

				<Text color="secondary" weight={ 400 } size={ 14 }>
					{ __(
						'SureRank AI helps you identify, understand, and fix the SEO issues that actually matter. It analyzes your pages, understands your content, and generates perfectly balanced meta titles, descriptions, all in one click.',
						'surerank'
					) }
				</Text>
			</Container>

			{ /* Capabilities Section */ }
			<Container className="p-2 gap-2" direction="column">
				<Text color="primary" weight={ 500 } size={ 14 }>
					{ __( "Here's what SureRank AI can do:", 'surerank' ) }
				</Text>
				<ul
					className="space-y-1.5"
					aria-label={ __( 'List of AI capabilities', 'surerank' ) }
				>
					{ aiCapabilities.map( ( capability ) => (
						<li
							key={ capability }
							className="flex items-center gap-1.5"
							aria-label={ capability }
						>
							<Check
								className="size-4 text-icon-interactive flex-shrink-0"
								aria-hidden="true"
							/>
							<Text size={ 14 } color="secondary" weight={ 400 }>
								{ capability }
							</Text>
						</li>
					) ) }
				</ul>
			</Container>

			{ /* Divider */ }
			<hr className="border-t border-b-0 border-x-0 border-solid border-border-subtle my-2" />

			{ /* Action Buttons */ }
			<div className="px-1 flex justify-between items-center gap-6">
				<Button
					variant="outline"
					size="md"
					onClick={ handleBack }
					type="button"
					disabled={ isConnecting }
					icon={ <ChevronLeft /> }
					iconPosition="left"
				>
					{ __( 'Back', 'surerank' ) }
				</Button>
				<div className="flex items-center gap-6">
					{ isAuthenticated ? (
						<>
							<Button
								variant="outline"
								size="md"
								icon={ <CircleCheckBig /> }
								iconPosition="left"
								className="cursor-not-allowed"
							>
								{ __( 'Connected', 'surerank' ) }
							</Button>
							<Button
								variant="primary"
								size="md"
								icon={ <ChevronRight /> }
								iconPosition="right"
								onClick={ handleNext }
							>
								{ __( 'Next', 'surerank' ) }
							</Button>
						</>
					) : (
						<>
							<Button
								variant="ghost"
								size="md"
								onClick={ handleSkip }
								type="button"
								disabled={ isConnecting }
								className="text-text-tertiary hover:text-text-primary"
							>
								{ __( 'Skip', 'surerank' ) }
							</Button>
							<Button
								variant="primary"
								size="md"
								icon={ <ChevronRight /> }
								iconPosition="right"
								onClick={ handleConnect }
								disabled={ isConnecting }
							>
								{ isConnecting
									? __( 'Connecting…', 'surerank' )
									: __( 'Connect', 'surerank' ) }
							</Button>
						</>
					) }
				</div>
			</div>
		</form>
	);
};

export default ConnectAI;
