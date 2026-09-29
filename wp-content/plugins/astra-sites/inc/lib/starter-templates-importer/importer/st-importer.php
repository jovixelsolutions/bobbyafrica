<?php
/**
 * Starter Templates Importer - Module.
 *
 * This file is used to register and manage the Zip AI Modules.
 *
 * @package Starter Templates Importer
 */

namespace STImporter\Importer;

use STImporter\Importer\WXR_Importer\ST_WXR_Importer;
use STImporter\Importer\ST_Importer_Helper;
use STImporter\Importer\ST_Widget_Importer;
use STImporter\Importer\ST_Plugin_Installer;
use STImporter\Importer\ST_Importer_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Module Class.
 */
class ST_Importer {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var self Class object.
	 */
	private static $instance = null;

	/**
	 * Initiator of this class.
	 *
	 * @since 1.0.0
	 * @return self initialized object of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initiate import process flog.
	 *
	 * @since 1.0.0
	 * @param string $template_type template type.
	 * @param string $uuid uuid.
	 * @return array<string, mixed>
	 */
	public static function set_import_process_start_flag( $template_type, $uuid = '' ) {
		ST_Importer_Log::add(
			'Starting import process',
			'info',
			array(
				'template_type' => $template_type,
				'uuid'          => $uuid,
			)
		);

		if ( empty( $uuid ) && 'ai' === $template_type ) {
			ST_Importer_Log::add( 'Import process failed: UUID is empty for AI template', 'error', array( 'template_type' => $template_type ) );
			return array(
				'status' => false,
				'error'  => __( 'uuid is empty.', 'astra-sites' ),
			);
		}

		if ( ! empty( $uuid ) ) {
			update_option( 'astra_sites_ai_import_started', 'yes', false );
			ST_Importer_Log::add( 'AI import started flag set', 'info', array( 'uuid' => $uuid ) );
		}
		do_action( 'st_before_start_import_process' );
		update_option( 'astra_sites_import_started', 'yes' );
		do_action( 'astra_sites_import_start' );

		ST_Importer_Log::add( 'Import process start flag set successfully', 'success', array( 'template_type' => $template_type ) );

		return array(
			'status' => true,
			'error'  => __( 'Import process start flof set successfully.', 'astra-sites' ),
		);
	}

