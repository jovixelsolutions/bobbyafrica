<?php
/**
 * Zip AI - Module.
 *
 * This file is used to register and manage the Zip AI Modules.
 *
 * @package zip-ai
 */

namespace STImporter\Resetter;

use Throwable;
use Exception;
use STImporter\Importer\ST_Importer_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Module Class.
 */
class ST_Resetter {

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
	 * Backup our existing settings.
	 *
	 * @return string
	 */
	public static function backup_settings() {

		ST_Importer_Log::add( 'Starting backup of site settings.' );

		$file_name    = 'astra-sites-backup-' . gmdate( 'd-M-Y-h-i-s' ) . '.json';
		$old_settings = get_option( 'astra-settings', array() );
		$upload_dir   = self::log_dir();
		$upload_path  = trailingslashit( $upload_dir['path'] );
		$log_file     = $upload_path . $file_name;
		$file_system  = self::get_filesystem();

		ST_Importer_Log::add(
			sprintf( 'Backup Settings - Backup file path: %s', $log_file ),
			'info',
			array(
				'file_name'      => $file_name,
				'settings_count' => count( $old_settings ),
			)
		);

		// If file system fails? Then take a backup in site option.
		if ( method_exists( $file_system, 'put_contents' ) && false === $file_system->put_contents( $log_file, wp_json_encode( $old_settings ), FS_CHMOD_FILE ) ) {
			update_option( 'astra_sites_' . $file_name, $old_settings, false );
			ST_Importer_Log::add(
				'Backup Settings - Backup file could not be written to filesystem. Using site option as fallback.',
				'warning',
				array(
					'fallback_option_key' => 'astra_sites_' . $file_name,
				)
			);
		} else {
			ST_Importer_Log::add(
				sprintf( 'Backup Settings - Settings backup created successfully at %s', $log_file ),
				'success',
				array(
					'file_path'      => $log_file,
					'settings_count' => count( $old_settings ),
				)
			);
		}

		return $log_file;
	}

	/**
	 * Log file directory
	 *
	 * @since 1.0.0
	 * @param  string $dir_name Directory Name.
	 * @return array<string, string>    Uploads directory array.
	 */
	public static function log_dir( $dir_name = 'st-importer' ) {

		$upload_dir = wp_upload_dir();

		// Build the paths.
		$dir_info = array(
			'path' => $upload_dir['basedir'] . '/' . $dir_name . '/',
			'url'  => $upload_dir['baseurl'] . '/' . $dir_name . '/',
		);

		// Create the upload dir if it doesn't exist.
		if ( ! file_exists( $dir_info['path'] ) ) {

			// Create the directory.
			wp_mkdir_p( $dir_info['path'] );

			if ( method_exists( self::get_filesystem(), 'put_contents' ) ) {
				// Add an index file for security.
				self::get_filesystem()->put_contents( $dir_info['path'] . 'index.html', '' );

				// Add an .htaccess for security.
				self::get_filesystem()->put_contents( $dir_info['path'] . '.htaccess', 'deny from all' );
			}
		}

		return $dir_info;
	}

	/**
	 * Get an instance of WP_Filesystem_Direct.
	 *
	 * @since 1.0.0
	 * @return object A WP_Filesystem_Direct instance.
	 */
	public static function get_filesystem() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();

