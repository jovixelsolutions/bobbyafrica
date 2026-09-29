<?php
/**
 * Default values: Greetings
 *
 * @since 3.9
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Defaults_Greetings' ) ) {

	/**
	 * Provides default values for greetings settings.
	 */
	class HT_CTC_Defaults_Greetings {

		/**
		 * Default greetings values.
		 *
		 * @var array
		 */
		public $greetings = '';

		/**
		 * Default greetings-1 values.
		 *
		 * @var array
		 */
		public $g_1 = '';

		/**
		 * Default greetings-2 values.
		 *
		 * @var array
		 */
		public $g_2 = '';

		/**
		 * Default greetings settings values.
		 *
		 * @var array
		 */
		public $g_settings = '';

		/**
		 * Initialize default values.
		 */
		public function __construct() {
			$this->defaults();
		}

		/**
		 * Set all default values.
		 */
		public function defaults() {
			$this->greetings  = $this->greetings();
			$this->g_1        = $this->g_1();
			$this->g_2        = $this->g_2();
			$this->g_settings = $this->g_settings();
		}


		/**
		 * Greetings - default values for greetings
		 * online symobol green dot: &#128994;
		 */
		public function greetings() {

			$values = array(
				'greetings_template'           => 'no',
				'header_content'               => '<p><span style="color: #ffffff;font-size: 17px;font-weight:500;">{site}</span></p><p><span style="color: #ffffff;font-size: 12px;">Typically replies within minutes</span></p>',
				'main_content'                 => '<span style="font-size:14px;">Any questions related to {title}?</span>',
				'bottom_content'               => '<p style="text-align: center;"><span style="font-size: 12px;">Online | Privacy policy</span></p>',
				'call_to_action'               => 'WhatsApp Us',
				'g_header_online_status_color' => '#06e376',
				'g_header_online_status'       => '1',
				'g_device'                     => 'all',
				'g_init'                       => 'default',
			);

			return $values;
		}

		/**
		 * Get default values for greetings-1.
		 *
		 * @return array Default greetings-1 configuration.
		 */
		public function g_1() {

			$values = array(
				'header_bg_color'      => '#075e54',
				'main_bg_color'        => '#ece5dd',
				'message_box_bg_color' => '#dcf8c6',
				'main_bg_image'        => '1',
				'cta_style'            => '7_1',
			);

			return $values;
		}

		/**
		 * Get default values for greetings-2.
		 *
		 * @return array Default greetings-2 configuration.
		 */
		public function g_2() {

			$values = array(
				'bg_color' => '#ffffff',
			);

			return $values;
		}

		/**
		 * G_first_setup - (not using) - version number (if new g setup). if already g setup done. then this default values wont run, so no value not set or blank.
		 */
		public function g_settings() {

			$values = array(
				'opt_in'        => 'Accept Privacy Policy',
				'g_size'        => 'm',
				'g_first_setup' => HT_CTC_VERSION,
			);

			return $values;
		}
	}

	new HT_CTC_Defaults_Greetings();

} // END class_exists check
