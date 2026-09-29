import { useEffect, useState } from 'react';
import { useStateValue, doApiFetch } from '@Store';

// Import dashboard components
import Overview from '@Components/dashboard/Overview';
import RecentEmailLogs from '@Components/dashboard/RecentEmailLogs';
import ProductReport from '@Components/dashboard/ProductReport';
import AddonsPlugins from '@Components/dashboard/AddonsPlugins';
import QuickAccess from '@Components/dashboard/QuickAccess';

const Dashboard = () => {
	// Safe state access with default values
	const [ state, dispatch ] = useStateValue();
	const dashboardData = state?.dashboardData || null;
	const [ isDashboardLoading, setIsDashboardLoading ] = useState( false );

	// Default to last 7 days, ending today
	const getDefaultRange = () => {
		const to = new Date();
		const from = new Date();
		from.setDate( to.getDate() - 6 ); // 7 days including today
		return { from, to };
	};

	const [ selectedRange, setSelectedRange ] = useState( getDefaultRange );

	useEffect( () => {
		const fetchDashboardData = async () => {
			setIsDashboardLoading( true );

			// Fallback to default range if cleared
			let from = selectedRange.from;
			let to = selectedRange.to;
			if ( ! from || ! to ) {
				const def = getDefaultRange();
				from = def.from;
				to = def.to;
			}

			await doApiFetch(
				'/wcar/api/admin/dashboard',
				{
					date_range: {
						from: new Date(
							Date.UTC(
								from.getFullYear(),
								from.getMonth(),
								from.getDate()
							)
						)
							.toISOString()
							.slice( 0, 10 ),
						to: new Date(
							Date.UTC(
								to.getFullYear(),
								to.getMonth(),
								to.getDate()
							)
						)
							.toISOString()
							.slice( 0, 10 ),
					},
				},
				'POST',
				// onSuccess.
				( response ) => {
					dispatch( {
						type: 'FETCH_DASHBOARD_DATA_SUCCESS',
						dashboardData: response,
					} );
					setIsDashboardLoading( false );
				},
				// onError.
				() => {
					setIsDashboardLoading( false );
				}
			);
		};

		fetchDashboardData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ selectedRange ] );

	return (
		<div className="p-4 md:p-8 flex flex-col gap-3 md:gap-6">
			{ /* Overview Section */ }
			<Overview
				dashboardData={ dashboardData }
				isDashboardLoading={ isDashboardLoading }
				selectedRange={ selectedRange }
				handleDateRangeChange={ setSelectedRange }
			/>

			<div className="flex flex-col lg:flex-row gap-3 md:gap-6">
				{ /* Recent Email Logs Section */ }
				<RecentEmailLogs
					dashboardData={ dashboardData }
					isDashboardLoading={ isDashboardLoading }
				/>

				{ /* Product Report Section */ }
				<ProductReport
					dashboardData={ dashboardData }
					isDashboardLoading={ isDashboardLoading }
				/>
			</div>

			<div className="flex flex-col lg:flex-row gap-3 md:gap-6">
				{ /* Extend Website Section */ }
				<AddonsPlugins />

				{ /* Quick Access Section */ }
				<QuickAccess />
			</div>
		</div>
	);
};

export default Dashboard;
