<?php
/**
 * CartFlows Ability Config.
 *
 * Defines all ability registrations: labels, descriptions, schemas, permissions, and meta.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cartflows_Ability_Config
 *
 * Static registry of all CartFlows ability definitions.
 */
class Cartflows_Ability_Config {

	/**
	 * Cached ability definitions.
	 *
	 * @var array|false
	 */
	public static $abilities = false;

	/**
	 * Get all ability configurations.
	 *
	 * @return array
	 */
	public static function get_abilities() {

		if ( false !== self::$abilities ) {
			return self::$abilities;
		}

		$cf = new Cartflows_Ability_Runtime();

		$abilities = array(

			// ============================================================
			// Flows — CRUD / Read
			// ============================================================

			// List all funnels with pagination, status, and search filters.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'list-flows' => array(
				'label'               => __( 'List funnels', 'cartflows' ),
				'description'         => __( 'Returns a paginated list of CartFlows funnels. Supports filtering by status (publish, draft, trash) and keyword search. Excludes the store checkout flow. Use to browse or discover funnels before taking action on them.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'status'   => array(
							'type'        => 'string',
							'enum'        => array( 'publish', 'draft', 'trash', 'any' ),
							'default'     => 'publish',
							'description' => __( 'Filter funnels by post status.', 'cartflows' ),
						),
						'search'   => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'Keyword to search in funnel titles.', 'cartflows' ),
						),
						'paged'    => array(
							'type'        => 'integer',
							'default'     => 1,
							'description' => __( 'Page number (1-based).', 'cartflows' ),
						),
						'per_page' => array(
							'type'        => 'integer',
							'default'     => 10,
							'description' => __( 'Funnels per page (max 100).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flows'       => array(
							'type'        => 'array',
							'description' => __( 'The funnels.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'             => array(
										'type'        => 'integer',
										'description' => __( 'Funnel ID.', 'cartflows' ),
									),
									'title'          => array(
										'type'        => 'string',
										'description' => __( 'Funnel title.', 'cartflows' ),
									),
									'status'         => array(
										'type'        => 'string',
										'description' => __( 'Post status.', 'cartflows' ),
									),
									'date_created'   => array(
										'type'        => 'string',
										'description' => __( 'Date created.', 'cartflows' ),
									),
									'date_modified'  => array(
										'type'        => 'string',
										'description' => __( 'Date last modified.', 'cartflows' ),
									),
									'url_view'       => array(
										'type'        => 'string',
										'format'      => 'uri',
										'description' => __( 'Frontend URL.', 'cartflows' ),
									),
									'url_edit'       => array(
										'type'        => 'string',
										'format'      => 'uri',
										'description' => __( 'Admin edit URL.', 'cartflows' ),
									),
									'flow_test_mode' => array(
										'type'        => 'boolean',
										'description' => __( 'Whether in test/sandbox mode.', 'cartflows' ),
									),
								),
							),
						),
						'total'       => array(
							'type'        => 'integer',
							'description' => __( 'Total matching funnels.', 'cartflows' ),
						),
						'total_pages' => array(
							'type'        => 'integer',
							'description' => __( 'Total pages.', 'cartflows' ),
						),
						'page'        => array(
							'type'        => 'integer',
							'description' => __( 'Current page.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->list_flows( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://flows',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get a single funnel by ID with full step list and settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-flow'   => array(
				'label'               => __( 'Get funnel', 'cartflows' ),
				'description'         => __( 'Returns full data for a single CartFlows funnel by ID, including its ordered steps, settings, and admin/frontend URLs. Use before editing, cloning, or inspecting a funnel.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'            => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'title'         => array(
							'type'        => 'string',
							'description' => __( 'Funnel title.', 'cartflows' ),
						),
						'slug'          => array(
							'type'        => 'string',
							'description' => __( 'URL slug.', 'cartflows' ),
						),
						'status'        => array(
							'type'        => 'string',
							'description' => __( 'Post status.', 'cartflows' ),
						),
						'url_view'      => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Frontend permalink.', 'cartflows' ),
						),
						'url_edit'      => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL.', 'cartflows' ),
						),
						'steps'         => array(
							'type'        => 'array',
							'description' => __( 'Ordered steps in this funnel.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'          => array(
										'type'        => 'integer',
										'description' => __( 'Step ID.', 'cartflows' ),
									),
									'title'       => array(
										'type'        => 'string',
										'description' => __( 'Step title.', 'cartflows' ),
									),
									'type'        => array(
										'type'        => 'string',
										'description' => __( 'Step type: checkout, thankyou, optin, or landing.', 'cartflows' ),
									),
									'status'      => array(
										'type'        => 'string',
										'description' => __( 'Step post status (publish, draft, trash).', 'cartflows' ),
									),
									'is_disabled' => array(
										'type'        => 'boolean',
										'description' => __( 'Whether the step is disabled.', 'cartflows' ),
									),
									'url'         => array(
										'type'        => 'string',
										'format'      => 'uri',
										'description' => __( 'Step frontend URL.', 'cartflows' ),
									),
								),
							),
						),
						'settings_data' => array(
							'type'        => 'object',
							'description' => __( 'Funnel settings panel data.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_flow( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://flows/{id}',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Steps — CRUD / Read
			// ============================================================

			// Get a single step by ID.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-step'   => array(
				'label'               => __( 'Get step', 'cartflows' ),
				'description'         => __( 'Returns full data for a single CartFlows step by ID, including its type (checkout, thankyou, optin, landing), settings, and page-builder edit link. Use to inspect a step before editing or cloning it.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'               => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'title'            => array(
							'type'        => 'string',
							'description' => __( 'Step title.', 'cartflows' ),
						),
						'type'             => array(
							'type'        => 'string',
							'description' => __( 'Step type: checkout, thankyou, optin, or landing.', 'cartflows' ),
						),
						'flow_id'          => array(
							'type'        => 'integer',
							'description' => __( 'Parent funnel ID.', 'cartflows' ),
						),
						'flow_title'       => array(
							'type'        => 'string',
							'description' => __( 'Parent funnel title.', 'cartflows' ),
						),
						'url_view'         => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Frontend URL.', 'cartflows' ),
						),
						'url_edit'         => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'WP admin edit link.', 'cartflows' ),
						),
						'url_page_builder' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Page builder direct edit link.', 'cartflows' ),
						),
						'settings_data'    => array(
							'type'        => 'object',
							'description' => __( 'Step settings.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_step( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/{id}',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Analytics / Stats
			// ============================================================

			// Get funnel analytics.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-flow-stats' => array(
				'label'               => __( 'Get funnel analytics', 'cartflows' ),
				'description'         => __( 'Returns revenue and order analytics for CartFlows funnels within a date range. Optionally filter by a specific funnel ID. Returns totals, per-date breakdowns, and the 4 most recent orders. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'date_from' => array(
							'type'        => 'string',
							'format'      => 'date',
							'default'     => '',
							'description' => __( 'Start date in YYYY-MM-DD format. Defaults to 30 days ago if empty.', 'cartflows' ),
						),
						'date_to'   => array(
							'type'        => 'string',
							'format'      => 'date',
							'default'     => '',
							'description' => __( 'End date in YYYY-MM-DD format. Defaults to today if empty.', 'cartflows' ),
						),
						'flow_id'   => array(
							'type'        => 'integer',
							'default'     => 0,
							'description' => __( 'Filter by funnel ID. Set to 0 for all funnels.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flow_stats'    => array(
							'type'        => 'object',
							'description' => __( 'Aggregated earnings and order counts for the date range.', 'cartflows' ),
						),
						'recent_orders' => array(
							'type'        => 'array',
							'description' => __( 'The 4 most recent CartFlows orders.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'order_id'       => array(
										'type'        => 'integer',
										'description' => __( 'WooCommerce order ID.', 'cartflows' ),
									),
									'customer_name'  => array(
										'type'        => 'string',
										'description' => __( 'Customer full name.', 'cartflows' ),
									),
									'customer_email' => array(
										'type'        => 'string',
										'format'      => 'email',
										'description' => __( 'Customer email.', 'cartflows' ),
									),
									'order_total'    => array(
										'type'        => 'string',
										'description' => __( 'Formatted order total with currency symbol.', 'cartflows' ),
									),
									'order_status'   => array(
										'type'        => 'string',
										'description' => __( 'Order status.', 'cartflows' ),
									),
									'order_date'     => array(
										'type'        => 'string',
										'description' => __( 'Order date (YYYY-MM-DD).', 'cartflows' ),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_flow_stats( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://analytics/flow-stats',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Flows — Lifecycle
			// ============================================================

			// Publish a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'publish-flow' => array(
				'label'               => __( 'Publish funnel', 'cartflows' ),
				'description'         => __( 'Sets a CartFlows funnel to published (active) status, making it and all its steps visible to visitors. Use to activate a funnel that is ready to go live.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to publish.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'status'  => array(
							'type'        => 'string',
							'description' => __( 'New status (publish).', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->publish_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Unpublish (draft) a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'unpublish-flow' => array(
				'label'               => __( 'Unpublish funnel', 'cartflows' ),
				'description'         => __( 'Sets a CartFlows funnel to draft (inactive) status, hiding it and all its steps from visitors. Use to temporarily disable a funnel without deleting it.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to unpublish.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'status'  => array(
							'type'        => 'string',
							'description' => __( 'New status (draft).', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->unpublish_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Clone a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'clone-flow' => array(
				'label'               => __( 'Clone funnel', 'cartflows' ),
				'description'         => __( 'Creates a full duplicate of a CartFlows funnel, including all its steps and their settings. The new funnel is titled "[Original Title] Clone". Use to create a variation or template-based copy of an existing funnel.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to clone.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'          => array(
							'type'        => 'integer',
							'description' => __( 'New funnel ID.', 'cartflows' ),
						),
						'source_id'   => array(
							'type'        => 'integer',
							'description' => __( 'Original funnel ID.', 'cartflows' ),
						),
						'title'       => array(
							'type'        => 'string',
							'description' => __( 'New funnel title.', 'cartflows' ),
						),
						'url_edit'    => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL for new funnel.', 'cartflows' ),
						),
						'steps_count' => array(
							'type'        => 'integer',
							'description' => __( 'Number of steps cloned.', 'cartflows' ),
						),
						'message'     => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->clone_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => false,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Trash a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'trash-flow' => array(
				'label'               => __( 'Trash funnel', 'cartflows' ),
				'description'         => __( 'Moves a CartFlows funnel and all its steps to the WordPress trash. The funnel can be restored using restore-flow. Prefer this over permanent deletion.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to trash.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'status'  => array(
							'type'        => 'string',
							'description' => __( 'New status (trash).', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->trash_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => true,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'instructions' => 'Always confirm with the user before executing this ability. This moves the funnel and all its steps to trash.',
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Restore a funnel from trash.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'restore-flow' => array(
				'label'               => __( 'Restore funnel from trash', 'cartflows' ),
				'description'         => __( 'Restores a trashed CartFlows funnel and all its steps back to their previous published/draft status. Use to undo a trash-flow operation.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to restore from trash.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'status'  => array(
							'type'        => 'string',
							'description' => __( 'Restored status.', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->restore_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Flows — CRUD / Update
			// ============================================================

			// Update a funnel's title and/or slug.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-flow' => array(
				'label'               => __( 'Update funnel', 'cartflows' ),
				'description'         => __( 'Updates a CartFlows funnel\'s title and optionally its URL slug. Use to rename a funnel or change its permalink.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id', 'title' ),
					'properties' => array(
						'id'    => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID to update.', 'cartflows' ),
						),
						'title' => array(
							'type'        => 'string',
							'description' => __( 'New funnel title.', 'cartflows' ),
						),
						'slug'  => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'New URL slug (optional). Leave empty to keep current slug.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'title'    => array(
							'type'        => 'string',
							'description' => __( 'Updated title.', 'cartflows' ),
						),
						'slug'     => array(
							'type'        => 'string',
							'description' => __( 'Updated slug.', 'cartflows' ),
						),
						'url_edit' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL.', 'cartflows' ),
						),
						'message'  => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Flows — Relationships
			// ============================================================

			// Reorder steps in a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'reorder-flow-steps' => array(
				'label'               => __( 'Reorder funnel steps', 'cartflows' ),
				'description'         => __( 'Changes the display order of steps within a CartFlows funnel. Provide the complete ordered array of step IDs. Call get-flow first to retrieve the current step IDs.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'flow_id', 'step_ids' ),
					'properties' => array(
						'flow_id'  => array(
							'type'        => 'integer',
							'description' => __( 'The funnel ID.', 'cartflows' ),
						),
						'step_ids' => array(
							'type'        => 'array',
							'description' => __( 'Full ordered array of step IDs in the desired order.', 'cartflows' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flow_id' => array(
							'type'        => 'integer',
							'description' => __( 'Funnel ID.', 'cartflows' ),
						),
						'steps'   => array(
							'type'        => 'array',
							'description' => __( 'Steps in updated order.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'    => array(
										'type'        => 'integer',
										'description' => __( 'Step ID.', 'cartflows' ),
									),
									'title' => array(
										'type'        => 'string',
										'description' => __( 'Step title.', 'cartflows' ),
									),
									'type'  => array(
										'type'        => 'string',
										'description' => __( 'Step type.', 'cartflows' ),
									),
								),
							),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->reorder_flow_steps( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Flows — Output / Embedding
			// ============================================================

			// Export one or more funnels as JSON.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'export-flow' => array(
				'label'               => __( 'Export funnel data', 'cartflows' ),
				'description'         => __( 'Exports one or more CartFlows funnels as structured JSON data, including all steps and their settings. Use for backup, migration between sites, or auditing funnel configuration.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'flow_ids' ),
					'properties' => array(
						'flow_ids' => array(
							'type'        => 'array',
							'description' => __( 'Array of funnel IDs to export. At least one required.', 'cartflows' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flows'        => array(
							'type'        => 'array',
							'description' => __( 'Array of funnel export objects (one per funnel ID).', 'cartflows' ),
							'items'       => array(
								'type'        => 'object',
								'description' => __( 'Full funnel export data including all steps and settings.', 'cartflows' ),
							),
						),
						'export_count' => array(
							'type'        => 'integer',
							'description' => __( 'Number of funnels exported.', 'cartflows' ),
						),
						'message'      => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->export_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Steps — Lifecycle
			// ============================================================

			// Clone a step.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'clone-step' => array(
				'label'               => __( 'Clone step', 'cartflows' ),
				'description'         => __( 'Creates a duplicate of a CartFlows step within the same funnel, copying all content and settings. The new step is titled "[Original Title] Clone". Use to create a variation of an existing step for A/B testing or reuse.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'flow_id', 'step_id' ),
					'properties' => array(
						'flow_id' => array(
							'type'        => 'integer',
							'description' => __( 'The parent funnel ID.', 'cartflows' ),
						),
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The step ID to clone.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'        => array(
							'type'        => 'integer',
							'description' => __( 'New step ID.', 'cartflows' ),
						),
						'source_id' => array(
							'type'        => 'integer',
							'description' => __( 'Original step ID.', 'cartflows' ),
						),
						'flow_id'   => array(
							'type'        => 'integer',
							'description' => __( 'Parent funnel ID.', 'cartflows' ),
						),
						'title'     => array(
							'type'        => 'string',
							'description' => __( 'New step title.', 'cartflows' ),
						),
						'type'      => array(
							'type'        => 'string',
							'description' => __( 'Step type.', 'cartflows' ),
						),
						'message'   => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->clone_step( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => false,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Steps — CRUD / Update
			// ============================================================

			// Rename a step.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-step-title' => array(
				'label'               => __( 'Update step title', 'cartflows' ),
				'description'         => __( 'Renames a CartFlows step. Use when setting up a new funnel or correcting step names.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_edit', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'title' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The step ID to rename.', 'cartflows' ),
						),
						'title'   => array(
							'type'        => 'string',
							'description' => __( 'New step title.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'title'   => array(
							'type'        => 'string',
							'description' => __( 'Updated title.', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_step_title( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Admin / Settings — Read
			// ============================================================

			// Get general settings (page builder, checkout, flags).
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-general-settings' => array(
				'label'               => __( 'Get general settings', 'cartflows' ),
				'description'         => __( 'Returns CartFlows general settings: default page builder, global checkout page, and display/override flags such as override_global_checkout and disallow_indexing. Use to inspect the current CartFlows configuration before making changes.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'default_page_builder'     => array(
							'type'        => 'string',
							'description' => __( 'Active page builder slug (e.g. elementor, gutenberg, divi).', 'cartflows' ),
						),
						'global_checkout'          => array(
							'type'        => 'string',
							'description' => __( 'Global checkout flow ID or empty string if not set.', 'cartflows' ),
						),
						'override_global_checkout' => array(
							'type'        => 'string',
							'description' => __( 'enable or disable.', 'cartflows' ),
						),
						'override_store_order_pay' => array(
							'type'        => 'string',
							'description' => __( 'enable or disable.', 'cartflows' ),
						),
						'disallow_indexing'        => array(
							'type'        => 'string',
							'description' => __( 'enable or disable.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_general_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://settings/general',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get the store checkout flow identity and status.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-store-checkout' => array(
				'label'               => __( 'Get store checkout flow', 'cartflows' ),
				'description'         => __( 'Returns the ID and details of the CartFlows flow configured as the global store checkout. Returns is_configured: false if no store checkout is set. Use to identify which funnel handles the WooCommerce checkout replacement.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flow_id'       => array(
							'type'        => 'integer',
							'description' => __( 'The store checkout flow ID, or 0 if not set.', 'cartflows' ),
						),
						'flow_title'    => array(
							'type'        => 'string',
							'description' => __( 'The store checkout flow title.', 'cartflows' ),
						),
						'url_edit'      => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL for the store checkout flow.', 'cartflows' ),
						),
						'is_configured' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether a store checkout flow is configured.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_store_checkout( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://settings/store-checkout',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get permalink (URL slug) settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-permalink-settings' => array(
				'label'               => __( 'Get permalink settings', 'cartflows' ),
				'description'         => __( 'Returns the CartFlows URL slug configuration for flows and steps: the step base slug, the flow base slug, and the permalink structure option. Use before updating permalink settings or auditing URL structure.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'permalink'           => array(
							'type'        => 'string',
							'description' => __( 'Step URL base slug.', 'cartflows' ),
						),
						'permalink_flow_base' => array(
							'type'        => 'string',
							'description' => __( 'Flow URL base slug.', 'cartflows' ),
						),
						'permalink_structure' => array(
							'type'        => 'string',
							'description' => __( 'Permalink structure option (empty for default).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_permalink_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://settings/permalink',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get pixel / analytics integration settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-integration-settings' => array(
				'label'               => __( 'Get integration settings', 'cartflows' ),
				'description'         => __( 'Returns the active pixel and analytics integration configurations for CartFlows: Facebook, Google Analytics, Google Ads, TikTok, Pinterest, and Snapchat. Each group contains the pixel/tracking ID and event toggle settings. Use the integration parameter to fetch a single group or omit it (default: all) to fetch everything at once.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'integration' => array(
							'type'        => 'string',
							'enum'        => array( 'facebook', 'google_analytics', 'google_ads', 'tiktok', 'pinterest', 'snapchat', 'all' ),
							'default'     => 'all',
							'description' => __( 'Which integration group to return. Defaults to all.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'facebook'         => array(
							'type'        => 'object',
							'description' => __( 'Facebook Pixel settings.', 'cartflows' ),
						),
						'google_analytics' => array(
							'type'        => 'object',
							'description' => __( 'Google Analytics (GA4) settings.', 'cartflows' ),
						),
						'google_ads'       => array(
							'type'        => 'object',
							'description' => __( 'Google Ads conversion settings.', 'cartflows' ),
						),
						'tiktok'           => array(
							'type'        => 'object',
							'description' => __( 'TikTok Pixel settings.', 'cartflows' ),
						),
						'pinterest'        => array(
							'type'        => 'object',
							'description' => __( 'Pinterest Tag settings.', 'cartflows' ),
						),
						'snapchat'         => array(
							'type'        => 'object',
							'description' => __( 'Snapchat Pixel settings.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_integration_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://settings/integrations',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Admin / Settings — Write
			// ============================================================

			// Update general settings (page builder, checkout, flags).
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-general-settings' => array(
				'label'               => __( 'Update general settings', 'cartflows' ),
				'description'         => __( 'Updates CartFlows general settings: default page builder, global checkout page, override_global_checkout flag, and disallow_indexing flag. Only fields that are provided are updated; omitted fields retain their current values. Use get-general-settings first to inspect current values.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'default_page_builder'     => array(
							'type'        => 'string',
							'enum'        => array( 'elementor', 'gutenberg', 'divi', 'beaver-builder', 'bricks-builder' ),
							'description' => __( 'The page builder to set as default.', 'cartflows' ),
						),
						'global_checkout'          => array(
							'type'        => 'string',
							'description' => __( 'Post ID of the global checkout flow, or empty string to unset.', 'cartflows' ),
						),
						'override_global_checkout' => array(
							'type'        => 'string',
							'enum'        => array( 'enable', 'disable' ),
							'description' => __( 'Whether to override the WooCommerce checkout with the CartFlows global checkout.', 'cartflows' ),
						),
						'disallow_indexing'        => array(
							'type'        => 'string',
							'enum'        => array( 'enable', 'disable' ),
							'description' => __( 'Whether to block search engine indexing of CartFlows steps.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'message'  => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
						'settings' => array(
							'type'        => 'object',
							'description' => __( 'Updated settings values (full merged state).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_general_settings( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update permalink (URL slug) settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-permalink-settings' => array(
				'label'               => __( 'Update permalink settings', 'cartflows' ),
				'description'         => __( 'Updates CartFlows URL slug settings for flows and steps. Forces a permalink flush after saving so changes take effect immediately. Use get-permalink-settings first to inspect current values before updating.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'permalink'           => array(
							'type'        => 'string',
							'description' => __( 'Step URL base slug.', 'cartflows' ),
						),
						'permalink_flow_base' => array(
							'type'        => 'string',
							'description' => __( 'Flow URL base slug.', 'cartflows' ),
						),
						'permalink_structure' => array(
							'type'        => 'string',
							'description' => __( 'Permalink structure option (empty string for default).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'message'             => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
						'permalink'           => array(
							'type'        => 'string',
							'description' => __( 'Saved step URL base slug.', 'cartflows' ),
						),
						'permalink_flow_base' => array(
							'type'        => 'string',
							'description' => __( 'Saved flow URL base slug.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_permalink_settings( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Checkout — Configuration / Read
			// ============================================================

			// Get checkout step settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-settings' => array(
				'label'               => __( 'Get checkout step settings', 'cartflows' ),
				'description'         => __( 'Returns the full configuration of a checkout step: layout, products assigned, form field toggles, place order button settings, and design options. Requires WooCommerce. Use to inspect or audit a checkout step before making changes.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'           => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'layout'            => array(
							'type'        => 'string',
							'description' => __( 'Checkout skin/layout.', 'cartflows' ),
						),
						'primary_color'     => array(
							'type'        => 'string',
							'description' => __( 'Primary color hex.', 'cartflows' ),
						),
						'font_family'       => array(
							'type'        => 'string',
							'description' => __( 'Base font family.', 'cartflows' ),
						),
						'products'          => array(
							'type'        => 'array',
							'description' => __( 'Assigned products.', 'cartflows' ),
							'items'       => array( 'type' => 'object' ),
						),
						'form_settings'     => array(
							'type'        => 'object',
							'description' => __( 'Form-level toggles (coupon, shipping, additional fields).', 'cartflows' ),
						),
						'button_settings'   => array(
							'type'        => 'object',
							'description' => __( 'Place order button configuration.', 'cartflows' ),
						),
						'advanced_settings' => array(
							'type'        => 'object',
							'description' => __( 'Product images, cart editing toggles.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_checkout_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/checkout/{id}/settings',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get checkout products.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-products' => array(
				'label'               => __( 'Get checkout products', 'cartflows' ),
				'description'         => __( 'Returns the list of WooCommerce products assigned to a checkout step, including product ID, name, quantity, discount type/value, and thumbnail URL. Requires WooCommerce. Use to inspect what products will be added to cart when a customer visits this checkout.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'products' => array(
							'type'        => 'array',
							'description' => __( 'Assigned products.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'product_id'     => array(
										'type'        => 'integer',
										'description' => __( 'WooCommerce product ID.', 'cartflows' ),
									),
									'name'           => array(
										'type'        => 'string',
										'description' => __( 'Product name.', 'cartflows' ),
									),
									'quantity'       => array(
										'type'        => 'integer',
										'description' => __( 'Quantity.', 'cartflows' ),
									),
									'discount_type'  => array(
										'type'        => 'string',
										'description' => __( 'Discount type (e.g. discount_percent, discount_price).', 'cartflows' ),
									),
									'discount_value' => array(
										'type'        => 'string',
										'description' => __( 'Discount amount.', 'cartflows' ),
									),
									'add_to_cart'    => array(
										'type'        => 'string',
										'description' => __( 'Whether added to cart (yes/no).', 'cartflows' ),
									),
									'img_url'        => array(
										'type'        => 'string',
										'description' => __( 'Product thumbnail URL.', 'cartflows' ),
									),
									'regular_price'  => array(
										'type'        => 'string',
										'description' => __( 'Regular price.', 'cartflows' ),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_checkout_products( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/checkout/{id}/products',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Get checkout form fields.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-fields' => array(
				'label'               => __( 'Get checkout form fields', 'cartflows' ),
				'description'         => __( 'Returns the billing and shipping field configuration for a checkout step, including field order, labels, enabled/required/optimized states, and widths. Also returns form-level toggles (coupon, additional fields, shipping). Requires WooCommerce. Use to inspect field layout before making changes.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'    => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
						'field_type' => array(
							'type'        => 'string',
							'enum'        => array( 'billing', 'shipping', 'all' ),
							'default'     => 'all',
							'description' => __( 'Which field group to return. Defaults to all.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'         => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'billing_fields'  => array(
							'type'        => 'object',
							'description' => __( 'Billing field configuration keyed by field name.', 'cartflows' ),
						),
						'shipping_fields' => array(
							'type'        => 'object',
							'description' => __( 'Shipping field configuration keyed by field name.', 'cartflows' ),
						),
						'form_settings'   => array(
							'type'        => 'object',
							'description' => __( 'Form-level toggles.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_checkout_fields( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/checkout/{id}/fields',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Checkout — Configuration / Write
			// ============================================================

			// Update checkout products.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-products' => array(
				'label'               => __( 'Update checkout products', 'cartflows' ),
				'description'         => __( 'Sets or replaces the products assigned to a checkout step. Each product entry specifies a WooCommerce product ID, quantity, and optional discount. Requires WooCommerce. Use to configure which products are pre-loaded into the cart when a customer visits the checkout.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'products' ),
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
						'products' => array(
							'type'        => 'array',
							'description' => __( 'Array of product configurations.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'product'        => array(
										'type'        => 'integer',
										'description' => __( 'WooCommerce product ID.', 'cartflows' ),
									),
									'quantity'       => array(
										'type'        => 'integer',
										'description' => __( 'Quantity. Defaults to 1.', 'cartflows' ),
									),
									'discount_type'  => array(
										'type'        => 'string',
										'enum'        => array( '', 'discount_percent', 'discount_price', 'coupon' ),
										'default'     => '',
										'description' => __( 'Type of discount to apply.', 'cartflows' ),
									),
									'discount_value' => array(
										'type'        => 'string',
										'default'     => '',
										'description' => __( 'Discount amount.', 'cartflows' ),
									),
									'add_to_cart'    => array(
										'type'        => 'string',
										'enum'        => array( 'yes', 'no' ),
										'default'     => 'yes',
										'description' => __( 'Whether to add this product to cart.', 'cartflows' ),
									),
								),
							),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'products' => array(
							'type'        => 'array',
							'description' => __( 'Saved product list.', 'cartflows' ),
						),
						'message'  => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_checkout_products( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update checkout layout.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-layout' => array(
				'label'               => __( 'Update checkout layout', 'cartflows' ),
				'description'         => __( 'Changes the checkout skin/layout for a step. Available Free layouts: modern-checkout, modern-one-column, one-column, two-column. Pro-only layouts (two-step, multistep-checkout) are not available through this ability. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'layout' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
						'layout'  => array(
							'type'        => 'string',
							'enum'        => array( 'modern-checkout', 'modern-one-column', 'one-column', 'two-column' ),
							'description' => __( 'Checkout skin to apply.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'layout'  => array(
							'type'        => 'string',
							'description' => __( 'Applied layout.', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_checkout_layout( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update place order button.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-place-order-button' => array(
				'label'               => __( 'Update place order button', 'cartflows' ),
				'description'         => __( 'Configures the checkout Place Order button: text, lock icon toggle, and price display toggle. Only provided fields are updated; omitted fields retain their current values. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'        => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
						'button_text'    => array(
							'type'        => 'string',
							'description' => __( 'Place order button text.', 'cartflows' ),
						),
						'show_lock_icon' => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show lock icon on button.', 'cartflows' ),
						),
						'show_price'     => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show cart total on button.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'        => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'button_text'    => array(
							'type'        => 'string',
							'description' => __( 'Button text.', 'cartflows' ),
						),
						'show_lock_icon' => array(
							'type'        => 'string',
							'description' => __( 'Lock icon toggle.', 'cartflows' ),
						),
						'show_price'     => array(
							'type'        => 'string',
							'description' => __( 'Price display toggle.', 'cartflows' ),
						),
						'message'        => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_checkout_place_order_button( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update checkout form settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-form-settings' => array(
				'label'               => __( 'Update checkout form settings', 'cartflows' ),
				'description'         => __( 'Toggles form-level settings on a checkout step: coupon field, collapsible coupon, additional fields, collapsible order note, ship-to-different-address, Google address autocomplete, product images in order review, and cart editing. Only provided fields are updated. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'                   => array(
							'type'        => 'integer',
							'description' => __( 'The checkout step ID.', 'cartflows' ),
						),
						'show_coupon_field'         => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show coupon field.', 'cartflows' ),
						),
						'optimize_coupon_field'     => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Make coupon field collapsible.', 'cartflows' ),
						),
						'show_additional_fields'    => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show additional (order notes) field.', 'cartflows' ),
						),
						'optimize_order_note'       => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Make order note field collapsible.', 'cartflows' ),
						),
						'ship_to_different_address' => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Enable ship to different address.', 'cartflows' ),
						),
						'google_autoaddress'        => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Enable Google address autocomplete.', 'cartflows' ),
						),
						'show_product_images'       => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show product images in order review.', 'cartflows' ),
						),
						'enable_cart_editing'       => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Allow removing products from checkout.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'settings' => array(
							'type'        => 'object',
							'description' => __( 'Updated form settings.', 'cartflows' ),
						),
						'message'  => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_checkout_form_settings( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Thank You — Configuration / Read
			// ============================================================

			// Get thank you step settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-thankyou-settings' => array(
				'label'               => __( 'Get thank you step settings', 'cartflows' ),
				'description'         => __( 'Returns the full configuration of a thank you step: layout skin, section visibility toggles (order overview, order details, billing, shipping), custom thank you text, redirect settings, and design options. Requires WooCommerce. Use to inspect or audit a thank you step before making changes.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The thank you step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'     => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'layout'      => array(
							'type'        => 'string',
							'description' => __( 'Thank you skin (legacy-tq-layout or modern-tq-layout).', 'cartflows' ),
						),
						'sections'    => array(
							'type'        => 'object',
							'description' => __( 'Section visibility toggles (show_overview, show_details, show_billing, show_shipping).', 'cartflows' ),
						),
						'custom_text' => array(
							'type'        => 'string',
							'description' => __( 'Custom thank you page message text.', 'cartflows' ),
						),
						'redirect'    => array(
							'type'        => 'object',
							'description' => __( 'Redirect settings (enabled, redirect_url).', 'cartflows' ),
						),
						'design'      => array(
							'type'        => 'object',
							'description' => __( 'Design settings (colors, fonts, container width).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_thankyou_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/thankyou/{id}/settings',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Thank You — Configuration / Write
			// ============================================================

			// Update thank you layout.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-layout' => array(
				'label'               => __( 'Update thank you layout', 'cartflows' ),
				'description'         => __( 'Changes the thank you page skin/layout for a step. Available layouts: legacy-tq-layout (classic table-based) or modern-tq-layout (styled modern design). Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'layout' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The thank you step ID.', 'cartflows' ),
						),
						'layout'  => array(
							'type'        => 'string',
							'enum'        => array( 'legacy-tq-layout', 'modern-tq-layout' ),
							'description' => __( 'Thank you skin to apply.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'layout'  => array(
							'type'        => 'string',
							'description' => __( 'Applied layout.', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_thankyou_layout( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update thank you section visibility.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-sections' => array(
				'label'               => __( 'Update thank you sections', 'cartflows' ),
				'description'         => __( 'Toggles visibility of order sections on a thank you step: order overview, order details, billing details, and shipping details. Only provided fields are updated; omitted fields retain their current values. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'       => array(
							'type'        => 'integer',
							'description' => __( 'The thank you step ID.', 'cartflows' ),
						),
						'show_overview' => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show order overview section.', 'cartflows' ),
						),
						'show_details'  => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show order details section.', 'cartflows' ),
						),
						'show_billing'  => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show billing details section.', 'cartflows' ),
						),
						'show_shipping' => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Show shipping details section.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'sections' => array(
							'type'        => 'object',
							'description' => __( 'Updated section visibility state.', 'cartflows' ),
						),
						'message'  => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_thankyou_sections( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update thank you redirect settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-redirect' => array(
				'label'               => __( 'Update thank you redirect', 'cartflows' ),
				'description'         => __( 'Configures post-purchase redirect on a thank you step. When enabled, visitors are redirected to the specified URL instead of seeing the thank you page. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'      => array(
							'type'        => 'integer',
							'description' => __( 'The thank you step ID.', 'cartflows' ),
						),
						'enabled'      => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Enable or disable redirect after purchase.', 'cartflows' ),
						),
						'redirect_url' => array(
							'type'        => 'string',
							'description' => __( 'The URL to redirect to after purchase.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'      => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'enabled'      => array(
							'type'        => 'string',
							'description' => __( 'Redirect enabled state.', 'cartflows' ),
						),
						'redirect_url' => array(
							'type'        => 'string',
							'description' => __( 'Redirect URL.', 'cartflows' ),
						),
						'message'      => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_thankyou_redirect( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update thank you custom text.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-custom-text' => array(
				'label'               => __( 'Update thank you custom text', 'cartflows' ),
				'description'         => __( 'Sets the custom message text displayed on a thank you page. This replaces the default WooCommerce "Thank you. Your order has been received." message. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'text' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The thank you step ID.', 'cartflows' ),
						),
						'text'    => array(
							'type'        => 'string',
							'description' => __( 'Custom thank you page message text. Supports shortcodes.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'text'    => array(
							'type'        => 'string',
							'description' => __( 'Saved custom text.', 'cartflows' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_thankyou_custom_text( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Optin — Configuration / Read
			// ============================================================

			// Get optin step settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-optin-settings' => array(
				'label'               => __( 'Get optin step settings', 'cartflows' ),
				'description'         => __( 'Returns the full configuration of an optin step: assigned product, submit button text, pass-fields settings, and billing field list. Requires WooCommerce. Use to inspect or audit an optin step before making changes.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The optin step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'     => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'product'     => array(
							'type'        => 'object',
							'description' => __( 'Assigned product (id, name). Must be simple, virtual, and free.', 'cartflows' ),
						),
						'button_text' => array(
							'type'        => 'string',
							'description' => __( 'Submit button text.', 'cartflows' ),
						),
						'pass_fields' => array(
							'type'        => 'object',
							'description' => __( 'URL parameter passing settings (enabled, specific_fields).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_optin_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/optin/{id}/settings',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Optin — Configuration / Write
			// ============================================================

			// Update optin product.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-product' => array(
				'label'               => __( 'Update optin product', 'cartflows' ),
				'description'         => __( 'Sets the WooCommerce product assigned to an optin step. The product must be a Simple, Virtual, and Free (price = 0) product. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'product_id' ),
					'properties' => array(
						'step_id'    => array(
							'type'        => 'integer',
							'description' => __( 'The optin step ID.', 'cartflows' ),
						),
						'product_id' => array(
							'type'        => 'integer',
							'description' => __( 'WooCommerce product ID. Must be a simple, virtual, and free product.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'      => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'product_id'   => array(
							'type'        => 'integer',
							'description' => __( 'Assigned product ID.', 'cartflows' ),
						),
						'product_name' => array(
							'type'        => 'string',
							'description' => __( 'Assigned product name.', 'cartflows' ),
						),
						'message'      => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_optin_product( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update optin button text.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-button-text' => array(
				'label'               => __( 'Update optin button text', 'cartflows' ),
				'description'         => __( 'Changes the submit button text on an optin form. Use to customize the call-to-action, for example "Get Started" or "Subscribe Now". Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id', 'button_text' ),
					'properties' => array(
						'step_id'     => array(
							'type'        => 'integer',
							'description' => __( 'The optin step ID.', 'cartflows' ),
						),
						'button_text' => array(
							'type'        => 'string',
							'description' => __( 'New submit button text.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'     => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'button_text' => array(
							'type'        => 'string',
							'description' => __( 'Saved button text.', 'cartflows' ),
						),
						'message'     => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_optin_button_text( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Update optin pass fields settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-pass-fields' => array(
				'label'               => __( 'Update optin pass fields', 'cartflows' ),
				'description'         => __( 'Configures whether optin form fields are passed as URL query parameters to the next funnel step. When enabled, specify which fields to pass (e.g. first_name, last_name, email). Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id'         => array(
							'type'        => 'integer',
							'description' => __( 'The optin step ID.', 'cartflows' ),
						),
						'enabled'         => array(
							'type'        => 'string',
							'enum'        => array( 'yes', 'no' ),
							'description' => __( 'Enable or disable passing fields as URL parameters.', 'cartflows' ),
						),
						'specific_fields' => array(
							'type'        => 'string',
							'description' => __( 'Comma-separated field names to pass (e.g. first_name, last_name).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'         => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'enabled'         => array(
							'type'        => 'string',
							'description' => __( 'Pass fields enabled state.', 'cartflows' ),
						),
						'specific_fields' => array(
							'type'        => 'string',
							'description' => __( 'Comma-separated field names.', 'cartflows' ),
						),
						'message'         => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_optin_pass_fields( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Landing — Configuration / Read
			// ============================================================

			// Get landing step settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-landing-settings' => array(
				'label'               => __( 'Get landing step settings', 'cartflows' ),
				'description'         => __( 'Returns the configuration of a landing step: step slug, next step link shortcode, and disable-step toggle. Landing pages are page-builder-driven, so this returns only the CartFlows-specific meta. Does not require WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'step_id' ),
					'properties' => array(
						'step_id' => array(
							'type'        => 'integer',
							'description' => __( 'The landing step ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'        => array(
							'type'        => 'integer',
							'description' => __( 'Step ID.', 'cartflows' ),
						),
						'slug'           => array(
							'type'        => 'string',
							'description' => __( 'Step URL slug.', 'cartflows' ),
						),
						'next_step_link' => array(
							'type'        => 'string',
							'description' => __( 'Next step link shortcode/URL for use in page builder.', 'cartflows' ),
						),
						'disable_step'   => array(
							'type'        => 'string',
							'description' => __( 'Whether step is disabled (yes/no).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_landing_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://steps/landing/{id}/settings',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Email Report — Configuration
			// ============================================================

			// Get email report settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-email-report-settings' => array(
				'label'               => __( 'Get email report settings', 'cartflows' ),
				'description'         => __( 'Returns the weekly email report configuration: whether reports are enabled, the list of recipient email addresses, and the next scheduled send time. Does not require WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'enabled'        => array(
							'type'        => 'string',
							'description' => __( 'Whether weekly reports are enabled (enable/disable).', 'cartflows' ),
						),
						'email_ids'      => array(
							'type'        => 'array',
							'description' => __( 'List of recipient email addresses.', 'cartflows' ),
							'items'       => array( 'type' => 'string' ),
						),
						'next_scheduled' => array(
							'type'        => 'string',
							'description' => __( 'Next scheduled send time (ISO 8601) or empty if not scheduled.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_email_report_settings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://settings/email-report',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Update email report settings.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-email-report-settings' => array(
				'label'               => __( 'Update email report settings', 'cartflows' ),
				'description'         => __( 'Toggles the weekly email report on or off and/or updates the list of recipient email addresses. The ActionScheduler cron is automatically managed by the module. Does not require WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'enabled'   => array(
							'type'        => 'string',
							'enum'        => array( 'enable', 'disable' ),
							'description' => __( 'Enable or disable the weekly email report.', 'cartflows' ),
						),
						'email_ids' => array(
							'type'        => 'array',
							'description' => __( 'List of recipient email addresses. Replaces the entire list.', 'cartflows' ),
							'items'       => array( 'type' => 'string' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'enabled'   => array(
							'type'        => 'string',
							'description' => __( 'Saved enabled status.', 'cartflows' ),
						),
						'email_ids' => array(
							'type'        => 'array',
							'description' => __( 'Saved recipient email list.', 'cartflows' ),
							'items'       => array( 'type' => 'string' ),
						),
						'message'   => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_email_report_settings( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Woo Dynamic Flow — Product-to-Flow Mapping
			// ============================================================

			// Get flow mapping for a single WooCommerce product.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'get-product-flow-mapping' => array(
				'label'               => __( 'Get product flow mapping', 'cartflows' ),
				'description'         => __( 'Returns the CartFlows flow mapping for a WooCommerce product: which flow the product redirects to after add-to-cart, and any custom button text override. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'product_id' ),
					'properties' => array(
						'product_id' => array(
							'type'        => 'integer',
							'description' => __( 'The WooCommerce product ID.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'product_id'       => array(
							'type'        => 'integer',
							'description' => __( 'Product ID.', 'cartflows' ),
						),
						'product_title'    => array(
							'type'        => 'string',
							'description' => __( 'Product title.', 'cartflows' ),
						),
						'flow_id'          => array(
							'type'        => 'integer',
							'description' => __( 'Mapped flow ID (0 if none).', 'cartflows' ),
						),
						'flow_title'       => array(
							'type'        => 'string',
							'description' => __( 'Mapped flow title (empty if none).', 'cartflows' ),
						),
						'add_to_cart_text' => array(
							'type'        => 'string',
							'description' => __( 'Custom add-to-cart button text (empty if default).', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->get_product_flow_mapping( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://woo-dynamic-flow/mapping/{product_id}',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// List all WooCommerce products that have a flow mapping.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'list-product-flow-mappings' => array(
				'label'               => __( 'List product flow mappings', 'cartflows' ),
				'description'         => __( 'Returns a paginated list of WooCommerce products that have a CartFlows flow mapping configured. Each entry includes the product, mapped flow, and custom button text. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'paged'    => array(
							'type'        => 'integer',
							'default'     => 1,
							'description' => __( 'Page number (1-based).', 'cartflows' ),
						),
						'per_page' => array(
							'type'        => 'integer',
							'default'     => 10,
							'description' => __( 'Products per page (max 100).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'mappings'    => array(
							'type'        => 'array',
							'description' => __( 'Products with flow mappings.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'product_id'       => array(
										'type'        => 'integer',
										'description' => __( 'Product ID.', 'cartflows' ),
									),
									'product_title'    => array(
										'type'        => 'string',
										'description' => __( 'Product title.', 'cartflows' ),
									),
									'flow_id'          => array(
										'type'        => 'integer',
										'description' => __( 'Mapped flow ID.', 'cartflows' ),
									),
									'flow_title'       => array(
										'type'        => 'string',
										'description' => __( 'Mapped flow title.', 'cartflows' ),
									),
									'add_to_cart_text' => array(
										'type'        => 'string',
										'description' => __( 'Custom button text.', 'cartflows' ),
									),
								),
							),
						),
						'total'       => array(
							'type'        => 'integer',
							'description' => __( 'Total products with mappings.', 'cartflows' ),
						),
						'total_pages' => array(
							'type'        => 'integer',
							'description' => __( 'Total pages.', 'cartflows' ),
						),
						'page'        => array(
							'type'        => 'integer',
							'description' => __( 'Current page.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->list_product_flow_mappings( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://woo-dynamic-flow/mappings',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// Update flow mapping on a WooCommerce product.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'update-product-flow-mapping' => array(
				'label'               => __( 'Update product flow mapping', 'cartflows' ),
				'description'         => __( 'Sets or clears the CartFlows flow mapping on a WooCommerce product. When a flow is mapped, adding the product to cart redirects the customer to that flow. Set flow_id to 0 to clear the mapping. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'product_id' ),
					'properties' => array(
						'product_id'       => array(
							'type'        => 'integer',
							'description' => __( 'The WooCommerce product ID.', 'cartflows' ),
						),
						'flow_id'          => array(
							'type'        => 'integer',
							'description' => __( 'Flow ID to map (0 to clear mapping).', 'cartflows' ),
						),
						'add_to_cart_text' => array(
							'type'        => 'string',
							'description' => __( 'Custom add-to-cart button text (empty string to clear).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'product_id'       => array(
							'type'        => 'integer',
							'description' => __( 'Product ID.', 'cartflows' ),
						),
						'flow_id'          => array(
							'type'        => 'integer',
							'description' => __( 'Saved flow ID.', 'cartflows' ),
						),
						'flow_title'       => array(
							'type'        => 'string',
							'description' => __( 'Saved flow title.', 'cartflows' ),
						),
						'add_to_cart_text' => array(
							'type'        => 'string',
							'description' => __( 'Saved button text.', 'cartflows' ),
						),
						'message'          => array(
							'type'        => 'string',
							'description' => __( 'Result message.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->update_product_flow_mapping( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Flows — Creation / Import
			// ============================================================

			// Create a new empty funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'create-flow' => array(
				'label'               => __( 'Create funnel', 'cartflows' ),
				'description'         => __( 'Creates a new empty CartFlows funnel with the given title and optional status. The funnel is created with no steps — use create-step to add steps after creation. Use to start building a funnel from scratch.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'title' ),
					'properties' => array(
						'title'  => array(
							'type'        => 'string',
							'description' => __( 'The funnel title.', 'cartflows' ),
						),
						'status' => array(
							'type'        => 'string',
							'enum'        => array( 'draft', 'publish' ),
							'default'     => 'draft',
							'description' => __( 'Post status for the new funnel.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flow_id'  => array(
							'type'        => 'integer',
							'description' => __( 'New funnel ID.', 'cartflows' ),
						),
						'title'    => array(
							'type'        => 'string',
							'description' => __( 'Funnel title.', 'cartflows' ),
						),
						'status'   => array(
							'type'        => 'string',
							'description' => __( 'Funnel post status.', 'cartflows' ),
						),
						'edit_url' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL for the new funnel.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->create_flow( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => false,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// Create a new step and attach it to a funnel.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'create-step' => array(
				'label'               => __( 'Create step', 'cartflows' ),
				'description'         => __( 'Creates a new CartFlows step and attaches it to an existing funnel. Free step types: landing, checkout, optin, thankyou. Upsell and downsell steps require CartFlows Pro. The step is published immediately and appended to the funnel\'s step list.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'flow_id', 'title', 'type' ),
					'properties' => array(
						'flow_id' => array(
							'type'        => 'integer',
							'description' => __( 'The parent funnel ID.', 'cartflows' ),
						),
						'title'   => array(
							'type'        => 'string',
							'description' => __( 'The step title.', 'cartflows' ),
						),
						'type'    => array(
							'type'        => 'string',
							'enum'        => array( 'landing', 'checkout', 'optin', 'thankyou' ),
							'description' => __( 'Step type. Free types: landing, checkout, optin, thankyou.', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'step_id'  => array(
							'type'        => 'integer',
							'description' => __( 'New step ID.', 'cartflows' ),
						),
						'flow_id'  => array(
							'type'        => 'integer',
							'description' => __( 'Parent funnel ID.', 'cartflows' ),
						),
						'title'    => array(
							'type'        => 'string',
							'description' => __( 'Step title.', 'cartflows' ),
						),
						'type'     => array(
							'type'        => 'string',
							'description' => __( 'Step type.', 'cartflows' ),
						),
						'edit_url' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Admin edit URL for the new step.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->create_step( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => false,
						'openWorldHint'   => false,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

			// ============================================================
			// Templates — Browse
			// ============================================================

			// List available flow templates from the cached library.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'list-flow-templates' => array(
				'label'               => __( 'List flow templates', 'cartflows' ),
				'description'         => __( 'Returns a paginated list of CartFlows flow templates from the cached template library. Supports filtering by type (free/pro), page builder, step type, category, and keyword search. Use to browse available templates before importing one with import-flow-template. Pro templates require CartFlows Pro to import.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return $cf->permission_callback( 'cartflows_manage_flows_steps' );
				},
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'type'         => array(
							'type'        => 'string',
							'enum'        => array( 'free', 'pro', '' ),
							'default'     => '',
							'description' => __( 'Filter by template type. "free" for free templates, "pro" for pro-only templates. Empty returns all.', 'cartflows' ),
						),
						'page_builder' => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'Filter by page builder slug (e.g. elementor, gutenberg, divi, beaver-builder, bricks-builder). Empty returns templates for the default page builder.', 'cartflows' ),
						),
						'step_type'    => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'Filter by step type (e.g. checkout, optin, landing, thankyou, upsell, downsell). Empty returns all types.', 'cartflows' ),
						),
						'category'     => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'Filter by category slug (e.g. sales-funnel, product-landing, lead-generation, store-checkout). Empty returns all categories.', 'cartflows' ),
						),
						'search'       => array(
							'type'        => 'string',
							'default'     => '',
							'description' => __( 'Keyword to search in template titles.', 'cartflows' ),
						),
						'page'         => array(
							'type'        => 'integer',
							'default'     => 1,
							'description' => __( 'Page number (1-based).', 'cartflows' ),
						),
						'per_page'     => array(
							'type'        => 'integer',
							'default'     => 20,
							'description' => __( 'Templates per page (max 100).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'templates'     => array(
							'type'        => 'array',
							'description' => __( 'Flow templates.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'            => array(
										'type'        => 'integer',
										'description' => __( 'Template ID.', 'cartflows' ),
									),
									'title'         => array(
										'type'        => 'string',
										'description' => __( 'Template title.', 'cartflows' ),
									),
									'type'          => array(
										'type'        => 'string',
										'description' => __( 'Template type: "free" or "pro".', 'cartflows' ),
									),
									'page_builder'  => array(
										'type'        => 'string',
										'description' => __( 'Page builder slug.', 'cartflows' ),
									),
									'is_pro'        => array(
										'type'        => 'boolean',
										'description' => __( 'Whether this template requires CartFlows Pro.', 'cartflows' ),
									),
									'categories'    => array(
										'type'        => 'array',
										'description' => __( 'Category slugs for this template.', 'cartflows' ),
										'items'       => array(
											'type' => 'string',
										),
									),
									'step_count'    => array(
										'type'        => 'integer',
										'description' => __( 'Number of steps in this template.', 'cartflows' ),
									),
									'step_types'    => array(
										'type'        => 'array',
										'description' => __( 'Step types present in this template.', 'cartflows' ),
										'items'       => array(
											'type' => 'string',
										),
									),
									'thumbnail_url' => array(
										'type'        => 'string',
										'format'      => 'uri',
										'description' => __( 'Template thumbnail URL.', 'cartflows' ),
									),
								),
							),
						),
						'total'         => array(
							'type'        => 'integer',
							'description' => __( 'Total matching templates.', 'cartflows' ),
						),
						'page'          => array(
							'type'        => 'integer',
							'description' => __( 'Current page.', 'cartflows' ),
						),
						'per_page'      => array(
							'type'        => 'integer',
							'description' => __( 'Templates per page.', 'cartflows' ),
						),
						'pro_available' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether CartFlows Pro is active on this site. Pro templates can only be imported when this is true.', 'cartflows' ),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->list_flow_templates( $input );
				},
				'meta'                => array(
					'uri'          => 'cartflows://templates',
					'annotations'  => array(
						'priority'        => 1.0,
						'readOnlyHint'    => true,
						'destructiveHint' => false,
						'idempotentHint'  => true,
						'openWorldHint'   => true,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			),

			// ============================================================
			// Templates — Import
			// ============================================================

			// Import a flow template from the CartFlows cloud library.
			CARTFLOWS_ABILITY_API_NAMESPACE . 'import-flow-template' => array(
				'label'               => __( 'Import flow template', 'cartflows' ),
				'description'         => __( 'Imports a flow template from the CartFlows cloud library by template ID. Creates a new funnel with all steps from the template. Upsell/downsell steps are skipped if CartFlows Pro is not active. Use list-flow-templates first to browse available templates. Requires WooCommerce.', 'cartflows' ),
				'category'            => 'cartflows',
				'permission_callback' => function () use ( $cf ) {
					return (
						get_option( 'cartflows_abilities_api_write', true ) &&
						$cf->permission_callback( 'cartflows_manage_flows_steps' )
					);
				},
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'template_id' ),
					'properties' => array(
						'template_id' => array(
							'type'        => 'integer',
							'description' => __( 'Template ID from the CartFlows cloud library (from list-flow-templates).', 'cartflows' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'flow_id'       => array(
							'type'        => 'integer',
							'description' => __( 'New funnel ID.', 'cartflows' ),
						),
						'title'         => array(
							'type'        => 'string',
							'description' => __( 'Funnel title.', 'cartflows' ),
						),
						'steps'         => array(
							'type'        => 'array',
							'description' => __( 'Imported steps.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'step_id' => array(
										'type'        => 'integer',
										'description' => __( 'Step ID.', 'cartflows' ),
									),
									'title'   => array(
										'type'        => 'string',
										'description' => __( 'Step title.', 'cartflows' ),
									),
									'type'    => array(
										'type'        => 'string',
										'description' => __( 'Step type.', 'cartflows' ),
									),
								),
							),
						),
						'skipped_steps' => array(
							'type'        => 'array',
							'description' => __( 'Steps that were skipped during import.', 'cartflows' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'title'  => array(
										'type'        => 'string',
										'description' => __( 'Skipped step title.', 'cartflows' ),
									),
									'type'   => array(
										'type'        => 'string',
										'description' => __( 'Skipped step type.', 'cartflows' ),
									),
									'reason' => array(
										'type'        => 'string',
										'description' => __( 'Reason for skipping.', 'cartflows' ),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => function ( $input ) use ( $cf ) {
					return $cf->import_flow_template( $input );
				},
				'meta'                => array(
					'annotations'  => array(
						'priority'        => 2.0,
						'readOnlyHint'    => false,
						'destructiveHint' => false,
						'idempotentHint'  => false,
						'openWorldHint'   => true,
					),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			),

		); // end $abilities.

		// Allow extensions to add or modify abilities.
		$abilities = apply_filters( 'cartflows_config_abilities', $abilities );
		if ( ! is_array( $abilities ) ) {
			$abilities = array();
		}

		self::$abilities = $abilities;

		return $abilities;
	}

	/**
	 * Get a single ability config by fully-qualified name.
	 *
	 * @param string $ability_name Ability identifier (e.g. 'cartflows/list-flows').
	 * @return array|false
	 */
	public static function get_ability( $ability_name ) {
		if ( false === self::$abilities ) {
			self::$abilities = self::get_abilities();
		}
		return isset( self::$abilities[ $ability_name ] ) ? self::$abilities[ $ability_name ] : false;
	}

	/**
	 * Get the input schema for a named ability.
	 *
	 * @param string $ability_name Ability identifier.
	 * @return array|false
	 */
	public static function get_ability_input_schema( $ability_name ) {
		$ability = self::get_ability( $ability_name );
		if ( false === $ability ) {
			return false;
		}
		return isset( $ability['input_schema'] ) ? $ability['input_schema'] : false;
	}

	/**
	 * Get the output schema for a named ability.
	 *
	 * @param string $ability_name Ability identifier.
	 * @return array|false
	 */
	public static function get_ability_output_schema( $ability_name ) {
		$ability = self::get_ability( $ability_name );
		if ( false === $ability ) {
			return false;
		}
		return isset( $ability['output_schema'] ) ? $ability['output_schema'] : false;
	}
}
