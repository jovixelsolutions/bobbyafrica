<?php
/**
 * Modern Cart Uninstall
 *
 * Cleans up all plugin options, transients, and analytics data
 * when the plugin is deleted via the WordPress admin.
 *
 * @package ModernCart
 * @since 1.0.9
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only delete data if the user has opted in via the settings toggle.
$moderncart_settings = get_option( 'moderncart_setting', array() );
if ( empty( $moderncart_settings['delete_data_on_uninstall'] ) ) {
	return;
}

// Plugin settings.
delete_option( 'moderncart_setting' );
delete_option( 'moderncart_cart' );
delete_option( 'moderncart_floating' );
delete_option( 'moderncart_appearance' );
delete_option( 'moderncart_version' );
delete_option( 'moderncart_is_onboarding_complete' );

// Analytics opt-in.
delete_option( 'mcw_usage_optin' );
delete_option( 'mcw_analytics_optin' );

// Analytics event tracking.
delete_option( 'mcw_usage_events_pending' );
delete_option( 'mcw_usage_events_pushed' );
delete_option( 'mcw_usage_installed_time' );
delete_option( 'mcw_tracked_version' );

// Analytics event flags (free).
delete_option( 'mcw_first_order_tracked' );
delete_option( 'mcw_first_coupon_applied' );
delete_option( 'mcw_first_settings_saved' );
delete_option( 'mcw_onboarding_skipped' );
delete_option( 'mcw_onboarding_exit_step' );

// Daily KPI data.
delete_option( 'mcw_daily_coupon_counts' );

// Analytics transients.
delete_transient( 'mcw_state_events_checked' );
delete_transient( 'moderncart_redirect_to_onboarding' );
delete_transient( 'moderncart_knowledge_base_data' );
