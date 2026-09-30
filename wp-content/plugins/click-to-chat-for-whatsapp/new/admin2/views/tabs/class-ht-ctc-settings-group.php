<?php
/**
 * Group Settings
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Settings_Group' ) ) {

	/**
	 * Group settings class.
	 */
	class HT_CTC_Settings_Group {

		/**
		 * Get fields
		 *
		 * @return array
		 */
		public static function fields() {
			$fields = array();

			// WhatsApp Group Settings
			$fields[] = self::card_whatsapp_group_settings();

			// Group Widget Style & Position
			$fields[] = self::card_group_widget_style_position();

			// Group Display - Devices
			$fields[] = self::card_group_devices();

			// Group Display - Pages
			$fields[] = self::card_group_pages();

			// Features
			$fields[] = self::card_features();

			return $fields;
		}

		// -------------------------------------------------------------------------
		// Private helpers
		// -------------------------------------------------------------------------

		/**
		 * Group Widget style options shared by Desktop and Mobile selectors.
		 *
		 * Centralises the list so desktop and mobile grids stay in sync
		 * and the definition only ever needs to be updated in one place.
		 *
		 * @return array
		 */
		private static function style_options() {
			return array(
				array(
					'value'       => '1',
					'name'        => __( 'Style 1', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Theme Button',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-1',
					// 'image'       => 'https://example.com/image.png',
				),
				array(
					'value'       => '2',
					'name'        => __( 'Style 2', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Green Square Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-2',
					// 'image'       => '',
				),
				array(
					'value'       => '3',
					'name'        => __( 'Style 3', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-3',
					// 'image'       => '',
				),
				array(
					'value'       => '3_1',
					'name'        => __( 'Style 3 Extend', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Large Icon',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-3-1',
					// 'image'       => '',
				),
				array(
					'value'       => '4',
					'name'        => 'Style 4',
					'sub_text'    => 'Chip (cylindrical)',
					'icon'        => 'dashicons dashicons-format-chat',
					'text'        => 'Chat',
					'class_field' => 'style-4',
					// 'image'       => '',
				),
				array(
					'value'       => '5',
					'name'        => __( 'Style 5', 'click-to-chat-for-whatsapp' ),
					// 'sub_text'    => 'Image Slider',
					'sub_text'    => 'Image on hover Content Box',
					'icon'        => 'dashicons dashicons-format-chat',
					'text'        => 'Chat',
					// 'text'        => 'Join Group',
					'class_field' => 'style-5',
					// 'image'       => '',
				),
				array(
					'value'       => '6',
					'name'        => 'Style 6',
					'sub_text'    => 'Plain text',
					// 'sub_text'    => 'Text Only',
					'text'        => 'Chat with us',
					'class_field' => 'style-6',
					// 'image'       => '',
				),
				array(
					'value'       => '7',
					'name'        => __( 'Style 7', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Icon with padding',
					// 'sub_text'    => 'Rounded Button',
					'text'        => 'WhatsApp Chat',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-7',
					// 'image'       => '',
				),
				array(
					'value'       => '7_1',
					'name'        => __( 'Style 7 Extend', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Icon on hover extend',
					// 'sub_text'    => 'Rounded Button',
					'text'        => 'WhatsApp Chat',
					'icon'        => 'dashicons dashicons-format-chat',
					'class_field' => 'style-7-1',
					// 'image'       => '',
				),
				array(
					'value'       => '8',
					'name'        => __( 'Style 8', 'click-to-chat-for-whatsapp' ),
					'sub_text'    => 'Button',
					// 'sub_text'    => 'Rect Button',
					'text'        => 'WhatsApp Chat',
					'class_field' => 'style-8',
					// 'image'       => '',
				),
				array(
					'value'       => '99',
					'name'        => 'Style 99',
					'sub_text'    => __( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ),
					'icon'        => 'dashicons dashicons-format-image',
					'class_field' => 'style-99',
					// 'image'       => '',
				),
			);
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
				'option_group' => 'ht_ctc_group',
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
		 * @return array
		 */
		private static function page_display_field( $label, $id ) {
			return array(
				'field_type'   => 'field_radio',
				'type'         => 'segment',
				'label'        => $label,
				'id'           => $id,
				'option_group' => 'ht_ctc_group[display]',
				'options'      => array(
					'g'    => __( 'Global', 'click-to-chat-for-whatsapp' ),
					'show' => __( 'Show', 'click-to-chat-for-whatsapp' ),
					'hide' => __( 'Hide', 'click-to-chat-for-whatsapp' ),
				),
				'default'      => 'g',
			);
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
				'option_group'   => 'ht_ctc_group[display]',
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
		 * WhatsApp Group Settings Card
		 */
		private static function card_whatsapp_group_settings() {
			$values = array(
				'field_type'  => 'card',
				'title'       => 'WhatsApp Group Settings',
				'description' => 'Configure your WhatsApp group chat widget',
				'fields'      => array(
					array(
						'field_type'   => 'field_text',
						'id'           => 'group_id',
						'label'        => __( 'WhatsApp Group ID', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_group',
						'placeholder'  => '9EHLsEsOeJk6AVtE8AvXiA',
						'help'         => sprintf(
							'%1$s - <a target="_blank" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
							__( 'Enter WhatsApp Group ID. E.g. 9EHLsEsOeJk6AVtE8AvXiA', 'click-to-chat-for-whatsapp' ),
							esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/find-whatsapp-group-id/' ) ),
							__( 'more info', 'click-to-chat-for-whatsapp' )
						),
					),
					array(
						'field_type'   => 'field_text',
						'id'           => 'call_to_action',
						'label'        => __( 'Call to Action', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_group',
						'placeholder'  => 'Join Group',
						'help'         => vsprintf(
							'%1$s - <a target="_blank" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
							array(
								__( 'Text that appears along with WhatsApp icon/button', 'click-to-chat-for-whatsapp' ),
								esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/call-to-action/' ) ),
								__( 'more info', 'click-to-chat-for-whatsapp' ),
							)
						),
					),
				),
			);
			return $values;
		}

		/**
		 * Group Widget Style & Position Card
		 */
		private static function card_group_widget_style_position() {
			$position_type_options = array( 'fixed' => 'Fixed' );
			$position_type_options = apply_filters( 'ht_ctc_fh_settings_fields_position_type_options', $position_type_options );
			$style_options         = self::style_options();

			// Shared position-type help text used for both Desktop and Mobile selectors.
			$position_type_help = array(
				__( 'Fixed: Position relative to the screen, stays at the same place even after page scroll', 'click-to-chat-for-whatsapp' ),
				vsprintf(
					'%1$s (PRO)<br><a target="_blank" rel="noopener" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
					array(
						__( 'Absolute: Position relative to the content (body tag) and moves with page scroll', 'click-to-chat-for-whatsapp' ),
						esc_url( HT_CTC_Utils::pro_url( 'inline', 'position_absolute', 'https://holithemes.com/plugins/click-to-chat/position-to-place/#pro_block' ) ),
						__( 'more info', 'click-to-chat-for-whatsapp' ),
					)
				),
			);

			$values = array(
				'field_type'  => 'card',
				'title'       => 'Group Widget Style & Position',
				'description' => 'Choose how your group button looks and where it appears',
				'fields'      => array(
					array(
						'field_type' => 'tabs',
						'class_pr'   => '',
						'tabs'       => array(
							'group-desktop-style' => array(
								'label'  => __( 'Desktop', 'click-to-chat-for-whatsapp' ),
								'fields' => array(
									array(
										'field_type'   => 'block_grid_select',
										'id'           => 'style_desktop',
										'label'        => sprintf( '%1$s: (%2$s)', __( 'Select Style', 'click-to-chat-for-whatsapp' ), __( 'Desktop', 'click-to-chat-for-whatsapp' ) ),
										'option_group' => 'ht_ctc_group',
										'class_pr'     => 'group-style-option-desktop',
										'options'      => $style_options,
									),
									array(
										'field_type'   => 'field_select',
										'id'           => 'position_type',
										'label'        => sprintf( '%1$s: (%2$s)', __( 'Position Type', 'click-to-chat-for-whatsapp' ), __( 'Desktop', 'click-to-chat-for-whatsapp' ) ),
										'option_group' => 'ht_ctc_group',
										'options'      => $position_type_options,
										'default'      => 'fixed',
										'help'         => $position_type_help,
									),
									array(
										'field_type'   => 'block_rows',
										'label'        => sprintf( '%1$s: (%2$s)', __( 'Position to Place', 'click-to-chat-for-whatsapp' ), __( 'Desktop', 'click-to-chat-for-whatsapp' ) ),
										'option_group' => 'ht_ctc_group',
										'fields'       => array(
											'col-1' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'side_1',
													'option_group' => 'ht_ctc_group',
													'options' => array(
														'top'    => __( 'Top', 'click-to-chat-for-whatsapp' ),
														'bottom' => __( 'Bottom', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'bottom',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'side_1_value',
													'option_group' => 'ht_ctc_group',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
											'col-2' => array(
												array(
													'field_type' => 'field_select',
													'id' => 'side_2',
													'option_group' => 'ht_ctc_group',
													'options' => array(
														'left' => __( 'Left', 'click-to-chat-for-whatsapp' ),
														'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'right',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'side_2_value',
													'option_group' => 'ht_ctc_group',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
										),
										'help'         => sprintf(
											'%1$s - <a target="_blank" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
											__( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ),
											esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/position-to-place/' ) ),
											__( 'more info', 'click-to-chat-for-whatsapp' )
										),
									),
								),
							),
							'group-mobile-style'  => array(
								'label'  => __( 'Mobile', 'click-to-chat-for-whatsapp' ),
								'fields' => array(
									array(
										'field_type'   => 'field_checkbox',
										'type'         => 'switch',
										'id'           => 'same_settings',
										'label'        => __( 'Mobile and Desktop same settings', 'click-to-chat-for-whatsapp' ),
										'option_group' => 'ht_ctc_group',
										'value'        => '1',
										'help'         => vsprintf(
											'%1$s, %2$s, %3$s',
											array(
												__( 'Select Style', 'click-to-chat-for-whatsapp' ),
												__( 'Position Type', 'click-to-chat-for-whatsapp' ),
												__( 'Position to Place', 'click-to-chat-for-whatsapp' ),
											)
										),
									),
									array(
										'field_type'     => 'block_grid_select',
										'id'             => 'style_mobile',
										'label'          => sprintf( '%1$s: (%2$s)', __( 'Select Style', 'click-to-chat-for-whatsapp' ), __( 'Mobile', 'click-to-chat-for-whatsapp' ) ),
										'option_group'   => 'ht_ctc_group',
										'class_pr'       => 'group-style-option-mobile',
										'data_watch'     => '#group-mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'options'        => $style_options,
									),
									array(
										'field_type'     => 'field_select',
										'id'             => 'position_type_mobile',
										'label'          => sprintf( '%1$s: (%2$s)', __( 'Position Type', 'click-to-chat-for-whatsapp' ), __( 'Mobile', 'click-to-chat-for-whatsapp' ) ),
										'class_pr'       => 'mobile-setting',
										'option_group'   => 'ht_ctc_group',
										'options'        => $position_type_options,
										'default'        => 'fixed',
										'help'           => $position_type_help,
										'data_watch'     => '#group-mobile-style-tab #same_settings',
										'data_hide_when' => '1',
									),
									array(
										'field_type'     => 'block_rows',
										'label'          => sprintf( '%1$s: (%2$s)', __( 'Position to Place', 'click-to-chat-for-whatsapp' ), __( 'Mobile', 'click-to-chat-for-whatsapp' ) ),
										'class_pr'       => 'mobile-setting',
										'option_group'   => 'ht_ctc_group',
										'data_watch'     => '#group-mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'fields'         => array(
											array(
												array(
													'field_type' => 'field_select',
													'id' => 'mobile_side_1',
													'option_group' => 'ht_ctc_group',
													'options' => array(
														'top'    => __( 'Top', 'click-to-chat-for-whatsapp' ),
														'bottom' => __( 'Bottom', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'bottom',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'mobile_side_1_value',
													'option_group' => 'ht_ctc_group',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
											array(
												array(
													'field_type' => 'field_select',
													'id' => 'mobile_side_2',
													'option_group' => 'ht_ctc_group',
													'options' => array(
														'left'  => __( 'Left', 'click-to-chat-for-whatsapp' ),
														'right' => __( 'Right', 'click-to-chat-for-whatsapp' ),
													),
													'default' => 'right',
												),
												array(
													'field_type' => 'field_text',
													'id' => 'mobile_side_2_value',
													'option_group' => 'ht_ctc_group',
													'placeholder' => '15px',
													'default' => '15px',
												),
											),
										),
										'help'           => sprintf(
											'%1$s - <a target="_blank" href="%2$s" class="external-link">%3$s <span class="dashicons dashicons-external"></span></a>',
											__( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ),
											esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/position-to-place/' ) ),
											__( 'more info', 'click-to-chat-for-whatsapp' )
										),
									),
									array(
										'field_type'     => 'block_divider',
										'data_watch'     => '#group-mobile-style-tab #same_settings',
										'data_hide_when' => '1',
									),
									array(
										'field_type'     => 'block_content_details',
										'style'          => 'margin-top: 10px;',
										'data_watch'     => '#group-mobile-style-tab #same_settings',
										'data_hide_when' => '1',
										'title'          => '<span class="not_samesettings select_styles_issue_description" style="font-size: 0.9em; display: inline;">If Styles for desktop, mobile not selected as expected <span style="color: #039be5; cursor: pointer;">Check this</span>, - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/select-styles/#styles-not-applied' ) ) . '" class="external-link">' . __( 'more info', 'click-to-chat-for-whatsapp' ) . ' <span class="dashicons dashicons-external"></span></a></span>',
										// 'title'          => '<span class="not_samesettings select_styles_issue_description" style="font-size: 0.9em; display: inline;">If the selected styles are not appearing correctly due to caching <span style="color: #039be5; cursor: pointer;">Enable this option</span> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/select-styles/#styles-not-applied" class="external-link">more info <span class="dashicons dashicons-external"></span></a></span>',
										'fields'         => array(
											array(
												'field_type' => 'field_checkbox',
												'id'    => 'select_styles_issue',
												'value' => '1',
												'option_group' => 'ht_ctc_group',
												'label' => __( 'Check this only, If styles for mobile, desktop not selected as expected(due to cache)', 'click-to-chat-for-whatsapp' ),
											),
										),
									),
								),
							),
						),
					),
				),
			);
			return $values;
		}

		/**
		 * Group Display Settings Card
		 */
		/**
		 * Group Devices Card
		 */
		private static function card_group_devices() {
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
		 * Group Pages Card
		 */
		private static function card_group_pages() {
			$pages_fields = array(
				array(
					'field_type'   => 'field_radio',
					'type'         => 'segment',
					'label'        => 'Global Display',
					'id'           => 'global_display',
					'option_group' => 'ht_ctc_group[display]',
					'options'      => array(
						'show' => __( 'Show', 'click-to-chat-for-whatsapp' ),
						'hide' => __( 'Hide', 'click-to-chat-for-whatsapp' ),
					),
					'default'      => 'show',
					'help'         => 'Global setting for all pages',
				),
				array(
					'field_type' => 'block_sub_heading',
					'title'      => __( 'Overwrite the Global settings', 'click-to-chat-for-whatsapp' ),
				),
				self::page_display_field( 'Home Page', 'home' ),
				self::page_display_field( 'Posts', 'posts' ),
				self::page_display_field( 'Pages', 'pages' ),
				self::page_display_field( 'Archive Pages', 'archive' ),
				self::page_display_field( 'Category Pages', 'category' ),
				self::page_display_field( '404 Page', 'page_404' ),
			);

			// Add WooCommerce settings only if WooCommerce is active
			if ( class_exists( 'WooCommerce' ) ) {
				$woo_fields   = array(
					array(
						'field_type' => 'block_sub_heading',
						'title'      => 'WooCommerce Pages',
					),
					self::page_display_field( 'Single Product Pages', 'woo_product' ),
					self::page_display_field( 'Shop Page', 'woo_shop' ),
					self::page_display_field( 'Cart Page', 'woo_cart' ),
					self::page_display_field( 'Checkout Page', 'woo_checkout' ),
					self::page_display_field( 'Thank You / Order Received Page', 'woo_order_received' ),
					self::page_display_field( 'My Account Page', 'woo_account' ),
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

			// Add page/category list fields
			$list_fields = array(
				array(
					'field_type'  => 'block_sub_heading',
					'title'       => 'Page/Category Lists',
					'description' => 'Specify individual pages (by ID) or categories (by name)',
				),
				self::list_field( sprintf( '%1$s (by ID)', __( 'Hide on this pages', 'click-to-chat-for-whatsapp' ) ), 'list_hideon_pages', 'Enter page IDs where you want to hide the group button', '#global_display_show', 'show' ),
				self::list_field( sprintf( '%1$s (by name)', __( 'Hide on this Category posts', 'click-to-chat-for-whatsapp' ) ), 'list_hideon_cat', 'Enter category names where you want to hide the group button', '#global_display_show', 'show' ),
				self::list_field( sprintf( '%1$s (by ID)', __( 'Show on this pages', 'click-to-chat-for-whatsapp' ) ), 'list_showon_pages', 'Enter page IDs where you want to show the group button', '#global_display_hide', 'hide' ),
				self::list_field( sprintf( '%1$s (by name)', __( 'Show on this Category posts', 'click-to-chat-for-whatsapp' ) ), 'list_showon_cat', 'Enter category names where you want to show the group button', '#global_display_hide', 'hide' ),
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
		 * Features Card
		 */
		private static function card_features() {
			$values = array(
				'field_type' => 'card',
				'title'      => 'Features',
				'class_pr'   => 'ctc-features-section',
				'fields'     => array(
					array(
						'field_type' => 'block_feature_box',
						'icon'       => 'code',
						'label'      => 'Shortcode',
						'content'    => sprintf( '%1$s: <code class="ctc-feature-code">[ht-ctc-group]</code> - <a target="_blank" href="' . esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/shortcodes-group/' ) ) . '" class="external-link">%2$s <span class="dashicons dashicons-external"></span></a>', __( 'Shortcodes for Group Chat', 'click-to-chat-for-whatsapp' ), __( 'more info', 'click-to-chat-for-whatsapp' ) ),
					),
				),
			);
			return $values;
		}
	}
}
