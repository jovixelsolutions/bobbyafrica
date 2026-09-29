import { __ } from '@wordpress/i18n';
import { Button, Tooltip, Text } from '@bsf/force-ui';
import { Sparkles } from 'lucide-react';
import { cn } from '@/functions/utils';

const IMPROVE_WITH_AI_TEXT = __( 'Improve with AI', 'surerank' );
const IMPROVING_TEXT = __( 'Improving…', 'surerank' );

/**
 * Reusable "Improve with AI" button component
 * Renders different button states based on authentication and content status
 *
 * @param {Object}   props                 - Component props
 * @param {boolean}  props.isAuthenticated - Whether user is authenticated with AI provider
 * @param {boolean}  props.isConnecting    - Whether connection is in progress
 * @param {boolean}  props.hasMinimumWords - Whether content meets minimum word requirement
 * @param {boolean}  props.isImproving     - Whether improvement is in progress
 * @param {Function} props.onImprove       - Callback when improve button is clicked
 * @param {Function} props.onConnect       - Callback when connect button is clicked
 * @return {JSX.Element|null} The button component or null
 */
export const ImproveWithAiButton = ( {
	isAuthenticated,
	isConnecting,
	hasMinimumWords,
	isImproving,
	onImprove,
	onConnect,
} ) => {
	// Authenticated + Has Minimum Words (Active State)
	if ( isAuthenticated && hasMinimumWords ) {
		return (
			<Button
				variant="outline"
				size="xs"
				icon={
					<Sparkles
						className={ cn( isImproving && 'animate-pulse' ) }
					/>
				}
				iconPosition="left"
				onClick={ onImprove }
				className={ cn(
					'text-background-brand',
					isImproving && 'cursor-not-allowed'
				) }
			>
				{ isImproving ? IMPROVING_TEXT : IMPROVE_WITH_AI_TEXT }
			</Button>
		);
	}

	// Authenticated + Below Minimum Words (Disabled with Tooltip)
	if ( isAuthenticated && ! hasMinimumWords ) {
		return (
			<Tooltip
				variant="dark"
				placement="top-end"
				title={ __( 'Minimum word count required', 'surerank' ) }
				content={
					<Text
						size={ 12 }
						weight={ 400 }
						color="inverse"
						className="leading-relaxed"
					>
						{ __(
							'Please add at least 5 words to your description before improving.',
							'surerank'
						) }
					</Text>
				}
				triggers={ [ 'hover' ] }
				tooltipPortalId="surerank-root"
				arrow={ true }
			>
				<Button
					variant="outline"
					size="xs"
					icon={ <Sparkles /> }
					iconPosition="left"
					className="text-icon-secondary cursor-not-allowed"
					onClick={ ( e ) => e.preventDefault() }
				>
					{ IMPROVE_WITH_AI_TEXT }
				</Button>
			</Tooltip>
		);
	}

	// Not Authenticated (Connect Prompt with Interactive Tooltip)
	if ( ! isAuthenticated ) {
		return (
			<Tooltip
				variant="dark"
				placement="top-end"
				title={ __( 'Connect with AI to improve this', 'surerank' ) }
				content={
					<div className="space-y-1">
						<Text
							size={ 12 }
							weight={ 400 }
							color="inverse"
							className="leading-relaxed"
						>
							{ __(
								"To generate better content with AI, you'll need to connect your AI provider first. It only takes a minute and unlocks all AI-powered features.",
								'surerank'
							) }
						</Text>
						<div className="mt-1.5">
							<Button
								size="xs"
								variant="link"
								onClick={ onConnect }
								disabled={ isConnecting }
								className="[&>span]:px-0 no-underline hover:no-underline focus:[box-shadow:none] text-link-visited-inverse hover:text-link-visited-inverse-hover"
							>
								{ isConnecting
									? __( 'Connecting…', 'surerank' )
									: __( 'Connect', 'surerank' ) }
							</Button>
						</div>
					</div>
				}
				triggers={ [ 'hover' ] }
				interactive={ true }
				tooltipPortalId="surerank-root"
				arrow={ true }
			>
				<Button
					variant="outline"
					size="xs"
					icon={ <Sparkles /> }
					iconPosition="left"
					className={
						hasMinimumWords
							? 'text-background-brand'
							: 'text-icon-secondary'
					}
				>
					{ IMPROVE_WITH_AI_TEXT }
				</Button>
			</Tooltip>
		);
	}

	return null;
};
