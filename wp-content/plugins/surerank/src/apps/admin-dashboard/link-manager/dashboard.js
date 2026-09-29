import UpgradeFeatureCard from '@/global/components/nudges/upgrade-feature-card';
import { __ } from '@wordpress/i18n';

/**
 * Link Manager Dashboard - Pro Feature Placeholder
 * Displays upgrade nudge for the Link Manager feature
 */
const LinkManagerDashboard = () => {
	return (
		<div className="mx-8 py-8">
			<UpgradeFeatureCard
				title={ __( 'Link Manager', 'surerank' ) }
				subtitle={ __(
					'Monitor and manage all internal and external links across your website. Track link health, broken links, and anchor texts.',
					'surerank'
				) }
				description={ [
					__(
						'Track internal and external links for all pages',
						'surerank'
					),
					__(
						'Monitor HTTP status codes and detect broken links',
						'surerank'
					),
					__(
						'Analyze anchor text distribution and optimization',
						'surerank'
					),
				] }
				imageName="upgrade-link-manager.svg"
				utmMedium="surerank_link_manager_dashboard"
				plan="pro"
			/>
		</div>
	);
};

export default LinkManagerDashboard;
