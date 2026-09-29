import { __ } from '@wordpress/i18n';
import { Button, Text } from '@bsf/force-ui';
import { Image } from 'lucide-react';

const FEATURES = [
	__( 'Auto-generate OG images for every page', 'surerank' ),
	__( 'Ready-to-use professional templates', 'surerank' ),
	__( 'Custom branding with logo & colors', 'surerank' ),
];

const UnauthenticatedScreen = ( { onConnect } ) => {
	return (
		<div className="bg-white border border-border-subtle rounded-xl shadow-sm p-4">
			<div className="bg-background-secondary rounded-lg p-2 w-full">
				<div className="bg-white rounded-md shadow-sm p-6 flex gap-6 h-[338px] overflow-hidden">
					<div className="flex flex-1 flex-col gap-2 items-start justify-center self-stretch">
						<div className="flex flex-col gap-2 w-full">
							<div className="bg-brand-background-50 p-2 rounded inline-flex w-fit">
								<Image
									className="size-6 text-brand-primary-600"
									strokeWidth={ 1.2 }
								/>
							</div>
							<Text
								size={ 20 }
								weight="600"
								color="primary"
								className="leading-[30px] tracking-[-0.1px]"
							>
								{ __(
									'Generate stunning social preview images automatically',
									'surerank'
								) }
							</Text>
							<Text
								size={ 16 }
								weight="400"
								color="secondary"
								className="leading-6"
							>
								{ __(
									'Create beautiful Open Graph images for your pages with AI-powered templates. Customize layouts, colors, and branding in just few clicks.',
									'surerank'
								) }
							</Text>
						</div>
						<ul className="list-disc pl-6 text-text-secondary">
							{ FEATURES.map( ( feature ) => (
								<li key={ feature }>
									<Text
										as="span"
										size={ 16 }
										weight="400"
										color="secondary"
										className="leading-7"
									>
										{ feature }
									</Text>
								</li>
							) ) }
						</ul>
						<div className="p-2 w-full">
							<Button
								variant="primary"
								size="md"
								onClick={ onConnect }
							>
								{ __( 'Connect Account', 'surerank' ) }
							</Button>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default UnauthenticatedScreen;
