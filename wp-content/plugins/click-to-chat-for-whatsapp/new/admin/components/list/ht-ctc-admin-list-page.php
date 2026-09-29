<?php
/**
 * List ..
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_List_Page' ) ) {

	/**
	 * Handles admin list page functionality.
	 */
	class HT_CTC_Admin_List_Page {

		/**
		 * Singleton instance.
		 *
		 * @var HT_CTC_Admin_List_Page|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return HT_CTC_Admin_List_Page
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Get available greetings templates.
		 *
		 * @return array Array of greetings template options.
		 */
		public function greetings_template() {

			/**
			 * Keys are like the file names (expect no)
			 * Note: dont inclued 'pro' keyword in this list.
			 */
			$values = array(
				'no'          => __( '-- No Greetings Dialog --', 'click-to-chat-for-whatsapp' ),
				'greetings-1' => __( 'Greetings-1 - Customizable Design', 'click-to-chat-for-whatsapp' ),
				'greetings-2' => __( 'Greetings-2 - Content Specific', 'click-to-chat-for-whatsapp' ),
			);

			$values = apply_filters( 'ht_ctc_fh_greetings_templates', $values );

			return $values;
		}
	}

} // END class_exists check
