<?php
/**
 * CartFlows Admin Helper.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\Wizard\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminHelper.
 */
class WizardHelper {

	/**
	 * Determine the active supported page builder.
	 *
	 * This function checks the list of supported page builders and returns the first active one.
	 * If no active page builder is found, it returns false.
	 *
	 * @param array $supported_page_builders List of supported page builders.
	 * @return string|bool The slug of the active page builder or false if none is active.
	 */
	public static function get_active_supported_builder( $supported_page_builders ) {
		foreach ( $supported_page_builders as $key => $builder ) {
			if ( 'yes' === $builder['install'] && 'yes' === $builder['active'] ) {
				return $key;
			} elseif ( 'divi' === $key && self::is_divi_enabled() ) {
				return $key;
			} elseif ( 'bricks-builder' === $key && self::is_bricks_enabled() ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Check if divi builder enabled.
	 *
	 * @param object $theme theme data.
	 * @return boolean
	 */
	public static function is_divi_enabled( $theme = false ) {

		if ( empty( $theme ) ) {
			$theme = wp_get_theme();
		}

		if ( defined( 'ET_BUILDER_THEME' ) || defined( 'ET_BUILDER_PLUGIN_VERSION' ) || 'Divi' == $theme->name || 'Divi' == $theme->parent_theme || 'Extra' == $theme->name || 'Extra' == $theme->parent_theme ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if brick builder enabled.
	 *
	 * @return boolean
	 */
	public static function is_bricks_enabled() {

		$theme = wp_get_theme();
		if ( 'Bricks' == $theme->name || 'Bricks' == $theme->parent_theme ) {
			return true;
		}

		return false;
	}
}

