<?php
/**
 * Cart Abandonment Recovery API Base.
 *
 * @package cart-abandonment-recovery
 */

namespace WCAR\Admin\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ApiBase.
 */
abstract class ApiBase extends \WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wcar/api';

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
	}

	/**
	 * Register API routes.
	 */
	public function get_api_namespace() {

		return $this->namespace;
	}
}