	/**
	 * Import Spectra Settings
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings spectra settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function import_spectra_settings( $settings = array() ) {

		ST_Importer_Log::add( 'Starting Spectra settings import', 'info', array( 'settings_count' => is_array( $settings ) ? count( $settings ) : 0 ) ); // @phpstan-ignore-line

		// Check if Spectra plugin is available.
		if ( ! is_callable( 'UAGB_Admin_Helper::get_instance' ) ) {
			ST_Importer_Log::add( 'Spectra plugin not found, attempting installation', 'warning' );

			// Try to install and activate Spectra plugin.
			$install_result = ST_Plugin_Installer::install_spectra_plugin();

			if ( ! $install_result['status'] ) {
				ST_Importer_Log::add( 'Spectra plugin installation failed', 'error', array( 'error' => $install_result['error'] ) );
				return array(
					'status' => false,
					'error'  => sprintf(
						// translators: Spectra plugin installation failed message.
						__( 'Spectra plugin installation failed: %s', 'astra-sites' ),
						$install_result['error']
					),
				);
			}

			ST_Importer_Log::add( 'Spectra plugin installed successfully', 'success' );

			// Manually load the plugin file after activation.
			$plugin_file = WP_PLUGIN_DIR . '/ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php';
			if ( file_exists( $plugin_file ) ) {
				include_once $plugin_file;
			}

			// Check again after manual loading.
			if ( ! is_callable( 'UAGB_Admin_Helper::get_instance' ) ) {
				ST_Importer_Log::add( 'Spectra plugin class not loaded after installation', 'error' );
				return array(
					'status' => false,
					'error'  => __( 'Spectra plugin installed but class not loaded. Please refresh and try again.', 'astra-sites' ),
				);
			}
		}

		if ( empty( $settings ) ) {
			ST_Importer_Log::add( 'Spectra settings are empty', 'warning' );
			return array(
				'status' => false,
				'error'  => __( 'Spectra settings are empty.', 'astra-sites' ),
			);
		}

		try {
			\UAGB_Admin_Helper::get_instance()->update_admin_settings_shareable_data( $settings ); // @phpstan-ignore-line

			ST_Importer_Log::add( 'Spectra settings imported successfully', 'success' );

			return array(
				'status'  => true,
				'message' => __( 'Spectra settings imported successfully.', 'astra-sites' ),
			);
		} catch ( \Exception $e ) {
			ST_Importer_Log::add( 'Spectra settings import exception', 'error', array( 'exception' => $e->getMessage() ) );
			return array(
				'status' => false,
				// translators: %s is the exception message.
				'error'  => sprintf( __( 'Spectra settings import failed: %s', 'astra-sites' ), $e->getMessage() ),
			);
		}
	}

	/**
	 * Import Surecart Settings
	 *
	 * @since 1.0.0
	 *
	 * @param int $id id.
	 * @return array<string, mixed>
	 */
	public static function import_surecart_settings( $id = 0 ) {

		ST_Importer_Log::add( 'Starting SureCart settings import', 'info', array( 'account_id' => $id ) );

		if ( ! is_callable( 'SureCart\Models\ProvisionalAccount::create' ) ) {
			ST_Importer_Log::add( 'SureCart ProvisionalAccount::create function not callable', 'error' );
			return array(
				'status' => false,
				'error'  => __( 'SureCart\Models\ProvisionalAccount::create function is not callable.', 'astra-sites' ),
			);
		}

		// Create Account if surecart selected as a feature.
		$create_account = isset( $_POST['create_account'] ) && 'true' === $_POST['create_account'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$email          = ST_Importer_Helper::get_business_details( 'business_email' );
		$currency       = isset( $_POST['source_currency'] ) ? sanitize_text_field( $_POST['source_currency'] ) : 'usd'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		ST_Importer_Log::add(
			'SureCart settings validation',
			'info',
			array(
				'currency'       => $currency,
				'create_account' => $create_account,
			)
		);

		$data = array(
			'email'            => empty( $email ) ? get_option( 'admin_email' ) : $email,
			'account_currency' => $currency,
		);

		if ( ! $create_account ) {
			$id = ! empty( $id ) ? base64_decode( sanitize_text_field( (string) $id ) ) : ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

			if ( empty( $id ) ) {
				ST_Importer_Log::add( 'SureCart account ID is empty', 'error', array( 'create_account' => $create_account ) );
				return array(
					'status' => false,
					'error'  => __( 'Id is empty.', 'astra-sites' ),
				);
			}

			$data['source_account_id'] = $id;
			ST_Importer_Log::add( 'SureCart source account ID set', 'info', array( 'account_id' => $id ) );
		}

		$token = \SureCart\Models\ApiToken::get(); // @phpstan-ignore-line

		if ( ! empty( $token ) ) {
			ST_Importer_Log::add( 'SureCart account already created, token exists', 'warning' );
			return array(
				'status' => true,
				'msg'    => __( 'Account is already created.', 'astra-sites' ),
			);
		}

		$template_data = ST_Importer_File_System::get_instance()->get_demo_content();
		$products      = $template_data['astra-site-surecart-settings']['products'] ?? null;

		ST_Importer_Log::add( 'SureCart processing products data', 'info', array( 'product_count' => is_array( $products ) ? count( $products ) : 0 ) );

		// If no products, set seed to true to create sample products.
		if ( empty( $products ) || ! is_array( $products ) ) {
			$data['seed'] = true;
			ST_Importer_Log::add( 'SureCart no products found, seed flag enabled', 'info' );
		} else {
			// Collect all image hash URLs first to minimize DB hits.
			$hash_urls = [];
			foreach ( $products as $product ) {
				foreach ( $product['gallery'] ?? [] as $attachment ) {
					if ( ! empty( $attachment['url'] ) ) {
						$hash_urls[] = ST_Importer_Helper::get_hash_image( $attachment['url'] );
					}
				}
			}

			ST_Importer_Log::add( 'SureCart hash URLs collected', 'info', array( 'hash_count' => count( $hash_urls ) ) );

			// Filter out empty hashes and get unique ones.
			$hash_urls = array_filter( array_unique( $hash_urls ) );

			// Map all hashes to their attachment IDs in one query.
			global $wpdb;
			$hash_map = [];
			if ( ! empty( $hash_urls ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $hash_urls ), '%s' ) );
				$results      = $wpdb->get_results(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $wpdb->postmeta is a table name, and $placeholders is dynamically created based on array size.
						"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_astra_sites_image_hash' AND meta_value IN ($placeholders)",
						$hash_urls
					),
					ARRAY_A
				);

				foreach ( $results as $row ) {
					$hash_map[ $row['meta_value'] ] = (int) $row['post_id'];
				}

				ST_Importer_Log::add( 'SureCart attachment IDs resolved', 'info', array( 'resolved_count' => count( $hash_map ) ) );
			}

