<?php
/**
 * Starter Templates Importer Log
 *
 * @package Starter Templates Importer
 * @since 1.1.25
 */

namespace STImporter\Importer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ST_Importer_Log - Logging class for import operations
 *
 * This class provides comprehensive logging functionality for the import process,
 * including severity levels and action hooks for extensibility.
 */
class ST_Importer_Log {

	/**
	 * Instance
	 *
	 * @since 1.1.25
	 * @var ST_Importer_Log|null Class instance
	 */
	private static $instance = null;

	/**
	 * Log File Path
	 *
	 * @since 1.1.25
	 * @var string|null
	 */
	private static $log_file = null;

	/**
	 * Get instance
	 *
	 * @since 1.1.25
	 * @return ST_Importer_Log Class instance.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get prefix for naming conventions (directories, options, transients, CSS classes)
	 *
	 * @param bool $hyphen Whether to return prefix with hyphens. Default false (underscores).
	 *
	 * @since 1.1.25
	 * @return string Prefix with underscores (e.g., 'astra_sites', 'ai_builder') or hyphens (e.g., 'astra-sites', 'ai-builder').
	 */
	public static function get_prefix( $hyphen = false ) {
		/**
		 * Filter the prefix used for directories, options, transients, and CSS classes.
		 *
		 * Returns prefix with underscores by default (e.g., 'astra_sites', 'ai_builder').
		 * The method will automatically convert to hyphens for directories and CSS classes.
		 *
		 * @param string $prefix The prefix to use.
		 * @since 1.1.25
		 */
		$prefix = apply_filters( 'st_importer_log_prefix', 'st_importer' );

		if ( $hyphen ) {
			$prefix = str_replace( '_', '-', $prefix );
		}

		return $prefix;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.1.25
	 */
	private function __construct() {
		// Check file read/write permissions.
		if ( current_user_can( 'edit_posts' ) ) {
			add_action( 'admin_init', array( $this, 'has_file_read_write' ) );
		}

		// Hook into import start action.
		add_action( 'astra_sites_import_start', array( $this, 'start' ), 10, 2 );

		// Cleanup on import success.
		add_action( 'astra_sites_after_analytics_reporting', array( $this, 'remove_option' ) );

		// Cleanup on WP-CLI import success.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			add_action( 'astra_sites_import_complete', array( $this, 'remove_option' ) );
		}
	}

	/**
	 * Check file read/write permissions and process.
	 *
	 * @since 1.1.25
	 * @return void
	 */
	public function has_file_read_write() {
		$upload_dir = self::log_dir();

		$file_created = false;
		if ( method_exists( self::get_filesystem(), 'put_contents' ) ) {
			$file_created = self::get_filesystem()->put_contents( $upload_dir['path'] . 'index.html', '' );
		}

		if ( ! $file_created ) {
			add_action( 'admin_notices', array( $this, 'file_permission_notice' ) );
			return;
		}

		// Set log file.
		self::set_log_file();

		/**
		 * Fires after file permissions are verified and log file is set.
		 *
		 * This action allows parent plugins to hook into the initialization process.
		 *
		 * @since 1.1.25
		 */
		do_action( 'st_importer_log_init' );
	}

