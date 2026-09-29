<?php
/**
 * Starting point for the admin side of this plugin.
 *
 * Include other files that are required on the admin side.
 *
 * In click-to-chat.php this file will be loaded when is_admin returns true.
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$ht_ctc_othersettings = get_option( 'ht_ctc_othersettings' );


// Includes
require_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/class-ht-ctc-formatting.php';

// others - hooks ....
require_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/class-ht-ctc-admin-hooks.php';

// add scripts
require_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-scripts.php';

// Main, Chat admin page
require_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-main-page.php';

// greetings
require_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-greetings-page.php';

do_action( 'ht_ctc_ah_admin_includes_after_main_page' );

// group admin page
if ( isset( $ht_ctc_othersettings['enable_group'] ) ) {
	include_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-group-page.php';
}

// share admin page
if ( isset( $ht_ctc_othersettings['enable_share'] ) ) {
	include_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-share-page.php';
}

// customize
require_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-customize-styles.php';

// other settings - enable options ..
require_once HT_CTC_PLUGIN_DIR . 'new/admin/class-ht-ctc-admin-other-settings.php';

// meta boxes - change values at page level
require_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/class-ht-ctc-metabox.php';

// admin demo
require_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_demo/class-ht-ctc-admin-demo.php';

// feedback
// require_once HT_CTC_PLUGIN_DIR . 'new/admin/feedback/class-ht-ctc-admin-deactivate-feedback.php';

do_action( 'ht_ctc_ah_admin_includes' );
