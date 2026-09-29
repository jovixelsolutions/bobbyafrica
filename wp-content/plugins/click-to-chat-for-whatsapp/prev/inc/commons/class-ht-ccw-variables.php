<?php
/**
 * Variables to use among plugin - try to avoid globals .. 
 * replaced variables.php 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CCW_Variables' ) ) :

class HT_CCW_Variables {

    /**
     * Db options table - ccw_options values
     * @var array get_options ccw_options
     */
    public $get_option;

    public function __construct() {
        $this->get_option();
    }

    public function get_option() {
        // Ensure an array is always available to avoid offset-on-false warnings
        $opts = get_option( 'ccw_options', array() );
        if ( ! is_array( $opts ) ) {
            $opts = array();
        }
        $this->get_option = $opts;
    }

    // public function ccw_enable() {
    //     $ccw_enable = esc_attr( $this->get_option['enable'] );
    //     return $ccw_enable;
    // }

}

endif; // END class_exists check
