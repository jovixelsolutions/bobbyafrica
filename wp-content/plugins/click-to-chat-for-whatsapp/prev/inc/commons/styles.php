<?php
/**
 * List of Styles
 * 
 * @uses chatbot.php, chatbot-mobile.php
 * 
 * @var values  -  is initiated in chat.php
 * $values = ht_ccw()->variables->get_option;
 * 
 * @package ccw
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ccw_options_cs = get_option( 'ccw_options_cs', array() );

//  if it is mobile device, or tab is_mobile is 1, if not 2 or any thing
$is_mobile = ht_ccw()->device_type->is_mobile;

$return_type = isset( $values['return_type'] ) ? esc_attr( $values['return_type'] ) : 'chat';

// Strict sanitization for values that will be interpolated into a JS string
// inside an HTML onclick attribute. esc_attr alone is insufficient there:
// the browser HTML-decodes the attribute before executing the JS, so &#039;
// becomes ' again and can break out of the JS string.
$group_id = isset( $values['group_id'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) $values['group_id'] ) : '';
$num      = preg_replace( '/[^0-9+]/', '', (string) ( isset( $num ) ? $num : '' ) );

$page_url     = get_permalink();
$text         = isset( $values['initial'] ) ? (string) $values['initial'] : '';
$initial_text = rawurlencode( str_replace( '{{url}}', (string) $page_url, $text ) );


// $an_on_load = "animated bounce infinite";
$an_on_load = isset( $ccw_options_cs['an_on_load'] ) ? esc_attr( $ccw_options_cs['an_on_load'] ) : 'no-animation';

// if yes - add's 'ccw-an' class to styles
// for class ccw-an - animated in javascript
$an_on_hover = isset( $ccw_options_cs['an_on_hover'] ) ? esc_attr( $ccw_options_cs['an_on_hover'] ) : 'ccw-no-hover-an';



/**
 * $redirect - redirect link for onclick attribute - window.open - direct link - using window.open
 * 
 * $redirect_a   -  full url - for 'a' tags - direct link - instead of calling another file using redirect_page
 */
$redirect = "";

// output : 1 - for mobile, '' - for desktop
if( 1 === $is_mobile ) {

    // selected style for mobile devices
    $style = isset( $values['stylemobile'] ) ? esc_attr( $values['stylemobile'] ) : '3';

    
    if ( 'group_chat' === $return_type ) {
        $redirect = "window.open('https://chat.whatsapp.com/$group_id', '_blank', 'noopener')";
        $redirect_a = "https://chat.whatsapp.com/$group_id";
    } else {
        $redirect = "window.open('https://api.whatsapp.com/send?phone=$num&text=$initial_text', '_blank', 'noopener')";
        $redirect_a = "https://api.whatsapp.com/send?phone=$num&text=$initial_text";
    }
} else {

    // selected style for desktop devices
    $style = isset( $values['style'] ) ? esc_attr( $values['style'] ) : '1';


    if ( isset( $values['app_first'] ) ) {

        // App First - so mobile based url
        if ( 'group_chat' === $return_type ) {
            $redirect = "window.open('https://chat.whatsapp.com/$group_id', '_blank', 'noreferrer')";
            $redirect_a = "https://chat.whatsapp.com/$group_id";
        } else {
            $redirect = "window.open('https://api.whatsapp.com/send?phone=$num&text=$initial_text', '_blank', 'noreferrer')";
            $redirect_a = "https://api.whatsapp.com/send?phone=$num&text=$initial_text";
        }


    } else {

        // General - Desktop url
        if ( 'group_chat' === $return_type ) {
            $redirect = "window.open('https://chat.whatsapp.com/$group_id', '_blank', 'noreferrer')";
            $redirect_a = "https://chat.whatsapp.com/$group_id";
        } else {
            $redirect = "window.open('https://web.whatsapp.com/send?phone=$num&text=$initial_text', '_blank', 'noreferrer')";
            $redirect_a = "https://web.whatsapp.com/send?phone=$num&text=$initial_text";
        }

    }
    
    
}

// floating style template path
$style = sanitize_file_name( $style );
$path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'prev/inc/commons/styles-list/style-' . $style. '.php';

$version = HT_CTC_VERSION;
?>
<!-- Click to Chat - prev - https://holithemes.com/plugins/click-to-chat/ v<?php echo esc_attr($version); ?> -->
<?php

if ( is_file( $path ) ) {
    include_once $path;
}
