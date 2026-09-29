<?php
/**
 * CartFlows Ability Runtime.
 *
 * Execute callbacks and helper methods for all registered CartFlows abilities.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
use CartflowsAdmin\AdminCore\Inc\FlowMeta;
use CartflowsAdmin\AdminCore\Inc\StepMeta;

/**
 * Class Cartflows_Ability_Runtime
 *
 * Handles category registration, ability registration, and all execute callbacks.
 */
class Cartflows_Ability_Runtime {

	/**
	 * Parsed input values for the current execution.
	 *
	 * @var array|false
	 */
	protected $input = false;

	// ============================================================
	// Registration
	// ============================================================

	/**
	 * Register the CartFlows ability category.
	 *
	 * Hooked to: wp_abilities_api_categories_init
	 *
	 * @return void
	 */
	public function register_categories() {

		wp_register_ability_category(
			'cartflows',
			array(
				'label'       => __( 'CartFlows', 'cartflows' ),
				'description' => __( 'Abilities for CartFlows — funnel and step management.', 'cartflows' ),
			)
		);
	}

	/**
	 * Register all CartFlows abilities.
	 *
	 * Hooked to: wp_abilities_api_init
	 *
	 * @return void
	 */
	public function register() {

		$abilities = Cartflows_Ability_Config::get_abilities();

		foreach ( $abilities as $ability_name => $ability ) {
			wp_register_ability(
				$ability_name,
				array(
					'label'               => $ability['label'],
					'description'         => $ability['description'],
					'category'            => $ability['category'],
					'input_schema'        => $ability['input_schema'],
					'output_schema'       => $ability['output_schema'],
					'execute_callback'    => $ability['execute_callback'],
					'permission_callback' => $ability['permission_callback'],
					'meta'                => $ability['meta'],
				)
			);
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Read
	// ============================================================

	/**
	 * List funnels with pagination and optional status/search filters.
	 *
	 * @param array $input Validated input.
	 * @return array
	 */
	public function list_flows( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'list-flows' );

			$status   = $this->input_get( 'status' );
			$search   = $this->input_get( 'search' );
			$page     = $this->clamp_page( $this->input_get( 'paged' ) );
			$per_page = $this->clamp_per_page( $this->input_get( 'per_page' ) );

			// Exclude the store checkout flow.
			$store_checkout_id = intval( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) );

			$args = array(
				'post_type'      => CARTFLOWS_FLOW_POST_TYPE,
				'post_status'    => $status,
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'orderby'        => 'ID',
				'order'          => 'DESC',
			);

			if ( ! empty( $search ) ) {
				$args['s'] = $search;
			}

			if ( $store_checkout_id > 0 ) {
				$args['post__not_in'] = array( $store_checkout_id ); //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			}

			$query = new \WP_Query( $args );

			$flows = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					global $post;

					$flows[] = array(
						'id'             => intval( $post->ID ),
						'title'          => esc_html( $post->post_title ),
						'status'         => esc_html( $post->post_status ),
						'date_created'   => esc_html( $post->post_date ),
						'date_modified'  => esc_html( $post->post_modified ),
						'url_view'       => esc_url( get_permalink( $post->ID ) ),
						'url_edit'       => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $post->ID ) ),
						'flow_test_mode' => ( 'yes' === wcf()->options->get_flow_meta_value( $post->ID, 'wcf-testing' ) ),
					);
				}
				wp_reset_postdata();
			}

			$total       = intval( $query->found_posts );
			$total_pages = ( $per_page > 0 ) ? intval( ceil( $total / $per_page ) ) : 1;

			return array(
				'flows'       => $flows,
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => $page,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Get a single funnel by ID with full step list, settings, and links.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			$meta_options  = AdminHelper::get_flow_meta_options( $flow_id );
			$steps_raw     = AdminHelper::prepare_step_data( $flow_id, $meta_options );
			$settings_data = FlowMeta::get_meta_settings( $flow_id );

			$steps = array();
			if ( is_array( $steps_raw ) ) {
				foreach ( $steps_raw as $step ) {
					$step_id = isset( $step['id'] ) ? intval( $step['id'] ) : 0;
					$steps[] = array(
						'id'          => $step_id,
						'title'       => esc_html( isset( $step['title'] ) ? $step['title'] : get_the_title( $step_id ) ),
						'type'        => esc_html( isset( $step['type'] ) ? $step['type'] : '' ),
						'status'      => esc_html( get_post_status( $step_id ) ),
						'is_disabled' => ( 'yes' === get_post_meta( $step_id, 'wcf-disable-step', true ) ),
						'url'         => esc_url( get_permalink( $step_id ) ),
					);
				}
			}

			return array(
				'id'            => intval( $flow_id ),
				'title'         => esc_html( get_the_title( $flow_id ) ),
				'slug'          => esc_html( get_post_field( 'post_name', $flow_id, 'edit' ) ),
				'status'        => esc_html( get_post_status( $flow_id ) ),
				'url_view'      => esc_url( get_permalink( $flow_id ) ),
				'url_edit'      => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $flow_id ) ),
				'steps'         => $steps,
				'settings_data' => $settings_data,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Get a single step by ID with type, settings, and page-builder links.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_step( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-step' );

			$step_id = $this->input_get( 'id' );

			if ( 0 === $step_id ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$meta_options = AdminHelper::get_step_meta_options( $step_id );
			$step_type    = isset( $meta_options['type'] ) ? $meta_options['type'] : '';
			$flow_id      = intval( get_post_meta( $step_id, 'wcf-flow-id', true ) );

			$settings_data = StepMeta::get_meta_settings( $step_id, $step_type );

			return array(
				'id'               => intval( $step_id ),
				'title'            => esc_html( get_the_title( $step_id ) ),
				'type'             => esc_html( $step_type ),
				'flow_id'          => $flow_id,
				'flow_title'       => esc_html( get_the_title( $flow_id ) ),
				'url_view'         => esc_url( get_permalink( $step_id ) ),
				'url_edit'         => esc_url( get_edit_post_link( $step_id, 'edit' ) ),
				'url_page_builder' => esc_url( (string) \Cartflows_Helper::get_page_builder_edit_link( $step_id ) ),
				'settings_data'    => isset( $settings_data['settings'] ) ? $settings_data['settings'] : array(),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Analytics
	// ============================================================

	/**
	 * Get flow analytics: revenue and order data for a date range.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_flow_stats( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-flow-stats' );

			if ( ! wcf()->is_woo_active ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for analytics.', 'cartflows' ) );
			}

			$date_from   = $this->input_get( 'date_from' );
			$date_to     = $this->input_get( 'date_to' );
			$flow_id_raw = $this->input_get( 'flow_id' );
			$flow_id     = intval( $flow_id_raw );

			// Validate flow if specified.
			if ( $flow_id > 0 && CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			$flow_id_param = ( $flow_id > 0 ) ? $flow_id : '';

			$earnings_data = AdminHelper::get_earnings( $date_from, $date_to, $flow_id_param, 'funnels', '' );

			// Collect recent orders (last 4 CartFlows orders).
			$recent_orders_raw = wc_get_orders(
				array(
					'limit'        => 4,
					'orderby'      => 'date',
					'order'        => 'DESC',
					'meta_key'     => '_wcf_flow_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_compare' => 'EXISTS',
				)
			);

			$recent_orders = array();
			foreach ( $recent_orders_raw as $order ) {
				$recent_orders[] = array(
					'order_id'       => intval( $order->get_id() ),
					'customer_name'  => esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
					'customer_email' => sanitize_email( $order->get_billing_email() ),
					'order_total'    => esc_html( get_woocommerce_currency_symbol( $order->get_currency() ) . $order->get_total() ),
					'order_status'   => esc_html( ucfirst( $order->get_status() ) ),
					'order_date'     => esc_html( wc_format_datetime( $order->get_date_created(), 'Y-m-d' ) ),
				);
			}

			return array(
				'flow_stats'    => is_array( $earnings_data ) ? $earnings_data : array(),
				'recent_orders' => $recent_orders,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Lifecycle
	// ============================================================

	/**
	 * Publish a funnel and all its steps.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function publish_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'publish-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			$this->set_flow_status( $flow_id, 'publish' );

			return array(
				'id'      => intval( $flow_id ),
				'status'  => 'publish',
				'message' => esc_html__( 'Funnel published successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Unpublish (draft) a funnel and all its steps.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function unpublish_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'unpublish-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			$this->set_flow_status( $flow_id, 'draft' );

			return array(
				'id'      => intval( $flow_id ),
				'status'  => 'draft',
				'message' => esc_html__( 'Funnel unpublished (set to draft).', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Clone a funnel with all its steps and settings.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function clone_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'clone-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			$post = get_post( $flow_id );

			if ( ! $post || CARTFLOWS_FLOW_POST_TYPE !== $post->post_type ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			global $wpdb;

			$current_user    = wp_get_current_user();
			$new_post_author = intval( $current_user->ID );

			// Insert the cloned flow post.
			$new_flow_id = wp_insert_post(
				array(
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $new_post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_name'      => $post->post_name,
					'post_parent'    => $post->post_parent,
					'post_password'  => $post->post_password,
					'post_status'    => $post->post_status,
					'post_title'     => $post->post_title . ' Clone',
					'post_type'      => $post->post_type,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order,
				)
			);

			if ( is_wp_error( $new_flow_id ) || ! $new_flow_id ) {
				throw new \Exception( esc_html__( 'Failed to create funnel clone.', 'cartflows' ) );
			}

			// Copy taxonomies.
			foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
				$post_terms = wp_get_object_terms( $flow_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_flow_id, $post_terms, $taxonomy, false );
			}

			// Copy post meta.
			$post_meta_infos = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $flow_id )
			);

			if ( ! empty( $post_meta_infos ) ) {
				$sql_query_sel = array();
				foreach ( $post_meta_infos as $meta_info ) {
					if ( '_wp_old_slug' === $meta_info->meta_key ) {
						continue;
					}
					$sql_query_sel[] = $wpdb->prepare( '( %d, %s, %s )', $new_flow_id, $meta_info->meta_key, $meta_info->meta_value );
				}
				if ( ! empty( $sql_query_sel ) ) {
					$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES " . implode( ',', $sql_query_sel ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
				}
			}

			// Clone all steps.
			$flow_steps     = get_post_meta( $flow_id, 'wcf-steps', true );
			$new_flow_steps = array();

			update_post_meta( $new_flow_id, 'wcf-steps', array() );

			if ( is_array( $flow_steps ) && ! empty( $flow_steps ) ) {
				foreach ( $flow_steps as $step_data ) {
					$step_id     = intval( $step_data['id'] );
					$step_object = get_post( $step_id );
					$step_type   = get_post_meta( $step_id, 'wcf-step-type', true );

					if ( ! $step_object ) {
						continue;
					}

					$new_step_id = wp_insert_post(
						array(
							'comment_status' => $step_object->comment_status,
							'ping_status'    => $step_object->ping_status,
							'post_author'    => $new_post_author,
							'post_content'   => $step_object->post_content,
							'post_excerpt'   => $step_object->post_excerpt,
							'post_name'      => $step_object->post_name,
							'post_parent'    => $step_object->post_parent,
							'post_password'  => $step_object->post_password,
							'post_status'    => $step_object->post_status,
							'post_title'     => $step_object->post_title,
							'post_type'      => $step_object->post_type,
							'to_ping'        => $step_object->to_ping,
							'menu_order'     => $step_object->menu_order,
						)
					);

					if ( is_wp_error( $new_step_id ) || ! $new_step_id ) {
						continue;
					}

					$step_meta_infos = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $step_id )
					);

					if ( ! empty( $step_meta_infos ) ) {
						$step_sql_sel = array();
						foreach ( $step_meta_infos as $meta_info ) {
							$step_sql_sel[] = $wpdb->prepare( '( %d, %s, %s )', $new_step_id, $meta_info->meta_key, $meta_info->meta_value );
						}
						if ( ! empty( $step_sql_sel ) ) {
							$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES " . implode( ',', $step_sql_sel ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
						}
					}

					update_post_meta( $new_step_id, 'wcf-flow-id', $new_flow_id );
					update_post_meta( $new_step_id, 'wcf-step-type', $step_type );
					wp_set_object_terms( $new_step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
					wp_set_object_terms( $new_step_id, 'flow-' . $new_flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

					$new_flow_steps[] = array(
						'id'    => intval( $new_step_id ),
						'title' => esc_html( $step_object->post_title ),
						'type'  => esc_html( $step_type ),
					);
				}
			}

			update_post_meta( $new_flow_id, 'wcf-steps', $new_flow_steps );
			AdminHelper::clear_cache();

			return array(
				'id'          => intval( $new_flow_id ),
				'source_id'   => intval( $flow_id ),
				'title'       => esc_html( get_the_title( $new_flow_id ) ),
				'url_edit'    => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $new_flow_id ) ),
				'steps_count' => count( $new_flow_steps ),
				'message'     => esc_html__( 'Funnel cloned successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Move a funnel and all its steps to trash.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function trash_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'trash-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			// Trash all steps.
			$steps = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( is_array( $steps ) ) {
				foreach ( $steps as $step ) {
					wp_trash_post( intval( $step['id'] ) );
				}
			}

			// Trash the flow taxonomy term.
			$term_data = term_exists( 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );
			if ( is_array( $term_data ) ) {
				wp_delete_term( intval( $term_data['term_id'] ), CARTFLOWS_TAXONOMY_STEP_FLOW );
			}

			// Trash the flow.
			wp_trash_post( $flow_id );

			return array(
				'id'      => intval( $flow_id ),
				'status'  => 'trash',
				'message' => esc_html__( 'Funnel moved to trash.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Restore a trashed funnel and all its steps.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function restore_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'restore-flow' );

			$flow_id = $this->input_get( 'id' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			// Restore all steps.
			$steps = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( is_array( $steps ) ) {
				foreach ( $steps as $step ) {
					wp_untrash_post( intval( $step['id'] ) );
				}
			}

			// Restore the flow.
			wp_untrash_post( $flow_id );

			return array(
				'id'      => intval( $flow_id ),
				'status'  => 'publish',
				'message' => esc_html__( 'Funnel restored from trash.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Update
	// ============================================================

	/**
	 * Update a funnel's title and/or slug.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-flow' );

			$flow_id = $this->input_get( 'id' );
			$title   = $this->input_get( 'title' );
			$slug    = $this->input_get( 'slug' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			if ( empty( $title ) ) {
				throw new \Exception( esc_html__( 'Funnel title cannot be empty.', 'cartflows' ) );
			}

			$post_data = array(
				'ID'         => $flow_id,
				'post_title' => sanitize_text_field( $title ),
			);

			if ( ! empty( $slug ) ) {
				$post_data['post_name'] = sanitize_title( $slug );
			}

			$result = wp_update_post( $post_data );

			if ( is_wp_error( $result ) ) {
				throw new \Exception( esc_html__( 'Failed to update funnel.', 'cartflows' ) );
			}

			return array(
				'id'       => intval( $flow_id ),
				'title'    => esc_html( get_the_title( $flow_id ) ),
				'slug'     => esc_html( get_post_field( 'post_name', $flow_id, 'edit' ) ),
				'url_edit' => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $flow_id ) ),
				'message'  => esc_html__( 'Funnel updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Relationships
	// ============================================================

	/**
	 * Reorder the steps within a funnel.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function reorder_flow_steps( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'reorder-flow-steps' );

			$flow_id  = $this->input_get( 'flow_id' );
			$step_ids = $this->input_get( 'step_ids' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			if ( empty( $step_ids ) || ! is_array( $step_ids ) ) {
				throw new \Exception( esc_html__( 'Step IDs array is required.', 'cartflows' ) );
			}

			$step_ids = array_map( 'intval', $step_ids );

			// Build a lookup of current steps.
			$flow_steps     = get_post_meta( $flow_id, 'wcf-steps', true );
			$flow_steps_map = array();

			if ( is_array( $flow_steps ) ) {
				foreach ( $flow_steps as $value ) {
					$flow_steps_map[ intval( $value['id'] ) ] = $value;
				}
			}

			$new_flow_steps = array();

			foreach ( $step_ids as $step_id ) {
				$new_step_data          = isset( $flow_steps_map[ $step_id ] ) ? $flow_steps_map[ $step_id ] : array();
				$new_step_data['id']    = $step_id;
				$new_step_data['title'] = esc_html( get_the_title( $step_id ) );
				$new_step_data['type']  = esc_html( get_post_meta( $step_id, 'wcf-step-type', true ) );
				$new_flow_steps[]       = $new_step_data;
			}

			update_post_meta( $flow_id, 'wcf-steps', $new_flow_steps );

			$ordered_steps = array();
			foreach ( $new_flow_steps as $step ) {
				$ordered_steps[] = array(
					'id'    => intval( $step['id'] ),
					'title' => esc_html( $step['title'] ),
					'type'  => esc_html( $step['type'] ),
				);
			}

			return array(
				'flow_id' => intval( $flow_id ),
				'steps'   => $ordered_steps,
				'message' => esc_html__( 'Funnel steps reordered successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Output
	// ============================================================

	/**
	 * Export one or more funnels as JSON data.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function export_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'export-flow' );

			$flow_ids = $this->input_get( 'flow_ids' );

			if ( empty( $flow_ids ) || ! is_array( $flow_ids ) ) {
				throw new \Exception( esc_html__( 'At least one funnel ID is required.', 'cartflows' ) );
			}

			$flow_ids = array_map( 'intval', $flow_ids );

			$exporter = \CartFlows_Importer::get_instance();
			$flows    = array();

			foreach ( $flow_ids as $flow_id ) {
				if ( $flow_id <= 0 ) {
					continue;
				}
				if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
					continue;
				}
				$flows[] = $exporter->get_flow_export_data( $flow_id );
			}

			if ( empty( $flows ) ) {
				throw new \Exception( esc_html__( 'No valid funnel IDs provided for export.', 'cartflows' ) );
			}

			return array(
				'flows'        => $flows,
				'export_count' => count( $flows ),
				'message'      => esc_html__( 'Funnel data exported successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Step Lifecycle
	// ============================================================

	/**
	 * Clone a step within the same funnel.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function clone_step( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'clone-step' );

			$flow_id = $this->input_get( 'flow_id' );
			$step_id = $this->input_get( 'step_id' );

			if ( 0 === $flow_id || 0 === $step_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel or step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $flow_id ) !== CARTFLOWS_FLOW_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_object     = get_post( $step_id );
			$current_user    = wp_get_current_user();
			$new_post_author = intval( $current_user->ID );

			global $wpdb;

			// Insert the cloned step.
			$new_step_id = wp_insert_post(
				array(
					'comment_status' => $step_object->comment_status,
					'ping_status'    => $step_object->ping_status,
					'post_author'    => $new_post_author,
					'post_content'   => $step_object->post_content,
					'post_excerpt'   => $step_object->post_excerpt,
					'post_name'      => $step_object->post_name,
					'post_parent'    => $step_object->post_parent,
					'post_password'  => $step_object->post_password,
					'post_status'    => $step_object->post_status,
					'post_title'     => $step_object->post_title . ' Clone',
					'post_type'      => $step_object->post_type,
					'to_ping'        => $step_object->to_ping,
					'menu_order'     => $step_object->menu_order,
				)
			);

			if ( is_wp_error( $new_step_id ) || ! $new_step_id ) {
				throw new \Exception( esc_html__( 'Failed to create step clone.', 'cartflows' ) );
			}

			// Copy taxonomies.
			foreach ( get_object_taxonomies( $step_object->post_type ) as $taxonomy ) {
				$post_terms = wp_get_object_terms( $step_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_step_id, $post_terms, $taxonomy, false );
			}

			// Copy step meta.
			$step_meta_infos = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $step_id )
			);

			if ( ! empty( $step_meta_infos ) ) {
				$step_sql_sel = array();
				foreach ( $step_meta_infos as $meta_info ) {
					if ( '_wp_old_slug' === $meta_info->meta_key ) {
						continue;
					}
					$step_sql_sel[] = $wpdb->prepare( '( %d, %s, %s )', $new_step_id, $meta_info->meta_key, $meta_info->meta_value );
				}
				if ( ! empty( $step_sql_sel ) ) {
					$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES " . implode( ',', $step_sql_sel ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
				}
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

			update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );
			update_post_meta( $new_step_id, 'wcf-step-type', $step_type );
			wp_set_object_terms( $new_step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
			wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

			// Append the new step to the flow's step list.
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => intval( $new_step_id ),
				'title' => esc_html( $step_object->post_title ),
				'type'  => esc_html( $step_type ),
			);

			if ( method_exists( '\Cartflows_Helper', 'get_instance' ) ) {
				$flow_steps = \Cartflows_Helper::get_instance()->maybe_update_flow_steps( $flow_id, $flow_steps );
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );
			AdminHelper::clear_cache();

			return array(
				'id'        => intval( $new_step_id ),
				'source_id' => intval( $step_id ),
				'flow_id'   => intval( $flow_id ),
				'title'     => esc_html( get_the_title( $new_step_id ) ),
				'type'      => esc_html( $step_type ),
				'message'   => esc_html__( 'Step cloned successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Step Update
	// ============================================================

	/**
	 * Rename a CartFlows step.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_step_title( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-step-title' );

			$step_id = $this->input_get( 'step_id' );
			$title   = $this->input_get( 'title' );

			if ( 0 === $step_id ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			if ( empty( $title ) ) {
				throw new \Exception( esc_html__( 'Step title cannot be empty.', 'cartflows' ) );
			}

			$result = wp_update_post(
				array(
					'ID'         => $step_id,
					'post_title' => sanitize_text_field( $title ),
				)
			);

			if ( is_wp_error( $result ) ) {
				throw new \Exception( esc_html__( 'Failed to update step title.', 'cartflows' ) );
			}

			return array(
				'id'      => intval( $step_id ),
				'title'   => esc_html( get_the_title( $step_id ) ),
				'message' => esc_html__( 'Step title updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Admin / Settings Read
	// ============================================================

	/**
	 * Return CartFlows general settings (page builder, checkout, flags).
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_general_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-general-settings' );

			$settings = \Cartflows_Helper::get_common_settings();

			return array(
				'default_page_builder'     => esc_html( isset( $settings['default_page_builder'] ) ? $settings['default_page_builder'] : '' ),
				'global_checkout'          => esc_html( isset( $settings['global_checkout'] ) ? $settings['global_checkout'] : '' ),
				'override_global_checkout' => esc_html( isset( $settings['override_global_checkout'] ) ? $settings['override_global_checkout'] : '' ),
				'override_store_order_pay' => esc_html( isset( $settings['override_store_order_pay'] ) ? $settings['override_store_order_pay'] : '' ),
				'disallow_indexing'        => esc_html( isset( $settings['disallow_indexing'] ) ? $settings['disallow_indexing'] : '' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Return the flow configured as the CartFlows store checkout.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_store_checkout( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-store-checkout' );

			$flow_id = intval( \Cartflows_Helper::get_global_setting( '_cartflows_store_checkout' ) );

			if ( $flow_id > 0 && CARTFLOWS_FLOW_POST_TYPE === get_post_type( $flow_id ) ) {
				return array(
					'flow_id'       => $flow_id,
					'flow_title'    => esc_html( get_the_title( $flow_id ) ),
					'url_edit'      => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $flow_id ) ),
					'is_configured' => true,
				);
			}

			return array(
				'flow_id'       => 0,
				'flow_title'    => '',
				'url_edit'      => '',
				'is_configured' => false,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Return CartFlows permalink (URL slug) settings.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_permalink_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-permalink-settings' );

			$settings = \Cartflows_Helper::get_permalink_settings();

			return array(
				'permalink'           => esc_html( isset( $settings['permalink'] ) ? $settings['permalink'] : '' ),
				'permalink_flow_base' => esc_html( isset( $settings['permalink_flow_base'] ) ? $settings['permalink_flow_base'] : '' ),
				'permalink_structure' => esc_html( isset( $settings['permalink_structure'] ) ? $settings['permalink_structure'] : '' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Return pixel / analytics integration settings, optionally filtered to one group.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_integration_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-integration-settings' );

			$integration = $this->input_get( 'integration' );

			$map = array(
				'facebook'         => '_cartflows_facebook',
				'google_analytics' => '_cartflows_google_analytics',
				'google_ads'       => '_cartflows_google_ads',
				'tiktok'           => '_cartflows_tiktok',
				'pinterest'        => '_cartflows_pinterest',
				'snapchat'         => '_cartflows_snapchat',
			);

			$result = array();

			if ( 'all' === $integration ) {
				foreach ( $map as $key => $option_name ) {
					$value          = \Cartflows_Helper::get_admin_settings_option( $option_name, array() );
					$result[ $key ] = is_array( $value ) ? $value : array();
				}
			} else {
				if ( ! isset( $map[ $integration ] ) ) {
					throw new \Exception( esc_html__( 'Invalid integration group.', 'cartflows' ) );
				}
				$value                  = \Cartflows_Helper::get_admin_settings_option( $map[ $integration ], array() );
				$result[ $integration ] = is_array( $value ) ? $value : array();
			}

			return $result;

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Admin / Settings Write
	// ============================================================

	/**
	 * Update CartFlows general settings.
	 *
	 * Only fields present in $input (non-default) are merged in; omitted fields keep existing values.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_general_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-general-settings' );

			$current = \Cartflows_Helper::get_common_settings();

			$allowed_keys = array( 'default_page_builder', 'global_checkout', 'override_global_checkout', 'disallow_indexing' );

			$updates = array();
			foreach ( $allowed_keys as $key ) {
				$value = $this->input_get( $key, null );
				if ( null !== $value && '' !== $value ) {
					$updates[ $key ] = sanitize_text_field( $value );
				}
			}

			if ( empty( $updates ) ) {
				throw new \Exception( esc_html__( 'No settings provided to update.', 'cartflows' ) );
			}

			$new_settings = wp_parse_args( $updates, $current );

			\Cartflows_Helper::update_admin_settings_option( '_cartflows_common', $new_settings, false );

			return array(
				'message'  => esc_html__( 'General settings updated successfully.', 'cartflows' ),
				'settings' => array(
					'default_page_builder'     => esc_html( isset( $new_settings['default_page_builder'] ) ? $new_settings['default_page_builder'] : '' ),
					'global_checkout'          => esc_html( isset( $new_settings['global_checkout'] ) ? $new_settings['global_checkout'] : '' ),
					'override_global_checkout' => esc_html( isset( $new_settings['override_global_checkout'] ) ? $new_settings['override_global_checkout'] : '' ),
					'override_store_order_pay' => esc_html( isset( $new_settings['override_store_order_pay'] ) ? $new_settings['override_store_order_pay'] : '' ),
					'disallow_indexing'        => esc_html( isset( $new_settings['disallow_indexing'] ) ? $new_settings['disallow_indexing'] : '' ),
				),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update CartFlows permalink (URL slug) settings.
	 *
	 * Forces a permalink flush after saving.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_permalink_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-permalink-settings' );

			$current = \Cartflows_Helper::get_permalink_settings();

			$permalink           = $this->input_get( 'permalink', null );
			$permalink_flow_base = $this->input_get( 'permalink_flow_base', null );
			$permalink_structure = $this->input_get( 'permalink_structure', null );

			if ( null === $permalink && null === $permalink_flow_base && null === $permalink_structure ) {
				throw new \Exception( esc_html__( 'No permalink settings provided to update.', 'cartflows' ) );
			}

			$new_settings = $current;

			if ( null !== $permalink ) {
				$new_settings['permalink'] = ! empty( $permalink )
					? sanitize_title( $permalink )
					: CARTFLOWS_STEP_PERMALINK_SLUG;
			}

			if ( null !== $permalink_flow_base ) {
				$new_settings['permalink_flow_base'] = ! empty( $permalink_flow_base )
					? sanitize_title( $permalink_flow_base )
					: CARTFLOWS_FLOW_PERMALINK_SLUG;
			}

			if ( null !== $permalink_structure ) {
				$new_settings['permalink_structure'] = sanitize_text_field( $permalink_structure );
			}

			\Cartflows_Helper::update_admin_settings_option( '_cartflows_permalink', $new_settings, true );
			update_option( 'cartflows_permalink_refresh', true );

			return array(
				'message'             => esc_html__( 'Permalink settings updated successfully.', 'cartflows' ),
				'permalink'           => esc_html( $new_settings['permalink'] ),
				'permalink_flow_base' => esc_html( $new_settings['permalink_flow_base'] ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Checkout Read
	// ============================================================

	/**
	 * Get checkout step settings: layout, products, form toggles, button, and design options.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_checkout_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-settings' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			// Layout.
			$layout = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-layout' );

			// Primary color and font.
			$primary_color = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-primary-color' );
			$font_family   = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-base-font-family' );

			// Products.
			$products_raw = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-products' );
			$products     = array();

			if ( is_array( $products_raw ) && ! empty( $products_raw ) ) {
				foreach ( $products_raw as $product ) {
					$product_id  = isset( $product['product'] ) ? intval( $product['product'] ) : 0;
					$product_obj = ( $product_id > 0 ) ? wc_get_product( $product_id ) : null;

					$products[] = array(
						'product_id'     => $product_id,
						'name'           => $product_obj ? esc_html( $product_obj->get_name() ) : '',
						'quantity'       => isset( $product['quantity'] ) ? intval( $product['quantity'] ) : 1,
						'discount_type'  => isset( $product['discount_type'] ) ? esc_html( $product['discount_type'] ) : '',
						'discount_value' => isset( $product['discount_value'] ) ? esc_html( $product['discount_value'] ) : '',
						'add_to_cart'    => isset( $product['add_to_cart'] ) ? esc_html( $product['add_to_cart'] ) : 'yes',
					);
				}
			}

			// Form settings.
			$form_settings = array(
				'show_coupon_field'         => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-show-coupon-field' ) ),
				'optimize_coupon_field'     => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-optimize-coupon-field' ) ),
				'show_additional_fields'    => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-additional-fields' ) ),
				'optimize_order_note'       => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-optimize-order-note-field' ) ),
				'ship_to_different_address' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-shipto-diff-addr-fields' ) ),
				'google_autoaddress'        => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-google-autoaddress' ) ),
			);

			// Button settings.
			$button_settings = array(
				'button_text'    => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-text' ) ),
				'show_lock_icon' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-lock' ) ),
				'show_price'     => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-price-display' ) ),
			);

			// Advanced settings.
			$advanced_settings = array(
				'show_product_images' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-order-review-show-product-images' ) ),
				'enable_cart_editing' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-remove-product-field' ) ),
			);

			return array(
				'step_id'           => intval( $step_id ),
				'layout'            => esc_html( $layout ),
				'primary_color'     => esc_html( $primary_color ),
				'font_family'       => esc_html( $font_family ),
				'products'          => $products,
				'form_settings'     => $form_settings,
				'button_settings'   => $button_settings,
				'advanced_settings' => $advanced_settings,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Get products assigned to a checkout step.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_checkout_products( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-products' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			$products_raw = wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-products' );
			$products     = array();

			if ( is_array( $products_raw ) && ! empty( $products_raw ) ) {
				foreach ( $products_raw as $product ) {
					$product_id  = isset( $product['product'] ) ? intval( $product['product'] ) : 0;
					$product_obj = ( $product_id > 0 ) ? wc_get_product( $product_id ) : null;

					$products[] = array(
						'product_id'     => $product_id,
						'name'           => $product_obj ? esc_html( $product_obj->get_name() ) : '',
						'quantity'       => isset( $product['quantity'] ) ? intval( $product['quantity'] ) : 1,
						'discount_type'  => isset( $product['discount_type'] ) ? esc_html( $product['discount_type'] ) : '',
						'discount_value' => isset( $product['discount_value'] ) ? esc_html( $product['discount_value'] ) : '',
						'add_to_cart'    => isset( $product['add_to_cart'] ) ? esc_html( $product['add_to_cart'] ) : 'yes',
						'img_url'        => $product_obj ? esc_url( (string) get_the_post_thumbnail_url( $product_id ) ) : '',
						'regular_price'  => $product_obj ? esc_html( \Cartflows_Helper::get_product_original_price( $product_obj ) ) : '',
					);
				}
			}

			return array(
				'step_id'  => intval( $step_id ),
				'products' => $products,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Get checkout form field configuration.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_checkout_fields( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-checkout-fields' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id    = $this->input_get( 'step_id' );
			$field_type = $this->input_get( 'field_type' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			$billing_fields  = array();
			$shipping_fields = array();

			if ( 'all' === $field_type || 'billing' === $field_type ) {
				$billing_raw = wcf()->options->get_checkout_meta_value( $step_id, 'wcf_field_order_billing' );
				if ( ! is_array( $billing_raw ) || empty( $billing_raw ) ) {
					$billing_raw = \Cartflows_Helper::get_checkout_fields( 'billing', $step_id );
				}
				$billing_fields = is_array( $billing_raw ) ? $billing_raw : array();
			}

			if ( 'all' === $field_type || 'shipping' === $field_type ) {
				$shipping_raw = wcf()->options->get_checkout_meta_value( $step_id, 'wcf_field_order_shipping' );
				if ( ! is_array( $shipping_raw ) || empty( $shipping_raw ) ) {
					$shipping_raw = \Cartflows_Helper::get_checkout_fields( 'shipping', $step_id );
				}
				$shipping_fields = is_array( $shipping_raw ) ? $shipping_raw : array();
			}

			$form_settings = array(
				'show_coupon_field'         => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-show-coupon-field' ) ),
				'optimize_coupon_field'     => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-optimize-coupon-field' ) ),
				'show_additional_fields'    => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-additional-fields' ) ),
				'optimize_order_note'       => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-optimize-order-note-field' ) ),
				'ship_to_different_address' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-shipto-diff-addr-fields' ) ),
				'google_autoaddress'        => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-google-autoaddress' ) ),
			);

			return array(
				'step_id'         => intval( $step_id ),
				'billing_fields'  => $billing_fields,
				'shipping_fields' => $shipping_fields,
				'form_settings'   => $form_settings,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Checkout Write
	// ============================================================

	/**
	 * Update products assigned to a checkout step.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_checkout_products( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-products' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id  = $this->input_get( 'step_id' );
			$products = $this->input_get( 'products' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			if ( ! is_array( $products ) ) {
				throw new \Exception( esc_html__( 'Products must be an array.', 'cartflows' ) );
			}

			$sanitized_products = array();

			foreach ( $products as $index => $product ) {
				$product_id = isset( $product['product'] ) ? intval( $product['product'] ) : 0;

				if ( $product_id <= 0 ) {
					continue;
				}

				$product_obj = wc_get_product( $product_id );
				if ( ! $product_obj ) {
					throw new \Exception(
						sprintf(
							/* translators: %d: product ID */
							esc_html__( 'WooCommerce product %d not found.', 'cartflows' ),
							$product_id
						)
					);
				}

				$unique_id = isset( $product['unique_id'] ) ? sanitize_text_field( $product['unique_id'] ) : md5( $product_id . '_' . $index . '_' . time() );

				$sanitized_products[] = array(
					'product'        => $product_id,
					'quantity'       => isset( $product['quantity'] ) ? max( 1, intval( $product['quantity'] ) ) : 1,
					'discount_type'  => isset( $product['discount_type'] ) ? sanitize_text_field( $product['discount_type'] ) : '',
					'discount_value' => isset( $product['discount_value'] ) ? sanitize_text_field( $product['discount_value'] ) : '',
					'add_to_cart'    => isset( $product['add_to_cart'] ) ? sanitize_text_field( $product['add_to_cart'] ) : 'yes',
					'unique_id'      => $unique_id,
				);
			}

			update_post_meta( $step_id, 'wcf-checkout-products', $sanitized_products );

			return array(
				'step_id'  => intval( $step_id ),
				'products' => $sanitized_products,
				'message'  => esc_html__( 'Checkout products updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update checkout layout/skin.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_checkout_layout( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-layout' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );
			$layout  = $this->input_get( 'layout' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			update_post_meta( $step_id, 'wcf-checkout-layout', sanitize_text_field( $layout ) );

			return array(
				'step_id' => intval( $step_id ),
				'layout'  => esc_html( $layout ),
				'message' => esc_html__( 'Checkout layout updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update place order button settings.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_checkout_place_order_button( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-place-order-button' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			$button_text    = $this->input_get( 'button_text', null );
			$show_lock_icon = $this->input_get( 'show_lock_icon', null );
			$show_price     = $this->input_get( 'show_price', null );

			$updated = false;

			if ( null !== $button_text && '' !== $button_text ) {
				update_post_meta( $step_id, 'wcf-checkout-place-order-button-text', sanitize_text_field( $button_text ) );
				$updated = true;
			}

			if ( null !== $show_lock_icon && '' !== $show_lock_icon ) {
				update_post_meta( $step_id, 'wcf-checkout-place-order-button-lock', sanitize_text_field( $show_lock_icon ) );
				$updated = true;
			}

			if ( null !== $show_price && '' !== $show_price ) {
				update_post_meta( $step_id, 'wcf-checkout-place-order-button-price-display', sanitize_text_field( $show_price ) );
				$updated = true;
			}

			if ( ! $updated ) {
				throw new \Exception( esc_html__( 'No button settings provided to update.', 'cartflows' ) );
			}

			return array(
				'step_id'        => intval( $step_id ),
				'button_text'    => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-text' ) ),
				'show_lock_icon' => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-lock' ) ),
				'show_price'     => esc_html( wcf()->options->get_checkout_meta_value( $step_id, 'wcf-checkout-place-order-button-price-display' ) ),
				'message'        => esc_html__( 'Place order button updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update checkout form-level settings (toggles).
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_checkout_form_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-checkout-form-settings' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for checkout abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'checkout' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a checkout type.', 'cartflows' ) );
			}

			// Map input field names to post meta keys.
			$field_meta_map = array(
				'show_coupon_field'         => 'wcf-show-coupon-field',
				'optimize_coupon_field'     => 'wcf-optimize-coupon-field',
				'show_additional_fields'    => 'wcf-checkout-additional-fields',
				'optimize_order_note'       => 'wcf-optimize-order-note-field',
				'ship_to_different_address' => 'wcf-shipto-diff-addr-fields',
				'google_autoaddress'        => 'wcf-google-autoaddress',
				'show_product_images'       => 'wcf-order-review-show-product-images',
				'enable_cart_editing'       => 'wcf-remove-product-field',
			);

			$updated  = false;
			$settings = array();

			foreach ( $field_meta_map as $input_key => $meta_key ) {
				$value = $this->input_get( $input_key, null );

				if ( null !== $value && '' !== $value ) {
					update_post_meta( $step_id, $meta_key, sanitize_text_field( $value ) );
					$updated                = true;
					$settings[ $input_key ] = esc_html( $value );
				} else {
					$settings[ $input_key ] = esc_html( wcf()->options->get_checkout_meta_value( $step_id, $meta_key ) );
				}
			}

			if ( ! $updated ) {
				throw new \Exception( esc_html__( 'No form settings provided to update.', 'cartflows' ) );
			}

			return array(
				'step_id'  => intval( $step_id ),
				'settings' => $settings,
				'message'  => esc_html__( 'Checkout form settings updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Thank You Read
	// ============================================================

	/**
	 * Get thank you step settings: layout, section visibility, custom text, redirect, and design.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_thankyou_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-thankyou-settings' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for thank you abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'thankyou' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a thank you type.', 'cartflows' ) );
			}

			// Layout.
			$layout = wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-layout' );

			// Section visibility.
			$sections = array(
				'show_overview' => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-overview-section' ) ),
				'show_details'  => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-details-section' ) ),
				'show_billing'  => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-billing-section' ) ),
				'show_shipping' => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-shipping-section' ) ),
			);

			// Custom text.
			$custom_text = wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-text' );

			// Redirect settings.
			$redirect = array(
				'enabled'      => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-tq-redirect-section' ) ),
				'redirect_url' => esc_url( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-redirect-link' ) ),
			);

			// Design settings.
			$design = array(
				'primary_color'       => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-primary-color' ) ),
				'text_color'          => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-text-color' ) ),
				'text_font_family'    => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-font-family' ) ),
				'text_font_size'      => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-font-size' ) ),
				'heading_color'       => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-heading-color' ) ),
				'heading_font_family' => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-heading-font-family' ) ),
				'heading_font_weight' => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-heading-font-wt' ) ),
				'container_width'     => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-container-width' ) ),
				'section_bg_color'    => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-section-bg-color' ) ),
			);

			return array(
				'step_id'     => intval( $step_id ),
				'layout'      => esc_html( $layout ),
				'sections'    => $sections,
				'custom_text' => esc_html( $custom_text ),
				'redirect'    => $redirect,
				'design'      => $design,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Thank You Write
	// ============================================================

	/**
	 * Update thank you layout/skin.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_thankyou_layout( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-layout' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for thank you abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );
			$layout  = $this->input_get( 'layout' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'thankyou' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a thank you type.', 'cartflows' ) );
			}

			update_post_meta( $step_id, 'wcf-tq-layout', sanitize_text_field( $layout ) );

			return array(
				'step_id' => intval( $step_id ),
				'layout'  => esc_html( $layout ),
				'message' => esc_html__( 'Thank you layout updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update thank you section visibility toggles.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_thankyou_sections( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-sections' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for thank you abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'thankyou' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a thank you type.', 'cartflows' ) );
			}

			$field_meta_map = array(
				'show_overview' => 'wcf-show-overview-section',
				'show_details'  => 'wcf-show-details-section',
				'show_billing'  => 'wcf-show-billing-section',
				'show_shipping' => 'wcf-show-shipping-section',
			);

			$updated  = false;
			$sections = array();

			foreach ( $field_meta_map as $input_key => $meta_key ) {
				$value = $this->input_get( $input_key, null );

				if ( null !== $value && '' !== $value ) {
					update_post_meta( $step_id, $meta_key, sanitize_text_field( $value ) );
					$updated                = true;
					$sections[ $input_key ] = esc_html( $value );
				} else {
					$sections[ $input_key ] = esc_html( wcf()->options->get_thankyou_meta_value( $step_id, $meta_key ) );
				}
			}

			if ( ! $updated ) {
				throw new \Exception( esc_html__( 'No section settings provided to update.', 'cartflows' ) );
			}

			return array(
				'step_id'  => intval( $step_id ),
				'sections' => $sections,
				'message'  => esc_html__( 'Thank you sections updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update thank you redirect settings.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_thankyou_redirect( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-redirect' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for thank you abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'thankyou' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a thank you type.', 'cartflows' ) );
			}

			$enabled      = $this->input_get( 'enabled', null );
			$redirect_url = $this->input_get( 'redirect_url', null );
			$updated      = false;

			if ( null !== $enabled && '' !== $enabled ) {
				update_post_meta( $step_id, 'wcf-show-tq-redirect-section', sanitize_text_field( $enabled ) );
				$updated = true;
			}

			if ( null !== $redirect_url && '' !== $redirect_url ) {
				update_post_meta( $step_id, 'wcf-tq-redirect-link', esc_url_raw( $redirect_url ) );
				$updated = true;
			}

			if ( ! $updated ) {
				throw new \Exception( esc_html__( 'No redirect settings provided to update.', 'cartflows' ) );
			}

			return array(
				'step_id'      => intval( $step_id ),
				'enabled'      => esc_html( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-show-tq-redirect-section' ) ),
				'redirect_url' => esc_url( wcf()->options->get_thankyou_meta_value( $step_id, 'wcf-tq-redirect-link' ) ),
				'message'      => esc_html__( 'Thank you redirect settings updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update thank you custom text.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_thankyou_custom_text( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-thankyou-custom-text' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for thank you abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );
			$text    = $this->input_get( 'text' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'thankyou' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not a thank you type.', 'cartflows' ) );
			}

			update_post_meta( $step_id, 'wcf-tq-text', sanitize_text_field( $text ) );

			return array(
				'step_id' => intval( $step_id ),
				'text'    => esc_html( get_post_meta( $step_id, 'wcf-tq-text', true ) ),
				'message' => esc_html__( 'Thank you custom text updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Optin Read
	// ============================================================

	/**
	 * Get optin step settings: product, button text, pass-fields config.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_optin_settings( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-optin-settings' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for optin abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'optin' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not an optin type.', 'cartflows' ) );
			}

			// Product.
			$product_raw = wcf()->options->get_optin_meta_value( $step_id, 'wcf-optin-product' );
			$product     = array(
				'id'   => 0,
				'name' => '',
			);

			if ( ! empty( $product_raw ) ) {
				$product_id  = is_array( $product_raw ) ? intval( reset( $product_raw ) ) : intval( $product_raw );
				$product_obj = ( $product_id > 0 ) ? wc_get_product( $product_id ) : null;

				if ( $product_obj ) {
					$product = array(
						'id'   => $product_id,
						'name' => esc_html( $product_obj->get_name() ),
					);
				}
			}

			// Button text.
			$button_text = wcf()->options->get_optin_meta_value( $step_id, 'wcf-submit-button-text' );

			// Pass fields.
			$pass_fields = array(
				'enabled'         => esc_html( wcf()->options->get_optin_meta_value( $step_id, 'wcf-optin-pass-fields' ) ),
				'specific_fields' => esc_html( wcf()->options->get_optin_meta_value( $step_id, 'wcf-optin-pass-specific-fields' ) ),
			);

			return array(
				'step_id'     => intval( $step_id ),
				'product'     => $product,
				'button_text' => esc_html( $button_text ),
				'pass_fields' => $pass_fields,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Optin Write
	// ============================================================

	/**
	 * Update optin product assignment.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_optin_product( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-product' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for optin abilities.', 'cartflows' ) );
			}

			$step_id    = $this->input_get( 'step_id' );
			$product_id = $this->input_get( 'product_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'optin' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not an optin type.', 'cartflows' ) );
			}

			if ( $product_id <= 0 ) {
				throw new \Exception( esc_html__( 'Invalid product ID.', 'cartflows' ) );
			}

			$product_obj = wc_get_product( $product_id );
			if ( ! $product_obj ) {
				throw new \Exception( esc_html__( 'WooCommerce product not found.', 'cartflows' ) );
			}

			if ( ! $product_obj->is_type( 'simple' ) ) {
				throw new \Exception( esc_html__( 'Product must be a Simple product type.', 'cartflows' ) );
			}

			if ( ! $product_obj->is_virtual() ) {
				throw new \Exception( esc_html__( 'Product must be Virtual.', 'cartflows' ) );
			}

			if ( $product_obj->get_price() > 0 ) {
				throw new \Exception( esc_html__( 'Product price must be zero (free).', 'cartflows' ) );
			}

			update_post_meta( $step_id, 'wcf-optin-product', array( $product_id ) );

			return array(
				'step_id'      => intval( $step_id ),
				'product_id'   => intval( $product_id ),
				'product_name' => esc_html( $product_obj->get_name() ),
				'message'      => esc_html__( 'Optin product updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update optin submit button text.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_optin_button_text( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-button-text' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for optin abilities.', 'cartflows' ) );
			}

			$step_id     = $this->input_get( 'step_id' );
			$button_text = $this->input_get( 'button_text' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'optin' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not an optin type.', 'cartflows' ) );
			}

			if ( empty( $button_text ) ) {
				throw new \Exception( esc_html__( 'Button text cannot be empty.', 'cartflows' ) );
			}

			update_post_meta( $step_id, 'wcf-submit-button-text', sanitize_text_field( $button_text ) );

			return array(
				'step_id'     => intval( $step_id ),
				'button_text' => esc_html( get_post_meta( $step_id, 'wcf-submit-button-text', true ) ),
				'message'     => esc_html__( 'Optin button text updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update optin pass-fields settings.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_optin_pass_fields( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-optin-pass-fields' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required for optin abilities.', 'cartflows' ) );
			}

			$step_id = $this->input_get( 'step_id' );

			if ( null === $step_id || 0 === intval( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			if ( get_post_type( $step_id ) !== CARTFLOWS_STEP_POST_TYPE ) {
				throw new \Exception( esc_html__( 'Step not found.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );
			if ( 'optin' !== $step_type ) {
				throw new \Exception( esc_html__( 'Step is not an optin type.', 'cartflows' ) );
			}

			$enabled         = $this->input_get( 'enabled', null );
			$specific_fields = $this->input_get( 'specific_fields', null );
			$updated         = false;

			if ( null !== $enabled && '' !== $enabled ) {
				update_post_meta( $step_id, 'wcf-optin-pass-fields', sanitize_text_field( $enabled ) );
				$updated = true;
			}

			if ( null !== $specific_fields && '' !== $specific_fields ) {
				update_post_meta( $step_id, 'wcf-optin-pass-specific-fields', sanitize_text_field( $specific_fields ) );
				$updated = true;
			}

			if ( ! $updated ) {
				throw new \Exception( esc_html__( 'No pass-fields settings provided to update.', 'cartflows' ) );
			}

			return array(
				'step_id'         => intval( $step_id ),
				'enabled'         => esc_html( wcf()->options->get_optin_meta_value( $step_id, 'wcf-optin-pass-fields' ) ),
				'specific_fields' => esc_html( wcf()->options->get_optin_meta_value( $step_id, 'wcf-optin-pass-specific-fields' ) ),
				'message'         => esc_html__( 'Optin pass-fields settings updated successfully.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ── Landing ─────────────────────────────────────────────────

	/**
	 * Return the configuration of a landing step.
	 *
	 * Fields returned: slug, next_step_link, disable_step.
	 *
	 * @param array $input Ability input containing step_id.
	 * @return array|WP_Error
	 * @throws \Exception On failure.
	 */
	public function get_landing_settings( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-landing-settings' );

			$step_id = $this->input_get( 'step_id' );

			if ( ! $step_id || 'cartflows_step' !== get_post_type( $step_id ) ) {
				throw new \Exception( esc_html__( 'Invalid step ID.', 'cartflows' ) );
			}

			$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

			if ( 'landing' !== $step_type ) {
				throw new \Exception( esc_html__( 'The provided step is not a landing step.', 'cartflows' ) );
			}

			$slug           = get_post_field( 'post_name', $step_id );
			$next_step_link = wcf()->utils->get_linking_url(
				array( 'id' => $step_id )
			);
			$disable_step   = get_post_meta( $step_id, 'wcf-disable-step', true );

			return array(
				'step_id'        => intval( $step_id ),
				'slug'           => esc_html( $slug ),
				'next_step_link' => esc_url( $next_step_link ),
				'disable_step'   => esc_html( ! empty( $disable_step ) ? $disable_step : 'no' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Email Report
	// ============================================================

	/**
	 * Get email report settings: enabled status, recipient list, next scheduled time.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_email_report_settings( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-email-report-settings' );

			$enabled   = get_option( 'cartflows_stats_report_emails', 'enable' );
			$email_raw = get_option( 'cartflows_stats_report_email_ids', get_option( 'admin_email' ) );

			$email_ids = array();
			if ( ! empty( $email_raw ) ) {
				$email_ids = array_values( array_filter( array_map( 'trim', preg_split( "/[\f\r\n]+/", $email_raw ) ) ) );
			}

			$next_scheduled = '';
			if ( function_exists( 'as_next_scheduled_action' ) ) {
				$timestamp = as_next_scheduled_action( 'cartflows_send_report_summary_email' );
				if ( ! empty( $timestamp ) && false !== $timestamp ) {
					$next_scheduled = gmdate( 'c', $timestamp );
				}
			}

			return array(
				'enabled'        => esc_html( $enabled ),
				'email_ids'      => array_map( 'sanitize_email', $email_ids ),
				'next_scheduled' => esc_html( $next_scheduled ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update email report settings: toggle enabled, update recipient list.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_email_report_settings( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-email-report-settings' );

			$enabled   = $this->input_get( 'enabled', '' );
			$email_ids = $this->input_get( 'email_ids', array() );

			if ( ! empty( $enabled ) ) {
				$enabled = in_array( $enabled, array( 'enable', 'disable' ), true ) ? $enabled : 'enable';
				update_option( 'cartflows_stats_report_emails', $enabled );
			}

			if ( ! empty( $email_ids ) && is_array( $email_ids ) ) {
				$sanitized = array_values( array_filter( array_map( 'sanitize_email', $email_ids ) ) );
				$email_str = implode( "\n", $sanitized );
				update_option( 'cartflows_stats_report_email_ids', $email_str );
			}

			// Read back saved values.
			$saved_enabled   = get_option( 'cartflows_stats_report_emails', 'enable' );
			$saved_email_raw = get_option( 'cartflows_stats_report_email_ids', get_option( 'admin_email' ) );
			$saved_email_ids = array();
			if ( ! empty( $saved_email_raw ) ) {
				$saved_email_ids = array_values( array_filter( array_map( 'trim', preg_split( "/[\f\r\n]+/", $saved_email_raw ) ) ) );
			}

			return array(
				'enabled'   => esc_html( $saved_enabled ),
				'email_ids' => array_map( 'sanitize_email', $saved_email_ids ),
				'message'   => esc_html__( 'Email report settings updated.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Woo Dynamic Flow
	// ============================================================

	/**
	 * Get flow mapping for a single WooCommerce product.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function get_product_flow_mapping( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'get-product-flow-mapping' );

			if ( ! class_exists( 'WooCommerce' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is not active.', 'cartflows' ) );
			}

			$product_id = $this->input_get( 'product_id' );
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				throw new \Exception( esc_html__( 'Invalid product ID.', 'cartflows' ) );
			}

			$flow_id          = intval( $product->get_meta( 'cartflows_redirect_flow_id' ) );
			$flow_title       = '';
			$add_to_cart_text = $product->get_meta( 'cartflows_add_to_cart_text' );

			if ( ! empty( $flow_id ) && get_post_type( $flow_id ) === CARTFLOWS_FLOW_POST_TYPE ) {
				$flow_title = get_the_title( $flow_id );
			}

			return array(
				'product_id'       => intval( $product_id ),
				'product_title'    => esc_html( $product->get_name() ),
				'flow_id'          => $flow_id,
				'flow_title'       => esc_html( $flow_title ),
				'add_to_cart_text' => esc_html( wp_unslash( $add_to_cart_text ) ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * List all WooCommerce products that have a flow mapping.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function list_product_flow_mappings( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'list-product-flow-mappings' );

			if ( ! class_exists( 'WooCommerce' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is not active.', 'cartflows' ) );
			}

			$page     = $this->clamp_page( $this->input_get( 'paged' ) );
			$per_page = $this->clamp_per_page( $this->input_get( 'per_page' ) );

			$query = new \WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => $per_page,
					'paged'          => $page,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => 'cartflows_redirect_flow_id',
							'value'   => array( '', '0' ),
							'compare' => 'NOT IN',
						),
					),
					'fields'         => 'ids',
				)
			);

			$mappings = array();

			if ( ! empty( $query->posts ) ) {
				foreach ( $query->posts as $pid ) {
					$product = wc_get_product( $pid );
					if ( ! $product ) {
						continue;
					}

					$flow_id    = intval( $product->get_meta( 'cartflows_redirect_flow_id' ) );
					$flow_title = '';
					if ( ! empty( $flow_id ) && get_post_type( $flow_id ) === CARTFLOWS_FLOW_POST_TYPE ) {
						$flow_title = get_the_title( $flow_id );
					}

					$mappings[] = array(
						'product_id'       => intval( $pid ),
						'product_title'    => esc_html( $product->get_name() ),
						'flow_id'          => $flow_id,
						'flow_title'       => esc_html( $flow_title ),
						'add_to_cart_text' => esc_html( wp_unslash( $product->get_meta( 'cartflows_add_to_cart_text' ) ) ),
					);
				}
			}

			$total       = intval( $query->found_posts );
			$total_pages = intval( $query->max_num_pages );

			return array(
				'mappings'    => $mappings,
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => $page,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Update flow mapping on a WooCommerce product.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function update_product_flow_mapping( $input ) {

		try {

			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'update-product-flow-mapping' );

			if ( ! class_exists( 'WooCommerce' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is not active.', 'cartflows' ) );
			}

			$product_id = $this->input_get( 'product_id' );
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				throw new \Exception( esc_html__( 'Invalid product ID.', 'cartflows' ) );
			}

			$flow_id          = $this->input_get( 'flow_id', '' );
			$add_to_cart_text = $this->input_get( 'add_to_cart_text', '' );

			if ( '' !== $flow_id ) {
				$flow_id = intval( $flow_id );

				// Validate flow exists if non-zero.
				if ( $flow_id > 0 && get_post_type( $flow_id ) !== CARTFLOWS_FLOW_POST_TYPE ) {
					throw new \Exception( esc_html__( 'Invalid flow ID. Flow does not exist.', 'cartflows' ) );
				}

				$product->update_meta_data( 'cartflows_redirect_flow_id', $flow_id );
			}

			if ( '' !== $add_to_cart_text ) {
				$product->update_meta_data( 'cartflows_add_to_cart_text', sanitize_text_field( $add_to_cart_text ) );
			}

			$product->save();

			// Read back saved values.
			$saved_flow_id    = intval( $product->get_meta( 'cartflows_redirect_flow_id' ) );
			$saved_flow_title = '';
			if ( ! empty( $saved_flow_id ) && get_post_type( $saved_flow_id ) === CARTFLOWS_FLOW_POST_TYPE ) {
				$saved_flow_title = get_the_title( $saved_flow_id );
			}
			$saved_button_text = $product->get_meta( 'cartflows_add_to_cart_text' );

			return array(
				'product_id'       => intval( $product_id ),
				'flow_id'          => $saved_flow_id,
				'flow_title'       => esc_html( $saved_flow_title ),
				'add_to_cart_text' => esc_html( wp_unslash( $saved_button_text ) ),
				'message'          => esc_html__( 'Product flow mapping updated.', 'cartflows' ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Flow Creation / Import
	// ============================================================

	/**
	 * Create a new empty funnel.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function create_flow( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'create-flow' );

			$title  = $this->input_get( 'title' );
			$status = $this->input_get( 'status' );

			if ( empty( $title ) ) {
				throw new \Exception( esc_html__( 'Funnel title cannot be empty.', 'cartflows' ) );
			}

			$new_flow_id = wp_insert_post(
				array(
					'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
					'post_title'   => sanitize_text_field( $title ),
					'post_status'  => $status,
					'post_content' => '',
				)
			);

			if ( is_wp_error( $new_flow_id ) || ! $new_flow_id ) {
				throw new \Exception( esc_html__( 'Failed to create funnel.', 'cartflows' ) );
			}

			update_post_meta( $new_flow_id, 'wcf-steps', array() );

			return array(
				'flow_id'  => intval( $new_flow_id ),
				'title'    => esc_html( get_the_title( $new_flow_id ) ),
				'status'   => esc_html( get_post_status( $new_flow_id ) ),
				'edit_url' => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $new_flow_id ) ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	/**
	 * Create a new step and attach it to a funnel.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function create_step( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'create-step' );

			$flow_id = $this->input_get( 'flow_id' );
			$title   = $this->input_get( 'title' );
			$type    = $this->input_get( 'type' );

			if ( 0 === $flow_id ) {
				throw new \Exception( esc_html__( 'Invalid funnel ID.', 'cartflows' ) );
			}

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
				throw new \Exception( esc_html__( 'Funnel not found.', 'cartflows' ) );
			}

			if ( empty( $title ) ) {
				throw new \Exception( esc_html__( 'Step title cannot be empty.', 'cartflows' ) );
			}

			// Upsell/downsell require CartFlows Pro.
			if ( in_array( $type, array( 'upsell', 'downsell' ), true ) ) {
				throw new \Exception( esc_html__( 'Upsell and Downsell steps require CartFlows Pro.', 'cartflows' ) );
			}

			$new_step_id = wp_insert_post(
				array(
					'post_type'   => CARTFLOWS_STEP_POST_TYPE,
					'post_title'  => sanitize_text_field( $title ),
					'post_status' => 'publish',
				)
			);

			if ( is_wp_error( $new_step_id ) || ! $new_step_id ) {
				throw new \Exception( esc_html__( 'Failed to create step.', 'cartflows' ) );
			}

			// Set step meta.
			update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );
			update_post_meta( $new_step_id, 'wcf-step-type', $type );

			// Set taxonomy terms.
			wp_set_object_terms( $new_step_id, $type, CARTFLOWS_TAXONOMY_STEP_TYPE );
			wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

			// Append to flow's wcf-steps meta.
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => intval( $new_step_id ),
				'title' => sanitize_text_field( $title ),
				'type'  => $type,
			);

			if ( method_exists( '\Cartflows_Helper', 'get_instance' ) ) {
				$flow_steps = \Cartflows_Helper::get_instance()->maybe_update_flow_steps( $flow_id, $flow_steps );
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );
			AdminHelper::clear_cache();

			return array(
				'step_id'  => intval( $new_step_id ),
				'flow_id'  => intval( $flow_id ),
				'title'    => esc_html( get_the_title( $new_step_id ) ),
				'type'     => esc_html( $type ),
				'edit_url' => esc_url( admin_url( 'admin.php?page=cartflows&path=flows&action=wcf-edit-flow&flow_id=' . $flow_id ) ),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Templates Browse
	// ============================================================

	/**
	 * List available flow templates from the cached library.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function list_flow_templates( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'list-flow-templates' );

			$type         = $this->input_get( 'type' );
			$page_builder = $this->input_get( 'page_builder' );
			$step_type    = $this->input_get( 'step_type' );
			$category     = $this->input_get( 'category' );
			$search       = $this->input_get( 'search' );
			$page         = $this->clamp_page( $this->input_get( 'page' ) );
			$per_page     = $this->clamp_per_page( $this->input_get( 'per_page' ) );

			// Use provided page builder or fall back to the default.
			if ( empty( $page_builder ) ) {
				$page_builder = \Cartflows_Helper::get_common_setting( 'default_page_builder' );
			}

			$all_flows = \Cartflows_Helper::get_instance()->get_flows_and_steps( sanitize_text_field( $page_builder ) );

			if ( ! is_array( $all_flows ) ) {
				$all_flows = array();
			}

			$templates = array();

			foreach ( $all_flows as $flow ) {
				$flow = (array) $flow;

				$flow_id    = isset( $flow['ID'] ) ? intval( $flow['ID'] ) : 0;
				$flow_title = isset( $flow['title'] ) ? $flow['title'] : '';
				$flow_type  = isset( $flow['type'] ) ? $flow['type'] : '';
				$is_pro     = 'pro' === $flow_type;
				$thumbnail  = isset( $flow['featured_image_url'] ) ? $flow['featured_image_url'] : '';
				$pb         = isset( $flow['page_builder'] ) ? $flow['page_builder'] : $page_builder;
				$categories = isset( $flow['category'] ) ? (array) $flow['category'] : array();

				// Get step types in this flow.
				$flow_steps      = isset( $flow['steps'] ) ? (array) $flow['steps'] : array();
				$flow_step_types = array();
				foreach ( $flow_steps as $step ) {
					$step = (array) $step;
					if ( isset( $step['type'] ) ) {
						$flow_step_types[] = $step['type'];
					}
				}

				// Filter by type (free/pro) if provided.
				if ( ! empty( $type ) && $flow_type !== $type ) {
					continue;
				}

				// Filter by step type if provided.
				if ( ! empty( $step_type ) && ! in_array( $step_type, $flow_step_types, true ) ) {
					continue;
				}

				// Filter by category if provided.
				if ( ! empty( $category ) && ! in_array( $category, $categories, true ) ) {
					continue;
				}

				// Filter by search keyword if provided.
				if ( ! empty( $search ) && false === stripos( $flow_title, $search ) ) {
					continue;
				}

				$templates[] = array(
					'id'            => $flow_id,
					'title'         => esc_html( $flow_title ),
					'type'          => esc_html( $flow_type ),
					'page_builder'  => esc_html( $pb ),
					'is_pro'        => $is_pro,
					'categories'    => array_map( 'sanitize_text_field', $categories ),
					'step_count'    => count( $flow_steps ),
					'step_types'    => array_map( 'sanitize_text_field', $flow_step_types ),
					'thumbnail_url' => esc_url( (string) $thumbnail ),
				);
			}

			$total  = count( $templates );
			$offset = ( $page - 1 ) * $per_page;
			$paged  = array_slice( $templates, $offset, $per_page );

			return array(
				'templates'     => $paged,
				'total'         => $total,
				'page'          => $page,
				'per_page'      => $per_page,
				'pro_available' => _is_cartflows_pro(),
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Execute Callbacks — Templates Import
	// ============================================================

	/**
	 * Import a flow template from the CartFlows cloud library.
	 *
	 * @param array $input Validated input.
	 * @return array
	 * @throws \Exception On failure.
	 */
	public function import_flow_template( $input ) {

		try {
			$this->init( $input, CARTFLOWS_ABILITY_API_NAMESPACE . 'import-flow-template' );

			if ( ! function_exists( 'WC' ) ) {
				throw new \Exception( esc_html__( 'WooCommerce is required to import flow templates.', 'cartflows' ) );
			}

			$template_id = $this->input_get( 'template_id' );

			if ( 0 === $template_id ) {
				throw new \Exception( esc_html__( 'Invalid template ID.', 'cartflows' ) );
			}

			// Fetch the flow template from the CartFlows cloud API.
			$response = \CartFlows_API::get_instance()->get_flow( $template_id );

			if ( ! isset( $response['success'] ) || ! $response['success'] ) {
				$message = isset( $response['message'] ) ? $response['message'] : __( 'Failed to fetch template from CartFlows library.', 'cartflows' );
				throw new \Exception( esc_html( $message ) );
			}

			// Check for API errors.
			$is_error = AdminHelper::has_api_error( $response['data'] );
			if ( $is_error['error'] ) {
				throw new \Exception( esc_html( $is_error['error_message'] ) );
			}

			// Check licence status for Pro templates.
			$license_status = isset( $response['data']['licence_status'] ) ? $response['data']['licence_status'] : '';

			if ( 'valid' !== $license_status && ! _is_cartflows_pro() ) {
				throw new \Exception( esc_html__( 'This template requires CartFlows Pro. Please upgrade to import premium templates.', 'cartflows' ) );
			}

			// Extract the flow title.
			$flow_title = isset( $response['title'] ) ? sanitize_text_field( $response['title'] ) : __( 'Imported Flow', 'cartflows' );

			// Create the flow post.
			$new_flow_id = wp_insert_post(
				array(
					'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
					'post_title'   => $flow_title,
					'post_status'  => 'publish',
					'post_content' => '',
				)
			);

			if ( is_wp_error( $new_flow_id ) || ! $new_flow_id ) {
				throw new \Exception( esc_html__( 'Failed to create funnel from template.', 'cartflows' ) );
			}

			update_post_meta( $new_flow_id, 'wcf-steps', array() );

			// Import flow post meta if available.
			if ( ! empty( $response['post_meta'] ) ) {
				$exclude_meta_keys = \Cartflows_Helper::get_instance()->get_meta_keys_to_exclude_from_import( $new_flow_id );
				foreach ( (array) $response['post_meta'] as $meta_key => $meta_value ) {
					if ( in_array( $meta_key, $exclude_meta_keys, true ) ) {
						continue;
					}
					$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';
					if ( $meta_value ) {
						if ( is_serialized( $meta_value, true ) ) {
							$raw_data = unserialize( stripslashes( $meta_value ), array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
							if ( false === $raw_data || is_object( $raw_data ) ) {
								$raw_data = '';
							}
						} else {
							$raw_data = $meta_value;
						}
						update_post_meta( $new_flow_id, $meta_key, $raw_data );
					}
				}
			}

			// Process steps from the template.
			// Get the steps list from the locally cached flows_and_steps data,
			// matching how the working AJAX import_flow() method gets steps from the frontend.
			// The remote API response does not contain steps in a reliably iterable format.
			$steps_raw      = array();
			$all_flows_data = \Cartflows_Helper::get_instance()->get_flows_and_steps();

			if ( ! empty( $all_flows_data ) && is_array( $all_flows_data ) ) {
				foreach ( $all_flows_data as $cached_flow ) {
					$cached_flow    = (array) $cached_flow;
					$cached_flow_id = isset( $cached_flow['ID'] ) ? absint( $cached_flow['ID'] ) : 0;

					if ( $cached_flow_id === $template_id ) {
						if ( isset( $cached_flow['steps'] ) ) {
							// Convert each step from stdClass to array if needed.
							foreach ( $cached_flow['steps'] as $cached_step ) {
								$steps_raw[] = (array) $cached_step;
							}
						}
						break;
					}
				}
			}

			// Fallback: try the remote API response if cached data did not yield steps.
			if ( empty( $steps_raw ) ) {
				$template_data = isset( $response['data'] ) ? $response['data'] : array();

				// cartflows_flow_flow may be a stdClass from json_decode, so cast it.
				if ( isset( $template_data['cartflows_flow_flow'] ) && ! empty( $template_data['cartflows_flow_flow'] ) ) {
					foreach ( (array) $template_data['cartflows_flow_flow'] as $api_step ) {
						$steps_raw[] = (array) $api_step;
					}
				}
			}

			$imported_steps = array();
			$skipped_steps  = array();
			$importer       = \CartFlows_Importer::get_instance();

			// Set the batch import flag before processing steps.
			\CartFlows_Batch_Process::set_is_wcf_template_import( true );

			foreach ( $steps_raw as $step ) {
				$step = (array) $step;

				$step_remote_id = isset( $step['ID'] ) ? absint( $step['ID'] ) : ( isset( $step['id'] ) ? absint( $step['id'] ) : 0 );
				$step_title     = isset( $step['title'] ) ? sanitize_text_field( $step['title'] ) : '';
				$step_type      = isset( $step['type'] ) ? sanitize_text_field( $step['type'] ) : '';

				// Skip upsell/downsell if Pro is not active.
				if ( in_array( $step_type, array( 'upsell', 'downsell' ), true ) && ( ! _is_cartflows_pro() || is_wcf_starter_plan() ) ) {
					$skipped_steps[] = array(
						'title'  => esc_html( $step_title ),
						'type'   => esc_html( $step_type ),
						'reason' => esc_html__( 'Upsell and Downsell steps require CartFlows Pro.', 'cartflows' ),
					);
					continue;
				}

				if ( empty( $step_remote_id ) ) {
					continue;
				}

				// Use the importer to create the step locally.
				$new_step_id = $importer->create_step( $new_flow_id, $step_type, $step_title );

				if ( ! empty( $new_step_id ) && ! is_wp_error( $new_step_id ) ) {
					// Fetch and import the step template content.
					$step_response = \CartFlows_API::get_instance()->get_template( $step_remote_id );

					if ( isset( $step_response['success'] ) && $step_response['success'] ) {
						// Handle Divi content.
						if ( 'divi' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
							if ( isset( $step_response['data']['divi_content'] ) && ! empty( $step_response['data']['divi_content'] ) ) {
								update_post_meta( $new_step_id, 'divi_content', $step_response['data']['divi_content'] );
								wp_update_post(
									array(
										'ID'           => $new_step_id,
										'post_content' => $step_response['data']['divi_content'],
									)
								);
							}
						}

						// Handle Gutenberg content.
						if ( 'gutenberg' === \Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
							if ( isset( $step_response['data']['divi_content'] ) && ! empty( $step_response['data']['divi_content'] ) ) {
								wp_update_post(
									array(
										'ID'           => $new_step_id,
										'post_content' => $step_response['data']['divi_content'],
									)
								);
							}
						}

						// Import step post meta.
						if ( ! empty( $step_response['post_meta'] ) ) {
							$step_exclude_keys = \Cartflows_Helper::get_instance()->get_meta_keys_to_exclude_from_import( $new_step_id );
							foreach ( (array) $step_response['post_meta'] as $meta_key => $meta_value ) {
								if ( in_array( $meta_key, $step_exclude_keys, true ) ) {
									continue;
								}
								$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';
								if ( $meta_value ) {
									if ( is_serialized( $meta_value, true ) ) {
										$raw_data = unserialize( stripslashes( $meta_value ), array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
										if ( false === $raw_data || is_object( $raw_data ) ) {
											$raw_data = '';
										}
									} elseif ( '_elementor_data' === $meta_key ) {
										$raw_data = is_array( $meta_value ) ? wp_slash( wp_json_encode( $meta_value ) ) : wp_slash( $meta_value );
									} else {
										$raw_data = $meta_value;
									}
									update_post_meta( $new_step_id, $meta_key, $raw_data );
								}
							}
						}

						update_post_meta( $new_step_id, 'cartflows_imported_step', 'yes' );

						do_action( 'cartflows_import_complete' );
						do_action( 'cartflows_after_template_import', $new_step_id, $step_response );
					}

					$imported_steps[] = array(
						'step_id' => intval( $new_step_id ),
						'title'   => esc_html( get_the_title( $new_step_id ) ),
						'type'    => esc_html( $step_type ),
					);
				}
			}

			// Reset the batch import flag after processing steps.
			\CartFlows_Batch_Process::set_is_wcf_template_import( false );

			return array(
				'flow_id'       => intval( $new_flow_id ),
				'title'         => esc_html( get_the_title( $new_flow_id ) ),
				'steps'         => $imported_steps,
				'skipped_steps' => $skipped_steps,
			);

		} catch ( \Exception $e ) {
			return $this->error( $e );
		}
	}

	// ============================================================
	// Private Helpers
	// ============================================================

	/**
	 * Set the post_status on a flow and all its steps.
	 *
	 * @param int    $flow_id   Flow post ID.
	 * @param string $new_status New post status ('publish', 'draft', etc.).
	 * @return void
	 */
	private function set_flow_status( $flow_id, $new_status ) {

		$steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( is_array( $steps ) ) {
			foreach ( $steps as $step ) {
				wp_update_post(
					array(
						'ID'          => intval( $step['id'] ),
						'post_status' => $new_status,
					)
				);
			}
		}

		wp_update_post(
			array(
				'ID'          => $flow_id,
				'post_status' => $new_status,
			)
		);
	}

	// ============================================================
	// Base Helpers (required by template)
	// ============================================================

	/**
	 * Initialize input parsing for the given ability.
	 *
	 * @param array  $input        Raw input.
	 * @param string $ability_name Fully-qualified ability name.
	 * @return void
	 */
	public function init( $input, $ability_name ) {
		$this->input_parse( $input, $ability_name );
	}

	/**
	 * Check whether the current user has the given capability/capabilities.
	 *
	 * @param string|array $caps Single capability string or array (AND logic).
	 * @return bool
	 */
	public function permission_callback( $caps ) {

		if ( empty( $caps ) ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( ! $user || 0 === $user->ID ) {
			return false;
		}

		if ( is_string( $caps ) ) {
			return (bool) $user->has_cap( $caps );
		}

		if ( is_array( $caps ) ) {
			foreach ( $caps as $cap ) {
				if ( ! $user->has_cap( $cap ) ) {
					return false;
				}
			}
			return true;
		}

		return false;
	}

	/**
	 * Parse and validate input against the ability's input_schema.
	 *
	 * Applies defaults, coerces types, sanitizes, and validates enums.
	 *
	 * @param array  $input        Raw input.
	 * @param string $ability_name Fully-qualified ability name.
	 * @return array Parsed input.
	 * @throws \Exception On failure.
	 */
	public function input_parse( $input, $ability_name ) {

		$this->input = array();

		if ( is_a( $input, 'WP_REST_Request' ) ) {
			$input = $input->get_json_params();
			if ( ! is_array( $input ) ) {
				$input = array();
			}
		}

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$input_schema = Cartflows_Ability_Config::get_ability_input_schema( $ability_name );
		if ( ! is_array( $input_schema ) || empty( $input_schema ) ) {
			return array();
		}

		if ( ! isset( $input_schema['properties'] ) || ! is_array( $input_schema['properties'] ) ) {
			return array();
		}

		// Required fields: array at object level per JSON Schema standard.
		$required_fields = ( isset( $input_schema['required'] ) && is_array( $input_schema['required'] ) )
			? $input_schema['required']
			: array();

		foreach ( $input_schema['properties'] as $name => $prop ) {

			$type      = isset( $prop['type'] ) ? strtolower( $prop['type'] ) : 'string';
			$raw_value = array_key_exists( $name, $input ) ? $input[ $name ] : null;

			// Required check BEFORE defaults.
			$is_required = in_array( $name, $required_fields, true );
			if ( $is_required && ( null === $raw_value || '' === $raw_value ) ) {
				throw new \Exception(
					sprintf(
						/* translators: %s: field name */
						esc_html__( 'Required field %s is missing.', 'cartflows' ),
						esc_html( $name )
					)
				);
			}

			// Apply schema default if value not provided.
			if ( null === $raw_value && isset( $prop['default'] ) ) {
				$raw_value = $prop['default'];
			}

			// Type-appropriate zero value if still null.
			if ( null === $raw_value ) {
				switch ( $type ) {
					case 'integer':
						$raw_value = 0;
						break;
					case 'number':
						$raw_value = 0.0;
						break;
					case 'boolean':
						$raw_value = false;
						break;
					case 'array':
						$raw_value = array();
						break;
					case 'object':
						$raw_value = array();
						break;
					default:
						$raw_value = '';
						break;
				}
			}

			$value = $raw_value;

			// Type coercion and sanitization.
			switch ( $type ) {
				case 'integer':
					$value = intval( $value );
					break;
				case 'number':
					$value = floatval( $value );
					break;
				case 'boolean':
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
					break;
				case 'string':
					$value = is_string( $value )
						? sanitize_text_field( $value )
						: sanitize_text_field( strval( $value ) );
					break;
				case 'array':
					if ( ! is_array( $value ) ) {
						$value = array();
					}
					$value = $this->sanitize_recursive( $value );
					break;
				case 'object':
					if ( ! is_array( $value ) && ! is_object( $value ) ) {
						$value = array();
					}
					if ( is_object( $value ) ) {
						$value = (array) $value;
					}
					$value = $this->sanitize_recursive( $value );
					break;
			}

			// Enum validation.
			if ( isset( $prop['enum'] ) && is_array( $prop['enum'] ) ) {
				if ( ! in_array( $value, $prop['enum'], true ) ) {
					throw new \Exception(
						sprintf(
							/* translators: %s: field name */
							esc_html__( 'Invalid value for %s.', 'cartflows' ),
							esc_html( $name )
						)
					);
				}
			}

			$this->input[ $name ] = $value;
		}

		return $this->input;
	}

	/**
	 * Get a parsed input value by name.
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Sentinel: omit to throw on missing; pass a value to use as fallback.
	 * @return mixed
	 * @throws \Exception On failure.
	 */
	public function input_get( $name, $default = '__CARTFLOWS_NO_DEFAULT__' ) {

		if ( false === $this->input ) {
			throw new \Exception( esc_html__( 'Inputs not parsed.', 'cartflows' ) );
		}

		if ( ! array_key_exists( $name, $this->input ) ) {
			if ( '__CARTFLOWS_NO_DEFAULT__' !== $default ) {
				return $default;
			}
			throw new \Exception(
				sprintf(
					/* translators: %s: property name */
					esc_html__( 'Property %s not found.', 'cartflows' ),
					esc_html( $name )
				)
			);
		}

		return $this->input[ $name ];
	}

	/**
	 * Recursively sanitize an array.
	 *
	 * @param array $data Data to sanitize.
	 * @return array
	 */
	protected function sanitize_recursive( $data ) {

		if ( ! is_array( $data ) ) {
			return $data;
		}

		$sanitized = array();
		foreach ( $data as $key => $value ) {
			$key = sanitize_text_field( strval( $key ) );
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_recursive( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} elseif ( is_int( $value ) ) {
				$sanitized[ $key ] = intval( $value );
			} elseif ( is_float( $value ) ) {
				$sanitized[ $key ] = floatval( $value );
			} elseif ( is_bool( $value ) ) {
				$sanitized[ $key ] = (bool) $value;
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Build a structured error response.
	 *
	 * Only exposes file/line info when WP_DEBUG is enabled.
	 *
	 * @param \Exception $e The exception.
	 * @return array
	 */
	public function error( $e ) {

		$error = array(
			'error' => array(
				'code'    => 'cartflows_error',
				'message' => esc_html( $e->getMessage() ),
			),
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$error['error']['debug'] = array(
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			);
		}

		return $error;
	}

	/**
	 * Clamp per_page to safe bounds (1–$max).
	 *
	 * @param int $per_page Raw per_page value.
	 * @param int $max      Maximum allowed (default 100).
	 * @return int
	 */
	protected function clamp_per_page( $per_page, $max = 100 ) {
		return max( 1, min( intval( $per_page ), $max ) );
	}

	/**
	 * Clamp page to minimum 1.
	 *
	 * @param int $page Raw page value.
	 * @return int
	 */
	protected function clamp_page( $page ) {
		return max( 1, intval( $page ) );
	}
}
