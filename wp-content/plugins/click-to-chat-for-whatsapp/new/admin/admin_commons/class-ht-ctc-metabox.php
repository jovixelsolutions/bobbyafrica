<?php
/**
 * Meta box
 * change values at page level
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_MetaBox' ) ) {

	/**
	 * Meta box class for Click to Chat plugin.
	 */
	class HT_CTC_MetaBox {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Call hooks.
			$this->hooks();
		}

		/**
		 * Initialize hooks.
		 */
		public function hooks() {

			/**
			 * Initialize plugin hooks:
			 * - If 'disable_page_level_settings' is not set in 'ht_ctc_othersettings' option,
			 *   then:
			 *   - Add a meta box to all posts and pages.
			 *   - Save meta box data when the post is saved.
			 */

			$othersettings = get_option( 'ht_ctc_othersettings' );

			if ( ! isset( $othersettings['disable_page_level_settings'] ) ) {
				// Add meta box.
				add_action( 'add_meta_boxes', array( $this, 'meta_box' ) );
				// Save meta box.
				add_action( 'save_post', array( $this, 'save_meta_box' ) );
			}
		}


		/**
		 * Add meta box.
		 */
		public function meta_box() {

			$post_types = get_post_types( array( 'public' => true ) );

			foreach ( $post_types as $type ) {
				if ( 'attachment' !== $type ) {
					add_meta_box(
						'ht_ctc_settings_meta_box',             // Id.
						'Click to Chat',                        // Title.
						array( $this, 'display_meta_box' ),     // Callback.
						$type,                                      // Post_type.
						'side',                                 // Context.
						'default'                               // Priority.
					);
				}
			}
		}


		/**
		 * Render meta box content.
		 *
		 * @param WP_Post $current_post The current post object.
		 */
		public function display_meta_box( $current_post ) {
			wp_nonce_field( 'ht_ctc_page_meta_box', 'ht_ctc_page_meta_box_nonce' );

			$othersettings    = get_option( 'ht_ctc_othersettings' );
			$ht_ctc_pagelevel = get_post_meta( $current_post->ID, 'ht_ctc_pagelevel', true );
			?>

		<p class="description">
			<?php esc_html_e( 'Change values at', 'click-to-chat-for-whatsapp' ); ?>
			<a target="_blank" href="https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/">
				<?php esc_html_e( 'Page level', 'click-to-chat-for-whatsapp' ); ?>
			</a>
		</p>

			<?php
			// Defaults.
			$number         = isset( $ht_ctc_pagelevel['number'] ) ? esc_attr( $ht_ctc_pagelevel['number'] ) : '';
			$call_to_action = isset( $ht_ctc_pagelevel['call_to_action'] ) ? esc_attr( $ht_ctc_pagelevel['call_to_action'] ) : '';
			$pre_filled     = isset( $ht_ctc_pagelevel['pre_filled'] ) ? esc_attr( $ht_ctc_pagelevel['pre_filled'] ) : '';
			$show_hide      = isset( $ht_ctc_pagelevel['show_hide'] ) ? esc_attr( $ht_ctc_pagelevel['show_hide'] ) : '';

			$options = get_option( 'ht_ctc_chat_options' );

			$ph_number         = '';
			$ph_call_to_action = '';
			$ph_pre_filled     = '';
			// If db values are correct.
			if ( is_array( $options ) ) {
				$ph_number         = ( isset( $options['number'] ) ) ? esc_attr( $options['number'] ) : '';
				$ph_call_to_action = ( isset( $options['call_to_action'] ) ) ? esc_attr( $options['call_to_action'] ) : '';
				$ph_pre_filled     = ( isset( $options['pre_filled'] ) ) ? esc_attr( $options['pre_filled'] ) : '';
			}
			?>


		<style>
			.ht-ctc-meta-box {
				/* border: 1px solid #e2e2e2; */
				/* border-radius: 8px; */
				/* padding: 10px; */
				background: #fff;
				/* box-shadow: 0 2px 4px rgba(0,0,0,0.05); */
				margin-bottom: 20px;
				max-width: 700px;
				box-sizing: border-box;
			}

			.ht-ctc-meta-field {
				margin-bottom: 20px;
			}

			.ht-ctc-meta-field label {
				display: block;
				margin-bottom: 6px;
				font-weight: 600;
				color: #333;
			}

			.ht-ctc-meta-field input[type="text"],
			.ht-ctc-meta-field input[type="number"],
			.ht-ctc-meta-field select,
			.ht-ctc-meta-field textarea {
				width: 100%;
				padding: 10px 12px;
				border: 1px solid #ccc;
				border-radius: 6px;
				font-size: 14px;
				background: #fff;
				box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
				box-sizing: border-box;
				appearance: none;
			}

			.ht-ctc-meta-field textarea {
				min-height: 80px;
				resize: vertical;
			}

			.ht-ctc-radio-group {
				display: flex;
				gap: 24px;
				margin-top: 10px;
			}

			.ht-ctc-radio-group label {
				font-weight: 500;
				color: #444;
			}

			.ht-ctc-meta-section-title {
				font-size: 16px;
				margin-bottom: 14px;
				font-weight: 500;
				border-bottom: 1px solid #eee;
				padding: 0px 0px 6px 0px !important;
				color: #222;
			}

			.ht-ctc-meta-description {
				margin-top: 6px;
				font-size: 13px;
				color: #777;
				line-height: 1.4;
			}

			.ht-ctc-checkbox {
				display: flex;
				align-items: center;
				gap: 8px;
				margin-top: 6px;
			}
		</style>


		<div class="ht-ctc-meta-box">
			<div class="ht-ctc-meta-section-title"><?php esc_html_e( 'Chat Settings', 'click-to-chat-for-whatsapp' ); ?></div>

			<div class="ht-ctc-meta-field">
				<label for="number"><?php esc_html_e( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?></label>
				<input type="text" id="number" name="ht_ctc_pagelevel[number]" value="<?php echo esc_attr( $number ); ?>" placeholder="<?php echo esc_attr( $ph_number ); ?>">
				<p class="ht-ctc-meta-description">
					<a href="https://holithemes.com/plugins/click-to-chat/whatsapp-number/" target="_blank">
						<?php esc_html_e( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?>
					</a> <?php esc_html_e( 'with country code', 'click-to-chat-for-whatsapp' ); ?>
				</p>
			</div>

			<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
				<p class="ht-ctc-meta-description">
					<a href="https://holithemes.com/plugins/click-to-chat/docs/custom-url/" target="_blank">Custom Link</a> (PRO)
				</p>
			<?php } ?>

			<?php do_action( 'ht_ctc_ah_admin_chat_meta_box_after_number', $current_post ); ?>

			<div class="ht-ctc-meta-field">
				<label for="call_to_action"><?php esc_html_e( 'Call to Action', 'click-to-chat-for-whatsapp' ); ?></label>
				<input type="text" id="call_to_action" name="ht_ctc_pagelevel[call_to_action]" value="<?php echo esc_attr( $call_to_action ); ?>" placeholder="<?php echo esc_attr( $ph_call_to_action ); ?>">
			</div>

			<div class="ht-ctc-meta-field">
				<label for="pre_filled"><?php esc_html_e( 'Pre-filled Message', 'click-to-chat-for-whatsapp' ); ?></label>
				<textarea id="pre_filled" name="ht_ctc_pagelevel[pre_filled]" placeholder="<?php echo esc_attr( $ph_pre_filled ); ?>"><?php echo esc_textarea( $pre_filled ); ?></textarea>
			</div>

			<div class="ht-ctc-meta-field">
				<label><?php esc_html_e( 'Display Settings', 'click-to-chat-for-whatsapp' ); ?></label>
				<div class="ht-ctc-radio-group">
					<label>
						<input type="radio" name="ht_ctc_pagelevel[show_hide]" value="show" <?php checked( $show_hide, 'show' ); ?>>
						<?php esc_html_e( 'Show', 'click-to-chat-for-whatsapp' ); ?>
					</label>
					<label>
						<input type="radio" name="ht_ctc_pagelevel[show_hide]" value="hide" <?php checked( $show_hide, 'hide' ); ?>>
						<?php esc_html_e( 'Hide', 'click-to-chat-for-whatsapp' ); ?>
					</label>
					<label>
						<input type="radio" name="ht_ctc_pagelevel[show_hide]" value="" <?php checked( $show_hide, '' ); ?>>
						<?php esc_html_e( 'Default', 'click-to-chat-for-whatsapp' ); ?>
					</label>
				</div>
			</div>
		</div>

			<?php
			do_action( 'ht_ctc_ah_admin_chat_bottom_meta_box', $current_post );

			if ( isset( $othersettings['enable_group'] ) ) {
				$group_id = isset( $ht_ctc_pagelevel['group_id'] ) ? esc_attr( $ht_ctc_pagelevel['group_id'] ) : '';
				?>

			<div class="ht-ctc-meta-box">
				<div class="ht-ctc-meta-section-title"><?php esc_html_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?></div>
				<div class="ht-ctc-meta-field">
					<label for="group_id"><?php esc_html_e( 'Group ID', 'click-to-chat-for-whatsapp' ); ?></label>
					<input type="text" id="group_id" name="ht_ctc_pagelevel[group_id]" value="<?php echo esc_attr( $group_id ); ?>">
				</div>
			</div>

				<?php
			}
		}


		/**
		 * Save meta box.
		 *
		 * @param int $post_id The post ID.
		 */
		public function save_meta_box( $post_id ) {

			// Check if our nonce is set.
			if ( ! isset( $_POST['ht_ctc_page_meta_box_nonce'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_POST['ht_ctc_page_meta_box_nonce'] ) );

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'ht_ctc_page_meta_box' ) ) {
				return;
			}

			// If this is an autosave.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/ht-ctc-admin-formatting.php';

			$editor = array();
			$editor = apply_filters( 'ht_ctc_fh_greetings_setting_meta_editor', $editor );

			if ( isset( $_POST['ht_ctc_pagelevel'] ) ) {

				$ht_ctc_pagelevel = array_filter( map_deep( wp_unslash( $_POST['ht_ctc_pagelevel'] ), 'sanitize_text_field' ) );

				if ( ! empty( $ht_ctc_pagelevel ) ) {

					// Sanitize.
					foreach ( $ht_ctc_pagelevel as $key => $value ) {
						if ( isset( $ht_ctc_pagelevel[ $key ] ) ) {
							if ( 'pre_filled' === $key ) {
								if ( function_exists( 'sanitize_textarea_field' ) ) {
									$new[ $key ] = sanitize_textarea_field( $ht_ctc_pagelevel[ $key ] );
								} else {
									$new[ $key ] = sanitize_text_field( $ht_ctc_pagelevel[ $key ] );
								}
							} elseif ( 'call_to_action' === $key ) {
								$new[ $key ] = sanitize_text_field( $ht_ctc_pagelevel[ $key ] );
							} elseif ( in_array( $key, $editor, true ) ) {
								if ( ! empty( $ht_ctc_pagelevel[ $key ] ) && '' !== $ht_ctc_pagelevel[ $key ] && function_exists( 'ht_ctc_wp_sanitize_text_editor' ) ) {
									$new[ $key ] = ht_ctc_wp_sanitize_text_editor( $ht_ctc_pagelevel[ $key ] );
								}
							} else {
								$new[ $key ] = sanitize_text_field( $ht_ctc_pagelevel[ $key ] );
							}
							$ht_ctc_pagelevel[ $key ] = $new[ $key ];
						}
					}

					update_post_meta( $post_id, 'ht_ctc_pagelevel', $ht_ctc_pagelevel );
				} else {
					delete_post_meta( $post_id, 'ht_ctc_pagelevel', '' );
				}
			}
		}
	}

	new HT_CTC_MetaBox();

} // END class_exists check.
