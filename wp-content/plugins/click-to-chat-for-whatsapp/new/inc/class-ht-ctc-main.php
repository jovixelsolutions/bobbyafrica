<?php
/**
 * Bootstrap the Click to Chat front-end features.
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Main' ) ) {

	/**
	 * Coordinates front-end feature loading.
	 */
	class HT_CTC_Main {

		/**
		 * Initialise the main loader.
		 */
		public function __construct() {
			$this->enables();
		}

		/**
		 * Load enabled front-end feature modules.
		 *
		 * @return void
		 */
		public function enables() {

			// 'chat' feature
			include_once HT_CTC_PLUGIN_DIR . 'new/inc/chat/class-ht-ctc-chat.php';
			include_once HT_CTC_PLUGIN_DIR . 'new/inc/chat/class-ht-ctc-chat-shortcode.php';

			$options = get_option( 'ht_ctc_othersettings' );

			// if enable call 'group' feature
			if ( isset( $options['enable_group'] ) ) {
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/group/class-ht-ctc-group.php';
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/group/class-ht-ctc-group-shortcode.php';
			}

			// if enabled call 'share' feature
			if ( isset( $options['enable_share'] ) ) {
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/share/class-ht-ctc-share.php';
				include_once HT_CTC_PLUGIN_DIR . 'new/inc/share/class-ht-ctc-share-shortcode.php';
			}
		}
	}

	new HT_CTC_Main();

} // END class_exists check
