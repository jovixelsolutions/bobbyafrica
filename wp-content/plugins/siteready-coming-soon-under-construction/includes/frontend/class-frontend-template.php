<?php
class SRUC_Frontend_Template {

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'render_maintenance_template' ) );
    }

    private function get_post_with_meta($post_id) {
    
        $post = get_post($post_id, ARRAY_A);

        if (!$post) {
            return [];
        }

        $meta = get_post_meta($post_id);

        $flat_meta = [];
        foreach ($meta as $key => $values) {
            $flat_meta[$key] = is_array($values) ? reset($values) : $values;
        }

        return array_merge($post, $flat_meta);
        
    }

    public function render_maintenance_template() {

        // Auto Disable Start
        $auto_time = get_option('sruc_auto_disable_time');
        if (!empty($auto_time)) {
            $current_time = current_time('timestamp');
            $end_time = strtotime($auto_time);
            
            if ($end_time && $current_time >= $end_time) {
                update_option('sruc_enabled', 0);
                delete_option('sruc_auto_disable_time');
            }
        }

        // Auto Disable End

        if ( isset($_GET['sruc_preview']) && current_user_can('manage_options') ) {

            if ( !isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sruc_settings_nonce' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'siteready-coming-soon-under-construction' ) );
            }
            $active_template = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

        }else{
            
            if ( is_admin() || current_user_can( 'manage_options' ) ) {
                return;
            }

            $enabled = get_option( 'sruc_enabled', 0 );
            if ( ! $enabled ) {
                return;
            }

            $active_template = get_option( 'sruc_active_template' );
            if ( ! $active_template ) {
                return;
            }

        } 

        $template_key = get_post_meta( $active_template, 'sruc_template_key', true );
        $short_name_template_key = str_replace( 'sruc_', '', $template_key );
        $short_name_lower_template_key = strtolower( str_replace( '_', '-', $short_name_template_key ) );

        $template_file = SRUC_PLUGIN_DIR . 'templates/'.$short_name_lower_template_key.'.php';
        if ( ! file_exists( $template_file ) ) {
            return;
        }
        
        $template_post_data = $this->get_post_with_meta($active_template);
        
        status_header( 503 ); 
        nocache_headers();

        echo '<!DOCTYPE html><html ';
        language_attributes(); 
        echo '><head>';

        echo '<meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . esc_html( get_bloginfo( 'name' ) ) . ' - ' . esc_html__( 'Siteready Coming Soon Under Construction', 'siteready-coming-soon-under-construction' ) . '</title>';
        // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
        echo '<link rel="stylesheet" href="' . esc_url( SRUC_PLUGIN_URL . 'assets/frontend/css/' . $short_name_lower_template_key . '.css' ) . '" type="text/css" media="all">';

        // Output custom CSS if set
        $custom_css = isset($template_post_data['sruc_custom_css']) ? $template_post_data['sruc_custom_css'] : '';
        if (!empty($custom_css)) {
            echo '<style type="text/css">' . wp_kses($custom_css, array()) . '</style>';
        }

        echo '</head><body>';

        include $template_file;

        // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
        echo '<script src="' . esc_url( SRUC_PLUGIN_URL . 'assets/frontend/js/'.$short_name_lower_template_key.'.js' ) . '"></script>';

        echo '</body></html>';
        exit;
    }
}
