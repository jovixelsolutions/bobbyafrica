import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { Button, Container, Label, Title } from '@bsf/force-ui';
import { Check, ChevronRight, X } from 'lucide-react';
import { useNavigateStep } from '@Onboarding/hooks';

const features = [
	__( 'Identify and fix SEO issues effortlessly', 'surerank' ),
	__(
		"Analyze and track website's performance in search engines",
		'surerank'
	),
	__( 'Optimize website for better rankings', 'surerank' ),
	__( 'Use AI to optimize your website', 'surerank' ),
	__( 'Enjoy an easy, simple setup', 'surerank' ),
];

const Welcome = () => {
	const { nextStep } = useNavigateStep();
	const [ isVideoOpen, setIsVideoOpen ] = useState( false );

	useEffect( () => {
		const handleKeyDown = ( e ) => {
			if ( e.key === 'Escape' ) {
				setIsVideoOpen( false );
			}
		};
		window.addEventListener( 'keydown', handleKeyDown );
		return () => window.removeEventListener( 'keydown', handleKeyDown );
	}, [] );

	const handleSubmit = ( event ) => {
		event.preventDefault();
	};

	return (
		<form className="flex flex-col gap-4" onSubmit={ handleSubmit }>
			<Container className="p-1 gap-1.5" direction="column">
				<Title
					tag="h2"
					title={ __( 'Welcome to SureRank!', 'surerank' ) }
					size="lg"
					className="[&>h2]:text-3xl [&>h2]:leading-9.5"
				/>
				<Label tag="p" className="text-base">
					{ __(
						'Set up your site’s SEO easily-no advanced skills needed!',
						'surerank'
					) }
				</Label>
			</Container>
			{ /* Inline muted autoplay video */ }
			<div className="p-1">
				<div
					className="relative w-full cursor-pointer"
					style={ { paddingBottom: '56.25%' } }
					onClick={ () => setIsVideoOpen( true ) }
				>
					<iframe
						className="absolute top-0 left-0 w-full h-full rounded-md pointer-events-none"
						src="https://www.youtube-nocookie.com/embed/GEeTu2D74Z8?autoplay=1&mute=1&loop=1&playlist=GEeTu2D74Z8"
						title={ __(
							'SureRank Introduction Video',
							'surerank'
						) }
						allow="accelerometer; autoplay; encrypted-media"
						tabIndex="-1"
					/>
				</div>
			</div>
			{ /* Video popup */ }
			{ isVideoOpen && (
				<div
					className="fixed inset-0 flex items-center justify-center bg-black/70 cursor-pointer z-[9999]"
					onClick={ () => setIsVideoOpen( false ) }
				>
					<div className="flex absolute top-10 right-8 text-white cursor-pointer hover:bg-white/10 p-2 rounded-full transition-colors">
						<X
							size={ 24 }
							onClick={ ( event ) => {
								event.stopPropagation();
								setIsVideoOpen( false );
							} }
						/>
					</div>
					<div
						className="relative rounded-lg shadow-2xl cursor-default w-full max-w-4xl aspect-video mx-4"
						onClick={ ( event ) => event.stopPropagation() }
					>
						<iframe
							className="w-full h-full rounded-lg"
							src="https://www.youtube-nocookie.com/embed/GEeTu2D74Z8?autoplay=1&start=0"
							title={ __(
								'SureRank Introduction Video',
								'surerank'
							) }
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
							allowFullScreen
						/>
					</div>
				</div>
			) }
			{ /* Feature list */ }
			<ul
				className="space-y-1.5 p-1"
				aria-label={ __( 'List of features', 'surerank' ) }
			>
				{ features.map( ( feature ) => (
					<li
						key={ feature }
						className="flex items-center gap-2"
						aria-label={ feature }
					>
						<Check
							className="size-3 text-icon-primary"
							aria-hidden="true"
						/>
						<Label
							size="sm"
							tag="p"
							className="font-medium text-field-label"
						>
							{ feature }
						</Label>
					</li>
				) ) }
			</ul>
			<hr className="border-t border-b-0 border-x-0 border-solid border-border-subtle m-1" />
			<div className="p-1">
				<Button
					variant="primary"
					size="md"
					icon={ <ChevronRight /> }
					iconPosition="right"
					onClick={ () => nextStep() }
					className="w-fit mr-auto"
				>
					{ __( "Let's Get Started", 'surerank' ) }
				</Button>
			</div>
		</form>
	);
};

export default Welcome;
