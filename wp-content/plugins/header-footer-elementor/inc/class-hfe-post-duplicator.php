<?php
/**
 * Post Duplicator functionality.
 *
 * @package header-footer-elementor
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Post_Duplicator
 *
 * Handles the post duplication functionality.
 *
 * @since 2.8.0
 */
class HFE_Post_Duplicator {

	/**
	 * Instance of HFE_Post_Duplicator
	 *
	 * @since 2.8.0
	 * @var HFE_Post_Duplicator
	 */
	private static $instance = null;

	/**
	 * Supported post types for duplication.
	 *
	 * @since 2.8.0
	 * @var array
	 */
	private $excluded_post_types = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_global_styles',
	);

	/**
	 * Instance of HFE_Post_Duplicator
	 *
	 * @since 2.8.0
	 * @return HFE_Post_Duplicator Instance of HFE_Post_Duplicator
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 2.8.0
	 */
	private function __construct() {
		// Support for all post types.
		add_action( 'admin_init', array( $this, 'add_post_type_filters' ) );

		// Handle the duplicate action.
		add_action( 'admin_action_hfe_duplicate_post', array( $this, 'duplicate_post' ) );

		// Add admin notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Enqueue admin styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Add filters for all post types.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function add_post_type_filters() {
		// Get all public post types.
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		// Add filter for each post type.
		foreach ( $post_types as $post_type ) {
			add_filter( $post_type . '_row_actions', array( $this, 'add_duplicate_link' ), 10, 2 );
		}
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function enqueue_admin_styles() {
		$screen = get_current_screen();

		// Enqueue on all post listing screens.
		if ( $screen && 'edit' === $screen->base ) {
			wp_enqueue_style(
				'hfe-post-duplicator',
				HFE_URL . 'assets/css/admin-post-duplicator.css',
				array(),
				HFE_VER
			);
		}
	}

	/**
	 * Add duplicate link to row actions.
	 *
	 * @since 2.8.0
	 * @param array   $actions Row actions.
	 * @param WP_Post $post    Post object.
	 * @return array Modified row actions.
	 */
	public function add_duplicate_link( $actions, $post ) {
		// Check if post type is excluded.
		if ( in_array( $post->post_type, $this->excluded_post_types, true ) ) {
			return $actions;
		}

		// Check if user has permission to edit posts.
		if ( ! $this->user_can_duplicate( $post ) ) {
			return $actions;
		}

		// Create nonce for security.
		$nonce = wp_create_nonce( 'hfe_duplicate_post_' . $post->ID );

		// Create duplicate link.
		$duplicate_link = admin_url(
			'admin.php?action=hfe_duplicate_post&post=' . absint( $post->ID ) . '&nonce=' . $nonce
		);

		$short_name = defined( 'UAEL_PLUGIN_SHORT_NAME' )
			? UAEL_PLUGIN_SHORT_NAME
			: 'UAE';

		// Add duplicate link to actions.
		$actions['hfe_duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $duplicate_link ),
			sprintf(
				/* translators: %s: Plugin short name */
				esc_html__( '%s Duplicator', 'header-footer-elementor' ),
				esc_html( $short_name )
			)
		);

		// Reorder actions to place UAE Duplicate before "Edit with Elementor" and after "Trash".
		$new_actions = array();

		foreach ( $actions as $key => $action ) {
			if ( 'trash' === $key ) {
				$new_actions['trash']         = $action;
				$new_actions['hfe_duplicate'] = $actions['hfe_duplicate'];
			} elseif ( 'hfe_duplicate' !== $key ) {
				$new_actions[ $key ] = $action;
			}
		}

		return $new_actions;
	}

	/**
	 * Check if user can duplicate the post.
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return bool Whether user can duplicate the post.
	 */
	private function user_can_duplicate( $post ) {
		// Get the post type object.
		$post_type_obj = get_post_type_object( $post->post_type );

		// Check if user can edit this post type.
		if ( ! $post_type_obj || ! current_user_can( $post_type_obj->cap->edit_posts ) ) {
			return false;
		}

		// Check if user can edit this specific post.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Duplicate post.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function duplicate_post() {
		// Ensure the user is logged in before processing any request.
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You must be logged in to duplicate posts.', 'header-footer-elementor' ) );
		}

		// Check if we're duplicating a post.
		if ( ! isset( $_GET['post'] ) || ! isset( $_GET['nonce'] ) ) {
			wp_die( esc_html__( 'No post to duplicate has been supplied!', 'header-footer-elementor' ) );
		}

		// Get the original post ID.
		$post_id = absint( $_GET['post'] );

		// Verify nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'hfe_duplicate_post_' . $post_id ) ) {
			wp_die( esc_html__( 'Security check failed!', 'header-footer-elementor' ) );
		}

		// Get the original post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_die( esc_html__( 'Post creation failed, could not find original post.', 'header-footer-elementor' ) );
		}

		// Check if post type is excluded.
		if ( in_array( $post->post_type, $this->excluded_post_types, true ) ) {
			wp_die( esc_html__( 'Post type not supported for duplication.', 'header-footer-elementor' ) );
		}

		// Check if user has permission to duplicate.
		if ( ! $this->user_can_duplicate( $post ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'header-footer-elementor' ) );
		}

		// Create the duplicate post.
		$new_post_id = $this->create_duplicate( $post );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html( $new_post_id->get_error_message() ) );
		}

		// Redirect to the edit screen for the new post.
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . absint( $new_post_id ) ) );
		exit;
	}

	/**
	 * Create duplicate of the post.
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return int|WP_Error New post ID or WP_Error.
	 */
	private function create_duplicate( $post ) {
		// Create new post data array.
		$new_post_data = array(
			'post_author'    => get_current_user_id(),
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft', // Always set to draft.
			/* translators: %s: Original post title */
			'post_title'     => sprintf( __( 'Copy of %s', 'header-footer-elementor' ), $post->post_title ),
			'post_type'      => $post->post_type,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
		);

		// Insert the new post.
		$new_post_id = wp_insert_post( $new_post_data );

		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		// Copy post meta (RAW copy for Elementor compatibility).
		$this->copy_post_meta_raw( $post->ID, $new_post_id );

		// Copy featured image.
		$this->copy_featured_image( $post->ID, $new_post_id );

		// Copy taxonomies.
		$this->copy_taxonomies( $post->ID, $new_post_id, $post->post_type );

		// Clear Elementor generated CSS for the duplicated post.
		delete_post_meta( $new_post_id, '_elementor_css' );

		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		/**
		 * Fires after a post has been duplicated.
		 *
		 * @since 2.8.0
		 * @param int $new_post_id New post ID.
		 * @param int $post_id     Original post ID.
		 *
		 * @note Callbacks must not call wp_insert_post() recursively — there is no
		 *       recursion guard. Avoid triggering further duplications from this hook.
		 */
		do_action( 'hfe_post_duplicated', $new_post_id, $post->ID );

		// Store the original post ID in transient for admin notice.
		set_transient(
			'hfe_duplicated_post_' . get_current_user_id(),
			array(
				'original_id' => $post->ID,
				'new_id'      => $new_post_id,
			),
			30
		);

		// Increment global duplication counter. Shared option used by both Lite and Pro.
		// Note: read-then-write is not atomic; under simultaneous duplications the counter
		// may under-count. This is an accepted trade-off for a non-critical stat.
		$count = (int) get_option( 'uae_duplicator_count', 0 );
		update_option( 'uae_duplicator_count', $count + 1, false );

		return $new_post_id;
	}

	/**
	 * Copy post meta.
	 *
	 * NOTE: Meta is copied RAW to avoid Elementor serialization issues.
	 *
	 * @since 2.8.0
	 * @param int $source_post_id Source post ID.
	 * @param int $target_post_id Target post ID.
	 * @return void
	 */
	private function copy_post_meta_raw( $source_post_id, $target_post_id ) {
		global $wpdb;

		$meta_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
				$source_post_id
			)
		);

		if ( empty( $meta_rows ) ) {
			return;
		}

		foreach ( $meta_rows as $meta ) {
			// Skip _edit_lock and _edit_last meta keys.
			if ( in_array( $meta->meta_key, array( '_edit_lock', '_edit_last', '_wp_old_slug' ), true ) ) {
				continue;
			}

			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->postmeta,
				array(
					'post_id'    => $target_post_id,
					'meta_key'   => $meta->meta_key,
					'meta_value' => $meta->meta_value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				),
				array( '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Copy featured image.
	 *
	 * @since 2.8.0
	 * @param int $source_post_id Source post ID.
	 * @param int $target_post_id Target post ID.
	 * @return void
	 */
	private function copy_featured_image( $source_post_id, $target_post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $source_post_id );

		if ( $thumbnail_id ) {
			set_post_thumbnail( $target_post_id, $thumbnail_id );
		}
	}

	/**
	 * Copy taxonomies.
	 *
	 * @since 2.8.0
	 * @param int    $source_post_id Source post ID.
	 * @param int    $target_post_id Target post ID.
	 * @param string $post_type      Post type.
	 * @return void
	 */
	private function copy_taxonomies( $source_post_id, $target_post_id, $post_type ) {
		// Get all taxonomies for the post type.
		$taxonomies = get_object_taxonomies( $post_type );

		if ( empty( $taxonomies ) ) {
			return;
		}

		// Copy terms for each taxonomy.
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $source_post_id, $taxonomy, array( 'fields' => 'slugs' ) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $target_post_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function admin_notices() {
		// Check if we have a duplicated post.
		$duplicated_post = get_transient( 'hfe_duplicated_post_' . get_current_user_id() );

		if ( ! $duplicated_post ) {
			return;
		}

		// Delete the transient.
		delete_transient( 'hfe_duplicated_post_' . get_current_user_id() );

		// Get the original post.
		$original_post = get_post( $duplicated_post['original_id'] );

		if ( ! $original_post ) {
			return;
		}

		// Get post type label.
		$post_type_obj   = get_post_type_object( $original_post->post_type );
		$post_type_label = $post_type_obj
			? $post_type_obj->labels->singular_name
			: __( 'Post', 'header-footer-elementor' );

		// Display success message.
		?>
		<div class="notice notice-success is-dismissible hfe-post-duplicator-notice">
			<p>
				<?php
				printf(
					/* translators: %1$s: Post type label, %2$s: Original post title */
					esc_html__( '%1$s duplicated successfully. You are now editing the duplicate of "%2$s".', 'header-footer-elementor' ),
					esc_html( $post_type_label ),
					esc_html( $original_post->post_title )
				);
				?>
			</p>
		</div>
		<?php
	}
}

// Initialize the class.
HFE_Post_Duplicator::instance();
