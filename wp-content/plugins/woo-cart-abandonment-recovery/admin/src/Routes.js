import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

import { Toaster } from '@bsf/force-ui';

import Header from '@Components/common/Header';
import Dashboard from '@Admin/pages/Dashboard';
import Settings from '@Admin/pages/Settings';
import FollowUp from '@Admin/pages/FollowUp';
import Product from '@Admin/pages/Product';
import DetailedReport from '@Admin/pages/DetailedReport';
import FollowUpTemplates from '@Admin/pages/FollowUpTemplates';
import Integrations from '@Admin/pages/Integrations';
import OnboardingLayout from '@Components/onboarding/OnboardingLayout';
import ProUpgradeHeaderBanner from '@Components/pro/ProUpgradeHeaderBanner';
// import Ottokit from '@Admin/components/integrations/Ottokit';

const Routes = () => {
	const urlParams = new URLSearchParams( useLocation().search );
	const currentPath = urlParams.get( 'path' ) || 'dashboard';

	useEffect( () => {
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	}, [ currentPath ] );

	// Define routes array with path and component mapping
	const routes = [
		{
			path: 'dashboard',
			component: <Dashboard />,
		},
		{
			path: 'settings',
			component: <Settings />,
		},
		{
			path: 'follow-up',
			component: <FollowUp />,
		},
		{
			path: 'product',
			component: <Product />,
		},
		{
			path: 'detailed-report',
			component: <DetailedReport />,
		},
		{
			path: 'follow-up-templates',
			component: <FollowUpTemplates />,
		},
		{
			path: 'integrations',
			component: <Integrations />,
		},
		{
			path: 'onboarding',
			component: <OnboardingLayout />,
		},
	];

	// Find matching route or default to dashboard
	const routePage = routes.find( ( route ) => route.path === currentPath )
		?.component || <Dashboard />;

	return (
		<div
			className={ `ca-page-content--wrapper ${ currentPath } bg-primary-background` }
		>
			{ currentPath !== 'onboarding' && (
				<>
					<ProUpgradeHeaderBanner />
					<Header />
				</>
			) }
			{ routePage }
			<Toaster
				position="top-right"
				design="stack"
				theme="light"
				autoDismiss={ true }
				dismissAfter={ 3000 }
				className="z-999999"
			/>
		</div>
	);
};

export default Routes;
