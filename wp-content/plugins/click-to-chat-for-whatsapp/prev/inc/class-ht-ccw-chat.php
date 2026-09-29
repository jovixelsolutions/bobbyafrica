<?php
/**
 * @uses ccw.php - initilaze at init
 * Adds floatings style using add_action - wp_footer 
 * 
 * get values, check things ..
 * include styles.php and 
 *  styles.php includes selected style template
 *      from commons/styles-list
 * 
 * @package ccw
 * @since 1.4  -  merge of chatbot.php, chatbot-mobile.php
 */



if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HT_CCW_Chat' ) ) :
    
class HT_CCW_Chat {


    // constructor
    public function __construct() {
        $this->floating_device();
    }

    /**
     * Add_action - wp_footer
     *
     * @uses this class contructor
     */
    public function floating_device() {
        add_action( 'wp_footer', array( $this, 'chat' ) );
    }




    /**
	 * Display - styles
	 * @uses - add_action hook - wp_footer
	 * @since 1.0
	 */
    function chat() {

        // similar - ht_ccw()->variables->get_option['enable'];
        $values = ht_ccw()->variables->get_option;
        
        $enable = isset( $values['enable'] ) ? esc_attr( $values['enable'] ) : '2';
        $num = isset( $values['number'] ) ? esc_attr( $values['number'] ) : '';
        $val = isset( $values['input_placeholder'] ) ? esc_attr( $values['input_placeholder'] ) : 'WhatsApp us';
        // $val = esc_attr( $values['input_placeholder'] );

        $position = isset( $values['position'] ) ? esc_attr( $values['position'] ) : '1';

        // $style = esc_attr( $values['style'] );


        // Analytics
        $google_analytics = '';
        $ga_category = '';
        $ga_action = '';
        $ga_label = '';



        if ( isset( $values['google_analytics'] ) ) {
            $google_analytics = 'true';

            $ht_ccw_ga = get_option( 'ht_ccw_ga', array() );

            $ga_category = esc_attr( $ht_ccw_ga['ga_category'] );
            $ga_action = esc_attr( $ht_ccw_ga['ga_action'] );
            $ga_label = esc_attr( $ht_ccw_ga['ga_label'] );

        }


        $page_title = esc_html( get_the_title() );

        /**
         * Pass values to JavaScript 
         * @var string google_analytics - is enable
         */
        $ht_ccw_var = array(
            'page_title' => $page_title,

            'google_analytics' => $google_analytics,
            'ga_category' => $ga_category,
            'ga_action' => $ga_action,
            'ga_label' => $ga_label,

            );

        wp_localize_script( 'ccw_app', 'ht_ccw_var', $ht_ccw_var );

        // enable
        // the output : is string '1'
        if( '1' === $enable ) {
            return;
        }
        
        // $ccw_option_values =  get_option('ccw_options');
        
        $this_page_id = get_the_ID();
        $pages_list_tohide = isset( $values['list_hideon_pages'] ) ? esc_attr( $values['list_hideon_pages'] ) : '';
        $pages_list_tohide_array = explode(',', $pages_list_tohide);
        
        
        if( ( is_single() || is_page() ) && in_array( $this_page_id, $pages_list_tohide_array ) ) {
            return;
        }
        
        
        if ( is_single() && isset( $values['hideon_posts'] ) ) {
            return;
        }
        
        if ( is_page() && isset( $values['hideon_page'] ) ) {
            if ( ( !is_home() ) && ( !is_front_page() ) ) {
                return;
            }
        }
        
        if ( is_home() && isset( $values['hideon_homepage'] ) ) {
            return;
        }
        
        if ( is_front_page() && isset( $values['hideon_frontpage'] ) ) {
            return;
        }
        
        if ( is_category() && isset( $values['hideon_category'] ) ) {
            return;
        }
        
        if ( is_archive() && isset( $values['hideon_archive'] ) ) {
            return;
        }
        
        if ( is_404() && isset( $values['hideon_404'] ) ) {
            return;
        }


        // Hide styles on this catergorys - list
        $list_hideon_cat = isset( $values['list_hideon_cat'] ) ? esc_attr( $values['list_hideon_cat'] ) : '';

        // avoid calling foreach, explode when hide on categorys list is empty
        if( $list_hideon_cat ) {

            //  Get current post Categorys list and create an array for that..
            $current_categorys_array = array();
            $current_categorys = get_the_category();
            foreach ( $current_categorys as $category ) {
                $current_categorys_array[] = strtolower($category->name);
            }

            $list_hideon_cat_array = explode(',', $list_hideon_cat);
        
            foreach ( $list_hideon_cat_array as $category ) {
                $category_trim = trim($category);
                if ( in_array( strtolower($category_trim), $current_categorys_array ) ) {
                    return;
                }
            }
        }
        
        // the value is getting in string 'num'
        if( '1' === $position ) {
            $p1 = 'bottom:'.(isset($values['position-1_bottom']) ? esc_attr( $values['position-1_bottom'] ) : '20px');
            $p2 = 'right:'.(isset($values['position-1_right']) ? esc_attr( $values['position-1_right'] ) : '20px');
        } elseif( '2' === $position ) {
            $p1 = 'bottom:'.(isset($values['position-2_bottom']) ? esc_attr( $values['position-2_bottom'] ) : '20px');
            $p2 = 'left:'.(isset($values['position-2_left']) ? esc_attr( $values['position-2_left'] ) : '20px');
        } elseif( '3' === $position ) {
            $p1 = 'top:'.(isset($values['position-3_top']) ? esc_attr( $values['position-3_top'] ) : '20px');
            $p2 = 'left:'.(isset($values['position-3_left']) ? esc_attr( $values['position-3_left'] ) : '20px');
        } elseif( '4' === $position ) {
            $p1 = 'top:'.(isset($values['position-4_top']) ? esc_attr( $values['position-4_top'] ) : '20px');
            $p2 = 'right:'.(isset($values['position-4_right']) ? esc_attr( $values['position-4_right'] ) : '20px');
        }



        include_once HT_CTC_PLUGIN_DIR .'prev/inc/commons/styles.php';

    }

}


// $chatbot = new CCW_Chatbot();
    

//  add_action( 'wp_head', 'chatbot' );
//  add_action( 'wp_footer', array( $chatbot, 'chatbot' ) );

endif; // END class_exists check