	/**
	 * File Permission Notice
	 *
	 * @since 1.1.25
	 * @return void
	 */
	public function file_permission_notice() {
		$upload_dir  = self::log_dir();
		$prefix      = self::get_prefix();
		$plugin_name = defined( 'ASTRA_SITES_NAME' ) ? ASTRA_SITES_NAME : 'Starter Templates';

		/* translators: %1$s refers to the plugin name */
		$notice = sprintf( __( 'Required File Permissions to import the templates from %s are missing.', 'astra-sites' ), $plugin_name );

		$notice_class = 'notice notice-error ' . $prefix . '-must-notices ' . $prefix . '-file-permission-issue';
		?>
		<div class="<?php echo esc_attr( $notice_class ); ?>">
			<p><?php echo esc_html( $notice ); ?></p>
			<?php if ( defined( 'FS_METHOD' ) ) { ?>
				<p><?php esc_html_e( 'This is usually due to inconsistent file permissions.', 'astra-sites' ); ?></p>
				<p><code><?php echo esc_html( $upload_dir['path'] ); ?></code></p>
			<?php } else { ?>
				<p><?php esc_html_e( 'You can easily update permissions by adding the following code into the wp-config.php file.', 'astra-sites' ); ?></p>
				<p><code>define( 'FS_METHOD', 'direct' );</code></p>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Log file directory
	 *
	 * @since 1.1.25
	 * @param  string $dir_name Directory Name.
	 * @return array<string, string>    Uploads directory array.
	 */
	public static function log_dir( $dir_name = 'st-importer' ) {
		// Get prefix and convert underscores to hyphens for directory name.
		$dir_name   = self::get_prefix( true );
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
	 * @since 1.1.25
	 * @return object A WP_Filesystem_Direct instance.
	 */
	public static function get_filesystem() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();

		return $wp_filesystem;
	}

	/**
	 * Get option name for recent import log file.
	 *
	 * @since 1.1.25
	 * @return string Option name for recent import log file.
	 */
	public static function get_option_name() {
		$prefix = self::get_prefix();
		return $prefix . '_recent_import_log_file';
	}

	/**
	 * Remove option on import success.
	 *
	 * @since 1.1.25
	 * @return void
	 */
	public function remove_option() {
		self::add( 'Import process completed. Cleaning up log file option.', 'info' );
		delete_option( self::get_option_name() );
	}

	/**
	 * Set log file
	 *
	 * @return void
	 *
	 * @since 1.1.25
	 */
	public static function set_log_file() {
		$upload_dir  = self::log_dir();
		$upload_path = trailingslashit( $upload_dir['path'] );

		// File format e.g. 'import-31-Oct-2017-06-39-12-hashcode.log'.
		self::$log_file = $upload_path . 'import-' . gmdate( 'd-M-Y-h-i-s' ) . '-' . wp_hash( 'starter-templates-log' ) . '.log';

		$option_name = self::get_option_name();
		if ( ! get_option( $option_name, false ) ) {
			update_option( $option_name, self::$log_file, false );
		}
	}

	/**
	 * Write content to a file.
	 *
	 * @param string               $message  The log message content.
	 * @param string               $severity The severity level (info, success, warning, error, fatal). Default 'info'.
	 * @param array<string, mixed> $context  Additional context data for the log entry. Default empty array.
	 *
	 * @since 1.1.25
	 * @return void
	 */
	public static function add( $message, $severity = 'info', $context = array() ) {
		// Normalize severity level.
		$severity           = strtolower( $severity );
		$allowed_severities = array( 'info', 'success', 'warning', 'error', 'fatal' );
		if ( ! in_array( $severity, $allowed_severities, true ) ) {
			$severity = 'info';
		}

		/**
		 * Fires before adding content to the import log file.
		 *
		 * This action allows parent plugins to hook into the logging process
		 * and perform custom actions based on the log severity and content.
		 *
		 * @since 1.1.25
		 *
		 * @param string               $message  The log message content.
		 * @param string               $severity The severity level (info, success, warning, error, fatal).
		 * @param array<string, mixed> $context  Additional context data for the log entry.
		 */
		do_action( 'st_importer_log_before_add', $message, $severity, $context );

		/**
		 * Filter the recent import log file path.
		 *
		 * @param string $option_name The option name for the recent import log file.
		 * @since 1.1.25
		 */
		$option_name = self::get_option_name();
		if ( get_option( $option_name, false ) ) {
			$log_file = get_option( $option_name, self::$log_file );
		} else {
			$log_file = self::$log_file;
		}

		// Format the log entry with severity level and timestamp.
		$severity_label    = strtoupper( $severity );
		$timestamp         = self::current_time();
		$formatted_content = sprintf( '[%s] [%s] %s', $timestamp, $severity_label, $message );

		// Add context data if provided.
		if ( ! empty( $context ) ) {
			$formatted_content .= ' | Context: ' . wp_json_encode( $context );
		}

		if ( apply_filters( 'astra_sites_debug_logs', false ) ) {
			error_log( $formatted_content ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is for the debug logs while importing. This is conditional and will not be logged in the debug.log file for normal users.
		}

		$existing_data = '';
		if ( file_exists( (string) $log_file ) && method_exists( self::get_filesystem(), 'get_contents' ) ) {
			$existing_data = self::get_filesystem()->get_contents( $log_file );
		}

		// Bail early to avoid potential Fatal errors during CLI imports.
		if ( ! $log_file ) {
			return;
		}

		// Style separator.
		$separator = PHP_EOL;

		if ( method_exists( self::get_filesystem(), 'put_contents' ) ) {
			self::get_filesystem()->put_contents( $log_file, $existing_data . $separator . $formatted_content, FS_CHMOD_FILE );
		}

		/**
		 * Fires after adding content to the import log file.
		 *
		 * This action allows parent plugins to hook into the logging process
		 * and perform custom actions after the log entry has been written.
		 *
		 * @since 1.1.25
		 *
		 * @param string               $message  The log message content.
		 * @param string               $severity The severity level (info, success, warning, error, fatal).
		 * @param array<string, mixed> $context  Additional context data for the log entry.
		 */
		do_action( 'st_importer_log_after_add', $message, $severity, $context );
	}

	/**
	 * Current Time for log.
	 *
	 * @since 1.1.25
	 * @return string Current time with time zone.
	 */
	public static function current_time() {
		return gmdate( 'H:i:s' ) . ' ' . date_default_timezone_get();
	}

	/**
	 * Get Log File
	 *
	 * @since 1.1.25
	 * @return string|null Log file path.
	 */
	public static function get_log_file() {
		return self::$log_file;
	}

	/**
	 * Add log file URL in UI response.
	 *
	 * @since 1.1.25
	 *
	 * @return array<string, mixed> Log file URLs.
	 */
	public static function add_log_file_url() {
		$upload_dir   = self::log_dir();
		$upload_path  = trailingslashit( $upload_dir['url'] );
		$file_abs_url = get_option( self::get_option_name(), self::$log_file );
		$file_url     = $upload_path . basename( (string) $file_abs_url );

		return array(
			'abs_url' => $file_abs_url,
			'url'     => $file_url,
		);
	}

	/**
	 * Import Start
	 *
	 * @since 1.1.25
	 * @param  array<string, string> $data         Import Data.
	 * @param  string                $demo_api_uri Import site API URL.
	 * @return void
	 */
	public function start( $data = array(), $demo_api_uri = '' ) {
		self::add( 'Started Import Process' );

		self::add( '# System Details: ' );
		self::add( "Debug Mode \t\t: " . self::get_debug_mode() );
		self::add( "Operating System \t: " . self::get_os() );
		self::add( "Software \t\t: " . self::get_software() );
		self::add( "MySQL version \t\t: " . self::get_mysql_version() );
		self::add( "XML Reader \t\t: " . self::get_xmlreader_status() );
		self::add( "PHP Version \t\t: " . self::get_php_version() );
		self::add( "PHP Max Input Vars \t: " . self::get_php_max_input_vars() );
		self::add( "PHP Max Post Size \t: " . self::get_php_max_post_size() );
		self::add( "PHP Extension GD \t: " . self::get_php_extension_gd() );
		self::add( "PHP Max Execution Time \t: " . self::get_max_execution_time() );
		self::add( "Max Upload Size \t: " . size_format( wp_max_upload_size() ) );
		self::add( "Memory Limit \t\t: " . self::get_memory_limit() );
		self::add( "Timezone \t\t: " . self::get_timezone() );
		self::add( PHP_EOL . '-----' . PHP_EOL );
		self::add( 'Importing Started! - ' . self::current_time() );

		self::add( '---' . PHP_EOL );

		/**
		 * Filter the help URL displayed in import logs.
		 *
		 * @param string $help_url The URL to help documentation.
		 * @since 1.1.25
		 */
		$help_url = apply_filters( 'st_importer_log_help_url', 'https://wpastra.com/docs/?p=1314&utm_source=demo-import-panel&utm_campaign=import-error&utm_medium=wp-dashboard' );

		self::add( 'WHY IMPORT PROCESS CAN FAIL? READ THIS - ' );
		self::add( $help_url . PHP_EOL );
		self::add( '---' . PHP_EOL );
	}

	/**
	 * Debug Mode
	 *
	 * @since 1.1.25
	 * @return string Enabled for Debug mode ON and Disabled for Debug mode Off.
	 */
	public static function get_debug_mode() {
		if ( WP_DEBUG ) {
			return __( 'Enabled', 'astra-sites' );
		}

		return __( 'Disabled', 'astra-sites' );
	}

	/**
	 * Memory Limit
	 *
	 * @since 1.1.25
	 * @return string Memory limit.
	 */
	public static function get_memory_limit() {
		$required_memory                = '64M';
		$memory_limit_in_bytes_current  = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$memory_limit_in_bytes_required = wp_convert_hr_to_bytes( $required_memory );

		if ( $memory_limit_in_bytes_current < $memory_limit_in_bytes_required ) {
			return sprintf(
				/* translators: %1$s Memory Limit, %2$s Recommended memory limit. */
				_x( 'Current memory limit %1$s. We recommend setting memory to at least %2$s.', 'Recommended Memory Limit', 'astra-sites' ),
				WP_MEMORY_LIMIT,
				$required_memory
			);
		}

		return WP_MEMORY_LIMIT;
	}

	/**
	 * Timezone
	 *
	 * @since 1.1.25
	 * @see https://codex.wordpress.org/Option_Reference/
	 *
	 * @return string Current timezone.
	 */
	public static function get_timezone() {
		$timezone = get_option( 'timezone_string' );

		if ( ! $timezone ) {
			$timezone = get_option( 'gmt_offset' );
		}

		return (string) $timezone;
	}

	/**
	 * Operating System
	 *
	 * @since 1.1.25
	 * @return string Current Operating System.
	 */
	public static function get_os() {
		return PHP_OS;
	}

	/**
	 * Server Software
	 *
	 * @since 1.1.25
	 * @return string Current Server Software.
	 */
	public static function get_software() {
		return isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : '';
	}

	/**
	 * MySql Version
	 *
	 * @since 1.1.25
	 * @return string Current MySql Version.
	 */
	public static function get_mysql_version() {
		global $wpdb;
		return $wpdb->db_version();
	}

	/**
	 * XML Reader
	 *
	 * @since 1.1.25
	 * @return string Current XML Reader status.
	 */
	public static function get_xmlreader_status() {

		if ( class_exists( 'XMLReader' ) ) {
			return __( 'Yes', 'astra-sites' );
		}

		return __( 'No', 'astra-sites' );
	}

	/**
	 * PHP Version
	 *
	 * @since 1.1.25
	 * @return string Current PHP Version.
	 */
	public static function get_php_version() {
		if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
			return _x( 'We recommend to use php 5.4 or higher', 'PHP Version', 'astra-sites' );
		}
		return PHP_VERSION;
	}

	/**
	 * PHP Max Input Vars
	 *
	 * @since 1.1.25
	 * @return string|false Current PHP Max Input Vars
	 */
	public static function get_php_max_input_vars() {
		return ini_get( 'max_input_vars' ); // phpcs:disable PHPCompatibility.IniDirectives.NewIniDirectives.max_input_varsFound
	}

	/**
	 * PHP Max Post Size
	 *
	 * @since 1.1.25
	 * @return string|false Current PHP Max Post Size
	 */
	public static function get_php_max_post_size() {
		return ini_get( 'post_max_size' );
	}

	/**
	 * PHP Max Execution Time
	 *
	 * @since 1.1.25
	 * @return string Current Max Execution Time
	 */
	public static function get_max_execution_time() {
		return ini_get( 'max_execution_time' );
	}

	/**
	 * PHP GD Extension
	 *
	 * @since 1.1.25
	 * @return string Current PHP GD Extension
	 */
	public static function get_php_extension_gd() {
		if ( extension_loaded( 'gd' ) ) {
			return __( 'Yes', 'astra-sites' );
		}

		return __( 'No', 'astra-sites' );
	}

	/**
	 * Display Data
	 *
	 * @since 1.1.25
	 * @return void
	 */
	public function display_data() {

		$crons  = _get_cron_array();
		$events = array();

		if ( empty( $crons ) ) {
			esc_html_e( 'You currently have no scheduled cron events.', 'astra-sites' );
		}

		if ( is_array( $crons ) ) {
			foreach ( $crons as $time => $cron ) {
				$keys           = array_keys( $cron );
				$key            = $keys[0];
				$events[ $key ] = $time;
			}
		}

		$prefix         = self::get_prefix( true );
		$transient_name = $prefix . '-import-check';

		$expired = get_site_transient( $transient_name );
		if ( $expired ) {
			global $wpdb;

			$transient_timeout = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_value
				FROM {$wpdb->options}
				WHERE option_name
				LIKE %s",
					'%_transient_timeout_' . $transient_name . '%'
				)
			);

			$older_date       = ! empty( $transient_timeout ) ? $transient_timeout[0] : time();
			$transient_status = 'Transient: Not Expired! Recheck in ' . human_time_diff( time(), $older_date );
		} else {
			$transient_status = 'Transient: Starting.. Process for each 5 minutes.';
		}

		$batch_option = $prefix . '-batch-status-string';
		$cron_event   = 'wp_astra_site_importer_cron'; // Static across all plugins.

		$temp  = get_site_option( $batch_option, '' );
		$temp .= isset( $events[ $cron_event ] ) ? '<br/>Batch: Recheck batch in ' . human_time_diff( time(), $events[ $cron_event ] ) : '<br/>Batch: Not Started! Until the Transient expire.';

		$upload_dir   = self::log_dir();
		$list_files   = list_files( $upload_dir['path'] );
		$backup_files = array();
		$log_files    = array();

		if ( is_array( $list_files ) ) {
			foreach ( $list_files as $key => $file ) {
				if ( strpos( $file, '.json' ) ) {
					$backup_files[] = $file;
				}
				if ( strpos( $file, '.txt' ) || strpos( $file, '.log' ) ) {
					$log_files[] = $file;
				}
			}
		}
		?>
		<table>
			<tr>
				<td>
					<h2><?php esc_html_e( 'Log Files', 'astra-sites' ); ?></h2>
					<ul>
						<?php
						foreach ( $log_files as $key => $file ) {
							$file_name = basename( $file );
							$file      = str_replace( $upload_dir['path'], $upload_dir['url'], $file );
							?>
							<li>
								<a target="_blank" href="<?php echo esc_url( $file ); ?>"><?php echo esc_html( $file_name ); ?></a>
							</li>
						<?php } ?>
					</ul>
				</td>
				<td>
					<h2><?php esc_html_e( 'Backup Files', 'astra-sites' ); ?></h2>
					<ul>
						<?php
						foreach ( $backup_files as $key => $file ) {
							$file_name = basename( $file );
							$file      = str_replace( $upload_dir['path'], $upload_dir['url'], $file );
							?>
							<li>
								<a target="_blank" href="<?php echo esc_url( $file ); ?>"><?php echo esc_html( $file_name ); ?></a>
							</li>
						<?php } ?>
					</ul>
				</td>
				<td>
					<div class="batch-log">
						<p><?php echo wp_kses_post( $temp ); ?></p>
						<p><?php echo wp_kses_post( $transient_status ); ?></p>
					</div>
				</td>
			</tr>
		</table>
		<?php
	}
}
