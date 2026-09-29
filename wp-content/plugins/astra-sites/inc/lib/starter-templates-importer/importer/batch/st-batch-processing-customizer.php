<?php
/**
 * Customizer batch import tasks.
 *
 * @package Astra Sites
 * @since 1.1.5
 */

namespace STImporter\Importer\Batch;

use STImporter\Importer\Helpers\ST_Image_Importer;
use STImporter\Importer\ST_Importer_Log;
/**
 * ST_Batch_Processing_Customizer
 *
 * @since 1.1.5
 */
class ST_Batch_Processing_Customizer {

	/**
	 * Instance
	 *
	 * @since 1.1.5
	 * @access private
	 * @var object Class object.
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.1.5
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
	 * @since 1.1.5
	 */
	public function __construct() {}

	/**
	 * Import
	 *
	 * @since 1.1.5
	 * @return void
	 */
	public function import() {
		// Log customizer image download start.
		ST_Importer_Log::add( 'Customizer image download batch processing started' );

		if ( defined( 'WP_CLI' ) ) {
			\WP_CLI::line( 'Processing "Customizer" Batch Import' );
		}

		// Process images download.
		self::images_download();

		// Log completion.
		ST_Importer_Log::add( 'Customizer batch import method completed', 'success' );
	}

	/**
	 * Downloads images from customizer.
	 *
	 * @return void
	 */
	public static function images_download() {
		$options       = get_option( 'astra-settings', array() );
		$initial_count = count( $options );

		// Log start with customizer options count.
		ST_Importer_Log::add(
			'Starting customizer image processing',
			'info',
			array(
				'customizer_options_count' => $initial_count,
			)
		);

		$processed_count = 0;
		$failed_count    = 0;

		array_walk_recursive(
			$options,
			function ( &$value ) use ( &$processed_count, &$failed_count ) {
				if ( ! is_array( $value ) && function_exists( 'astra_sites_is_valid_image' ) && astra_sites_is_valid_image( $value ) && method_exists( ST_Image_Importer::get_instance(), 'import' ) ) {

					// Log individual image processing start.
					ST_Importer_Log::add(
						'Processing customizer image',
						'info',
						array(
							'original_url' => $value,
						)
					);

					$downloaded_image = ST_Image_Importer::get_instance()->import(
						array(
							'url' => $value,
							'id'  => 0,
						)
					);

					// Log individual image processing result.
					if ( isset( $downloaded_image['url'] ) ) {
						ST_Importer_Log::add(
							'Customizer image processed successfully',
							'success',
							array(
								'original_url'   => $value,
								'downloaded_url' => $downloaded_image['url'],
								'image_data'     => $downloaded_image,
							)
						);
						$value = $downloaded_image['url'];
						$processed_count++;
					} else {
						ST_Importer_Log::add(
							'Customizer image processing failed',
							'error',
							array(
								'original_url' => $value,
								'result'       => $downloaded_image,
							)
						);
						$failed_count++;
					}
				}
			}
		);

		// Updated settings.
		update_option( 'astra-settings', $options );

		// Log completion with processed count.
		ST_Importer_Log::add(
			'Customizer image download completed',
			'success',
			array(
				'options_processed'             => $initial_count,
				'images_processed_successfully' => $processed_count,
				'images_failed'                 => $failed_count,
				'total_images_attempted'        => $processed_count + $failed_count,
			)
		);
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
ST_Batch_Processing_Customizer::get_instance();
