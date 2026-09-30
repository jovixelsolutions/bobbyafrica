<?php
/**
 * Meta box - change values at page level. (2026 admin UI)
 *
 * Admin2 copy of new/admin/admin_commons/class-ht-ctc-metabox.php.
 * Kept as a separate class so each admin UI's meta box logic can change
 * independently - update this file for the 2026 UI, the admin_commons file
 * for the 2019 UI.
 *
 * @package Click_To_Chat
 * @subpackage Admin2
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_MetaBox' ) ) {

	/**
	 * Meta box class for Click to Chat plugin.
	 */
	class HT_CTC_Admin_MetaBox {

		/**
		 * Sanitizer used for a page-level field the map does not name.
		 *
		 * Textarea rather than text: it strips tags but keeps line breaks, so a field
		 * this build knows nothing about (a PRO / add-on field, or one from a newer
		 * version) is never mangled beyond recognition. Matches what free 4.41 - 4.42.x
		 * applied to every page-level field.
		 *
		 * @var string
		 */
		const DEFAULT_SANITIZE_CALLBACK = 'sanitize_textarea_field';

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

			$othersettings = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );

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

			$othersettings    = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );
			$ht_ctc_pagelevel = get_post_meta( $current_post->ID, 'ht_ctc_pagelevel', true );
			?>

		<p class="description">
			<?php esc_html_e( 'Change values at', 'click-to-chat-for-whatsapp' ); ?>
			<a target="_blank" href="<?php echo esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/' ) ); ?>">
				<?php esc_html_e( 'Page level', 'click-to-chat-for-whatsapp' ); ?>
			</a>
		</p>

			<?php
			// Defaults.
			$number         = isset( $ht_ctc_pagelevel['number'] ) ? esc_attr( $ht_ctc_pagelevel['number'] ) : '';
			$call_to_action = isset( $ht_ctc_pagelevel['call_to_action'] ) ? esc_attr( $ht_ctc_pagelevel['call_to_action'] ) : '';
			$pre_filled     = isset( $ht_ctc_pagelevel['pre_filled'] ) ? esc_attr( $ht_ctc_pagelevel['pre_filled'] ) : '';
			$show_hide      = isset( $ht_ctc_pagelevel['show_hide'] ) ? esc_attr( $ht_ctc_pagelevel['show_hide'] ) : '';

			$options = HT_CTC_Utils::get_option( 'ht_ctc_chat_options' );

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
					<a href="<?php echo esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/whatsapp-number/' ) ); ?>" target="_blank">
						<?php esc_html_e( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?>
					</a> <?php esc_html_e( 'with country code', 'click-to-chat-for-whatsapp' ); ?>
				</p>
			</div>

			<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
				<p class="ht-ctc-meta-description">
					<a href="<?php echo esc_url( HT_CTC_Utils::pro_url( 'inline', 'page_custom_link', 'https://holithemes.com/plugins/click-to-chat/docs/custom-url/' ) ); ?>" target="_blank" rel="noopener">Custom Link</a> (PRO)
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
		 * Page-level field map: field key => sanitize callback.
		 *
		 * The map decides the SANITIZE CALLBACK, not (yet) what may be saved — an
		 * unlisted key is still stored, sanitized with DEFAULT_SANITIZE_CALLBACK. See
		 * save_meta_box() for why, and for the plan to tighten this later.
		 *
		 * A callback is the name of an HT_CTC_Sanitizer method — the same vocabulary as
		 * the REST schema's `sanitization_callbacks`, so a field is sanitized identically
		 * whether it is saved globally or per page. Unknown names fall back to
		 * sanitize_text_field, so a typo weakens nothing.
		 *
		 * select / radio / checkbox values just use sanitize_text_field: the front end
		 * compares them against known literals, so an unrecognized value has no effect.
		 *
		 * PRO / add-ons register their own fields through the filter.
		 *
		 * @return array Field key => HT_CTC_Sanitizer method name.
		 */
		public function pagelevel_fields() {

			$field_map = array(
				'number'         => 'ctc_sanitize_whatsapp_number',
				'call_to_action' => 'ctc_sanitize_emoji_text',
				'pre_filled'     => 'ctc_sanitize_emoji_textarea',
				// Radio: 'Default' posts '' and is intentionally not stored.
				'show_hide'      => 'sanitize_text_field',
				'group_id'       => 'sanitize_text_field',
			);

			$field_map = apply_filters( 'ht_ctc_fh_pagelevel_fields', $field_map );

			return is_array( $field_map ) ? $field_map : array();
		}


		/**
		 * Sanitize one page-level value with the callback its field declares.
		 *
		 * @param mixed  $value             Raw (unslashed) request value.
		 * @param string $key               Field key.
		 * @param string $sanitize_callback HT_CTC_Sanitizer method name — see pagelevel_fields().
		 * @return string Sanitized value; '' when the value is unusable (caller drops it).
		 */
		private function sanitize_field( $value, $key, $sanitize_callback ) {

			// Page-level fields are scalar. Arrays / objects are never stored.
			if ( ! is_scalar( $value ) ) {
				return '';
			}

			$value             = (string) $value;
			$sanitize_callback = is_string( $sanitize_callback ) ? $sanitize_callback : self::DEFAULT_SANITIZE_CALLBACK;

			if ( class_exists( 'HT_CTC_Sanitizer' ) ) {
				// sanitize_value() resolves the name against its own allow-list of
				// sanitizers and falls back to sanitize_text_field for anything else.
				return HT_CTC_Sanitizer::sanitize_value( $value, $key, array( $key => $sanitize_callback ) );
			}

			// Shared sanitizer unavailable: strip rather than store raw input.
			return function_exists( 'sanitize_textarea_field' ) ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
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

			if ( ! isset( $_POST['ht_ctc_pagelevel'] ) || ! is_array( $_POST['ht_ctc_pagelevel'] ) ) {
				return;
			}

			// Not sanitized here: every value is sanitized individually below, by the
			// sanitizer its field declares in pagelevel_fields(). A blanket
			// sanitize_textarea_field() pass here would strip the editor fields' markup
			// before their own sanitizer ever sees it.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- per-field sanitizing, see sanitize_field().
			$raw = wp_unslash( $_POST['ht_ctc_pagelevel'] );

			// Shared leaf sanitizers — the same ones the REST save path uses.
			HT_CTC_Utils::load_class( 'new/inc/api/utils/class-ht-ctc-sanitizer.php', 'HT_CTC_Sanitizer' );

			$field_map        = $this->pagelevel_fields();
			$ht_ctc_pagelevel = array();

			/**
			 * Every posted field is saved; the map only picks its sanitize callback.
			 *
			 * Deliberately NOT an allow-list yet: 'ht_ctc_fh_pagelevel_fields' does not
			 * exist before free 4.43, so PRO 2.21 / 2.22 register nothing through it and
			 * dropping unlisted keys would silently wipe every PRO page-level setting on
			 * those sites. Unlisted keys are kept, sanitized with
			 * DEFAULT_SANITIZE_CALLBACK — what 4.41 - 4.42.x did to every field.
			 *
			 */

			// allow-list (1 of 2): keys already on this post stay allowed.
			// $stored = get_post_meta( $post_id, 'ht_ctc_pagelevel', true );
			// $stored = is_array( $stored ) ? $stored : array();

			foreach ( $raw as $key => $value ) {

				$key = HT_CTC_Sanitizer::sanitize_key( $key );

				if ( '' === $key ) {
					continue;
				}

				// allow-list (2 of 2): drop anything the map does not name.
				// if ( ! isset( $field_map[ $key ] ) && ! array_key_exists( $key, $stored ) ) {
				// continue;
				// }

				$sanitize_callback = isset( $field_map[ $key ] ) ? $field_map[ $key ] : self::DEFAULT_SANITIZE_CALLBACK;
				$value             = $this->sanitize_field( $value, $key, $sanitize_callback );

				// Empty means "no page-level value" — leave it out so the global applies.
				if ( '' === $value ) {
					continue;
				}

				$ht_ctc_pagelevel[ $key ] = $value;
			}

			if ( empty( $ht_ctc_pagelevel ) ) {
				delete_post_meta( $post_id, 'ht_ctc_pagelevel' );
				return;
			}

			update_post_meta( $post_id, 'ht_ctc_pagelevel', $ht_ctc_pagelevel );
		}
	}

	new HT_CTC_Admin_MetaBox();

} // END class_exists check.
