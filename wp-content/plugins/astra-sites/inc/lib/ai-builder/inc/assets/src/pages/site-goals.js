import { useState, useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_KEY } from '../store';
import Container from '../components/container';
import Heading from '../components/heading';
import Divider from '../components/divider';
import NavigationButtons from '../components/navigation-buttons';
import TextArea from '../components/textarea';
import Tooltip from '../components/tooltip';
import { useNavigateSteps } from '../router';
import { classNames } from '../helpers';
import {
	ShoppingCartIcon,
	HeartIcon,
	Squares2X2Icon,
	CalendarIcon,
	EnvelopeIcon,
	ArrowTrendingUpIcon,
	EllipsisHorizontalIcon,
	BriefcaseIcon,
	CheckIcon,
} from '@heroicons/react/24/outline';

const goalsList = [
	{
		id: 'sell-products',
		title: __( 'Sell Products', 'ai-builder' ),
		icon: ShoppingCartIcon,
		tooltip: __(
			'For online stores or product catalogs. Helps us set up the right shop layout and e-commerce tools.',
			'ai-builder'
		),
	},
	{
		id: 'collect-donations',
		title: __( 'Collect Donations', 'ai-builder' ),
		icon: HeartIcon,
		tooltip: __(
			'For fundraising or charity pages. Guides us to generate persuasive copy and enable donation features.',
			'ai-builder'
		),
	},
	{
		id: 'offer-services',
		title: __( 'Offer Services', 'ai-builder' ),
		icon: BriefcaseIcon,
		tooltip: __(
			'For service-based businesses. Helps create service pages and booking functionality.',
			'ai-builder'
		),
	},
	{
		id: 'generate-leads',
		title: __( 'Generate Leads', 'ai-builder' ),
		icon: EnvelopeIcon,
		tooltip: __(
			'For businesses that want inquiries or form submissions. Optimizes copy and lead-capture tools.',
			'ai-builder'
		),
	},
	{
		id: 'promote-events',
		title: __( 'Promote Events', 'ai-builder' ),
		icon: CalendarIcon,
		tooltip: __(
			'For workshops, launches, or meetups. Helps us create event pages and promotional messaging.',
			'ai-builder'
		),
	},
	{
		id: 'publish-blogs',
		title: __( 'Publish Blogs', 'ai-builder' ),
		icon: Squares2X2Icon,
		tooltip: __(
			'For educational or content-driven sites. Shapes long-form copy and blog structure.',
			'ai-builder'
		),
	},
	{
		id: 'build-brand-trust',
		title: __( 'Build Brand Trust', 'ai-builder' ),
		icon: ArrowTrendingUpIcon,
		tooltip: __(
			'For awareness-focused sites that highlight credibility. Improves tone, testimonials, and trust-driven copy.',
			'ai-builder'
		),
	},
	{
		id: 'others',
		title: __( 'Others', 'ai-builder' ),
		icon: EllipsisHorizontalIcon,
		tooltip: '',
	},
];

const GoalCard = ( { id, title, Icon, enabled, onClick, tooltip } ) => {
	return (
		<Tooltip
			className="!w-48 text-center zw-tooltip__classic"
			content={ tooltip }
		>
			<div className="relative">
				<div
					onClick={ () => onClick( id ) }
					className={ classNames(
						'w-full h-[120px] rounded-lg flex flex-col items-center justify-center shadow-sm cursor-pointer transition-all duration-150',
						'border flex flex-col text-center items-center justify-center gap-2 rounded-md',
						'cursor-pointer hover:bg-background-secondary hover:border-accent-st relative',
						enabled
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
							'inline-flex absolute top-2 right-2 p-[0.15rem] border border-solid rounded',
							enabled
								? 'border-accent-st bg-accent-st'
								: 'border-zip-app-inactive-icon'
						) }
					>
						<CheckIcon
							className="w-2.5 h-2.5 text-white"
							strokeWidth={ 4 }
						/>
					</span>

					<Icon className="w-9 h-9 text-accent-st" />
					<p className="mt-2 text-sm font-semibold text-center text-heading-text">
						{ title }
					</p>
				</div>
			</div>
		</Tooltip>
	);
};

const SiteGoals = () => {
	const { nextStep, previousStep } = useNavigateSteps();
	const { setSiteGoalsAIStep } = useDispatch( STORE_KEY );

	const { siteGoals, siteGoalsOther } = useSelect( ( select ) => {
		const { getSiteGoals } = select( STORE_KEY );
		return getSiteGoals();
	}, [] );

	const [ selectedGoals, setSelectedGoals ] = useState( siteGoals || [] );
	const [ otherNote, setOtherNote ] = useState( siteGoalsOther || '' );

	useEffect( () => {
		setSelectedGoals( siteGoals || [] );
		setOtherNote( siteGoalsOther || '' );
	}, [ siteGoals, siteGoalsOther ] );

	const toggleGoal = useCallback(
		( id ) => {
			const exists = selectedGoals.includes( id );
			const next = exists
				? selectedGoals.filter( ( g ) => g !== id )
				: [ ...selectedGoals, id ];
			setSelectedGoals( next );
		},
		[ selectedGoals ]
	);

	const handleSave = () => {
		setSiteGoalsAIStep( {
			siteGoals: selectedGoals,
			siteGoalsOther: otherNote,
		} );
	};

	const handleClickContinue = () => {
		handleSave();
		nextStep();
	};

	const handleClickPrevious = () => {
		handleSave();
		previousStep();
	};

	return (
		<Container>
			<Heading
				heading={ __(
					'What are the goals of this site?',
					'ai-builder'
				) }
				subHeading={ __(
					'Select all that apply. This helps personalize content for your site.',
					'ai-builder'
				) }
			/>

			<div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-8">
				{ goalsList.map( ( goal ) => (
					<GoalCard
						key={ goal.id }
						id={ goal.id }
						title={ goal.title }
						Icon={ goal.icon }
						enabled={ selectedGoals.includes( goal.id ) }
						onClick={ toggleGoal }
						tooltip={ goal.tooltip }
					/>
				) ) }
			</div>

			{ selectedGoals.includes( 'others' ) && (
				<div className="mt-6">
					<label className="text-sm font-semibold text-heading-text">
						{ __( 'Any additional goals', 'ai-builder' ) }
					</label>
					<TextArea
						value={ otherNote }
						onChange={ ( e ) => setOtherNote( e.target.value ) }
						placeholder={ __(
							'Tell us more (Optional)',
							'ai-builder'
						) }
						textAreaClassName="min-h-[90px] font-normal text-sm leading-6 placeholder:font-normal placeholder:text-sm placeholder:leading-6"
						className="mt-3"
					/>
				</div>
			) }

			<Divider />

			<NavigationButtons
				onClickContinue={ handleClickContinue }
				onClickPrevious={ handleClickPrevious }
			/>
		</Container>
	);
};

export default SiteGoals;
