<?php
/**
 * Batch Processing
 *
 * @package ST Importer
 * @since 1.2.14
 */

namespace STImporter\Importer\Batch;

use STImporter\Importer\Batch\ST_Batch_Processing;
use STImporter\Importer\ST_Importer_Log;

if ( ! class_exists( 'ST_Batch_Processing_Elementor' ) ) :

	/**
	 * Astra Sites Batch Processing Brizy
	 *
	 * @since 1.2.14
	 */
	class ST_Batch_Processing_Elementor {

		/**
		 * Instance
		 *
		 * @since 1.2.14
		 * @access private
		 * @var object Class object.
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @since 1.2.14
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
		 * @since 1.2.14
		 */
		public function __construct() {}

		/**
		 * Import
		 *
		 * @since 1.2.14
		 * @return array<string, mixed>
		 */
		public function import() {
			// Log Elementor batch processing start.
			ST_Importer_Log::add( 'Elementor batch processing started' );

			if ( defined( 'WP_CLI' ) ) {
				\WP_CLI::line( 'Processing Batch Import' );
			}

			$post_types = apply_filters( 'astra_sites_elementor_batch_process_post_types', array( 'page', 'post', 'wp_block', 'wp_template', 'wp_navigation', 'wp_template_part', 'wp_global_styles', 'sc_form' ) );
			if ( defined( 'WP_CLI' ) ) {
				\WP_CLI::line( 'For post types: ' . implode( ', ', $post_types ) );
			}

			$post_ids = St_Batch_Processing::get_pages( $post_types );

			// Log post count.
			ST_Importer_Log::add(
				'Elementor posts retrieved for processing',
				'info',
				array(
					'post_count' => count( $post_ids ),
					'post_types' => implode( ', ', $post_types ),
				)
			);

			if ( ! is_array( $post_ids ) ) {
				ST_Importer_Log::add( 'Elementor batch processing failed - post IDs are empty', 'error' );
				return array(
					'success' => false,
					'msg'     => __( 'Post ids are empty', 'astra-sites' ),
				);
			}

			foreach ( $post_ids as $post_id ) {
				$this->import_single_post( $post_id );
			}

			ST_Importer_Log::add( 'Elementor batch processing completed successfully', 'success', array( 'posts_processed' => count( $post_ids ) ) );

			return array(
				'success' => true,
				'msg'     => __( 'Batch process completed.', 'astra-sites' ),
			);
		}

		/**
		 * Update post meta.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {
			ST_Importer_Log::add( 'Processing Elementor post', 'info', array( 'post_id' => $post_id ) );

			if ( defined( 'WP_CLI' ) ) {
				\WP_CLI::line( 'Elementor - Processing page: ' . $post_id );
			}

			// Is page imported with Starter Sites?
			// If not then skip batch process.
			$imported_from_demo_site = get_post_meta( $post_id, '_astra_sites_enable_for_batch', true );
			if ( ! $imported_from_demo_site ) {
				ST_Importer_Log::add( 'Skipping post - not imported from demo site', 'info', array( 'post_id' => $post_id ) );
				return;
			}

			$is_elementor_page = get_post_meta( $post_id, '_elementor_version', true );
			$elementor_data    = get_post_meta( $post_id, '_elementor_data', true );

			if ( ! $is_elementor_page || empty( $elementor_data ) ) {
				ST_Importer_Log::add(
					'Skipping post - not an Elementor page or missing data',
					'info',
					array(
						'post_id'     => $post_id,
						'has_version' => ! empty( $is_elementor_page ),
						'has_data'    => ! empty( $elementor_data ),
					)
				);
				return;
			}

			$widget_data = is_array( $elementor_data ) ? $elementor_data : json_decode( $elementor_data, true );

			if ( ! is_array( $widget_data ) ) {
				ST_Importer_Log::add( 'Skipping post - widget data is not an array', 'warning', array( 'post_id' => $post_id ) );
				return;
			}

			$this->process_elementor_widgets( $widget_data );

			$updated_data = wp_json_encode( $widget_data );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				update_metadata( 'post', $post_id, '_elementor_data', wp_slash( $updated_data ) );
				ST_Importer_Log::add( 'Updated Elementor data successfully', 'success', array( 'post_id' => $post_id ) );
			} else {
				ST_Importer_Log::add(
					'JSON encoding error occurred',
					'error',
					array(
						'post_id'    => $post_id,
						'json_error' => json_last_error_msg(),
					)
				);
			}

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$elementor = \Elementor\Plugin::instance();
				$elementor->files_manager->clear_cache();
				ST_Importer_Log::add( 'Cleared Elementor cache', 'info', array( 'post_id' => $post_id ) );
			}

			ST_Importer_Log::add( 'Completed processing Elementor post', 'success', array( 'post_id' => $post_id ) );
		}

		/**
		 * Process widgets recursively to replace strings in settings.
		 *
		 * @param array<int, array<mixed>> $widgets The widgets array to process.
		 *
		 * @return void
		 */
		public function process_elementor_widgets( &$widgets ) {
			ST_Importer_Log::add( 'Processing Elementor widgets', 'info', array( 'widget_count' => count( $widgets ) ) );

			$processed_count = 0;

			foreach ( $widgets as &$widget ) {

				if ( isset( $widget['widgetType'] ) && 'shortcode' === $widget['widgetType'] && has_shortcode( $widget['settings']['shortcode'], 'wpforms' ) ) {
					$this->replace_wpforms_ids( $widget );
				}

				if ( isset( $widget['widgetType'] ) && 'wpforms' === $widget['widgetType'] ) {
					$this->replace_wpforms_widget_ids( $widget );
				}

				if ( isset( $widget['widgetType'] ) && 'shortcode' === $widget['widgetType'] && has_shortcode( $widget['settings']['shortcode'], 'sureforms' ) ) {
					$this->replace_sureforms_ids( $widget );
				}

				if ( isset( $widget['widgetType'] ) && 'sureforms_form' === $widget['widgetType'] ) {
					$this->replace_sureforms_widget_id( $widget );
				}

				if ( isset( $widget['widgetType'] ) && 'shortcode' === $widget['widgetType'] && has_shortcode( $widget['settings']['shortcode'], 'sc_form' ) ) {
					$this->replace_surecart_forms_ids( $widget );
				}

				if ( isset( $widget['elements'] ) && is_array( $widget['elements'] ) ) {
					$this->process_elementor_widgets( $widget['elements'] );
				}

				$processed_count++;
			}

			ST_Importer_Log::add( 'Completed processing Elementor widgets', 'info', array( 'processed_count' => $processed_count ) );
		}

		/**
		 * Replace wpforms IDs in shortcode widget.
		 *
		 * @since 1.1.23
		 *
		 * @param array<string, mixed> $widget Widget data.
		 * @return void
		 */
		public function replace_wpforms_ids( &$widget ) {
			$ids_mapping = get_option( 'astra_sites_wpforms_ids_mapping', array() );

			if ( empty( $ids_mapping ) ) {
				return;
			}

			// Update wpforms_id in shortcode widget.
			foreach ( $ids_mapping as $old_id => $new_id ) {
				$widget['settings']['shortcode'] = str_replace( '[wpforms id="' . $old_id . '"]', '[wpforms id="' . $new_id . '"]', $widget['settings']['shortcode'] );
			}
		}

		/**
		 * Replace form IDs in WPForms widget.
		 *
		 * @since 1.1.23
		 *
		 * @param array<string, mixed> $widget Widget data.
		 * @return void
		 */
		public function replace_wpforms_widget_ids( &$widget ) {
			$ids_mapping = get_option( 'astra_sites_wpforms_ids_mapping', array() );

			if ( empty( $ids_mapping ) ) {
				return;
			}

			// Update form_id for WPForms widget.
			$widget['settings']['form_id'] = $ids_mapping[ $widget['settings']['form_id'] ] ?? $widget['settings']['form_id'];
		}

		/**
		 * Replace SureForm IDs in content.
		 *
		 * @since 1.1.9
		 *
		 * @param array<string, mixed> $widget Widget data.
		 * @return void
		 */
		public function replace_sureforms_widget_id( &$widget ) {
			$sureform_id_map = get_option( 'astra_sites_sureforms_id_map', array() );

			if ( empty( $sureform_id_map ) ) {
				return;
			}

			$widget['settings']['srfm_form_block'] = $sureform_id_map[ $widget['settings']['srfm_form_block'] ] ?? $widget['settings']['srfm_form_block'];
		}

		/**
		 * Replace SureForm IDs in content.
		 *
		 * @since 1.1.9
		 *
		 * @param array<string, mixed> $widget Widget data.
		 * @return void
		 */
		public function replace_sureforms_ids( &$widget ) {
			$sureform_id_map = get_option( 'astra_sites_sureforms_id_map', array() );

			if ( empty( $sureform_id_map ) ) {
				return;
			}

			foreach ( $sureform_id_map as $old_id => $new_id ) {
				$widget['settings']['shortcode'] = str_replace( '[sureforms id="' . $old_id . '"]', '[sureforms id="' . $new_id . '"]', $widget['settings']['shortcode'] );
			}
		}

		/**
		 * Replace SureForm IDs in content.
		 *
		 * @since 1.1.9
		 *
		 * @param array<string, mixed> $widget Widget data.
		 * @return void
		 */
		public function replace_surecart_forms_ids( &$widget ) {
			$surecart_forms_id_map = get_option( 'astra_sites_surecart_forms_id_map', array() );

			if ( empty( $surecart_forms_id_map ) ) {
				return;
			}

			foreach ( $surecart_forms_id_map as $old_id => $new_id ) {
				$widget['settings']['shortcode'] = str_replace( '[sc_form id=' . $old_id . ']', '[sc_form id=' . $new_id . ']', $widget['settings']['shortcode'] );
			}

		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	ST_Batch_Processing_Elementor::get_instance();

endif;