		return $wp_filesystem;
	}

	/**
	 * Reset site options
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $options option aray.
	 * @return void
	 */
	public static function reset_site_options( $options = array() ) {

		if ( ! is_array( $options ) ) {
			ST_Importer_Log::add(
				'Reset Site Options - No options provided or invalid format.',
				'warning'
			);
			return;
		}

		if ( empty( $options ) ) {
			ST_Importer_Log::add(
				'Reset Site Options - No site options to reset.',
				'warning'
			);
			return;
		}

		ST_Importer_Log::add(
			sprintf( 'Reset Site Options - Starting reset of %d site options.', count( $options ) ),
			'info',
			array(
				'total_options' => count( $options ),
			)
		);

		// Check if SureCart is required plugin, then clear API token and account cache.
		$required_plugins = (array) astra_get_site_data( 'required-plugins' );
		$plugins_slug     = array_column( $required_plugins, 'slug' );
		if ( in_array( 'surecart', $plugins_slug, true ) ) {
			ST_Importer_Log::add( 'Reset Site Options - SureCart detected as required plugin. Clearing API token and account cache.', 'info' );

			if ( class_exists( '\SureCart' ) && class_exists( '\SureCart\Models\ApiToken' ) ) {
				// 1. Clear the API token
				\SureCart\Models\ApiToken::clear();

				// 2. Clear the account cache.
				\SureCart::account()->clearCache();
			}

			// Optionally, clear the option value to ensure it's reset.
			$options['sc_api_token'] = '';
		}

		$deleted_count = 0;
		$failed_count  = 0;

		foreach ( $options as $option_key => $option_value ) {
			if ( delete_option( $option_key ) ) {
				$deleted_count++;
			} else {
				$failed_count++;
			}
		}

		ST_Importer_Log::add(
			sprintf( 'Reset Site Options - Site options reset completed. Deleted: %d, Failed or not found: %d', $deleted_count, $failed_count ),
			'success',
			array(
				'total_options' => count( $options ),
				'deleted_count' => $deleted_count,
				'failed_count'  => $failed_count,
				'option_keys'   => array_keys( $options ),
			)
		);
	}

	/**
	 * Reset customizer data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function reset_customizer_data() {

		ST_Importer_Log::add(
			'Reset Customizer Data - Starting reset of customizer settings.',
			'info'
		);

		$settings       = get_option( 'astra-settings', array() );
		$settings_count = count( $settings );

		ST_Importer_Log::add(
			sprintf( 'Reset Customizer Data - Retrieved customizer data with %d settings entries.', $settings_count ),
			'info',
			array(
				'settings_count' => $settings_count,
			)
		);

		if ( delete_option( 'astra-settings' ) ) {
			ST_Importer_Log::add(
				sprintf( 'Reset Customizer Data - Customizer settings successfully reset. Deleted %d settings entries.', $settings_count ),
				'success',
				array(
					'settings_count' => $settings_count,
					'option_name'    => 'astra-settings',
				)
			);
		} else {
			ST_Importer_Log::add(
				'Reset Customizer Data - Customizer settings reset completed, but option may not have existed or was already empty.',
				'warning',
				array(
					'settings_count' => $settings_count,
					'option_name'    => 'astra-settings',
				)
			);
		}
	}

	/**
	 * Reset widgets data
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $old_widgets_data widget data.
	 * @return void
	 */
	public static function reset_widgets_data( $old_widgets_data = array() ) {

		ST_Importer_Log::add(
			'Reset Widgets Data - Starting reset of widgets data.',
			'info'
		);

		if ( empty( $old_widgets_data ) ) {
			ST_Importer_Log::add(
				'Reset Widgets Data - No old widgets data provided for reset.',
				'warning'
			);
			return;
		}

		$old_widget_ids = array();
		foreach ( $old_widgets_data as $old_sidebar_key => $old_widgets ) {
			if ( ! empty( $old_widgets ) && is_array( $old_widgets ) ) {
				$old_widget_ids = array_merge( $old_widget_ids, $old_widgets );
			}
		}

		ST_Importer_Log::add(
			sprintf( 'Reset Widgets Data - Retrieved %d widgets from %d sidebars.', count( $old_widget_ids ), count( $old_widgets_data ) ),
			'info',
			array(
				'total_widgets'  => count( $old_widget_ids ),
				'total_sidebars' => count( $old_widgets_data ),
			)
		);

		// Process if not empty.
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );

		if ( empty( $old_widget_ids ) || empty( $sidebars_widgets ) ) {
			ST_Importer_Log::add(
				'Reset Widgets Data - No widgets or sidebars found to reset.',
				'warning',
				array(
					'old_widget_ids'   => count( $old_widget_ids ),
					'sidebars_widgets' => count( $sidebars_widgets ),
				)
			);
			return;
		}

		$moved_to_inactive     = 0;
		$removed_from_sidebars = 0;

		foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
			$widgets = (array) $widgets;

			if ( ! empty( $widgets ) && is_array( $widgets ) ) {
				foreach ( $widgets as $widget_id ) {

					if ( in_array( $widget_id, $old_widget_ids, true ) ) {

						// Move old widget to inacitve list.
						$sidebars_widgets['wp_inactive_widgets'][] = $widget_id;
						$moved_to_inactive++;

						// Remove old widget from sidebar.
						$sidebars_widgets[ $sidebar_id ] = array_diff( $sidebars_widgets[ $sidebar_id ], array( $widget_id ) );
						$removed_from_sidebars++;
					}
				}
			}
		}

		update_option( 'sidebars_widgets', $sidebars_widgets );

		ST_Importer_Log::add(
			sprintf( 'Reset Widgets Data - Widgets reset completed. Moved %d widgets to inactive, removed %d from sidebars.', $moved_to_inactive, $removed_from_sidebars ),
			'success',
			array(
				'total_widgets'         => count( $old_widget_ids ),
				'moved_to_inactive'     => $moved_to_inactive,
				'removed_from_sidebars' => $removed_from_sidebars,
			)
		);
	}

	/**
	 * Reset posts in chunks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function reset_posts() {

		ST_Importer_Log::add(
			'Reset Posts - Starting post deletion process.',
			'info'
		);

		ST_Resetter::get_instance()->start_error_handler();

		// Suspend bunches of stuff in WP core.
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );

		$posts = json_decode( stripslashes( sanitize_text_field( $_POST['ids'] ) ), true ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $posts ) ) {
			ST_Importer_Log::add(
				'Reset Posts - No posts provided for deletion.',
				'warning'
			);
			wp_suspend_cache_invalidation( false );
			wp_defer_term_counting( false );
			wp_defer_comment_counting( false );
			self::stop_error_handler();
			return;
		}

		ST_Importer_Log::add(
			sprintf( 'Reset Posts - Retrieved %d posts for deletion.', count( $posts ) ),
			'info',
			array(
				'total_posts' => count( $posts ),
			)
		);

		$deleted_count = 0;
		$post_types    = array();

		foreach ( $posts as $key => $post_id ) {
			$post_id = absint( $post_id );
			if ( $post_id ) {
				$post_type = get_post_type( $post_id );
				if ( ! isset( $post_types[ $post_type ] ) ) {
					$post_types[ $post_type ] = 0;
				}
				$post_types[ $post_type ]++;

				do_action( 'astra_sites_before_delete_imported_posts', $post_id, $post_type );
				if ( wp_delete_post( $post_id, true ) ) {
					$deleted_count++;
				}
			}
		}

		// Re-enable stuff in core.
		wp_suspend_cache_invalidation( false );
		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		ST_Importer_Log::add(
			sprintf( 'Reset Posts - Post deletion completed. Deleted %d of %d posts.', $deleted_count, count( $posts ) ),
			'success',
			array(
				'total_posts'   => count( $posts ),
				'deleted_count' => $deleted_count,
				'post_types'    => $post_types,
			)
		);

		self::stop_error_handler();
	}

	/**
	 * Start the error handling.
	 *
	 * @return void
	 */
	public function start_error_handler() {
		if ( ! interface_exists( 'Throwable' ) ) {
			// Fatal error handler for PHP < 7.
			register_shutdown_function( array( $this, 'shutdown_handler' ) );
		}

		// Fatal error handler for PHP >= 7, and uncaught exception handler for all PHP versions.
		set_exception_handler( array( $this, 'exception_handler' ) );
	}

	/**
	 * Stop and restore the error handlers.
	 *
	 * @return void
	 */
	public static function stop_error_handler() {
		// Restore the error handlers.
		restore_error_handler();
		restore_exception_handler();
	}

	/**
	 * Displays fatal error output for sites running PHP < 7.
	 *
	 * @return void
	 */
	public function shutdown_handler() {
		$e = error_get_last();

		if ( empty( $e ) || ! ( $e['type'] & ST_ERROR_FATALS ) ) { // @phpstan-ignore-line
			return;
		}

		if ( $e['type'] & E_RECOVERABLE_ERROR ) {
			$error = 'Catchable fatal error';
		} else {
			$error = 'Fatal error';
		}

		if ( wp_doing_ajax() ) {
			wp_send_json_error(
				array(
					'message' => __( 'There was an error your website.', 'astra-sites' ),
					'stack'   => array(
						'error-message' => $error,
						'error'         => $e,
					),
				)
			);
		}
	}

	/**
	 * Uncaught exception handler.
	 *
	 * In PHP >= 7 this will receive a Throwable object.
	 * In PHP < 7 it will receive an Exception object.
	 *
	 * @throws Exception Exception that is catched.
	 * @param Throwable|Exception $e The error or exception.
	 *
	 * @return void
	 */
	public function exception_handler( $e ) {
		if ( is_a( $e, 'Exception' ) ) {
			$error = 'Uncaught Exception';
		} else {
			$error = 'Uncaught Error';
		}

		if ( wp_doing_ajax() ) {
			wp_send_json_error(
				array(
					'message' => __( 'There was an error on your website.', 'astra-sites' ),
					'stack'   => array(
						'error-message' => sprintf(
							'%s: %s',
							$error,
							$e->getMessage()
						),
						'file'          => $e->getFile(),
						'line'          => $e->getLine(),
						'trace'         => $e->getTrace(),
					),
				)
			);
		}

		throw $e;
	}

	/**
	 * Reset terms and forms.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function reset_terms_and_forms() {

		ST_Importer_Log::add(
			'Reset Terms and Forms - Starting reset of terms and forms.',
			'info'
		);

		ST_Resetter::get_instance()->start_error_handler();

		$terms = self::astra_sites_get_reset_term_data();

		if ( empty( $terms ) ) {
			ST_Importer_Log::add(
				'Reset Terms and Forms - No terms found for deletion.',
				'warning'
			);
		} else {
			ST_Importer_Log::add(
				sprintf( 'Reset Terms and Forms - Retrieved %d terms for deletion.', count( $terms ) ),
				'info',
				array(
					'total_terms' => count( $terms ),
				)
			);

			$deleted_terms   = 0;
			$failed_terms    = 0;
			$term_taxonomies = array();

			foreach ( $terms as $key => $term_id ) {
				$term_id = absint( $term_id );
				if ( $term_id ) {
					$term = get_term( $term_id );
					if ( ! is_wp_error( $term ) && ! empty( $term ) && is_object( $term ) ) {

						if ( ! isset( $term_taxonomies[ $term->taxonomy ] ) ) {
							$term_taxonomies[ $term->taxonomy ] = 0;
						}
						$term_taxonomies[ $term->taxonomy ]++;

						do_action( 'astra_sites_before_delete_imported_terms', $term_id, $term );

						if ( wp_delete_term( $term_id, $term->taxonomy ) ) {
							$deleted_terms++;
						} else {
							$failed_terms++;
						}
					}
				}
			}

			ST_Importer_Log::add(
				sprintf( 'Reset Terms and Forms - Term deletion completed. Deleted %d of %d terms.', $deleted_terms, count( $terms ) ),
				'success',
				array(
					'total_terms'     => count( $terms ),
					'deleted_count'   => $deleted_terms,
					'failed_count'    => $failed_terms,
					'term_taxonomies' => $term_taxonomies,
				)
			);
		}

		$forms = self::astra_sites_get_reset_form_data();

		if ( empty( $forms ) ) {
			ST_Importer_Log::add(
				'Reset Terms and Forms - No forms found for deletion.',
				'warning'
			);
		} else {
			ST_Importer_Log::add(
				sprintf( 'Reset Terms and Forms - Retrieved %d forms for deletion.', count( $forms ) ),
				'info',
				array(
					'total_forms' => count( $forms ),
				)
			);

			$deleted_forms = 0;
			$failed_forms  = 0;

			foreach ( $forms as $key => $post_id ) {
				$post_id = absint( $post_id );
				if ( $post_id ) {

					do_action( 'astra_sites_before_delete_imported_wp_forms', $post_id );
					if ( wp_delete_post( $post_id, true ) ) {
						$deleted_forms++;
					} else {
						$failed_forms++;
					}
				}
			}

			ST_Importer_Log::add(
				sprintf( 'Reset Terms and Forms - Form deletion completed. Deleted %d of %d forms.', $deleted_forms, count( $forms ) ),
				'success',
				array(
					'total_forms'   => count( $forms ),
					'deleted_count' => $deleted_forms,
					'failed_count'  => $failed_forms,
				)
			);
		}

		self::stop_error_handler();
	}

	/**
	 * Get all the forms to be reset.
	 *
	 * @since 1.0.0
	 * @return array<int, int>
	 */
	public static function astra_sites_get_reset_form_data() {
		global $wpdb;

		$form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_astra_sites_imported_wp_forms'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We need this to get all the WP forms. Traditional WP_Query would have been expensive here.

		return $form_ids;
	}

	/**
	 * Get all the terms to be reset.
	 *
	 * @since 1.0.0
	 * @return array<int, int>
	 */
	public static function astra_sites_get_reset_term_data() {
		global $wpdb;

		$term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_astra_sites_imported_term'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We need this to get all the terms and taxonomy. Traditional WP_Query would have been expensive here.

		return $term_ids;
	}
}
