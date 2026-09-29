<?php
/**
 * Provides access to stored Click to Chat values.
 *
 * Retrieves the plugin options from the database for reuse across shortcodes and features.
 *
 * @package Click_To_Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Values' ) ) {

	/**
	 * Handles retrieval of Click to Chat option values.
	 */
	class HT_CTC_Values {

		/**
		 * Chat options loaded from the database.
		 *
		 * @var array
		 */
		public $chat;

		/**
		 * Initialize the values helper.
		 */
		public function __construct() {
			$this->chat_fn();
		}

		/**
		 * Load chat options from the database.
		 *
		 * @return void
		 */
		public function chat_fn() {
			$this->chat = get_option( 'ht_ctc_chat_options' );
		}
	}

} // END class_exists check
