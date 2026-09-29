<?php
/**
 * AI Builder Importer Log
 *
 * This class serves as a wrapper around ST_Importer_Log,
 * providing AI Builder-specific customizations through prefix filter.
 *
 * @since 1.1.0
 * @package AI Builder
 */

namespace AiBuilder\Inc\Classes;

use AiBuilder\Inc\Traits\Instance;
use STImporter\Importer\ST_Importer_Log;

/**
 * AI Builder Importer Log
 */
class Ai_Builder_Importer_Log {
	use Instance;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	private function __construct() {
		// Bail out if ST_Importer_Log does not exist.
		if ( ! class_exists( 'STImporter\Importer\ST_Importer_Log' ) ) {
			return;
		}

		// Set prefix for ai-builder.
		add_filter( 'st_importer_log_prefix', array( $this, 'set_plugin_prefix' ) );

		// Initialize the ST_Importer_Log instance.
		ST_Importer_Log::get_instance();
	}

	/**
	 * Set prefix for ai-builder
	 *
	 * @since 1.2.70
	 * @param string $prefix Default prefix.
	 * @return string Modified prefix.
	 */
	public function set_plugin_prefix( $prefix ) {
		return 'ai_builder';
	}

	/**
	 * Add log entry - Wrapper for ST_Importer_Log::add()
	 *
	 * @since 1.1.0
	 * @param string               $content  Content to be saved to the file.
	 * @param string               $severity Severity level: 'info', 'success', 'warning', 'error', 'fatal'. Default 'info'.
	 * @param array<string, mixed> $context  Additional context data for the log entry.
	 * @return void
	 */
	public static function add( $content, $severity = 'info', $context = array() ) {
		// Bail out if ST_Importer_Log does not exist.
		if ( ! class_exists( 'STImporter\Importer\ST_Importer_Log' ) ) {
			return;
		}

		ST_Importer_Log::add( $content, $severity, $context );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Ai_Builder_Importer_Log::Instance();