			// Save products data to options for use when SureCart products are created.
			// Format: [slug => [old_post_id => int, old_sc_id => string, prices => array]].
			$surecart_mapping_data = array();

			// Build final products array with resolved gallery IDs.
			foreach ( $products as $index => $product ) {
				$gallery_ids = [];

				foreach ( $product['gallery'] ?? [] as $attachment ) {
					$hash_url = ST_Importer_Helper::get_hash_image( $attachment['url'] ?? '' );
					if ( ! empty( $hash_url ) && isset( $hash_map[ $hash_url ] ) ) {
						$gallery_ids[] = $hash_map[ $hash_url ];
					}
				}

				$products[ $index ]['gallery_ids'] = $gallery_ids;

				// Map nested data arrays if they exist, else set to empty arrays or default values.
				$products[ $index ]['prices']              = ! empty( $product['prices']['data'] ) ? $product['prices']['data'] : array( array( 'amount' => 9900 ) );
				$products[ $index ]['variants']            = ! empty( $product['variants']['data'] ) ? $product['variants']['data'] : array();
				$products[ $index ]['variant_options']     = ! empty( $product['variant_options']['data'] ) ? $product['variant_options']['data'] : array();
				$products[ $index ]['product_collections'] = ! empty( $product['product_collections']['data'] ) ? $product['product_collections']['data'] : array();

				$product_slug = $product['slug'] ?? '';
				$old_post_id  = $product['post']['ID'] ?? null;
				$old_sc_id    = $product['id'] ?? null;
				$prices       = $product['prices']['data'] ?? array();

				if ( ! empty( $product_slug ) ) {
					$surecart_mapping_data[ $product_slug ] = array(
						'old_post_id' => $old_post_id,
						'old_sc_id'   => $old_sc_id,
						'prices'      => array(),
					);

					foreach ( $prices as $price ) {
						$surecart_mapping_data[ $product_slug ]['prices'][] = array(
							'old_price_id' => $price['id'],
						);
					}
				}
			}

			if ( ! empty( $surecart_mapping_data ) ) {
				update_option( 'astra_sites_surecart_mapping_data', $surecart_mapping_data, false );
			}

