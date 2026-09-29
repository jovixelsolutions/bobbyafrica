<?php
/**
 * Trait.
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Traits;

/**
 * Trait Get_Instance.
 */
trait Get_Instance {
	/**
	 * Instance object.
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 0.0.1
	 * @return self initialized object of class.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
