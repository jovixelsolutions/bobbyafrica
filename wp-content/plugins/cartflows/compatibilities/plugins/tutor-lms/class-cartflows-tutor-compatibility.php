<?php
/**
 * Tutor LMS Compatibility (PHP-based)
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles CartFlows compatibility with Tutor LMS.
 *
 * Registers UI, assets, AJAX handlers, and frontend redirects
 * for CartFlows course template integration.
 */
class Cartflows_Tutor_Compatibility {

	/**
	 * Instance
	 *
	 * @since 1.1.1
	 * @var object Class object.
	 * @access private
	 */
	private static $instance;

	const META_KEY   = '_cartflows_course_template';
	const NONCE_NAME = 'cartflows_tutor_template_save';

	/**
	 * Initiator
	 *
	 * @since 1.1.1
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Registers all required hooks for Tutor LMS integration.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'tutor_course_builder_footer', array( $this, 'render_settings_template' ) );

		add_action( 'tutor_before_course_builder_load', array( $this, 'enqueue_assets' ) );

		add_action( 'wp_ajax_cartflows_save_tutor_course_template', array( $this, 'ajax_save_course_template' ) );

		add_action( 'template_redirect', array( $this, 'override_course_template' ), 5 );
	}

	/**
	 * Enqueues admin assets for the Tutor course builder.
	 *
	 * Loads CSS and JS only on the Tutor LMS course builder page.
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		global $pagenow;

		if ( ! is_admin() || 'admin.php' !== $pagenow || empty( $_GET['page'] ) || 'create-course' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $course_id ) {
			return;
		} 

		wp_enqueue_style(
			'cartflows-tutor-course-template',
			CARTFLOWS_URL . 'compatibilities/plugins/tutor-lms/tutor-course-template.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script(
			'cartflows-tutor-course-template',
			CARTFLOWS_URL . 'compatibilities/plugins/tutor-lms/tutor-course-template.js',
			array(),
			CARTFLOWS_VER,
			true
		);

		wp_localize_script(
			'cartflows-tutor-course-template',
			'cartflowsTutorData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::NONCE_NAME ),
				'courseId' => $course_id,
			)
		);
	}

	/**
	 * Renders the CartFlows course template selector UI.
	 *
	 * Outputs the settings panel inside the Tutor LMS course builder.
	 *
	 * @return void
	 */
	public function render_settings_template() {

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $course_id ) {
			return;
		}

		$current_template = '';
		$current          = get_post_meta( $course_id, self::META_KEY, true );
		if ( is_string( $current ) ) {
			$current_template = (string) $current;
		}
		$templates = $this->get_cartflows_templates();
		?>

		<div class="cartflows-tutor-settings-wrapper">
			<div class="cartflows-tutor-settings-panel">

				<div class="cartflows-tutor-settings-header">
					<h3><?php esc_html_e( 'CartFlows Course Template', 'cartflows' ); ?></h3>
					<p class="cartflows-tutor-settings-description">
						<?php
						esc_html_e(
							'Redirect non-enrolled students to a CartFlows template',
							'cartflows'
						);
						?>
					</p>
				</div>

				<div class="cartflows-tutor-settings-body">

					<div class="cartflows-tutor-select-container">
						<label for="cartflows_course_template">
							<?php esc_html_e( 'Select CartFlows Template', 'cartflows' ); ?>
						</label>
						<select
							id="cartflows_course_template"
							name="cartflows_course_template"
							class="cartflows-tutor-select"
						>
							<?php foreach ( $templates as $id => $label ) : ?>
								<option
									value="<?php echo esc_attr( (string) $id ); ?>"
									<?php selected( $current_template, (string) $id ); ?>
								>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>

						<span
							id="cartflows-template-status"
							class="cartflows-template-status"
							aria-live="polite"
						></span>
					</div>

					<p class="cartflows-tutor-field-hint">
						<?php
						esc_html_e(
							'Non-enrolled students will be redirected to the selected CartFlows template.',
							'cartflows'
						);
						?>
					</p>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Handles AJAX request to save the selected CartFlows template.
	 *
	 * Validates nonce, permissions, and template ID before persisting.
	 *
	 * @return void
	 */
	public function ajax_save_course_template() {

		if (
		empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::NONCE_NAME ) ) {
			wp_send_json_error(
				array( 'message' => 'Invalid nonce.' ),
				403
			);
		}

		$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;

		if ( ! $course_id ) {
			wp_send_json_error(
				array( 'message' => 'Invalid course ID.' ),
				400
			);
		}

		if ( ! current_user_can( 'edit_post', $course_id ) ) {
			wp_send_json_error(
				array( 'message' => 'Permission denied.' ),
				403
			);
		}

		$template_raw = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : 'none';

		$template = $this->validate_template_id( $template_raw );

		update_post_meta( $course_id, self::META_KEY, $template );

		wp_send_json_success(
			array(
				'template' => $template,
				'message'  => 'Template saved.',
			)
		);
	}

	/**
	 * Retrieves available CartFlows step templates.
	 *
	 * @return array<string|int, string> List of template IDs mapped to labels.
	 */
	private function get_cartflows_templates() {

		$templates = array(
			'none' => __( 'None', 'cartflows' ),
		);

		if ( ! defined( 'CARTFLOWS_STEP_POST_TYPE' ) ) {
			return $templates;
		}

		$step_ids = get_posts(
			array(
				'post_type'              => CARTFLOWS_STEP_POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => array( 'landing', 'checkout', 'optin' ),
						'compare' => 'IN',
					),
				),
			)
		);

		if ( empty( $step_ids ) ) {
			return $templates;
		}

		foreach ( $step_ids as $step_id ) {
			$templates[ $step_id ] = get_the_title( $step_id ) . ' (#' . $step_id . ')';
		}

		return $templates;
	}

	/**
	 * Validates a CartFlows template ID.
	 *
	 * Ensures the template exists and matches the expected post type.
	 *
	 * @param string $template Raw template value.
	 * @return string Validated template ID or 'none'.
	 */
	private function validate_template_id( $template ) {

		if ( 'none' === $template ) {
			return 'none';
		}

		$template = absint( $template );

		if ( $template && defined( 'CARTFLOWS_STEP_POST_TYPE' ) && CARTFLOWS_STEP_POST_TYPE === get_post_type( $template ) && 'publish' === get_post_status( $template ) ) {
			return (string) $template;
		}

		return 'none';
	}

	/**
	 * Redirects non-enrolled users to the selected CartFlows template.
	 *
	 * Runs on the frontend for protected Tutor LMS courses.
	 *
	 * @return void
	 */
	public function override_course_template() {

		if ( is_admin() || ! function_exists( 'tutor' ) || ! is_singular( tutor()->course_post_type ) ) {
			return;
		}

		$course_id = get_the_ID();

		if ( ! $course_id ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( function_exists( 'tutor_utils' ) && tutor_utils()->is_enrolled( $course_id, $user_id ) ) {
			return;
		}

		$template_id = 0;
		$template    = get_post_meta( (int) $course_id, self::META_KEY, true );

		if ( empty( $template ) || 'none' === $template ) {
			return;
		}

		if ( is_scalar( $template ) ) {
			$template_id = intval( $template );
		}

		$url = get_permalink( $template_id );

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}
}

Cartflows_Tutor_Compatibility::get_instance();
