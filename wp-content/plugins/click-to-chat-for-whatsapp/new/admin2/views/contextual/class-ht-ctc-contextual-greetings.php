<?php
/**
 * Contextual Greetings Settings Provider
 *
 * Single source of truth for the per-template greetings field definitions.
 *
 * The free cards these replace are no longer served — HT_CTC_Settings_Greetings::fields()
 * does not call them — so on a free site each id below appears once.
 *
 * NOT so with PRO. Its greetings cards declare the same ids (header_bg_color,
 * main_bg_color, message_box_bg_color, cta_style) and still render on this tab, twice
 * over: once per PRO template. Opening this panel there makes a third. Saving is safe,
 * because the names differ (ht_ctc_greetings_1[…] vs ht_ctc_greetings_pro_1[…]), but a
 * <label for> resolves to whichever rendered first, and so would any unscoped data_watch
 * on those ids — scope them like '#greetings_1 #cta_style', which the card id supports.
 *
 * Parking cannot help here: it deconflicts contextual cards against each other, not
 * against the rest of the page. Closing it properly means scoping PRO's ids per card.
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 4.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Contextual_Greetings' ) ) {

	/**
	 * Contextual Greetings class.
	 */
	class HT_CTC_Contextual_Greetings {

		/**
		 * Get all greetings contextual fields.
		 *
		 * @return array<string, array> Keyed by template ID.
		 */
		public static function fields() {
			$fields = array();

			$fields['greetings_1'] = self::greetings_1();
			$fields['greetings_2'] = self::greetings_2();

			/**
			 * Filter the whole set of contextual greetings templates.
			 *
			 * Adds or removes templates - PRO registers its own here. To change the fields
			 * of a template already defined above, use its per-template filter instead:
			 * ht_ctc_fh_contextual_fields_greetings_1.
			 *
			 * A template added here also needs its tile to point at it
			 * (data-contextual-group / data-contextual-id, via the option's 'attributes'),
			 * and its option group registered in the settings schema - a key the schema
			 * does not allow is dropped on save without an error.
			 *
			 * @since 4.43
			 * @param array $fields Template definitions keyed by contextual id.
			 */
			$fields = apply_filters( 'ht_ctc_fh_contextual_fields_greetings_fields', $fields );

			return $fields;
		}

		/**
		 * Greetings Dialog - 1
		 *
		 * @return array
		 */
		private static function greetings_1() {
			$values = array(
				'title'  => __( 'Greetings Dialog - 1', 'click-to-chat-for-whatsapp' ),
				'desc'   => __( 'Greetings-1 - Customizable Design', 'click-to-chat-for-whatsapp' ),
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 'header_bg_color',
						'label'        => __( 'Header - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#075e54',
						'help'         => __( 'Header - Background Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 'main_bg_color',
						'label'        => __( 'Main Content - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#ece5dd',
						'help'         => __( 'Main Content - Background Color', 'click-to-chat-for-whatsapp' ),
					),
					array(
						'field_type'   => 'field_color',
						'id'           => 'message_box_bg_color',
						'label'        => __( 'Message Box - Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_1',
						'default'      => '#dcf8c6',
						'help'         => 'Main Content as a Message Box with Background Color',
					),
					array(
						'field_type' => 'block_content_details',
						'title'      => 'Background image, Call to Action - button type',
						'fields'     => array(
							array(
								'field_type'   => 'field_checkbox',
								'id'           => 'main_bg_image',
								'label'        => __( 'Background image', 'click-to-chat-for-whatsapp' ),
								'option_group' => 'ht_ctc_greetings_1',
								'help'         => 'Add a WhatsApp-like background image to the main content.',
							),
							array(
								'field_type'   => 'field_select',
								'id'           => 'cta_style',
								'label'        => __( 'Call to Action - button type', 'click-to-chat-for-whatsapp' ),
								'option_group' => 'ht_ctc_greetings_1',
								'options'      => array(

									/*
									 * Each option names the style it stands for, so the
									 * Customize trigger below opens the right one without
									 * deriving an id from the stored value.
									 */
									'1'   => array(
										'label'      => 'Style 1: Theme Button',
										'attributes' => array( 'data-contextual-id' => 'style_1' ),
									),
									'7_1' => array(
										'label'      => 'Style 7 Extend: Rounded Button',
										'attributes' => array( 'data-contextual-id' => 'style_7_1' ),
									),
								),
							),

							/*
							 * A trigger inside the panel. The panel does not move for this one
							 * — it is already open on this card — it swaps to the style card in
							 * place, and the edits made here are kept for when it swaps back.
							 */
							array(
								'field_type'       => 'block_contextual_trigger',
								'label'            => 'Customize',
								'contextual_group' => 'contextual_styles',
								'contextual_watch' => '#cta_style',
							),
						),
						'style'      => 'margin: 1px 0px 5px 0px;',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/greetings-1/' ),
						'label'      => 'Greetings-1',
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);

			return $values;
		}

		/**
		 * Greetings Dialog - 2
		 *
		 * @return array
		 */
		private static function greetings_2() {
			$values = array(
				'title'  => __( 'Greetings Dialog - 2', 'click-to-chat-for-whatsapp' ),
				'desc'   => __( 'Greetings-2 - Content Specific', 'click-to-chat-for-whatsapp' ),
				'fields' => array(
					array(
						'field_type'   => 'field_color',
						'id'           => 'bg_color',
						'label'        => __( 'Background Color', 'click-to-chat-for-whatsapp' ),
						'option_group' => 'ht_ctc_greetings_2',
						'default'      => '#ffffff',
						'help'         => 'Greetings Dialog Background Color',
					),

					/*
					 * Greetings-2's call to action is always Style 1, so this pins that item
					 * outright instead of watching a field. Replaces the line of prose that
					 * used to send the user to Click to Chat -> Customize.
					 */
					array(
						'field_type'       => 'block_contextual_trigger',
						'label'            => 'Customize Call to Action',
						'contextual_group' => 'contextual_styles',
						'contextual_id'    => 'style_1',
						'help'             => 'Call to Action button uses Style 1.',
					),
					array(
						'field_type' => 'block_external_link',
						'url'        => HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/docs/greetings-2/' ),
						'label'      => 'Greetings-2',
						'icon'       => 'dashicons dashicons-external',
					),
				),
			);

			return $values;
		}
	}
}
