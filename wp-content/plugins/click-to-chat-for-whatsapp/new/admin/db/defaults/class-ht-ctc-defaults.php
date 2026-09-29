<?php
/**
 * Default values..
 *
 * @since 3.9
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Defaults' ) ) {

	/**
	 * Provides default values for plugin settings.
	 */
	class HT_CTC_Defaults {

		/**
		 * Main options default values.
		 *
		 * @var array
		 */
		public $main_options = '';

		/**
		 * Initialize defaults.
		 */
		public function __construct() {
		}
	}

	new HT_CTC_Defaults();

} // END class_exists check
