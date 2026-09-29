<?php
/**
 * When plugin upgrades
 *
 * Update the db values to compatibile with in versions
 *
 * @package Click_To_Chat
 * @since 3.2.2
 * @from ht-ctc-db.php -> db()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Update_DB' ) ) {

	/**
	 * Handles database migrations on plugin updates.
	 */
	class HT_CTC_Update_DB {


		/**
		 * Initialize update routine.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->ht_ctc_updatedb();
		}


		/**
		 * Update db - First
		 *
		 * @since 3.2.2 ( intiall 3.0, later 3.2.2 moved form class-ht-ctc-db.php )
		 */
		public function ht_ctc_updatedb() {

			$ht_ctc_plugin_details = get_option( 'ht_ctc_plugin_details' );

			// only if already installed.
			if ( isset( $ht_ctc_plugin_details['version'] ) ) {

				// v3: if not yet updated to v3 or above  (in v3 $ht_ctc_plugin_details['v3'] is not added)
				if ( ! isset( $ht_ctc_plugin_details['v3'] ) ) {
					$this->v3_update();
				}

				// v3.3.3: if not yet updated to v3.3.3 or above  (in v3 $ht_ctc_plugin_details['v3_3_3'] is not added)
				if ( ! isset( $ht_ctc_plugin_details['v3_3_3'] ) ) {
					$this->v3_3_3_update_woo();
					$this->v3_3_3_update_show_hide_chat();
					$this->v3_3_3_update_show_hide_group();
					$this->v3_3_3_update_show_hide_share();
				}

				// v3.3.5: if not yet updated to v3.3.5 or above  (in v3.3.5 $ht_ctc_plugin_details['v3_3_5'] is not added)
				if ( ! isset( $ht_ctc_plugin_details['v3_3_5'] ) ) {
					$this->v3_3_5_update();
				}

				/**
				 * V3.23: if not yet updated to v3.23 or above  (in v3 $ht_ctc_plugin_details['v3'] is not added)
				 */
				if ( ! isset( $ht_ctc_plugin_details['v3_23'] ) ) {
					$this->v3_23_update();
				}

				/**
				 * V3.31: if not yet updated to v3.31 or above
				 */
				if ( ! isset( $ht_ctc_plugin_details['v3_31'] ) ) {
					$this->v3_31_update();
				}

				/**
				 * V4.34: if not yet updated to v4.34 or above
				 */
				if ( ! isset( $ht_ctc_plugin_details['v4_34'] ) ) {
					$this->v4_34_update();
				}

				/**
				 * V4.36: if not yet updated to v4.36 or above
				 */
				if ( ! isset( $ht_ctc_plugin_details['v4_36'] ) ) {
					$this->v4_36_update();
				}
			}
		}


		/**
		 * Database updates..
		 */



		/**
		 * Updating to v4.36 or above
		 *
		 * 4.36 changes.
		 * Google Analytics params, gtm params, pixel params are added to db. when upgrades.
		 *
		 * google analytis setting for parameters added in approx. v3.31.
		 */
		public function v4_36_update() {

			$os = get_option( 'ht_ctc_othersettings', array() );

			// Ensure $os is an array to prevent errors.
			if ( ! is_array( $os ) ) {
				$os = array();
			}

			$new_data = array(); // hold new structure data

			/**
			 * Migration Logic:
			 * We check if the parameters exist in the DB.
			 * We also check 'parms_saved' (for GA/Pixel) and 'parms_saved_2' (for GTM).
			 *
			 * 'parms_saved'/'parms_saved_2' are hidden fields saved when the user submits the settings form.
			 * If these flags exist, it means the user has explicitly saved the settings at some point.
			 * In that case, we TRUST the database (even if params are empty, the user might have deleted them intentionally).
			 *
			 * If these flags DO NOT exist, it means the user is likely running on default settings (runtime defaults).
			 * In this case, we populate the DB with those defaults to maintain behavior now that runtime generation is removed.
			 */

			// 1. Google Analytics Params
			// Check if params are missing AND user hasn't actively saved settings before (backward compatibility).
			// isset check is safe because $os is guaranteed strictly to be an array above.
			if ( ! isset( $os['g_an_params'] ) && ! isset( $os['parms_saved'] ) ) {

				$g_an_value = ( isset( $os['g_an'] ) ) ? esc_attr( $os['g_an'] ) : 'ga4';

				if ( 'ga' === $g_an_value ) {
					// Legacy Google Analytics (Universal Analytics) defaults
					$new_data['g_an_params'] = array(
						'g_an_param_1',
						'g_an_param_2',
					);

					$new_data['g_an_param_1'] = array(
						'key'   => 'event_category',
						'value' => 'Click to Chat for WhatsApp',
					);

					$new_data['g_an_param_2'] = array(
						'key'   => 'event_label',
						'value' => '{title}, {url}',
					);

				} else {
					// GA4 defaults
					$new_data['g_an_params'] = array(
						'g_an_param_1',
						'g_an_param_2',
						'g_an_param_3',
					);

					$new_data['g_an_param_1'] = array(
						'key'   => 'number',
						'value' => '{number}',
					);

					$new_data['g_an_param_2'] = array(
						'key'   => 'title',
						'value' => '{title}',
					);

					$new_data['g_an_param_3'] = array(
						'key'   => 'url',
						'value' => '{url}',
					);
				}
			}

			// 2. GTM (Google Tag Manager) Params
			// Check if params are missing AND 'parms_saved_2' flag does not exist.
			if ( ! isset( $os['gtm_params'] ) && ! isset( $os['parms_saved_2'] ) ) {

				$new_data['gtm_params'] = array(
					'gtm_param_1',
					'gtm_param_2',
					'gtm_param_3',
					'gtm_param_4',
					'gtm_param_5',
				);

				$new_data['gtm_param_1'] = array(
					'key'   => 'type',
					'value' => 'chat',
				);

				$new_data['gtm_param_2'] = array(
					'key'   => 'number',
					'value' => '{number}',
				);

				$new_data['gtm_param_3'] = array(
					'key'   => 'title',
					'value' => '{title}',
				);

				$new_data['gtm_param_4'] = array(
					'key'   => 'url',
					'value' => '{url}',
				);

				$new_data['gtm_param_5'] = array(
					'key'   => 'ref',
					'value' => 'dataLayer push',
				);

			}

			// 3. Meta Pixel Params
			// Check if params are missing AND user hasn't actively saved settings before.
			if ( ! isset( $os['pixel_params'] ) && ! isset( $os['parms_saved'] ) ) {

				$new_data['pixel_params'] = array(
					'pixel_param_1',
					'pixel_param_2',
					'pixel_param_3',
					'pixel_param_4',
				);

				$new_data['pixel_param_1'] = array(
					'key'   => 'Category',
					'value' => 'Click to Chat for WhatsApp',
				);

				$new_data['pixel_param_2'] = array(
					'key'   => 'ID',
					'value' => '{number}',
				);

				$new_data['pixel_param_3'] = array(
					'key'   => 'Title',
					'value' => '{title}',
				);

				$new_data['pixel_param_4'] = array(
					'key'   => 'URL',
					'value' => '{url}',
				);

			}

			if ( ! is_array( $new_data ) ) {
				$new_data = array();
			}

			// Merge defaults ($new_data) with existing options ($os).
			// Existing keys in $os will overwrite $new_data, preserving user settings if they exist.
			$update_othersettings = array_merge( $new_data, $os );

			update_option( 'ht_ctc_othersettings', $update_othersettings );
		}



		/**
		 * Updating to v4.34 or above
		 *
		 * 4.34 changes. setting form GTM datalayer push. so by default enabled. (as like early app js how datalayer pused. now with settings.)
		 * and in 4.30 we added google anayalytics data to send to gtm datalayer form app js but now as deprecated. so added ga_gtm as enabled.
		 */
		public function v4_34_update() {

			$os = get_option( 'ht_ctc_othersettings' );

			$new_data = array(); // hold new structure data

			$new_data['ga_gtm']         = '1';
			$new_data['gtm']            = '1';
			$new_data['gtm_event_name'] = 'Click to Chat';

			if ( ! is_array( $new_data ) ) {
				$new_data = array();
			}
			if ( ! is_array( $os ) ) {
				$os = array();
			}

			$os = array_merge( $new_data, $os );
			update_option( 'ht_ctc_othersettings', $os );
		}




		/**
		 * Updating to v3.31 or above
		 *
		 * 3.31 changes. if google_analytics and ga4
		 *
		 * @version 3.31. input fields google_analytics, ga4 become g_an. with value ga or ga4.
		 *
		 * early if google_analytics, ga4 is enabled. 'value' is 1.
		 * @since 3.31 google_analytics, ga4 becomes one field: 'g_an' and 'value' will be ga(only google_analytics is enabled) or ga4(google_analytics and ga4 are enabled.).
		 * ga or ga4. (coampatible with older versions: g_an value updates at the time of plugin upgrade. class-ht-ctc-update-db.php)
		 */
		public function v3_31_update() {

			$os = get_option( 'ht_ctc_othersettings' );

			$n                    = array();
			$n['g_an_event_name'] = 'click to chat';

			// if google_analytics is enabled.
			// (for safety params not added to db.)
			if ( isset( $os['google_analytics'] ) ) {
				if ( isset( $os['ga4'] ) ) {
					$n['g_an'] = 'ga4';
				} else {
					// only ga is enable but not ga4
					$n['g_an']            = 'ga';
					$n['g_an_event_name'] = 'chat: {number}';
				}
			}

			// if ( isset($os['fb_pixel']) ) {
			// $n['pixel_event_type'] = 'trackCustom';
			// $n['pixel_custom_event_name'] = 'Click to Chat by HoliThemes';
			// $n['pixel_standard_event_name'] = 'Lead';
			// }

			$othersettings        = get_option( 'ht_ctc_othersettings', array() );
			$othersettings        = ( is_array( $othersettings ) ) ? $othersettings : array();
			$update_othersettings = array_merge( $n, $othersettings );
			update_option( 'ht_ctc_othersettings', $update_othersettings );
		}





		/**
		 * Updating to v3.23 or above
		 *
		 * From main settings page web whatsapp checkbox feature is moved to other_setting - url_structure feature 3.12.
		 * And now in 3.23 url_structure is moving to main settings.
		 *
		 * At 3.12 web whatsapp migration is not done from db. instead checking both values.
		 * Now in 3.23 updating the database comptibile with web whatsapp and url structure.
		 *
		 * Url_structure_d - web / default(wa.me)  / custom_url
		 *
		 * @note: merge this function in the next db update. as only one value..
		 *      @imp - if merging - here 'return' is used if not set. need to update this..
		 */
		public function v3_23_update() {

			$options = get_option( 'ht_ctc_chat_options' );
			$os      = get_option( 'ht_ctc_othersettings' );

			if ( ! isset( $os['url_structure_d'] ) && ! isset( $options['webandapi'] ) ) {
				return;
			}

			$n              = array();
			$n['not_empty'] = '1';

			// desktop target
			if ( isset( $os['url_target_d'] ) ) {
				$n['url_target_d'] = esc_attr( $os['url_target_d'] );
			}

			// destop structure
			if ( isset( $options['webandapi'] ) ) {
				$n['url_structure_d'] = 'web';
			}

			if ( isset( $os['url_structure_d'] ) ) {
				$n['url_structure_d'] = esc_attr( $os['url_structure_d'] );
			}

			// mobile structure
			if ( isset( $os['url_structure_m'] ) ) {
				$n['url_structure_m'] = esc_attr( $os['url_structure_m'] );
			}

			$chat        = get_option( 'ht_ctc_chat_options', array() );
			$chat        = ( is_array( $chat ) ) ? $chat : array();
			$update_chat = array_merge( $n, $chat );
			update_option( 'ht_ctc_chat_options', $update_chat );
		}



		/**
		 * Updating to v3.3.3 or above
		 *
		 * Select styles issue checkbox  move from other styles to its c/g/s
		 *
		 * @note: merge this function in the next db update. as only one value..
		 *      @imp - if merging - here 'return' is used if not set. need to update this..
		 */
		public function v3_3_5_update() {

			$os = get_option( 'ht_ctc_othersettings' );

			if ( ! isset( $os['select_styles_issue'] ) ) {
				return;
			}

			$n                        = array();
			$n['select_styles_issue'] = '1';

			$chat        = get_option( 'ht_ctc_chat_options', array() );
			$update_chat = array_merge( $n, $chat );
			update_option( 'ht_ctc_chat_options', $update_chat );

			$group        = get_option( 'ht_ctc_group', array() );
			$update_group = array_merge( $n, $group );
			update_option( 'ht_ctc_group', $update_group );

			$share        = get_option( 'ht_ctc_share', array() );
			$update_share = array_merge( $n, $share );
			update_option( 'ht_ctc_share', $update_share );
		}





		/**
		 * Updating to v3.3.3 or above
		 *
		 * Chat
		 */
		public function v3_3_3_update_show_hide_chat() {

			$options = get_option( 'ht_ctc_chat_options' );

			// show/hide select settings value 2.0 to 3.3.2
			$show_or_hide = ( isset( $options['show_or_hide'] ) ) ? esc_html( $options['show_or_hide'] ) : '';

			$n = array();

			// desktop
			$n['display_desktop'] = 'show';
			if ( isset( $options['hideon_desktop'] ) ) {
				$n['display_desktop'] = 'hide';
			}

			// mobile
			$n['display_mobile'] = 'show';
			if ( isset( $options['hideon_mobile'] ) ) {
				$n['display_mobile'] = 'hide';
			}

			/**
			 * Show / hide
			 *
			 * If its hide based on then default is hide and get only show settings and apply
			 * If its show based on then default is show and get only hide settings and apply
			 */

			$n['display']['show_hide'] = 'setting';

			if ( 'hide' === $show_or_hide ) {
				// default show on all pages (check: show settings)

				// new settings - select show
				$n['display']['global_display'] = 'show';

				// check if any hide settings added

				// posts
				if ( isset( $options['hideon_posts'] ) ) {
					$n['display']['posts'] = 'hide';
				}

				// pages
				if ( isset( $options['hideon_page'] ) ) {
					$n['display']['pages'] = 'hide';
				}

				// home page
				if ( isset( $options['hideon_homepage'] ) ) {
					$n['display']['home'] = 'hide';
				}

				// category
				if ( isset( $options['hideon_category'] ) ) {
					$n['display']['category'] = 'hide';
				}

				// archive
				if ( isset( $options['hideon_archive'] ) ) {
					$n['display']['archive'] = 'hide';
				}

				// 404
				if ( isset( $options['hideon_404'] ) ) {
					$n['display']['page_404'] = 'hide';
				}

				// woocommerce product pages
				if ( isset( $options['hideon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'hide';
				}

				// post id's
				if ( isset( $options['list_hideon_pages'] ) ) {
					$n['display']['list_hideon_pages'] = $options['list_hideon_pages'];
				}

				// category names
				if ( isset( $options['list_hideon_cat'] ) ) {
					$n['display']['list_hideon_cat'] = $options['list_hideon_cat'];
				}
			} elseif ( 'show' === $show_or_hide ) {

				// default hide on all pages (check: hide)

				// new settings - select hide
				$n['display']['global_display'] = 'hide';

				// check if any hide settings added

				// posts
				if ( isset( $options['showon_posts'] ) ) {
					$n['display']['posts'] = 'show';
				}

				// pages
				if ( isset( $options['showon_page'] ) ) {
					$n['display']['pages'] = 'show';
				}

				// home page
				if ( isset( $options['showon_homepage'] ) ) {
					$n['display']['home'] = 'show';
				}

				// category
				if ( isset( $options['showon_category'] ) ) {
					$n['display']['category'] = 'show';
				}

				// archive
				if ( isset( $options['showon_archive'] ) ) {
					$n['display']['archive'] = 'show';
				}

				// 404
				if ( isset( $options['showon_404'] ) ) {
					$n['display']['page_404'] = 'show';
				}

				// woocommerce product pages
				if ( isset( $options['showon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'show';
				}

				// post id's
				if ( isset( $options['list_showon_pages'] ) ) {
					$n['display']['list_showon_pages'] = $options['list_showon_pages'];
				}

				// category names
				if ( isset( $options['list_showon_cat'] ) ) {
					$n['display']['list_showon_cat'] = $options['list_showon_cat'];
				}
			}

			$db_values = get_option( 'ht_ctc_chat_options', array() );
			$update_os = array_merge( $n, $db_values );
			update_option( 'ht_ctc_chat_options', $update_os );
		}


		/**
		 * Updating to v3.3.3 or above
		 *
		 * Group
		 */
		public function v3_3_3_update_show_hide_group() {

			$options = get_option( 'ht_ctc_group' );

			// show/hide select settings value 2.0 to 3.3.2
			$show_or_hide = ( isset( $options['show_or_hide'] ) ) ? esc_html( $options['show_or_hide'] ) : '';

			$n = array();

			// desktop
			$n['display_desktop'] = 'show';
			if ( isset( $options['hideon_desktop'] ) ) {
				$n['display_desktop'] = 'hide';
			}

			// mobile
			$n['display_mobile'] = 'show';
			if ( isset( $options['hideon_mobile'] ) ) {
				$n['display_mobile'] = 'hide';
			}

			/**
			 * Show / hide
			 *
			 * If its hide based on then default is hide and get only show settings and apply
			 * If its show based on then default is show and get only hide settings and apply
			 */

			$n['display']['show_hide'] = 'setting';

			if ( 'hide' === $show_or_hide ) {
				// default show on all pages (check: show settings)

				// new settings - select show
				$n['display']['global_display'] = 'show';

				// check if any hide settings added

				// posts
				if ( isset( $options['hideon_posts'] ) ) {
					$n['display']['posts'] = 'hide';
				}

				// pages
				if ( isset( $options['hideon_page'] ) ) {
					$n['display']['pages'] = 'hide';
				}

				// home page
				if ( isset( $options['hideon_homepage'] ) ) {
					$n['display']['home'] = 'hide';
				}

				// category
				if ( isset( $options['hideon_category'] ) ) {
					$n['display']['category'] = 'hide';
				}

				// archive
				if ( isset( $options['hideon_archive'] ) ) {
					$n['display']['archive'] = 'hide';
				}

				// 404
				if ( isset( $options['hideon_404'] ) ) {
					$n['display']['page_404'] = 'hide';
				}

				// woocommerce product pages
				if ( isset( $options['hideon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'hide';
				}

				// post id's
				if ( isset( $options['list_hideon_pages'] ) ) {
					$n['display']['list_hideon_pages'] = $options['list_hideon_pages'];
				}

				// category names
				if ( isset( $options['list_hideon_cat'] ) ) {
					$n['display']['list_hideon_cat'] = $options['list_hideon_cat'];
				}
			} elseif ( 'show' === $show_or_hide ) {

				// default hide on all pages (check: hide)

				// new settings - select hide
				$n['display']['global_display'] = 'hide';

				// check if any hide settings added

				// posts
				if ( isset( $options['showon_posts'] ) ) {
					$n['display']['posts'] = 'show';
				}

				// pages
				if ( isset( $options['showon_page'] ) ) {
					$n['display']['pages'] = 'show';
				}

				// home page
				if ( isset( $options['showon_homepage'] ) ) {
					$n['display']['home'] = 'show';
				}

				// category
				if ( isset( $options['showon_category'] ) ) {
					$n['display']['category'] = 'show';
				}

				// archive
				if ( isset( $options['showon_archive'] ) ) {
					$n['display']['archive'] = 'show';
				}

				// 404
				if ( isset( $options['showon_404'] ) ) {
					$n['display']['page_404'] = 'show';
				}

				// woocommerce product pages
				if ( isset( $options['showon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'show';
				}

				// post id's
				if ( isset( $options['list_showon_pages'] ) ) {
					$n['display']['list_showon_pages'] = $options['list_showon_pages'];
				}

				// category names
				if ( isset( $options['list_showon_cat'] ) ) {
					$n['display']['list_showon_cat'] = $options['list_showon_cat'];
				}
			}

			$db_values = get_option( 'ht_ctc_group', array() );
			$update_os = array_merge( $n, $db_values );
			update_option( 'ht_ctc_group', $update_os );
		}


		/**
		 * Updating to v3.3.3 or above
		 *
		 * Share
		 */
		public function v3_3_3_update_show_hide_share() {

			$options = get_option( 'ht_ctc_share' );

			// show/hide select settings value 2.0 to 3.3.2
			$show_or_hide = ( isset( $options['show_or_hide'] ) ) ? esc_html( $options['show_or_hide'] ) : '';

			$n = array();

			// desktop
			$n['display_desktop'] = 'show';
			if ( isset( $options['hideon_desktop'] ) ) {
				$n['display_desktop'] = 'hide';
			}

			// mobile
			$n['display_mobile'] = 'show';
			if ( isset( $options['hideon_mobile'] ) ) {
				$n['display_mobile'] = 'hide';
			}

			/**
			 * Show / hide
			 *
			 * If its hide based on then default is hide and get only show settings and apply
			 * If its show based on then default is show and get only hide settings and apply
			 */

			$n['display']['show_hide'] = 'setting';

			if ( 'hide' === $show_or_hide ) {
				// default show on all pages (check: show settings)

				// new settings - select show
				$n['display']['global_display'] = 'show';

				// check if any hide settings added

				// posts
				if ( isset( $options['hideon_posts'] ) ) {
					$n['display']['posts'] = 'hide';
				}

				// pages
				if ( isset( $options['hideon_page'] ) ) {
					$n['display']['pages'] = 'hide';
				}

				// home page
				if ( isset( $options['hideon_homepage'] ) ) {
					$n['display']['home'] = 'hide';
				}

				// category
				if ( isset( $options['hideon_category'] ) ) {
					$n['display']['category'] = 'hide';
				}

				// archive
				if ( isset( $options['hideon_archive'] ) ) {
					$n['display']['archive'] = 'hide';
				}

				// 404
				if ( isset( $options['hideon_404'] ) ) {
					$n['display']['page_404'] = 'hide';
				}

				// woocommerce product pages
				if ( isset( $options['hideon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'hide';
				}

				// post id's
				if ( isset( $options['list_hideon_pages'] ) ) {
					$n['display']['list_hideon_pages'] = $options['list_hideon_pages'];
				}

				// category names
				if ( isset( $options['list_hideon_cat'] ) ) {
					$n['display']['list_hideon_cat'] = $options['list_hideon_cat'];
				}
			} elseif ( 'show' === $show_or_hide ) {

				// default hide on all pages (check: hide)

				// new settings - select hide
				$n['display']['global_display'] = 'hide';

				// check if any hide settings added

				// posts
				if ( isset( $options['showon_posts'] ) ) {
					$n['display']['posts'] = 'show';
				}

				// pages
				if ( isset( $options['showon_page'] ) ) {
					$n['display']['pages'] = 'show';
				}

				// home page
				if ( isset( $options['showon_homepage'] ) ) {
					$n['display']['home'] = 'show';
				}

				// category
				if ( isset( $options['showon_category'] ) ) {
					$n['display']['category'] = 'show';
				}

				// archive
				if ( isset( $options['showon_archive'] ) ) {
					$n['display']['archive'] = 'show';
				}

				// 404
				if ( isset( $options['showon_404'] ) ) {
					$n['display']['page_404'] = 'show';
				}

				// woocommerce product pages
				if ( isset( $options['showon_wooproduct'] ) ) {
					$n['display']['woo_product'] = 'show';
				}

				// post id's
				if ( isset( $options['list_showon_pages'] ) ) {
					$n['display']['list_showon_pages'] = $options['list_showon_pages'];
				}

				// category names
				if ( isset( $options['list_showon_cat'] ) ) {
					$n['display']['list_showon_cat'] = $options['list_showon_cat'];
				}
			}

			$db_values = get_option( 'ht_ctc_share', array() );
			$update_os = array_merge( $n, $db_values );
			update_option( 'ht_ctc_share', $update_os );
		}


		/**
		 * Updating to v3.3.3 or above
		 *  - woocommerce option changed from ht_ctc_chat_options settings to ht_ctc_woo_options
		 *
		 * Chat
		 */
		public function v3_3_3_update_woo() {

			$options = get_option( 'ht_ctc_woo_options' );

			$chat = get_option( 'ht_ctc_chat_options' );

			$woo = array(
				'woo' => 'settings',
			);

			if ( isset( $chat['woo_pre_filled'] ) ) {
				$woo['woo_pre_filled'] = $chat['woo_pre_filled'];
			}
			if ( isset( $chat['woo_call_to_action'] ) ) {
				$woo['woo_call_to_action'] = $chat['woo_call_to_action'];
			}

			$db_woo    = get_option( 'ht_ctc_woo_options', array() );
			$update_os = array_merge( $woo, $db_woo );
			update_option( 'ht_ctc_woo_options', $update_os );
		}

		/**
		 * Updating to v3 or above.
		 *  - style 3 Extend to Style-3_1
		 *  - analytics, .. switch to other settings..
		 */
		public function v3_update() {

			$ht_ctc_othersettings = get_option( 'ht_ctc_othersettings' );
			$ht_ctc_s3            = get_option( 'ht_ctc_s3' );

			// ht_ctc_main_options to ht_ctc_othersettings
			$ht_ctc_main_options = get_option( 'ht_ctc_main_options' );

			if ( $ht_ctc_main_options ) {

				$os = array(
					'hello' => 'world',
				);

				if ( isset( $ht_ctc_main_options['google_analytics'] ) ) {
					$os['google_analytics'] = '1';
				}
				if ( isset( $ht_ctc_main_options['fb_pixel'] ) ) {
					$os['fb_pixel'] = '1';
				}
				if ( isset( $ht_ctc_main_options['enable_group'] ) ) {
					$os['enable_group'] = '1';
				}
				if ( isset( $ht_ctc_main_options['enable_share'] ) ) {
					$os['enable_share'] = '1';
				}

				$db_os     = get_option( 'ht_ctc_othersettings', array() );
				$update_os = array_merge( $os, $db_os );
				update_option( 'ht_ctc_othersettings', $update_os );

				// delete ht_ctc_main_options settings, as transfered to other settings
				delete_option( 'ht_ctc_main_options' );
			}

			// style-3 type extend is selected.. and if style 3 to 3_1
			if ( isset( $ht_ctc_s3['s3_type'] ) && 'extend' === $ht_ctc_s3['s3_type'] ) {

				$ht_ctc_chat_options = get_option( 'ht_ctc_chat_options' );
				$ht_ctc_group        = get_option( 'ht_ctc_group' );
				$ht_ctc_share        = get_option( 'ht_ctc_share' );

				// this works as s3 type extend came later version of select style dekstop, mobile.
				// chat
				if ( isset( $ht_ctc_chat_options['style_desktop'] ) && isset( $ht_ctc_chat_options['style_mobile'] ) ) {
					if ( '3' === $ht_ctc_chat_options['style_desktop'] ) {
						$ht_ctc_chat_options['style_desktop'] = '3_1';
					}
					if ( '3' === $ht_ctc_chat_options['style_mobile'] ) {
						$ht_ctc_chat_options['style_mobile'] = '3_1';
					}
					update_option( 'ht_ctc_chat_options', $ht_ctc_chat_options );
				}

				// group
				if ( isset( $ht_ctc_group['style_desktop'] ) ) {
					if ( '3' === $ht_ctc_group['style_desktop'] ) {
						$ht_ctc_group['style_desktop'] = '3_1';
					}
					if ( '3' === $ht_ctc_group['style_mobile'] ) {
						$ht_ctc_group['style_mobile'] = '3_1';
					}
					update_option( 'ht_ctc_group', $ht_ctc_group );
				}

				// share
				if ( isset( $ht_ctc_share['style_desktop'] ) ) {
					if ( '3' === $ht_ctc_share['style_desktop'] ) {
						$ht_ctc_share['style_desktop'] = '3_1';
					}
					if ( '3' === $ht_ctc_share['style_mobile'] ) {
						$ht_ctc_share['style_mobile'] = '3_1';
					}
					update_option( 'ht_ctc_share', $ht_ctc_share );
				}
			}
		}
	}

	new HT_CTC_Update_DB();

} // END class_exists check
