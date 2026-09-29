import PageContentWrapper from '@/apps/admin-components/page-content-wrapper';
import { UpgradeNotice } from '@/global/components/nudges';
import { __ } from '@wordpress/i18n';

/**
 * Breadcrumbs Settings - Pro Feature Placeholder
 * Displays upgrade nudge for the Breadcrumbs feature
 */
const BreadcrumbsSettings = () => {
	return (
		<PageContentWrapper
			title={ __( 'Breadcrumbs', 'surerank' ) }
			description={ __(
				'Add breadcrumb navigation to improve user experience and SEO. Customize separators, home links, and automatically generate JSON-LD schema for search engines.',
				'surerank'
			) }
		>
			<UpgradeNotice
				title={ __(
					'Unlock Breadcrumbs with Full Customization',
					'surerank'
				) }
				plan="starter"
				description={ __(
					'Upgrade to SureRank Pro to enable breadcrumb navigation with full customization options, shortcode support, template tags, and automatic schema markup generation.',
					'surerank'
				) }
				utmMedium="surerank_breadcrumbs"
			/>
		</PageContentWrapper>
	);
};

export default BreadcrumbsSettings;
