<?php
/**
 * Init
 *
 * @since 1.0.0
 * @package NPS Survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Astra_Sites_Nps_Notice' ) ) :

	/**
	 * Admin
	 */
	class Astra_Sites_Nps_Notice {
		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @var (Object) Astra_Sites_Nps_Notice
		 */
		private static $instance = null;

		/**
		 * Get Instance
		 *
		 * @since 1.0.0
		 *
		 * @return object Class object.
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
		 * @since 1.0.0
		 */
		private function __construct() {
			add_action( 'admin_footer', array( $this, 'render_nps_survey' ), 999 );
			
			// Additional data passing via nps survey.
			add_filter( 'nps_survey_post_data', array( $this, 'nps_survey_post_data' ) );
		}

		/**
		 * Add import data to the post survey.
		 *
		 * @since 4.4.46
		 * @param mixed $post_data Post data.
		 *
		 * @return mixed
		 */
		public function nps_survey_post_data( $post_data ) {
			if ( isset( $post_data['plugin_slug'] ) && 'astra-sites' !== $post_data['plugin_slug'] ) {
				return $post_data;
			}

			$import_type = get_option( 'astra_sites_current_import_template_type' );
			$post_data['import_type'] = ! empty( $import_type ) ? $import_type : '';
			
			// Normalize AI import type to ai-builder.
			if ( 'ai' === $post_data['import_type'] ) {
				$post_data['import_type'] = 'ai-builder';
			}
			
			// Add UUID for AI builder imports if available.
			if ( 'ai-builder' === $post_data['import_type'] ) {
				$zip_import_data = get_option( 'zipwp_import_site_details' );
				if ( is_array( $zip_import_data ) && isset( $zip_import_data['uuid'] ) ) {
					$post_data['uuid'] = sanitize_text_field( $zip_import_data['uuid'] );
				}
			}

			return $post_data;
		}

		/** 
		 * Render NPS Survey
		 *
		 * @return void
		 */
		public function render_nps_survey() {

			if ( ! class_exists( 'Nps_Survey' ) ) {
				return;
			}

			if ( class_exists( 'Astra_Sites_White_Label' ) && is_callable( 'Astra_Sites_White_Label::get_instance' ) && Astra_Sites_White_Label::get_instance()->is_white_labeled() ) {
				return;
			}

			$allowed_screens = array( 'appearance_page_starter-templates', 'appearance_page_ai-builder' );
			
			Nps_Survey::show_nps_notice(
				'nps-survey-astra-sites',
				array(
					'show_if' => get_option( 'astra_sites_import_complete', false ),
					'dismiss_timespan' => 2 * WEEK_IN_SECONDS,
					'display_after' => 0,
					'plugin_slug' => 'astra-sites',
					'show_on_screens' => $allowed_screens,
					'message' => array(

						// Step 1 i.e rating input.
						'logo'                  => esc_url( INTELLIGENT_TEMPLATES_URI . 'assets/images/logo.svg' ),
						'plugin_name'           => __( 'Starter Templates', 'astra-sites' ),
						'nps_rating_title'            => __( 'Quick Question!', 'astra-sites' ),
						'nps_rating_message'          => sprintf(
							/* translators: %s is the plugin name */
							__( "How would you rate %s? Love it, hate it, or somewhere in between? Your honest answer helps us understand how we're doing.", 'astra-sites' ),
							'#pluginname'
						),
						'rating_min_label'      => __( 'Hate it', 'astra-sites' ),
						'rating_max_label'      => __( 'Love it', 'astra-sites' ),

						// Step 2A i.e. for rating 8 and above.
						'feedback_title'        => __( 'Thanks a lot for your feedback! 😍', 'astra-sites' ),
						'feedback_content'      => __( "Thanks for being part of the Starter Templates community! Got feedback or suggestions? We'd love to hear it.", 'astra-sites' ),

						// Step 2B i.e. for rating 7 and below.
						'plugin_rating_title'   => __( 'Thank you for your feedback', 'astra-sites' ),
						'plugin_rating_content' => __( 'We value your input. How can we improve your experience?', 'astra-sites' ),
					),
					'privacy_policy'  => array(
						'disable' => true, // Enable when we have a privacy policy url.
					),
				)
			);
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Sites_Nps_Notice::get_instance();

endif;