			$data['products'] = $products;
			ST_Importer_Log::add( 'SureCart product array prepared', 'success', array( 'product_count' => count( $products ) ) );
		}

		ST_Importer_Log::add( 'SureCart API call to create account' );

		try {
			$result = \SureCart\Models\ProvisionalAccount::create( $data ); // @phpstan-ignore-line

			if ( isset( $result['status'] ) && $result['status'] ) {
				ST_Importer_Log::add( 'SureCart settings import completed successfully', 'success' );
			} else {
				$error_msg = isset( $result['error'] ) ? $result['error'] : 'Unknown error';
				ST_Importer_Log::add( 'SureCart settings import API error', 'error', array( 'error' => $error_msg ) );
			}

			return $result;
		} catch ( \Exception $e ) {
			ST_Importer_Log::add( 'SureCart settings import exception', 'fatal', array( 'exception' => $e->getMessage() ) );
			return array(
				'status' => false,
				'error'  => $e->getMessage(),
			);
		}
	}

	/**
	 * Import Customizer Settings.
	 *
	 * @since 1.0.0
	 *
	 * @param  array<string, mixed> $customizer_data Customizer Data.
	 * @return array<string, mixed>
	 */
	public static function import_customizer_settings( $customizer_data = array() ) {

		ST_Importer_Log::add( 'Starting customizer settings import', 'info', array( 'settings_count' => is_array( $customizer_data ) ? count( $customizer_data ) : 0 ) ); // @phpstan-ignore-line

		if ( empty( $customizer_data ) ) {
			ST_Importer_Log::add( 'Customizer data is empty, import skipped', 'error' );
			return array(
				'status' => false,
				'error'  => __( 'Customizer data is empty.', 'astra-sites' ),
			);
		}

		try {
			ST_Importer_Log::add( 'Validating customizer data keys', 'info', array( 'keys' => array_keys( $customizer_data ) ) );

			update_option( '_astra_sites_old_customizer_data', $customizer_data, false );
			ST_Importer_Log::add( 'Old customizer data backed up', 'info' );

			// Update Astra Theme customizer settings.
			if ( isset( $customizer_data['astra-settings'] ) ) {

				// Strip residual `websitedemos.net` URLs from classic-template
				// customizer data so menu/button links resolve to the newly
				// imported site. AI imports ship with clean URLs, so this is
				// a no-op there by default.
				$is_classic = 'ai' !== get_option( 'astra_sites_current_import_template_type' );

				/**
				 * Filters whether to scrub `websitedemos.net` URLs from the
				 * customizer settings being saved during import.
				 *
				 * Returning `false` preserves the raw customizer payload.
				 * Defaults to `true` for classic-template imports and
				 * `false` for AI imports (which don't ship demo URLs).
				 *
				 * @since 1.1.33
				 *
				 * @param bool  $scrub           Whether to run the scrub. Default based on template type.
				 * @param array $customizer_data The full customizer data array being imported.
				 */
				$should_scrub = (bool) apply_filters( 'astra_sites_scrub_customizer_demo_urls', $is_classic, $customizer_data );

				if ( $should_scrub ) {
					$customizer_data['astra-settings'] = ST_Importer_Helper::replace_source_site_url( $customizer_data['astra-settings'] );
				}

				update_option( 'astra-settings', $customizer_data['astra-settings'] );
				ST_Importer_Log::add( 'Astra theme settings updated', 'success', array( 'option_key' => 'astra-settings' ) );
			} else {
				ST_Importer_Log::add( 'Astra theme settings not found in import data', 'warning' );
			}

			// Add Custom CSS.
			if ( isset( $customizer_data['custom-css'] ) ) {
				wp_update_custom_css_post( $customizer_data['custom-css'] );
				ST_Importer_Log::add( 'Custom CSS updated', 'success', array( 'option_key' => 'custom-css' ) );
			} else {
				ST_Importer_Log::add( 'Custom CSS not found in import data', 'warning' );
			}

			ST_Importer_Log::add( 'Customizer settings import completed successfully', 'success' );

			return array(
				'status'  => true,
				'message' => __( 'Customizer data imported successfully.', 'astra-sites' ),
			);
		} catch ( \Exception $e ) {
			ST_Importer_Log::add( 'Customizer settings import exception', 'error', array( 'exception' => $e->getMessage() ) );
			return array(
				'status' => false,
				'error'  => $e->getMessage(),
			);
		}

	}

	/**
	 * Prepare XML Data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wxr_url url.
	 * @return array<string, mixed>
	 */
	public static function prepare_xml_data( $wxr_url ) {

		ST_Importer_Log::add( 'Starting XML data preparation', 'info', array( 'wxr_url' => $wxr_url ) );

		if ( ! ST_WXR_Importer::is_valid_wxr_url( $wxr_url ) ) {
			ST_Importer_Log::add( 'Invalid WXR URL validation failed', 'error', array( 'wxr_url' => $wxr_url ) );
			return array(
				'status' => false,
				/* Translators: %s is WXR URL. */
				'error'  => sprintf( __( 'Invalid WXR Request URL - %s', 'astra-sites' ), $wxr_url ),
			);
		}

		ST_Importer_Log::add( 'WXR URL validation passed' );

		$overrides = array(
			'wp_handle_sideload' => 'upload',
		);

		// Download XML file.
		ST_Importer_Log::add( 'Starting XML file download', 'info', array( 'url' => $wxr_url ) );
		$xml_path = ST_WXR_Importer::download_file( $wxr_url, $overrides );

		if ( $xml_path['success'] ) {

			ST_Importer_Log::add( 'XML file downloaded successfully', 'success', array( 'file_path' => $xml_path['data']['file'] ) );

			$post = array(
				'post_title'     => basename( $wxr_url ),
				'guid'           => $xml_path['data']['url'],
				'post_mime_type' => $xml_path['data']['type'],
			);

			// As per wp-admin/includes/upload.php.
			ST_Importer_Log::add( 'Inserting XML file as attachment', 'info', array( 'filename' => basename( $wxr_url ) ) );
			$post_id = wp_insert_attachment( $post, $xml_path['data']['file'] );

			if ( is_wp_error( $post_id ) ) { // @phpstan-ignore-line
				ST_Importer_Log::add( 'XML attachment insertion failed', 'error', array( 'error' => $post_id->get_error_message() ) );
				return array(
					'status' => false,
					// translators: %s is the error message.
					'error'  => sprintf( __( 'Error occurred while inserting XML file: %s', 'astra-sites' ), $post_id->get_error_message() ),
				);
			}

			if ( ! is_int( $post_id ) ) {
				ST_Importer_Log::add( 'XML file download returned invalid post ID', 'error', array( 'post_id' => var_export( $post_id, true ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- Used for logging context data.
				return array(
					'status' => false,
					'error'  => __( 'There was an error downloading the XML file.', 'astra-sites' ),
				);
			} else {
				ST_Importer_Log::add( 'XML attachment created', 'success', array( 'post_id' => $post_id ) );

				update_option( 'astra_sites_imported_wxr_id', $post_id, false );
				ST_Importer_Log::add( 'Generating attachment metadata', 'info', array( 'post_id' => $post_id ) );

				$attachment_metadata = wp_generate_attachment_metadata( $post_id, $xml_path['data']['file'] );
				wp_update_attachment_metadata( $post_id, $attachment_metadata );

				ST_Importer_Log::add( 'Parsing XML data from file', 'info', array( 'post_id' => $post_id ) );
				$data        = ST_WXR_Importer::get_xml_data( $xml_path['data']['file'], $post_id );
				$data['xml'] = $xml_path['data'];

				ST_Importer_Log::add(
					'XML data preparation completed successfully',
					'success',
					array(
						'post_id'  => $post_id,
						'has_data' => ! empty( $data ),
					)
				);

				return array(
					'status' => true,
					'data'   => $data,
				);
			}
		} else {
			$error_message = isset( $xml_path['data'] )
				? $xml_path['data']
				: __( 'Could not download data file. Please check your internet connection and try again.', 'astra-sites' );

			ST_Importer_Log::add( 'XML file download failed', 'error', array( 'error' => $error_message ) );

			return array(
				'status' => false,
				// translators: %s is the download error message.
				'error'  => sprintf( __( 'File download failed: %s', 'astra-sites' ), $error_message ),
			);
		}
	}

	/**
	 * Update post option
	 *
	 * @since 1.1.18
	 *
	 * @return void
	 */
	public static function set_elementor_kit() {

		ST_Importer_Log::add( 'Starting Elementor kit setup' );

		// Update Elementor Theme Kit Option.
		$args = array(
			'post_type'   => 'elementor_library',
			'post_status' => 'publish',
			'numberposts' => 1,
			'meta_query'  => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Setting elementor kit. WP Query would have been expensive.
				array(
					'key'   => '_astra_sites_imported_post',
					'value' => '1',
				),
				array(
					'key'   => '_elementor_template_type',
					'value' => 'kit',
				),
			),
		);

		ST_Importer_Log::add( 'Querying for Elementor kit posts', 'info', array( 'post_type' => 'elementor_library' ) );

		$query = get_posts( $args );

		if ( ! empty( $query ) && isset( $query[0]->ID ) ) {
			$kit_id = $query[0]->ID;
			update_option( 'elementor_active_kit', $kit_id );
			ST_Importer_Log::add( 'Elementor active kit set successfully', 'success', array( 'kit_id' => $kit_id ) );
		} else {
			ST_Importer_Log::add( 'No Elementor kit post found, unable to set active kit', 'warning' );
		}
	}

	/**
	 * Import site options.
	 *
	 * @since  1.0.0
	 *
	 * @param array<string, mixed> $options Array of options to be imported from the demo.
	 *
	 * @return array<string, mixed>
	 */
	public static function import_options( $options = array() ) {

		ST_Importer_Log::add( 'Starting site options import', 'info', array( 'options_count' => is_array( $options ) ? count( $options ) : 0 ) ); // @phpstan-ignore-line

		if ( empty( $options ) ) {
			ST_Importer_Log::add( 'Site options are empty, import skipped', 'error' );
			return array(
				'status' => false,
				'error'  => __( 'Site options are empty!', 'astra-sites' ),
			);
		}

		// Set meta for tracking the post.
		if ( is_array( $options ) ) {
			update_option( '_astra_sites_old_site_options', $options, false );
			ST_Importer_Log::add( 'Old site options backed up', 'info' );
		}

		try {
			$processed_count = 0;
			$skipped_count   = 0;

			foreach ( $options as $option_name => $option_value ) {

				// Is option exist in defined array site_options()?
				if ( null !== $option_value ) {
					ST_Importer_Log::add( 'Processing option', 'info', array( 'option_name' => $option_name ) );

					switch ( $option_name ) {
						case 'page_for_posts':
						case 'page_on_front':
								ST_Importer_Log::add(
									'Updating page ID option',
									'info',
									array(
										'option_name'  => $option_name,
										'option_value' => $option_value,
									)
								);
								ST_Option_Importer::update_page_id_by_option_value( $option_name, $option_value );
								$processed_count++;
							break;

						// nav menu locations.
						case 'nav_menu_locations':
								ST_Importer_Log::add( 'Setting navigation menu locations', 'info', array( 'menu_count' => is_array( $option_value ) ? count( $option_value ) : 0 ) );
								ST_Option_Importer::set_nav_menu_locations( $option_value );
								$processed_count++;
							break;

						// insert logo.
						case 'custom_logo':
								ST_Importer_Log::add( 'Importing custom logo', 'info', array( 'logo_id' => $option_value ) );
								ST_Option_Importer::insert_logo( $option_value );
								$processed_count++;
							break;

						case 'site_title':
							try {
								ST_Importer_Log::add( 'Updating site title', 'info', array( 'site_title' => $option_value ) );
								update_option( 'blogname', $option_value );
								$processed_count++;
							} catch ( \Exception $e ) {
								// Failed silently: sometimes Elementor throws exception as it hooks into `update_option_blogname`.
								ST_Importer_Log::add( 'Site title update exception (handled)', 'warning', array( 'exception' => $e->getMessage() ) );
								astra_sites_error_log( 'Handled exception while updating blogname: ' . $e->getMessage() );
								$skipped_count++;
							}
							break;

						case 'elementor_active_kit':
							if ( '' !== $option_value ) {
								ST_Importer_Log::add( 'Setting up Elementor kit', 'info' );
								self::set_elementor_kit();
								$processed_count++;
							} else {
								ST_Importer_Log::add( 'Elementor kit option is empty, skipping', 'warning' );
								$skipped_count++;
							}
							break;

						default:
							ST_Importer_Log::add( 'Updating standard option', 'info', array( 'option_name' => $option_name ) );
							update_option( $option_name, $option_value );
							$processed_count++;
							break;
					}
				} else {
					ST_Importer_Log::add( 'Option value is null, skipping', 'info', array( 'option_name' => $option_name ) );
					$skipped_count++;
				}
			}

			ST_Importer_Log::add( 'Running st_importer_import_site_options action' );
			do_action( 'st_importer_import_site_options', $options );

			ST_Importer_Log::add(
				'Site options import completed successfully',
				'success',
				array(
					'processed_count' => $processed_count,
					'skipped_count'   => $skipped_count,
					'total_count'     => count( $options ),
				)
			);

			return array(
				'status'  => true,
				'message' => __( 'Options imported successfully.', 'astra-sites' ),
			);
		} catch ( \Exception $e ) {
			ST_Importer_Log::add( 'Site options import exception', 'fatal', array( 'exception' => $e->getMessage() ) );
			return array(
				'status' => false,
				'error'  => $e,
			);
		}

	}

	/**
	 * Import Widgets.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $widgets_data Widgets Data.
	 * @param  string $data Widgets Data.
	 * @return array<string, mixed>
	 */
	public static function import_widgets( $widgets_data, $data = '' ) {

		ST_Importer_Log::add( 'Starting widgets import' );

		// Prepare widget data.
		ST_Importer_Log::add(
			'Processing widget data',
			'info',
			array(
				'data_type'         => gettype( $data ),
				'widgets_data_type' => gettype( $widgets_data ),
			)
		);

		if ( is_object( $data ) ) { // @phpstan-ignore-line
			// $data is set and is an object.
			ST_Importer_Log::add( 'Using data parameter as widget object', 'info' );
			$widgets_data = $data;
		} elseif ( is_string( $data ) ) {
			// $data is set but is not an object.
			ST_Importer_Log::add( 'Parsing widget data from JSON string', 'info' );
			$widgets_data = (object) json_decode( $data );
		}

		if ( ! is_object( $widgets_data ) ) {
			ST_Importer_Log::add( 'Invalid widget data, not an object after processing', 'error', array( 'final_type' => gettype( $widgets_data ) ) );
			return array(
				'status' => false,
				'error'  => __( 'Widget data is empty!', 'astra-sites' ),
			);
		}

		try {
			ST_Importer_Log::add( 'Importing widget data via ST_Widget_Importer', 'info' );
			ST_Widget_Importer::import_widgets_data( $widgets_data );

			ST_Importer_Log::add( 'Retrieving current sidebars and widgets' );
			$sidebars_widgets = get_option( 'sidebars_widgets', array() );

			ST_Importer_Log::add( 'Backing up old widgets data', 'info', array( 'sidebars_count' => is_array( $sidebars_widgets ) ? count( $sidebars_widgets ) : 0 ) );
			update_option( '_astra_sites_old_widgets_data', $sidebars_widgets, false );

			ST_Importer_Log::add( 'Widgets import completed successfully', 'success' );

			return array(
				'status'  => true,
				'message' => __( 'Widgets imported successfully.', 'astra-sites' ),
			);
		} catch ( \Exception $e ) {
			ST_Importer_Log::add( 'Widgets import exception', 'error', array( 'exception' => $e->getMessage() ) );
			return array(
				'status' => false,
				'error'  => $e->getMessage(),
			);
		}

	}
}
