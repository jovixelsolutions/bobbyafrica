<?php
/**
 * Plugin Installer for Starter Templates Importer
 *
 * @package Starter Templates Importer
 */

namespace STImporter\Importer;

use STImporter\Importer\ST_Importer_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Installer Class
 */
class ST_Plugin_Installer {

	/**
	 * Install and activate a plugin
	 *
	 * @since 1.1.21
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $plugin_file Plugin main file path (relative to plugins directory).
	 * @return array<string, mixed> Result array with status and message
	 */
	public static function install_and_activate_plugin( $plugin_slug, $plugin_file ) {
		ST_Importer_Log::add(
			sprintf( 'Starting plugin installation and activation: %s (file: %s)', $plugin_slug, $plugin_file ),
			'info',
			array(
				'plugin_slug' => $plugin_slug,
				'plugin_file' => $plugin_file,
			)
		);

		// Check user capabilities.
		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
			ST_Importer_Log::add(
				sprintf( 'Permission denied for plugin installation: %s', $plugin_slug ),
				'error',
				array(
					'plugin_slug' => $plugin_slug,
					'plugin_file' => $plugin_file,
					'error'       => 'User lacks install_plugins or activate_plugins capability',
				)
			);

			return array(
				'status' => false,
				'error'  => __( "Permission denied: You don't have sufficient permissions to install or activate plugins. Please contact your site administrator.", 'astra-sites' ),
			);
		}

