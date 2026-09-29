import { Text, Badge, Skeleton } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { cn } from '@Functions/utils';
import { isProActive } from '@/functions/nudges';
import UpgradeButton from './upgrade-button';

/**
 * UpgradeFeatureCard - A reusable component for showcasing Pro features
 * Based on Figma design: card component (Node ID: 25792-157529)
 *
 * @param {Object}   props                  - Component props
 * @param {string}   props.title            - The main title text (default: "Instantly Index Your Content with SureRank")
 * @param {string}   props.subtitle         - The subtitle text (default: "Get your new and updated pages discovered faster...")
 * @param {string[]} props.description      - Array of feature description strings (each will be a bullet point)
 * @param {string}   props.buttonLabel      - The button label text (default: "Upgrade Now")
 * @param {Function} props.onButtonClick    - Optional button click handler
 * @param {string}   props.className        - Additional CSS classes
 * @param {*}        props.headerContent    - Optional custom header content/image
 * @param {string}   props.imageName        - Image filename (will be combined with assets URL)
 * @param {boolean}  props.showHeader       - Whether to show the header section (default: true)
 * @param {string}   props.headerBackground - Header background color class (default: brand-background-50)
 * @param {string}   props.plan             - Plan name (default: 'starter')
 * @param {string}   props.utmMedium        - UTM medium parameter for tracking (e.g., 'surerank_instant_indexing')
 * @param {string}   props.utmContent       - UTM content parameter for tracking
 * @return {JSX.Element} UpgradeFeatureCard component
 */
const UpgradeFeatureCard = ( {
	title = __( 'Instantly Index Your Content with SureRank', 'surerank' ),
	subtitle = __(
		'Get your new and updated pages discovered faster with Instant Indexing in SureRank Pro.',
		'surerank'
	),
	description = [
		__( 'Instantly notify Google of new or updated pages', 'surerank' ),
		__( 'Improve visibility and keep search results fresh', 'surerank' ),
		__( 'Automate indexing for posts, products, and pages', 'surerank' ),
	],
	buttonLabel = __( 'Upgrade Now', 'surerank' ),
	onButtonClick,
	className = '',
	headerContent = null,
	imageName = 'upgrade-indexNow.svg',
	showHeader = true,
	headerBackground = 'bg-brand-background-50',
	utmMedium,
	utmContent,
	plan,
	...props
} ) => {
	const [ isImageLoaded, setIsImageLoaded ] = useState( false );
	const imageRef = useRef( null );

	// Generate image URL if imageName is provided
	const imageUrl =
		imageName && window?.surerank_globals?.admin_assets_url
			? `${ window.surerank_globals.admin_assets_url }/images/${ imageName }`
			: null;

	useEffect( () => {
		if ( imageRef.current?.complete ) {
			setIsImageLoaded( true );
		}
	}, [ imageUrl ] );

	// Don't render if the required plan is already active
	if ( plan && isProActive( plan ) ) {
		return null;
	}

	return (
		<div
			className={ cn(
				'flex flex-col gap-2 p-4 bg-background-primary border-0.5 border-solid border-border-subtle rounded-xl shadow-sm',
				className
			) }
			role="article"
			{ ...props }
		>
			{ /* Wrapper */ }
			<div className="flex flex-row flex-wrap gap-2 p-2 bg-background-secondary rounded-lg">
				{ /* Modal Card */ }
				<div className="flex flex-col gap-6 p-6 bg-background-primary rounded-md shadow-sm w-full sm:flex-row">
					{ /* Header Section */ }
					{ showHeader && (
						<div className="flex flex-col gap-2 p-2">
							<div
								className={ cn(
									'h-64 w-full rounded flex items-center justify-center flex-shrink-0 sm:w-64',
									headerBackground
								) }
							>
								{ headerContent ||
									( imageUrl ? (
										<>
											{ ! isImageLoaded && (
												<Skeleton
													variant="rectangular"
													className="h-full w-full"
													aria-label={ __(
														'Loading feature image',
														'surerank'
													) }
												/>
											) }
											<img
												ref={ imageRef }
												src={ imageUrl }
												alt={ title }
												className={ cn(
													'h-full w-full object-contain',
													! isImageLoaded && 'hidden'
												) }
												onLoad={ () =>
													setIsImageLoaded( true )
												}
											/>
										</>
									) : (
										<div className="text-center text-text-tertiary">
											{ /* Placeholder for header content */ }
										</div>
									) ) }
							</div>
						</div>
					) }

					{ /* Content Section */ }
					<div className="flex flex-col justify-center gap-2 flex-1 min-w-0">
						{ /* Title Section */ }
						<div className="flex flex-col gap-2">
							<div className="flex flex-row items-center gap-3">
								<Text
									size={ 20 }
									weight={ 600 }
									color="primary"
									lineHeight={ 30 }
									className="tracking-tight"
								>
									{ title }
								</Text>
								<Badge
									label={ __( 'Pro', 'surerank' ) }
									variant="blue"
									size="sm"
									type="pill"
								/>
							</div>
							<div className="flex flex-row items-stretch">
								<Text
									size={ 16 }
									weight={ 400 }
									color="secondary"
									lineHeight={ 24 }
								>
									{ subtitle }
								</Text>
							</div>
						</div>

						{ /* Description */ }
						<div>
							{ Array.isArray( description ) ? (
								<ul className="list-disc list-inside space-y-2 text-text-secondary ml-4">
									{ description.map( ( item, index ) => (
										<li key={ index }>
											<Text
												size={ 16 }
												weight={ 400 }
												color="secondary"
												lineHeight={ 28 }
												className="inline"
											>
												{ item }
											</Text>
										</li>
									) ) }
								</ul>
							) : (
								<Text
									size={ 16 }
									weight={ 400 }
									color="secondary"
									lineHeight={ 28 }
									className="whitespace-pre-line"
								>
									{ description }
								</Text>
							) }
						</div>

						{ /* Footer Actions */ }
						<div className="flex flex-row justify-end items-center gap-3 p-2">
							<div className="flex flex-row items-center gap-3 w-full">
								<UpgradeButton
									label={ buttonLabel }
									variant="primary"
									size="md"
									showIcon={ false }
									onClick={ onButtonClick }
									utmMedium={ utmMedium }
									utmContent={ utmContent }
								/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default UpgradeFeatureCard;
