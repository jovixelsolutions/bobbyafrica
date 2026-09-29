import PageContentWrapper from '@AdminComponents/page-content-wrapper';
import { __ } from '@wordpress/i18n';
import withSuspense from '@AdminComponents/hoc/with-suspense';
import GeneratePageContent from '@Functions/page-content-generator';
import { createLazyRoute } from '@tanstack/react-router';
import { Text } from '@bsf/force-ui';

export const PAGE_CONTENT = [
	{
		container: {
			id: 'miscellaneous-container',
			direction: 'column',
			gap: 6,
		},
		content: [
			{
				container: null,
				content: [
					{
						type: 'switch',
						id: 'surerank_usage_optin',
						storeKey: 'surerank_usage_optin',
						dataType: 'boolean',
						label: __( 'Contribute to SureRank', 'surerank' ),
						description: (
							<span>
								<span>
									{ __(
										'Help shape the future of SureRank. Share how you use the plugin so we can build features that matter, fix issues faster, and make smarter decisions.',
										'surerank'
									) }
								</span>
								<a
									href="https://surerank.com/share-usage-data/"
									target="_blank"
									rel="noopener noreferrer"
									className="no-underline hover:no-underline ring-0"
								>
									<Text
										as="span"
										color="link"
										className="inline underline-offset-2 hover:underline"
									>
										{ __( 'Learn More', 'surerank' ) }
									</Text>
								</a>
							</span>
						),
					},
				],
			},
		],
	},
];

const Miscellaneous = () => {
	return (
		<PageContentWrapper
			title={ __( 'Miscellaneous', 'surerank' ) }
			description={ __(
				'Additional settings and preferences for SureRank.',
				'surerank'
			) }
		>
			<GeneratePageContent json={ PAGE_CONTENT } />
		</PageContentWrapper>
	);
};

export const LazyRoute = createLazyRoute( '/tools/miscellaneous' )( {
	component: withSuspense( Miscellaneous ),
} );

export default withSuspense( Miscellaneous );
