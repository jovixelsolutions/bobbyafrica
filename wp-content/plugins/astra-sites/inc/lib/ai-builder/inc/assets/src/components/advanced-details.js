import { useState, useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	ChevronUpIcon,
	ChevronDownIcon,
	BriefcaseIcon,
	FaceSmileIcon,
	InformationCircleIcon,
	SparklesIcon,
	ChatBubbleLeftRightIcon,
	MegaphoneIcon,
	ShoppingBagIcon,
	CheckIcon,
} from '@heroicons/react/24/outline';
import { STORE_KEY } from '../store';
import TagsInput from './tags-input';
import Tooltip from './tooltip';
import { classNames } from '../helpers';

const tones = [
	{
		id: 'balanced',
		title: __( 'Balanced', 'ai-builder' ),
		icon: SparklesIcon,
		tooltip: __(
			'Default: Balanced tone suitable for most websites. A mix of clarity, engagement, and structure.',
			'ai-builder'
		),
	},
	{
		id: 'professional',
		title: __( 'Professional', 'ai-builder' ),
		icon: BriefcaseIcon,
		tooltip: __(
			'Clear, formal, and business-appropriate. Ideal for corporate and service brands.',
			'ai-builder'
		),
	},
	{
		id: 'friendly',
		title: __( 'Friendly', 'ai-builder' ),
		icon: FaceSmileIcon,
		tooltip: __(
			'Warm and approachable language. Works well for lifestyle and local services.',
			'ai-builder'
		),
	},
	{
		id: 'informative',
		title: __( 'Informative', 'ai-builder' ),
		icon: InformationCircleIcon,
		tooltip: __(
			'Fact-driven and structured copy. Best for product education and detailed pages.',
			'ai-builder'
		),
	},
	{
		id: 'engaging',
		title: __( 'Engaging', 'ai-builder' ),
		icon: SparklesIcon,
		tooltip: __(
			'Attention-holding and expressive writing. Good for brands that want stronger interaction.',
			'ai-builder'
		),
	},
	{
		id: 'conversational',
		title: __( 'Conversational', 'ai-builder' ),
		icon: ChatBubbleLeftRightIcon,
		tooltip: __(
			'Natural, casual phrasing like real speech. Makes copy feel more human.',
			'ai-builder'
		),
	},
	{
		id: 'persuasive',
		title: __( 'Persuasive', 'ai-builder' ),
		icon: MegaphoneIcon,
		tooltip: __(
			'Influence-driven writing that motivates action. Strong for decisions and signups.',
			'ai-builder'
		),
	},
	{
		id: 'sales-focused',
		title: __( 'Sales-focused', 'ai-builder' ),
		icon: ShoppingBagIcon,
		tooltip: __(
			'Benefit-heavy, conversion-oriented copy. Designed to support revenue pages.',
			'ai-builder'
		),
	},
	{
		id: 'humorous',
		title: __( 'Humorous', 'ai-builder' ),
		icon: FaceSmileIcon,
		tooltip: __(
			'Light-hearted copy with playful elements. Best for casual brands and social campaigns.',
			'ai-builder'
		),
	},
];

const ToneCard = ( { id, title, Icon, selected, onClick, tooltip } ) => {
	return (
		<Tooltip className="!w-48 text-center" content={ tooltip }>
			<div className="relative">
				<div
					onClick={ () => onClick( id ) }
					className={ classNames(
						'w-full h-[120px] rounded-lg flex flex-col items-center justify-center shadow-sm cursor-pointer transition-all duration-150',
						'border flex flex-col text-center items-center justify-center gap-2 rounded-md',
						'cursor-pointer hover:bg-background-secondary hover:border-accent-st relative',
						selected
							? 'border-accent-st bg-background-secondary'
							: 'border-border-primary'
					) }
					role="button"
					tabIndex={ 0 }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' || e.key === ' ' ) {
							onClick( id );
						}
					} }
				>
					<span
						className={ classNames(
							'inline-flex absolute top-2 right-2 p-[0.15rem] border border-solid rounded-full',
							selected
								? 'border-accent-st bg-accent-st'
								: 'border-zip-app-inactive-icon'
						) }
					>
						<CheckIcon
							className="w-2.5 h-2.5 text-white"
							strokeWidth={ 4 }
						/>
					</span>

					<Icon
						className="w-8 h-8 text-accent-st"
						strokeWidth={ 1.5 }
					/>
					<p className="mt-2 text-sm font-semibold text-center text-heading-text">
						{ title }
					</p>
				</div>
			</div>
		</Tooltip>
	);
};

