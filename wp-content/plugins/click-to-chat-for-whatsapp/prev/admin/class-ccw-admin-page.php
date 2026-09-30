<?php
/**
* Content of the options page .. 
* admin_menu.php  -> settings_page.php  -> admin_page.php
*
* @package ccw
* @subpackage Administration
* @since 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CCW_Admin_Page' ) ) :
    
class CCW_Admin_Page {

    function ccw_custom_settings() {
        
        register_setting( 'ccw_settings_group', 'ht_ctc_switch' , 'ccw_options_sanitize' );
        register_setting( 'ccw_settings_group', 'ccw_options' , 'ccw_options_sanitize' );
    
        add_settings_section( 'ccw_settings', '', array( $this, 'ccw_settings_section' ), 'ccw_options_settings' );
    
        add_settings_field( 'ht_ctc_switch', __( 'Switch to New Interface' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_switch_cb' ), 'ccw_options_settings', 'ccw_settings' );

        add_settings_field( 'ccw_enable', __( 'Enable Floating Styles' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_enable_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_enable_sc', __( 'Enable ShortCodes' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_enable_sc_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_return_type', __( 'Return Type' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_return_type_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_number', __( 'WhatsApp Number' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_number_input_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_pre_text', __( 'Initial Message' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_prefix_message_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_group_id', __( 'Group Id' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_group_id_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_style', __( 'Style for Desktops' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_style_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_style_mobile', __( 'Style for Mobile Devices' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_style_mobile_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_position', __( 'Position to Place' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_position_input_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_placeholder', __( 'Text to Display' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_input_placeholder_cb' ), 'ccw_options_settings', 'ccw_settings' );
        
        add_settings_field( 'ccw_google_analytics', __( 'Google Analytics' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_google_analytics_cb' ), 'ccw_options_settings', 'ccw_settings' );
        
        add_settings_field( 'ccw_checkbox', __( 'Hide Based on post type' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_checkbox_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_list_id_tohide', __( "Posts, Pages Id's to Hide" , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_list_id_tohide_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_list_cat_tohide', __( 'Categorys to Hide' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_list_cat_tohide_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_custom_shortcode', __( 'Shortcode name' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_custom_shortcode_cb' ), 'ccw_options_settings', 'ccw_settings' );
        add_settings_field( 'ccw_app_first', __( 'App First / If Cache Issue' , 'click-to-chat-for-whatsapp' ), array( $this, 'ccw_app_first_cb' ), 'ccw_options_settings', 'ccw_settings' );
 
    }

    
    // heading
    function ccw_settings_section() {
        echo '<h1>Click to Chat - Interface-1</h1>';
    }



    /**
     * Switch interface
     */
    function ccw_switch_cb() {
        $options = get_option( 'ht_ctc_switch', array() );
        $interface_value = isset( $options['interface'] ) ? esc_attr( $options['interface'] ) : 'no';
        ?>

        <div class="ccw-settings-field notice notice-info inline" style="padding: 20px; border-left: 4px solid #72aee6; background: #f0f6fc; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 6px;">
            <h3 style="margin-top: 0; color: #0a4b78;">✨ Discover the New Interface!</h3>
            <p class="description" style="font-size: 14px; margin-bottom: 10px; color: #1c2b36;">We've built a completely redesigned experience with powerful new features, faster performance, and a beautiful modern UI. We highly recommend making the switch!</p>
            <p class="description" style="font-size: 13px; margin-bottom: 15px; color: #50575e;">
                <em>Note: You will just need to briefly reconfigure your settings after switching.</em>
            </p>

            <select name="ht_ctc_switch[interface]" class="regular-text" style="padding: 4px 8px; font-size: 14px; border-radius: 6px; border-color: #8c8f94;">
                <option value="no" <?php echo $interface_value === 'no' ? 'SELECTED' : ''; ?> >Previous Interface</option>
                <option value="yes" <?php echo $interface_value === 'yes' ? 'SELECTED' : ''; ?> >Try the New Interface!</option>
            </select>
            <p class="description" style="margin-top: 12px;"> 
                <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/new-interface/" style="font-weight: 600; text-decoration: none; color: #2271b1;">Explore the New Features &rarr;</a>
            </p>
        </div>

        <?php
    }




    // enable / disable floating styles
    function ccw_enable_cb() {
        $ccw_enable = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[enable]" class="regular-text">
                <option value="1">No</option>
                <option value="2" <?php echo isset($ccw_enable['enable']) && esc_attr( $ccw_enable['enable'] ) === '2' ? 'SELECTED' : ''; ?>  >Yes</option>
            </select>
        </div>
        <?php
    }

    // enable / disable shortcodes
    function ccw_enable_sc_cb() {
        $ccw_enable_sc = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[enable_sc]" class="regular-text">
                <option value="1">No</option>
                <option value="2" <?php echo isset($ccw_enable_sc['enable_sc']) && esc_attr( $ccw_enable_sc['enable_sc'] ) === '2' ? 'SELECTED' : ''; ?>  >Yes</option>
            </select>
            <p class="description">If Selected - No - then Hides Shortcodes and its syntax - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/enable-disable-styles/">more info</a> </p>
        </div>
        <?php
    }

    // Return type  - chat or group chat
    function ccw_return_type_cb() {
        $ccw_return_type = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[return_type]" class="regular-text">
                <option value="chat" <?php echo isset($ccw_return_type['return_type']) && esc_attr( $ccw_return_type['return_type'] ) === 'chat' ? 'SELECTED' : ''; ?> >Chat</option>
                <option value="group_chat" <?php echo isset($ccw_return_type['return_type']) && esc_attr( $ccw_return_type['return_type'] ) === 'group_chat' ? 'SELECTED' : ''; ?> >Group chat - Invite</option>
            </select>
            <p class="description">Default return type for Floating Style, shortcodes. But for shortcodes can change using shortcode attributes - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/return-type-chat-or-group-chat/">more info</a> </p>
        </div>
        <?php
    }


    // Desktop - select style 
    function ccw_style_cb() {
        $ccw_style = get_option( 'ccw_options', array() );
        $style_value = isset( $ccw_style['style'] ) ? esc_attr( $ccw_style['style'] ) : 1;
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[style]" class="regular-text">
                <option value="1" <?php echo $style_value === '1' ? 'SELECTED' : ''; ?> >Style-1</option>
                <option value="2" <?php echo $style_value === '2' ? 'SELECTED' : ''; ?> >Style-2</option>
                <option value="3" <?php echo $style_value === '3' ? 'SELECTED' : ''; ?> >Style-3</option>
                <option value="4" <?php echo $style_value === '4' ? 'SELECTED' : ''; ?> >Style-4</option>
                <option value="5" <?php echo $style_value === '5' ? 'SELECTED' : ''; ?> >Style-5</option>
                <option value="6" <?php echo $style_value === '6' ? 'SELECTED' : ''; ?> >Style-6</option>
                <option value="7" <?php echo $style_value === '7' ? 'SELECTED' : ''; ?> >Style-7</option>
                <option value="8" <?php echo $style_value === '8' ? 'SELECTED' : ''; ?> >Style-8</option>
                <option value="9" <?php echo $style_value === '9' ? 'SELECTED' : ''; ?> >Style-9</option>
                <option value="99" <?php echo $style_value === '99' ? 'SELECTED' : ''; ?> >Add your own image / GIF (Style-99)</option>
                <option value="0" <?php echo $style_value === '0' ? 'SELECTED' : ''; ?> >Hide on Desktop Devices</option>
            </select>
            <p class="description"> - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/styles/">List of Styles</a> </p>
            <p class="description">These styles are customizable - <a target="_blank" href="<?php echo esc_url(admin_url( 'admin.php?page=ccw-edit-styles' )); ?>">Customize Styles</a> </p>
        </div>
        <?php
    }

    function ccw_style_mobile_cb() {
        $ccw_stylemobile = get_option( 'ccw_options', array() );
        $style_mobile_value = isset( $ccw_stylemobile['stylemobile'] ) ? esc_attr( $ccw_stylemobile['stylemobile'] ) : '1';
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[stylemobile]" class="regular-text">
                <option value="1" <?php echo $style_mobile_value === '1' ? 'SELECTED' : ''; ?> >Style-1</option>
                <option value="2" <?php echo $style_mobile_value === '2' ? 'SELECTED' : ''; ?> >Style-2</option>
                <option value="3" <?php echo $style_mobile_value === '3' ? 'SELECTED' : ''; ?> >Style-3</option>
                <option value="4" <?php echo $style_mobile_value === '4' ? 'SELECTED' : ''; ?> >Style-4</option>
                <option value="5" <?php echo $style_mobile_value === '5' ? 'SELECTED' : ''; ?> >Style-5</option>
                <option value="6" <?php echo $style_mobile_value === '6' ? 'SELECTED' : ''; ?> >Style-6</option>
                <option value="7" <?php echo $style_mobile_value === '7' ? 'SELECTED' : ''; ?> >Style-7</option>
                <option value="8" <?php echo $style_mobile_value === '8' ? 'SELECTED' : ''; ?> >Style-8</option>
                <option value="9" <?php echo $style_mobile_value === '9' ? 'SELECTED' : ''; ?> >Style-9</option>
                <option value="99" <?php echo $style_mobile_value === '99' ? 'SELECTED' : ''; ?> >Add your own image / GIF (Style-99)</option>
                <option value="0" <?php echo $style_mobile_value === '0' ? 'SELECTED' : ''; ?> >Hide on Mobile Devices</option>
            </select>
        </div>
        <?php
    }

    function ccw_number_input_cb() {
        $ccw_number = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[number]" value="<?php echo isset($ccw_number['number']) ? esc_attr( $ccw_number['number'] ) : ''; ?>" id="whatsapp_number" type="text" class="regular-text">
            <p class="description">Enter whatsapp number with country code ( e.g. 916123456789 ) please dont include +, ( here in e.g. 91 is country code 6123456789 is mobile number - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/whatsapp-number/">more info</a> ) </p>
        </div>
        <?php
    }


    function ccw_prefix_message_cb() {
        $ccw_initial = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[initial]" value="<?php echo isset($ccw_initial['initial']) ? esc_attr( $ccw_initial['initial'] ) : ''; ?>" id="whatsapp_initial" type="text" class="regular-text">
            <p class="description">Initial message ( pre-filled ), placeholder {{url}} to add webpage url -  <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/pre-filled-message/">more info</a> </p>
        </div>
        <?php
    }


    function ccw_group_id_cb() {
        $ccw_group_id = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[group_id]" value="<?php echo isset($ccw_group_id['group_id']) ? esc_attr( $ccw_group_id['group_id'] ) : ''; ?>" id="whatsapp_group_id" type="text" class="regular-text">
            <p class="description">Enter whatsapp Group Id - E.g. 9EHLsEsOeJk6AVtE8AvXiA  - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/find-whatsapp-group-id/">more info</a> </p>
        </div>
        <?php
    }

    // position
    function ccw_position_input_cb() {
        $ccw_position = get_option( 'ccw_options', array() );
        $ccw_position_value = isset($ccw_position['position']) ? esc_attr( $ccw_position['position'] ) : '1';
        ?>
        <div class="ccw-settings-field">
            <select name="ccw_options[position]" class="select regular-text">
                <option value="1"  <?php echo $ccw_position_value === '1' ? 'SELECTED' : ''; ?> >bottom right</option>
                <option value="2"  <?php echo $ccw_position_value === '2' ? 'SELECTED' : ''; ?> >bottom left</option>
                <option value="3"  <?php echo $ccw_position_value === '3' ? 'SELECTED' : ''; ?> >top left</option>
                <option value="4"  <?php echo $ccw_position_value === '4' ? 'SELECTED' : ''; ?> >top right</option>
            </select>
            <p class="description">e.g. 10px - please add css units as suffix, e.g. 10px, 10%, 10rem, 10em .. <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/position-to-place/">more info</a> </p>
        </div>

        <div class="display-none position position-1 bottom-right" style="margin-top: 10px;">
            <label for="position-1_bottom">position_bottom: </label>
            <input name="ccw_options[position-1_bottom]" value="<?php echo isset($ccw_position['position-1_bottom']) ? esc_attr( $ccw_position['position-1_bottom'] ) : ''; ?>" id="position-1_bottom" type="text" class="regular-text">
            <br><br>
            <label for="position-1_right">position_right: </label>
            <input name="ccw_options[position-1_right]" value="<?php echo isset($ccw_position['position-1_right']) ? esc_attr( $ccw_position['position-1_right'] ) : ''; ?>" id="position-1_right" type="text" class="regular-text">
        </div>

        <div class="display-none position position-2 bottom-left" style="margin-top: 10px;">
            <label for="position-2_bottom">position_bottom: </label>
            <input name="ccw_options[position-2_bottom]" value="<?php echo isset($ccw_position['position-2_bottom']) ? esc_attr( $ccw_position['position-2_bottom'] ) : ''; ?>" id="position-2_bottom" type="text" class="regular-text">
            <br><br>
            <label for="position-2_left">position_left: </label>
            <input name="ccw_options[position-2_left]" value="<?php echo isset($ccw_position['position-2_left']) ? esc_attr( $ccw_position['position-2_left'] ) : ''; ?>" id="position-2_left" type="text" class="regular-text">
        </div>

        <div class="display-none position position-3 top-left" style="margin-top: 10px;">
            <label for="position-3_top">position_top: </label>
            <input name="ccw_options[position-3_top]" value="<?php echo isset($ccw_position['position-3_top']) ? esc_attr( $ccw_position['position-3_top'] ) : ''; ?>" id="position-3_top" type="text" class="regular-text">
            <br><br>
            <label for="position-3_left">position_left: </label>
            <input name="ccw_options[position-3_left]" value="<?php echo isset($ccw_position['position-3_left']) ? esc_attr( $ccw_position['position-3_left'] ) : ''; ?>" id="position-3_left" type="text" class="regular-text">
        </div>

        <div class="display-none position position-4 top-right" style="margin-top: 10px;">
            <label for="position-4_top">position_top: </label>
            <input name="ccw_options[position-4_top]" value="<?php echo isset($ccw_position['position-4_top']) ? esc_attr( $ccw_position['position-4_top'] ) : ''; ?>" id="position-4_top" type="text" class="regular-text">
            <br><br>
            <label for="position-4_right">position_right: </label>
            <input name="ccw_options[position-4_right]" value="<?php echo isset($ccw_position['position-4_right']) ? esc_attr( $ccw_position['position-4_right'] ) : ''; ?>" id="position-4_right" type="text" class="regular-text">
        </div>

        <?php 
    }

    // Text - placeholder
    function ccw_input_placeholder_cb() {
        $ccw_placeholder = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[input_placeholder]" value="<?php echo isset($ccw_placeholder['input_placeholder']) ? esc_attr( $ccw_placeholder['input_placeholder'] ) : ''; ?>" id="input_placeholder" type="text" class="regular-text">
            <p class="description"> - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/text-to-display/">more info</a> </p>
        </div>
        <?php
    }


    // Enable Google Analytics 
    function ccw_google_analytics_cb() {
        $ccw_google_analytics = get_option( 'ccw_options', array() );

        if ( isset( $ccw_google_analytics['google_analytics'] ) ) {
            ?>
            <label>
                <input name="ccw_options[google_analytics]" type="checkbox" value="1" <?php checked( $ccw_google_analytics['google_analytics'], 1 ); ?> id="google_analytics" />
                Google Analytics
            </label>
            <?php
        } else {
            ?>
            <label>
                <input name="ccw_options[google_analytics]" type="checkbox" value="1" id="google_analytics" />
                Google Analytics
            </label>
            <?php
        }
        ?>
        
        <p class="description"> If Google Analytics is installed - creates an Event at there - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/google-analytics/">more info</a> </p>
        <p class="description"> Customize Event Values - <a target="_blank" href="<?php echo esc_url(admin_url( 'admin.php?page=ccw-edit-styles#ga-analytics' )); ?>"><?php esc_html_e( 'Customize Styles' , 'click-to-chat-for-whatsapp' ) ?></a>  </p>
        <p class="description"> Using - <a target="_blank" href="https://holithemes.com/google-analytics-for-click-to-chat-for-whatsapp-plugin/">Google Tag Manager</a> </p>
        <?php
    }


    function ccw_checkbox_cb() {
        $ccw_checkbox = get_option( 'ccw_options', array() );
        ?>
        <fieldset>
            <label>
                <input name="ccw_options[hideon_posts]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_posts']) ? $ccw_checkbox['hideon_posts'] : 0, 1 ); ?> />
                Hide on - Posts
            </label><br>
            <label>
                <input name="ccw_options[hideon_page]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_page']) ? $ccw_checkbox['hideon_page'] : 0, 1 ); ?> />
                Hide on - Pages
            </label><br>
            <label>
                <input name="ccw_options[hideon_homepage]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_homepage']) ? $ccw_checkbox['hideon_homepage'] : 0, 1 ); ?> />
                Hide on - Home Page
            </label><br>
            <label>
                <input name="ccw_options[hideon_frontpage]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_frontpage']) ? $ccw_checkbox['hideon_frontpage'] : 0, 1 ); ?> />
                Hide on - Front Page
            </label><br>
            <label>
                <input name="ccw_options[hideon_category]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_category']) ? $ccw_checkbox['hideon_category'] : 0, 1 ); ?> />
                Hide on - Category
            </label><br>
            <label>
                <input name="ccw_options[hideon_archive]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_archive']) ? $ccw_checkbox['hideon_archive'] : 0, 1 ); ?> />
                Hide on - Archive
            </label><br>
            <label>
                <input name="ccw_options[hideon_404]" type="checkbox" value="1" <?php checked( isset($ccw_checkbox['hideon_404']) ? $ccw_checkbox['hideon_404'] : 0, 1 ); ?> />
                Hide on - 404 Page
            </label>
        </fieldset>
        <p class="description">Check to hide - Hide - Styles - based on type of the page <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/show-hide-styles-based-on-type-of-the-page/">more info</a> </p>
        <?php
    }

    function ccw_list_id_tohide_cb() {
        $ccw_list_id_tohide = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[list_hideon_pages]" value="<?php echo isset($ccw_list_id_tohide['list_hideon_pages']) ? esc_attr( $ccw_list_id_tohide['list_hideon_pages'] ) : ''; ?>" id="ccw_list_id_tohide" type="text" class="regular-text">
            <p class="description"> Add Post, Pages, Media - ID's to hide, Add multiple id's separate with a comma ( , ) - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/show-hide-styles-based-on-id/">more info</a> </p>
        </div>
        <?php
    }

    function ccw_list_cat_tohide_cb() {
        $ccw_list_cat_tohide = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[list_hideon_cat]" value="<?php echo isset($ccw_list_cat_tohide['list_hideon_cat']) ? esc_attr( $ccw_list_cat_tohide['list_hideon_cat'] ) : ''; ?>" id="ccw_list_cat_tohide" type="text" class="regular-text">
            <p class="description">Category name's to hide, Add multiple Categories separate with a comma ( , ) - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/hide-styles-based-on-category/">more info</a> </p>
        </div>
        <?php
    }

    function ccw_custom_shortcode_cb() {
        $ccw_shortcode = get_option( 'ccw_options', array() );
        ?>
        <div class="ccw-settings-field">
            <input name="ccw_options[shortcode]" value="<?php echo isset($ccw_shortcode['shortcode']) ? esc_attr( $ccw_shortcode['shortcode'] ) : ''; ?>" id="shortcode" type="text" class="regular-text">
            <?php
            $shortcode_list = '';
            // global used here is defined by wordpress 
            foreach ($GLOBALS['shortcode_tags'] AS $key => $value) {
               $shortcode_list .= $key . ', ';
             }
            ?>
            <p class="description"> Default values is 'chat', can customize shortcode name - <a target="_blank" href="https://holithemes.com/plugins/whatsapp-chat/change-shortcode-name/">more info</a> </p>
            <p class="description"> please dont change to already existing shortcode name </p>
        </div>
        <?php
    }




    function ccw_app_first_cb() {
        $ccw_app_first = get_option( 'ccw_options', array() );
        $is_checked = isset( $ccw_app_first['app_first'] ) ? $ccw_app_first['app_first'] : '';
        ?>
        <div class="ccw-settings-field">
            <label style="display: flex; align-items: center; gap: 8px;">
                <input name="ccw_options[app_first]" type="checkbox" value="1" <?php checked( $is_checked, 1 ); ?> id="app_first" />
                <span style="font-weight: 600; color: #1d2327;">Open WhatsApp Desktop App / api.whatsapp links</span>
            </label>
            <p class="description" style="margin-top: 8px;">If checked, desktop users are redirected to <code>api.whatsapp.com</code> which automatically opens the native WhatsApp Desktop App.</p>
            <p class="description" style="margin-top: 4px;">If unchecked, desktop users will be directed to WhatsApp Web (<code>web.whatsapp.com</code>).</p>
        </div>
        <?php
    }






    // Sanitize callback ..
    function ccw_options_sanitize( $input ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'not allowed to modify - please contact admin ' );
        }
        
        $new_input = array();

        foreach ($input as $key => $value) {
            if( isset( $input[$key] ) ) {
                $new_input[$key] = sanitize_text_field( $input[$key] );
            }
        }

        return $new_input;
    }


}



$admin_page = new CCW_Admin_Page();

add_action( 'admin_init', array( $admin_page,'ccw_custom_settings' ) );

endif; // END class_exists check
