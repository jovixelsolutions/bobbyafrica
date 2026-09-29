/* eslint-disable no-mixed-spaces-and-tabs */
import parse from 'html-react-parser';
import { Title, LineChart, Button } from '@bsf/force-ui';
import {
	InformationCircleIcon,
	XMarkIcon,
	ArrowTrendingUpIcon,
	ArrowTrendingDownIcon,
} from '@heroicons/react/24/outline';
import { __, sprintf } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import DateRange from '@Components/fields/DateRange';
import AppTooltip from '@Components/common/AppTooltip';

const formatXAxis = ( tick ) => {
	// Try to format as 'MMM dd' (e.g., 'Jun 20')
	if ( ! tick ) {
		return '';
	}
	const date = new Date( tick );
	if ( isNaN( date.getTime() ) ) {
		return tick;
	}
	return date.toLocaleDateString( 'en-US', {
		month: 'short',
		day: '2-digit',
	} );
};

const formatYAxis = ( value ) => {
	if ( value === null || value === undefined ) {
		return '';
	}
	if ( Math.abs( value ) >= 1.0e9 ) {
		return ( value / 1.0e9 ).toFixed( 1 ).replace( /\.0$/, '' ) + 'B';
	}
	if ( Math.abs( value ) >= 1.0e6 ) {
		return ( value / 1.0e6 ).toFixed( 1 ).replace( /\.0$/, '' ) + 'M';
	}
	if ( Math.abs( value ) >= 1.0e3 ) {
		return ( value / 1.0e3 ).toFixed( 1 ).replace( /\.0$/, '' ) + 'K';
	}
	return value.toString();
};

