<?php
/**
 * CartFlows
 * Delete Plugin Data.
 *
 * @package CartFlows
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


global $wpdb;


$is_delete = get_option( 'cartflows_delete_plugin_data', false );

if ( 'enable' === $is_delete ) {

	$options = array(
		'cartflows-version',
		'wcf_setup_complete',
		'cartflows-divi-flows-and-steps-1',
		'cartflows-gutenberg-flows-and-steps-1',
		'cartflows-elementor-flows-and-steps-1',
		'cartflows-beaver-builder-flows-and-steps-1',
		'cartflows-last-export-checksums-latest',
		'cartflows-batch-status-string',
		'cartflows-elementor-requests',
		'cartflows-fresh-site',
		'cartflows-batch-is-complete',

		'cartflows-old-ui-user',
		'cartflows-legacy-admin',
		'cartflows-legacy-meta-show-design-options',
		'cartflows-assets-version',

		'_cartflows_common',
		'_cartflows_permalink',
		'_cartflows_facebook',
		'_cartflows_google_analytics',
		'_cartflows_offer_global_settings',

		'cf_analytics_installed_time',
		'cf_usage_optin',
		'cartflows_delete_plugin_data',
		'wcf-instant-checkout-notice-skipped',
	);

	foreach ( $options as $index => $key ) {
		delete_option( $key );
	}

	// Clean up per-user "new UI available" notice dismissal flags set by the
	// legacy admin's soft-promo notice. The fourth arg ($delete_all = true)
	// removes the meta for every user in one query.
	delete_metadata( 'user', 0, 'cartflows-switch-to-new-ui-notice', '', true );

	if ( function_exists( 'as_next_scheduled_action' ) && as_next_scheduled_action( 'cartflows_send_report_summary_email' ) ) {
		as_unschedule_all_actions( 'cartflows_send_report_summary_email' );
	}
}
