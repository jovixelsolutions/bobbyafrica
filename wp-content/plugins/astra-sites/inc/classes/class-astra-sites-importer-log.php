<?php
/**
 * Astra Pro Sites Importer Log
 *
 * This class serves as a wrapper around ST_Importer_Log,
 * providing dynamic prefix switching between ai-builder and astra-sites based on page builder.
 *
 * @since 1.1.0
 * @package Astra Sites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use STImporter\Importer\ST_Importer_Log;

if ( ! class_exists( 'Astra_Sites_Importer_Log' ) ) :

	/**
	 * Astra Pro Sites Importer Log
	 */
	class Astra_Sites_Importer_Log {

		/**
		 * Instance
		 *
		 * @since 1.1.0
		 * @var (Object) Class object
		 */
		private static $instance = null;

		/**
		 * Set Instance
		 *
		 * @since 1.1.0
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.1.0
		 */
		private function __construct() {
			// Bail out if ST_Importer_Log does not exist.
			if ( ! class_exists( 'STImporter\Importer\ST_Importer_Log' ) ) {
				return;
			}

			// Set prefix based on page builder (dynamic switching).
			add_filter( 'st_importer_log_prefix', array( $this, 'get_prefix_by_page_builder' ), 99 );

			// Initialize the ST_Importer_Log instance.
			ST_Importer_Log::get_instance();
		}

		/**
		 * Get prefix by page builder - Dynamic based on page builder setting
		 *
		 * @since 4.4.49
		 * @param string $prefix Default prefix.
		 * @return string Modified prefix (astra_sites or ai_builder).
		 */
		public function get_prefix_by_page_builder( $prefix ) {
			$page_builder = Astra_Sites_Page::get_instance()->get_setting( 'page_builder', 'gutenberg' );
			if ( 'ai-builder' === $page_builder ) {
				return 'ai_builder';
			}
			return 'astra_sites';
		}

		/**
		 * Get Log File URL
		 *
		 * @return string File URL.
		 * @since 4.4.49
		 */
		public static function get_log_file_url() {
			$prefix      = self::get_instance()->get_prefix_by_page_builder( '' );
			$option_name = $prefix . '_recent_import_log_file';
			$log_file    = get_option( $option_name, false );
			if ( ! empty( $log_file ) && is_string( $log_file ) ) {
				return str_replace( ABSPATH, esc_url( site_url() ) . '/', $log_file );
			}

			return '';
		}

		/**
		 * Add log entry - Wrapper for ST_Importer_Log::add()
		 *
		 * @since 1.1.0
		 * @param string $content  Content to be saved to the file.
		 * @param string $severity Severity level: 'info', 'success', 'warning', 'error', 'fatal'. Default 'info'.
		 * @param array  $context  Additional context data for the log entry.
		 * @return void
		 */
		public static function add( $content, $severity = 'info', $context = array() ) {
			// Bail out if ST_Importer_Log does not exist.
			if ( ! class_exists( 'STImporter\Importer\ST_Importer_Log' ) ) {
				return;
			}

			ST_Importer_Log::add( $content, $severity, $context );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Sites_Importer_Log::get_instance();

endif;
