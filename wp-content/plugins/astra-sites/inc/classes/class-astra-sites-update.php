<?php
/**
 * Astra Sites Update
 *
 * @package Astra Sites
 */

if ( ! class_exists( 'Astra_Sites_Update' ) ) :

	/**
	 * Astra Sites Update
	 *
	 * @since 4.2.2
	 */
	class Astra_Sites_Update {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 4.2.2
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @since 4.2.2
		 * @return object initialized object of class.
		 */
		public static function set_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 4.2.2
		 */
		public function __construct() {
			add_action( 'astra_update_before', __CLASS__ . '::init' );

			// Check for plugin updates on plugins_loaded.
			self::init();
		}

		/**
		 * Update
		 *
		 * @since 4.2.2
		 * @return void
		 */
		public static function init() {
			do_action( 'astra_sites_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'astra-sites-auto-version', '0' );

			// If equals then return.
			if ( version_compare( $saved_version, ASTRA_SITES_VER, '=' ) ) {
				return;
			}

			if ( version_compare( $saved_version, '4.2.2 ', '<' ) ) {
				// Run batch process to create the json files for existing users.
				delete_site_option( 'astra-sites-fresh-site' );
				delete_site_option( 'astra-sites-last-export-checksums' );

				$site_pages = get_option( 'astra-sites-requests' );
				if ( ! empty( $site_pages ) ) {
					// Delete all sites.
					for ( $site_page = 1; $site_page <= $site_pages; $site_page++ ) {
						delete_site_option( 'astra-sites-and-pages-page-' . $site_page );
					}
				}

				$old_options = array(
					'astra-blocks-1',
					'astra-blocks-2',
					'astra-blocks-3',
					'astra-blocks-4',
					'astra-sites-site-category',
					'astra-sites-all-site-categories',
					'astra-sites-page-builders',
					'astra-blocks-categories',
					'astra-sites-requests',
				);

				foreach ( $old_options as $option ) {
					delete_site_option( $option );
				}

				delete_site_transient( 'astra-sites-import-check' );
			}

			if ( version_compare( $saved_version, '4.3.1', '<' ) ) {
				if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
					delete_site_option( 'astra-sites-fresh-site' );
					delete_site_option( 'astra-sites-batch-status' );
					delete_site_transient( 'astra-sites-import-check' );
				}
			}

			if ( version_compare( $saved_version, '4.4.2', '<' ) ) {
				$transient_name = 'nps-survay-form-dismissed';
				$expiration_time = get_option( '_transient_timeout_' . $transient_name );
				$nps_transient = get_transient( $transient_name );

				if ( $nps_transient && $expiration_time ) {
					$transient_duration = 2 * WEEK_IN_SECONDS;
					$creation_time = $expiration_time - $transient_duration;

					$status = get_option( 'nps-survay-form-dismiss-status', array() );
					$status['dismiss_time'] = $creation_time;
					update_option( 'nps-survay-form-dismiss-status', $status );
				}
			}

			if ( version_compare( $saved_version, '4.4.6', '<' ) ) {

				$old_dismiss_varible = get_option( 'nps-survay-form-dismiss-status' );

				if ( ! empty( $old_dismiss_varible ) ) {
					update_option( 'nps-survey-astra-sites', $old_dismiss_varible );
					delete_option( 'nps-survay-form-dismiss-status' );
				}
			}

			if ( version_compare( $saved_version, '4.4.49', '<' ) ) {
				self::background_updater_4_4_49();
			}

			// Auto update product latest version.
			update_option( 'astra-sites-auto-version', ASTRA_SITES_VER, 'no' );

			do_action( 'astra_sites_update_after' );
		}

		/**
		 * Background updates for version 4.4.49.
		 *
		 * @since 4.4.49
		 * @return void
		 */
		public static function background_updater_4_4_49() {
			// Retrieve the installed time and optin status of BSF Analytics and update it as per product specific key.
			$analytics_options = array(
				'bsf_analytics_installed_time' => 'astra_sites_usage_installed_time',
				'bsf_analytics_optin'          => 'astra_sites_usage_optin',
			);

			foreach ( $analytics_options as $source => $target ) {
				$status = get_site_option( $source );
				if ( ! get_site_option( $target ) && $status ) {
					update_site_option( $target, $status );
				}
			}
		}
	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Update::set_instance();

endif;