const AdvancedDetails = ( {
	imageKeywords = [],
	debouncedDescriptionValue,
	currentDescriptionValue,
	description,
	fetchKeywords,
} ) => {
	const [ showAdvancedOptions, setShowAdvancedOptions ] = useState( false );

	const { setSiteToneAIStep, setUserKeywordsAIStep } =
		useDispatch( STORE_KEY );

	const { siteTone, userKeywords } = useSelect( ( select ) => {
		const { getSiteTone, getUserKeywords } = select( STORE_KEY );
		return {
			siteTone: getSiteTone(),
			userKeywords: getUserKeywords(),
		};
	}, [] );

	const [ keywords, setKeywords ] = useState( userKeywords || [] );
	const [ selectedTone, setSelectedTone ] = useState(
		siteTone || 'balanced'
	);

	// Fetches keywords on content change by keystroke or by improved content insertion from AI.
	useEffect( () => {
		if ( debouncedDescriptionValue !== undefined ) {
			if (
				showAdvancedOptions &&
				( ! imageKeywords || imageKeywords.length < 3 )
			) {
				if (
					( debouncedDescriptionValue || description ) &&
					( debouncedDescriptionValue || description ).trim() !== ''
				) {
					fetchKeywords( debouncedDescriptionValue );
				}
			}
		}
	}, [ debouncedDescriptionValue, showAdvancedOptions ] );

	// Fetches keywords advanced option dropdown opens.
	useEffect( () => {
		if (
			showAdvancedOptions &&
			( ! imageKeywords || ! imageKeywords.length )
		) {
			if (
				( currentDescriptionValue || description ) &&
				( currentDescriptionValue || description ).trim() !== ''
			) {
				fetchKeywords( currentDescriptionValue || description );
			}
		}
	}, [ showAdvancedOptions ] );

	const handleKeywordsChange = ( newTags ) => {
		setKeywords( newTags );
		setUserKeywordsAIStep( newTags );
	};

	const addSuggestedKeyword = useCallback(
		( k ) => {
			if ( ! k ) {
				return;
			}
			if ( keywords.includes( k ) ) {
				return;
			}
			if ( keywords.length >= 10 ) {
				return;
			}

			const next = [ ...keywords, k ];
			setKeywords( next );
			setUserKeywordsAIStep( next );
		},
		[ keywords ]
	);

	const toggleTone = ( id ) => {
		setSelectedTone( id );
		setSiteToneAIStep( id );
	};

	return (
		<div className="mt-4">
			<div className="flex justify-start mt-2">
				<span
					className="cursor-pointer flex items-center gap-1 text-heading-text hover:text-accent-st"
					onClick={ () =>
						setShowAdvancedOptions( ! showAdvancedOptions )
					}
					role="button"
					tabIndex={ 0 }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' || e.key === ' ' ) {
							setShowAdvancedOptions( ! showAdvancedOptions );
						}
					} }
				>
					<p className="text-sm font-semibold">
						{ __( 'Advanced', 'ai-builder' ) }
					</p>
					{ showAdvancedOptions ? (
						<ChevronUpIcon className="w-4 h-4" />
					) : (
						<ChevronDownIcon className="w-4 h-4" />
					) }
				</span>
			</div>

			<div
				className={ classNames(
					'space-y-6 transition-all duration-200 overflow-hidden',
					showAdvancedOptions ? 'max-h-[1000px] mt-3' : 'max-h-0'
				) }
			>
				<div>
					<label className="font-semibold text-sm text-heading-text">
						{ __( 'Add Some Keywords', 'ai-builder' ) }
					</label>
					<TagsInput
						value={ keywords }
						onChange={ handleKeywordsChange }
						placeholder={ __( 'Add keywords', 'ai-builder' ) }
						className="mt-3 !ring-0 border border-border-primary rounded-lg"
						maxTokens={ 10 }
					/>

					{ imageKeywords?.filter( ( k ) => ! keywords.includes( k ) )
						.length ? (
						<>
							<div className="mt-3 text-sm text-body-text">
								{ __( 'Suggested Keywords', 'ai-builder' ) }
							</div>
							<div className="flex gap-2 flex-wrap mt-2">
								{ imageKeywords
									.filter( ( k ) => ! keywords.includes( k ) )
									.map( ( k, idx ) => (
										<button
											key={ k + idx }
											type="button"
											onClick={ () =>
												addSuggestedKeyword( k )
											}
											className="px-3 py-1.5 rounded-full border border-border-primary text-sm text-body-text hover:bg-background-secondary hover:border-accent-st"
										>
											{ k }
										</button>
									) ) }
							</div>
						</>
					) : null }

					<div className="mt-2 text-sm text-secondary-text">
						{ __(
							'Separate with commas or the Enter key. Enter between 5 and 10 keywords',
							'ai-builder'
						) }
					</div>
				</div>

				<div>
					<label className="font-semibold text-sm text-heading-text">
						{ __( 'Choose Your Tone', 'ai-builder' ) }
					</label>
					<div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-3">
						{ tones.map( ( t ) => (
							<ToneCard
								key={ t.id }
								id={ t.id }
								title={ t.title }
								Icon={ t.icon }
								selected={ selectedTone === t.id }
								onClick={ toggleTone }
								tooltip={ t.tooltip }
							/>
						) ) }
					</div>
				</div>
			</div>
		</div>
	);
};

export default AdvancedDetails;
