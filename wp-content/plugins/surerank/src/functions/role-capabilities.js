/**
 * Check current user capability.
 *
 * @param {string} capability The capability to check.
 *
 * @return {boolean} Whether the user has the capability.
 */
export default function currentUserCan( capability ) {
	// If surerank_admin_common is not defined, return true always.
	if ( ! surerank_admin_common?.roles_capabilities ) {
		return true;
	}
	return surerank_admin_common?.roles_capabilities?.includes( capability );
}
