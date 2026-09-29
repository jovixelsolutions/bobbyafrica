import { __ } from '@wordpress/i18n';
import { Check, ExternalLink } from 'lucide-react';
import { Button, Title, Badge, Text } from '@bsf/force-ui';
import { exitURL } from '@Onboarding/components/exit-button';

const defaultSettings = [
	{
		title: __( 'Metadata Generation', 'surerank' ),
		description: __(
			'Automatically generates SEO meta titles and descriptions for your content.',
			'surerank'
		),
	},
	{
		title: __( 'XML Sitemap Generated', 'surerank' ),
		description: __(
			'An XML sitemap is automatically created and updated to help search engines discover and index your content.',
			'surerank'
		),
	},
	{
		title: __( 'Schema Markup Enabled', 'surerank' ),
		description: __(
			'Structured data is configured to help search engines better understand your content and improve rich snippet visibility.',
			'surerank'
		),
	},
	{
		title: __( 'Open Graph & Twitter Cards', 'surerank' ),
		description: __(
			'Social media meta tags are enabled to ensure your content looks great when shared on Facebook, Twitter, and other platforms.',
			'surerank'
		),
	},
	{
		title: __( 'Auto-Set Image Alt & Title Attributes', 'surerank' ),
		description: __(
			'Automatically adds alt text and title attributes to images that are missing them.',
			'surerank'
		),
	},
];

const Success = () => {
	const handleSubmit = ( event ) => {
		event.preventDefault();
	};

	const handleClick = () => {
		window.open( exitURL, '_self', 'noopener,noreferrer' );
	};

	const handleDocumentation = () => {
		window.open(
			'https://surerank.com/docs/',
			'_blank',
			'noopener,noreferrer'
		);
	};

	return (
		<form
			className="flex flex-col gap-4 px-6 py-6"
			onSubmit={ handleSubmit }
		>
			<div className="flex flex-col gap-2">
				<Title
					tag="h3"
					title={ __( 'Your SEO Engine Is Ready! 🚀', 'surerank' ) }
					size="lg"
					className="text-2xl font-semibold leading-8"
				/>
				<Text color="secondary" weight={ 400 } size={ 14 }>
					{ __(
						"You've successfully set up SureRank. We've enabled the best-recommended SEO settings for you so your site stays optimized from day one.",
						'surerank'
					) }
				</Text>
			</div>

			<div className="flex flex-col gap-2">
				<Text size={ 18 } weight={ 600 } color="primary">
					{ __( 'Default Settings Enabled', 'surerank' ) }
				</Text>
				<div className="flex flex-col gap-0">
					{ defaultSettings.map( ( setting, index ) => (
						<div
							key={ index }
							className="flex flex-col gap-2 px-1 py-3"
						>
							<div className="flex items-start gap-2">
								<Badge
									size="xs"
									variant="green"
									type="pill"
									icon={ <Check className="mt-[0.5px]" /> }
									label=" "
									className="inline-block rounded-full size-5 mt-1"
									closable={ false }
								/>
								<div className="flex-1 flex flex-col gap-1">
									<Text
										size={ 14 }
										weight={ 500 }
										color="secondary"
									>
										{ setting.title }
									</Text>
									<Text
										size={ 12 }
										weight={ 400 }
										color="secondary"
									>
										{ setting.description }
									</Text>
								</div>
							</div>
							{ index < defaultSettings.length - 1 && (
								<div className="px-1">
									<hr className="border-b border-t-0 border-x-0 border-solid border-border-subtle m-0" />
								</div>
							) }
						</div>
					) ) }
				</div>
			</div>

			{ /* Footer */ }
			<div className="p-1">
				<div className="flex justify-start gap-3 flex-col md:flex-row">
					{ /* Action buttons */ }
					<Button
						variant="primary"
						size="md"
						className="w-full md:w-auto"
						onClick={ handleClick }
					>
						{ __( 'Go To Dashboard', 'surerank' ) }
					</Button>
					<Button
						variant="ghost"
						size="md"
						className="w-full md:w-auto"
						icon={ <ExternalLink /> }
						iconPosition="right"
						onClick={ handleDocumentation }
					>
						{ __( 'Documentation', 'surerank' ) }
					</Button>
				</div>
			</div>
		</form>
	);
};

export default Success;
