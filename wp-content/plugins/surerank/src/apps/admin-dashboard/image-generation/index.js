import PageContentWrapper from '@/apps/admin-components/page-content-wrapper';
import { UpgradeNotice } from '@/global/components/nudges';
import { Button, Text } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import { ArrowUpRight } from 'lucide-react';
import { isProActive } from '@/functions/nudges';

const getPluginsUrl = () => {
	const adminUrl = window?.surerank_globals?.wp_dashboard_url || '';
	return adminUrl.replace( /admin\.php$/, 'plugins.php' );
};

/**
 * Image Generation Settings - Pro Feature Placeholder
 * Shown when Pro is not installed (upgrade nudge), or when an outdated Pro
 * is active that hasn't yet shipped the Image Generation feature
 * (backward-compat notice asking the user to update Pro).
 *
 * @since x.x.x
 */
const ImageGenerationUpgrade = () => {
	if ( isProActive( 'pro' ) ) {
		return (
			<PageContentWrapper
				title={ __( 'Image Generation', 'surerank' ) }
				description={ __(
					'Automatically generate Open Graph images for social sharing of your WordPress posts and pages.',
					'surerank'
				) }
			>
				<div
					className="flex flex-row items-stretch gap-2 p-3 rounded-lg border border-solid shadow-sm bg-brand-background-50 border-indigo-300"
					role="banner"
				>
					<div className="flex flex-row items-center gap-2 flex-1">
						<div className="flex flex-col gap-1 flex-1">
							<Text
								size={ 14 }
								weight={ 600 }
								color="primary"
								lineHeight={ 20 }
							>
								{ __(
									'Update SureRank premium version to use Image Generation',
									'surerank'
								) }
							</Text>
							<Text
								size={ 14 }
								weight={ 400 }
								color="secondary"
								lineHeight={ 20 }
							>
								{ __(
									'Your installed SureRank premium version does not include Image Generation. Update SureRank premium version to the latest version to access this feature.',
									'surerank'
								) }
							</Text>
						</div>
						<div className="flex items-center gap-2">
							<Button
								size="md"
								variant="link"
								icon={ <ArrowUpRight className="w-5 h-5" /> }
								iconPosition="right"
								className="no-underline ring-0"
								onClick={ () => {
									window.open(
										getPluginsUrl(),
										'_blank',
										'noopener,noreferrer'
									);
								} }
							>
								{ __( 'Update Plugin', 'surerank' ) }
							</Button>
						</div>
					</div>
				</div>
			</PageContentWrapper>
		);
	}

	return (
		<PageContentWrapper
			title={ __( 'Image Generation', 'surerank' ) }
			description={ __(
				'Automatically generate Open Graph images for social sharing of your WordPress posts and pages.',
				'surerank'
			) }
		>
			<UpgradeNotice
				title={ __( 'Upgrade to unlock Image Generation', 'surerank' ) }
				plan="pro"
				description={ __(
					'Upgrade to SureRank Pro or Business and generate stunning AI-powered Open Graph images with custom branding presets for your posts and pages.',
					'surerank'
				) }
				utmMedium="surerank_og_image_generation"
			/>
		</PageContentWrapper>
	);
};

export default ImageGenerationUpgrade;
