<?php
/**
 * Misc batch import tasks.
 *
 * @package Astra Sites
 * @since 1.1.6
 */

namespace STImporter\Importer\Batch;

use STImporter\Importer\ST_Importer_Log;

if ( ! class_exists( 'ST_Batch_CE_Process_Images' ) ) :

	/**
	 * ST_Batch_CE_Process_Images
	 *
	 * @since 4.0.11
	 */
	class ST_Batch_CE_Process_Images {

		/**
		 * Instance
		 *
		 * @since 1.1.6
		 * @access private
		 * @var object Class object.
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @since 1.1.6
		 * @return object initialized object of class.
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Offset
		 *
		 * @var int
		 */
		private static $offset = 0;

		/**
		 * Chunk Size
		 *
		 * @var int
		 */
		private static $chunk_size = 10;

		/**
		 * Constructor
		 *
		 * @since 4.0.11
		 */
		public function __construct() {}

		/**
		 * Import
		 *
		 * @since 4.0.11
		 * @return void
		 */
		public function import() {

			if ( defined( 'WP_CLI' ) ) {
				\WP_CLI::line( 'Processing "Images" Batch Import' );
			}

			self::$offset = get_option( 'st_attachments_offset', self::$chunk_size );

			self::image_processing();
		}

		/**
		 * Process Images with the metadata.
		 *
		 * @since 4.0.11
		 * @throws \Exception If there is an error.
		 * @return void
		 */
		public static function image_processing() {
			$all_attachments = get_option( 'st_attachments', array() );

			ST_Importer_Log::add(
				'Starting CE image metadata processing',
				'info',
				array(
					'total_attachments' => count( $all_attachments ),
					'offset'            => self::$offset,
					'chunk_size'        => self::$chunk_size,
				)
			);

			if ( empty( $all_attachments ) ) {
				ST_Importer_Log::add( 'No attachments found for CE image processing', 'warning' );
				return;
			}

			$window = array_slice( $all_attachments, self::$offset, self::$chunk_size );
			ST_Importer_Log::add(
				'Processing CE image batch',
				'info',
				array(
					'batch_size' => count( $window ),
					'offset'     => self::$offset,
				)
			);

			$success_count = 0;
			$error_count   = 0;

			foreach ( $window as $attachment_id ) {
				$file = get_attached_file( $attachment_id );
				if ( false !== $file ) {
					try {
						ST_Importer_Log::add(
							'Generating attachment metadata',
							'info',
							array(
								'attachment_id' => $attachment_id,
								'file'          => basename( $file ),
							)
						);
						wp_generate_attachment_metadata( $attachment_id, $file );
						$success_count++;
					} catch ( \Exception $e ) {
						ST_Importer_Log::add(
							'Failed to generate attachment metadata',
							'error',
							array(
								'attachment_id' => $attachment_id,
								'file'          => basename( $file ),
								'exception'     => $e->getMessage(),
							)
						);
						$error_count++;
						throw new \Exception( $e->getMessage() );
					}
				} else {
					ST_Importer_Log::add( 'Attachment file not found', 'warning', array( 'attachment_id' => $attachment_id ) );
					$error_count++;
				}
			}

			update_option( 'st_attachments_offset', self::$offset + self::$chunk_size );

			ST_Importer_Log::add(
				'CE image batch processing completed',
				'success',
				array(
					'successful' => $success_count,
					'errors'     => $error_count,
					'new_offset' => self::$offset + self::$chunk_size,
				)
			);
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	ST_Batch_CE_Process_Images::get_instance();

endif;
