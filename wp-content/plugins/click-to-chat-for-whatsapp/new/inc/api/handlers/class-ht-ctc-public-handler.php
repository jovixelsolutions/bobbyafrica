<?php
/**
 * Public REST API Handler for Click to Chat
 *
 * @package Click_To_Chat
 * @subpackage API\Handlers
 * @since 4.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Public_Handler' ) ) {

	/**
	 * Public REST API handler class.
	 *
	 * Placeholder — no endpoints are currently registered. When implementing,
	 * add methods here and uncomment the matching route definitions in
	 * HT_CTC_Rest_API::register_public_routes().
	 *
	 * Planned endpoints:
	 *   - GET /get_ht_ctc_chat_var    → returns chat widget config
	 *   - GET /get_ht_ctc_variables   → returns runtime variables
	 *
	 * Each handler method should validate the request via
	 * HT_CTC_Security::validate_rest_request() before returning data through
	 * rest_ensure_response(). For prior commented reference code,
	 * see git history of this file.
	 */
	class HT_CTC_Public_Handler {
	}

}
