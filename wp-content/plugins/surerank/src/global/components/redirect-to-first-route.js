import { Navigate, useRouter } from '@tanstack/react-router';
import { getNavLinks } from '@Global/constants/nav-links';
import { filterNavLinksByRoutes } from '@Functions/router';

/**
 * Get the first available route path based on the router's flat routes and navigation links.
 *
 * @param {Object} router - The TanStack Router instance.
 * @return {string} The path of the first available route.
 */
export const getFirstAvailableRoute = ( router ) => {
	// Get all routes from the router using TanStack's flatRoutes API
	const allRoutes = router.flatRoutes || [];

	// Create a Set of available route paths
	const routePaths = new Set(
		allRoutes
			.map( ( route ) => route.fullPath )
			.filter( ( path ) => path && path !== '/' )
	);

	// Get raw navigation links
	const rawNavLinks = getNavLinks();

	// Filter nav links based on available routes
	const filteredNavLinks = filterNavLinksByRoutes( rawNavLinks, routePaths );

	// Find the first available route from the filtered nav links
	let firstRoutePath = '/dashboard'; // Default fallback

	// Helper to find first path in links array
	const findFirstPath = ( links ) => {
		for ( const link of links ) {
			if ( link.path ) {
				return link.path;
			}
		}
		return null;
	};

	// Iterate through sections to find the first available link
	for ( const section of filteredNavLinks ) {
		if ( section.links && section.links.length > 0 ) {
			const path = findFirstPath( section.links );
			if ( path ) {
				firstRoutePath = path;
				break;
			}
		}
	}

	return firstRoutePath;
};

/**
 * Component to redirect to first available route using TanStack Router's API
 * This is useful when a user doesn't have access to the default route
 * and needs to be redirected to the first available route they can access.
 *
 * @return {JSX.Element} Navigate component that redirects to the first available route
 */
const RedirectToFirstRoute = () => {
	const router = useRouter();

	const firstRoutePath = getFirstAvailableRoute( router );

	return <Navigate to={ firstRoutePath } />;
};

export default RedirectToFirstRoute;