		try {
			// Check if plugin is already active.
			if ( is_plugin_active( $plugin_file ) ) {
				ST_Importer_Log::add(
					sprintf( 'Plugin already active: %s', $plugin_slug ),
					'info',
					array(
						'plugin_slug' => $plugin_slug,
						'plugin_file' => $plugin_file,
						'status'      => 'already_active',
					)
				);

				return array(
					'status'  => true,
					// translators: %s is the plugin slug.
					'message' => sprintf( __( '%s plugin is already active.', 'astra-sites' ), $plugin_slug ),
				);
			}

			// Check if plugin is installed but not active.
			if ( self::is_plugin_installed( $plugin_file ) ) {
				$activation_result = self::activate_plugin( $plugin_file );
				if ( $activation_result['status'] ) {
					ST_Importer_Log::add(
						sprintf( 'Plugin activated successfully: %s', $plugin_slug ),
						'success',
						array(
							'plugin_slug' => $plugin_slug,
							'plugin_file' => $plugin_file,
							'status'      => 'activated',
						)
					);

					return array(
						'status'  => true,
						// translators: %s is the plugin slug.
						'message' => sprintf( __( '%s plugin activated successfully.', 'astra-sites' ), $plugin_slug ),
					);
				} else {
					ST_Importer_Log::add(
						sprintf( 'Plugin activation failed: %s', $plugin_slug ),
						'error',
						array(
							'plugin_slug'       => $plugin_slug,
							'plugin_file'       => $plugin_file,
							'activation_result' => $activation_result,
						)
					);

					return $activation_result;
				}
			}

			// Plugin not installed, need to install first.
			$installation_result = self::install_plugin( $plugin_slug );
			if ( ! $installation_result['status'] ) {
				return $installation_result;
			}

			// Now activate the plugin.
			$activation_result = self::activate_plugin( $plugin_file );
			if ( $activation_result['status'] ) {
				ST_Importer_Log::add(
					sprintf( 'Plugin installed and activated successfully: %s', $plugin_slug ),
					'success',
					array(
						'plugin_slug' => $plugin_slug,
						'plugin_file' => $plugin_file,
						'status'      => 'installed_and_activated',
					)
				);

				return array(
					'status'  => true,
					// translators: %s is the plugin slug.
					'message' => sprintf( __( '%s plugin installed and activated successfully.', 'astra-sites' ), $plugin_slug ),
				);
			} else {
				ST_Importer_Log::add(
					sprintf( 'Plugin installed but activation failed: %s', $plugin_slug ),
					'error',
					array(
						'plugin_slug'       => $plugin_slug,
						'plugin_file'       => $plugin_file,
						'activation_result' => $activation_result,
					)
				);

				return $activation_result;
			}
		} catch ( \Exception $e ) {
			ST_Importer_Log::add(
				sprintf( 'Exception during plugin installation: %s', $plugin_slug ),
				'error',
				array(
					'plugin_slug'     => $plugin_slug,
					'plugin_file'     => $plugin_file,
					'exception'       => $e->getMessage(),
					'exception_trace' => $e->getTraceAsString(),
				)
			);

			return array(
				'status' => false,
				// translators: %s is the exception message.
				'error'  => sprintf( __( 'Plugin installation failed: %s', 'astra-sites' ), $e->getMessage() ),
			);
		}
	}

	/**
	 * Check if plugin is installed
	 *
	 * @since 1.1.21
	 *
	 * @param string $plugin_file Plugin main file path.
	 * @return bool
	 */
	private static function is_plugin_installed( $plugin_file ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $plugin_file ] );
	}

	/**
	 * Install plugin
	 *
	 * @since 1.1.21
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return array<string, mixed> Result array
	 */
	private static function install_plugin( $plugin_slug ) {
		ST_Importer_Log::add(
			sprintf( 'Starting plugin installation: %s', $plugin_slug ),
			'info',
			array(
				'plugin_slug' => $plugin_slug,
			)
		);

		// Include necessary WordPress files.
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		if ( ! class_exists( 'WP_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}
		if ( ! class_exists( 'WP_Ajax_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		}

		try {
			// Get plugin information.
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin_slug,
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'requires'          => false,
						'rating'            => false,
						'ratings'           => false,
						'downloaded'        => false,
						'last_updated'      => false,
						'added'             => false,
						'tags'              => false,
						'compatibility'     => false,
						'homepage'          => false,
						'donate_link'       => false,
					),
				)
			);

			if ( is_wp_error( $api ) ) {
				ST_Importer_Log::add(
					sprintf( 'Plugin information not found: %s', $plugin_slug ),
					'error',
					array(
						'plugin_slug'   => $plugin_slug,
						'error_message' => $api->get_error_message(),
						'error_code'    => $api->get_error_code(),
					)
				);

				return array(
					'status' => false,
					// translators: %s is the error message.
					'error'  => sprintf( __( 'Plugin information not found: %s', 'astra-sites' ), $api->get_error_message() ),
				);
			}

			// Install the plugin.
			$download_link  = isset( $api->download_link ) ? $api->download_link : '';
			$upgrader       = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
			$install_result = $upgrader->install( $download_link );

			if ( is_wp_error( $install_result ) ) {
				ST_Importer_Log::add(
					sprintf( 'Plugin installation failed (WP_Error): %s', $plugin_slug ),
					'error',
					array(
						'plugin_slug'   => $plugin_slug,
						'download_link' => $download_link,
						'error_message' => $install_result->get_error_message(),
						'error_code'    => $install_result->get_error_code(),
					)
				);

				return array(
					'status' => false,
					// translators: %s is the error message.
					'error'  => sprintf( __( 'Plugin installation failed: %s', 'astra-sites' ), $install_result->get_error_message() ),
				);
			}

			if ( ! $install_result ) {
				ST_Importer_Log::add(
					sprintf( 'Plugin installation failed (unknown reason): %s', $plugin_slug ),
					'error',
					array(
						'plugin_slug'   => $plugin_slug,
						'download_link' => $download_link,
						'error'         => 'install_result returned false',
					)
				);

				return array(
					'status' => false,
					'error'  => __( 'Plugin installation failed for unknown reason.', 'astra-sites' ),
				);
			}

			ST_Importer_Log::add(
				sprintf( 'Plugin installed successfully: %s', $plugin_slug ),
				'success',
				array(
					'plugin_slug'   => $plugin_slug,
					'download_link' => $download_link,
				)
			);

			return array(
				'status'  => true,
				// translators: %s is the plugin slug.
				'message' => sprintf( __( '%s plugin installed successfully.', 'astra-sites' ), $plugin_slug ),
			);

		} catch ( \Exception $e ) {
			ST_Importer_Log::add(
				sprintf( 'Exception during plugin installation: %s', $plugin_slug ),
				'error',
				array(
					'plugin_slug'     => $plugin_slug,
					'exception'       => $e->getMessage(),
					'exception_trace' => $e->getTraceAsString(),
				)
			);

			return array(
				'status' => false,
				// translators: %s is the exception message.
				'error'  => sprintf( __( 'Plugin installation exception: %s', 'astra-sites' ), $e->getMessage() ),
			);
		}
	}

	/**
	 * Activate plugin
	 *
	 * @since 1.1.21
	 *
	 * @param string $plugin_file Plugin main file path.
	 * @return array<string, mixed> Result array
	 */
	private static function activate_plugin( $plugin_file ) {
		ST_Importer_Log::add(
			sprintf( 'Starting plugin activation: %s', $plugin_file ),
			'info',
			array(
				'plugin_file' => $plugin_file,
			)
		);

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$activation_result = activate_plugin( $plugin_file );

		if ( is_wp_error( $activation_result ) ) {
			ST_Importer_Log::add(
				sprintf( 'Plugin activation failed: %s', $plugin_file ),
				'error',
				array(
					'plugin_file'   => $plugin_file,
					'error_message' => $activation_result->get_error_message(),
					'error_code'    => $activation_result->get_error_code(),
				)
			);

			return array(
				'status' => false,
				// translators: %s is the error message.
				'error'  => sprintf( __( 'Plugin activation failed: %s', 'astra-sites' ), $activation_result->get_error_message() ),
			);
		}

		self::after_plugin_activate( $plugin_file );

		ST_Importer_Log::add(
			sprintf( 'Plugin activated successfully: %s', $plugin_file ),
			'success',
			array(
				'plugin_file' => $plugin_file,
			)
		);

		return array(
			'status'  => true,
			'message' => __( 'Plugin activated successfully.', 'astra-sites' ),
		);
	}

	/**
	 * After Plugin Activate
	 *
	 * @since 4.4.39
	 *
	 * @param  string               $plugin_init        Plugin Init File.
	 * @param  array<string, mixed> $options            Site Options.
	 * @param  array<string, mixed> $enabled_extensions Enabled Extensions.
	 * @param  string               $plugin_slug        Plugin slug.
	 * @param  bool                 $was_plugin_active  Flag indicating if the plugin was already active.
	 * @return void
	 */
	public static function after_plugin_activate( $plugin_init = '', $options = array(), $enabled_extensions = array(), $plugin_slug = '', $was_plugin_active = false ) {
		$data = array(
			'astra_site_options' => $options,
			'enabled_extensions' => $enabled_extensions,
			'plugin_slug'        => $plugin_slug,
			'was_plugin_active'  => $was_plugin_active,
		);

		do_action( 'astra_sites_after_plugin_activation', $plugin_init, $data );
	}

	/**
	 * Install and activate Spectra plugin specifically
	 *
	 * @since 1.1.21
	 *
	 * @return array<string, mixed> Result array
	 */
	public static function install_spectra_plugin() {
		return self::install_and_activate_plugin( 'ultimate-addons-for-gutenberg', 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' );
	}
}
