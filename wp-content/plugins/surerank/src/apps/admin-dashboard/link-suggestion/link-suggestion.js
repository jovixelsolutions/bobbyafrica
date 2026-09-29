import UpgradeFeatureCard from '@/global/components/nudges/upgrade-feature-card';
import { __ } from '@wordpress/i18n';

/**
 * Link Suggestion - Pro Feature Placeholder
 * Displays upgrade nudge for the Link Suggestion feature
 */
const LinkSuggestion = () => {
	return (
		<div className="mx-8 py-8">
			<UpgradeFeatureCard
				title={ __( 'AI-Powered Link Suggestions', 'surerank' ) }
				subtitle={ __(
					'Discover relevant internal linking opportunities automatically. Our AI analyzes your content to suggest the most contextually appropriate links.',
					'surerank'
				) }
				description={ [
					__(
						'AI-powered content similarity analysis using embeddings',
						'surerank'
					),
					__(
						'Automatically find related posts and pages for internal linking',
						'surerank'
					),
					__(
						'Optimize your site structure and improve SEO with smart link suggestions',
						'surerank'
					),
					__(
						'Configure which post types and statuses to include',
						'surerank'
					),
				] }
				imageName="upgrade-link-suggestions.svg"
				utmMedium="surerank_link_suggestion"
				plan="pro"
			/>
		</div>
	);
};

export default LinkSuggestion;
