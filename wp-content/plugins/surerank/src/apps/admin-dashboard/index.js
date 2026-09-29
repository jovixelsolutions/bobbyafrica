// Register SureRank's apiFetch middleware before any apiFetch call so
// settings saves automatically fall back to admin-ajax.php when a
// security plugin or WAF blocks /wp-json/. See #2362.
import '@Functions/api-fetch-middleware';

import { mountComponent } from '@Functions/utils';
import createAdminRouter, {
	createRoute,
	createChildRoute,
	buildRoutePathMap,
	filterNavLinksByRoutes,
	filterFalsyRoutes,
} from '@Functions/router';
import NotFound from './not-found';
import Dashboard from './dashboard';
import { Toaster, toast } from '@bsf/force-ui';
import { getNavLinks } from '@Global/constants/nav-links';
import { Navigate, useNavigate, useLocation } from '@tanstack/react-router';
import SidebarLayout from '@AdminComponents/layout/sidebar-layout';
import SearchConsole from '../admin-search-console';
import {
	ENABLE_GOOGLE_CONSOLE,
	ENABLE_SCHEMAS,
	ENABLE_MIGRATION,
} from '@Global/constants';
import { applyFilters, addFilter } from '@wordpress/hooks';
import RedirectToFirstRoute from '@Global/components/redirect-to-first-route';

// Import all the components directly
import TitleAndDescriptionRoute from '@AdminGeneral/general/title-and-description/title-and-description';
import SiteInformationRoute from '@AdminGeneral/general/site-information';
import HomePageRoute from '@AdminGeneral/general/home-page/home-page';
import ArchivePagesRoute from '@AdminGeneral/advanced/archive-pages/archive-pages';
import SocialGeneralRoute from '@AdminGeneral/social/general/general';
import FacebookRoute from '@AdminGeneral/social/facebook/facebook';
import TwitterRoute from '@AdminGeneral/social/twitter/twitter';
import AccountRoute from '@AdminGeneral/social/account/account';
import RobotInstructionsRoute from '@AdminGeneral/advanced/robot-instructions/robot-instructions';
import SitemapsRoute from '@AdminGeneral/advanced/sitemaps/sitemaps';
import ImageSeoRoute from '@AdminGeneral/advanced/image-seo/image-seo';
import FeaturesManagementRoute from '@AdminGeneral/advanced/features-management/features-management';
import ContentAnalysisRoute from '@AdminDashboard/content-analysis/content-analysis';
import SiteSeoChecksRoute from '@AdminDashboard/site-seo-checks/site-seo-checks-main';
import MigrationRoute from '@AdminGeneral/advanced/tools/migration';
import MiscellaneousRoute from '@AdminGeneral/advanced/tools/miscellaneous';
import RobotsTxtEditorRoute from '@AdminGeneral/advanced/tools/robots-txt-editor/robots-txt-editor';
import BreadcrumbsRoute from '@AdminDashboard/breadcrumbs/settings';
import SchemaRoute from '@AdminGeneral/schema/schema';
import ImportExportSettingsRoute from '@AdminGeneral/advanced/tools/import-export-settings';
import RedirectionManager from '@AdminDashboard/link-manager/redirection-manager';
import RoleManager from '@AdminDashboard/role-manager/index';
import LinkManagerDashboard from '@AdminDashboard/link-manager/dashboard';
import LinkManagerSettings from '@AdminDashboard/link-manager/settings';
import LinkSuggestion from '@AdminDashboard/link-suggestion/link-suggestion';
import InstantIndexingSettings from '@AdminDashboard/instant-indexing/settings';
import InstantIndexingLogs from '@AdminDashboard/instant-indexing/logs';
import EmailReportsRoute from '@AdminGeneral/advanced/email-reports';
import GoogleIndexingSettings from '@AdminDashboard/google-indexing/settings';
import GoogleIndexingLogs from '@AdminDashboard/google-indexing/logs';
import ImageGenerationUpgrade from '@AdminDashboard/image-generation';
import currentUserCan from '@/functions/role-capabilities';
import { isProActive } from '@/functions/nudges';

// Define toast globally for PRO plugin.
if ( window && ! window?.toast ) {
	window.toast = toast;
}

