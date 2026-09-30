<?php
/**
 * Settings Form - pro_features Group
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Pro_Features' ) ) {
	/**
	 * Pro features settings class.
	 */
	class HT_CTC_Settings_Pro_Features {
		/**
		 * Retrieve fields settings for the settings page.
		 *
		 * @return array
		 */
		public static function fields() {

			// The panel is a class (not a bare template) so it can be rendered more
			// than once in a request: load_file() uses require_once, which would make
			// a second include a silent no-op and hand back an empty tab.
			$html_content = '';

			if ( HT_CTC_Utils::load_class( 'new/admin2/views/panels/class-ht-ctc-pro-features-panel.php', 'HT_CTC_Pro_Features_Panel' ) ) {
				// Output buffering to grab the isolated layout's html.
				ob_start();
				HT_CTC_Pro_Features_Panel::render();
				$html_content = ob_get_clean();
			}

			$value = array(
				array(
					'field_type' => 'block_raw_html',
					'content'    => $html_content,
				),
			);

			return $value;
		}
	}
}
