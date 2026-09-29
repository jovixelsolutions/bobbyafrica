<?php
/**
 * CartFlows Loader.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Nps_Survey' ) ) :

	/**
	 * Cartflows Nps Survey Loader
	 *
	 * @since 2.1.3
	 */
	class Cartflows_Nps_Survey {
		/**
		 * Instance
		 *
		 * @since 2.1.3
		 * @var (Object) Cartflows_Nps_Survey
		 */
		private static $instance = null;

		/**
		 * Get Instance
		 *
		 * @since 2.1.3
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
		 * @since 2.1.3
		 */
		private function __construct() {
			$this->version_check();
			add_action( 'init', array( $this, 'load' ), 999 );
			add_filter( 'nps_survey_api_endpoint', array( $this, 'update_webhook_url' ), 10, 2 );
		}

		/**
		 * Version Check
		 *
		 * @since 2.1.3
		 * @return void
		 */
		public function version_check() {

			$file = realpath( dirname( __FILE__ ) . '/nps-survey/version.json' );

			// Is file exist?
			if ( is_file( $file ) ) {

				$file_data = json_decode( file_get_contents( $file ), true );

				global $nps_survey_version, $nps_survey_init;

				$path = realpath( dirname( __FILE__ ) . '/nps-survey/nps-survey.php' );

				$version = isset( $file_data['nps-survey'] ) ? $file_data['nps-survey'] : 0;

				if ( null === $nps_survey_version ) {
					$nps_survey_version = '1.0.0';
				}

				// Compare versions.
				if ( version_compare( $version, $nps_survey_version, '>=' ) ) {
					$nps_survey_version = $version;
					$nps_survey_init    = $path;
				}
			}
		}

		/**
		 * Load latest plugin
		 *
		 * @since 2.1.3
		 * @return void
		 */
		public function load() {

			global $nps_survey_version, $nps_survey_init;
			if ( is_file( realpath( $nps_survey_init ) ) ) {
				include_once realpath( $nps_survey_init );
			}
		}

		/**
		 * Update the NPS survey webhook URL.
		 *
		 * @param string       $url Webhook URL.
		 * @param array<mixed> $post_data NPS survey post data.
		 *
		 * @return string
		 */
		public function update_webhook_url( $url, $post_data ) {
			if ( isset( $post_data['plugin_slug'] ) && 'cartflows' === $post_data['plugin_slug'] ) {
				// Return the NPS webhook URL if the plugin slug is CartFlows.
				return CARTFLOWS_NPS_WEBHOOK_URL;
			}

			return $url;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Nps_Survey::get_instance();

endif;
