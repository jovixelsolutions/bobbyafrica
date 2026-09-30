<?php
/**
 * AI Initialization.
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * AI Initialization.
 *
 * @since x.x.x
 */
class Cartflows_Ai_Init {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * Constructor
	 *
	 * @since x.x.x
	 */
	public function __construct() {
		if ( apply_filters( 'cartflows_ai_auth_enabled', true ) ) {
			include_once CARTFLOWS_DIR . 'modules/content-generation/class-cartflows-ai-utils.php';
			include_once CARTFLOWS_DIR . 'modules/content-generation/class-cartflows-ai-auth.php';
			include_once CARTFLOWS_DIR . 'modules/content-generation/class-cartflows-ai-api.php';

			add_action( 'admin_notices', array( $this, 'render_auth_connected_notice' ) );
		}
	}

	/**
	 * Render a one-shot success banner right after the account is connected.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function render_auth_connected_notice() {
		if ( ! get_transient( 'cartflows_auth_connected_notice' ) ) {
			return;
		}

		delete_transient( 'cartflows_auth_connected_notice' );

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html__( 'Your CartFlows account is now connected. Auto Suggest and related Pro features are ready to use.', 'cartflows' )
		);
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Ai_Init::get_instance();
