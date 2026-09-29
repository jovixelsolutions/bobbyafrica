<?php
/**
 * Plugin Name: Siteready Coming Soon Under Construction
 * Plugin URI: 
 * Description: Display a customizable coming soon or maintenance page with countdown and live preview — perfect for launches, redesigns, or site updates.
 * Version: 1.0.9
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: themescart
 * Author URI: https://www.themescart.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: siteready-coming-soon-under-construction
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SRUC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SRUC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SRUC_PLUGIN_VERSION', '1.0.9');
define('SRUC_THEME_BUNDLE_IMAGE_URL', plugin_dir_url(__FILE__) . 'assets/admin/images/get-theme-bundle-img.png');
define('SRUC_ELEMENTO_API_MAIN', 'https://www.themescarts.com/');

require_once(SRUC_PLUGIN_DIR . 'includes/class-autoloader.php');
if (is_admin()) {
    require_once SRUC_PLUGIN_DIR . 'admin/themes-page/themes-page.php';
    add_action('admin_enqueue_scripts', function ($hook) {

        if ($hook == 'toplevel_page_sruc-settings') {
            wp_enqueue_style('bootstrap-admin-css', SRUC_PLUGIN_URL . 'assets/admin/css/bootstrap-min.css', array(), SRUC_PLUGIN_VERSION);
            wp_enqueue_script('bootstrap-admin-js', SRUC_PLUGIN_URL . 'assets/admin/js/bootstrap-bundle-min.js', array('jquery'), SRUC_PLUGIN_VERSION, true);
        }

        wp_enqueue_style('sruc-theme-admin-style', SRUC_PLUGIN_URL . 'assets/admin/css/admin-theme-page.css', [], SRUC_PLUGIN_VERSION);
        wp_enqueue_script('sruc-theme-admin-js', SRUC_PLUGIN_URL . 'assets/admin/js/admin-theme-page.js', ['jquery'], SRUC_PLUGIN_VERSION, true);
        wp_localize_script(
            'sruc-theme-admin-js',
            'sruc_obj',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
            ]
        );
    });
}

function sruc_initialize_plugin()
{
    $plugin = new SRUC_Plugin();
    $plugin->run();
}
add_action('plugins_loaded', 'sruc_initialize_plugin');


register_activation_hook(__FILE__, 'sruc_plugin_activation_hook');
function sruc_plugin_activation_hook()
{
    update_option('sruc_show_activation_popup', true);
}

add_action('wp_login', 'sruc_user_login_hook', 10, 2);
function sruc_user_login_hook($user_login, $user)
{
    update_option('sruc_show_activation_popup', true);
}


add_action('admin_footer', 'sruc_custom_popup_html');
function sruc_custom_popup_html()
{
    $current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

    if (!get_option('sruc_show_activation_popup')) {
        return;
    }
    if ($current_tab === 'theme_templates') {
        return;
    }
    ?>
    <style>
        .faq-copy-id {
            cursor: pointer;
        }

        #sruc-popup-overlay {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: auto;
            height: auto;
            background: transparent;
            z-index: 9999;
            display: block;
        }

        #sruc-popup-content {
            background: #fff;
            padding: 30px;
            border-radius: 6px;
            max-width: 550px;
            text-align: center;
            width: 100%;
            position: relative;
            border: 2px solid rgba(226, 230, 235, 0.7);
        }

        #sruc-popup-content .sruc-popup-template-btn,
        #sruc-popup-content .sruc-popup-bundle-btn {
            background: linear-gradient(to right, #d4335d, #ff5627);
            border: none;
            border-radius: 30px;
            font-size: 18px;
            padding: 5px 25px;
            font-family: Prompt, sans-serif;
        }

        #sruc-popup-content .sruc-popup-template-btn:hover,
        #sruc-popup-content .sruc-popup-bundle-btn:hover {
            background: #101110;
        }

        .sruc-popup-wrap {
            display: flex;
            gap: 25px;
            justify-content: center;
        }

        #sruc-popup-content img {
            width: 100%;
            object-fit: contain;
        }

        #sruc-popup-content .sruc-popup-bundle-btn {
            background: -webkit-linear-gradient(left, #00a8d5, #00ddd8);
        }

        #sruc-popup-content h2 {
            font-size: 18px;
            margin: 20px 0;
            font-family: Prompt, sans-serif;
        }

        #sruc-popup-content .sruc-popup-dismiss {
            rotate: 45deg;
            position: absolute;
            top: 0;
            right: 12px;
            font-size: 31px;
            cursor: pointer;
            color: black;
        }
    </style>
    <script>
        window.sruc_obj = window.sruc_obj || {
            ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
        };
    </script>

    <script>
        jQuery(document).ready(function ($) {

            $('#sruc-popup-content .sruc-popup-dismiss, #sruc-popup-content .sruc-popup-template-btn').on('click', function () {
                $.ajax({
                    url: sruc_obj.ajax_url,
                    type: 'POST',
                    data: { action: 'sruc_get_notice_dismiss' },
                    success: function (response) {
                        $('#sruc-popup-overlay').hide();
                        // $('.sruc-premium-floating-btn').fadeIn(); 
                        $('.sruc-premium-floating-btn')
                            .fadeIn()
                            .attr('style', 'display:inline-block !important;');
                    }
                });
            });

        });
    </script>

    <div id="sruc-popup-overlay">
        <div id="sruc-popup-content">
            <span class="dashicons dashicons-plus-alt2 sruc-popup-dismiss"></span>
            <img src="<?php echo esc_url(SRUC_THEME_BUNDLE_IMAGE_URL); ?>" alt="Bundle Image">
            <h2><?php echo esc_html('Take Your Website to the Next Level with Premium Themes – Starting at Just $39!'); ?>
            </h2>
            <div class="sruc-popup-wrap">
                <?php
                $nonce = wp_create_nonce('sruc_settings_nonce');
                ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=sruc-settings&tab=theme_templates&_wpnonce=' . $nonce)); ?>"
                    class="button button-primary sruc-popup-template-btn"><?php echo esc_html('Website Templates'); ?></a>
                <a href="<?php echo esc_url(SRUC_ELEMENTO_API_MAIN) . 'products/wordpress-theme-bundle'; ?>" target="_blank"
                    class="button button-primary sruc-popup-bundle-btn"><?php echo esc_html('Get Theme Bundle'); ?></a>
            </div>
        </div>
    </div>
    <?php
}

register_deactivation_hook(__FILE__, 'sruc_plugin_deactivation_hook');
function sruc_plugin_deactivation_hook()
{
    delete_option('sruc_show_deactivation_popup');
}

add_action('wp_logout', function () {
    delete_option('sruc_show_deactivation_popup');
});

add_action('admin_enqueue_scripts', function () {

    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    if ($current_page === 'templates_page') {
        return;
    }

    if (!get_option('sruc_show_activation_popup')) {
        update_option('sruc_show_deactivation_popup', true);
    }

    $dismissed = get_option('sruc_show_deactivation_popup');

    wp_register_style('sruc-admin-styles', false);
    wp_enqueue_style('sruc-admin-styles');

    if (!$dismissed) {
        $css = '.sruc-premium-floating-btn { display: none !important; position: fixed; bottom: 20px; right: 20px; z-index: 9999; padding: 10px 15px; }';
    } else {
        $css = '.sruc-premium-floating-btn { display: inline-block; position: fixed; bottom: 20px; right: 20px; z-index: 9999; padding: 10px 15px; }';
    }

    wp_add_inline_style('sruc-admin-styles', $css);
});


add_action('admin_footer', function () {

    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $current_tab  = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

    if ($current_page === 'sruc-settings' && $current_tab === 'theme_templates') {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
            wp_die(esc_html__('Security check failed. 1', 'siteready-coming-soon-under-construction'));
        }
        return;
    }
    ?>
    <?php
    $nonce = wp_create_nonce('sruc_settings_nonce');
    ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=sruc-settings&tab=theme_templates&_wpnonce=' . $nonce)); ?>"
        class="sruc-premium-floating-btn button button-primary">
        <?php echo esc_html('Website Templates'); ?>
    </a>
    <?php
});

add_action('in_admin_header', 'sruc_show_admin_topbar');
function sruc_show_admin_topbar()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $screen = get_current_screen();
    if (isset($screen->base) && in_array($screen->base, ['post', 'post-new'], true)) {
        return;
    }

    $theme = wp_get_theme();
    $author = $theme->get('Author');
    $textdomain = $theme->get('TextDomain');


    $shop_url = SRUC_ELEMENTO_API_MAIN . 'products/wordpress-theme-bundle';

    if (strtolower(trim($author)) === 'themes carts') {
        $constant_name = strtoupper(str_replace('-', '_', $textdomain)) . '_BUY_NOW';
        if (defined($constant_name)) {
            $shop_url = constant($constant_name);
        }
    }

    echo '<div class="bundle-marque">              
        <div class="bundle-content">
            <p class="offer-code">🎉 20% Mega Sale Discount 🎉Use Code: <strong>"HOTDEALS20"</strong> 🎁 </p>
        </div>
        <div class="bundle-button">
            <a class="main-bar-buy-btn sruc-topbar-btn" target="_blank" href="' . esc_url($shop_url) . '">Buy Now</a>
        </div>
    </div>';
}