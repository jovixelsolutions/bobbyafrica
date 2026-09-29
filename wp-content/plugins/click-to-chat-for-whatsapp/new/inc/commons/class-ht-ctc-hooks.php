<?php
/**
 * Hooks
 *
 * @since 2.8
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Hooks' ) ) {

	/**
	 * Registers hooks that drive Click to Chat behaviour.
	 */
	class HT_CTC_Hooks {

		/**
		 * Plugin version currently loaded.
		 *
		 * @var string
		 */
		public $version = HT_CTC_VERSION;

		/**
		 * Cached main Click to Chat options.
		 *
		 * @var array<string, mixed>|false
		 */
		public $main_options = false;

		/**
		 * Cached ancillary Click to Chat options.
		 *
		 * @var array<string, mixed>|false
		 */
		public $other_options = false;

		/**
		 * Boot hook registrations on construction.
		 */
		public function __construct() {
			$this->hooks();
			$this->main_options  = get_option( 'ht_ctc_main_options' );
			$this->other_options = get_option( 'ht_ctc_othersettings' );
		}

		/**
		 * Register action and filter callbacks.
		 */
		private function hooks() {
			// ## Action Hooks ##
			add_action( 'ht_ctc_ah_before_fixed_position', array( $this, 'comment' ) );
			add_action( 'ht_ctc_ah_before_fixed_position', array( $this, 'css_styles' ) );

			// ## Filter Hooks ##
			add_filter( 'ht_ctc_fh_chat', array( $this, 'chat_settings' ) );
			add_filter( 'ht_ctc_fh_load_app_js_bottom', array( $this, 'load_app_js_bottom' ) );

			// other settings
			add_filter( 'ht_ctc_fh_os', array( $this, 'other_settings' ) );
		}

		/**
		 * Action Hooks
		 */

		/**
		 * Output animation styles before the fixed-position container.
		 *
		 * @return void
		 */
		public function css_styles() {

			$othersettings      = get_option( 'ht_ctc_othersettings' );
			$greetings_settings = get_option( 'ht_ctc_greetings_settings' );

			// Entry effects
			// check: - entry effect - 'from center', 'from corner' - have to make work as similar to other effects
			$entry = ( isset( $othersettings['show_effect'] ) ) ? esc_attr( $othersettings['show_effect'] ) : '';

			// if greetings dialog is modal, then dont add animations. its causing position issue i.e. due to animation-fill-mode: both;
			$is_greetings_modal = 'no';
			if ( isset( $greetings_settings['g_position'] ) && 'modal' === $greetings_settings['g_position'] ) {
				$is_greetings_modal = 'yes';
			}

			if ( '' !== $entry && 'no-show-effects' !== $entry && 'no' === $is_greetings_modal ) {
				// if ( '' !== $entry && 'no-show-effects' !== $entry && 'From Corner' !== $entry ) {

				$an_duration = '1s';
				$an_delay    = '0s';
				$an_itr      = '1';

				if ( 'From Center' === $entry ) {
					$entry = 'center';
				}

				// From Corner animation handle from js
				if ( 'From Corner' === $entry ) {
					$entry       = 'corner';
					$an_duration = '0.4s';
				}

				include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-animations.php';
				$animations = new HT_CTC_Animations();
				// $entry is a callback function name
				$animations->entry( $entry, $an_duration, $an_delay, $an_itr );
			}

			// Animation styles
			$an_type = ( isset( $othersettings['an_type'] ) ) ? esc_attr( $othersettings['an_type'] ) : '';

			if ( '' !== $an_type && 'no-animation' !== $an_type && 'no' === $is_greetings_modal ) {

				$an_duration = '1s';
				$an_delay    = ( isset( $othersettings['an_delay'] ) ) ? esc_attr( $othersettings['an_delay'] ) : '0';
				$an_delay    = "{$an_delay}s";
				$an_itr      = ( isset( $othersettings['an_itr'] ) ) ? esc_attr( $othersettings['an_itr'] ) : '1';

				include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-animations.php';
				$animations = new HT_CTC_Animations();
				$animations->animations( $an_type, $an_duration, $an_delay, $an_itr );
			}
		}

		/**
		 * Print a marker comment identifying plugin output.
		 *
		 * phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		 *
		 * @return void
		 */
		public function comment() {
			?>
		<!-- Click to Chat - https://holithemes.com/plugins/click-to-chat/  v<?php echo esc_attr( $this->version ); ?> -->
			<?php
		}


		/**
		 * Filter Hooks
		 */

		/**
		 * Chat settings filter.
		 *
		 * Normalises number formats and applies fallbacks for legacy values.
		 *
		 * @param array<string, mixed> $ht_ctc_chat Chat configuration data.
		 * @return array<string, mixed> Filtered configuration array.
		 */
		public function chat_settings( $ht_ctc_chat ) {

			// Number format
			if ( isset( $ht_ctc_chat['number'] ) ) {

				if ( isset( $ht_ctc_chat['intl'] ) && class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'wa_number' ) ) {
					$ht_ctc_chat['number'] = HT_CTC_Formatting::wa_number( $ht_ctc_chat['number'] );
				} else {
					// fallback if intl type input is not set. i.e. set number before intl input feature added
					$ht_ctc_chat['number'] = preg_replace( '/\D/', '', $ht_ctc_chat['number'] );
					$ht_ctc_chat['number'] = ltrim( $ht_ctc_chat['number'], '0' );
				}
			}

			return $ht_ctc_chat;
		}

		/**
		 * Filter additional settings (animations, analytics, classes, etc.).
		 *
		 * @param array<string, mixed> $ht_ctc_os Settings array passed to the front end.
		 * @return array<string, mixed> Normalised settings array.
		 */
		public function other_settings( $ht_ctc_os ) {

			$othersettings = get_option( 'ht_ctc_othersettings' );

			$ht_ctc_os['v'] = HT_CTC_VERSION;

			$ht_ctc_os['is_ga_enable'] = ( isset( $othersettings['g_an'] ) ) ? 'yes' : 'no';
			// $ht_ctc_os['is_ga_enable'] = (isset( $othersettings['google_analytics'] )) ? 'yes' : 'no';
			// $ht_ctc_os['ga4'] = (isset( $othersettings['ga4'] )) ? 'yes' : 'no';

			$ht_ctc_os['is_fb_pixel'] = ( isset( $othersettings['fb_pixel'] ) ) ? 'yes' : 'no';
			$ht_ctc_os['ga_ads']      = ( isset( $othersettings['ga_ads'] ) ) ? 'yes' : 'no';

			if ( ! isset( $ht_ctc_os['data-attributes'] ) ) {
				$ht_ctc_os['data-attributes'] = '';
			} else {
				$ht_ctc_os['data-attributes'] = (string) $ht_ctc_os['data-attributes'];
			}

			$ht_ctc_os['show_effect'] = ( isset( $othersettings['show_effect'] ) ) ? esc_attr( $othersettings['show_effect'] ) : '';

			// show effect ? if 'From Corner' - then return time (for other effects - this->css_styles() handles)
			if ( 'From Corner' === $ht_ctc_os['show_effect'] ) {
				$ht_ctc_os['show_effect'] = 150;
			}

			// Animations
			$ht_ctc_os['an_type'] = 'no-animation';
			// no-animation/bounce/flash/fade/flip/slide/zoom..
			$an_type = ( isset( $othersettings['an_type'] ) ) ? esc_attr( $othersettings['an_type'] ) : 'no-animation';

			if ( 'no-animation' !== $an_type ) {
				// @used by group/share
				$ht_ctc_os['data-attributes'] .= "data-an_type='ht_ctc_an_$an_type' ";

				$ht_ctc_os['an_type'] = "ht_ctc_an_$an_type";
			}

			// class names - animations, entry effects, ..
			$entry = ( isset( $othersettings['show_effect'] ) ) ? esc_attr( $othersettings['show_effect'] ) : 'no-show-effects';

			/**
			 * Entry effect - add class name only
			 * reqular animation type added from js.
			 */
			if ( '' !== $entry && 'no-show-effects' !== $entry ) {

				if ( 'From Center' === $entry ) {
					$entry = 'center';
				}

				if ( 'From Corner' === $entry ) {
					$entry = 'corner';
				}

				// if $entry is not corner or center the return
				// if ( 'corner' === $entry || 'center' === $entry ) {
				$ht_ctc_os['class_names']  = ( isset( $ht_ctc_os['class_names'] ) ) ? esc_attr( $ht_ctc_os['class_names'] ) : '';
				$ht_ctc_os['class_names'] .= " ht_ctc_entry_animation ht_ctc_an_entry_$entry";
				// }

			}

			// Aria-hidden = true
			if ( isset( $othersettings['aria'] ) ) {
				$ht_ctc_os['data-attributes'] .= ' aria-hidden=true ';
				$ht_ctc_os['attributes']       = ' aria-hidden=true';
			}

			return $ht_ctc_os;
		}



		/**
		 * Allow compatibility adjustments to the script loading strategy.
		 *
		 * @param bool|array<string, mixed> $load_app_js_bottom Script enqueue arguments.
		 * @return bool|array<string, mixed> Potentially modified enqueue arguments.
		 */
		public function load_app_js_bottom( $load_app_js_bottom ) {

			// compatibility
			// autoptimize cache plugin
			if ( class_exists( 'autoptimizeCache' ) ) {
				$load_app_js_bottom = false;
			}
			return $load_app_js_bottom;
		}
	}

	new HT_CTC_Hooks();

} // END class_exists check
