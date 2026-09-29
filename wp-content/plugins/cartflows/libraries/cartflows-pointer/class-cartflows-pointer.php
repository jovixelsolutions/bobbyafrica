<?php
/**
 * Cartflows Pointer Library.
 *
 * A lightweight library for WordPress admin pointers.
 *
 * @version 1.0.0
 * @package cartflows-pointer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pointer' ) ) {

	/**
	 * Cartflows Pointer
	 */
	class Cartflows_Pointer {

		/**
		 * Instance
		 *
		 * @var Cartflows_Pointer|null
		 */
		private static $instance = null;

		/**
		 * Pointer configuration.
		 *
		 * @var array<string, mixed>
		 */
		private $config = array();

		/**
		 * Library URL.
		 *
		 * @var string
		 */
		private $library_url = '';

		/**
		 * Library version.
		 *
		 * @var string
		 */
		private $version = '1.0.0';

		/**
		 * Get Instance
		 *
		 * @return Cartflows_Pointer
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'admin_init', array( $this, 'setup' ) );
		}

		/**
		 * Setup the pointer configuration.
		 *
		 * @return void
		 */
		public function setup() {
			/**
			 * Filter to provide pointer configuration.
			 *
			 * @param array $config Pointer configuration array.
			 */
			$config = apply_filters( 'cartflows_pointer_config', array() );

			if ( empty( $config ) ) {
				return;
			}

			$this->library_url = $this->get_library_url( __DIR__ );
			$this->config      = $this->parse_config( $config );

			$this->init_hooks();
		}

		/**
		 * Get library URL from path.
		 *
		 * @param string $path Library path.
		 * @return string Library URL.
		 */
		private function get_library_url( $path ) {
			$content_dir = wp_normalize_path( WP_CONTENT_DIR );
			$path        = wp_normalize_path( $path );
			return str_replace( $content_dir, content_url(), $path );
		}

		/**
		 * Parse and validate configuration with defaults.
		 *
		 * @param array<string, mixed> $config Raw configuration.
		 * @return array<string, mixed> Parsed configuration.
		 */
		private function parse_config( $config ) {
			$defaults = array(
				'option_name'     => 'cartflows_pointer_data',
				'title'           => '',
				'content'         => '',
				'button_text'     => __( 'Get Started', 'cartflows' ),
				'button_url'      => admin_url(),
				'dismiss_text'    => __( 'Dismiss', 'cartflows' ),
				'target_selector' => '#menu-plugins',
				'fallback_target' => '#menu-plugins',
				'allowed_pages'   => array( 'index.php' ),
				'post_type'       => null,
				'max_posts'       => 1,
				'capability'      => 'cartflows_manage_flows_steps',
				'position'        => array(
					'edge'  => 'left',
					'align' => 'center',
				),
			);

			return wp_parse_args( $config, $defaults );
		}

		/**
		 * Initialize hooks.
		 *
		 * @return void
		 */
		private function init_hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pointer_assets' ) );
			add_action( 'wp_ajax_cartflows_pointer_should_show', array( $this, 'ajax_should_show' ) );
			add_action( 'wp_ajax_cartflows_pointer_dismiss', array( $this, 'ajax_dismiss' ) );
			add_action( 'wp_ajax_cartflows_pointer_accept', array( $this, 'ajax_accept' ) );
		}

		/**
		 * Determine if the pointer should be displayed.
		 *
		 * @return bool
		 */
		private function should_display() {
			global $pagenow;

			// Check user capability.
			if ( ! current_user_can( $this->get_capability() ) ) {
				return false;
			}

			// Check if already dismissed or accepted.
			$pointer_data = $this->get_pointer_data();
			if ( ! empty( $pointer_data['dismissed'] ) || ! empty( $pointer_data['accepted'] ) ) {
				return false;
			}

			// Check post count if post_type is specified.
			if ( ! empty( $this->config['post_type'] ) && is_string( $this->config['post_type'] ) ) {
				$count      = wp_count_posts( $this->config['post_type'] );
				$post_count = isset( $count->publish ) ? (int) $count->publish : 0;
				$max_posts  = isset( $this->config['max_posts'] ) ? $this->config['max_posts'] : 1;

				if ( $post_count > $max_posts ) {
					return false;
				}
			}

			// Check allowed pages.
			$allowed_pages = isset( $this->config['allowed_pages'] ) && is_array( $this->config['allowed_pages'] )
				? $this->config['allowed_pages']
				: array();

			if ( ! in_array( $pagenow, $allowed_pages, true ) ) {
				return false;
			}

			// Extra check for admin.php?page=cartflows
			if ( 'admin.php' === $pagenow ) {
				if ( empty( $_GET['page'] ) || 'cartflows' !== sanitize_key( $_GET['page'] ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Enqueue pointer assets.
		 *
		 * @return void
		 */
		public function enqueue_pointer_assets() {
			if ( ! $this->should_display() ) {
				return;
			}

			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );

			wp_enqueue_script(
				'cartflows-pointer',
				$this->library_url . '/assets/js/cartflows-pointer.js',
				array( 'wp-pointer', 'jquery' ),
				$this->version,
				true
			);

			wp_localize_script(
				'cartflows-pointer',
				'cartflowsPointerData',
				array(
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( 'cartflows_pointer_nonce' ),
					'target_selector' => $this->config['target_selector'],
					'fallback_target' => $this->config['fallback_target'],
					'position'        => $this->config['position'],
				)
			);
		}

		/**
		 * AJAX handler: Check if pointer should be shown.
		 *
		 * @return void
		 */
		public function ajax_should_show() {
			// Security: Check capability.
			if ( ! current_user_can( $this->get_capability() ) ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'cartflows' ) ), 403 );
			}

			// Security: Verify nonce.
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cartflows_pointer_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'cartflows' ) ), 403 );
			}

			wp_send_json(
				array(
					'show'        => true,
					'title'       => $this->config['title'],
					'content'     => $this->config['content'],
					'button_text' => $this->config['button_text'],
					'button_url'  => $this->config['button_url'],
					'dismiss'     => $this->config['dismiss_text'],
				)
			);
		}

		/**
		 * AJAX handler: Pointer dismissed.
		 *
		 * @return void
		 */
		public function ajax_dismiss() {
			// Security: Check capability.
			if ( ! current_user_can( $this->get_capability() ) ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'cartflows' ) ), 403 );
			}

			// Security: Verify nonce.
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cartflows_pointer_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'cartflows' ) ), 403 );
			}

			$this->update_pointer_data( 'dismissed', time() );
			wp_send_json_success();
		}

		/**
		 * AJAX handler: Pointer CTA accepted.
		 *
		 * @return void
		 */
		public function ajax_accept() {
			// Security: Check capability.
			if ( ! current_user_can( $this->get_capability() ) ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'cartflows' ) ), 403 );
			}

			// Security: Verify nonce.
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cartflows_pointer_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'cartflows' ) ), 403 );
			}

			$this->update_pointer_data( 'accepted', time() );
			wp_send_json_success();
		}

		/**
		 * Get capability from config.
		 *
		 * @return string
		 */
		private function get_capability() {
			return isset( $this->config['capability'] ) && is_string( $this->config['capability'] )
				? $this->config['capability']
				: 'manage_options';
		}

		/**
		 * Get pointer data from options.
		 *
		 * @return array<string, mixed>
		 */
		private function get_pointer_data() {
			$option_name = isset( $this->config['option_name'] ) && is_string( $this->config['option_name'] )
				? $this->config['option_name']
				: 'cartflows_pointer_data';
			$data        = get_option( $option_name, array() );
			return is_array( $data ) ? $data : array();
		}

		/**
		 * Update pointer data in options.
		 *
		 * @param string $key   Data key.
		 * @param mixed  $value Data value.
		 * @return void
		 */
		private function update_pointer_data( $key, $value ) {
			$option_name = isset( $this->config['option_name'] ) && is_string( $this->config['option_name'] )
				? $this->config['option_name']
				: 'cartflows_pointer_data';
			$data         = $this->get_pointer_data();
			$data[ $key ] = $value;
			update_option( $option_name, $data );
		}
	}

	// Self-initialize.
	Cartflows_Pointer::get_instance();
}
