<?php
/**
* Settings page - customize styles  ( settings_page.php is main page and this is sub page )
* options page
* content of this page load / continue at admin_page_customize_styles.php
*  cs  - customize styles
*
* @package ccw
* @subpackage Administration
* @since 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap">

<?php settings_errors(); ?>
    
        <div class="options-container">
            <div class="options-form">
                <form action="options.php" method="post">
                    <?php settings_fields( 'ccw_settings_group_cs' ); ?>
                    <?php do_settings_sections( 'ccw_options_settings_cs' ) ?>
                    <?php submit_button() ?>
                </form>
            </div>
        </div>
        
</div>