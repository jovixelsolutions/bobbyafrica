<?php
/**
 * HFE Learn API
 *
 * @package HFE
 * @since 2.8.4
 */

namespace HFE\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

/**
 * Class HFE_Learn_API
 *
 * Handles the learn tab functionality including REST API endpoints and admin actions.
 *
 * @since 2.8.4
 */
class HFE_Learn_API extends WP_REST_Controller {
    
    /**
     * REST API namespace
     *
     * @since 2.8.4
     * @var string
     */
    protected $namespace = 'hfe/v1';
    
    /**
     * Constructor
     *
     * Initialize hooks and actions for the learn API.
     *
     * @since 2.8.4
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        // Header.
        add_action( 'admin_post_uae_create_header_elementor', array( $this, 'uae_create_header_elementor' ) );
        add_action( 'admin_post_uae_edit_header_elementor', array( $this, 'uae_edit_header_elementor' ) );

        // Footer.
        add_action( 'admin_post_uae_create_footer_elementor', array( $this, 'uae_create_footer_elementor' ) );
        add_action( 'admin_post_uae_edit_footer_elementor', array( $this, 'uae_edit_footer_elementor' ) );

        // Page Builder.
        add_action( 'admin_post_uae_create_page_elementor', array( $this, 'uae_create_page_and_open_elementor' ) );

        add_action( 'admin_post_uae_open_extension', array( $this, 'uae_open_extension' ) );
        add_action( 'admin_post_uae_enable_duplicator', array( $this, 'uae_enable_duplicator' ) );
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'uae_auto_open_extension_tab' ) );
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'uae_elementor_widget_search' ) );
    }

    /**
     * Add Elementor widget search functionality.
     *
     * Enqueues inline JavaScript to handle widget search and auto-click in Elementor editor.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_elementor_widget_search() {
        wp_add_inline_script(
            'elementor-editor',
            "(function () {
                jQuery(window).on('elementor:init', function () {
                    const params = new URLSearchParams(window.location.search);
                    let keyword = params.get('uae_widgets');

                    if (!keyword) {
                        return;
                    }

                    const lowerKeyword = keyword.toLowerCase();
                    const skipClick = ( lowerKeyword === 'header' || lowerKeyword === 'footer' );

                    if ( skipClick ) {
                        keyword = 'uae';
                    }

                    let attempts = 0;
                    const maxAttempts = 40;

                    const interval = setInterval(function () {
                        attempts++;

                        const searchInput = document.querySelector(
                            '.elementor-panel input[type=\"search\"]'
                        );

                        if (!searchInput) {
                            if (attempts >= maxAttempts) {
                                clearInterval(interval);
                            }
                            return;
                        }

                        searchInput.value = keyword;
                        searchInput.dispatchEvent(
                            new Event('input', { bubbles: true })
                        );

                        if ( skipClick ) {
                            clearInterval(interval);
                            return;
                        }

                        setTimeout(function () {
                            const widgets = document.querySelectorAll(
                                '#elementor-panel-elements-wrapper .elementor-element'
                            );

                            for (const widget of widgets) {
                                const titleEl = widget.querySelector('.title');

                                if (
                                    titleEl &&
                                    titleEl.textContent
                                        .toLowerCase()
                                        .includes(lowerKeyword)
                                ) {
                                    widget.click();
                                    clearInterval(interval);
                                    return;
                                }
                            }
                        }, 300);

                        if (attempts >= maxAttempts) {
                            clearInterval(interval);
                        }
                    }, 250);
                });
            })();"
        );
    }

    /**
     * Handle header/footer template creation or editing.
     *
     * Creates or finds existing header/footer template and redirects to appropriate editor.
     *
     * @since 2.8.4
     * @param string $type Template type ('header' or 'footer').
     * @param bool   $open_elementor Whether to open in Elementor editor.
     * @return void
     */
    private function handle_hf_template( $type = 'header', $open_elementor = true ) {

        check_admin_referer( 'hfe_learn_action' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'header-footer-elementor' ) );
        }

        $title = ( 'footer' === $type )
            ? __( 'UAE Learn Footer', 'header-footer-elementor' )
            : __( 'UAE Learn Header', 'header-footer-elementor' );

        $existing = get_posts(
            array(
                'post_type'      => 'elementor-hf',
                'post_status'    => array( 'draft', 'publish' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'   => 'uae_learn',
                        'value' => 'yes',
                    ),
                    array(
                        'key'   => 'ehf_template_type',
                        'value' => 'type_' . $type,
                    ),
                ),
            )
        );

        if ( ! empty( $existing ) ) {
            $post_id = $existing[0];
        } else {
            $post_id = wp_insert_post(
                array(
                    'post_type'   => 'elementor-hf',
                    'post_title'  => $title,
                    'post_status' => 'draft',
                )
            );
            $template_type = 'type_' . $type;
            update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
            update_post_meta( $post_id, '_wp_page_template', 'default' );
            update_post_meta( $post_id, 'ehf_template_type', $template_type );
            update_post_meta( $post_id, 'uae_learn', 'yes' );
        }

        if ( $open_elementor ) {
            wp_safe_redirect(
                admin_url(
                    'post.php?post=' . $post_id .
                    '&action=elementor' .
                    '&uae_widgets=' . $type
                )
            );
        } else {
            wp_safe_redirect(
                admin_url(
                    'post.php?post=' . $post_id .
                    '&action=edit&open_metabox=ehf-meta-box'
                )
            );
        }

        exit;
    }

    /**
     * Create Header and Redirect to Header screen.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_create_header_elementor() {
        $this->handle_hf_template( 'header', false );
    }

    /**
     * Create or check Header and Redirect to Elementor.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_edit_header_elementor() {
        $this->handle_hf_template( 'header', true );
    }

    /**
     * Create Footer and Redirect to Footer screen.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_create_footer_elementor() {
        $this->handle_hf_template( 'footer', false );
    }

    /**
     * Create or check Footer and Redirect to Elementor.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_edit_footer_elementor() {
        $this->handle_hf_template( 'footer', true );
    }
    
    /**
     * Create Page and Open Elementor showing UAE widgets.
     *
     * Creates or finds existing UAE learn page and opens it in Elementor editor
     * with optional widget search functionality.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_create_page_and_open_elementor() {
        check_admin_referer( 'hfe_learn_action' );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'header-footer-elementor' ) );
        }

        // Get keyword from request.
        $uae_widgets = isset( $_GET['uae_widgets'] )
            ? sanitize_text_field( wp_unslash( $_GET['uae_widgets'] ) )
            : '';

        $existing = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'draft', 'publish', 'private' ),
                'meta_key'       => 'uae_learn',
                'meta_value'     => 'yes',
                'posts_per_page' => 1,
                'fields'         => 'ids',
            )
        );

        if ( ! empty( $existing ) ) {
            $page_id = $existing[0];

            wp_safe_redirect(
                admin_url(
                    'post.php?post=' . $page_id .
                    '&action=elementor' .
                    '&uae_widgets=' . rawurlencode( $uae_widgets )
                )
            );
            exit;
        }

        $page_id = wp_insert_post(
            array(
                'post_type'   => 'page',
                'post_title'  => __( 'UAE Learn Page', 'header-footer-elementor' ),
                'post_status' => 'draft',
            )
        );

        if ( is_wp_error( $page_id ) ) {
            wp_die( esc_html__( 'Page creation failed.', 'header-footer-elementor' ) );
        }

        update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
        update_post_meta( $page_id, 'uae_learn', 'yes' );

        wp_safe_redirect(
            admin_url(
                'post.php?post=' . $page_id .
                '&action=elementor' .
                '&uae_widgets=' . rawurlencode( $uae_widgets )
            )
        );
        exit;
    }

    /**
     * Fetch elementor kit id and Redirect to elementor site setting.
     *
     * Opens specific extension tab in Elementor site settings.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_open_extension() {
        check_admin_referer( 'hfe_learn_action' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'header-footer-elementor' ) );
        }

        $ext = isset( $_GET['ext'] )
            ? sanitize_key( $_GET['ext'] )
            : '';

        if ( empty( $ext ) ) {
            wp_die( esc_html__( 'Invalid extension.', 'header-footer-elementor' ) );
        }

        // Active Elementor Kit.
        $kit_id = get_option( 'elementor_active_kit' );

        if ( empty( $kit_id ) ) {
            wp_die( esc_html__( 'No active Elementor Kit found.', 'header-footer-elementor' ) );
        }

        wp_safe_redirect(
            admin_url(
                'post.php?post=' . absint( $kit_id ) .
                '&action=elementor' .
                '&open_ext=' . rawurlencode( $ext )
            )
        );

        exit;
    }

    /**
     * Enable UAE Duplicator and redirect to pages.
     *
     * Enables the post duplicator widget and redirects to the pages list.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_enable_duplicator() {
        check_admin_referer( 'hfe_learn_action' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'header-footer-elementor' ) );
        }

        // Get current enabled widgets.
        $enabled_widgets = get_option( '_hfe_widgets', array() );

        if ( ! is_array( $enabled_widgets ) ) {
            $enabled_widgets = array();
        }

        // Check if duplicator is already enabled.
        if ( ! isset( $enabled_widgets['Post_Duplicator'] ) || 'Post_Duplicator' !== $enabled_widgets['Post_Duplicator'] ) {
            // Enable the duplicator widget (set value to slug).
            $enabled_widgets['Post_Duplicator'] = 'Post_Duplicator';
            update_option( '_hfe_widgets', $enabled_widgets );
        }

        // Redirect to pages list.
        wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
        exit;
    }

    /**
     * Auto open extension tab in Elementor.
     *
     * Adds JavaScript to automatically open specific extension tabs in Elementor kit.
     *
     * @since 2.8.4
     * @return void
     */
    public function uae_auto_open_extension_tab() {
        ?>
        <script>
        (function(){

            const MAP = {
                reading_progress : '.elementor-panel-menu-item-hfe-reading-progress-bar',
                scroll_to_top    : '.elementor-panel-menu-item-hfe-scroll-to-top-settings'
            };

            function tryOpen( selector ) {

                if ( ! window.elementor || ! elementor.config || ! elementor.config.document ) {
                    return;
                }

                if ( elementor.config.document.type !== 'kit' ) {
                    return;
                }

                const btn = document.querySelector( selector );

                if ( btn ) {
                    btn.click();
                    return;
                }

                setTimeout( function(){
                    tryOpen( selector );
                }, 200 );
            }

            window.addEventListener('elementor:init', function () {

                const params = new URLSearchParams( window.location.search );
                const ext = params.get('open_ext');

                if ( ! ext || ! MAP[ ext ] ) {
                    return;
                }

                tryOpen( MAP[ ext ] );

            });

        })();
        </script>
        <?php
    }



    /**
     * Register REST API routes
     *
     * Registers endpoints for learning chapters and progress tracking.
     *
     * @since 2.8.4
     * @return void
     */
    public function register_routes() {
        // GET endpoint - fetch chapters with progress.
        register_rest_route(
            $this->namespace,
            'get-learn-chapters',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_learn_chapters' ),
                    'permission_callback' => array( $this, 'get_permissions_check' ),
                ),
            )
        );

        // POST endpoint - save progress.
        register_rest_route(
            $this->namespace,
            'update-learn-progress',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'save_learn_progress' ),
                    'permission_callback' => array( $this, 'get_permissions_check' ),
                    'args'                => array(
                        'chapterId' => array(
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                        'stepId'    => array(
                            'required'          => true,
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                        'completed' => array(
                            'required' => true,
                            'type'     => 'boolean',
                        ),
                    ),
                ),
            )
        );
    }

    /**
	 * Get default learn chapters structure.
	 *
	 * Returns the complete structure of all available chapters and their steps.
	 * This serves as the source of truth for chapter definitions used across
	 * the theme for both frontend display and analytics validation.
	 *
	 * @return array Array of chapter objects with their steps.
	 * @since 2.8.4
	 */
	public static function get_chapters_structure() {
         // Define UAE learning chapters.
        $chapters = array(
            array(
                'id'          => 'create-header',
                'title'       => __( 'Create Your Header', 'header-footer-elementor' ),
                'description' => __( 'Build a stunning header that showcases your brand on every page.', 'header-footer-elementor' ),
                'steps'       => array(
                    array(
                        'id'          => 'setup-header',
                        'title'       => __( 'Set Up Header Under 5 Mins!', 'header-footer-elementor' ),
                        'description' => __( 'Create a branded header that appears on every page and keeps your navigation consistent.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Create-Header.gif',
                                    'alt' => __( 'Add header in Elementor', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Set Up Header', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_header_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                    // STEP 2.
                    array(
                        'id'          => 'setup-display-conditions',
                        'title'       => __( 'Control Where Your Template Appears', 'header-footer-elementor' ),
                        'description' => __( 'Use Display Conditions to choose exactly where this template shows — entire site, specific pages, posts, or archives.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Header-Display-Con.gif',
                                    'alt' => __( 'Add Display conditions to your header', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Display Conditions', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_header_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),


                    // STEP 3.
                    array(
                        'id'          => 'add-header-widgets',
                        'title'       => __( 'Add Your Favorite UAE Widgets and Make it Live', 'header-footer-elementor' ),
                        'description' => __( 'Include logo, menu, CTA, and contact access for a complete header.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Edit-Header.gif',
                                    'alt' => __( 'Add UAE widgets to your header', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Header Widgets', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_edit_header_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),
                ),
            ),
            array(
                'id'          => 'create-footer',
                'title'       => __( 'Create Your Footer', 'header-footer-elementor' ),
                'description' => __( 'Design a professional footer that builds trust and improves site navigation.', 'header-footer-elementor' ),
                'steps'       => array(
                    // STEP 1.
                    array(
                        'id'          => 'setup-footer',
                        'title'       => __( 'Set Up Footer in less than 5 mins!', 'header-footer-elementor' ),
                        'description' => __( 'Add a global footer for consistent links, policies, and brand presence on every page.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Create-Footer.gif',
                                    'alt' => __( 'Set Up Footer Template', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Set Up Footer', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_footer_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                    array(
                        'id'          => 'setup-display-conditions',
                        'title'       => __( 'Control Where Your Template Appears', 'header-footer-elementor' ),
                        'description' => __( 'Use Display Conditions to choose exactly where this template shows — entire site, specific pages, posts, or archives.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Footer-Display-Con.gif',
                                    'alt' => __( 'Add display conditons to your footer', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Display Conditions', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_footer_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),


                    // STEP 3.
                    array(
                        'id'          => 'add-footer-widgets',
                        'title'       => __( 'Add Your Footer Widgets and Make it live.', 'header-footer-elementor' ),
                        'description' => __( 'Insert essential elements like Social Icons, Contact Info, Copyright Text, and Navigation Links for complete site credibility.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Edit-Footer.gif',
                                    'alt' => __( 'Footer Widgets in Elementor', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Footer Widgets', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_edit_footer_elementor' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),
                ),
            ),
            array(
                'id'          => 'add-powerful-widgets',
                'title'       => __( 'Add More Powerful Widgets', 'header-footer-elementor' ),
                'description' => __( 'Supercharge your pages with UAE\'s most popular content widgets for better engagement.', 'header-footer-elementor' ),
                'steps'       => array(
                    // STEP 1.
                    array(
                        'id'          => 'add-info-card',
                        'title'       => __( 'Create Eye-Catching Info Cards', 'header-footer-elementor' ),
                        'description' => __( 'Display key features, services, or benefits with beautiful Info Card widgets that convert visitors into customers.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Info-Card.gif',
                                    'alt' => __( 'Info Card Widget in Elementor', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Info Card', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_page_elementor&uae_widgets=info card' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                    // STEP 2.
                    array(
                        'id'          => 'add-posts-widget',
                        'title'       => __( 'Showcase Dynamic Content with Posts', 'header-footer-elementor' ),
                        'description' => __( 'Automatically display your latest blog posts, news, or updates with the Basic Posts widget for fresh, engaging content.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Basic-Post.gif',
                                    'alt' => __( 'Basic Posts Widget in Elementor', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Add Posts Widget', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_create_page_elementor&uae_widgets=basic posts' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                ),
            ),
            array(
                'id'          => 'uae-power-extensions',
                'title'       => __( 'Power Extensions', 'header-footer-elementor' ),
                'description' => __( 'Enable time-saving features that streamline your workflow and enhance user experience.', 'header-footer-elementor' ),
                'steps'       => array(
                    // STEP 1.
                    array(
                        'id'          => 'enable-duplicator',
                        'title'       => __( 'Duplicate Pages in One Click', 'header-footer-elementor' ),
                        'description' => __( 'Save hours by cloning any page or post instantly with Post Duplicator - perfect for creating template variations.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Duplicator.gif',
                                    'alt' => __( 'Post Duplicator Extension', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Enable UAE Duplicator', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_enable_duplicator' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                    // STEP 2.
                    array(
                        'id'          => 'enable-scroll-to-top',
                        'title'       => __( 'Add Scroll to Top Button', 'header-footer-elementor' ),
                        'description' => __( 'Improve navigation on long pages with a floating button that helps visitors jump back to the top effortlessly.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Scroll-Button.gif',
                                    'alt' => __( 'Scroll To Top Setting', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Enable Scroll Button', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_open_extension&ext=scroll_to_top' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),
                    array(
                        'id'          => 'enable-reading-progres-bar',
                        'title'       => __( 'Add Reading Progress Bar', 'header-footer-elementor' ),
                        'description' => __( 'Improve navigation on long pages with a floating button that helps visitors jump back to the top effortlessly.', 'header-footer-elementor' ),
                        'learn'       => array(
                            'type'    => 'dialog',
                            'content' => array(
                                'type' => 'image',
                                'data' => array(
                                    'src' => 'https://ultimateelementor.com/wp-content/uploads/2026/02/Learn-Tab-Reading-Progress-Bar.gif',
                                    'alt' => __( 'Reading Progress Bar', 'header-footer-elementor' ),
                                ),
                            ),
                        ),
                        'action'      => array(
                            'label'      => __( 'Enable Reading Progress Bar', 'header-footer-elementor' ),
                            'url'        => add_query_arg( '_wpnonce', wp_create_nonce( 'hfe_learn_action' ), admin_url( 'admin-post.php?action=uae_open_extension&ext=reading_progress' ) ),
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                    ),

                ),
            ),
        );

        // Add Pro features chapter if not Pro.
        if ( ! defined( 'UAEL_PRO' ) ) {
            $chapters[] = array(
                'id'          => 'pro-features',
                'title'       => __( 'Pro Features', 'header-footer-elementor' ),
                'description' => __( 'Unlock advanced widgets and features with UAE Pro', 'header-footer-elementor' ),
                'steps'       => array(
                    array(
                        'id'          => 'explore-pro',
                        'title'       => __( 'Explore Pro Features', 'header-footer-elementor' ),
                        'description' => __( 'See what\'s available in the Pro version', 'header-footer-elementor' ),
                        'action'      => array(
                            'label'      => __( 'Upgrade to Pro', 'header-footer-elementor' ),
                            'url'        => 'https://ultimateelementor.com/pricing/?utm_source=hfe-learn&utm_medium=learn-tab&utm_campaign=upgrade',
                            'isExternal' => true,
                        ),
                        'completed'   => false,
                        'isPro'       => true,
                    ),
                ),
            );
        }


		/**
		 * Filter learn chapters structure.
		 *
		 * @param array $chapters Learn chapters data.
		 * @since 2.8.4
		 */
		return apply_filters( 'hfe_learn_chapters', $chapters );
	}
    
    /**
     * Get learn chapters with user progress
     *
     * Retrieves all learning chapters with user-specific progress data.
     *
     * @since 2.8.4
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|array
     */
    public function get_learn_chapters( $request ) {
        // Get saved progress from user meta.
        $user_id        = get_current_user_id();

        // Get chapters structure.
		$chapters = self::get_chapters_structure();

		// Get saved progress from user meta.
		$saved_progress = get_user_meta( $user_id, 'hfe_learn_progress', true );
		if ( ! is_array( $saved_progress ) ) {
			$saved_progress = array();
		}

		// Merge saved progress with chapters.
		foreach ( $chapters as &$chapter ) {
			// Validate chapter structure.
			if ( ! isset( $chapter['id'], $chapter['steps'] ) || ! is_array( $chapter['steps'] ) ) {
				continue;
			}

			$chapter_id = $chapter['id'];

			foreach ( $chapter['steps'] as &$step ) {
				if ( ! isset( $step['id'] ) ) {
					continue;
				}

				$step_id = $step['id'];
				if ( isset( $saved_progress[ $chapter_id ][ $step_id ] ) ) {
					$step['completed'] = $saved_progress[ $chapter_id ][ $step_id ];
				}
			}
		}

		return $chapters;
    }
    
    /**
     * Save learn progress
     *
     * Updates user progress for a specific learning step.
     *
     * @since 2.8.4
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function save_learn_progress( $request ) {
        $chapter_id = $request->get_param( 'chapterId' );
        $step_id    = $request->get_param( 'stepId' );
        $completed  = $request->get_param( 'completed' );

        // Get current progress.
        $user_id        = get_current_user_id();
        $saved_progress = get_user_meta( $user_id, 'hfe_learn_progress', true );
        if ( ! is_array( $saved_progress ) ) {
            $saved_progress = array();
        }

        // Initialize chapter array if it doesn't exist.
        if ( ! isset( $saved_progress[ $chapter_id ] ) || ! is_array( $saved_progress[ $chapter_id ] ) ) {
            $saved_progress[ $chapter_id ] = array();
        }

        // Update progress for this step.
        $saved_progress[ $chapter_id ][ $step_id ] = (bool) $completed;

        // Save to user meta.
        update_user_meta( $user_id, 'hfe_learn_progress', $saved_progress );

        // Track event when ALL learn chapters are fully completed.
        if ( (bool) $completed && class_exists( '\HFE_Analytics_Events' )
            && ! \HFE_Analytics_Events::is_tracked( 'learn_completed' )
        ) {
            $chapters     = self::get_chapters_structure();
            $all_complete = true;

            foreach ( $chapters as $chapter ) {
                if ( empty( $chapter['steps'] ) || ! is_array( $chapter['steps'] ) ) {
                    continue;
                }
                if ( empty( $saved_progress[ $chapter['id'] ] ) || ! is_array( $saved_progress[ $chapter['id'] ] ) ) {
                    $all_complete = false;
                    break;
                }
                foreach ( $chapter['steps'] as $step ) {
                    if ( empty( $saved_progress[ $chapter['id'] ][ $step['id'] ] ) ) {
                        $all_complete = false;
                        break 2;
                    }
                }
            }

            if ( $all_complete ) {
                $install_time       = get_option( 'uae_usage_installed_time', 0 );
                $days_since_install = 0;
                if ( $install_time > 0 ) {
                    $days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
                }
                \HFE_Analytics_Events::track(
                    'learn_completed',
                    (string) count( $chapters ),
                    [ 'days_since_install' => (string) $days_since_install ]
                );
            }
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Progress saved successfully.', 'header-footer-elementor' ),
            ),
            200
        );
    }
    
    /**
     * Permission check for API endpoints
     *
     * Checks if current user has permission to access learn API endpoints.
     *
     * @since 2.8.4
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function get_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                'rest_cannot_view',
                __( 'Sorry, you cannot access this resource.', 'header-footer-elementor' ),
                array( 'status' => rest_authorization_required_code() )
            );
        }
        return true;
    }
}

// Initialize the API
new HFE_Learn_API();
