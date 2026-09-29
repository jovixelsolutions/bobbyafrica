<?php
/**
 * Image Importer
 *
 * @see https://github.com/elementor/elementor/blob/master/includes/template-library/classes/class-import-images.php
 *
 * => How to use?
 *
 *  $image = array(
 *      'url' => '<image-url>',
 *      'id'  => '<image-id>',
 *  );
 *
 *  $downloaded_image = Astra_Sites_Image_Importer::get_instance()->import( $image );
 *
 * @package Astra Sites
 * @since 1.0.14
 */

namespace STImporter\Importer\Helpers;

use STImporter\Importer\ST_Importer_Helper;
use STImporter\Importer\ST_Importer_Log;

if ( ! class_exists( 'ST_Image_Importer' ) ) :

	/**
	 * Astra Sites Image Importer
	 *
	 * @since 1.0.14
	 */
	class ST_Image_Importer {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access private
		 */
		private static $instance = null;

		/**
		 * Images IDs
		 *
		 * @var array   The Array of already image IDs.
		 * @since 1.0.14
		 */
		private $already_imported_ids = array();

		/**
		 * Initiator
		 *
		 * @since 1.0.14
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.14
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'defer_image_processing_while_import' ) );

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
		}

		/**
		 * Add filters to defer image subsizes processing while importing.
		 */
		public function defer_image_processing_while_import() {
			if ( function_exists( 'astra_sites_has_import_started' ) && astra_sites_has_import_started() && 'ai' !== get_option( 'astra_sites_current_import_template_type' ) ) {
				$this->defer_image_subsizes();
				ST_Importer_Log::add( 'Image subsizes deferral enabled for import' );
			}
		}

		/**
		 * Defer image subsizes.
		 *
		 * @return void
		 */
		public function defer_image_subsizes() {
			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'buffer_images_for_processing' ), 10, 3 );
		}

		/**
		 * Force attachment size geenration in the background.
		 *
		 * @param array   $new_sizes       Array of image sizes.
		 * @param array   $image_meta      Metadata of the image.
		 * @param integer $attachment_id Attachment id.
		 *
		 * @return array
		 */
		public function buffer_images_for_processing( $new_sizes, $image_meta, $attachment_id ) {
			$all_attachments = get_option( 'st_attachments', array() );
			// If the cron job is already scheduled, bail.
			if ( in_array( $attachment_id, $all_attachments, true ) ) {
				return $new_sizes;
			}

			$all_attachments[] = $attachment_id;

			update_option( 'st_attachments', $all_attachments, 'no' );

			// Return blank array of sizes to not generate any sizes in this request.
			return array();
		}


		/**
		 * Process Image Download
		 *
		 * @since 1.0.14
		 * @param  array $attachments Attachment array.
		 * @return array              Attachment array.
		 */
		public function process( $attachments ) {

			$downloaded_images = array();

			foreach ( $attachments as $key => $attachment ) {
				$downloaded_images[] = $this->import( $attachment );
			}

			return $downloaded_images;
		}

		/**
		 * Get Hash Image.
		 *
		 * @since 1.0.14
		 * @param  string $attachment_url Attachment URL.
		 * @return string                 Hash string.
		 */
		public function get_hash_image( $attachment_url ) {
			return sha1( $attachment_url );
		}

		/**
		 * Remove dimensions from filename if present.
		 * Pattern: filename-widthxheight.extension (e.g., services-1-150x150.jpg)
		 *
		 * @since 1.1.26
		 * @param  string $filename Filename with potential dimensions.
		 * @return array|false Array of base filename without dimensions or false if no dimensions found.
		 */
		public function get_filename_without_dimensions( $filename ) {
			// Match pattern: filename-widthxheight.extension
			// For example: services-1-150x150.jpg -> services-1.jpg.

			$pattern = '/^(.+?)-(\d+x\d+)\.(.+)$/i';
			if ( preg_match( $pattern, $filename, $matches ) ) {
				// Return the base filename with extension and dimensions.
				return [
					'filename'   => $matches[1] . '.' . $matches[3],
					'base'       => $matches[1],
					'dimensions' => $matches[2],
					'ext'        => $matches[3],
				];
			}
			return false;
		}

		/**
		 * Create a successful response for an existing attachment.
		 *
		 * @since 1.1.26
		 *
		 * @param int    $post_id The attachment ID.
		 * @param string $url     The attachment URL.
		 * @return array          The response array.
		 */
		private function create_successful_response( $post_id, $url ) {
			$new_attachment               = array(
				'id'  => $post_id,
				'url' => $url,
			);
			$this->already_imported_ids[] = $post_id;

			return array(
				'status'     => true,
				'attachment' => $new_attachment,
			);
		}

		/**
		 * Get the URL for a dimensioned file if it exists, otherwise return the original attachment URL.
		 *
		 * @since 1.1.26
		 * @param int    $post_id   The attachment ID.
		 * @param string $filename  The requested filename with dimensions.
		 * @param string $dimensions The dimensions part (e.g. '150x150').
		 * @return string|false     The URL if found, false otherwise.
		 */
		private function get_dimensioned_or_original_url( $post_id, $filename, $dimensions ) {
			$upload_dir    = wp_upload_dir();
			$attached_file = get_post_meta( $post_id, '_wp_attached_file', true );
			if ( ! $attached_file ) {
				return wp_get_attachment_url( $post_id );
			}

			$file_pathinfo         = pathinfo( $attached_file );
			$dimension_file_name   = $file_pathinfo['filename'] . '-' . $dimensions . '.' . $file_pathinfo['extension'];
			$original_file_path    = $upload_dir['basedir'] . '/' . $attached_file;
			$dimensioned_file_path = str_replace( basename( $attached_file ), $dimension_file_name, $original_file_path );

			if ( file_exists( $dimensioned_file_path ) ) {
				// If the dimensioned file exists, construct its URL.
				$relative_path = str_replace( $upload_dir['basedir'], '', $dimensioned_file_path );
				return $upload_dir['baseurl'] . $relative_path;
			}

			// Otherwise, use the default attachment URL.
			return wp_get_attachment_url( $post_id );
		}

		/**
		 * Get Saved Image.
		 *
		 * @since 1.0.14
		 * @param  string $attachment   Attachment Data.
		 * @return array                Attachment Data.
		 */
		private function get_saved_image( $attachment ) {

			if ( apply_filters( 'astra_sites_image_importer_skip_image', false, $attachment ) ) {
				return array(
					'status'     => true,
					'attachment' => $attachment,
				);
			}

			global $wpdb;

			// 1. Is already imported in Batch Import Process?
			$post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We are checking if this image is already processed. WO_Query would have been overkill.
				$wpdb->prepare(
					'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
						WHERE `meta_key` = \'_astra_sites_image_hash\'
							AND `meta_value` = %s
					;',
					$this->get_hash_image( $attachment['url'] )
				)
			);

			// 2. Is image already imported though XML?
			if ( empty( $post_id ) ) {

				// Get file name without extension.
				// To check it exist in attachment.
				$filename = basename( $attachment['url'] );

				// Find the attachment by meta value.
				// Code reused from Elementor plugin.
				$post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We are checking if this attachment is already processed. WO_Query would have been overkill.
					$wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta}
						WHERE meta_key = '_wp_attached_file'
						AND meta_value LIKE %s",
						'%/' . $filename . '%'
					)
				);

				// 3. Check if the filename contains dimensions and try to find the original image.
				if ( empty( $post_id ) ) {
					$base_filename_data = $this->get_filename_without_dimensions( $filename );
					if ( false !== $base_filename_data && $base_filename_data['filename'] !== $filename ) {
						$base_filename  = $base_filename_data['filename'];
						$base_name_only = $base_filename_data['base'];
						$dimensions     = $base_filename_data['dimensions'];

						// First, try to find by meta value (higher priority).
						$post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We are checking if this attachment is already processed. WO_Query would have been overkill.
							$wpdb->prepare(
								"SELECT post_id FROM {$wpdb->postmeta}
								WHERE meta_key = '_wp_attached_file'
								AND meta_value LIKE %s",
								'%/' . $base_filename . '%'
							)
						);

						if ( ! empty( $post_id ) ) {
							// Found by meta, now check if dimensioned version exists.
							$original_attachment_path = get_attached_file( $post_id );
							$original_dir             = dirname( $original_attachment_path );
							$dimensioned_file_path    = $original_dir . '/' . $filename;

							if ( file_exists( $dimensioned_file_path ) ) {
								// Return URL for the specific dimensioned file.
								$upload_dir            = wp_upload_dir();
								$relative_path         = str_replace( $upload_dir['basedir'], '', $dimensioned_file_path );
								$dimensioned_image_url = $upload_dir['baseurl'] . $relative_path;

								return $this->create_successful_response( $post_id, $dimensioned_image_url );
							}
						} else {
							// Not found by meta, try by post_title (lower priority).
							$post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We are checking if this attachment is already processed. WO_Query would have been overkill.
								$wpdb->prepare(
									"SELECT ID FROM {$wpdb->posts}
									WHERE post_type = 'attachment'
									AND post_title = %s",
									$base_name_only
								)
							);

							if ( $post_id ) {
								// Check if dimensioned file exists for the found attachment.
								$attachment_url = $this->get_dimensioned_or_original_url( $post_id, $filename, $dimensions );
								if ( $attachment_url ) {
									return $this->create_successful_response( $post_id, $attachment_url );
								}
							}
						}
					}
				}
			}

			if ( $post_id ) {
				return $this->create_successful_response(
					$post_id,
					wp_get_attachment_url( $post_id )
				);
			}

			return array(
				'status'     => false,
				'attachment' => $attachment,
			);
		}

		/**
		 * Import Image
		 *
		 * @since 1.0.14
		 * @param  array $attachment Attachment array.
		 * @throws \Exception Exception that is catched.
		 * @return array              Attachment array.
		 */
		public function import( $attachment ) {
			ST_Importer_Log::add( 'Starting image import for URL: ' . ( isset( $attachment['url'] ) ? $attachment['url'] : 'No URL provided' ) );

			if ( isset( $attachment['url'] ) && ! astra_sites_is_valid_url( $attachment['url'] ) ) {
				ST_Importer_Log::add( 'Invalid image URL provided: ' . $attachment['url'], 'warning' );
				return $attachment;
			}

			$saved_image = $this->get_saved_image( $attachment );

			if ( $saved_image['status'] ) {
				ST_Importer_Log::add( 'Image already exists in media library (ID: ' . $saved_image['attachment']['id'] . '), skipping download' );
				return $saved_image['attachment'];
			}

			ST_Importer_Log::add( 'Downloading image from remote URL: ' . $attachment['url'] );
			$file_content = wp_remote_retrieve_body(
				wp_safe_remote_get(
					$attachment['url'],
					array(
						'timeout'   => '60',
						'sslverify' => false,
					)
				)
			);

			// Empty file content?
			if ( empty( $file_content ) ) {
				ST_Importer_Log::add( 'Failed to download image - empty file content received from: ' . $attachment['url'], 'error' );
				return $attachment;
			}

			// Extract the file name and extension from the URL.
			$filename = basename( $attachment['url'] );
			ST_Importer_Log::add( 'Successfully downloaded image, preparing to upload: ' . $filename );

			$upload = wp_upload_bits( $filename, null, $file_content );

			astra_sites_error_log( $filename );
			astra_sites_error_log( wp_json_encode( $upload ) );

			// Check if upload was successful.
			if ( ! empty( $upload['error'] ) || empty( $upload['url'] ) || empty( $upload['file'] ) ) {
				ST_Importer_Log::add( 'Image upload failed for ' . $filename . ': ' . ( ! empty( $upload['error'] ) ? $upload['error'] : 'Unknown error' ), 'error' );
				return $attachment;
			}

			ST_Importer_Log::add( 'Image uploaded successfully: ' . $upload['url'] );

			$post = array(
				'post_title' => $filename,
				'guid'       => $upload['url'],
			);
			astra_sites_error_log( wp_json_encode( $post ) );

			$info = wp_check_filetype( $upload['file'] );
			if ( $info ) {
				$post['post_mime_type'] = $info['type'];
			} else {
				// For now just return the origin attachment.
				ST_Importer_Log::add( 'Failed to determine file type for: ' . $filename, 'error' );
				return $attachment;
			}

			$post_id = wp_insert_attachment( $post, $upload['file'] );
			ST_Importer_Log::add( 'Attachment post created with ID: ' . $post_id );

			try {

				if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
					include ABSPATH . 'wp-admin/includes/image.php';
				}

				ST_Importer_Log::add( 'Generating attachment metadata for attachment ID: ' . $post_id );
				wp_update_attachment_metadata(
					$post_id,
					wp_generate_attachment_metadata( $post_id, $upload['file'] )
				);
				ST_Importer_Log::add( 'Attachment metadata generated and saved for attachment ID: ' . $post_id );
			} catch ( \Exception $e ) {
				ST_Importer_Log::add( 'Exception during metadata generation for attachment ID ' . $post_id . ': ' . $e->getMessage(), 'error' );
				throw $e;
			}

			update_post_meta( $post_id, '_astra_sites_image_hash', $this->get_hash_image( $attachment['url'] ) );
			ST_Importer_Helper::track_post( $post_id );

			$new_attachment = array(
				'id'  => $post_id,
				'url' => $upload['url'],
			);

			$this->already_imported_ids[] = $post_id;

			ST_Importer_Log::add( 'Image import completed successfully - ID: ' . $post_id . ', URL: ' . $upload['url'] );

			return $new_attachment;
		}

		/**
		 * Is Image URL
		 *
		 * @since 1.3.10
		 *
		 * @param  string $url URL.
		 * @return boolean
		 */
		public function is_image_url( $url = '' ) {
			if ( empty( $url ) ) {
				return false;
			}

			if ( astra_sites_is_valid_image( $url ) ) {
				return true;
			}

			return false;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	ST_Image_Importer::get_instance();

endif;
