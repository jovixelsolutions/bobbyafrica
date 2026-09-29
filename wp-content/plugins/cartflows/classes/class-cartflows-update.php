<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Cartflows_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function init() {

			do_action( 'cartflows_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-version', CARTFLOWS_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_VER, '=' ) ) {
				return;
			}

			$this->logger_files();

			if ( version_compare( $saved_version, '1.1.22', '<' ) ) {
				update_option( 'wcf_setup_skipped', true );
			}

			if ( version_compare( $saved_version, '1.2.0', '<' ) ) {

				$this->changed_wp_templates();
			}

			/* Add legacy admin option */
			if ( version_compare( $saved_version, '1.6.0', '<' ) ) {
				update_option( 'cartflows-old-ui-user', true );
				update_option( 'cartflows-legacy-admin', true );
				update_option( 'cartflows-legacy-meta-show-design-options', true );
			}

			/* Updating meta for global checkout migration & Permalinks */
			if ( version_compare( $saved_version, '1.10.0', '<' ) ) {

				$global_checkout = \Cartflows_Helper::get_common_setting( 'global_checkout' );

				if ( $global_checkout ) {
					$flow_id = wcf()->utils->get_flow_id_from_step_id( $global_checkout );
					if ( $flow_id ) {
						update_option( '_cartflows_store_checkout', $flow_id );
						update_option( '_cartflows_old_global_checkout', $global_checkout );
						delete_post_meta( $global_checkout, 'wcf-checkout-products' );
					}
				}

				update_option( 'cartflows_show_weekly_report_email_notice', 'yes' );

				$permalink_settings = Cartflows_Helper::get_admin_settings_option( '_cartflows_permalink', false, false );

				if ( ! $permalink_settings ) {

					$default_settings = array(
						'permalink'           => CARTFLOWS_STEP_POST_TYPE,
						'permalink_flow_base' => CARTFLOWS_FLOW_POST_TYPE,
						'permalink_structure' => '',

					);

					Cartflows_Helper::update_admin_settings_option( '_cartflows_permalink', $default_settings );

				}
			}
			// Update required license key to lowercase. Can be removed after 3 major update.
			if ( version_compare( $saved_version, '1.11.1', '<' ) && _is_cartflows_pro() ) {

				$api_key      = get_option( 'wc_am_client_CartFlows_api_key' );
				$api_key_data = get_option( 'wc_am_client_CartFlows' );

				// Take backup of options.
				update_option(
					'cartflows_license_backup_data',
					array(
						'wc_am_client_cartflows_api_key' => $api_key,
						'wc_am_client_cartflows'         => $api_key_data,
					)
				);

				delete_option( 'wc_am_client_CartFlows_api_key' );
				delete_option( 'wc_am_client_CartFlows' );

				// Delete the cached value for old user ( before v1.11.0 and after v1.11.0 ) so WP can add new value.
				wp_cache_flush();

				if ( $api_key_data ) {

					$new_data = array(
						'wc_am_client_cartflows_api_key' => isset( $api_key_data['wc_am_client_CartFlows_api_key'] ) ? $api_key_data['wc_am_client_CartFlows_api_key'] : $api_key_data['wc_am_client_cartflows_api_key'],
					);

					update_option( 'wc_am_client_cartflows', $new_data );
				}

				if ( $api_key ) {
					update_option( 'wc_am_client_cartflows_api_key', $api_key );
				}
			}

			// Set default page builder to "Other" for those who have set it as "DIVI".
			if ( version_compare( $saved_version, '2.0.0', '<' ) ) {
				$common_settings = get_option( '_cartflows_common', array() );

				if ( is_array( $common_settings ) && 'divi' === $common_settings['default_page_builder'] ) {

					$common_settings['default_page_builder'] = 'elementor';
					update_option( '_cartflows_common', $common_settings );
				}
			}

			// Migrate old combined custom script meta to separate JS and CSS meta fields.
			if ( version_compare( $saved_version, '2.2.2', '<' ) ) {
				$this->migrate_custom_scripts();
			}

			// 3.0 ships a redesigned admin UI. Existing users (saved_version < 3.0.0) keep the
			// legacy admin until they opt into the new UI from the in-app notice or the
			// Advanced-tab toggle. Saved as 'enable' to match the toggle's backComp format
			// so the field round-trips correctly. Fresh installs short-circuit at the
			// `! $saved_version` early return above and default to the new UI.
			if ( is_string( $saved_version ) && version_compare( $saved_version, '3.0.0', '<' ) ) {
				update_option( 'cartflows-legacy-admin', 'enable' );
			}

			// Update auto saved version number.
			update_option( 'cartflows-version', CARTFLOWS_VER );

			// Update cartflows asset version to regenerate the dynamic css. We are using the time() function to add the random number.
			update_option( 'cartflows-assets-version', time() );

			do_action( 'cartflows_update_after' );
		}

		/**
		 * Loading logger files.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function logger_files() {

			if ( ! defined( 'CARTFLOWS_LOG_DIR' ) ) {

				$upload_dir = wp_upload_dir( null, false );

				define( 'CARTFLOWS_LOG_DIR', $upload_dir['basedir'] . '/cartflows-logs/' );
			}

			wcf()->create_files();
		}

		/**
		 * Set script migration status.
		 *
		 * @param string $status Migration status value.
		 * @since 2.2.2
		 * @return void
		 */
		public function set_script_migration_status( $status ) {
			update_option( 'cartflows_script_migration_status', $status );
		}

		/**
		 * Determine if the migration notice should be shown.
		 *
		 * Returns false if migration is completed or accepted.
		 * Returns false if status is 'skipped' and 7 days have not yet passed.
		 * Returns false if no old script values exist (auto-sets status to 'completed').
		 *
		 * @since 2.2.2
		 * @return bool
		 */
		public function should_show_migration_notice() {
			$status = \CartFlows_Helper::get_script_migration_status();

			if ( 'completed' === $status || 'accepted' === $status ) {
				return false;
			}

			if ( 'skipped' === $status ) {
				$skip_timestamp = (int) get_option( 'cartflows_script_migration_skip_timestamp', 0 );

				// Show again only after 7 days have passed since last skip.
				if ( ( time() - $skip_timestamp ) < WEEK_IN_SECONDS ) {
					return false;
				}
			}

			// Check cached result first to avoid running the meta query on every page load.
			$has_old_scripts = get_option( 'cartflows_has_old_custom_scripts', '' );

			if ( '' === $has_old_scripts ) {
				// First time check — run the query once and cache the result.
				$has_old_scripts = $this->has_old_custom_scripts() ? 'yes' : 'no';
				update_option( 'cartflows_has_old_custom_scripts', $has_old_scripts, true );
			}

			// If no old script values exist, auto-complete migration and enable the code editor.
			if ( 'yes' !== $has_old_scripts ) {
				$this->set_script_migration_status( 'completed' );
				return false;
			}

			return true;
		}

		/**
		 * Check if any posts have old combined custom script meta values.
		 *
		 * Queries the database for any cartflows_step or cartflows_flow posts
		 * that have non-empty 'wcf-custom-script' or 'wcf-flow-custom-script' meta.
		 *
		 * @since 2.2.3
		 * @return bool True if old script data exists, false otherwise.
		 */
		public function has_old_custom_scripts() {

			global $wpdb;

			$result = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT 1 FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE p.post_type IN (%s, %s)
					AND pm.meta_key IN (%s, %s)
					AND pm.meta_value != ''
					LIMIT 1",
					'cartflows_step',
					'cartflows_flow',
					'wcf-custom-script',
					'wcf-flow-custom-script'
				)
			);

			return ( null !== $result );
		}

		/**
		 * Get current migration skip count.
		 *
		 * @since 2.2.2
		 * @return int
		 */
		public function get_migration_skip_count() {
			return (int) get_option( 'cartflows_script_migration_skip_count', 0 );
		}

		/**
		 * Increment migration skip count and record the skip timestamp.
		 *
		 * @since 2.2.2
		 * @return void
		 */
		public function increment_migration_skip_count() {
			$count = $this->get_migration_skip_count();
			update_option( 'cartflows_script_migration_skip_count', $count + 1 );
			update_option( 'cartflows_script_migration_skip_timestamp', time() );
		}

		/**
		 * Run on-demand migration of custom scripts triggered by user action.
		 *
		 * Unlike migrate_custom_scripts() which runs during version updates,
		 * this method is called only when the user explicitly clicks "Migrate Data"
		 * in the admin notice. It returns a count of migrated posts and updates
		 * the migration status to 'completed'.
		 *
		 * @since 2.2.2
		 * @return int Number of posts that had data migrated.
		 */
		public function migrate_custom_scripts_on_demand() {

			$migrated_count = 0;

			// Migrate step-level custom scripts.
			$migrated_count += $this->migrate_post_type_scripts_counted(
				'cartflows_step',
				'wcf-custom-script',
				'wcf-step-custom-js',
				'wcf-step-custom-css'
			);

			// Migrate flow-level custom scripts.
			$migrated_count += $this->migrate_post_type_scripts_counted(
				'cartflows_flow',
				'wcf-flow-custom-script',
				'wcf-flow-custom-js',
				'wcf-flow-custom-css'
			);

			$this->set_script_migration_status( 'completed' );

			return $migrated_count;
		}

		/**
		 * Migrate custom scripts for a specific post type and return the count of migrated posts.
		 *
		 * @param string $post_type The post type to query.
		 * @param string $old_meta_key The old combined script meta key.
		 * @param string $new_js_meta_key The new JS-only meta key.
		 * @param string $new_css_meta_key The new CSS-only meta key.
		 *
		 * @since 2.2.2
		 * @return int Number of posts migrated.
		 */
		public function migrate_post_type_scripts_counted( $post_type, $old_meta_key, $new_js_meta_key, $new_css_meta_key ) {

			global $wpdb;

			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT p.ID, pm.meta_value FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
					WHERE p.post_type = %s AND pm.meta_key = %s AND pm.meta_value != ''
					LIMIT 500",
					$post_type,
					$old_meta_key
				)
			);

			if ( empty( $results ) || ! is_array( $results ) ) {
				return 0;
			}

			$migrated = 0;

			foreach ( $results as $row ) {
				$post_id   = $row->ID;
				$old_value = $row->meta_value;

				// Skip if the new meta fields already have values (don't overwrite manual entries).
				$existing_js  = get_post_meta( $post_id, $new_js_meta_key, true );
				$existing_css = get_post_meta( $post_id, $new_css_meta_key, true );

				if ( ! empty( $existing_js ) || ! empty( $existing_css ) ) {
					continue;
				}

				// Decode HTML entities first since the old value was stored encoded.
				$decoded = html_entity_decode( $old_value, ENT_QUOTES, 'UTF-8' );

				// Extract CSS and JS from the combined script.
				$parsed = $this->parse_combined_script( $decoded );

				$did_migrate = false;

				if ( ! empty( $parsed['css'] ) ) {
					update_post_meta( $post_id, $new_css_meta_key, htmlentities( $parsed['css'] ) );
					$did_migrate = true;
				}

				if ( ! empty( $parsed['js'] ) ) {
					update_post_meta( $post_id, $new_js_meta_key, htmlentities( $parsed['js'] ) );
					$did_migrate = true;
				}

				if ( $did_migrate ) {
					++$migrated;
				}
			}

			return $migrated;
		}

		/**
		 * Migrate old combined custom script meta to separate JS and CSS meta fields.
		 *
		 * Previously, a single textarea accepted both JS and CSS with <script>/<style> tags.
		 * Now we have separate CodeMirror editors for JS and CSS, so we need to split the
		 * old combined value into the new separate meta fields.
		 *
		 * @since 2.2.2
		 * @return void
		 */
		public function migrate_custom_scripts() {

			// Migrate step-level custom scripts.
			$this->migrate_post_type_scripts(
				'cartflows_step',
				'wcf-custom-script',
				'wcf-step-custom-js',
				'wcf-step-custom-css'
			);

			// Migrate flow-level custom scripts.
			$this->migrate_post_type_scripts(
				'cartflows_flow',
				'wcf-flow-custom-script',
				'wcf-flow-custom-js',
				'wcf-flow-custom-css'
			);
		}

		/**
		 * Migrate custom scripts for a specific post type from old combined meta to separate JS/CSS meta.
		 *
		 * @param string $post_type The post type to query.
		 * @param string $old_meta_key The old combined script meta key.
		 * @param string $new_js_meta_key The new JS-only meta key.
		 * @param string $new_css_meta_key The new CSS-only meta key.
		 *
		 * @since 2.2.2
		 * @return void
		 */
		public function migrate_post_type_scripts( $post_type, $old_meta_key, $new_js_meta_key, $new_css_meta_key ) {

			global $wpdb;

			// Get all posts of this type that have the old custom script meta with a non-empty value.
			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT p.ID, pm.meta_value FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
					WHERE p.post_type = %s AND pm.meta_key = %s AND pm.meta_value != ''
					LIMIT 500", // Limit to 500 posts per batch to avoid memory issues.
					$post_type,
					$old_meta_key
				)
			);

			if ( empty( $results ) || ! is_array( $results ) ) {
				return;
			}

			foreach ( $results as $row ) {
				$post_id   = $row->ID;
				$old_value = $row->meta_value;

				// Skip if the new meta fields already have values (don't overwrite manual entries).
				$existing_js  = get_post_meta( $post_id, $new_js_meta_key, true );
				$existing_css = get_post_meta( $post_id, $new_css_meta_key, true );

				if ( ! empty( $existing_js ) || ! empty( $existing_css ) ) {
					continue;
				}

				// Decode HTML entities first since the old value was stored encoded.
				$decoded = html_entity_decode( $old_value, ENT_QUOTES, 'UTF-8' );

				// Extract CSS and JS from the combined script.
				$parsed = $this->parse_combined_script( $decoded );

				if ( ! empty( $parsed['css'] ) ) {
					// Store encoded with htmlentities to match the FILTER_SCRIPT save format.
					update_post_meta( $post_id, $new_css_meta_key, htmlentities( $parsed['css'] ) );
				}

				if ( ! empty( $parsed['js'] ) ) {
					// Store encoded with htmlentities to match the FILTER_SCRIPT save format.
					update_post_meta( $post_id, $new_js_meta_key, htmlentities( $parsed['js'] ) );
				}
			}
		}

		/**
		 * Parse a combined script string to extract CSS and JS portions.
		 *
		 * The old custom script field could contain:
		 * - <style>...CSS...</style> blocks
		 * - <script>...JS...</script> blocks
		 * - Bare JS code (no tags, treated as JS)
		 *
		 * @param string $content The combined script content (decoded).
		 * @return array Associative array with 'js' and 'css' keys.
		 *
		 * @since 2.2.2
		 */
		public function parse_combined_script( $content ) {

			$css = '';
			$js  = '';

			// Extract all <style>...</style> blocks as CSS.
			if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $content, $style_matches ) ) {
				$css = trim( implode( "\n", $style_matches[1] ) );
			}

			// Extract all <script>...</script> blocks as JS.
			if ( preg_match_all( '/<script[^>]*>(.*?)<\/script>/is', $content, $script_matches ) ) {
				$js = trim( implode( "\n", $script_matches[1] ) );
			}

			// Remove all <style>...</style> and <script>...</script> blocks from the content.
			$remaining = preg_replace( '/<style[^>]*>.*?<\/style>/is', '', $content );
			if ( null === $remaining ) {
				$remaining = '';
			}

			$remaining = preg_replace( '/<script[^>]*>.*?<\/script>/is', '', $remaining );
			if ( null === $remaining ) {
				$remaining = '';
			}

			$remaining = trim( $remaining );

			// Any remaining content (not inside tags) is treated as JS.
			if ( ! empty( $remaining ) ) {
				$js = trim( $js . "\n" . $remaining );
			}

			return array(
				'css' => $css,
				'js'  => $js,
			);
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function changed_wp_templates() {

			global $wpdb;

			$query_results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT  {$wpdb->posts}.ID FROM {$wpdb->posts}  LEFT JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )
                where {$wpdb->posts}.post_type = %s AND  {$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value != %s AND {$wpdb->postmeta}.meta_value != %s",
					'cartflows_step',
					'_wp_page_template',
					'cartflows-canvas',
					'cartflows-default'
				)
			); // db call ok; no-cache ok.

			if ( is_array( $query_results ) && ! empty( $query_results ) ) {

				require_once CARTFLOWS_DIR . 'classes/importer/batch-process/class-cartflows-change-template-batch.php';

				wcf()->logger->log( '(✓) Update Templates BATCH Started!' );

				$change_template_batch = new Cartflows_Change_Template_Batch();

				foreach ( $query_results as $query_result ) {

					wcf()->logger->log( '(✓) POST ID ' . $query_result->ID );
					$change_template_batch->push_to_queue( $query_result->ID );
				}

				$change_template_batch->save()->dispatch();
			}
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Update::get_instance();

endif;
