<?php
/**
 * Admin device settings helper.
 *
 * Handles style selection and position configuration for desktop and mobile.
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 2.11 Updated in 3.3.3 when admin-mobile/admin-desktop were merged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// style
$style_desktop = ( isset( $options['style_desktop'] ) ) ? esc_attr( $options['style_desktop'] ) : '2';
$style_mobile  = ( isset( $options['style_mobile'] ) ) ? esc_attr( $options['style_mobile'] ) : '2';


// desktop position
$side_1       = ( isset( $options['side_1'] ) ) ? esc_attr( $options['side_1'] ) : '';
$side_1_value = ( isset( $options['side_1_value'] ) ) ? esc_attr( $options['side_1_value'] ) : '';
$side_2       = ( isset( $options['side_2'] ) ) ? esc_attr( $options['side_2'] ) : '';
$side_2_value = ( isset( $options['side_2_value'] ) ) ? esc_attr( $options['side_2_value'] ) : '';

// mobile position
$mobile_side_1       = ( isset( $options['mobile_side_1'] ) ) ? esc_attr( $options['mobile_side_1'] ) : '';
$mobile_side_1_value = ( isset( $options['mobile_side_1_value'] ) ) ? esc_attr( $options['mobile_side_1_value'] ) : '';
$mobile_side_2       = ( isset( $options['mobile_side_2'] ) ) ? esc_attr( $options['mobile_side_2'] ) : '';
$mobile_side_2_value = ( isset( $options['mobile_side_2_value'] ) ) ? esc_attr( $options['mobile_side_2_value'] ) : '';

$position_type        = ( isset( $options['position_type'] ) ) ? esc_attr( $options['position_type'] ) : 'fixed';
$position_type_mobile = ( isset( $options['position_type_mobile'] ) ) ? esc_attr( $options['position_type_mobile'] ) : 'fixed';

$position_type_values = array(
	'fixed' => 'Fixed',
);

$position_type_values = apply_filters( 'ht_ctc_fh_position_type_values', $position_type_values );

?>

<ul class="collapsible ht_ctc_device_settings">
<li class="">
<div class="collapsible-header"><?php esc_html_e( 'Style, Position - Desktop, Mobile', 'click-to-chat-for-whatsapp' ); ?>
	<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
</div>
<div class="collapsible-body">

<blockquote class="not_samesettings" style="margin-bottom: 25px;">Desktop:</blockquote>

<!-- style -->
<p class="description ht_ctc_admin_desktop ht_ctc_subtitle"><?php esc_html_e( 'Select Style', 'click-to-chat-for-whatsapp' ); ?><span class="not_samesettings"><?php esc_html_e( ' (Desktop)', 'click-to-chat-for-whatsapp' ); ?></span>:</p>
<div class="row ht_ctc_admin_desktop" id="row_styles">
	<input name="<?php echo esc_attr( $dbrow ); ?>[style_desktop]" value="<?php echo esc_attr( $style_desktop ); ?>" type="text" style="display:none;" class="chat_select_style select_style_desktop ctc_ad_main_page_on_change_style">

	<div class="row ht_ctc_admin_desktop ctc_select_style ctc_style_desktop">
		<div class="collection select_style_container" data-style="<?php echo esc_attr( $style_desktop ); ?>">
			<span class="collection-item select_style_item"  data-style="1"><span class="badge">Theme Button</span>Style-1</span>
			<span class="collection-item select_style_item"  data-style="2"><span class="badge">Green Square Icon</span>Style-2</span>
			<span class="collection-item select_style_item"  data-style="3"><span class="badge">Icon</span>Style-3</span>
			<span class="collection-item select_style_item"  data-style="3_1"><span class="badge">Large Icon</span>Style-3 Extend</span>
			<span class="collection-item select_style_item"  data-style="4"><span class="badge">Chip (cylindrical)</span>Style-4</span>
			<span class="collection-item select_style_item"  data-style="5"><span class="badge">Image on hover Content Box</span>Style-5</span>
			<span class="collection-item select_style_item"  data-style="6"><span class="badge">Plain text</span>Style-6</span>
			<span class="collection-item select_style_item"  data-style="7"><span class="badge">Icon with padding</span>Style-7</span>
			<span class="collection-item select_style_item"  data-style="7_1"><span class="badge">Icon on hover extend</span>Style-7 Extend</span>
			<span class="collection-item select_style_item"  data-style="8"><span class="badge">Button</span>Style-8</span>
			<span class="collection-item select_style_item"  data-style="99"><span class="badge">Own Image</span>Style-99</span>
		</div>
	</div>

	<p class="description"><a style="" target="_blank" href="https://holithemes.com/plugins/click-to-chat/list-of-styles/"><?php esc_html_e( 'List of Styles', 'click-to-chat-for-whatsapp' ); ?></a> | 
		<span title="colors, size, hover effects, .." class="customize_styles_link"><?php esc_html_e( 'Customize the styles', 'click-to-chat-for-whatsapp' ); ?>  <a target="_blank" class="customize_styles_href" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-customize-styles' ) ); ?>">( Click to Chat -> Customize )</a></span> | 
		<span title="add message window"><?php esc_html_e( 'Add Greetings Dialog', 'click-to-chat-for-whatsapp' ); ?>  <a target="_blank" class="greetings_page_link" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-greetings' ) ); ?>">( Click to Chat -> Greetings )</a></span>
	</p>

</div>


<!-- position type -->
<p class="description ht_ctc_admin_desktop ht_ctc_subtitle"><?php esc_html_e( 'Position Type', 'click-to-chat-for-whatsapp' ); ?><span class="not_samesettings"><?php esc_html_e( ' (Desktop)', 'click-to-chat-for-whatsapp' ); ?></span>:</p>
<div class="row ht_ctc_admin_desktop">
	<div class="input-field col s12 m12">
		<select name="<?php echo esc_attr( $dbrow ); ?>[position_type]" class="chat_select_position_type ctc_no_demo">
			<?php
			foreach ( $position_type_values as $key => $value ) {
				?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $position_type ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
				<?php
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Fixed: Position relative to the screen, stays at the same place even after page scroll', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description"><?php esc_html_e( 'Absolute: Position relative to the content (body tag) and moves with page scroll', 'click-to-chat-for-whatsapp' ); ?> (PRO) - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/position-to-place/#pro_block">more info</a></p>
	</div>
</div>

<?php
// Action hook - After select style - Desktop
// do_action('ht_ctc_ah_admin_desktop_after_select_sytle', $options, $dbrow );
?>

<!-- Desktop position -->
<!-- side - 1 -->
<p class="description ht_ctc_admin_desktop ht_ctc_subtitle" id="position_to_place"><?php esc_html_e( 'Position to Place', 'click-to-chat-for-whatsapp' ); ?><span class="not_samesettings"><?php esc_html_e( ' (Desktop)', 'click-to-chat-for-whatsapp' ); ?></span>:</p>
<div class="row ht_ctc_admin_desktop" style="display:flex; margin-top:16px;">
	<br>
	<div class="input-field col s6">
		<select name="<?php echo esc_attr( $dbrow ); ?>[side_1]" class="position_bottom_top ctc_demo_position">
				<option value="bottom" <?php echo ( 'bottom' === $side_1 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'bottom', 'click-to-chat-for-whatsapp' ); ?></option>
				<option value="top" <?php echo ( 'top' === $side_1 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'top', 'click-to-chat-for-whatsapp' ); ?></option>
		</select>
		<label>top / bottom </label>
	</div>
	<div class="input-field col s6">
		<input name="<?php echo esc_attr( $dbrow ); ?>[side_1_value]" value="<?php echo esc_attr( $side_1_value ); ?>" id="side_1_value" type="text" class="input-margin position_bottom_top_value ctc_demo_position">
		<label for="side_1_value"><?php esc_html_e( 'E.g. 10px', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>
</div>

<!-- side - 2 -->
<div class="row ht_ctc_admin_desktop" style="display:flex; margin-bottom:0;">
	<div class="input-field col s6">
		<select name="<?php echo esc_attr( $dbrow ); ?>[side_2]" class="position_right_left ctc_demo_position">
				<option value="right" <?php echo ( 'right' === $side_2 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'right', 'click-to-chat-for-whatsapp' ); ?></option>
				<option value="left" <?php echo ( 'left' === $side_2 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'left', 'click-to-chat-for-whatsapp' ); ?></option>
		</select>
		<label><?php esc_html_e( 'right / left', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>

	<div class="input-field col s6">
		<input name="<?php echo esc_attr( $dbrow ); ?>[side_2_value]" value="<?php echo esc_attr( $side_2_value ); ?>" id="side_2_value" type="text" class="input-margin position_right_left_value ctc_demo_position">
		<label for="side_2_value"><?php esc_html_e( 'E.g. 50%', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>
</div>
<p class="description ht_ctc_admin_desktop"><?php esc_html_e( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/position-to-place/">more info</a> </p>



<br><br>

<?php

// Same setting for Mobile Devices
if ( isset( $options['same_settings'] ) ) {
	?>
	<p class="description" style="margin-bottom: 25px;">
		<label>
			<input name="<?php echo esc_attr( $dbrow ); ?>[same_settings]" type="checkbox" value="1" <?php checked( $options['same_settings'], 1 ); ?> class="same_settings ctc_no_demo" id="same_settings" />
			<span><?php esc_html_e( 'Mobile and Desktop same setttings', 'click-to-chat-for-whatsapp' ); ?></span>
		</label>
	</p>
	<?php
} else {
	?>
	<p class="description" style="margin-bottom: 25px;">
		<label>
			<input name="<?php echo esc_attr( $dbrow ); ?>[same_settings]" type="checkbox" value="1" class="same_settings ctc_no_demo" id="same_settings" />
			<span><?php esc_html_e( 'Mobile and Desktop same setttings', 'click-to-chat-for-whatsapp' ); ?></span>
		</label>
	</p>
	<?php
}

?>

<blockquote class="not_samesettings " style="margin-bottom: 25px;"><?php esc_html_e( 'Mobile', 'click-to-chat-for-whatsapp' ); ?>:</blockquote>

<!-- mobile style -->
<p class="description ht_ctc_admin_mobile ht_ctc_subtitle not_samesettings"><?php esc_html_e( 'Select Style (Mobile)', 'click-to-chat-for-whatsapp' ); ?>:</p>
<div class="row ht_ctc_admin_mobile not_samesettings">
	<input name="<?php echo esc_attr( $dbrow ); ?>[style_mobile]" value="<?php echo esc_attr( $style_mobile ); ?>" type="text" style="display:none;" class="chat_select_style select_style_mobile ctc_ad_main_page_on_change_style">

	<div class="row ht_ctc_admin_mobile ctc_select_style ctc_style_mobile">
		<div class="collection m_select_style_container" data-style="<?php echo esc_attr( $style_mobile ); ?>">
			<span class="collection-item m_select_style_item"  data-style="1"><span class="badge">Theme Button</span>Style-1</span>
			<span class="collection-item m_select_style_item"  data-style="2"><span class="badge">Green Square Icon</span>Style-2</span>
			<span class="collection-item m_select_style_item"  data-style="3"><span class="badge">Icon</span>Style-3</span>
			<span class="collection-item m_select_style_item"  data-style="3_1"><span class="badge">Large Icon</span>Style-3 Extend</span>
			<span class="collection-item m_select_style_item"  data-style="4"><span class="badge">Chip (cylindrical)</span>Style-4</span>
			<span class="collection-item m_select_style_item"  data-style="5"><span class="badge">Image on hover Content Box</span>Style-5</span>
			<span class="collection-item m_select_style_item"  data-style="6"><span class="badge">Plain text</span>Style-6</span>
			<span class="collection-item m_select_style_item"  data-style="7"><span class="badge">Icon with padding</span>Style-7</span>
			<span class="collection-item m_select_style_item"  data-style="7_1"><span class="badge">Icon on hover extend</span>Style-7 Extend</span>
			<span class="collection-item m_select_style_item"  data-style="8"><span class="badge">Button</span>Style-8</span>
			<span class="collection-item m_select_style_item"  data-style="99"><span class="badge">Own Image</span>Style-99</span>
		</div>
	</div>


</div>



<!-- position type - mobile -->
<p class="description ht_ctc_admin_mobile ht_ctc_subtitle not_samesettings"><?php esc_html_e( 'Position Type', 'click-to-chat-for-whatsapp' ); ?>:</p>
<div class="row ht_ctc_admin_mobile not_samesettings">
	<div class="input-field col s12 m12">
		<select name="<?php echo esc_attr( $dbrow ); ?>[position_type_mobile]" class="chat_select_position_type ctc_no_demo">
			<?php
			foreach ( $position_type_values as $key => $value ) {
				?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $position_type_mobile ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
				<?php
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Fixed: Position relative to the screen, stays at the same place even after page scroll', 'click-to-chat-for-whatsapp' ); ?></p>
		<p class="description"><?php esc_html_e( 'Absolute: Position relative to the content (body tag) and moves with page scroll', 'click-to-chat-for-whatsapp' ); ?> (PRO) - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/position-to-place/#pro_block">more info</a></p>
	</div>
</div>

<?php
// Action hook - After select style - Mobile
// do_action('ht_ctc_ah_admin_mobile_after_select_sytle', $options, $dbrow );
?>

<!-- Mobile position -->
<!-- side - 1 -->
<p class="description ht_ctc_admin_mobile ht_ctc_subtitle not_samesettings"><?php esc_html_e( 'Position to Place (Mobile)', 'click-to-chat-for-whatsapp' ); ?>:</p>
<div class="row ht_ctc_admin_mobile not_samesettings" style="display:flex; margin-top:16px;">
	<br>
	<div class="input-field col s6">
		<select name="<?php echo esc_attr( $dbrow ); ?>[mobile_side_1]" class="select-2 ctc_no_demo">
				<option value="bottom" <?php echo ( 'bottom' === $mobile_side_1 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'bottom', 'click-to-chat-for-whatsapp' ); ?></option>
				<option value="top" <?php echo ( 'top' === $mobile_side_1 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'top', 'click-to-chat-for-whatsapp' ); ?></option>
		</select>
		<label>top / bottom </label>
	</div>
	<div class="input-field col s6">
		<input name="<?php echo esc_attr( $dbrow ); ?>[mobile_side_1_value]" value="<?php echo esc_attr( $mobile_side_1_value ); ?>" id="mobile_side_1_value" type="text" class="input-margin ctc_no_demo">
		<label for="mobile_side_1_value"><?php esc_html_e( 'E.g. 10px', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>
</div>

<!-- side - 2 -->
<div class="row ht_ctc_admin_mobile not_samesettings" style="display:flex; margin-bottom:0;">
	<div class="input-field col s6">
		<select name="<?php echo esc_attr( $dbrow ); ?>[mobile_side_2]" class="select-2 ctc_no_demo">
				<option value="right" <?php echo ( 'right' === $mobile_side_2 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'right', 'click-to-chat-for-whatsapp' ); ?></option>
				<option value="left" <?php echo ( 'left' === $mobile_side_2 ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'left', 'click-to-chat-for-whatsapp' ); ?></option>
		</select>
		<label><?php esc_html_e( 'right / left', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>

	<div class="input-field col s6">
		<input name="<?php echo esc_attr( $dbrow ); ?>[mobile_side_2_value]" value="<?php echo esc_attr( $mobile_side_2_value ); ?>" id="mobile_side_2_value" type="text" class="input-margin ctc_no_demo">
		<label for="mobile_side_2_value"><?php esc_html_e( 'E.g. 50%', 'click-to-chat-for-whatsapp' ); ?></label>
	</div>
</div>
<p class="description ht_ctc_admin_mobile not_samesettings"><?php esc_html_e( 'Add css units as suffix - e.g. 10px, 50%', 'click-to-chat-for-whatsapp' ); ?> - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/position-to-place/"><?php esc_html_e( 'more info', 'click-to-chat-for-whatsapp' ); ?></a> </p>



<br class="not_samesettings">
<hr class="not_samesettings" style="max-width: 500px;">
<br class="not_samesettings">
<p class="description"><span class="not_samesettings select_styles_issue_description" style="font-size: 0.7em;">If Styles for desktop, mobile not selected as expected <span style="color: #039be5; cursor: pointer;">Check this</span>, - <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/select-styles/#styles-not-applied">more info</a></span></p>

<div class="select_styles_issue_checkbox ctc_init_display_none" style="">
	<?php
	// If checked loads both styles and display the needed style
	// cache issue while selecting styles
	if ( isset( $options['select_styles_issue'] ) ) {
		?>
		<p id="styles_issue">
			<label>
				<input name="<?php echo esc_attr( $dbrow ); ?>[select_styles_issue]" type="checkbox" value="1" <?php checked( $options['select_styles_issue'], 1 ); ?> id="select_styles_issue" />
				<!-- <span>Style for device is not as expected(due to cache)</span> -->
				<span><?php esc_html_e( 'Check this only, If styles for mobile, desktop not selected as expected(due to cache)', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
		<?php
	} else {
		?>
		<p id="styles_issue">
			<label>
				<input name="<?php echo esc_attr( $dbrow ); ?>[select_styles_issue]" type="checkbox" value="1" id="select_styles_issue" />
				<span><?php esc_html_e( 'Check this, If styles for mobile, desktop not selected as expected(due to cache)', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
		<?php
	}
	?>
</div>

</div>
</div>
</li>
</ul>
