<?php
/**
 * Knowledge Graph Init class
 *
 * Handles the initialization and hooks for knowledge graph functionality.
 *
 * @package SureRank\Inc\Modules\Knowledge_Graph
 * @since 1.6.6
 */

namespace SureRank\Inc\Modules\Knowledge_Graph;

use SureRank\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Init class
 *
 * Handles initialization and WordPress hooks for knowledge graph functionality.
 */
class Init {

	use Get_Instance;

	/**
	 * Constructor
	 */
	public function __construct() {
		Controller::get_instance();
		add_filter( 'surerank_api_controllers', [ $this, 'register_api_controller' ], 20 );
	}

	/**
	 * Register API controller for this module.
	 *
	 * @param array<string> $controllers Existing controllers.
	 * @return array<string> Updated controllers.
	 * @since 1.6.6
	 */
	public function register_api_controller( $controllers ) {
		$controllers[] = '\SureRank\Inc\Modules\Knowledge_Graph\Api';
		return $controllers;
	}
}
