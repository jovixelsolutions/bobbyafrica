<?php
class SRUC_Plugin {

    public function __construct() {
        new SRUC_Admin_Menu();
    }

    public function run() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'init', array( $this, 'register_under_maintenance_post_type' ) );
        add_action( 'init', array( $this, 'create_default_templates' ) );
        add_action( 'init', array( $this, 'add_new_templates_on_update' ) );
        add_action( 'init', array( $this, 'fix_legacy_timestamp_totals' ) );
        add_action( 'init', array( $this, 'render_frontend_template' ) );

    }

    public function enqueue_admin_assets() {

        
        if (isset($_GET['page']) && sanitize_text_field( wp_unslash($_GET['page'])) === 'sruc-settings') {

            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sruc_settings_nonce' ) ) {
                wp_die( esc_html__( 'Security check failed. 2', 'siteready-coming-soon-under-construction' ) );
            }
            
            wp_enqueue_style('bootstrap-admin-css',SRUC_PLUGIN_URL . 'assets/admin/css/bootstrap-min.css',array(),SRUC_PLUGIN_VERSION);
            wp_enqueue_script('bootstrap-admin-js', SRUC_PLUGIN_URL . 'assets/admin/js/bootstrap-bundle-min.js', array('jquery'),SRUC_PLUGIN_VERSION, true );
            wp_enqueue_style('sruc-admin-style', SRUC_PLUGIN_URL . 'assets/admin/css/admin-styles.css',  array(), SRUC_PLUGIN_VERSION );
            wp_enqueue_script('sruc-admin-js', SRUC_PLUGIN_URL . 'assets/admin/js/admin-scripts.js',array( 'jquery' ),SRUC_PLUGIN_VERSION, true );
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_media();
            wp_localize_script(
                'sruc-admin-js',
                'srucMedia',
                array(
                    'title'  => __( 'Select Logo', 'siteready-coming-soon-under-construction' ),
                    'button' => __( 'Use this logo', 'siteready-coming-soon-under-construction' ),
                )
            );
        }

        
    }


    public function render_frontend_template() {
    if ( class_exists( 'SRUC_Frontend_Template' ) ) {

        $enabled = get_option( 'sruc_enabled', 0 );
        $is_preview = isset($_GET['sruc_preview']) && current_user_can('manage_options');

        if ( $is_preview ) {
            if (! isset( $_GET['_wpnonce'] )
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sruc_settings_nonce' )
            ) {
                wp_die( esc_html__( 'Security check failed.', 'siteready-coming-soon-under-construction' ) );
            }
        }

        if ( $enabled || $is_preview ) {
            new SRUC_Frontend_Template();
        }
    }
}


    public function register_under_maintenance_post_type() {
        $labels = array(
            'name'               => __( 'Maintenance Templates', 'siteready-coming-soon-under-construction' ),
            'singular_name'      => __( 'Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'menu_name'          => __( 'Maintenance Templates', 'siteready-coming-soon-under-construction' ),
            'name_admin_bar'     => __( 'Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'add_new'            => __( 'Add New', 'siteready-coming-soon-under-construction' ),
            'add_new_item'       => __( 'Add New Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'new_item'           => __( 'New Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'edit_item'          => __( 'Edit Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'view_item'          => __( 'View Maintenance Template', 'siteready-coming-soon-under-construction' ),
            'all_items'          => __( 'All Maintenance Templates', 'siteready-coming-soon-under-construction' ),
            'search_items'       => __( 'Search Maintenance Templates', 'siteready-coming-soon-under-construction' ),
            'parent_item_colon'  => __( 'Parent Maintenance Templates:', 'siteready-coming-soon-under-construction' ),
            'not_found'          => __( 'No Maintenance Templates found.', 'siteready-coming-soon-under-construction' ),
            'not_found_in_trash' => __( 'No Maintenance Templates found in Trash.', 'siteready-coming-soon-under-construction' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'menu_icon'          => 'dashicons-hammer',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
        );

        register_post_type( 'sruc_template', $args );
    }

    public static function create_default_templates() {
        $existing = get_posts(array(
            'post_type'      => 'sruc_template',
            'posts_per_page' => 1
        ));
        
        if ( empty($existing) ) {

            if ( ! function_exists( 'media_sideload_image' ) ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
            }

            
            $templates = array(
                array(
                    'post_title' => 'Template 1',
                    'template_key' => 'sruc_template_1',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-1.png',
                    'meta' => array(
                        'sruc_sub_heading'        => 'We Got Something New!',
                        'sruc_heading'            => 'COMING SOON',
                        'sruc_short_description'  => 'STAY TUNED!',
                        'sruc_description'        => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'sruc_timestramp_days'    => '10',
                        'sruc_timestramp_hours'   => '00',
                        'sruc_timestramp_minutes' => '00',
                        'sruc_timestramp_seconds' => '00',

                        //  Colors
                        'sruc_sub_heading_color'        => '#000000',
                        'sruc_heading_color'            => '#ffffff',  
                        'sruc_short_description_color'  => '#ffffff',
                        'sruc_description_color'        => '#333333',
                        'sruc_timer_bg_color'           => '#000000', 
                        'sruc_timer_text_color'         => '#ffffff',

                        //  Sizes
                        'sruc_sub_heading_size'         => '33px',
                        'sruc_heading_size'             => '140px',
                        'sruc_short_description_size'   => '35px',
                        'sruc_description_size'         => '14px',
                        'sruc_timer_size'               => '50px',

                    )
                ),
                array(
                    'post_title' => 'Template 2',
                    'template_key' => 'sruc_template_2',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-2.png',
                    'meta' => array(
                        'sruc_sub_heading'        => 'Sorry,',
                        'sruc_heading_first'            => 'Under',
                        'sruc_heading_second'            => 'Construction',
                        'sruc_description'        => 'Designed by example.com',

                        //  Colors
                        'sruc_sub_heading_color'       => '#000000',
                        'sruc_heading_first_color'     => '#f7b500',
                        'sruc_heading_second_color'    => '#f7b500',
                        'sruc_description_color'       => '#ffffff',

                        //  Sizes
                        'sruc_sub_heading_size'        => '50px',
                        'sruc_heading_first_size'     => '50px',
                        'sruc_heading_second_size'    => '50px',
                        'sruc_description_size'        => '14px',
                    )
                ),
                array(
                    'post_title' => 'Template 3',
                    'template_key' => 'sruc_template_3',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-3.png',
                    'meta' => array(
                        'sruc_sub_heading'        => 'THIS PAGE IS',
                        'sruc_heading'            => 'UNDER CONSTRUCTION',
                        'sruc_description'        => 'Lorem Ipsum door sit amet, consectetur tit, sed diam nonummy nibh euismod tincid dolore magna aliquam erat volutpat.',
                        'sruc_button_text'        => 'LEARN MORE',
                        'sruc_button_url'         => '#',

                        // Colors
                        'sruc_sub_heading_color'       => '#3f2b96',   
                        'sruc_heading_color'           => '#3f2b96',   
                        'sruc_description_color'       => '#333333',   
                        'sruc_button_text_color'       => '#ffffff',  
                        'sruc_button_bg_color'         => '#f7b500',   

                        // Sizes
                        'sruc_sub_heading_size'        => '30px',
                        'sruc_heading_size'            => '39px',
                        'sruc_description_size'        => '14px',
                        'sruc_button_size'             => '16px',
                    )
                ),
                array(
                    'post_title' => 'Template 4',
                    'template_key' => 'sruc_template_4',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-4.png',
                    'meta' => array(
                        'sruc_heading_first' => "THIS SITE IS",
                        'sruc_heading_first_color'           => '#333333',
                        'sruc_heading_first_size'            => '45px',

                        'sruc_heading_second' => "UNDER",
                        'sruc_heading_second_color'           => '#FFFFFF',
                        'sruc_heading_second_size'            => '55px',

                        'sruc_heading_third' => "CONSTRUCTION",
                        'sruc_heading_third_color'           => '#333333',
                        'sruc_heading_third_size'            => '45px',

                    )
                ),
                array(
                    'post_title' => 'Template 5',
                    'template_key' => 'sruc_template_5',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-5.png',
                    'meta' => array(
                        'sruc_sub_heading_first'        => "UNDER",
                        'sruc_sub_heading_second'       => "CONSTRUCTION",
                        'sruc_heading'            => 'WE ARE COMING SOON',
                        'sruc_short_description'  => 'CONTACT@YOURMAIL.COM',
                        'sruc_description'        => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.',

                        //  Colors
                        'sruc_heading_color'            => '#2d2d2d',
                        'sruc_heading_highlight_color'  => '#ffffffff',
                        'sruc_sub_heading_first_color'  => '#003457ff',
                        'sruc_sub_heading_second_color' => '#f7b500',
                        'sruc_description_color'        => '#666666',
                        'sruc_short_description_color'  => '#999999',

                        //  Sizes
                       'sruc_heading_size'             => '25px',
                       'sruc_sub_heading_first_size'   => '70px',
                       'sruc_sub_heading_second_size'  => '40px',
                       'sruc_description_size'         => '16px',
                       'sruc_short_description_size'   => '16px',

                    )
                ),

                array(
                    'post_title'     => 'Template 6',
                    'template_key'   => 'sruc_template_6',
                    'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-6.png',
                    'meta'           => array(
                        'sruc_sub_heading'        => 'Website is under construction',
                        'sruc_heading'            => 'COMING SOON',
                        'sruc_description'        => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'sruc_timestramp_days'    => '00',
                        'sruc_timestramp_hours'   => '00',
                        'sruc_timestramp_minutes' => '00',
                        'sruc_timestramp_seconds' => '00',

                        // Colors
                        'sruc_sub_heading_color'        => '#ffffff',
                        'sruc_heading_color'            => '#ffffff',  
                        'sruc_description_color'        => '#dfdfdf',
                        'sruc_timer_bg_color'           => '', 
                        'sruc_timer_text_color'         => '',
                        'sruc_timer_label_color'        => '#ffffff',

                        // Sizes
                        'sruc_sub_heading_size'         => '45px',
                        'sruc_heading_size'             => '115px',
                        'sruc_description_size'         => '20px',
                        'sruc_timer_size'               => '70px',
                        'sruc_timer_label_size'         => '18px',
                    )
                ),


            );

            $i = 0;
            foreach ( $templates as $template ) {
                $post_id = wp_insert_post(array(
                    'post_title'   => $template['post_title'],
                    'post_type'    => 'sruc_template',
                    'post_status'  => 'publish',
                    'post_content' => 'null'
                ));

                if ( $template['post_title'] == 'Template 1' ) {
                    update_option( 'sruc_active_template', $post_id );
                }


                if ( $post_id && !is_wp_error($post_id) ) {
                    foreach ( $template['meta'] as $key => $value ) {
                        update_post_meta($post_id, $key, $value);
                    }

                    self::save_countdown_total( $post_id, $template['meta'] );

                    update_post_meta($post_id, 'sruc_template_key', $template['template_key']);

                    if ( !empty($template['featured_image']) ) {
                       
                       
                        $image_url  = $template['featured_image'];
                        $image_name = 'template' . $i . '.png';
                        $i++;
                        $upload_dir = wp_upload_dir();

                        $image_data = @file_get_contents( $image_url );
                        if ( $image_data ) {
                            $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
                            $filename         = basename( $unique_file_name );

                            if ( wp_mkdir_p( $upload_dir['path'] ) ) {
                                $file = $upload_dir['path'] . '/' . $filename;
                            } else {
                                $file = $upload_dir['basedir'] . '/' . $filename;
                            }

                            file_put_contents( $file, $image_data );

                            $wp_filetype = wp_check_filetype( $filename, null );

                            $attachment = array(
                                'post_mime_type' => $wp_filetype['type'],
                                'post_title'     => sanitize_file_name( $filename ),
                                'post_content'   => '',
                                'post_status'    => 'inherit'
                            );

                            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

                            require_once ABSPATH . 'wp-admin/includes/image.php';
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

                            wp_update_attachment_metadata( $attach_id, $attach_data );

                            set_post_thumbnail( $post_id, $attach_id );
                        }
                    }
                }
            }
        }
    }

    public static function add_new_templates_on_update() {

        $existing = get_posts( array(
            'post_type'      => 'sruc_template',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'title'          => 'Template 6',
        ) );

        if ( !empty( $existing ) ) {
            return;
        }

        $new_templates = array(

            array(
                'post_title'     => 'Template 6',
                'template_key'   => 'sruc_template_6',
                'featured_image' => SRUC_PLUGIN_URL . 'assets/admin/images/template-6.png',
                'meta'           => array(
                    'sruc_sub_heading'        => 'Website is under construction',
                    'sruc_heading'            => 'COMING SOON',
                    'sruc_description'        => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    'sruc_timestramp_days'    => '00',
                    'sruc_timestramp_hours'   => '00',
                    'sruc_timestramp_minutes' => '00',
                    'sruc_timestramp_seconds' => '00',
                    'sruc_sub_heading_color'        => '#ffffff',
                    'sruc_heading_color'            => '#ffffff',  
                    'sruc_description_color'        => '#dfdfdf',
                    'sruc_timer_bg_color'           => '', 
                    'sruc_timer_text_color'         => '',
                    'sruc_timer_label_color'        => '#ffffff',
                    'sruc_sub_heading_size'         => '45px',
                    'sruc_heading_size'             => '115px',
                    'sruc_description_size'         => '20px',
                    'sruc_timer_size'               => '70px',
                    'sruc_timer_label_size'         => '18px',
                )
            ),

        );

        foreach ( $new_templates as $template ) {

            $exists = get_posts(array(
                'post_type'  => 'sruc_template',
                'meta_key'   => 'sruc_template_key',
                'meta_value' => $template['template_key'],
                'posts_per_page' => 1,
            ));

            if ( ! empty($exists) ) {
                continue;
            }

            $post_id = wp_insert_post(array(
                'post_title'   => $template['post_title'],
                'post_type'    => 'sruc_template',
                'post_status'  => 'publish',
                'post_content' => '',
            ));

            if ( ! is_wp_error($post_id) ) {

                foreach ($template['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }

                self::save_countdown_total( $post_id, $template['meta'] );

                update_post_meta($post_id, 'sruc_template_key', $template['template_key']);

                if ( ! empty($template['featured_image']) ) {

                    $image_url  = $template['featured_image'];
                    $upload_dir = wp_upload_dir();
                    $image_data = @file_get_contents($image_url);

                    if ( $image_data ) {
                        $filename = basename($image_url);
                        $file = $upload_dir['path'] . '/' . $filename;

                        file_put_contents($file, $image_data);

                        $wp_filetype = wp_check_filetype($filename, null);

                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title'     => sanitize_file_name($filename),
                            'post_status'    => 'inherit'
                        );

                        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                        require_once ABSPATH . 'wp-admin/includes/image.php';

                        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        set_post_thumbnail($post_id, $attach_id);
                    }
                }
            }
        }
    }

    private static function save_countdown_total( $post_id, $meta ) {
        if (
            ! isset( $meta['sruc_timestramp_days'] )
            && ! isset( $meta['sruc_timestramp_hours'] )
            && ! isset( $meta['sruc_timestramp_minutes'] )
            && ! isset( $meta['sruc_timestramp_seconds'] )
        ) {
            return;
        }

        $days    = isset( $meta['sruc_timestramp_days'] ) ? (int) $meta['sruc_timestramp_days'] : 0;
        $hours   = isset( $meta['sruc_timestramp_hours'] ) ? (int) $meta['sruc_timestramp_hours'] : 0;
        $minutes = isset( $meta['sruc_timestramp_minutes'] ) ? (int) $meta['sruc_timestramp_minutes'] : 0;
        $seconds = isset( $meta['sruc_timestramp_seconds'] ) ? (int) $meta['sruc_timestramp_seconds'] : 0;

        $total_seconds = ( $days * 86400 ) + ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;

        update_post_meta( $post_id, 'sruc_timestramp_total', time() + $total_seconds );
    }

    public static function fix_legacy_timestamp_totals() {
        $templates = get_posts( array(
            'post_type'      => 'sruc_template',
            'posts_per_page' => -1,
        ) );

        foreach ( $templates as $template ) {
            $total = get_post_meta( $template->ID, 'sruc_timestramp_total', true );

            // '00' was the old hardcoded default that was never derived from the
            // configured days/hours/minutes/seconds, leaving the countdown expired
            // on the front end even though the admin's Text tab showed the right values.
            if ( $total !== '00' && $total !== '' ) {
                continue;
            }

            $days    = (int) get_post_meta( $template->ID, 'sruc_timestramp_days', true );
            $hours   = (int) get_post_meta( $template->ID, 'sruc_timestramp_hours', true );
            $minutes = (int) get_post_meta( $template->ID, 'sruc_timestramp_minutes', true );
            $seconds = (int) get_post_meta( $template->ID, 'sruc_timestramp_seconds', true );

            $total_seconds = ( $days * 86400 ) + ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;

            if ( $total_seconds > 0 ) {
                update_post_meta( $template->ID, 'sruc_timestramp_total', time() + $total_seconds );
            }
        }
    }
}