// Routes
const LegacySitemapsRedirect = () => (
	<Navigate to="/general/sitemaps" replace />
);

const dashboardRoutes = [
	// Default route redirects to dashboard
	createRoute(
		'/',
		() =>
			currentUserCan( 'surerank_global_setting' ) ? (
				<Navigate to="/dashboard" />
			) : (
				<RedirectToFirstRoute />
			),
		[],
		{ navbarOnly: true }
	),
	// Dashboard routes
	createRoute( '/dashboard', Dashboard, [], {
		navbarOnly: true,
		capability: 'surerank_global_setting',
	} ),
];

const generalAndAdvancedRoutes = [
	// General routes
	createRoute(
		'/general',
		null,
		[
			createChildRoute( '/', TitleAndDescriptionRoute ),
			createChildRoute( '/sitemaps', SitemapsRoute ),
			createChildRoute( '/site-information', SiteInformationRoute ),
		],
		{
			capability: 'surerank_global_setting',
		}
	),
	createRoute(
		'/general/homepage',
		null,
		[
			createChildRoute( '/', HomePageRoute ),
			createChildRoute( '/social', HomePageRoute ),
			createChildRoute( '/advanced', HomePageRoute ),
		],
		{
			capability: 'surerank_global_setting',
		}
	),
	createChildRoute( '/general/archive_pages', ArchivePagesRoute, [], {
		capability: 'surerank_global_setting',
	} ),
	createRoute(
		'/general/social',
		null,
		[
			createChildRoute( '/', SocialGeneralRoute ),
			createChildRoute( '/facebook', FacebookRoute ),
			createChildRoute( '/x', TwitterRoute ),
			createChildRoute( '/accounts', AccountRoute ),
		],
		{
			capability: 'surerank_global_setting',
		}
	),

	// Advanced routes
	createRoute( '/advanced', null, [
		createRoute(
			'/robot_instructions',
			null,
			[
				createChildRoute( '/indexing', RobotInstructionsRoute ),
				createChildRoute( '/following', RobotInstructionsRoute ),
				createChildRoute( '/archiving', RobotInstructionsRoute ),
			],
			{
				capability: 'surerank_global_setting',
			}
		),
		createChildRoute( '/email-reports', EmailReportsRoute, [], {
			capability: 'surerank_global_setting',
		} ),
		createChildRoute( '/sitemaps', LegacySitemapsRedirect, [], {
			capability: 'surerank_global_setting',
		} ),
		createChildRoute( '/image-seo', ImageSeoRoute, [], {
			capability: 'surerank_global_setting',
		} ),
		createChildRoute( '/image-generation', ImageGenerationUpgrade, [], {
			capability: 'surerank_global_setting',
		} ),
		// Conditionally include schema route
		...( ENABLE_SCHEMAS && SchemaRoute
			? [
					createChildRoute( '/schema', SchemaRoute, [], {
						capability: 'surerank_global_setting',
					} ),
			  ]
			: [] ),
		createChildRoute( '/robots-txt-editor', RobotsTxtEditorRoute, [], {
			capability: 'surerank_global_setting',
		} ),
		createChildRoute( '/breadcrumbs', BreadcrumbsRoute, [], {
			capability: 'surerank_global_setting',
		} ),
	] ),
];

const searchConsoleRoutes = [
	createRoute( '/search-console', SearchConsole, [], {
		navbarOnly: true,
		capability: 'surerank_global_setting',
	} ),
	createRoute( '/content-performance', ContentAnalysisRoute, [], {
		navbarOnly: true,
		capability: 'surerank_global_setting',
	} ),
];

const siteSeoAnalysisRoutes = [
	createRoute( '/site-seo-analysis', SiteSeoChecksRoute, [], {
		navbarOnly: true,
		capability: 'surerank_global_setting',
	} ),
];

