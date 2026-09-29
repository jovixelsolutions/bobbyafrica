<?php

spl_autoload_register( 'sruc_autoloader' );
function sruc_autoloader( $class_name ) {
    if ( strpos( $class_name, 'SRUC_' ) === 0 ) {
        $short_name = str_replace( 'SRUC_', '', $class_name );
        $short_name_lower = strtolower( str_replace( '_', '-', $short_name ) );

        if ( $short_name === 'Plugin' ) {
            $folder = SRUC_PLUGIN_DIR . 'includes/';
        } elseif ( $short_name === 'Admin_Menu' ) {
            $folder = SRUC_PLUGIN_DIR . 'includes/admin/';
        } elseif ( strpos( $short_name, 'Frontend_Template' ) !== false ) {
            $folder = SRUC_PLUGIN_DIR . 'includes/frontend/';
        } else {
            $folder = SRUC_PLUGIN_DIR . 'includes/';
        }

        $file = $folder . 'class-' . $short_name_lower . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}

 