const Overview = ( {
	dashboardData,
	isDashboardLoading,
	selectedRange,
	handleDateRangeChange,
} ) => {
	// Define metrics data structure
	console.log( dashboardData );
	const iconUp = (
		<ArrowTrendingUpIcon className="h-4 w-4 stroke-[3px] text-green-700" />
	);
	const iconDown = (
		<ArrowTrendingDownIcon className="h-4 w-4 stroke-[3px] text-red-600" />
	);
	const getPercentValue = ( percentString ) => {
		// Remove anything that is not a number, dot, or minus
		const value = parseFloat(
			percentString?.replace( /[^\d.-]/g, '' ).trim()
		);
		return isNaN( value ) ? 0 : value;
	};

	const getPercentChange = ( prev, curr ) => {
		if ( prev === 0 && curr === 0 ) {
			return 0;
		}
		if ( prev === 0 && curr !== 0 ) {
			return 100;
		}
		if ( prev !== 0 && curr === 0 ) {
			return -100;
		}
		// Normal percentage change
		return ( ( curr - prev ) / prev ) * 100;
	};

	const currentRecoveryRate = getPercentValue( dashboardData?.recovery_rate );
	const previousRecoveryRate = getPercentValue(
		dashboardData?.previous_period?.recovery_rate
	);
	const recoveryRateChange = getPercentChange(
		previousRecoveryRate,
		currentRecoveryRate
	);
	const metricsData = [
		{
			label: __( 'Recovered Revenue', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.recovered_revenue
				? parse( dashboardData?.recovered_revenue )
				: '0.00',
			tooltip: __(
				'The revenue that has been recovered.',
				'woo-cart-abandonment-recovery'
			),
			color:
				dashboardData?.recovered_revenue_amount > 0
					? 'text-green-700'
					: '',
		},
		{
			label: __( 'Recovered Orders', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.recovered_orders || '0',
			tooltip: __(
				'The number of orders that have been recovered.',
				'woo-cart-abandonment-recovery'
			),
			color: dashboardData?.recovered_orders > 0 ? 'text-green-700' : '',
		},
		{
			label: __( 'Recoverable Revenue', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.recoverable_revenue
				? parse( dashboardData?.recoverable_revenue )
				: '0.00',
			tooltip: __(
				'The revenue that can be recovered.',
				'woo-cart-abandonment-recovery'
			),
		},
		{
			label: __( 'Recoverable Orders', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.recoverable_orders || '0',
			tooltip: __(
				'The number of orders that can be recovered.',
				'woo-cart-abandonment-recovery'
			),
		},
		{
			label: __( 'Lost Orders', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.lost_orders || '0',
			tooltip: __(
				'The number of orders that have been lost.',
				'woo-cart-abandonment-recovery'
			),
		},
		{
			label: __( 'Recovery Rate', 'woo-cart-abandonment-recovery' ),
			value: dashboardData?.recovery_rate || '0.00%',
			tooltip: __(
				'The percentage of orders that have been recovered.',
				'woo-cart-abandonment-recovery'
			),
			color:
				currentRecoveryRate > previousRecoveryRate
					? 'text-green-700'
					: currentRecoveryRate < previousRecoveryRate &&
					  'text-red-600',
			icon: (
				<AppTooltip
					content={ sprintf(
						/* translators: 1: recovery rate change percentage, 2: 'increase' or 'decrease'. */
						__(
							'%1$s%% %2$s from previous period',
							'woo-cart-abandonment-recovery'
						),
						Math.abs( recoveryRateChange ).toFixed( 2 ),
						recoveryRateChange > 0
							? __( 'increase', 'woo-cart-abandonment-recovery' )
							: __( 'decrease', 'woo-cart-abandonment-recovery' )
					) }
					position="top"
				>
					{ currentRecoveryRate > previousRecoveryRate
						? iconUp
						: currentRecoveryRate < previousRecoveryRate &&
						  iconDown }
				</AppTooltip>
			),
		},
	];

	const chartData = dashboardData?.revenue_chart_data || [];

	const handleClearFilter = () => {
		handleDateRangeChange( { from: null, to: null } );
	};

	const hasActiveFilter = selectedRange?.from && selectedRange?.to;

	return (
		<SectionWrapper className="flex flex-col gap-2">
			<div className="p-1 flex gap-2 justify-between flex-wrap relative">
				<Title
					description=""
					icon={ null }
					iconPosition="right"
					size="lg"
					tag="h1"
					title={ __( 'Overview', 'woo-cart-abandonment-recovery' ) }
					className="[&>*]:text-gray-900"
				/>
				<div className="flex items-center gap-2">
					{ hasActiveFilter && (
						<Button
							variant="link"
							size="xs"
							icon={ <XMarkIcon className="h-4 w-4" /> }
							onClick={ handleClearFilter }
							className="text-red-500 no-underline whitespace-nowrap focus:ring-0 [box-shadow:none] focus:[box-shadow:none] hover:no-underline hover:text-red-500"
							aria-label={ __(
								'Clear Filter',
								'woo-cart-abandonment-recovery'
							) }
						>
							{ __(
								'Clear Filter',
								'woo-cart-abandonment-recovery'
							) }
						</Button>
					) }
					<DateRange
						selectedRange={ selectedRange }
						setSelectedRange={ handleDateRangeChange }
						disabled={ isDashboardLoading }
					/>
				</div>
			</div>
			<div className="p-1 rounded-lg flex flex-col gap-2 bg-light-background">
				<div className="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-6 gap-2">
					{ metricsData.map( ( item, index ) => (
						<div
							key={ index }
							className="p-3 rounded-md shadow-sm bg-white flex-1 flex flex-col gap-2"
						>
							<div className="text-sm font-medium text-gray-500 inline-flex items-center">
								{ item.label }{ ' ' }
								<AppTooltip
									content={ item.tooltip }
									position="top"
								>
									<InformationCircleIcon className="h-3 w-3 ml-1 text-gray-500" />
								</AppTooltip>
							</div>
							{ isDashboardLoading ? (
								<SkeletonLoader
									height="2rem"
									className="mt-1"
								/>
							) : (
								<div
									className={ `flex gap-1 justify-between text-3xl font-semibold text-gray-900 ${ item.color }` }
								>
									{ item.value }
									{ item.icon && <span>{ item.icon }</span> }
								</div>
							) }
						</div>
					) ) }
				</div>
				<div className="p-3 flex flex-col gap-6 rounded-md shadow-sm bg-white">
					{ isDashboardLoading ? (
						<SkeletonLoader height="300px" />
					) : (
						<>
							<div className="flex gap-2">
								<div className="p-1 flex gap-2 items-center">
									<div className="h-3 w-3 bg-flamingo-400"></div>
									<span className="text-gray-500 text-sm font-medium">
										{ __(
											'Recoverable Revenue',
											'woo-cart-abandonment-recovery'
										) }
									</span>
								</div>
								<div className="p-1 flex gap-2 items-center">
									<div className="h-3 w-3 bg-amber-400"></div>
									<span className="text-gray-500 text-sm font-medium">
										{ __(
											'Recovered Revenue',
											'woo-cart-abandonment-recovery'
										) }
									</span>
								</div>
							</div>
							<LineChart
								data={ chartData }
								dataKeys={ [
									__(
										'recoverable',
										'woo-cart-abandonment-recovery'
									),
									__(
										'recovered',
										'woo-cart-abandonment-recovery'
									),
								] }
								colors={ [
									{ stroke: '#F06434' },
									{ stroke: '#FBBF24' },
								] }
								showXAxis={ true }
								showYAxis={ true }
								showTooltip={ true }
								showCartesianGrid={ true }
								xAxisDataKey="day"
								xAxisTickFormatter={ formatXAxis }
								yAxisTickFormatter={ formatYAxis }
								chartWidth="100%"
								chartHeight={ 300 }
								lineChartWrapperProps={ {
									margin: {
										left: -12,
										right: 0,
										top: 0,
										bottom: 0,
									},
								} }
								xAxisFontSize="sm"
								strokeDasharray="0 0"
							/>
						</>
					) }
				</div>
			</div>
		</SectionWrapper>
	);
};

export default Overview;
