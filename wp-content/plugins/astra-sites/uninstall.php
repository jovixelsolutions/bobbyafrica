<?php
/**
 * Starter Templates Uninstall
 *
 * Fired when the plugin is deleted via the WordPress admin.
 *
 * @since 4.5.4
 * @package Starter Templates
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Site data options.
$site_pages = get_option( 'astra-sites-requests' );
if ( ! empty( $site_pages ) ) {

	// Delete all sites.
	for ( $site_page = 1; $site_page <= $site_pages; $site_page++ ) {
		delete_site_option( 'astra-sites-and-pages-page-' . $site_page );
	}

	// Delete all pages count.
	delete_site_option( 'astra-sites-requests' );
}

delete_option( 'astra_sites_recent_import_log_file' );
delete_option( 'ai_builder_recent_import_log_file' );
delete_option( 'astra_sites_import_data' );
delete_option( 'astra_sites_wpforms_ids_mapping' );
delete_option( '_astra_sites_old_customizer_data' );
delete_option( '_astra_sites_old_site_options' );
delete_option( '_astra_sites_old_widgets_data' );
delete_option( 'astra_sites_settings' );
delete_option( 'astra_parent_page_url' );
delete_option( 'astra-sites-favorites' );
delete_site_option( 'astra-sites-fresh-site' );
delete_site_option( 'astra-sites-batch-status' );
delete_site_option( 'astra-sites-batch-status-string' );
delete_site_option( 'astra-sites-all-site-categories' );
delete_site_option( 'astra-sites-all-site-categories-and-tags' );
delete_site_option( 'astra-sites-current-page' );
delete_site_option( 'astra_sites_surecart_mapping_data' );

// Import state options.
delete_option( 'astra_sites_import_complete' );
delete_option( 'astra_sites_batch_process_started' );
delete_option( 'astra_sites_batch_process_started_time' );
delete_option( 'astra_sites_batch_process_complete' );
delete_option( 'astra_sites_ai_import_started' );
delete_option( 'ast_ai_import_current_url' );
delete_option( 'astra_sites_import_started' );
delete_option( 'astra_sites_current_import_template_type' );
