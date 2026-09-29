<?php
/**
 *  Admin Show/Hide
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 2.8 updated 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $dbrow = 'ht_ctc_chat_options';

?>

<ul class="collapsible ht_ctc_show_hide_settings">
<li class="">
<div class="collapsible-header" id="showhide_settings"><?php esc_html_e( 'Display Settings', 'click-to-chat-for-whatsapp' ); ?>
	<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
</div>
<div class="collapsible-body">

<?php


if ( 'chat' === $type ) {
	do_action( 'ht_ctc_ah_admin_chat_before_showhide' );
}
do_action( 'ht_ctc_ah_admin_before_showhide' );

$show_hide_settings = array(
	'home'     => 'Home Page',
	'posts'    => 'Posts',
	'pages'    => 'Pages',
	'archive'  => 'Archive pages',
	'category' => 'Category pages',
	'page_404' => '404 Page',
);

// woocommerce
if ( class_exists( 'WooCommerce' ) ) {
	$show_hide_settings['WooCommerce']        = '';
	$show_hide_settings['woo_product']        = 'Single Product pages';
	$show_hide_settings['woo_shop']           = 'Shop (Product Archive page)';
	$show_hide_settings['woo_cart']           = 'Cart page';
	$show_hide_settings['woo_checkout']       = 'Checkout page';
	$show_hide_settings['woo_order_received'] = 'Thank you / Order Received page';
	$show_hide_settings['woo_account']        = 'Account page';
}

// custom post types
$custom_post_types = get_post_types(
	array(
		'public'   => true,
		'_builtin' => false,
	)
);

// woocommerce product working in different way.. woo_product
unset( $custom_post_types['product'] );

// not empty array - custom post types
if ( ! empty( $custom_post_types ) ) {
	// title custom post type
	$show_hide_settings['Custom Post Types'] = '';
	// merge
	$show_hide_settings = array_merge( $show_hide_settings, $custom_post_types );
}

// display settings - options - sub array
$display_settings = ( isset( $options['display'] ) ) ? $options['display'] : '';

// n_show_hide
$check_global_display = ( isset( $display_settings['global_display'] ) ) ? esc_html( $display_settings['global_display'] ) : 'show';

// post id
$list_hideon_pages = ( isset( $display_settings['list_hideon_pages'] ) ) ? esc_html( $display_settings['list_hideon_pages'] ) : '';
$list_showon_pages = ( isset( $display_settings['list_showon_pages'] ) ) ? esc_html( $display_settings['list_showon_pages'] ) : '';
// category
$list_hideon_cat = ( isset( $display_settings['list_hideon_cat'] ) ) ? esc_html( $display_settings['list_hideon_cat'] ) : '';
$list_showon_cat = ( isset( $display_settings['list_showon_cat'] ) ) ? esc_html( $display_settings['list_showon_cat'] ) : '';

$display_desktop = ( isset( $options['display_desktop'] ) ) ? esc_attr( $options['display_desktop'] ) : 'show';
$display_mobile  = ( isset( $options['display_mobile'] ) ) ? esc_attr( $options['display_mobile'] ) : 'show';
?>

<div class="row show_hide_device">
	<p class="col s3">
		<span class="dashicons dashicons-desktop"></span>
		<?php esc_html_e( 'Desktop', 'click-to-chat-for-whatsapp' ); ?>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display_desktop]" value="show" type="radio" <?php checked( 'show' === $display_desktop ); ?> class="with-gap device_display radio_desktop"/>
		<span><?php esc_html_e( 'Show', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-visibility"></span>
		</label>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display_desktop]" value="hide" type="radio" <?php checked( 'hide' === $display_desktop ); ?> class="with-gap device_display radio_desktop"/>
		<span><?php esc_html_e( 'Hide', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-hidden"></span>
		</label>
	</p>
</div>

<!-- display mobile -->
<div class="row show_hide_device">
	<p class="col s3">
		<span class="dashicons dashicons-smartphone"></span>
		<?php esc_html_e( 'Mobile', 'click-to-chat-for-whatsapp' ); ?>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display_mobile]" value="show" type="radio" <?php checked( 'show' === $display_mobile ); ?> class="with-gap device_display radio_mobile"/>
		<span><?php esc_html_e( 'Show', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-visibility"></span>
		</label>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display_mobile]" value="hide" type="radio" <?php checked( 'hide' === $display_mobile ); ?> class="with-gap device_display radio_mobile"/>
		<span><?php esc_html_e( 'Hide', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-hidden"></span>
		</label>
	</p>
</div>

<br>
<hr style="max-width: 500px;">
<br>

<div class="row show_hide_global ">
	<p class="col s3">
		<!-- <span class="dashicons dashicons-admin-site-alt3"></span> -->
		<strong><?php esc_html_e( 'Global', 'click-to-chat-for-whatsapp' ); ?></strong>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][global_display]" value="show" type="radio" <?php checked( 'show' === $check_global_display ); ?> class="with-gap global_display"/>
		<span><?php esc_html_e( 'Show on all pages', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-visibility"></span>
		</label>
	</p>
	<p class="col s4">
		<label>
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][global_display]" value="hide" type="radio" <?php checked( 'hide' === $check_global_display ); ?> class="with-gap global_display"/>
		<span><?php esc_html_e( 'Hide on all pages', 'click-to-chat-for-whatsapp' ); ?></span>
		<span class="dashicons dashicons-hidden"></span>
		</label>
	</p>
</div>

<br>

<p class="description" style="margin:16px 0px 20px 0px;"><strong><?php esc_html_e( 'Overwrite the Global settings', 'click-to-chat-for-whatsapp' ); ?></strong></p>
<?php

foreach ( $show_hide_settings as $key => $value ) {

	if ( '' === $value ) {
		// heading
		?>
		<p class="description" style="margin-bottom:16px;"><strong><?php echo esc_html( $key ); ?>: </strong></p>
		<?php
	} else {
		$is_checked_show_hide = ( isset( $display_settings[ $key ] ) ) ? esc_html( $display_settings[ $key ] ) : 'g';
		if ( 'woo_order_received' === $key ) {
			// order_received / thank you page  -  is added later version after checkout feature is added. . should not distrub the exsiting users - default/initial checkout value itself..
			$is_checked_show_hide = ( isset( $display_settings[ $key ] ) ) ? esc_html( $display_settings[ $key ] ) : '';
			if ( '' === $is_checked_show_hide ) {
				$is_checked_show_hide = ( isset( $display_settings['woo_checkout'] ) ) ? esc_html( $display_settings['woo_checkout'] ) : 'g';
			}
		}
		?>
		<div class="row show_hide_types">
			<p class="col s3">
				<?php echo esc_html( $value ); ?>:
			</p>
			<p class="col s3 m3 l2 show_box">
				<label>
				<input name="<?php echo esc_attr( $dbrow ); ?>[display][<?php echo esc_attr( $key ); ?>]" value="show" type="radio" <?php checked( 'show' === $is_checked_show_hide ); ?> class="with-gap show_btn <?php echo esc_attr( $key ); ?>"/>
				<span class="ctc_radio_text"><?php esc_html_e( 'Show', 'click-to-chat-for-whatsapp' ); ?></span>
				<span class="dashicons dashicons-visibility"></span>
				</label>
			</p>
			<p class="col s3 m3 l2 hide_box">
				<label>
				<input name="<?php echo esc_attr( $dbrow ); ?>[display][<?php echo esc_attr( $key ); ?>]" value="hide" type="radio" <?php checked( 'hide' === $is_checked_show_hide ); ?> class="with-gap hide_btn <?php echo esc_attr( $key ); ?>"/>
				<span class="ctc_radio_text"><?php esc_html_e( 'Hide', 'click-to-chat-for-whatsapp' ); ?></span>
				<span class="dashicons dashicons-hidden"></span>
				</label>
			</p>
			<p class="col s3 m3 l2 global_box">
				<label>
				<input name="<?php echo esc_attr( $dbrow ); ?>[display][<?php echo esc_attr( $key ); ?>]" value="g" type="radio" <?php checked( '' === $is_checked_show_hide || 'g' === $is_checked_show_hide ); ?> class="with-gap global_btn <?php echo esc_attr( $key ); ?>"/>
				<span class="ctc_radio_text"><?php esc_html_e( 'Global', 'click-to-chat-for-whatsapp' ); ?> 
					<span class="global_show_or_hide_label"></span>
					<span class="global_show_or_hide_icon"></span>
				</span>
				</label>
			</p>
		</div>
	
		<?php
	}
}


?>
<br>
<p class="description"><strong><?php esc_html_e( 'Post Id\'s', 'click-to-chat-for-whatsapp' ); ?></strong></p>
<!-- ID's list to hide styles  -->
<div class="row hide_settings">
	<div class="input-field col s12 m7">
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][list_hideon_pages]" value="<?php echo esc_attr( $list_hideon_pages ); ?>" id="ccw_list_id_tohide" type="text" class="input-margin">
		<label for="ccw_list_id_tohide"><?php esc_html_e( 'Hide on this pages', 'click-to-chat-for-whatsapp' ); ?> <span class="dashicons dashicons-hidden"></span></label>
		<p class="description"><?php esc_html_e( "Add post id's to hide. Add multiple post id's by separating with a comma ( , )", 'click-to-chat-for-whatsapp' ); ?></p>
	</div>
</div>

<!-- ID's list to show styles -->   
<div class="row show_settings">
	<div class="input-field col s7">
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][list_showon_pages]" value="<?php echo esc_attr( $list_showon_pages ); ?>" id="ccw_list_id_toshow" type="text" class="input-margin">
		<label for="ccw_list_id_toshow"><?php esc_html_e( 'Show on this pages', 'click-to-chat-for-whatsapp' ); ?> <span class="dashicons dashicons-visibility"></span></label>
		<p class="description"><?php esc_html_e( "Add Post, Page, Media - ID's to show styles, Add multiple id's by separating with a comma ( , )", 'click-to-chat-for-whatsapp' ); ?></p>
	</div>
</div>

<p class="description"><strong><?php esc_html_e( 'Category names', 'click-to-chat-for-whatsapp' ); ?></strong></p>
<!-- Categorys list - to hide -->
<div class="row hide_settings">
	<div class="input-field col s12 m7">
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][list_hideon_cat]" value="<?php echo esc_attr( $list_hideon_cat ); ?>" id="list_hideon_cat" type="text" class="input-margin">
		<label for="list_hideon_cat"><?php esc_html_e( 'Hide on this Category posts', 'click-to-chat-for-whatsapp' ); ?> <span class="dashicons dashicons-hidden"></span></label>
		<p class="description"><?php esc_html_e( 'Hides on this Category type pages, Add multiple Categories by separating with a comma ( , ) ', 'click-to-chat-for-whatsapp' ); ?></p>
	</div>
</div>

<!-- Categorys list - to show -->
<div class="row show_settings">
	<div class="input-field col s7">
		<input name="<?php echo esc_attr( $dbrow ); ?>[display][list_showon_cat]" value="<?php echo esc_attr( $list_showon_cat ); ?>" id="ccw_list_cat_toshow" type="text" class="input-margin">
		<label for="ccw_list_cat_toshow"><?php esc_html_e( 'Show on this Category posts', 'click-to-chat-for-whatsapp' ); ?> <span class="dashicons dashicons-visibility"></span></label>
		<p class="description"><?php esc_html_e( 'Show on this Category type pages, Add multiple Categories by separating with a comma ( , )', 'click-to-chat-for-whatsapp' ); ?> </p>
	</div>
</div>


<?php

if ( 'chat' === $type ) {
	do_action( 'ht_ctc_ah_admin_chat_after_showhide' );
}
do_action( 'ht_ctc_ah_admin_after_showhide' );
?>

<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/show-hide-styles/"><?php esc_html_e( 'Display Settings', 'click-to-chat-for-whatsapp' ); ?></a> </p>
<!-- <details style="margin-top:5px;">
	<summary style="cursor:pointer;"><?php esc_html_e( 'Usecases', 'click-to-chat-for-whatsapp' ); ?></summary>
	<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/show-only-on-selected-pages/"><?php esc_html_e( 'Show only on selected pages', 'click-to-chat-for-whatsapp' ); ?></a><?php esc_html_e( ' (Single, Cart, Checkout page)', 'click-to-chat-for-whatsapp' ); ?></p>
	<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/hide-only-on-selected-pages/"><?php esc_html_e( 'Hide only on selected pages', 'click-to-chat-for-whatsapp' ); ?></a><?php esc_html_e( ' (Single, Cart, Checkout page)', 'click-to-chat-for-whatsapp' ); ?></p>
	<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/show-hide-on-mobile-desktop/"><?php esc_html_e( 'Show/Hide on Mobile/Desktop', 'click-to-chat-for-whatsapp' ); ?></a></p>
</details> -->

<?php
if ( ! defined( 'HT_CTC_PRO_VERSION' ) && isset( $type ) && 'chat' === $type ) {
	?>
	<br><hr><br>

	<p class="description">PRO</p>

	<div class="ctc_pro_content" style="margin-bottom: 25px;">
		<p class="description ht_ctc_subtitle"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/time-delay-scroll/"><?php esc_html_e( 'Time, Scroll Delay', 'click-to-chat-for-whatsapp' ); ?></a></p>
		<p class="description ht_ctc_content_point"><?php esc_html_e( 'Display After Time Delay', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description ht_ctc_content_point"><?php esc_html_e( 'Display After User Scroll', 'click-to-chat-for-whatsapp' ); ?></p>
	</div>

	<div style="margin-bottom: 25px;" id="ht_ctc_bh">
		<p class="description ht_ctc_subtitle"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/docs/business-hours-online-offline/"><?php esc_html_e( 'Business Hours', 'click-to-chat-for-whatsapp' ); ?> (<?php esc_html_e( 'online/offline', 'click-to-chat-for-whatsapp' ); ?>)</a></p>
		<p class="description ht_ctc_content_point"><?php esc_html_e( 'Hide When offline', 'click-to-chat-for-whatsapp' ); ?> (or)</p>
		<p class="description ht_ctc_content_point"><?php esc_html_e( 'Change WhatsApp Number When Offline', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description ht_ctc_content_point"><?php esc_html_e( 'Change Call to Action When Offline', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description ht_ctc_content_point">Online status badge at greetings header image during business hours</p>
	</div>

	<?php
}
?>

</div>
</li>
</ul>
