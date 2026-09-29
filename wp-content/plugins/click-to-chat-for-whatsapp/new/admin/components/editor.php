<?php
/**
 * Editor
 *
 * $db_value is santized esc_attr - so call db again.. and reassing $db_value
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$editor_title = ( isset( $input['title'] ) ) ? $input['title'] : '';
$description  = ( isset( $input['description'] ) ) ? $input['description'] : '';
$label        = ( isset( $input['label'] ) ) ? $input['label'] : '';
$placeholder  = ( isset( $input['placeholder'] ) ) ? $input['placeholder'] : '';
$parent_style = ( isset( $input['parent_style'] ) ) ? $input['parent_style'] : '';
$parent_class = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';


// function ctc_edit_quicktags( $qtInit, $editor_id = 'content' ) {
// $qtInit['buttons'] = 'strong,code,more,close';
// return $qtInit;
// }
// add_filter( 'quicktags_settings', 'ctc_edit_quicktags', 10, 2 );

// if ( ! function_exists( 'ctc_tiny_mce_toolbar_settings' ) ) {
// function ctc_tiny_mce_toolbar_settings( $args ) {
// $args['fontsize_formats'] = "6px 8px 10px 12px 13px 14px 15px 16px 18px 20px 24px 28px 32px 36px";
// return $args;
// }
// }
// add_filter( 'tiny_mce_before_init', 'ctc_tiny_mce_toolbar_settings' );

// if ( ! function_exists( 'ctc_tinymce_mce_buttons' ) ) {
// function ctc_tinymce_mce_buttons( $buttons ) {
// return $buttons;
// }
// }
// add_filter( 'mce_buttons', 'ctc_tinymce_mce_buttons' );

/**
 * Add TinyMCE buttons to editor toolbar.
 *
 * @param array $buttons Existing buttons.
 * @return array Modified buttons array.
 */
if ( ! function_exists( 'ctc_tinymce_mce_buttons_2' ) ) {
	/**
	 * Add TinyMCE buttons to second row.
	 *
	 * @param array $buttons Array of buttons.
	 * @return array Modified buttons array.
	 */
	function ctc_tinymce_mce_buttons_2( $buttons ) {

		$key = array_search( 'forecolor', $buttons, true );

		// add after forecolor
		if ( false !== $key && is_int( $key ) ) {
			array_splice( $buttons, $key + 1, 0, 'backcolor' );
		}

		// add at first
		array_unshift( $buttons, 'fontselect' );
		array_unshift( $buttons, 'fontsizeselect' );

		return $buttons;
	}
}
add_filter( 'mce_buttons_2', 'ctc_tinymce_mce_buttons_2' );

// db_value call again for editor. and santize using wp_kses
$db_value = ( isset( $options[ $db_key ] ) ) ? $options[ $db_key ] : '';

if ( '' !== $db_value ) {
	$allowed_html = wp_kses_allowed_html( 'post' );

	// $allowed_html['iframe'] = array(
	// 'src'             => true,
	// 'height'          => true,
	// 'width'           => true,
	// 'frameborder'     => true,
	// 'allowfullscreen' => true,
	// 'title' => true,
	// 'allow' => true,
	// 'autoplay' => true,
	// 'clipboard-write' => true,
	// 'encrypted-media' => true,
	// 'gyroscope' => true,
	// 'picture-in-picture' => true,
	// );

	$db_value = html_entity_decode( wp_kses( $db_value, $allowed_html ) );
}

?>
<div class="row ctc_component_editor <?php echo esc_attr( $parent_class ); ?>" style="<?php echo esc_attr( $parent_style ); ?>">
<p class="description ht_ctc_subtitle" style="margin-top: 2px;"><?php echo esc_html( $editor_title ); ?> </p>
<?php

$content   = $db_value;
$editor_id = $db_key;
$args      = array(
	'textarea_name'    => "{$dbrow}[{$db_key}]",
	'textarea_rows'    => 10,
	'editor_height'    => 250,
	// 'media_buttons' => false,
	'drag_drop_upload' => true,
	'tinymce'          => array(
		'textarea_rows'        => 10,
		'fontsize_formats'     => '6px 8px 10px 12px 13px 14px 15px 16px 18px 20px 24px 28px 32px 36px',
		'wordpress_adv_hidden' => false,
	),
);

wp_editor( $content, $editor_id, $args );

if ( '' !== $description ) {
	?>
	<p class="description" style="padding-left: 0.9rem;"><?php echo wp_kses_post( $description ); ?></p>
	<?php
}
?>
</div>
