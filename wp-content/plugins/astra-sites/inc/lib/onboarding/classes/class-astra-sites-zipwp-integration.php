<?php


class Astra_Sites_ZipWP_Integration {

	/**
	 * Instance
	 *
	 * @since 4.0.0
	 * @access private
	 * @var object Class object.
	 */
    private static $instance = null;

    /**
     * Initiator
     *
     * @since 4.0.0
	 * @return mixed 
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 4.0.0
     */
    public function __construct() {
        $this->define_constants();
        add_action( 'wp_enqueue_scripts', array( $this, 'register_preview_scripts' ) );
    }

    /**
	 * Check whether a given request has permission to read notes.
	 *
	 * @param  object $request WP_REST_Request Full details about the request.
	 * @return object|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'gt_rest_cannot_access',
				__( 'Sorry, you are not allowed to do that.', 'astra-sites' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

    /**
	 * Register scripts.
	 *
	 * @return void
	 * @since  4.0.0
	 */
	public function register_preview_scripts() {

        if ( is_customize_preview() ) {
            return;
        }

		$handle       = 'starter-templates-zip-preview';
		$js_deps_file = INTELLIGENT_TEMPLATES_DIR . 'assets/dist/template-preview/main.asset.php';
		$js_dep       = [
			'dependencies' => array(),
			'version'      => ASTRA_SITES_VER,
		];

		if ( file_exists( $js_deps_file ) ) {

			$script_info = include_once $js_deps_file;

			if ( isset( $script_info['dependencies'] ) && isset( $script_info['version'] ) ) {
				$js_dep['dependencies'] = $script_info['dependencies'];
				$js_dep['version']      = $script_info['version'];
			}
		}

		wp_register_script( $handle, INTELLIGENT_TEMPLATES_URI . 'assets/dist/template-preview/main.js', $js_dep['dependencies'], $js_dep['version'], true );

		$color_palette_prefix     = '--ast-global-';
		$ele_color_palette_prefix = '--ast-global-';

		if ( class_exists( 'Astra_Global_Palette' ) ) {

			$astra_callable_class = new \Astra_Global_Palette();

			if ( is_callable( array( $astra_callable_class, 'get_css_variable_prefix' ) ) ) {
				$color_palette_prefix = \Astra_Global_Palette::get_css_variable_prefix();
			}

			if ( is_callable( array( $astra_callable_class, 'get_palette_slugs' ) ) ) {
				$ele_color_palette_prefix = \Astra_Global_Palette::get_palette_slugs();
			}
		}

		wp_localize_script(
			$handle,
			'starter_templates_zip_preview',
			array(
				'AstColorPaletteVarPrefix'    => $color_palette_prefix,
				'AstEleColorPaletteVarPrefix' => $ele_color_palette_prefix,
			)
		);

		wp_enqueue_script( $handle );
		wp_add_inline_style( 'starter-templates-zip-preview-custom', '#wpadminbar { display: none !important; }' );
	}

    /**
     * Define Constants
     *
     * @since 4.0.0
     * @return void
     */
    public function define_constants() : void {

        if ( ! defined( 'ZIPWP_APP' ) ) {
            define( 'ZIPWP_APP', apply_filters( 'ast_block_templates_zip_app_url', 'https://app.zipwp.com/auth' ) );
        }

        if ( ! defined( 'ZIPWP_API' ) ) {
            define( 'ZIPWP_API', apply_filters( 'ast_block_templates_zip_api_url', 'https://api.zipwp.com/api' ) );
        }
    }

	/**
	 * Make an authenticated GET request to the ZipWP API.
	 *
	 * @param string $endpoint Full API endpoint URL.
	 * @return array<string, mixed>
	 * @since 4.0.0
	 */
	private function make_api_request( $endpoint ) {
		$response = wp_safe_remote_get(
			$endpoint,
			array(
				'headers'   => Astra_Sites_ZipWP_Api::get_instance()->get_api_headers(),
				'timeout'   => 100,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'data'   => 'Failed ' . $response->get_error_message(),
				'status' => false,
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'data'   => 'Failed',
				'status' => false,
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array(
				'data'   => 'Failed: Invalid JSON response',
				'status' => false,
			);
		}

		return array(
			'data'   => $data ?: array(),
			'status' => true,
		);
	}

	/**
	 * Get ZIP Plans.
	 *
	 * @return array<string, mixed>
	 */
	public function get_zip_plans() {
		return $this->make_api_request(
			Astra_Sites_ZipWP_Api::get_instance()->get_api_domain() . '/plan/current-plan'
		);
	}

	/**
	 * Get ZIP Addons.
	 *
	 * @return array<string, mixed>
	 */
	public function get_zip_addons() {
		return $this->make_api_request(
			Astra_Sites_ZipWP_Api::get_instance()->get_api_domain() . '/user/addons'
		);
	}
}

Astra_Sites_ZipWP_Integration::get_instance();
