<?php
/**
 * LearnDash Compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for LearnDash Compatibility
 */
class Cartflows_Learndash_Compatibility {
	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Constructor
	 */
	public function __construct() {
		// Add meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Add custom classes to the metabox wrapper.
		add_filter( 'postbox_classes_sfwd-courses_cartflows-course-template-settings', array( $this, 'add_metabox_classes' ), 30, 1 );
		
		// Save meta box.
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 3 );
		
		// Override course template redirect.
		add_action( 'template_redirect', array( $this, 'cartflows_override_course_template' ) );

		/**
		 * The CartFlows metabox gets added but has display none property.
		 * This filter ensures it is visible in the settings tab.
		 */
		add_filter(
			'learndash_header_tab_menu',
			array( $this, 'cartflows_manage_course_settings_metaboxes' ),
			50,
			3
		);

		/**
		 * Add the save filter for the classic editor
		 */
		add_filter( 'learndash_metabox_save_fields', array( $this, 'cartflows_save_setting_for_classic_editor' ), 10, 1 );
	}

	/**
	 * Add meta box to course edit screen.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'cartflows-course-template-settings',
			__( 'CartFlows Course Template Settings', 'cartflows' ),
			array( $this, 'render_meta_box' ),
			'sfwd-courses',
			'normal',
			'low'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post The post object.
	 * 
	 * @return void
	 */
	public function render_meta_box( $post ) {
		// Get current saved value.
		$template = get_post_meta( $post->ID, 'sfwd-courses_wcf_course_template', true );

		// Get all available templates.
		$templates = $this->get_cartflows_templates();

		// Show description before the wrapper.
		$this->show_metabox_description();

		?>
		<div class="sfwd sfwd_options cartflows-course-template-settings">

			<?php
				// Add nonce for security. 
				wp_nonce_field( 'cartflows_course_template_nonce_action', 'cartflows_course_template_nonce' ); 
			?>

			<div id="cartflows-course-template-settings_wcf_course_template_field" class="sfwd_input sfwd_input_type_select sfwd_input_type_select--cartflows-course-template-settings_wcf_course_template">
				<span class="sfwd_option_label" style="text-align:left;vertical-align:top;">
					<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('cartflows-course-template-settings_wcf_course_template_tip');">
						<?php if ( defined( 'LEARNDASH_LMS_PLUGIN_URL' ) ) : ?>
							<img alt="" src="<?php echo esc_url( LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ); ?>" />
						<?php endif; ?>
						<label class="sfwd_label textinput" for="cartflows-course-template-settings_wcf_course_template">
							<?php esc_html_e( 'Select CartFlows Template for this Course', 'cartflows' ); ?>
						</label>
					</a>
					<div class="sfwd_help_text_div" style="display:none" id="cartflows-course-template-settings_wcf_course_template_tip">
						<label class="sfwd_help_text">
						<?php
							echo wp_kses_post(
								sprintf(
									/* translators: 1: anchor start, 2: anchor close */
									__( 'Non-enrolled students will be redirected to the selected CartFlows template. If you have not created any Flow already, add new Flow from %1$shere%2$s.', 'cartflows' ),
									'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE . '&add-new-flow' ) ) . '" target="_blank">',
									'</a>'
								)
							);
						?>
						</label>
					</div>
				</span>

				<span class="sfwd_option_input">
					<div class="sfwd_option_div" id="cartflows-course-template-settings_wcf_course_template">
						<select autocomplete="off" name="wcf_course_template" id="cartflows-course-template-settings_wcf_course_template" class="learndash-section-field learndash-section-field-select">
							<?php foreach ( $templates as $template_id => $template_title ) : ?>
								<option value="<?php echo esc_attr( (string) $template_id ); ?>" <?php selected( $template, $template_id ); ?>>
									<?php echo esc_html( $template_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</span>
				<p class="ld-clear"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Get all available CartFlows templates.
	 *
	 * @return array<int|string, string>
	 */
	private function get_cartflows_templates() {
		$all_posts = array(
			'none' => esc_html__( 'None', 'cartflows' ),
		);

		$landing_steps = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => array( 'landing', 'checkout', 'optin' ),
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $landing_steps as $landing_step ) {
			$all_posts[ $landing_step->ID ] = get_the_title( $landing_step->ID ) . ' ( #' . $landing_step->ID . ')';
		}

		return $all_posts;
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 * @param boolean $update  Update or new post.
	 * 
	 * @return void
	 */
	public function save_meta_box( $post_id, $post, $update ) {
		// Verify nonce.
		if ( ! isset( $_POST['cartflows_course_template_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cartflows_course_template_nonce'] ) ), 'cartflows_course_template_nonce_action' ) ) {
			return;
		}

		// Verify user has permission to edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Save the template value.
		$template = isset( $_POST['wcf_course_template'] ) ? sanitize_text_field( wp_unslash( $_POST['wcf_course_template'] ) ) : 'none';
		
		// Validate that it's either 'none' or a valid post ID.
		if ( 'none' !== $template ) {
			
			// Verify the post exists and is a CartFlows step.
			if ( $template > 0 ) {
				$step_post = get_post( absint( $template ) );
				if ( ! $step_post || ( defined( 'CARTFLOWS_STEP_POST_TYPE' ) && CARTFLOWS_STEP_POST_TYPE !== $step_post->post_type ) ) {
					$template = 'none';
				}
			} else {
				$template = 'none';
			}
		}

		// Update post meta.
		update_post_meta( $post_id, 'sfwd-courses_wcf_course_template', $template );

		/**
		 * Action hook after saving CartFlows course template.
		 *
		 * @param int    $post_id  The course post ID.
		 * @param string $template The template value saved.
		 */
		do_action( 'cartflows_after_save_course_template', $post_id, $template );
	}

	/**
	 * Manage course settings metaboxes to show CartFlows metabox.
	 *
	 * @param array<int, array<string, mixed>> $header_data_tabs The header data tabs.
	 * @param string                           $menu_tab_key The menu tab key.
	 * @param string                           $screen_post_type The screen post type.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function cartflows_manage_course_settings_metaboxes( $header_data_tabs, $menu_tab_key, $screen_post_type ) {
		foreach ( $header_data_tabs as $key => $data_tabs ) {
			if ( 'sfwd-courses-settings' === $data_tabs['id'] ) {
				if ( isset( $header_data_tabs[ $key ]['metaboxes'] ) && is_array( $header_data_tabs[ $key ]['metaboxes'] ) ) {
					$header_data_tabs[ $key ]['metaboxes'][] = 'cartflows-course-template-settings';
				}
			}
		}
		return $header_data_tabs;
	}

	/**
	 * Override course template redirect for non-enrolled users.
	 *
	 * @return bool
	 */
	public function cartflows_override_course_template() {
		// Don't run any code in admin area.
		if ( is_admin() ) {
			return false;
		}

		// Don't override the template if the post type is not `course`.
		if ( ! is_singular( 'sfwd-courses' ) ) {
			return false;
		}

		$course_id = learndash_get_course_id();
		$user_id   = get_current_user_id();
		if ( is_user_logged_in() && sfwd_lms_has_access( $course_id, $user_id ) ) {
			return false;
		}

		$template = learndash_get_course_meta_setting( get_the_id(), 'wcf_course_template' );

		if ( 'none' !== $template && $template ) {
			$link = get_permalink( $template );
			wp_safe_redirect( $link );
			exit;
		}
	}


	/**
	 * Save CartFlows settings in classic editor.
	 *
	 * @param array<string, mixed> $fields The settings fields to be updated.
	 *
	 * @return array<string, mixed>
	 */
	public function cartflows_save_setting_for_classic_editor( $fields ) {
		// Verify nonce before processing the field value.
		if ( ! isset( $_POST['cartflows_course_template_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cartflows_course_template_nonce'] ) ), 'cartflows_course_template_nonce_action' ) ) {
			return $fields;
		}

		if ( isset( $_POST['wcf_course_template'] ) ) {
			$fields['wcf_course_template'] = sanitize_text_field( wp_unslash( $_POST['wcf_course_template'] ) );
		}
		return $fields;
	}

	/**
	 * Add custom classes to metabox wrapper.
	 *
	 * @param array<int, string> $classes Array of classes for postbox.
	 *
	 * @return array<int, string>
	 */
	public function add_metabox_classes( $classes ) {
		if ( ! in_array( 'ld_settings_postbox', $classes, true ) ) {
			$classes[] = 'ld_settings_postbox';
		}

		if ( ! in_array( 'ld_settings_postbox_sfwd-courses', $classes, true ) ) {
			$classes[] = 'ld_settings_postbox_sfwd-courses';
		}

		if ( ! in_array( 'ld_settings_postbox_sfwd-courses_cartflows-course-template-settings', $classes, true ) ) {
			$classes[] = 'ld_settings_postbox_sfwd-courses_cartflows-course-template-settings';
		}

		return $classes;
	}

	/**
	 * Show metabox description.
	 *
	 * @return void
	 */
	private function show_metabox_description() {
		$description = esc_html__( 'Select a CartFlows template to redirect non-enrolled students to when they try to access this course.', 'cartflows' );
		
		if ( ! empty( $description ) ) {
			echo '<div class="ld-metabox-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Learndash_Compatibility::get_instance();
