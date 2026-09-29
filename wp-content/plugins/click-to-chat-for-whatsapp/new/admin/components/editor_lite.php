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

$field_title  = ( isset( $input['title'] ) ) ? $input['title'] : '';
$description  = ( isset( $input['description'] ) ) ? $input['description'] : '';
$label        = ( isset( $input['label'] ) ) ? $input['label'] : '';
$placeholder  = ( isset( $input['placeholder'] ) ) ? $input['placeholder'] : '';
$parent_style = ( isset( $input['parent_style'] ) ) ? $input['parent_style'] : '';
$parent_class = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';

/**
 * Add font selection buttons to TinyMCE editor toolbar.
 *
 * @param array $buttons Existing buttons array.
 * @return array Modified buttons array with font selection buttons added.
 */
if ( ! function_exists( 'ctc_tinymce_add_font_sizes' ) ) {
	/**
	 * Add font size and font family selectors to TinyMCE.
	 *
	 * @param array $buttons Array of buttons.
	 * @return array Modified buttons array.
	 */
	function ctc_tinymce_add_font_sizes( $buttons ) {
		array_unshift( $buttons, 'fontselect' );
		array_unshift( $buttons, 'fontsizeselect' );
		return $buttons;
	}
}
add_filter( 'mce_buttons_2', 'ctc_tinymce_add_font_sizes' );


// db_value call again for editor. and santize using wp_kses
$db_value = ( isset( $options[ $db_key ] ) ) ? $options[ $db_key ] : '';

if ( '' !== $db_value ) {
	$allowed_html = wp_kses_allowed_html( 'post' );
	$db_value     = html_entity_decode( wp_kses( $db_value, $allowed_html ) );
}

?>
<div class="row ctc_component_editor <?php echo esc_attr( $parent_class ); ?>" style="<?php echo esc_attr( $parent_style ); ?>">
<?php
if ( '' !== $field_title ) {
	?>
	<p class="description ht_ctc_subtitle" style="margin-top: 12px;"><?php echo esc_html( $field_title ); ?> </p>
	<?php
}

?>
<?php

$content   = $db_value;
$editor_id = $db_key;
$args      = array(
	'textarea_name' => "{$dbrow}[{$db_key}]",
	'textarea_rows' => 10,
	'editor_height' => 150,
	'media_buttons' => false,
	'teeny'         => false,
	'tinymce'       => array(
		'toolbar1'         => 'bold, link, italic, underline, forecolor, backcolor, fontsizeselect, fontselect, undo, redo, removeformat',
		'toolbar2'         => false,
		'fontsize_formats' => '6px 8px 10px 12px 13px 14px 15px 16px 18px 20px 24px 28px 32px 36px',
	),
);

wp_editor( $content, $editor_id, $args );

if ( '' !== $description ) {
	?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
	<?php
}
?>
</div>
