<?php
/**
 * Settings data helpers.
 *
 * @package Click_To_Chat
 * @subpackage commons
 *
 * todo
 *  security
 *  sanitize
 *  validation
 *  error handling
 *  logging
 *  caching
 *  have to test with pro version
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Data' ) ) {

	/**
	 * Provides computed settings data for frontend usage.
	 */
	class HT_CTC_Settings_Data {


		// public function __construct() {
		// }

		/**
		 * Build chat variables array used on the frontend.
		 *
		 * @param int|null $page_id Optional page ID for page-level overrides.
		 * @return array
		 */
		public static function get_ht_ctc_chat_var( $page_id = null ) {

			$ctc         = array();
			$ht_ctc_chat = array();
			$ht_ctc_os   = array();

			$options            = get_option( 'ht_ctc_chat_options', array() );
			$othersettings      = get_option( 'ht_ctc_othersettings', array() );
			$greetings          = get_option( 'ht_ctc_greetings_options', array() );
			$greetings_settings = get_option( 'ht_ctc_greetings_settings', array() );

			// if any of the options are not array return an empty array
			if ( ! is_array( $options ) || ! is_array( $othersettings ) || ! is_array( $greetings ) || ! is_array( $greetings_settings ) ) {
				return $ctc;
			}

			// page level
			// no page level settings if getting values using rest api
			$ht_ctc_pagelevel = array();

			// Add page-level overrides
			if ( $page_id && get_post_status( $page_id ) ) {
				$ht_ctc_pagelevel = get_post_meta( $page_id, 'ht_ctc_pagelevel', true );
			}

			// number
			$ht_ctc_chat['number'] = ( isset( $options['number'] ) ) ? esc_attr( $options['number'] ) : '';

			// safe side action .. if number not saved in new method
			if ( '' === $ht_ctc_chat['number'] ) {
				$cc  = ( isset( $options['cc'] ) ) ? esc_attr( $options['cc'] ) : '';
				$num = ( isset( $options['num'] ) ) ? esc_attr( $options['num'] ) : '';
				if ( '' !== $cc && '' !== $num ) {
					$ht_ctc_chat['number'] = $cc . $num;
				}
			}

			$ht_ctc_chat['pre_filled'] = isset( $options['pre_filled'] ) ? esc_attr( $options['pre_filled'] ) : '';

			$ht_ctc_chat['url_target_d']    = ( isset( $options['url_target_d'] ) ) ? esc_attr( $options['url_target_d'] ) : '_blank';
			$ht_ctc_chat['url_structure_d'] = ( isset( $options['url_structure_d'] ) ) ? esc_attr( $options['url_structure_d'] ) : '';
			$ht_ctc_chat['url_structure_m'] = ( isset( $options['url_structure_m'] ) ) ? esc_attr( $options['url_structure_m'] ) : '';

			$ht_ctc_chat['display_mobile']  = ( isset( $options['display_mobile'] ) ) ? esc_attr( $options['display_mobile'] ) : 'show';
			$ht_ctc_chat['display_desktop'] = ( isset( $options['display_desktop'] ) ) ? esc_attr( $options['display_desktop'] ) : 'show';

			$notification_time = ( isset( $othersettings['notification_time'] ) ) ? esc_attr( $othersettings['notification_time'] ) : '';

			// z-index. have to be numeric value.
			$zindex_raw = isset( $othersettings['zindex'] ) ? $othersettings['zindex'] : '';
			$zindex     = ( is_scalar( $zindex_raw ) && is_numeric( trim( $zindex_raw ) ) ) ? trim( $zindex_raw ) : '99999999';
			$zindex     = esc_attr( $zindex );

			$analytics = ( isset( $othersettings['analytics'] ) ) ? esc_attr( $othersettings['analytics'] ) : 'all';

			$ht_ctc_chat['css'] = "display: none; cursor: pointer; z-index: $zindex;";

			$default_position = '';
			$position         = '';
			$position_mobile  = '';
			include HT_CTC_PLUGIN_DIR . 'new/inc/commons/position-to-place.php';

			// position: e.g. position: fixed; bottom: 15px; right: 15px;
			$ht_ctc_chat['position']        = $position;
			$ht_ctc_chat['position_mobile'] = $position_mobile;

			// Greetings - init display ..
			$g_init = isset( $greetings_settings['g_init'] ) ? esc_attr( $greetings_settings['g_init'] ) : 'default';

			// webhook
			$hook_url       = isset( $othersettings['hook_url'] ) ? esc_attr( $othersettings['hook_url'] ) : '';
			$webhook_format = isset( $othersettings['webhook_format'] ) ? esc_attr( $othersettings['webhook_format'] ) : 'json';

			$g_an_event_name = ( isset( $othersettings['g_an_event_name'] ) ) ? esc_attr( $othersettings['g_an_event_name'] ) : 'click to chat';

			$pixel_event_type = ( isset( $othersettings['pixel_event_type'] ) ) ? esc_attr( $othersettings['pixel_event_type'] ) : 'trackCustom';
			$pixel_event_name = 'Click to Chat by HoliThemes';
			if ( 'trackCustom' === $pixel_event_type ) {
				if ( isset( $othersettings['pixel_custom_event_name'] ) && '' !== $othersettings['pixel_custom_event_name'] ) {
					$pixel_event_name = esc_attr( $othersettings['pixel_custom_event_name'] );
				}
			} elseif ( isset( $othersettings['pixel_standard_event_name'] ) && '' !== $othersettings['pixel_standard_event_name'] ) {
					// lead, ..
					$pixel_event_name = esc_attr( $othersettings['pixel_standard_event_name'] );
			}

			// multilingual support
			if ( function_exists( 'wpml_translate_single_string' ) ) {
				$ht_ctc_chat['number']     = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['number'], 'Click to Chat for WhatsApp', 'number' );
				$ht_ctc_chat['pre_filled'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['pre_filled'], 'Click to Chat for WhatsApp', 'pre_filled' );
			}

			// page level settings (have to be after the multilingual)
			if ( isset( $ht_ctc_pagelevel['number'] ) ) {
				$ht_ctc_chat['number'] = esc_attr( $ht_ctc_pagelevel['number'] );
			}
			if ( isset( $ht_ctc_pagelevel['pre_filled'] ) ) {
				$ht_ctc_chat['pre_filled'] = esc_attr( $ht_ctc_pagelevel['pre_filled'] );
			}

			// might overwrite at filter hooks

			// analytics
			$ht_ctc_os['is_ga_enable'] = 'yes';
			$ht_ctc_os['is_fb_pixel']  = 'yes';
			$ht_ctc_os['ga_ads']       = 'no';

			// show effect
			$ht_ctc_os['show_effect'] = '';
			$ht_ctc_os['an_type']     = '';

			$ht_ctc_chat['schedule'] = 'no';

			// hooks
			include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-hooks.php';
			$ht_ctc_chat = apply_filters( 'ht_ctc_fh_chat', $ht_ctc_chat );
			$ht_ctc_os   = apply_filters( 'ht_ctc_fh_os', $ht_ctc_os );

			// settings to return to include at ht_ctc_chat_var
			$ctc = array(
				'number'           => esc_attr( $ht_ctc_chat['number'] ),
				'pre_filled'       => esc_attr( $ht_ctc_chat['pre_filled'] ),
				'url_target_d'     => esc_attr( $ht_ctc_chat['url_target_d'] ),
				'dis_d'            => esc_attr( $ht_ctc_chat['display_desktop'] ),
				'dis_m'            => esc_attr( $ht_ctc_chat['display_mobile'] ),
				'pos_d'            => esc_attr( $ht_ctc_chat['position'] ),
				'pos_m'            => esc_attr( $ht_ctc_chat['position_mobile'] ),
				'css'              => esc_attr( $ht_ctc_chat['css'] ),
				'schedule'         => esc_attr( $ht_ctc_chat['schedule'] ),
				'se'               => esc_attr( $ht_ctc_os['show_effect'] ),
				'ani'              => esc_attr( $ht_ctc_os['an_type'] ),
				'g_init'           => esc_attr( $g_init ),
				'g_an_event_name'  => esc_attr( $g_an_event_name ),
				'pixel_event_name' => esc_attr( $pixel_event_name ),
			);

			// desktop url structure if web whatsapp
			if ( 'web' === $ht_ctc_chat['url_structure_d'] ) {
				$ctc['url_structure_d'] = 'web';
			}

			// mobile url structure if whatsapp://..
			if ( 'wa_colon' === $ht_ctc_chat['url_structure_m'] ) {
				$ctc['url_structure_m'] = 'wa_colon';
			}

			// notification time
			if ( '' !== $notification_time ) {
				$ctc['n_time'] = $notification_time;
			}

			// if no number content is added. i.e. if no number is set in the plugin settings.
			if ( '' === $ht_ctc_chat['number'] ) {
				$ctc['no_number'] = __( 'No WhatsApp Number Found!', 'click-to-chat-for-whatsapp' );
			}

			// anlalytics count type
			if ( 'session' === $analytics ) {
				$ctc['analytics'] = $analytics;
			}

			// ga - is google analytics enabled
			if ( 'yes' === $ht_ctc_os['is_ga_enable'] ) {
				$ctc['ga'] = 'yes';
			}

			// ads - is google ads enabled
			if ( 'yes' === $ht_ctc_os['ga_ads'] ) {
				$ctc['ads'] = 'yes';
			}

			// fb pixel
			if ( 'yes' === $ht_ctc_os['is_fb_pixel'] ) {
				$ctc['fb'] = 'yes';
			}

			// Add custom URL fields for Main version
			if ( isset( $options['custom_url_d'] ) && '' !== $options['custom_url_d'] ) {
				$ctc['custom_url_d'] = esc_url_raw( $options['custom_url_d'] );
			}
			if ( isset( $options['custom_url_m'] ) && '' !== $options['custom_url_m'] ) {
				$ctc['custom_url_m'] = esc_url_raw( $options['custom_url_m'] );
			}

			// webhook
			if ( '' !== $hook_url ) {
				// $ctc hook url
				$ctc['hook_url'] = $hook_url;
				$hook_v          = isset( $othersettings['hook_v'] ) ? $othersettings['hook_v'] : '';

				if ( is_array( $hook_v ) ) {
					$hook_v = array_filter( $hook_v );
					$hook_v = array_values( $hook_v );
					$hook_v = array_map( 'esc_attr', $hook_v );

					if ( isset( $hook_v[0] ) ) {
						// $ctc - hook values
						$ctc['hook_v'] = $hook_v;
					}
				}
			}

			// webhook sharing data type. - json, stringify json
			if ( 'json' === $webhook_format ) {
				$ctc['webhook_format'] = 'json';
			}

			// notification time
			if ( '' !== $notification_time ) {
				$ctc['n_time'] = $notification_time;
			}

			// Greetings - display device based (if not all then add value)
			$g_device = isset( $greetings_settings['g_device'] ) ? esc_attr( $greetings_settings['g_device'] ) : 'all';
			if ( 'all' !== $g_device ) {
				$ctc['g_device'] = $g_device;
			}

			// filter hooks - ctc - ht_ctc_chat_var
			$ctc = apply_filters( 'ht_ctc_fh_ctc', $ctc );

			return $ctc;
		}

		/**
		 * Retrieve HT CTC variables for front-end use.
		 *
		 * @return array
		 */
		public static function get_ht_ctc_variables() {

			$othersettings = get_option( 'ht_ctc_othersettings' );
			$options       = get_option( 'ht_ctc_chat_options', array() );

			$g_an_event_name = ( isset( $othersettings['g_an_event_name'] ) ) ? esc_attr( $othersettings['g_an_event_name'] ) : 'click to chat';

			$pixel_event_type = ( isset( $othersettings['pixel_event_type'] ) ) ? esc_attr( $othersettings['pixel_event_type'] ) : 'trackCustom';
			$pixel_event_name = 'Click to Chat by HoliThemes';
			if ( 'trackCustom' === $pixel_event_type ) {
				if ( isset( $othersettings['pixel_custom_event_name'] ) && '' !== $othersettings['pixel_custom_event_name'] ) {
					$pixel_event_name = esc_attr( $othersettings['pixel_custom_event_name'] );
				}
			} elseif ( isset( $othersettings['pixel_standard_event_name'] ) && '' !== $othersettings['pixel_standard_event_name'] ) {
					// lead, ..
					$pixel_event_name = esc_attr( $othersettings['pixel_standard_event_name'] );
			}

			$g_an_params  = ( isset( $othersettings['g_an_params'] ) && is_array( $othersettings['g_an_params'] ) ) ? array_map( 'esc_attr', $othersettings['g_an_params'] ) : '';
			$pixel_params = ( isset( $othersettings['pixel_params'] ) && is_array( $othersettings['pixel_params'] ) ) ? array_map( 'esc_attr', $othersettings['pixel_params'] ) : '';

			$g_an_value = ( isset( $options['g_an'] ) ) ? esc_attr( $options['g_an'] ) : 'ga4';

			$values = array(
				'g_an_event_name'  => $g_an_event_name,
				'pixel_event_type' => $pixel_event_type,
				'pixel_event_name' => $pixel_event_name,
			);

			// google analytics params
			if ( is_array( $g_an_params ) && isset( $g_an_params[0] ) ) {

				foreach ( $g_an_params as $param ) {
					$param_options = ( isset( $othersettings[ $param ] ) ) ? $othersettings[ $param ] : array();
					$key           = ( isset( $param_options['key'] ) ) ? esc_attr( $param_options['key'] ) : '';
					$value         = ( isset( $param_options['value'] ) ) ? esc_attr( $param_options['value'] ) : '';

					if ( ! empty( $key ) && ! empty( $value ) ) {
						$values['g_an_params'][] = $param;
						$values[ $param ]        = array(
							'key'   => $key,
							'value' => $value,
						);
					}
				}
			}

			// pixel params
			if ( is_array( $pixel_params ) && isset( $pixel_params[0] ) ) {

				foreach ( $pixel_params as $param ) {
					$param_options = ( isset( $othersettings[ $param ] ) ) ? $othersettings[ $param ] : array();
					$key           = ( isset( $param_options['key'] ) ) ? esc_attr( $param_options['key'] ) : '';
					$value         = ( isset( $param_options['value'] ) ) ? esc_attr( $param_options['value'] ) : '';

					if ( ! empty( $key ) && ! empty( $value ) ) {
						$values['pixel_params'][] = $param;
						$values[ $param ]         = array(
							'key'   => $key,
							'value' => $value,
						);
					}
				}
			}

			// filter hook - values - ht_ctc_variables
			$values = apply_filters( 'ht_ctc_fh_variables', $values );

			return $values;
		}
	}

	// new HT_CTC_Settings_Data();

} // END class_exists check
