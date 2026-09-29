<?php
/**
 * Add image
 *
 * @uses admin-greetings-page.php - header image.
 *
 * @since 3.34
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hide_img               = '';
$hide_remove_img_button = '';

// safe
$options = isset( $options ) && is_array( $options ) ? $options : array();

$g_header_image = isset( $options['g_header_image'] ) ? esc_attr( $options['g_header_image'] ) : '';

$g_header_online_status       = ( isset( $options['g_header_online_status'] ) ) ? 1 : '';
$g_header_online_status_color = isset( $options['g_header_online_status_color'] ) ? esc_attr( $options['g_header_online_status_color'] ) : '#06e376';
$parent_class                 = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';


if ( '' === $g_header_image ) {
	$hide_img               = 'display:none;';
	$hide_remove_img_button = 'display:none;';
}
?>

<div class="template_g_header_image <?php echo esc_attr( $parent_class ); ?>" style="margin-bottom:40px;">


	<div class="row row_g_header_image">
		<div class="col s12" style="display:flex; gap:8px; align-items:center;">
			<input type="hidden" name="<?php echo esc_attr( $dbrow ); ?>[g_header_image]" class="g_header_image" value="<?php echo esc_attr( $g_header_image ); ?>" />
			<img class="g_header_image_preview" style="width:50px; height:50px; border-radius:50%; <?php echo esc_attr( $hide_img ); ?>" src="<?php echo esc_url( $g_header_image ); ?>" style="max-width: 100%;" />
			<input type="button" class="button-primary ctc_add_image_wp" value="Add Header Image" data-agent=""/>
			<input type="button" class="button-secondary ctc_remove_image_wp" style="margin: 0 1px; <?php echo esc_attr( $hide_remove_img_button ); ?>" value="Remove Image" data-agent=""/>
		</div>
	</div>

	<div class="row row_g_header_online_status" style="margin-bottom:0;">
		<div class="col s12">
			<label class="ctc_checkbox_label">
				<input class="g_header_online_status" name="<?php echo esc_attr( $dbrow ); ?>[g_header_online_status]" type="checkbox" value="1" <?php checked( $g_header_online_status, 1 ); ?> />
				<span><?php esc_html_e( 'Add Online Status badge at header image', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</div>
	</div>
	<div class="row_g_header_online_status_color">
		<div class="row ctc_side_by_side" style="margin-bottom:2px;">
			<div class="input-field col s6" style="margin-bottom:0;">
				<p calss="description" style="margin-bottom: 5px;">Online Status Badge Color</p>
				<input class="ht-ctc-color g_header_online_status_color" name="<?php echo esc_attr( $dbrow ); ?>[g_header_online_status_color]" data-default-color="#06e376" data-update-type="background-color" data-update-selector=".for_greetings_header_image_badge" id="g_header_online_status_color" value="<?php echo esc_attr( $g_header_online_status_color ); ?>" type="text">
			</div>
			<?php
			do_action( 'ht_ctc_ah_admin_header_status_badge' );
			?>
		</div>
		<p class="description" style="margin-top:0;">PRO: Badge color based on <a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat#ht_ctc_bh' ) ); ?>">business hours</a></p>
	</div>

</div>
