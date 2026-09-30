<?php
/**
 * Display Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Display' ) ) {

	/**
	 * Display settings class.
	 */
	class HT_CTC_Settings_Display {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// Devices
			$fields[] = self::card_devices();

			// Pages
			$fields[] = self::card_pages();

			// Targeting
			$fields[] = self::card_targeting();

			// Business Hours
			$fields[] = self::card_business_hours();

			return $fields;
		}

		/**
		 * Helper to create device display option fields
		 *
		 * @param string $label Field label.
		 * @param string $id Field ID.
		 * @return array
		 */
		private static function device_display_field( $label, $id ) {
			return array(
				'field_type'   => 'field_radio',
				'type'         => 'segment',
				'label'        => $label,
				'id'           => $id,
				'option_group' => 'ht_ctc_chat_options',
				'options'      => array(
					'show' => __( 'Show', 'click-to-chat-for-whatsapp' ),
					'hide' => __( 'Hide', 'click-to-chat-for-whatsapp' ),
				),
				'default'      => 'show',
			);
		}

		/**
		 * Helper to create page display option fields
		 *
		 * @param string $label Field label.
		 * @param string $id Field ID.
		 * @param string $help_click Optional help text shown via a "?" toggle next to the label.
		 * @return array
		 */
		private static function page_display_field( $label, $id, $help_click = '' ) {
			$field = array(
				'field_type'   => 'field_radio',
				'type'         => 'segment',
				'label'        => $label,
				'id'           => $id,
				'option_group' => 'ht_ctc_chat_options[display]',
				'options'      => array(
					'g'    => __( 'Global', 'click-to-chat-for-whatsapp' ),
					'show' => __( 'Show', 'click-to-chat-for-whatsapp' ),
					'hide' => __( 'Hide', 'click-to-chat-for-whatsapp' ),
				),
				'default'      => 'g',
			);

			// Optional "?" help toggle next to the label (clarifies ambiguous page types).
			if ( '' !== $help_click ) {
				$field['help_click'] = $help_click;
			}

			return $field;
		}

		/**
		 * Helper to create list text fields
		 *
		 * @param string $label Field label.
		 * @param string $id Field ID.
		 * @param string $help Field help text.
		 * @param string $watch Data watch string.
		 * @param string $show_when Data show when string.
		 * @return array
		 */
		private static function list_field( $label, $id, $help, $watch, $show_when ) {
			$type        = ( strpos( $id, 'pages' ) !== false ) ? 'page' : 'category';
			$placeholder = ( 'page' === $type ) ? 'Enter page IDs separated by commas (e.g., 12, 34, 56)' : 'Enter category names separated by commas (e.g., News, Offers)';
			return array(
				'field_type'     => 'field_text',
				'label'          => $label,
				'id'             => $id,
				'option_group'   => 'ht_ctc_chat_options[display]',
				'placeholder'    => $placeholder,
				'help'           => $help,
				'data_watch'     => $watch,
				'data_show_when' => $show_when,
			);
		}

		/**
		 * Get public custom post types that use display controls.
		 *
		 * @return array
		 */
		private static function get_display_custom_post_type_keys() {
			if ( ! function_exists( 'get_post_types' ) ) {
				return array();
			}

			$custom_post_types = get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				)
			);

			if ( is_array( $custom_post_types ) ) {
				unset( $custom_post_types['product'] );
				return array_values( $custom_post_types );
			}

			return array();
		}

		/**
		 * Devices Card
		 */
		private static function card_devices() {
			$values = array(
				'field_type'     => 'card',
				'title'          => 'Devices',
				'description'    => 'Control display on different devices',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(
					self::device_display_field( 'Desktop Display', 'display_desktop' ),
					self::device_display_field( 'Mobile Display', 'display_mobile' ),
				),
			);
			return $values;
		}

		/**
		 * Pages Card
		 */
		private static function card_pages() {
			$pages_fields = array(
				array(
					'field_type'   => 'field_radio',
					'type'         => 'segment',
					'label'        => sprintf( '%1$s %2$s', __( 'Global', 'click-to-chat-for-whatsapp' ), __( 'Display', 'click-to-chat-for-whatsapp' ) ),
					'id'           => 'global_display',
					'option_group' => 'ht_ctc_chat_options[display]',
					'options'      => array(
						'show' => __( 'Show', 'click-to-chat-for-whatsapp' ),
						'hide' => __( 'Hide', 'click-to-chat-for-whatsapp' ),
					),
					'default'      => 'show',
					'help'         => 'Global setting for all pages',
				),
				array(
					'field_type'  => 'block_sub_heading',
					'title'       => __( 'Overwrite the Global settings', 'click-to-chat-for-whatsapp' ),
					'description' => 'If global display is enabled, you can hide on specific pages and vice-versa',
					'class_pr'    => '',
				),
				self::page_display_field( 'Home Page', 'home', 'The front page of your site.' ),
				self::page_display_field( 'Posts', 'posts', 'Single blog post pages.' ),
				self::page_display_field( 'Pages', 'pages', 'Static pages such as About or Contact.' ),
				self::page_display_field( 'Archive pages', 'archive', 'Listing pages that group posts together — date, author, tag and custom taxonomy archives.' ),
				self::page_display_field( 'Category pages', 'category', 'Pages that list all posts filed under a category.' ),
				self::page_display_field( '404 Page', 'page_404', 'The error page shown when a URL is not found (“page not found”).' ),
			);

			// Add WooCommerce settings only if WooCommerce is active
			if ( class_exists( 'WooCommerce' ) ) {
				$woo_fields   = array(
					array(
						'field_type'  => 'block_sub_heading',
						'title'       => 'WooCommerce Pages',
						'description' => 'Control display on WooCommerce specific pages',
					),
					self::page_display_field( 'Single Product Pages', 'woo_product', 'Individual product detail pages.' ),
					self::page_display_field( 'Shop Page', 'woo_shop', 'The main store page that lists all products.' ),
					self::page_display_field( 'Cart Page', 'woo_cart', 'The shopping cart page.' ),
					self::page_display_field( 'Checkout Page', 'woo_checkout', 'The checkout / payment page.' ),
					self::page_display_field( 'Thank You / Order Received Page', 'woo_order_received', 'The order confirmation (thank-you) page shown after checkout.' ),
					self::page_display_field( 'Account Page', 'woo_account', 'The customer’s My Account dashboard.' ),
				);
				$pages_fields = array_merge( $pages_fields, $woo_fields );
			}

			$custom_post_types = self::get_display_custom_post_type_keys();
			if ( ! empty( $custom_post_types ) ) {
				$custom_post_types_fields = array(
					array(
						'field_type'  => 'block_sub_heading',
						'title'       => 'Custom Post Types',
						'description' => 'Control display on custom post types',
					),
				);
				foreach ( $custom_post_types as $cpt ) {
					// // Use get_post_type_object to fetch the properly registered human-readable name,
					// // falling back to a capitalized slug if the object isn't found.
					// $post_type_obj = get_post_type_object( $cpt );
					// $cpt_label     = $post_type_obj ? $post_type_obj->labels->name : ucfirst( $cpt );
					// $custom_post_types_fields[]  = self::page_display_field( $cpt_label, $cpt );
					$custom_post_types_fields[] = self::page_display_field( $cpt, $cpt );
				}
				$pages_fields = array_merge( $pages_fields, $custom_post_types_fields );
			}

			// Add Page/Category Lists section
			$list_fields = array(
				array(
					'field_type'  => 'block_sub_heading',
					'title'       => 'Page/Category Lists',
					'description' => 'Specify individual pages (by ID) or categories (by name)',
				),
				self::list_field( sprintf( '%1$s (by ID)', __( 'Hide on this pages', 'click-to-chat-for-whatsapp' ) ), 'list_hideon_pages', 'Enter page IDs where you want to hide the chat widget', '#global_display_show', 'show' ),
				self::list_field( sprintf( '%1$s (by name)', __( 'Hide on this Category posts', 'click-to-chat-for-whatsapp' ) ), 'list_hideon_cat', 'Enter category names where you want to hide the chat widget', '#global_display_show', 'show' ),
				self::list_field( sprintf( '%1$s (by ID)', __( 'Show on this pages', 'click-to-chat-for-whatsapp' ) ), 'list_showon_pages', 'Enter page IDs where you want to show the chat widget', '#global_display_hide', 'hide' ),
				self::list_field( sprintf( '%1$s (by name)', __( 'Show on this Category posts', 'click-to-chat-for-whatsapp' ) ), 'list_showon_cat', 'Enter category names where you want to show the chat widget', '#global_display_hide', 'hide' ),
				array(
					'field_type' => 'block_external_link',
					'title'      => '',
					'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/show-hide-styles/' ),
					'label'      => __( 'Display Settings', 'click-to-chat-for-whatsapp' ),
				),
			);

			$pages_fields = array_merge( $pages_fields, $list_fields );

			$values = array(
				'field_type'     => 'card',
				'title'          => 'Pages',
				'description'    => 'Control display on specific pages',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => $pages_fields,
			);
			return $values;
		}

		/**
		 * Business Hours Card
		 */
		private static function card_business_hours() {
			$values = array(
				'field_type'     => 'card',
				'title'          => __( 'Business Hours', 'click-to-chat-for-whatsapp' ),
				'description'    => 'Control display based on time and day',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {

				$hours_url = 'https://holithemes.com/plugins/click-to-chat/docs/business-hours-online-offline/';

				// Section anchor on that page. An id the page does not carry
				// yet is ignored by the browser, so the link lands at the top
				// meanwhile instead of breaking.
				$offline_url = $hours_url . '#hide-when-offline';

				$values['fields'] = array(
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-clock',
						'title'       => __( 'Business Hours', 'click-to-chat-for-whatsapp' ),
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Set several time slots a day, in your site\'s timezone — the chat widget goes online and offline on its own.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'business_hours', $hours_url ),
					),
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-hidden',
						'title'       => 'Offline Behaviour',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Outside those hours, hide the widget — or keep it and answer on a different number, with a different call to action.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'business_hours_offline', $offline_url ),
					),
					// Single closing CTA for the tab: Business Hours is the last
					// card, so the pitch ends here instead of after every teaser.
					array(
						'field_type' => 'block_raw_html',
						'content'    => '<a href="' . esc_url( HT_CTC_Utils::pro_url( 'teaser', 'display' ) ) . '" target="_blank" rel="noopener" class="ctc-pro-btn ctc-pro-btn-primary ctc-pro-teaser-cta">Upgrade to PRO <span class="dashicons dashicons-external"></span><span class="screen-reader-text">(opens in a new tab)</span></a>',
					),
				);
			}

			$values = apply_filters( 'ht_ctc_fh_settings_fields_display_business_hours', $values );
			return $values;
		}

		/**
		 * Targeting Card
		 */
		private static function card_targeting() {
			$values = array(
				'field_type'     => 'card',
				'title'          => 'Targeting',
				'description'    => 'Control who sees the chat widget, and when it appears',
				'data_watch'     => '#connection_type',
				'data_show_when' => 'single',
				'fields'         => array(),
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				/*
				 * One teaser per capability PRO actually adds to THIS card
				 * (country, login status, delay triggers). The ids and copy
				 * follow the PRO tab catalogue in views/panels/class-ht-ctc-pro-features-panel.php,
				 * so the same feature is described the same way wherever it is
				 * promoted.
				 */
				$display_url = 'https://holithemes.com/plugins/click-to-chat/display/';

				$values['fields'] = array(
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-admin-site-alt3',
						'title'       => 'Country-Based Display',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Pick from 249 countries — each visitor\'s country is detected automatically, with nothing to set up.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'country_display', $display_url ),
					),
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-admin-users',
						'title'       => 'Login-Status Display',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Show the chat widget to everyone, only to logged-in users, or only to logged-out users.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'login_status_display', $display_url ),
					),
					array(
						'field_type'  => 'block_pro_feature',
						'icon'        => 'dashicons dashicons-controls-play',
						'title'       => 'Time & Scroll Delay',
						'badge'       => __( 'PRO', 'click-to-chat-for-whatsapp' ),
						'description' => 'Hold the chat widget back a set number of seconds, or until the visitor has scrolled a set percentage of the page.',
						'button_text' => 'Learn more',
						'url'         => HT_CTC_Utils::pro_url( 'teaser', 'display_delay', $display_url ),
					),
				);
			}

			$values = apply_filters( 'ht_ctc_fh_settings_fields_display_targeting', $values );
			return $values;
		}
	}
}
