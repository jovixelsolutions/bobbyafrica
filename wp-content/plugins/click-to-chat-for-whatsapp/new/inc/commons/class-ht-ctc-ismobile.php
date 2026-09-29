<?php
/**
 * Find mobile device or not ..
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_IsMobile' ) ) {

	/**
	 * Mobile device detection class.
	 */
	class HT_CTC_IsMobile {

		/**
		 * Return is mobile or not
		 * while using in condition check with 1 not with 2
		 *
		 * @var int - if mobile : 1 ?  2
		 */
		public $is_mobile;

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->is_mobile = $this->is_mobile();
		}


		/**
		 * Check if current device is mobile.
		 *
		 * Uses wp_is_mobile() if available, falls back to custom detection.
		 *
		 * @return string 'yes' if mobile device, 'no' otherwise.
		 */
		public function is_mobile() {

			if ( function_exists( 'wp_is_mobile' ) ) {
				if ( wp_is_mobile() ) {
					$this->is_mobile = 'yes';
					return $this->is_mobile;
				} else {
					$this->is_mobile = 'no';
					return $this->is_mobile;
				}
			} elseif ( $this->php_is_mobile() ) {
				// added like this  -  an user mention that wp_is_mobile uncauched error
				$this->is_mobile = 'yes';
				return $this->is_mobile;
			} else {
				$this->is_mobile = 'no';
				return $this->is_mobile;
			}
		}


		/**
		 * PHP-based mobile device detection.
		 *
		 * Fallback method when wp_is_mobile() is not available.
		 * Uses user agent string pattern matching.
		 *
		 * @return int 1 if mobile device detected, 0 otherwise.
		 */
		public function php_is_mobile() {
			// return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			return preg_match( '/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|boost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $user_agent );
		}
	}

} // END class_exists check
