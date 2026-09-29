<?php
/**
 * Switcher between the new and previous user interfaces.
 *
 * New users default to the new interface. Previous users retain the legacy
 * interface unless they explicitly opt in to the new experience.
 *
 * @package ClickToChat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Switch' ) ) {

	/**
	 * Handles switching between new and legacy plugin interfaces.
	 */
	class HT_CTC_Switch {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->to_switch();
		}

		/**
		 * Define plugin-wide constants.
		 *
		 * @return void
		 */
		private function define_constants() {

			$this->define( 'HT_CTC_WP_MIN_VERSION', '4.6' );
			$this->define( 'HT_CTC_PLUGIN_DIR_URL', plugin_dir_url( HT_CTC_PLUGIN_FILE ) );
			$this->define( 'HT_CTC_PLUGIN_BASENAME', plugin_basename( HT_CTC_PLUGIN_FILE ) );
			$this->define( 'HT_CTC_BLOG_NAME', get_bloginfo( 'name' ) );
			// $this->define( 'HT_CTC_SITE_URL', get_site_url() );
			// $this->define( 'HT_CTC_HOME_URL', home_url('/') );
			// $this->define( 'HT_CTC_HOME_URL', get_bloginfo('url') );

			$this->define( 'HT_CTC_PLUGIN_SLUG', 'click-to-chat-for-whatsapp' );

			do_action( 'ht_ctc_ah_define_constants' );
		}

		/**
		 * Define a constant if it does not already exist.
		 *
		 * @param string $name  Constant name.
		 * @param mixed  $value Constant value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}


		/**
		 * Determine whether to load the new or legacy interface.
		 */
		public function to_switch() {

			// New interface yes/no.
			$is_new = '';

			// User new/prev.
			$user = '';

				/**
				 * For first-time users or those who switched to the new interface set $is_new to yes.
				 *
				 * Users who switch back to the previous interface or upgrade from an older
				 * version will keep $is_new set to no.
				 */
			$ccw_options = get_option( 'ccw_options' );

			if ( isset( $ccw_options['number'] ) ) {
				$user   = 'prev';
				$is_new = 'no';
			} else {
				// new user - new interface
				$user   = 'new';
				$is_new = 'yes';
			}

			// Previous user and if switched.
			if ( 'prev' === $user ) {

				$ht_ctc_switch = get_option( 'ht_ctc_switch' );

				if ( isset( $ht_ctc_switch['interface'] ) && 'yes' === $ht_ctc_switch['interface'] ) {
					$is_new = 'yes';
				}
			}

			// while testing
			// $is_new = 'yes';

			// Define HT_CTC_IS_NEW.
			if ( ! defined( 'HT_CTC_IS_NEW' ) ) {
				define( 'HT_CTC_IS_NEW', $is_new );
			}

			// Include related files.
			if ( 'yes' === HT_CTC_IS_NEW ) {
				// New interface.

				// Register hooks.
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/class-ht-ctc-register.php';
				register_activation_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'activate' ) );
				register_deactivation_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'deactivate' ) );
				register_uninstall_hook( HT_CTC_PLUGIN_FILE, array( 'HT_CTC_Register', 'uninstall' ) );

				// Include main file - new.
				include_once HT_CTC_PLUGIN_DIR . 'new/class-ht-ctc.php';

				/**
				 * Retrieve the singleton instance of the Click to Chat core class.
				 *
				 * @return HT_CTC
				 */
				function ht_ctc() {
					return HT_CTC::instance();
				}

				ht_ctc();

			} else {
				// Previous interface.

				// Include main file - previous.
				include_once HT_CTC_PLUGIN_DIR . 'prev/inc/class-ht-ccw.php';

				/**
				 * Retrieve the singleton instance of the legacy Click to Chat class.
				 *
				 * @return HT_CCW
				 */
				function ht_ccw() {
					return HT_CCW::instance();
				}

				ht_ccw();
			}
		}
	}

	new HT_CTC_Switch();

} // END class_exists check