// Link Manager routes
const linkManagerRoutes = [
	createRoute(
		'/link-manager',
		null,
		[
			createChildRoute( '/redirection-manager', RedirectionManager, [], {
				fullWidth: true,
				navbarOnly: false,
				capability: 'surerank_global_setting',
			} ),
			createChildRoute( '/link-suggestion', LinkSuggestion, [], {
				fullWidth: isProActive( 'pro' ) ? false : true,
				navbarOnly: false,
				capability: 'surerank_global_setting',
			} ),
			createChildRoute(
				'/link-manager/dashboard',
				LinkManagerDashboard,
				[],
				{
					fullWidth: true,
					navbarOnly: false,
					capability: 'surerank_global_setting',
				}
			),
			createChildRoute(
				'/link-manager/settings',
				LinkManagerSettings,
				[],
				{
					fullWidth: true,
					navbarOnly: false,
					capability: 'surerank_global_setting',
				}
			),
		],
		{
			capability: 'surerank_global_setting',
		}
	),
];

// Instant Indexing routes
const instantIndexingRoutes = [
	createRoute(
		'/advanced/instant-indexing',
		null,
		[
			createChildRoute( '/settings', InstantIndexingSettings, [], {
				fullWidth: false,
			} ),
			createChildRoute( '/logs', InstantIndexingLogs, [], {
				fullWidth: false,
			} ),
		],
		{
			capability: 'surerank_global_setting',
		}
	),
];

// Google Indexing routes
const googleIndexingRoutes = [
	createRoute(
		'/advanced/google-indexing',
		null,
		[
			createChildRoute( '/settings', GoogleIndexingSettings, [], {
				fullWidth: false,
			} ),
			createChildRoute( '/logs', GoogleIndexingLogs, [], {
				fullWidth: false,
			} ),
		],
		{
			capability: 'surerank_global_setting',
		}
	),
];

// Tools routes
const toolsRoutes = [
	createRoute( '/tools', null, [
		createChildRoute( '/manage-features', FeaturesManagementRoute, [], {
			capability: 'manage_options',
		} ),
		createChildRoute( '/import-export', ImportExportSettingsRoute, [], {
			capability: 'manage_options',
		} ),
		...( ENABLE_MIGRATION
			? [
					createChildRoute( '/migrate', MigrationRoute, [], {
						capability: 'manage_options',
					} ),
			  ]
			: [] ),
		createChildRoute( '/miscellaneous', MiscellaneousRoute, [], {
			capability: 'manage_options',
		} ),
		createChildRoute( '/role-manager', RoleManager, [], {
			capability: 'manage_options',
		} ),
	] ),
];
// Register a HOC that provides TanStack Router utilities (navigate + location) as props.
// Used by the Pro plugin to inject router context into its separately-bundled components.
addFilter( 'surerank.router.with-router-props', 'surerank/router-props', () => {
	return ( Component ) => {
		function WithRouterProps( props ) {
			const navigate = useNavigate();
			const location = useLocation();
			return (
				<Component
					{ ...props }
					routerNavigate={ navigate }
					routerLocation={ location }
				/>
			);
		}
		return WithRouterProps;
	};
} );

// Combine all routes
const baseRoutes = filterFalsyRoutes( [
	...dashboardRoutes,
	...generalAndAdvancedRoutes,
	...instantIndexingRoutes,
	...googleIndexingRoutes,
	...linkManagerRoutes,
	...toolsRoutes,
	...siteSeoAnalysisRoutes,
	// Conditionally include search console routes
	...( ENABLE_GOOGLE_CONSOLE ? searchConsoleRoutes : [] ),
] );

const filteredRoutes = filterFalsyRoutes(
	applyFilters( 'surerank-pro.routes', [ ...baseRoutes ] )
);

const routeMap = new Map();

filteredRoutes.forEach( ( route ) => {
	routeMap.set( route.path, route );
} );

export const routes = Array.from( routeMap.values() );

// Navigation Links - filter based on available routes
const rawNavLinks = getNavLinks();
const routePaths = buildRoutePathMap( routes );
export const navLinks = filterNavLinksByRoutes( rawNavLinks, routePaths );

// Create router using the original createAdminRouter but with custom layout
const Router = createAdminRouter( {
	navLinks,
	routes,
	notFoundComponent: NotFound,
	defaultLayout: {
		component: SidebarLayout,
		props: {},
	},
} );

const App = () => {
	return (
		<>
			<Router />
			<Toaster className="z-[9999999]" />
		</>
	);
};

mountComponent( '#surerank-root', <App /> );
