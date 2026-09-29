<?php
/**
 * Manage default option values stored in the database.
 *
 * @package Click_To_Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_DB' ) ) {

	/**
	 * Database management class for Click to Chat plugin.
	 */
	class HT_CTC_DB {

		/**
		 * Plugin options storage.
		 *
		 * @var array
		 */
		public $os = array();

		/**
		 * Constructor.
		 *
		 * Initializes database setup.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->db();
		}


		/**
		 * Populate default options where needed.
		 *
		 * Sets up all plugin option tables with default values.
		 *
		 * @return void
		 */
		public function db() {

			$this->os              = array();
			$ht_ctc_plugin_details = get_option( 'ht_ctc_plugin_details' );

			if ( is_array( $ht_ctc_plugin_details ) ) {
				$this->os = $ht_ctc_plugin_details;
			}

			// only if already installed - then only call db updater
			if ( isset( $ht_ctc_plugin_details['version'] ) ) {
				// @since 3.2.2
				include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-update-db.php';
			}

			$this->ht_ctc_othersettings();
			$this->ht_ctc_chat_options();
			$this->ht_ctc_s2();
			$this->ht_ctc_plugin_details();
			// $this->ht_ctc_one_time();
		}



		/**
		 * Initialize other settings options.
		 *
		 * Sets up animation, analytics, and feature toggle settings.
		 *
		 * @return void
		 */
		public function ht_ctc_othersettings() {

			$values = array(
				'an_type'     => 'no-animation',
				'an_delay'    => '0',
				'an_itr'      => '1',
				'show_effect' => 'no-show-effects',
				'amp'         => '1',
			);

			// new installs.
			if ( ! isset( $this->os['version'] ) ) {
				$values['show_effect'] = 'From Corner';

				// $values['google_analytics'] = '1';
				$values['g_an']            = 'ga4';
				$values['g_an_event_name'] = 'click to chat';

				// google analytics params
				$values['g_an_params'] = array(
					'g_an_param_1',
					'g_an_param_2',
					'g_an_param_3',
				);

				$values['g_an_param_1'] = array(
					'key'   => 'number',
					'value' => '{number}',
				);

				$values['g_an_param_2'] = array(
					'key'   => 'title',
					'value' => '{title}',
				);

				$values['g_an_param_3']     = array(
					'key'   => 'url',
					'value' => '{url}',
				);
				$values['g_an_param_order'] = '4';

				$values['gtm']            = '1';
				$values['gtm_event_name'] = 'Click to Chat';

				// gtm params
				$values['gtm_params'] = array(
					'gtm_param_1',
					'gtm_param_2',
					'gtm_param_3',
					'gtm_param_4',
					'gtm_param_5',
				);

				$values['gtm_param_1'] = array(
					'key'   => 'type',
					'value' => 'chat',
				);

				$values['gtm_param_2'] = array(
					'key'   => 'number',
					'value' => '{number}',
				);

				$values['gtm_param_3'] = array(
					'key'   => 'title',
					'value' => '{title}',
				);

				$values['gtm_param_4'] = array(
					'key'   => 'url',
					'value' => '{url}',
				);

				$values['gtm_param_5']     = array(
					'key'   => 'ref',
					'value' => 'dataLayer push',
				);
				$values['gtm_param_order'] = '6';

				$values['fb_pixel']                  = '1';
				$values['pixel_event_type']          = 'trackCustom';
				$values['pixel_custom_event_name']   = 'Click to Chat by HoliThemes';
				$values['pixel_standard_event_name'] = 'Lead';

				// pixel params
				$values['pixel_params'] = array(
					'pixel_param_1',
					'pixel_param_2',
					'pixel_param_3',
					'pixel_param_4',
				);

				$values['pixel_param_1'] = array(
					'key'   => 'Category',
					'value' => 'Click to Chat for WhatsApp',
				);

				$values['pixel_param_2'] = array(
					'key'   => 'ID',
					'value' => '{number}',
				);

				$values['pixel_param_3'] = array(
					'key'   => 'Title',
					'value' => '{title}',
				);

				$values['pixel_param_4']     = array(
					'key'   => 'URL',
					'value' => '{url}',
				);
				$values['pixel_param_order'] = '5';

			}

			$db_values = get_option( 'ht_ctc_othersettings', array() );
			$db_values = ( is_array( $db_values ) ) ? $db_values : array();

			$update_values = array_merge( $values, $db_values );
			update_option( 'ht_ctc_othersettings', $update_values );
		}





		/**
		 * Initialize chat options.
		 *
		 * Sets up main chat configuration including number, styles, and positioning.
		 *
		 * @return void
		 */
		public function ht_ctc_chat_options() {

			$values = array(
				'cc'                => '',
				'num'               => '',
				'number'            => '',
				'pre_filled'        => '',
				'call_to_action'    => 'WhatsApp us',
				'style_desktop'     => '2',
				'style_mobile'      => '2',

				'side_1'            => 'bottom',
				'side_1_value'      => '15px',
				'side_2'            => 'right',
				'side_2_value'      => '15px',

				// 'show_or_hide' => 'hide',
				'list_hideon_pages' => '',
				'list_hideon_cat'   => '',
				'list_showon_pages' => '',
				'list_showon_cat'   => '',

			);

			$options = get_option( 'ht_ctc_chat_options' );
			// mobile position if not set
			if ( ! isset( $options['mobile_side_1_value'] ) && ! isset( $options['mobile_side_2_value'] ) ) {
				$mobile_values = array(
					'mobile_side_1'       => ( isset( $options['side_1'] ) ) ? esc_attr( $options['side_1'] ) : 'bottom',
					'mobile_side_1_value' => ( isset( $options['side_1_value'] ) ) ? esc_attr( $options['side_1_value'] ) : '10px',
					'mobile_side_2'       => ( isset( $options['side_2'] ) ) ? esc_attr( $options['side_2'] ) : 'right',
					'mobile_side_2_value' => ( isset( $options['side_2_value'] ) ) ? esc_attr( $options['side_2_value'] ) : '10px',
				);
				$values        = array_merge( $values, $mobile_values );
			}

			// for new installs.
			if ( ! isset( $this->os['version'] ) ) {
				$values['same_settings']             = '1';
				$values['display_desktop']           = 'show';
				$values['display_mobile']            = 'show';
				$values['display']['global_display'] = 'show';
			}

			$db_values = get_option( 'ht_ctc_chat_options', array() );
			$db_values = ( is_array( $db_values ) ) ? $db_values : array();

			$update_values = array_merge( $values, $db_values );
			update_option( 'ht_ctc_chat_options', $update_values );
		}


		// styles

		/**
		 * Initialize style-2 options.
		 *
		 * Sets up green square icon style configuration.
		 *
		 * @return void
		 */
		public function ht_ctc_s2() {

			$style_2 = array(

				's2_img_size'   => '50px',
				'cta_textcolor' => '#ffffff',
				'cta_bgcolor'   => '#25D366',

			);

			// new install
			if ( ! isset( $this->os['version'] ) ) {
				$style_2['cta_type']      = 'hover';
				$style_2['cta_font_size'] = '15px';
			} else {
				$style_2['cta_type'] = 'hide';
			}

			$db_values = get_option( 'ht_ctc_s2', array() );
			$db_values = ( is_array( $db_values ) ) ? $db_values : array();

			$update_values = array_merge( $style_2, $db_values );
			update_option( 'ht_ctc_s2', $update_values );
		}



		/**
		 * Initialize plugin details and version tracking.
		 *
		 * Add plugin Details to db
		 * Add plugin version to db - useful for upgrading db - class-ht-ctc-update-db.php
		 *      update version value for each update.
		 *
		 * The first_install_time @since v3.7 ( if installed before v3.7 first_install_time will be the first plugin version upgrade time of v3.7 or + )
		 * v3, v3_2_5, v3_3_3, v3_3_5 - values changed to time @since v3.7.
		 */
		public function ht_ctc_plugin_details() {

			$time = time();

			// plugin details
			$values = array(
				'version'            => HT_CTC_VERSION,
				'first_version'      => HT_CTC_VERSION,
				'first_install_time' => $time,
				'v3'                 => $time,
				'v3_2_5'             => $time,
				'v3_3_3'             => $time,
				'v3_3_5'             => $time,
				'v3_7'               => $time,
				'v3_8'               => $time,
				'v3_9'               => $time,
				'v3_19'              => $time,
				'v3_23'              => $time,
				'v3_28'              => $time,
				'v3_31'              => $time,
				'v4_3'               => $time,
				'v4_34'              => $time,
				'v4_36'              => $time,
			);

			$db_values = get_option( 'ht_ctc_plugin_details', array() );
			$db_values = ( is_array( $db_values ) ) ? $db_values : array();

			// extra safe instead of directly merge.
			$update_values = $values;
			if ( is_array( $db_values ) ) {
				$update_values = array_merge( $values, $db_values );
			}

			/**
			 * IMP: have to update version number..
			 * (always use the latest value)
			 */
			$update_values['version'] = HT_CTC_VERSION;

			update_option( 'ht_ctc_plugin_details', $update_values );
		}
	}

	new HT_CTC_DB();

} // END class_exists check
