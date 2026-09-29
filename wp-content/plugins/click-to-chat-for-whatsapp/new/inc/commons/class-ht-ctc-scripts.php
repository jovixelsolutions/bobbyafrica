<?php
/**
 * Registers front-end styles and scripts.
 *
 * Loads the plugin assets with the appropriate WordPress hooks.
 *
 * @package Click_To_Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Scripts' ) ) {

	/**
	 * Manages Click to Chat asset registration.
	 */
	class HT_CTC_Scripts {

		/**
		 * Hook into WordPress to register assets.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Register WordPress hooks for enqueuing assets.
		 *
		 * @return void
		 */
		public function hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 1 );
		}

		/**
		 * Register styles for the front end.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function register_scripts() {

			$os = get_option( 'ht_ctc_othersettings' );
			$cb = get_option( 'ht_ctc_code_blocks' );

			/**
			 * If amp_is_request no need to add scripts.
			 *
			 * Note: amp_is_request should call after 'parse_query' action. so check here only. i.e. in wp_enqueue_scripts
			 * ref: https://amp-wp.org/reference/function/amp_is_request/
			 *
			 * @since 3.20
			 */
			if ( isset( $os['amp'] ) ) {
				if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
					return;
				}
			}

			// load_app_js_bottom
			$load_app_js_bottom = true;
			$wp_ver             = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : '1.0';

			$js_load = ( isset( $os['js_load'] ) ) ? esc_attr( $os['js_load'] ) : 'defer';

			if ( version_compare( $wp_ver, '6.3', '>=' ) ) {
				$load_app_js_bottom = array(
					'in_footer' => true,
					'strategy'  => $js_load,
				);
			}
			$load_app_js_bottom = apply_filters( 'ht_ctc_fh_load_app_js_bottom', $load_app_js_bottom );

			// js
			$css      = 'main.css';
			$js       = 'app.js';
			$woo_js   = 'woo.js';
			$group_js = 'group.js';
			$share_js = 'share.js';

			if ( defined( 'HT_CTC_DEBUG_MODE' ) ) {
				$css      = 'dev/main.dev.css';
				$js       = 'dev/app.dev.js';
				$woo_js   = 'dev/woo.dev.js';
				$group_js = 'dev/group.dev.js';
				$share_js = 'dev/share.dev.js';
			}

			// in v3.34 app.js is refactored. a lot of changed done. so added backward compatibility.
			if ( defined( 'HT_CTC_PRO_VERSION' ) && version_compare( HT_CTC_PRO_VERSION, '2.16', '<' ) ) {
				if ( defined( 'HT_CTC_DEBUG_MODE' ) ) {
					$js = 'bc/3-33.app.dev.js';
				} else {
					$js = 'bc/app.js';
				}
			}

			do_action( 'ht_ctc_ah_scripts_before' );

			// enqueue main.css
			wp_enqueue_style( 'ht_ctc_main_css', plugins_url( "new/inc/assets/css/$css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );

			// app.js for all (chat)
			wp_enqueue_script( 'ht_ctc_app_js', plugins_url( "new/inc/assets/js/$js", HT_CTC_PLUGIN_FILE ), array( 'jquery' ), HT_CTC_VERSION, $load_app_js_bottom );

			// woocommerce
			if ( class_exists( 'WooCommerce' ) ) {

				// if - cart layout option is checked.
				$woo_options = get_option( 'ht_ctc_woo_options' );

				if ( isset( $woo_options['woo_single_layout_cart_btn'] ) || isset( $woo_options['woo_shop_layout_cart_btn'] ) ) {
					wp_enqueue_script( 'ht_ctc_woo_js', plugins_url( "new/inc/assets/js/$woo_js", HT_CTC_PLUGIN_FILE ), array( 'jquery' ), HT_CTC_VERSION, $load_app_js_bottom );
				}
			}

			/**
			 * Custom css
			 * custom css code. ht_ctc_main_css - already enqueued above
			 * dont use esc_attr. quotes, .. may not work.
			 */
			$custom_css = ( isset( $cb['custom_css'] ) ) ? ( $cb['custom_css'] ) : '';

			if ( '' !== $custom_css ) {

				// Decode HTML entities (Fixes &quot; to ")
				$custom_css = html_entity_decode( $custom_css, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

				// Remove CSS Comments completely (Fixes the / text / bug)
				// We do this first so comments don't mess up regex or become garbage text.
				$custom_css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $custom_css );

				// Strip HTML tags (Security layer)
				$custom_css = wp_strip_all_tags( $custom_css );

				// Remove malicious "url" usage and "expression"
				// Removes url(...) entirely to prevent hidden XSS
				$custom_css = preg_replace( '/url\s*\((?:["\']?)(?:[^"\')]+)(?:["\']?)\)/i', '', $custom_css );
				// Removes expression(...)
				$custom_css = preg_replace( '/expression\s*\(/i', '', $custom_css );
				// Removes javascript: protocols
				$custom_css = preg_replace( '/javascript\s*:/i', '', $custom_css );

				// Remove malicious "alert", "confirm", "prompt"
				$custom_css = preg_replace(
					'/\b(alert|confirm|prompt)\s*\([^)]*\)/i',
					'',
					$custom_css
				);

				// Allow-list valid CSS characters
				// Note: We included '\*' (asterisk) so Universal Selectors work.
				$custom_css = preg_replace(
					'/[^a-zA-Z0-9\s\#\.\:\;\,\-\%\{\}\(\)\/\@\!\[\]\=\"\'_\*\>\+\~\&\\\]/',
					'',
					$custom_css
				);

				// Normalize whitespace (Compression)
				$custom_css = preg_replace( '/\s+/', ' ', trim( $custom_css ) );

				// Output
				if ( ! empty( $custom_css ) ) {
					wp_add_inline_style( 'ht_ctc_main_css', $custom_css );
				}
			}

			// group.js
			if ( isset( $os['enable_group'] ) ) {
				wp_enqueue_script( 'ht_ctc_group_js', plugins_url( "new/inc/assets/js/$group_js", HT_CTC_PLUGIN_FILE ), array( 'jquery', 'ht_ctc_app_js' ), HT_CTC_VERSION, $load_app_js_bottom );
			}

			// share.js
			if ( isset( $os['enable_share'] ) ) {
				wp_enqueue_script( 'ht_ctc_share_js', plugins_url( "new/inc/assets/js/$share_js", HT_CTC_PLUGIN_FILE ), array( 'jquery', 'ht_ctc_app_js' ), HT_CTC_VERSION, $load_app_js_bottom );
			}

			do_action( 'ht_ctc_ah_scripts_after' );
		}
	}


	new HT_CTC_Scripts();


} // END class_exists check
